<?php
/**
 * Authorityモデル
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Model
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class Authority extends AppModel
{
	public $validate = array();

	public function __construct() {
		parent::__construct();

		//エラーメッセージ取得
		$this->validate = array(
			'default_name' => array(
				'notEmpty'  => array(
					'rule' => array('notEmpty'),
					'last' => true,
					'required' => true,
					'allowEmpty' => false,
					'message' => __('Please be sure to input.')
				),
				'maxLength'  => array(
					'rule' => array('maxLength', 30),
					'message' => __('The input must be up to %s characters.', 30)
				),
				'duplicationAuthorityName'  => array(
					'rule' => array('_duplicationAuthorityName'),
					'message' => __d('authority', 'Authority with the same name')
				)
			),

			'system_flag' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'last' => true,
					'required' => true,
					'message' => __('The input must be a boolean.')
				)
			),

			'hierarchy' => array(
				'numeric' => array(
					'rule' => 'numeric',
					'required' => true,
					'message' => __('The input must be a number.')
				),
				'range' => array(
					'rule' => array('range', NC_AUTH_GUEST - 1, NC_AUTH_ADMIN + 1),
					'message' => __('The input must be a number bigger than %d and less than %d.', NC_AUTH_GUEST, NC_AUTH_ADMIN)
				)
			),

			'allow_creating_community' => array(
				'rule' => array('inList', array(
					NC_ALLOW_CREATING_COMMUNITY_OFF,
					NC_ALLOW_CREATING_COMMUNITY_ONLY_USER,
					NC_ALLOW_CREATING_COMMUNITY_ALL_USER,
					NC_ALLOW_CREATING_COMMUNITY_FORCE_ALL,
					NC_ALLOW_CREATING_COMMUNITY_ADMIN,
				), false),
				'allowEmpty' => true,
				'message' => __('It contains an invalid string.')
			),

			'allow_new_participant' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'last' => true,
					'required' => true,
					'message' => __('The input must be a boolean.')
				)
			),

			'myportal_use_flag' => array(
				'inList' => array(
					'rule' => array('inList', array(
						NC_MYPORTAL_USE_NOT,
						NC_MYPORTAL_USE_ALL,
						NC_MYPORTAL_MEMBERS,
					), false),
					'allowEmpty' => false,
					'message' => __('It contains an invalid string.')
				)
			),

			'allow_myportal_viewing_hierarchy' => array(
				'inList' => array(
					'rule' => array('inList', array(
						NC_AUTH_GUEST,
						NC_AUTH_MIN_GENERAL,
						NC_AUTH_MIN_MODERATE,
						NC_AUTH_MIN_CHIEF,
					), false),
					'allowEmpty' => false,
					'message' => __('It contains an invalid string.')
				)
			),

			'private_use_flag' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'last' => true,
					'required' => false,
					'message' => __('The input must be a boolean.')
				)
			),

			'public_createroom_flag' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'last' => true,
					'required' => false,
					'message' => __('The input must be a boolean.')
				)
			),

			'group_createroom_flag' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'last' => true,
					'required' => false,
					'message' => __('The input must be a boolean.')
				)
			),

			'myportal_createroom_flag' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'last' => true,
					'required' => false,
					'message' => __('The input must be a boolean.')
				)
			),

			'private_createroom_flag' => array(
				'boolean'  => array(
									'rule' => array('boolean'),
									'last' => true,
									'required' => false,
									'message' => __('The input must be a boolean.')
								)
			),

			'allow_htmltag_flag' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'last' => true,
					'required' => true,
					'message' => __('The input must be a boolean.')
				)
			),

			'allow_meta_flag' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'last' => true,
					'required' => true,
					'allowEmpty' => false,
					'message' => __('The input must be a boolean.')
				)
			),

			'allow_theme_flag' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'last' => true,
					'required' => true,
					'allowEmpty' => false,
					'message' => __('The input must be a boolean.')
				)
			),

			'allow_style_flag' => array(
				'inList' => array(
					'rule' => array('inList', array(
						_OFF,
						_ON,
						NC_ALLOWED_TO_EDIT_CSS,
					), false),
					'allowEmpty' => false,
					'message' => __('It contains an invalid string.')
				),
			),

			'allow_layout_flag' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'last' => true,
					'required' => true,
					'allowEmpty' => false,
					'message' => __('The input must be a boolean.')
				)
			),

			'allow_attachment' => array(
				'numeric' => array(
					'rule' => 'numeric',
					'required' => true,
					'message' => __('The input must be a number.')
				),
				'range' => array(
					'rule' => array('range', -1, 3),
					'message' => __('The input must be a number bigger than %d and less than %d.', 0, 2)
				)
			),

			'allow_video' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'last' => true,
					'required' => true,
					'message' => __('The input must be a boolean.')
				)
			),

			'max_size' => array(
				'numeric' => array(
					'rule' => 'numeric',
					'required' => true,
					'message' => __('The input must be a number.')
				)
			),

			'change_leftcolumn_flag' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'last' => true,
					'required' => false,
					'message' => __('The input must be a boolean.')
				)
			),

			'change_rightcolumn_flag' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'last' => true,
					'required' => false,
						'message' => __('The input must be a boolean.')
				)
			),

			'change_headercolumn_flag' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'last' => true,
					'required' => false,
					'message' => __('The input must be a boolean.')
				)
			),

			'change_footercolumn_flag' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'last' => true,
					'required' => false,
					'message' => __('The input must be a boolean.')
				)
			),

			'display_participants_editing' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'last' => true,
					'required' => false,
					'message' => __('The input must be a boolean.')
				)
			),

			'allow_move_operation' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'last' => true,
					'required' => false,
					'message' => __('The input must be a boolean.')
				)
			),

			'allow_copy_operation' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'last' => true,
					'required' => false,
					'message' => __('The input must be a boolean.')
				)
			),

			'allow_shortcut_operation' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'last' => true,
					'required' => false,
					'message' => __('The input must be a boolean.')
				)
			),

			'allow_operation_of_shortcut' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'last' => true,
					'required' => false,
					'message' => __('The input must be a boolean.')
				)
			),

		);
	}

/**
 * 重複チェック
 *
 * @param   array    $data
 * @return  boolean
 */
	public function _duplicationAuthorityName($data) {
		$lang = Configure::read(NC_CONFIG_KEY.'.'.'language');

		$conditions = array(
			'or' => array(
				'Authority.default_name' => $data['default_name'],
				'AuthorityLang.name' => $data['default_name'],
			),
		);
		if(!empty($this->data['Authority']['id']))
			$conditions['Authority.id !='] = $this->data['Authority']['id'];
		$params = array(
			'fields' => array(
				'Authority.id',
				'Authority.default_name',
				'AuthorityLang.name'
			),
			'conditions' => $conditions,
			'joins' => array(
				array("type" => "LEFT",
					"table" => "authority_langs",
					"alias" => "AuthorityLang",
					"conditions" => array(
						"`AuthorityLang`.`authority_id`=`Authority`.`id`",
						'AuthorityLang.lang' => $lang
					)
				)
			),
			'recursive' => -1,
		);

		$authorities = $this->find( 'all', $params);
		if(count($authorities) == 0) {
			return true;
		}

		foreach($authorities as $authority) {
			if(isset($authority['AuthorityLang']['name'])) {
				if($authority['AuthorityLang']['name'] == $data['default_name']) {
					return false;
				}
			} else if($authority['Authority']['default_name'] == $data['default_name']) {
				return false;
			}
		}

		return true;
	}

