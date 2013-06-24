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
	public $name = 'Authority';
	public $validate = array();

    public function __construct() {
		parent::__construct();

		//エラーメッセージ取得
		$this->validate = array(
			'authority_name' => array(
				'notEmpty'  => array(
									'rule' => array('notEmpty'),
									'last' => true,
									'required' => true,
									'allowEmpty' => false,
									'message' => __('Please be sure to input.')
								),
				'maxlength'  => array(
									'rule' => array('maxLength', 30),
									'message' => __('The input must be up to %s characters.', 30)
								),
				'duplicationAuthorityName'  => array(
									'rule' => array('_duplicationAuthorityName'),
									'message' => __d('authorities', 'Authority with the same name')
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
					NC_ALLOW_CREATING_COMMUNITY_ALL,
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
				'boolean'  => array(
					'rule' => array('boolean'),
					'last' => true,
					'required' => true,
					'message' => __('The input must be a boolean.')
				)
			),

			'allow_myportal_viewing_hierarchy' => array(
				'inList' => array(
					'rule' => array('inList', array(
						NC_AUTH_OTHER,
						NC_AUTH_MIN_GENERAL,
						NC_AUTH_MIN_MODERATE,
						NC_AUTH_MIN_CHIEF,
					), false),
					'allowEmpty' => true,
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
			)

	    );
	}

/**
 * 重複チェック
 *
 * @param   array    $data
 * @return  boolean
 */
	protected function _duplicationAuthorityName($data) {
		if(!empty($this->data['Authority']['id']))
			$data['id !='] = $this->data['Authority']['id'];

		$count = $this->find( 'count', array('conditions' => $data, 'recursive' => -1) );
		if($count != 0)
			return false;
		return true;
	}

	public function findAuthSelectHtml() {
		$conditions = array(
			'Authority.hierarchy >=' => NC_AUTH_MIN_GENERAL,
			'Authority.hierarchy <=' => NC_AUTH_CHIEF,
			'Authority.display_participants_editing' => _ON,		// 参加ルーム選択に出すものだけ取得
		);
		$order = array(
			'Authority.hierarchy' => "ASC",
			'Authority.id' => "ASC"
		);
		$params = array(
						'fields' => array(
							'Authority.id',
							'Authority.authority_name',
							'Authority.hierarchy'
						),
						'conditions' => $conditions,
						'order' => $order,
						'callbacks' => 'after'
						);

		return $this->_afterFind($this->find('all', $params));
	}

	protected function _afterFind($results) {
		$rets = array();
		$select_chief_arr = array();
		$select_moderate_arr = array();
		$select_general_arr = array();

		foreach ($results as $key => $val) {
			$hierarchy = $val['Authority']['hierarchy'];
			if($hierarchy >= NC_AUTH_MIN_ADMIN) {
				continue;
			} else if($hierarchy >= NC_AUTH_MIN_CHIEF) {
				$select_chief_arr[$val['Authority']['id']] = $val['Authority'];
			} else if($hierarchy >= NC_AUTH_MIN_MODERATE) {
				$select_moderate_arr[$val['Authority']['id']] = $val['Authority'];
			} else if($hierarchy >= NC_AUTH_MIN_GENERAL) {
				$select_general_arr[$val['Authority']['id']] = $val['Authority'];
			} else {
				continue;
			}
		}
		$rets[NC_AUTH_CHIEF] = $select_chief_arr;
		$rets[NC_AUTH_MODERATE] = $select_moderate_arr;
		$rets[NC_AUTH_GENERAL] = $select_general_arr;
		$rets[NC_AUTH_GUEST][NC_AUTH_GUEST_ID]['id'] = NC_AUTH_GUEST_ID;
		$rets[NC_AUTH_GUEST][NC_AUTH_GUEST_ID]['authority_name'] = 'Guest';
		$rets[NC_AUTH_GUEST][NC_AUTH_GUEST_ID]['hierarchy'] = NC_AUTH_GUEST;
		$rets[NC_AUTH_OTHER][NC_AUTH_OTHER_ID]['id'] = NC_AUTH_OTHER_ID;
		$rets[NC_AUTH_OTHER][NC_AUTH_OTHER_ID]['authority_name'] = 'Non members';
		$rets[NC_AUTH_OTHER][NC_AUTH_OTHER_ID]['hierarchy'] = NC_AUTH_OTHER;
		return $rets;
	}

/**
 * 参加者のみ取得する必要があるかどうかの確認
 * @param  integer       $authorityId
 * @param  Model Page    $page
 * @return integer
 * 			0:参加者のみ表示　
 * 			1:すべての会員表示（変更不可)
 * 			2:すべての会員表示（PageUserLinkにない会員は、default値と不参加のみ変更可）
 * 			3:すべての会員表示（変更可）
 * @since  v 3.0.0.0
 */
	public function getParticipantType($authorityId, $page) {
		if($page['Page']['space_type'] == NC_SPACE_TYPE_PRIVATE || $page['Page']['space_type'] == NC_SPACE_TYPE_MYPORTAL) {
			return 0;
		} else if($page['Page']['space_type'] == NC_SPACE_TYPE_PUBLIC) {
			return 3;
		}
		$authority = $this->findById($authorityId);
		if(!isset($authority['Authority'])) {
			return 0;
		}

		if($authority['Authority']['allow_new_participant']) {
			return 3;
		} else if($page['Community']['publication_range_flag'] == NC_PUBLICATION_RANGE_FLAG_ALL) {
			return 1;
		} else if($page['Community']['publication_range_flag'] == NC_PUBLICATION_RANGE_FLAG_LOGIN_USER) {
			return 2;
		}

		return 0;
	}

/**
 * 権限リスト取得
 * @param   void
 * @return  array
 * @since   v 3.0.0.0
 */
	public function findList() {
		return $this->find('list', array(
			'fields' => array('authority_name'),
			'order' => array('hierarchy' => 'DESC', 'id' => 'ASC')
		));
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