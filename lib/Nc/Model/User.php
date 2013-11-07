<?php
/**
 * Userモデル
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Model
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class User extends AppModel
{
	public $belongsTo = array(
		'Authority'    => array(
			//'foreignKey'    => '',
			'fields' => array('id', 'hierarchy', 'allow_creating_community', 'allow_new_participant', 'myportal_use_flag',
				 'allow_myportal_viewing_hierarchy', 'private_use_flag', 'display_participants_editing'),
		),
	);

/**
 * Behavior name
 *
 * @var array
 */
	public $actsAs = array(
		'TimeZone',
		'Auth' => array('joins' => false, 'afterFind' => false),
		'Upload' => array(
			'avatar' => array(
				'fileType' => 'image',
				'thumbnailSizes' => array(
					'original' => NC_UPLOAD_AVATAR_RESIZE_MODE,
                    'thumbnail' => NC_UPLOAD_AVATAR_THUMBNAIL_RESIZE_MODE
                ),
				'checkComponentAction'=>'User.UserDownload',
				'deleteOnUpdate' => true,
			),
		),
	);

	public $validate = array();

	public $emailTags = array();

	public $changedAuthorityId = null;

/**
 * バリデート前処理
 *
 * <pre>
 * validatesメソッドを実行すると自動で処理される
 * </pre>
 *
 * @param   array  オプション
 * @return  void
 * @since   v 3.0.0.0
 */
	public function beforeValidate($options = array())
	{
		/*
		 * エラーメッセージ設定
		 */
		if(!isset($options['items'])) {
			$UserItem = ClassRegistry::init('UserItem');
			$items = $UserItem->findList();
		} else {
			$items = $options['items'];
		}
		/*
		 * emailカラムセット
		 */
		$this->emailTags = array();
		foreach($items as $item) {
			if($item['UserItem']['tag_name'] == "" && ($item['UserItem']['type'] == 'email' || $item['UserItem']['type'] == 'mobile_email')) {
				$this->emailTags[] = $item['UserItem']['id'];
			}
		}

		foreach($items as $item) {
			if($item['UserItem']['tag_name'] == "" || $item['UserItem']['tag_name'] == "username") {
				continue;
			}
			if(!isset($item['UserItemLang']['name'])) {
				$name = $item['UserItem']['default_name'];
			} else {
				$name = $item['UserItemLang']['name'];
			}

			// 必須チェック
			if($item['UserItem']['required'] == _ON) {
				//if($item['UserItem']['tag_name'] != 'password' || empty($this->data[$this->alias]['id'])) {
					// パスワードで編集ならば必須としない
					// TODO:インストールでは、パスワードで編集であっても必須チェックをしなければならないため、一時コメントアウト。
					$this->validate[$item['UserItem']['tag_name']]['notEmpty'] = array(
						'rule' => array('notEmpty'),
						'last' => true,
						//'required' => true,
						'allowEmpty' => false,
						'message' => __('Please input %s.', $name),
					);
				//}
			}

			// 重複チェック
			if($item['UserItem']['allow_duplicate'] == _OFF) {
				if($item['UserItem']['type'] == 'email' || $item['UserItem']['type'] == 'mobile_email') {
					$this->validate[$item['UserItem']['tag_name']]['duplicateEmail'] = array(
						'rule' => array('duplicateEmail'),
						//'last' => false,
						'allowEmpty' => true,
						'message' => __('The contents of %s is already in use.', $name),
					);
				} else {
					$this->validate[$item['UserItem']['tag_name']]['duplicate'] = array(
						'rule' => array('duplicate'),
						//'last' => false,
						'allowEmpty' => true,
						'message' => __('The contents of %s is already in use.', $name),
					);
				}
			}

			// minlengthチェック
			if($item['UserItem']['minlength'] != '0') {
				$this->validate[$item['UserItem']['tag_name']]['minlength'] = array(
					'rule' => array('minLength', $item['UserItem']['minlength']),
					//'last' => false,
					'allowEmpty' => true,
					'message' => __('The input must be at least %s characters.', $item['UserItem']['minlength']),
				);
			}

			// maxlengthチェック
			if($item['UserItem']['maxlength'] != '0') {
				$this->validate[$item['UserItem']['tag_name']]['maxLength'] = array(
					'rule' => array('maxLength', $item['UserItem']['maxlength']),
					//'last' => true,
					//'required' => true,
					'allowEmpty' => true,
					'message' => __('The input must be up to %s characters.', $item['UserItem']['maxlength']),
				);
			}

			// emailチェック
			if($item['UserItem']['type'] == 'email' || $item['UserItem']['type'] == 'mobile_email') {
				$this->validate[$item['UserItem']['tag_name']]['email'] = array(
					'rule' => array('email'),
					//'last' => true,
					//'required' => true,
					'allowEmpty' => true,
					'message' => __('Unauthorized pattern for %s.',$name),
				);
			}

			// 正規表現チェック
			if($item['UserItem']['regexp'] != '') {
				$this->validate[$item['UserItem']['tag_name']]['custom'] = array(
					'rule' => array('custom', $item['UserItem']['regexp']),
					//'last' => true,
					//'required' => true,
					'allowEmpty' => true,
					'message' => __('Unauthorized pattern for %s.', $name),
				);
			}

			// permalinkチェック
			if($item['UserItem']['tag_name'] == 'permalink') {
				$this->validate[$item['UserItem']['tag_name']]['invalidPermalink'] = array(
					'rule' => array('invalidPermalink'),
					'last' => true,
					'message' => __('It contains an invalid string.')
				);
			}

			if($item['UserItem']['tag_name'] == "avatar") {
				$required = false;
				if($item['UserItem']['required'] == _ON) {
					$required = true;
				}
				$this->validate[$item['UserItem']['tag_name']]['isValidExtension'] = array(
					'rule' => array('isValidExtension', null, $required),
					'last' => true
				);
				$this->validate[$item['UserItem']['tag_name']]['isBelowMaxSize'] = array(
					'rule' => array('isBelowMaxSize', null, $required),
					'last' => true
				);
			}
		}

		return parent::beforeValidate($options);
	}