/**
 * 権限追加時初期値
 * @param   integer baseAuthorityId
 * @return  Model Authority
 * @since   v 3.0.0.0
 */
	public function findDefault($baseAuthorityId = null) {
		if(!isset($baseAuthorityId)) {
			$hierarchyArr = $this->getHierarchyByUserAuthorityId(NC_AUTH_GENERAL_ID);
			return array(
				'Authority' => array(
					'id' => 0,
					'default_name' => '',
					'system_flag' => _OFF,
					'hierarchy' => $hierarchyArr[0],
			));
		}
		$hierarchyArr = $this->getHierarchyByUserAuthorityId($baseAuthorityId);
		if($baseAuthorityId == NC_AUTH_ADMIN_ID) {
			$hierarchy = $hierarchyArr[1];
		} else if($baseAuthorityId == NC_AUTH_GUEST_ID) {
			$hierarchy = $hierarchyArr[0];
		} else {
			$hierarchy = $hierarchyArr[0];
			$interlevel = $hierarchyArr[1] - $hierarchyArr[0];
			if($interlevel > 0) {
				$hierarchy += floor($interlevel/2);
			}
		}
		$index = $baseAuthorityId - 1;
		$allowCreatingCommunityArr = explode('|', AUTHORITY_ALLOW_CREATING_COMMUNITY_LIST);
		$allowNewParticipantArr = explode('|', AUTHORITY_ALLOW_NEW_PARTICIPANT_LIST);
		$myportalUseFlagArr = explode('|', AUTHORITY_MYPORTAL_USE_FLAG_LIST);
		$allowMyportalViewingHierarchyArr = explode('|', AUTHORITY_MYPORTAL_VIEWING_HIERARCHY_LIST);
		$privateUseFlagArr = explode('|', AUTHORITY_PRIVATE_USE_FLAG_LIST);
		$publicCreateroomFlagArr = explode('|', AUTHORITY_PUBLIC_CREATEROOM_FLAG_LIST);
		$groupCreateroomFlagArr = explode('|', AUTHORITY_GROUP_CREATEROOM_FLAG_LIST);
		$myportalCreateroomFlagArr = explode('|', AUTHORITY_MYPORTAL_CREATEROOM_FLAG_LIST);
		$privateCreateroomFlagArr = explode('|', AUTHORITY_PRIVATE_CREATEROOM_FLAG_LIST);
		$allowHtmltagFlagArr = explode('|', AUTHORITY_ALLOW_HTMLTAG_FLAG_LIST);
		$allowMetaFlagArr = explode('|', AUTHORITY_ALLOW_META_FLAG_LIST);
		$allowThemeFlagArr = explode('|', AUTHORITY_ALLOW_THEME_FLAG_LIST);
		$allowStyleFlagArr = explode('|', AUTHORITY_ALLOW_STYLE_FLAG_LIST);
		$allowLayoutFlagArr = explode('|', AUTHORITY_ALLOW_LAYOUT_FLAG_LIST);
		$allowAttachmentArr = explode('|', AUTHORITY_ALLOW_ATTACHMENT);
		$allowVideoArr = explode('|', AUTHORITY_ALLOW_VIDEO);
		$maxSizeArr = explode('|', AUTHORITY_MAX_SIZE);
		$changeLeftcolumnFlagArr = explode('|', AUTHORITY_CHANGE_LEFTCOLUMN_FLAG);
		$changeRightcolumnFlagArr = explode('|', AUTHORITY_CHANGE_RIGHTCOLUMN_FLAG);
		$changeHeadercolumnFlagArr = explode('|', AUTHORITY_CHANGE_HEADERCOLUMN_FLAG);
		$changeFootercolumnFlagArr = explode('|', AUTHORITY_CHANGE_FOOTERCOLUMN_FLAG);
		$allowMoveOperationArr = explode('|', AUTHORITY_ALLOW_MOVE_OPERATION);
		$allowCopyOperationArr = explode('|', AUTHORITY_ALLOW_COPY_OPERATION);
		$allowOperationOfShortcutArr = explode('|', AUTHORITY_ALLOW_SHORTCUT_OPERATION);
		$allowShortcutOperationArr = explode('|', AUTHORITY_ALLOW_OPERATION_OF_SHORTCUT);

		$ret = array(
			'Authority' => array(
				'id' => 0,
				'default_name' => '',
				'system_flag' => _OFF,
				'hierarchy' => $hierarchy,
				'allow_creating_community' => $allowCreatingCommunityArr[$index],
				'allow_new_participant' => $allowNewParticipantArr[$index],
				'myportal_use_flag' => $myportalUseFlagArr[$index],
				'allow_myportal_viewing_hierarchy' => $allowMyportalViewingHierarchyArr[$index],
				'private_use_flag' => $privateUseFlagArr[$index],
				'public_createroom_flag' => $publicCreateroomFlagArr[$index],
				'group_createroom_flag' => $groupCreateroomFlagArr[$index],
				'myportal_createroom_flag' => $myportalCreateroomFlagArr[$index],
				'private_createroom_flag' => $privateCreateroomFlagArr[$index],
				'allow_htmltag_flag' => $allowHtmltagFlagArr[$index],
				'allow_meta_flag' => $allowMetaFlagArr[$index],
				'allow_theme_flag' => $allowThemeFlagArr[$index],
				'allow_style_flag' => $allowStyleFlagArr[$index],
				'allow_layout_flag' => $allowLayoutFlagArr[$index],
				'allow_attachment' => $allowAttachmentArr[$index],
				'allow_video' => $allowVideoArr[$index],
				'max_size' => $maxSizeArr[$index],
				'change_leftcolumn_flag' => $changeLeftcolumnFlagArr[$index],
				'change_rightcolumn_flag' => $changeRightcolumnFlagArr[$index],
				'change_headercolumn_flag' => $changeHeadercolumnFlagArr[$index],
				'change_footercolumn_flag' => $changeFootercolumnFlagArr[$index],
				'display_participants_editing' => _OFF,
				'allow_move_operation' => $allowMoveOperationArr[$index],
				'allow_copy_operation' => $allowCopyOperationArr[$index],
				'allow_shortcut_operation' => $allowShortcutOperationArr[$index],
				'allow_operation_of_shortcut' => $allowShortcutOperationArr[$index],
			)
		);

		return $ret;
	}

