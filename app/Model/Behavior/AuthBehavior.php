<?php
/**
 * Auth Behavior
 * ・Tableにcontent_idを付与することで、自動的に記事等の編集・削除権限までのテーブルをJoinしてデータを取得する。
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Model.Behavior
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */

class AuthBehavior extends ModelBehavior {
/**
 * Defaults
 *
 * @var array
 */
	protected $_defaults = array(
		'content_id' => 'content_id', // Content.idとJoinするカラム名
		'afterFind' => true,	// 自動的にafterFindを実行して記事に対するhierarchy(優先順序)を取得するかどうか。
		// joinするテーブル一覧(belongsTo)
		'joins' => array(
			'Content'      => array(
				'type' => 'INNER',
				'fields' => array('title', 'shortcut_type', 'room_id')
			),
			'PageUserLink' => array(
				'foreignKey'    => '',
				'fields' => false,
				'conditions' => array(
					'BlogPost.created_user_id = PageUserLink.user_id',
					'Content.room_id = PageUserLink.room_id'
				)
			),
			'PageAuthority'    => array(
				'foreignKey'    => '',
				'className' => 'Authority',
				'fields' => array('hierarchy'),
				'conditions' => array(
					'PageUserLink.authority_id = PageAuthority.id',
				)
			),
			// これ以下はAuthBehaviorで再取得されるため必須ではない。
			// SQL文の数を少なくするため記述しているが、表示速度によっては、JOINをやめる。
			'Page' => array(
				'foreignKey'    => '',
				'fields' => array('space_type', 'root_id'),
				'conditions' => array(
					'Content.room_id = Page.id'
				)
			),
			'Community' => array(
				'foreignKey'    => '',
				'type' => 'LEFT',
				'fields' => array('publication_range_flag'),
				'conditions' => "`Page`.`root_id`=`Community`.`room_id`"
			),
			'User' => array(
				'foreignKey'    => '',
				'type' => 'LEFT',
				'fields' => array('authority_id'),
				'conditions' => "`BlogPost`.`created_user_id`=`User`.`id`"
			),
			'Authority' => array(
				'foreignKey'    => '',
				'type' => 'LEFT',
				'fields' => array('hierarchy','display_participants_editing'),
				'conditions' => "`User`.`authority_id`=`Authority`.`id`"
			),
		),
	);

/**
 * Initiate Auth Behavior
 *
 * @param Model $Model instance of model
 * @param array $config array of configuration settings.
 * @return void
 */
	public function setup(Model $Model, $config = array()) {
		if (isset($config[0])) {
			$config['content_id'] = $config[0];
			unset($config[0]);
		}
		$settings = array_merge($this->_defaults, $config);

		$this->settings[$Model->alias] = $settings;
		if($settings['joins'] === false) {
			return;
		}

		$data = $Model->getAssociated('belongsTo');
		foreach($settings['joins'] as $key => $value) {
			if(!in_array($key, $data)) {
				if($key == 'Content' && $settings['content_id'] != 'content_id') {
					$value['foreignKey'] = $settings['content_id'];
				}
				$Model->bindModel( array('belongsTo' => array($key => $value)) , false);
			}
		}
	}

/**
 * afterFind
 *
 * @param  Model Object           $Model
 * @param  array   $results
 * @param  boolean $primary
 * @return array $results
 * @since   v 3.0.0.0
 */
	public function afterFind(Model $Model, $results, $primary = false) {
		if($this->settings[$Model->alias]['afterFind'] == false) {
			return $results;
		}
		foreach ($results as $key => $val) {
			if(!isset($val['Content'])) {
				// コンテンツがない場合、afterFindを実行しない。
				// 指定モデル内のメソッドすべてでafterFindが呼ばれるのを防ぐため。
				return $results;
			}
			$val = $this->setDefaultAuthority($Model, $val);
			$results[$key] = $val;
		}
		return $results;
	}

/**
 * joinするテーブル一覧(belongsTo)取得
 * @param  Model Object           $Model
 * @return array
 * @since  v 3.0.0.0
 */
	public function getJoins($Model) {
		$this->settings[$Model->alias]['joins'];
	}

/**
 * ページにおけるデフォルトの権限(authority_id, hierarchy)をAuthorityにセット
 * @param  Model Object           $Model
 * @param  Model
 * @param  integer $shortcutFlag
 * @param  stinrg $modelName default 'Page'
 * @return void
 * @since  v 3.0.0.0
 */
	public function setDefaultAuthority($Model, $val, $shortcutFlag = null, $modelName = 'Page') {
		if(isset($val['PageAuthority']['hierarchy'])) {
			return $val;
		}
		// Content取得
		/*if(!isset($val['Content']['shortcut_type']) && !isset($shortcutFlag) && isset($val[$Model->alias]['content_id'])) {
			App::uses('Content', 'Model');
			$Content = new Content();
			$params = array(
				'fields' => array(
					'Content.title',
					'Content.room_id',
					'Content.shortcut_type',
				),
				'conditions' => array('id' => $val[$Model->alias]['content_id']),
				'recursive' => -1,
			);
			$currentContent = $Content->find('first', $params);
			if(isset($val['Content'])) {
				$val['Content'] = array_merge($currentContent['Content'], $val['Content']);
			} else {
				$val['Content'] = $currentContent['Content'];
			}
		} else */
		if(isset($shortcutFlag)) {
			$val['Content']['shortcut_type'] = $shortcutFlag;
		}

		if(!isset($val['Content']['shortcut_type']) || $val['Content']['shortcut_type'] == NC_SHORTCUT_TYPE_SHOW_ONLY) {
			// ゲスト固定
			$val['PageAuthority']['id'] = NC_AUTH_GUEST_ID;
			$val['PageAuthority']['hierarchy'] = NC_AUTH_GUEST;
			return;
		}



		$ret = $this->getDefaultAuthority($Model, $val, $modelName);
		$val['PageAuthority']['id'] = $ret['id'];
		$val['PageAuthority']['hierarchy'] = $ret['hierarchy'];

		return $val;
	}

/**
 * ページにおけるデフォルトの権限(authority_id, hierarchy)を取得
 * 		公開ルームでPageUserLinkには存在しない会員の場合、Config.default_entry_XXXX_authority_idをベースの会員権限として、
 * 		閲覧会員の権限(Authority.hierarchy)が、ベースの会員権限内であれば、会員の権限(User.authority_id)で
 * 		閲覧を許可。
 * 			例：Authority id:4 hierarchy:150 一般, Authority id:7 hierarchy:151 一般2
 * 				会員A User authority_id: 7
 * 				公開ルーム Config.default_entry_XXXX_authority_id:4
 * 					=> 会員Aが公開ルームを一般2の権限で閲覧可能
 * @param  Model Object           $Model
 * @param  Model
 * @param  integer $shortcutFlag
 * @param  stinrg $modelName default 'Page'
 * @param  boolean ログイン会員の権限を取得
 * @return array('id' => integer authority_id,'hierarchy' => integer hierarchy)
 * @since  v 3.0.0.0
 */
	public function getDefaultAuthority($Model, $val, $shortcutFlag = null, $modelName = 'Page', $isLoginUser = false) {
		$loginUser = Configure::read(NC_SYSTEM_KEY.'.user');
		$loginUserId = isset($loginUser['id']) ? $loginUser['id'] : _OFF;
		if(isset($shortcutFlag)) {
			$val['Content']['shortcut_type'] = $shortcutFlag;
		}
		if(isset($val['PageUserLink']['authority_id']) && $val['PageUserLink']['authority_id'] == NC_AUTH_OTHER_ID) {
			return array(
				'id' => NC_AUTH_OTHER_ID,
				'hierarchy' => NC_AUTH_OTHER
			);
		}

		// Page取得
		if((!isset($val[$modelName]['space_type']) || !isset($val[$modelName]['root_id'])) && isset($val['Content']['room_id'])) {
			App::uses('Page', 'Model');
			$Page = new Page();
			$params = array(
				'fields' => array(
					'Page.space_type',
					'Page.root_id',
				),
				'conditions' => array('id' => $val['Content']['room_id']),
				'recursive' => -1,
			);
			$currentPage = $Page->find('first', $params);
			if(isset($val[$modelName])) {
				$val[$modelName] = array_merge($currentPage['Page'], $val[$modelName]);
			} else {
				$val[$modelName] = $currentPage['Page'];
			}
		}

		if(!isset($val[$modelName]['space_type']) || !isset($val[$modelName]['root_id'])) {
			// ゲスト固定
			return array(
				'id' => NC_AUTH_GUEST_ID,
				'hierarchy' => NC_AUTH_GUEST
			);
		}
		// Community取得
		if(!isset($val['Community']['publication_range_flag']) && $val[$modelName]['space_type'] == NC_SPACE_TYPE_GROUP && $val[$modelName]['root_id'] != 0) {
			App::uses('Community', 'Model');
			$Community = new Community();
			$params = array(
				'fields' => array(
					'Community.publication_range_flag'
				),
				'conditions' => array('room_id' => $val[$modelName]['root_id']),
				'recursive' => -1,
			);
			$currentCommunity = $Community->find('first', $params);
			if(isset($currentCommunity['Community'])) {
				if(isset($val['Community'])) {
					$val['Community'] = array_merge($currentCommunity['Community'], $val['Community']);
				} else {
					$val['Community'] = $currentCommunity['Community'];
				}
			}
		}
		// User取得
		if($isLoginUser) {
			$userId = $loginUserId;
		} else if(isset($val['PageUserLink']['user_id'])) {
			$userId = $val['PageUserLink']['user_id'];
		} else if(isset($val[$Model->alias]['user_id'])) {
			$userId = $val[$Model->alias]['user_id'];
		} else if($Model->alias != 'Page' && $Model->alias != 'Block' && $Model->alias != 'Content' && isset($val[$Model->alias]['created_user_id'])) {
			$userId = $val[$Model->alias]['created_user_id'];
		} else if($Model->alias == 'User' && isset($val[$Model->alias]['id'])) {
			$userId = $val[$Model->alias]['id'];
		}

		if(isset($val['User']['authority_id'])) {
			$bufAuthorityId = $val['User']['authority_id'];
		} else if(isset($val['Authority']['id'])) {
			$bufAuthorityId = $val['Authority']['id'];
		}
		if(!empty($userId) && $userId == $loginUserId) {
			$bufAuthorityId = $val['User']['authority_id'] = $loginUser['authority_id'];
			$val['Authority']['hierarchy'] = $loginUser['hierarchy'];
			$val['Authority']['display_participants_editing'] = $loginUser['display_participants_editing'];
		} else if((!isset($bufAuthorityId) || !isset($val['Authority']['hierarchy']) ||
			!isset($val['Authority']['display_participants_editing'])) && isset($userId)) {
			App::uses('User', 'Model');
			$User = new User();
			$currentUser = $User->find('first', array(
				'fields' => array(
					'User.authority_id', 'Authority.hierarchy','Authority.display_participants_editing'
				),
				'conditions' => array('User.id' => $userId),
			));
			if(isset($currentUser['User'])) {
				$val['User']['authority_id'] = $bufAuthorityId = $currentUser['User']['authority_id'];
				if(isset($val['Authority'])) {
					$val['Authority'] = array_merge($currentUser['Authority'], $val['Authority']);
				} else {
					$val['Authority'] = $currentUser['Authority'];
				}
			}
		}

		$authorityId = NC_AUTH_OTHER_ID;
		$hierarchy = NC_AUTH_OTHER;
		if($val[$modelName]['space_type'] == NC_SPACE_TYPE_PUBLIC) {
			$authorityId = Configure::read(NC_CONFIG_KEY.'.default_entry_public_authority_id');
			$hierarchy = Configure::read(NC_CONFIG_KEY.'.default_entry_public_hierarchy');
		} else if($val[$modelName]['space_type'] == NC_SPACE_TYPE_MYPORTAL) {
			$authorityId = Configure::read(NC_CONFIG_KEY.'.default_entry_myportal_authority_id');
			$hierarchy = Configure::read(NC_CONFIG_KEY.'.default_entry_myportal_hierarchy');
		} else if($val[$modelName]['space_type'] == NC_SPACE_TYPE_PRIVATE) {
			$authorityId = Configure::read(NC_CONFIG_KEY.'.default_entry_private_authority_id');
			$hierarchy = Configure::read(NC_CONFIG_KEY.'.default_entry_private_hierarchy');
		} else if(isset($val[$modelName]['root_id']) && $val[$modelName]['root_id'] == 0) {
			$authorityId = NC_AUTH_OTHER_ID;
			$hierarchy = NC_AUTH_OTHER;
		} else {
			if($val['Community']['publication_range_flag'] == NC_PUBLICATION_RANGE_FLAG_ONLY_USER ||
				(intval($loginUserId) == 0 && $val['Community']['publication_range_flag'] == NC_PUBLICATION_RANGE_FLAG_LOGIN_USER)) {
				$authorityId = NC_AUTH_OTHER_ID;
				$hierarchy = NC_AUTH_OTHER;
			} else {
				$authorityId = Configure::read(NC_CONFIG_KEY.'.default_entry_group_authority_id');
				$hierarchy = Configure::read(NC_CONFIG_KEY.'.default_entry_group_hierarchy');
			}
		}
		App::uses('Authority', 'Model');
		$Authority = new Authority();
		list($minHierarchy, $maxHierarchy) = $Authority->getHierarchyByUserAuthorityId($authorityId);
		if(isset($val['Authority']['display_participants_editing']) && $val['Authority']['display_participants_editing'] &&
			$val['Authority']['hierarchy'] >= $minHierarchy && $val['Authority']['hierarchy'] <= $maxHierarchy) {
			$authorityId = $bufAuthorityId;
			$hierarchy = $val['Authority']['hierarchy'];
		}
		return array(
			'id' => $authorityId,
			'hierarchy' => $hierarchy
		);
	}

/**
 * 会員ID、ページ情報からHierarchyを返す
 * @param  Model Object           $Model
 * @param  Model
 * @param  integer $shortcutFlag
 * @param  stinrg $modelName default 'Page'
 * @param  boolean ログイン会員の権限を取得
 * @return integer hierarchy
 * @since  v 3.0.0.0
 */
	public function getDefaultHierarchy($Model, $val, $shortcutFlag = null, $modelName = 'Page', $isLoginUser = false) {
		$authority = $this->getDefaultAuthority($Model, $val, $shortcutFlag, $modelName, $isLoginUser);
		return $authority['hierarchy'];
	}

/**
 * ページにおけるデフォルトの権限を取得
 * @param  Model Object           $Model
 * @param  Model
 * @param  integer $shortcutFlag
 * @param  stinrg $modelName default 'Page'
 * @param  boolean ログイン会員の権限を取得
 * @return integer authority_id
 * @since  v 3.0.0.0
 */
	public function getDefaultAuthorityId($Model, $val, $shortcutFlag = null, $modelName = 'Page', $isLoginUser = false) {
		$authority = $this->getDefaultAuthority($Model, $val, $shortcutFlag, $modelName, $isLoginUser);
		return $authority['id'];
	}
}