/**
 * 保存前処理
 *
 * <pre>
 * ・権限が変更されたかどうかをセット
 * ・パスワードhash化
 * </pre>
 * @param   array  $options
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function beforeSave($options = array()) {
		$this->changedAuthorityId = null;
		$user = $this->find('first', array('fields' => array('myportal_page_id', 'private_page_id', 'authority_id'), 'conditions' => array($this->primaryKey => $this->id), 'recursive' => -1));

		if (isset($user[$this->alias]['authority_id']) && isset($this->data[$this->alias]['authority_id'])){
			if($this->data[$this->alias]['authority_id'] != $user[$this->alias]['authority_id']) {
				$this->changedAuthorityId = array(
					'pre_authority_id' => $user[$this->alias]['authority_id'],
					'authority_id' => $this->data[$this->alias]['authority_id'],
					'myportal_page_id' => $user[$this->alias]['myportal_page_id'],
					'private_page_id' => $user[$this->alias]['private_page_id'],
				);
			}
		}
		// パスワードhash化
		if(isset($this->data[$this->alias]['password'])) {
			$this->data[$this->alias]['password'] = AuthComponent::password($this->data[$this->alias]['password']);
		}
		return true;
	}

/**
 * 登録後処理
 * 		マイポータル、プライベートスペースが使用可能かどうかが変更されている場合、
 *		それに応じてpagesテーブルのdisplay_flagを変更する(NC_DISPLAY_FLAG_ON or NC_DISPLAY_FLAG_DISABLE)
 * @param   boolean $created
 * @param   array   $options
 * @return  void
 * @since   v 3.0.0.0
 */
	public function afterSave($created, $options = array()) {
		if (!$created && count($this->changedAuthorityId) > 0) {
			$Authority = ClassRegistry::init('Authority');
			$Page = ClassRegistry::init('Page');

			$oldAuthorityId = $this->changedAuthorityId['pre_authority_id'];
			$newAuthorityId = $this->changedAuthorityId['authority_id'];
			$myportalPageId = $this->changedAuthorityId['myportal_page_id'];
			$privatePageId = $this->changedAuthorityId['private_page_id'];
			$oldAuthority = $Authority->find('first', array('fields' => array('myportal_use_flag', 'private_use_flag'), 'conditions' => array($Authority->primaryKey => $oldAuthorityId), 'recursive' => -1));
			$newAuthority = $Authority->find('first', array('fields' => array('myportal_use_flag', 'private_use_flag'), 'conditions' => array($Authority->primaryKey => $newAuthorityId), 'recursive' => -1));
			if($oldAuthority['Authority']['myportal_use_flag'] != $newAuthority['Authority']['myportal_use_flag']) {
				// update
				$fields = array($Page->alias.'.display_flag'=> ($newAuthority['Authority']['myportal_use_flag']) ? NC_DISPLAY_FLAG_ON : NC_DISPLAY_FLAG_DISABLE);
				$conditions = array(
					$Page->alias.".room_id" => $myportalPageId,
				);
				$Page->updateAll($fields, $conditions);
			}
			if($oldAuthority['Authority']['private_use_flag'] != $newAuthority['Authority']['private_use_flag']) {
				// update
				$fields = array($Page->alias.'.display_flag'=> ($newAuthority['Authority']['private_use_flag']) ? NC_DISPLAY_FLAG_ON : NC_DISPLAY_FLAG_DISABLE);
				$conditions = array(
					$Page->alias.".room_id" => $privatePageId,
				);
				$Page->updateAll($fields, $conditions);
			}
		}
	}

/**
 * 重複チェック
 *
 * @param   array    $data
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function duplicate($data) {
		if(!empty($this->data[$this->alias]['id']))
			$data['id !='] = $this->data[$this->alias]['id'];

		$count = $this->find( 'count', array('conditions' => $data, 'recursive' => -1) );
		if($count != 0)
			return false;
		return true;
    }

/**
 * 重複チェック(Email)
 *
 * @param   array    $check
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function duplicateEmail($data) {
		$values = array_values($data);

		$conditions['or'] = array(
				array('User.email' => $values[0]),
				array('User.mobile_email' => $values[0])
		);
		if(!empty($this->data[$this->alias]['id']))
			$conditions['id !='] = $this->data[$this->alias]['id'];

		$count = $this->find( 'count', array('conditions' => $conditions, 'recursive' => -1) );
		if($count != 0)
			return false;

		// $this->emailTagsからuser_item_linksテーブルのチェック
		if(count($this->emailTags) > 0) {
			$UserItemLink = ClassRegistry::init('UserItemLink');
			$conditions = array(
				'user_item_id' => $this->emailTags,
				'content' => $values[0],
			);
			if(!empty($this->data[$this->alias]['id']))
				$conditions['user_id !='] = $this->data[$this->alias]['id'];
			$count = $UserItemLink->find( 'count', array('conditions' => $conditions, 'recursive' => -1) );
			if($count != 0)
				return false;
		}

		return true;
    }

/**
 * リンク識別子に不正な文字列がないかのバリデータ
 *
 * @param  array     $data
 * @return boolean
 * @since   v 3.0.0.0
 */
	public function invalidPermalink($data) {
		if($data['permalink'] == '') {
			return true;
		}
		$permalink = $data['permalink'];
		if(preg_match(NC_PERMALINK_PROHIBITION, $permalink)) {
			return false;
		}

		$mypotal_prefix = NC_SPACE_MYPORTAL_PREFIX;
		if($mypotal_prefix != '') {
			$chk_permalink = $mypotal_prefix.'/'.$permalink;
		} else {
			$chk_permalink = $permalink;
		}
		if(preg_match(NC_PERMALINK_PROHIBITION_DIR_PATTERN, $chk_permalink.'/')) {
			return false;
		}

		$private_prefix = NC_SPACE_PRIVATE_PREFIX;
		if($private_prefix != '') {
			$chk_permalink = $private_prefix.'/'.$permalink;
		} else {
			$chk_permalink = $permalink;
		}
		if(preg_match(NC_PERMALINK_PROHIBITION_DIR_PATTERN, $chk_permalink.'/')) {
			return false;
		}

		return true;
    }