/**
 * 権限一覧取得
 * @param  string       $type
 * @param  array        $query
 * @return Model Authority|Authorities
 * @since  v 3.0.0.0
 */
	public function findList($type = 'first', $query = array()) {
		$query['fields'] = 'Authority.*, AuthorityLang.name';
		$query['joins'] = $this->getJoinsArray();
		$query['order'] = array(
			'Authority.hierarchy' => "DESC",
			'Authority.system_flag' => "DESC"
		);
		return parent::find($type, $query);
	}

/**
 * 参加者選択 -> 権限Select
 * @param  void
 * @return array
 * @since  v 3.0.0.0
 */
	public function findAuthSelect() {
		$conditions = array(
			'Authority.hierarchy >=' => NC_AUTH_GUEST,
			'Authority.hierarchy <=' => NC_AUTH_CHIEF,
			'Authority.display_participants_editing' => _ON,		// 参加ルーム選択に出すものだけ取得
		);
		$order = array(
			'Authority.hierarchy' => 'DESC',
			'Authority.id' => 'DESC',
		);
		$params = array(
			'fields' => array(
				'Authority.id',
				'Authority.default_name',
				'AuthorityLang.name',
				'Authority.hierarchy'
			),
			'conditions' => $conditions,
			'joins' => $this->getJoinsArray(),
			'order' => $order,
			//'callbacks' => 'after',
		);

		return $this->_afterFind($this->find('all', $params));
	}

	protected function _afterFind($results) {
		$rets = array();
		$select_chief_arr = array();
		$select_moderate_arr = array();
		$select_general_arr = array();
		$select_guest_arr = array();

		foreach ($results as $key => $val) {
			$hierarchy = $val['Authority']['hierarchy'];
			if(isset($val['AuthorityLang']['name'])) {
				$val['Authority']['name'] = $val['AuthorityLang']['name'];
			} else {
				$val['Authority']['name'] = $val['Authority']['default_name'];
			}
			if($hierarchy >= NC_AUTH_MIN_ADMIN) {
				continue;
			} else if($hierarchy >= NC_AUTH_MIN_CHIEF) {
				$select_chief_arr[$val['Authority']['id']] = $val['Authority'];
			} else if($hierarchy >= NC_AUTH_MIN_MODERATE) {
				$select_moderate_arr[$val['Authority']['id']] = $val['Authority'];
			} else if($hierarchy >= NC_AUTH_MIN_GENERAL) {
				$select_general_arr[$val['Authority']['id']] = $val['Authority'];
			} else if($hierarchy >= NC_AUTH_GUEST) {
				$select_guest_arr[$val['Authority']['id']] = $val['Authority'];
			} else {
				continue;
			}
		}
		$rets[NC_AUTH_CHIEF] = $select_chief_arr;
		$rets[NC_AUTH_MODERATE] = $select_moderate_arr;
		$rets[NC_AUTH_GENERAL] = $select_general_arr;
		$rets[NC_AUTH_GUEST] = $select_guest_arr;
		$rets[NC_AUTH_OTHER][NC_AUTH_OTHER_ID]['id'] = NC_AUTH_OTHER_ID;
		$rets[NC_AUTH_OTHER][NC_AUTH_OTHER_ID]['name'] = __('Non members');
		$rets[NC_AUTH_OTHER][NC_AUTH_OTHER_ID]['hierarchy'] = NC_AUTH_OTHER;
		return $rets;
	}