/**
 * 最終ログイン日時更新
 * @param   array $user
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function updateLastLogin($user) {
		$this->create();
		$this->id = $user[$this->alias]['id'];
		$user[$this->alias]['previous_login'] = $user[$this->alias]['last_login'];
		$user[$this->alias]['last_login'] = $this->nowDate();
		$this->set($user);
		$fields = array('last_login', 'previous_login');

		$ret = $this->save($user, true, $fields);
		if(!$ret) {
			return false;
		}
		return true;
	}

/**
 *
 * マイポータル、マイルームのページ情報から、その会員情報を取得する。
 * マイポータル、マイルーム以外ならば'' エラーならばfalse
 * @param   array $page
 * @param   array $loginUser
 * @return  mixed ''|false|array $user
 * @since   v 3.0.0.0
 */
	public function currentUser($page, $loginUser = null) {
		$user = array();
		if($page['Page']['space_type'] != NC_SPACE_TYPE_MYPORTAL && $page['Page']['space_type'] != NC_SPACE_TYPE_PRIVATE) {
			return '';
		}

		$buf_permalink_arr = explode('/', $page['Page']['permalink']);
		if(!isset($loginUser['permalink']) || $buf_permalink_arr[0] != $loginUser['permalink']) {
			$conditions = array('User.permalink' => $buf_permalink_arr[0]);
			$user = $this->find( 'first', array(
				'conditions' => $conditions
			) );
			if(isset($user['Authority'])) {
				// 権限関連をAdd
				$user['User'] = array_merge($user['User'], $user['Authority']);
			}
		} else {
			$user['User'] = $loginUser;
		}
		if(!isset($user['User']))
			return false;

		return $user;
	}