/**
 * 参加者のみ取得する必要があるかどうかの確認
 * @param  integer       $authorityId
 * @param  Model Page    $page
 * @return integer
 * 			0(NC_DISPLAY_FLAG_ENTRY_USERS):参加者のみ表示
 * 			1(NC_PARTICIPANT_TYPE_DEFAULT_ENABLED):すべての会員表示（PageUserLinkにない会員は、default値と不参加のみ変更可）
 * 			2(NC_PARTICIPANT_TYPE_ENABLED):参加者のみ表示
 * @since  v 3.0.0.0
 */
	public function getParticipantType($authorityId, $page) {
		if($page['Page']['space_type'] == NC_SPACE_TYPE_PRIVATE || $page['Page']['space_type'] == NC_SPACE_TYPE_MYPORTAL) {
			return NC_DISPLAY_FLAG_ENTRY_USERS;
		} else if($page['Page']['space_type'] == NC_SPACE_TYPE_PUBLIC) {
			return NC_PARTICIPANT_TYPE_ENABLED;
		}
		$authority = $this->findById($authorityId);
		if(!isset($authority['Authority'])) {
			return NC_DISPLAY_FLAG_ENTRY_USERS;
		}

		if($authority['Authority']['allow_new_participant'] || $page['Community']['participate_force_all_users'] == _ON ) {
			return NC_PARTICIPANT_TYPE_ENABLED;
		} else if($page['Community']['publication_range_flag'] != NC_PUBLICATION_RANGE_FLAG_ONLY_USER) {
			return NC_PARTICIPANT_TYPE_DEFAULT_ENABLED;
		}
		return NC_DISPLAY_FLAG_ENTRY_USERS;
	}

/**
 * 権限リスト取得
 * @param   void
 * @return  array
 * @since   v 3.0.0.0
 */
	public function findSelectList() {
		$results = $this->find('all', array(
			'fields' => array('Authority.id', 'Authority.default_name', 'AuthorityLang.name'),
			'joins' => $this->getJoinsArray(),
			'order' => array('Authority.hierarchy' => 'DESC', 'Authority.id' => 'DESC'),
		));
		$ret = array();
		foreach($results as $result) {
			if(isset($result['AuthorityLang']['name'])) {
				$authorityName = $result['AuthorityLang']['name'];
			} else {
				$authorityName = $result['Authority']['default_name'];
			}
			$ret[$result['Authority']['id']] = $authorityName;
		}
		return $ret;
	}

/**
 * Authorityモデル共通JOIN文
 * @param   string  $lang
 * @return  array   $joins
 * @since   v 3.0.0.0
 */
	public function getJoinsArray($lang = null) {
		if(!isset($lang)) {
			$lang = Configure::read(NC_CONFIG_KEY.'.'.'language');
		}
		return array(
			array(
				"type" => "LEFT",
				"table" => "authority_langs",
				"alias" => "AuthorityLang",
				"conditions" => array(
					"`AuthorityLang`.`authority_id`=`Authority`.`id`",
					"`AuthorityLang`.`lang`" => $lang,
				)
			),
		);
	}
/**
 * チェックする権限の最小値を取得
 *
 * @param   integer $hierarchy
 * @return  integer $hierarchy
 * @since   v 3.0.0.0
 */
	public function getMinHierarchy($hierarchy) {
		return $this->getUserAuthorityId($hierarchy, false);
	}