/**
 * 参加者情報取得
 * サブグループの場合は、親で参加している会員のみ取得
 *
 * @param   Model Page $page
 * @param   integer  $participantType
 * 						0:参加者のみ表示　
 * 						1:すべての会員表示（PageUserLinkにない会員は、default値と不参加のみ変更可）
 * 						2:すべての会員表示（すべての権限へ変更可）
 * 						null : 親ルームのデータを取得しない(会員参加登録時使用)
 * @param   array    $conditions
 * @param   array    $joins
 * @param   integer  $startPage
 * @param   integer  $limit
 * @param   string $sortname
 * @param   string   $sortorder
 * @return  array array($total, $pages_users_link)
 * @since   v 3.0.0.0
 */
	public function findParticipant($page, $participantType = NC_PARTICIPANT_TYPE_DEFAULT_ENABLED, $conditions = array(), $joins = array(), $startPage = 1, $limit= 30, $sortname= 'chief', $sortorder = 'DESC') {

		$roomId = $page['Page']['room_id'];
		$rootId = $page['Page']['root_id'];
		$fields = array(
			'PageUserLink.id',
			'PageUserLink.user_id',
			'PageUserLink.authority_id',
			'Page.space_type',
			'Page.root_id',
			'User.id',
			'User.handle',
			'User.authority_id',
			'PageAuthority.id',
			'PageAuthority.hierarchy',
			'Authority.id',
			'Authority.display_participants_editing',
			'Authority.hierarchy',
			'Community.publication_range_flag',
			'Community.participate_force_all_users',
			'Community.participate_flag',
		);
		$type = ($participantType == NC_DISPLAY_FLAG_ENTRY_USERS  || is_null($participantType)) ? "INNER" : "LEFT";
		$joins[] = array(
			"type" => $type,
			"table" => "page_user_links",
			"alias" => "PageUserLink",
			"conditions" => "`User`.`id`=`PageUserLink`.`user_id`".
			" AND `PageUserLink`.`room_id` =".intval($roomId)
		);
		$joins[] = array(
			"type" => $type,
			"table" => "pages",
			"alias" => "Page",
			"conditions" => "`Page`.`id`=`PageUserLink`.`room_id`"
		);
		$joins[] = array(
			"type" => "LEFT",
			"table" => "authorities",
			"alias" => "PageAuthority",
			"conditions" => "`PageAuthority`.`id`=`PageUserLink`.`authority_id`"
		);
		$joins[] = array(
			"type" => "INNER",
			"table" => "authorities",
			"alias" => "Authority",
			"conditions" => "`Authority`.`id`=`User`.`authority_id`"
		);

		$joins[] = array(
			"type" => "LEFT",
			"table" => "communities",
			"alias" => "Community",
			"conditions" => array('Community.room_id' => $rootId),
		);
		/*$joins[] = array(
			"type" => "LEFT",
			"table" => "pages",
			"alias" => "Page",
			"conditions" => "`Page`.`id`=`PageUserLink`.`room_id`"
		);*/

		if(!is_null($participantType) && $page['Page']['thread_num'] > 1) {
		//if($page['Page']['id'] != $page['Page']['room_id'] && $page['Page']['thread_num'] > 1) {
			// 親ルームが存在するならば、親ルームの参加者のみ表示
			$fields[] = 'PageUserLinkParent.authority_id';
			$fields[] = 'AuthorityParent.id';
			$fields[] = 'AuthorityParent.hierarchy';

			$Page = ClassRegistry::init('Page');
			$parentPage = $Page->findById($page['Page']['parent_id']);
			$parentRoomId = $parentPage['Page']['room_id'];
			$type = ($participantType == NC_DISPLAY_FLAG_ENTRY_USERS) ? "INNER" : "LEFT";
			$joins[] = array(
				"type" => "LEFT",
				"table" => "page_user_links",
				"alias" => "PageUserLinkParent",
				"conditions" => "`User`.`id`=`PageUserLinkParent`.`user_id`".
				" AND `PageUserLinkParent`.`room_id` =".intval($parentRoomId)
			);
			$joins[] = array(
				"type" => "LEFT",
				"table" => "authorities",
				"alias" => "AuthorityParent",
				"conditions" => "`AuthorityParent`.`id`=`PageUserLinkParent`.`authority_id`"
			);
		}

		if(empty($conditions)) {
			$conditions = array();
		}

		$total = $this->find('count', array(
			'fields' => 'COUNT(*) as count',
			'joins' => $joins,
			'conditions' => $conditions,
			'recursive' => -1
		));
		if($total == 0) {
			return array(0, array() );
		}

		if($sortname == 'chief') {
			$order = array(
				'PageAuthority.hierarchy' => $sortorder,
				'Authority.hierarchy' => $sortorder,
				'User.handle' => $sortorder,
				'User.id' => $sortorder
			);
			if(isset($parentRoomId)) {
				$order = array(
					'PageAuthority.hierarchy' => $sortorder,
					'AuthorityParent.hierarchy' => $sortorder,
					'Authority.hierarchy' => $sortorder,
					'User.handle' => 'ASC',
					'User.id' => 'ASC'
				);
			} else {
				$order = array(
					'PageAuthority.hierarchy' => $sortorder,
					'Authority.hierarchy' => $sortorder,
					'User.handle' => 'ASC',
					'User.id' => 'ASC'
				);
			}
		} else if(!empty($sortname)) {
			$order = array(
				'User.'.$sortname => $sortorder,
				'User.id' => $sortorder
			);
		}

		$params = array(
			'fields' => $fields,
			'joins' => $joins,
			'conditions' => $conditions,
			'limit' => $limit,
			'page' => $startPage,
			'recursive' => -1,
			'order' => $order
		);
		$users = $this->find('all', $params);
		foreach($users as $key => $user) {
			$user['Page'] = $page['Page'];
			$ret = $this->getDefaultAuthority($user);
			if(!isset($users[$key]['PageUserLinkParent']['authority_id']) && !isset($users[$key]['PageUserLink']['authority_id'])) {
				$users[$key]['PageAuthority']['id'] = $ret['id'];
				$users[$key]['PageAuthority']['hierarchy'] = $ret['hierarchy'];
			}
			if(!isset($users[$key]['PageUserLinkParent']['authority_id'])) {
				$users[$key]['AuthorityParent']['id'] = $ret['id'];
				$users[$key]['AuthorityParent']['hierarchy'] = $ret['hierarchy'];
			}
		}
		return array($total, $users);
	}

/**
 * ContentIdからメール送信先email一覧取得
 *
 * @param integer					$contentId
 * @param integer					$moreThanHierarchy(モデレーター以上、主担以上等)
 *
 * 									$pageId指定ありの場合、ルーム権限
 * 									$pageId指定なしの場合、会員権限
 * @param array						$conditions
 * @param array						$order
 *
 * @return  boolean|array ('email' => Array $email, 'mobileEmail' => Array $mobileEmail)
 * @since   v 3.0.0.0
*/
	public function getSendMails($contentId = null, $moreThanHierarchy = null, $conditions = array(), $order = array()) {
		$loginUser = Configure::read(NC_SYSTEM_KEY.'.user');
		$userId = $loginUser['id'];
		//$emailRes = array('email' => array(), 'mobileEmail' => array());
		$Content = ClassRegistry::init('Content');
		$content = $Content->findById($contentId);

		if (!isset($content['Content'])) {
			return false;
		}

		$emailRes = $this->getSendMailsByPageId($content['Content']['room_id'], $moreThanHierarchy, $conditions, $order);

		// コンテンツ元IDからショートカットを取得
		$params = array(
			'fields' => array('Content.id', 'Content.room_id', 'Content.shortcut_type'),
			'conditions' => array(
				'Content.master_id' => $contentId,
				//'Content.id !=' => $contentId,
				'Content.shortcut_type' => NC_SHORTCUT_TYPE_SHOW_AUTH,
			)
		);
		$shortcutContents = $Content->find('all', $params);
		if(count($shortcutContents) > 0) {
			// 権限を付与したショートカットが配置してあるルームもメール配信対象とする。
			foreach($shortcutContents as $shortcutContent) {
				if($shortcutContent['Content']['shortcut_type'] == NC_SHORTCUT_TYPE_SHOW_ONLY) {
					// 閲覧のみ
					if($moreThanHierarchy != NC_AUTH_GUEST) {
						continue;
					}
					// 配置ルームのゲスト以上にメール送信
					$Block = ClassRegistry::init('Block');
					$blockParams = array(
						'fields' => array('Page.room_id'),
						'conditions' => array(
							'Block.content_id' => $shortcutContent['Content']['id']
						),
						'joins' => array(
							array("type" => "LEFT",
								"table" => "pages",
								"alias" => "Page",
								"conditions" => "`Block`.`page_id`=`Page`.`id`"
							),
						)
					);
					$shortcutBlock = $Block->find('first', $blockParams);
					if(isset($shortcutBlock['Page'])) {
						$masterEmailRes = $this->getSendMailsByPageId($shortcutBlock['Page']['room_id'], $moreThanHierarchy, $conditions, $order);
					}
				} else {
					$masterEmailRes = $this->getSendMailsByPageId($shortcutContent['Content']['room_id'], $moreThanHierarchy, $conditions, $order);
				}
				if(count($masterEmailRes['email']) > 0) {
					$emailRes['email'] = array_merge($emailRes['email'], $masterEmailRes['email']);
				}
				if(count($masterEmailRes['mobileEmail']) > 0) {
					$emailRes['mobileEmail'] = array_merge($emailRes['mobileEmail'], $masterEmailRes['mobileEmail']);
				}
			}
		}
		return $emailRes;
	}

/**
 * PageIdからメール送信先email一覧取得
 *
 * <pre>
 * 	contentIdに結びつけれない場合、ページIDからメールを送信する。基本的にgetSendMailsを用いる。
 * </pre>
 *
 * @param integer					$pageId
 * @param integer					$moreThanHierarchy(モデレーター以上、主担以上等)
 *
 * 									$pageId指定ありの場合、ルーム権限
 * 									$pageId指定なしの場合、会員権限
 * @param array						$conditions
 * @param array						$order
 *
 * @return  boolean|array ('email' => Array $email, 'mobileEmail' => Array $mobileEmail)
 * @since   v 3.0.0.0
 */
	public function getSendMailsByPageId($pageId = null, $moreThanHierarchy = null, $conditions = array(), $order = array()) {
		// TODO:現状Userテーブルのみから取得　今後UserItemLinkテーブルからも取得できるように修正予定
		$Page = ClassRegistry::init('Page');
		$UserItem = ClassRegistry::init('UserItem');
		$UserItemLink = ClassRegistry::init('UserItemLink');

		$loginUser = Configure::read(NC_SYSTEM_KEY.'.user');
		$userId = $loginUser['id'];
		$emailRes = array('email' => array(), 'mobileEmail' => array());

		$page_params = array(
			'fields' => array('Page.*', 'Community.publication_range_flag', 'Community.participate_force_all_users'),
			'joins' => array(
				array("type" => "LEFT",
					"table" => "communities",
					"alias" => "Community",
					"conditions" => "`Community`.`room_id`=`Page`.`room_id`"
				),
			),
			'conditions' => array('Page.id' => $pageId)
		);
		$page = $Page->find('first', $page_params);
		if (!isset($page['Page'])) {
			return false;
		}
		$defaultEntryHierarchy = $Page->getDefaultHierarchy($page);

		if(!empty($pageId)) {
			if(($page['Page']['space_type'] == NC_SPACE_TYPE_PUBLIC ||
				($page['Page']['space_type'] == NC_SPACE_TYPE_GROUP && $page['Community']['publication_range_flag'] != NC_PUBLICATION_RANGE_FLAG_ONLY_USER)) &&
				$moreThanHierarchy <= NC_AUTH_GENERAL
			) {
				$type = 'LEFT';
			} else {
				$type = 'INNER';
			}
			$isDefaultEntry = false;
			if($page['Community']['publication_range_flag'] != NC_PUBLICATION_RANGE_FLAG_ONLY_USER &&
				$page['Community']['participate_force_all_users'] == _ON) {
				$isDefaultEntry = true;
			}

			$params = array(
				'fields' => array('User.id', 'User.email', 'User.mobile_email', 'PageUserLink.authority_id', 'PageAuthority.hierarchy'),	// 'PageUserLink.authority_id',
				'joins' => array(
					array("type" => $type,
						"table" => "page_user_links",
						"alias" => "PageUserLink",
						"conditions" => "`User`.`id`=`PageUserLink`.`user_id`".
						" AND `PageUserLink`.`room_id` =".intval($pageId)
					),
					array("type" => $type,
						"table" => "authorities",
						"alias" => "PageAuthority",
						"conditions" => "`PageAuthority`.`id`=`PageUserLink`.`authority_id`"
					),
				),
				'conditions' => $conditions,
				'order' => $order,
				'recursive' => -1
			);
		} else {
			// ルームID指定なし
			// User.authority_idのみでメールを送信
			if($moreThanHierarchy != null) {
				$joins =  array(
					array(
						"type" => "INNER",
						"table" => "authorities",
						"alias" => "Authority",
						"conditions" => array(
							"`Authority`.`id`=`User`.`authority_id`",
							"`Authority`.`authority_id` >= " => $moreThanHierarchy
						)
					)
				);;
			} else {
				$joins =  array(
					array(
						"type" => "INNER",
						"table" => "authorities",
						"alias" => "Authority",
						"conditions" => "`Authority`.`id`=`User`.`authority_id`"
					)
				);
			}
			$params = array(
				'fields' => array('User.id', 'User.email', 'User.mobile_email', 'Authority.hierarchy'),
				'joins' => $joins,
				'conditions' => $conditions,
				'order' => $order,
				'recursive' => -1
			);
		}
		$users = $this->find('all', $params);

		if (count($users) == 0) {
			return $emailRes;
		}

		// メールを受信可否、受け取るかどうかの設定を取得
		$userItems = $UserItem->find('list', array(
			'fields' => array('id', 'allow_email_reception_flag'),
			'conditions' => array('tag_name' => array('email', 'mobile_email'))
		));
		$itemIds = array_keys($userItems);


		foreach($users as $user) {
			if(isset($user['PageAuthority']) && $user['PageUserLink']['authority_id'] === (string) NC_AUTH_OTHER_ID) {
				continue;
			}
			if(isset($user['PageAuthority']) && $user['PageAuthority']['hierarchy'] === null) {
				if(!$isDefaultEntry) {
					continue;
				}
				$user['PageAuthority']['hierarchy'] = $defaultEntryHierarchy;
			}
			if($moreThanHierarchy == null || (isset($user['PageAuthority']) && $user['PageAuthority']['hierarchy'] >= $moreThanHierarchy)) {
				$userItemLinks = $UserItemLink->find('list', array(
					'fields' => array('user_item_id', 'email_reception_flag'),
					'conditions' => array('user_id' => $user['User']['id'],'user_item_id' => $itemIds)
				));
				if(!empty($user['User']['email']) && (empty($userItems[NC_ITEM_ID_EMAIL]) || $userItemLinks[NC_ITEM_ID_EMAIL] == _ON)) {
					$emailRes['email'][$user['User']['email']] = $user['User']['email'] ;
				}
				if(!empty($user['User']['mobile_email']) && (empty($userItems[NC_ITEM_ID_MOBILE_EMAIL]) || $userItemLinks[NC_ITEM_ID_MOBILE_EMAIL] == _ON)) {
					$emailRes['mobileEmail'][$user['User']['mobile_email']] = $user['User']['mobile_email'] ;
				}
			}
		}

		return $emailRes;
	}