/**
 * hierarchyからベース権限を取得
 *
 * @param   integer $hierarchy
 * @param   boolean $isUserAuthorityId hierarchyの最小値を取得する場合、false
 * @return  integer $userAuthorityId|$minHierarchy
 * @since   v 3.0.0.0
 */
	public function getUserAuthorityId($hierarchy, $isUserAuthorityId = true) {
		$userAuthorityId = null;
		if($hierarchy == NC_AUTH_OTHER) {
			$userAuthorityId = NC_AUTH_OTHER_ID;
			$minHierarchy = NC_AUTH_OTHER;
		} else if($hierarchy == NC_AUTH_GUEST) {
			$userAuthorityId = NC_AUTH_GUEST_ID;
			$minHierarchy = NC_AUTH_GUEST;
		} else if($hierarchy >= NC_AUTH_MIN_GENERAL && $hierarchy <= NC_AUTH_GENERAL) {
			$userAuthorityId = NC_AUTH_GENERAL_ID;
			$minHierarchy = NC_AUTH_MIN_GENERAL;
		} else if($hierarchy >= NC_AUTH_MIN_MODERATE && $hierarchy <= NC_AUTH_MODERATE) {
			$userAuthorityId = NC_AUTH_MODERATE_ID;
			$minHierarchy = NC_AUTH_MIN_MODERATE;
		} else if($hierarchy >= NC_AUTH_MIN_CHIEF && $hierarchy <= NC_AUTH_CHIEF) {
			$userAuthorityId = NC_AUTH_CHIEF_ID;
			$minHierarchy = NC_AUTH_MIN_CHIEF;
		} else if($hierarchy >= NC_AUTH_MIN_ADMIN && $hierarchy <= NC_AUTH_ADMIN) {
			$userAuthorityId = NC_AUTH_ADMIN_ID;
			$minHierarchy = NC_AUTH_MIN_ADMIN;
		}

		if(!$isUserAuthorityId) {
			return $minHierarchy;
		}
		return $userAuthorityId;
	}

/**
 * hierarchyからベース権限名称を取得
 *
 * @param   integer $hierarchy
 * @return  string $userAuthorityName
 * @since   v 3.0.0.0
 */
	public function getUserAuthorityName($hierarchy) {
		$userAuthorityName = __('Non members');
		$userAuthorityId = $this->getUserAuthorityId($hierarchy);
		switch($userAuthorityId) {
			case NC_AUTH_ADMIN_ID:
				$userAuthorityName = __('Administrator');
				break;
			case NC_AUTH_CHIEF_ID:
				$userAuthorityName = __('Room Manager');
				break;
			case NC_AUTH_MODERATE_ID:
				$userAuthorityName = __('Moderator');
				break;
			case NC_AUTH_GENERAL_ID:
				$userAuthorityName = __('Common User');
				break;
			case NC_AUTH_GUEST_ID:
				$userAuthorityName = __('Guest');
				break;
		}
		return $userAuthorityName;
	}

/**
 * user_authority_idからhierarchyの取得
 * @param   integer $userAuthorityId
 * @return  array   array(integer $minHierarchy, integer $maxHierarchy)
 * @since   v 3.0.0.0
 */
	public function getHierarchyByUserAuthorityId($userAuthorityId) {
		$ret = array(NC_AUTH_OTHER, NC_AUTH_OTHER);
		if($userAuthorityId == NC_AUTH_GUEST_ID) {
			$ret = array(NC_AUTH_GUEST, NC_AUTH_GUEST);
		} else if($userAuthorityId == NC_AUTH_GENERAL_ID) {
			$ret = array(NC_AUTH_MIN_GENERAL, NC_AUTH_GENERAL);
		} else if($userAuthorityId == NC_AUTH_MODERATE_ID) {
			$ret = array(NC_AUTH_MIN_MODERATE, NC_AUTH_MODERATE);
		} else if($userAuthorityId == NC_AUTH_CHIEF_ID) {
			$ret = array(NC_AUTH_MIN_CHIEF, NC_AUTH_CHIEF);
		} else if($userAuthorityId == NC_AUTH_ADMIN_ID) {
			$ret = array(NC_AUTH_MIN_ADMIN, NC_AUTH_ADMIN);
		}
		return $ret;
	}
}