/*
 * 絞り込み検索用の条件取得
 *
 * <pre>
 * ・会員管理が編集可能ならば、すべてから検索可能
 * ・個人情報管理で閲覧可能なものを検索対象にする。
 *  </pre>
 * @param   Object Request $request
 * @param   integer $adminHierarchy
 * @param   array   $itemAuthorityLinks
 * @return  array (array conditions, array joins)
 * @since   v 3.0.0.0
 */
	public function getRefineSearch($request, $adminHierarchy = null, $itemAuthorityLinks = null) {
		$lang = Configure::read(NC_CONFIG_KEY.'.'.'language');
		$loginUser = Configure::read(NC_SYSTEM_KEY.'.user');
		$Authority = ClassRegistry::init('Authority');
		$loginHierarchy = $loginUser['hierarchy'];

		$conditions = array();
		$joins = array();

		$joinsCount = 0;
		if(isset($request->data['User']) || isset($request->data['UserItemLink'])) {
			if($adminHierarchy < NC_AUTH_MIN_CHIEF && !isset($itemAuthorityLinks)) {
				// 会員管理が編集不可能(会員管理が編集可能ならば、すべてから検索可能)
				$UserItemAuthorityLink = ClassRegistry::init('UserItemAuthorityLink');
				$itemAuthorityLinks = $UserItemAuthorityLink->findList();

				$authorities = $Authority->find('list', array('fields' => array('id', 'hierarchy')));
				$bufAuthorities = array();
				foreach($authorities as $key => $hierarchy) {
					$bufAuthorities[$Authority->getUserAuthorityId($hierarchy)][] = $key;
				}
			}

			$UserItem = ClassRegistry::init('UserItem');

			$allItems = $UserItem->find('all', array(
				'fields' => 'id, tag_name, type, allow_public_flag',
				'recursive' => -1
			));
			$items = array();
			$itemsAllowPublicFlags = array();
			$itemsTypes = array();

			$itemsTags = array();

			foreach($allItems as $bufItem) {
				$items[$bufItem['UserItem']['id']] = $bufItem['UserItem']['tag_name'];
				$itemsAllowPublicFlags[$bufItem['UserItem']['id']] = $bufItem['UserItem']['allow_public_flag'];
				$itemsTypes[$bufItem['UserItem']['id']] = $bufItem['UserItem']['type'];

				if($bufItem['UserItem']['tag_name'] != '') {
					$itemsTags[$bufItem['UserItem']['tag_name']] = $bufItem['UserItem']['id'];
				}
			}

			if(isset($request->data['User'])) {
				foreach($request->data['User'] as $dataKey => $value) {
					if($dataKey == 'communities') {
						// 参加コミュニティー
						$roomId = intval($value);

						if($roomId == 0) {
							// コミュニティーに参加していない会員
							// 自分のコミュニティーに参加している会員を取得
							if($adminHierarchy < NC_AUTH_MIN_CHIEF) {
								// 会員管理の主担より小さい場合、検索不可
								continue;
							}

							// 公開コミュニティー「すべての会員をデフォルトで参加させる」があれば、PageUserLink.authority_id = NC_AUTH_OTHER_IDの会員が検索対象
							// 公開コミュニティー「すべての会員をデフォルトで参加させる」がなければ、PageUserLink.authority_id がNULL OR NC_AUTH_OTHER_IDの会員すべて
							$Community = ClassRegistry::init('Community');
							$roomIds = $Community->find('list', array(
								'fields' => array('Community.id', 'Community.room_id'),
								'conditions' => array(
									'publication_range_flag' =>
										array(
											NC_PUBLICATION_RANGE_FLAG_LOGIN_USER,
											NC_PUBLICATION_RANGE_FLAG_ALL
										),
									'participate_force_all_users' => _ON,
								)
							));
							if(count($roomIds) > 0) {
								$isPublicCommunity = true;
							} else {
								$isPublicCommunity = false;
							}

							$PageUserLink = ClassRegistry::init('PageUserLink');
							if($isPublicCommunity) {
								// DISTINCT等で同じIDを省いていない。
								// 全部で不参加
								$userIds = array();
								foreach($roomIds as $roomId) {
									$params = array(
										'fields' => array('PageUserLink.user_id'),
										'conditions' => array(
											'PageUserLink.room_id' => $roomId,
											'PageUserLink.authority_id' => NC_AUTH_OTHER_ID
										),
									);
									if(count($userIds) > 0) {
										$params['conditions']['user_id'] = $userIds;
									}
									$userIds = $PageUserLink->find('list', $params);
									if(count($userIds) == 0) {
										break;
									}
								}

								if(count($userIds) > 0) {
									$params = array(
										'fields' => array('PageUserLink.user_id'),
										'conditions' => array(
											'PageUserLink.user_id' => $userIds,
											'PageUserLink.authority_id !=' => NC_AUTH_OTHER_ID
										),
										'joins' => array(array(
											"type" => "INNER",
											"table" => "communities",
											"alias" => "Community",
											"conditions" => "`PageUserLink`.`room_id`=`Community`.`room_id`"
										)),
									);
									$entryUserIds = $PageUserLink->find('list', $params);
									$userIds = array_diff($userIds, $entryUserIds);
								}
								$conditions['User.id'] = $userIds;
							} else {
								$roomIds = $Community->find('list', array(
									'fields' => array('Community.id', 'Community.room_id')
								));
								$results = $PageUserLink->find('all',
									array(
										'fields' => "PageUserLink.user_id, MAX(PageUserLink.authority_id) as max_number",
										'conditions' => array(
											'PageUserLink.room_id' => $roomIds
										),
										'group' => 'PageUserLink.user_id'
									)
								);
								$userIds = array();
								foreach($results as $result) {
									if(!empty($result[0]['max_number'])) {
										$userIds[] = $result['PageUserLink']['user_id'];
									}
								}
								$userIdsCount = count($userIds);
								if($userIdsCount == 1) {
									$userIdsValues = array_values($userIds);
									$conditions['User.id !='] = $userIdsValues[0];
								} else if($userIdsCount > 1) {
									$conditions['User.id NOT'] = $userIds;
								}
							}

						} else {
							$params = array(
								'fields' => array('Community.participate_force_all_users'),
								'joins' => array(array(
									"type" => "INNER",
									"table" => "communities",
									"alias" => "Community",
									"conditions" => "`Page`.`root_id`=`Community`.`room_id`"
								)),
								'conditions' => array(
									'Page.id' => $roomId
								),
							);
							$Page = ClassRegistry::init('Page');
							$room = $Page->find('first', $params);
							if(intval($room['Community']['participate_force_all_users']) == _OFF) {
								$joins[$joinsCount] = array(
									"type" => "INNER",
									"table" => "page_user_links",
									"alias" => 'PageUserLink',
									"conditions" => array(
										"PageUserLink.user_id = User.id",
										"PageUserLink.room_id" => $roomId,
										'PageUserLink.authority_id !=' => NC_AUTH_OTHER_ID,
									)
								);
								$joinsCount++;
							} else {
								$joins[$joinsCount] = array(
									"type" => "LEFT",
									"table" => "page_user_links",
									"alias" => 'PageUserLink',
									"conditions" => array(
										"PageUserLink.user_id = User.id",
										"PageUserLink.room_id" => $roomId
									)
								);
								$joinsCount++;
								$conditions['and']['or'] = array(
									'PageUserLink.authority_id !=' => NC_AUTH_OTHER_ID,
									'PageUserLink.authority_id IS NULL'
								);
							}
						}

						continue;
					} else if(!isset($itemsTags[$dataKey])) {
						continue;
					}
					$itemId = $itemsTags[$dataKey];
					if(!isset($items[$itemId]) || $items[$itemId] == '' || $value == '') {
						continue;
					}
					$key = $items[$itemId];

					if($itemsTypes[$itemId] == 'label' && is_array($value)) {
						// 更新日時等
						if((isset($value[0]) && $value[0] == '') && (isset($value[1]) && $value[1] == '')) {
							continue;
						} else {
							if(isset($value[0]) && intval($value[0]) >= 0) {
								$time = $this->_formatDate(intval($value[0]));
								$conditions['User.'.$key. ' <='] = $time;
							}
							if(isset($value[1]) && intval($value[1]) >= 0) {
								$time = $this->_formatDate(intval($value[1]));
								$conditions['User.'.$key. ' >='] = $time;
							}
						}
					} else if(is_array($value) || $value != '') {
						if($itemsTypes[$itemId] == 'checkbox' || $itemsTypes[$itemId] == 'radio' ||
							$itemsTypes[$itemId] == 'select') {
							$conditions['User.'.$key] = $value;
						} else {
							$conditions['User.'.$key.' LIKE'] = '%'.$value.'%';
						}
					}
					if(isset($itemAuthorityLinks) && count($itemAuthorityLinks) > 0) {
						$showUserAuthorityIds = array();
						foreach($itemAuthorityLinks as $userAuthorityId => $itemAuthorityLink) {
							$showLowerHierarchy = $itemAuthorityLink[$itemId]['show_lower_hierarchy'];
							if($loginHierarchy >= $showLowerHierarchy) {
								// 閲覧可
								$showUserAuthorityIds[] = $userAuthorityId;
							}
						}
						foreach($showUserAuthorityIds as $showUserAuthorityId) {
							if(isset($conditions['OR']['Authority.id'])) {
								$conditions['OR']['Authority.id'] = array_merge($conditions['OR']['Authority.id'], $bufAuthorities[$showUserAuthorityId]);
							} else {
								$conditions['OR']['Authority.id'] = $bufAuthorities[$showUserAuthorityId];
							}
						}
						$conditions['OR']['User.id'] = $loginUser['id'];
					}
				}
			}

			if(isset($request->data['UserItemLink'])) {
				foreach($request->data['UserItemLink'] as $itemId => $valueArr) {
					$value = $valueArr['content'];
					if(!isset($items[$itemId]) || ($items[$itemId] != '' && $items[$itemId] != 'username') || $value == '') {
						continue;
					}

					if(isset($itemAuthorityLinks) && count($itemAuthorityLinks) > 0) {
						$showUserAuthorityIds = array();
						foreach($itemAuthorityLinks as $userAuthorityId => $itemAuthorityLink) {
							$showLowerHierarchy = $itemAuthorityLink[$itemId]['show_lower_hierarchy'];
							if($loginHierarchy >= $showLowerHierarchy) {
								// 閲覧可
								$showUserAuthorityIds[] = $userAuthorityId;
							}
						}
						foreach($showUserAuthorityIds as $showUserAuthorityId) {
							if(isset($conditions['OR']['Authority.id'])) {
								$conditions['OR']['Authority.id'] = array_merge($conditions['OR']['Authority.id'], $bufAuthorities[$showUserAuthorityId]);
							} else {
								$conditions['OR']['Authority.id'] = $bufAuthorities[$showUserAuthorityId];
							}
						}
						$conditions['OR']['User.id'] = $loginUser['id'];
					}

					if(is_array($value) || $value != '') {
						$alias = "UserItemLink_".$itemId;
						if(is_array($value)) {
							$set_content = array();
							foreach($value as $v) {
								$set_content['AND'][] = array(
									$alias.".content LIKE" => '%'.$v.'|%'
								);
							}
							$joins[$joinsCount] = array(
								"type" => "INNER",
								"table" => "user_item_links",
								"alias" => $alias,
								"conditions" => array(
									$alias.".user_id = User.id",
									$alias.".user_item_id" => $itemId,
									$set_content
								)
							);
						} else {
							if($itemsTypes[$itemId] == 'checkbox' || $itemsTypes[$itemId] == 'radio' ||
								$itemsTypes[$itemId] == 'select') {
								$joinsKey = $alias.".content";
								$joinsValue = $value;
							} else {
								$joinsKey = $alias.".content LIKE";
								$joinsValue = '%'.$value.'%';
							}
							$joins[$joinsCount] = array(
								"type" => "INNER",
								"table" => "user_item_links",
								"alias" => $alias,
								"conditions" => array(
									$alias.".user_id = User.id",
									$alias.".user_item_id" => $itemId,
									$joinsKey => $joinsValue
								)
							);
						}
					}
				}
			}
		}

		return array($conditions, $joins);
	}

/**
 * 会員絞り込み　日付のフォーマット生成
 *
 * @param integer $value
 * @return  string
 * @since   v 3.0.0.0
 */
	protected function _formatDate($value) {
		$time = $this->nowDate("YmdHis");
		$time = mktime(intval(substr($time, 8, 2)), intval(substr($time, 10, 2)),
			intval(substr($time, 12, 2)), intval(substr($time, 4, 2)),
			intval(substr($time, 6, 2)) - intval($value), intval(substr($time, 0, 4)));
		$time = date("YmdHis", $time);
		return $time;
	}

/**
 * 会員削除
 *
 * @param integer|Model Users|Model User					$userId
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function deleteUser($userId) {
		$Page = ClassRegistry::init('Page');
		$UserItemLink = ClassRegistry::init('UserItemLink');
		$Passport = ClassRegistry::init('Passport');
		$PageUserLink = ClassRegistry::init('PageUserLink');
		$Upload = ClassRegistry::init('Upload');
		if(isset($userId[0]['User'])) {
			$deleteUsers = $userId;
		} else if(isset($userId['User'])) {
			$deleteUsers = array($userId);
		} else {
			$user = $this->findById($userId);
			if(!$user) {
				return true;
			}
			$deleteUsers = array($user);
		}
		$deleteUserIds = array();
		foreach($deleteUsers as $deleteUser) {
			if(($deleteUser['User']['myportal_page_id'] != 0 && !$Page->deletePage($deleteUser['User']['myportal_page_id'], true))
				|| ($deleteUser['User']['private_page_id'] != 0 && !$Page->deletePage($deleteUser['User']['private_page_id'], true))) {
				return false;
			}
			$deleteUserIds[] = $deleteUser['User']['id'];
		}
		$pageUserLinkConditions = array(
			"PageUserLink.user_id" => $deleteUserIds
		);
		$passportConditions = array(
			"Passport.user_id" => $deleteUserIds
		);
		$userItemLinkConditions = array(
			"UserItemLink.user_id" => $deleteUserIds
		);

		if(!$PageUserLink->deleteAll($pageUserLinkConditions) || !$Passport->deleteAll($passportConditions) ||
			!$UserItemLink->deleteAll($userItemLinkConditions)) {
			return false;
		}

		foreach($deleteUserIds as $deleteUserId) {
			// Callbackを呼ぶため、個々でdelete
			if(!$this->delete($deleteUserId)) {
				return false;
			}
		}

		// 退会ユーザーは、user_idを0で更新
		$fields = array(
			$Upload->alias.'.user_id' => 0,
		);
		$conditions = array(
			$Upload->alias.".user_id" => $deleteUserIds
		);
		if(!$Upload->updateAll($fields, $conditions)) {
			return false;
		}

		return true;
	}
}