<?php
/**
 * UserControllerクラス
 *
 * <pre>
 * 会員管理コントローラー
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       App.Controller
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class UserController extends UserAppController {

/**
 * Component name
 *
 * @var array
 */
	public $components = array('Security', 'User.UserCommon', 'CheckAuth' => array('allowAuth' => NC_AUTH_GUEST));

/**
 * 表示前処理
 * <pre>
 * Tokenチェック処理
 * </pre>
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function beforeFilter() {
		if($this->action == "search") {
			// 検索絞り込みは、ページ設定からも呼ばれるため、権限チェックを緩くして
			// ログインしているかどうかだけチェック
			$this->CheckAuth->allowAuth = null;
			$this->CheckAuth->allowUserAuth = NC_AUTH_GUEST;
		}

		parent::beforeFilter();

		if($this->action == "edit") {
			$this->Security->unlockedFields = array('language', 'activeLang', 'User.avatar');	// , 'User.avatarFile'
		}
		if($this->action == "edit" && (isset($this->request->data['PageUserLink']) || isset($this->request->data['User']['avatar']['file']))) {
			// back or avatar
			$this->Security->validatePost = false;
		} else if($this->action == "edit" && isset($this->request->data['uploadFlag'])) {
			$this->Security->validatePost = false;
		} else if($this->action == "index" || $this->action == "display_setting" || $this->action == "select_group" || $this->action == "select_auth") {
			$this->Security->validatePost = false;
			if(isset($this->request->data['isSearch']) && $this->request->data['isSearch']) {
				$this->Security->csrfUseOnce = false;
			}
		} else if($this->action == "detail") {
			$this->Security->csrfCheck = false;
			$this->Security->validatePost = false;
		}
	}

/**
 * 表示後処理
 * <pre>
 * 	セッションにセットしてあった言語を元に戻す。
 * </pre>
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function afterFilter()
	{
		parent::afterFilter();
		$preLang = $this->Session->read(NC_SYSTEM_KEY.'.user.pre_lang');
		if(isset($preLang)) {
			Configure::write(NC_CONFIG_KEY.'.'.'language', $preLang);
			$this->Session->write(NC_CONFIG_KEY.'.language', $preLang);
			$this->Session->delete(NC_SYSTEM_KEY.'.user.pre_lang');
		}

	}

/**
 * 会員一覧
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function index() {
		$this->set("items", $this->Item->findList('list', array(), array('ItemLang.name')));
		// 言語切替
		$this->_setLanguage('Elements/list');

		if ($this->request->is('post')) {
			if(isset($this->request->data['DelSingleUser'])) {
				// 削除リンク
				$this->request->data['DelUser'] = $this->request->data['DelSingleUser'];
			}
			if(isset($this->request->data['DelUser'])) {
				// 削除処理
				if($this->hierarchy < NC_AUTH_MIN_CHIEF) {
					$this->response->statusCode('403');
					$this->flash(__('Authority Error!  You do not have the privilege to access this page.'), '');
					return;
				}
				$deleteUserIds = array();
				$loginUser = $this->Auth->user();
				foreach($this->request->data['DelUser'] as $userId => $userOperation) {
					if(!isset($userOperation['delete']) || !$userOperation['delete']) {
						continue;
					}
					$deleteUserIds[] = $userId;
				}
				$deleteUsers = $this->User->find('all', array(
					'conditions' => array(
						'User.id' => $deleteUserIds,
						'Authority.hierarchy <=' => $loginUser['hierarchy']
					)
				));

				if(!$this->User->deleteUser($deleteUsers)) {
					throw new InternalErrorException(__('Failed to delete the database, (%s).', 'users'));
				}
			}

			$this->render('Elements/list');
		}
	}

/**
 * 会員一覧詳細部分表示(Grid表示)
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function detail() {
		$pageNum = intval($this->request->data['page']) == 0 ? 1 : intval($this->request->data['page']);
		$rp = intval($this->request->data['rp']);
		$sortname = isset($this->request->data['sortname']) ? $this->request->data['sortname'] : "Authority.hierarchy" ;
		$sortorder = ($this->request->data['sortorder'] == "asc" || $this->request->data['sortorder'] == "desc") ? $this->request->data['sortorder'] : "desc";

		$loginUser = $this->Auth->user();
		$this->set('loginHierarchy', $loginUser['hierarchy']);

		$activeLang = $this->_setLanguage();

		$fields = array(
			'User.*, UserItemLinkUsername.content, Authority.default_name, AuthorityLang.name, Authority.hierarchy, Authority.system_flag'
		);
		list($conditions, $joins) = $this->User->getRefineSearch($this->request, $this->hierarchy);
		$joins[] = 	array("table" => "authorities",
			"alias" => "Authority",
			"conditions" => "`User`.`authority_id`=`Authority`.`id`"
		);
		$joins[] = 	array(
			"type" => "LEFT",
			"table" => "authority_langs",
			"alias" => "AuthorityLang",
			"conditions" => array(
				"`AuthorityLang`.`authority_id`=`Authority`.`id`",
				"AuthorityLang.lang" => $activeLang,
			)
		);
		$alias = "UserItemLinkUsername";
		$joins[] = array(
			"type" => "LEFT",
			"table" => "user_item_links",
			"alias" => $alias,
			"conditions" => array(
				$alias.".user_id = User.id",
				$alias.".item_id" => NC_ITEM_ID_USERNAME,
				$alias.".lang" => $activeLang,
			)
		);
		$order = array(
			$sortname => $sortorder,
			'User.id' => 'asc'
		);

		/*
		 * データ取得
		 */
		$users = array();
		$total = $this->User->find('count', array(
			'fields' => 'COUNT(*) as count',
			'joins' => $joins,
			'conditions' => $conditions,
			'recursive' => -1
		));
		if($total != 0) {
			$params = array(
				'fields' => $fields,
				'conditions' => $conditions,
				'joins' => $joins,
				'order' => $order,
				'limit' => $rp,
				'page' => $pageNum,
				'recursive' => -1
			);
			$users = $this->User->find('all', $params);
			if($this->hierarchy < NC_AUTH_MIN_CHIEF) {
				$itemsAuthoritiesLinks = $this->ItemAuthorityLink->findList();
				$chk_arr = array(
					NC_ITEM_ID_HANDLE => 'handle',
					NC_ITEM_ID_USERNAME => 'username',
					NC_ITEM_ID_AUTHORITY_ID => 'authority_id',
					NC_ITEM_ID_IS_ACTIVE => 'active_flag',
					NC_ITEM_ID_CREATED =>'created',
					NC_ITEM_ID_LAST_LOGIN => 'last_login'
				);

				foreach($users as $key => $user) {
					if($user['User']['id'] == $loginUser['id']) {
						continue;
					}
					
					$chkBaseAuthorityId = $this->Authority->getUserAuthorityId($user['Authority']['hierarchy']);

					foreach($chk_arr as $itemId => $chk) {
						if($itemsAuthoritiesLinks[$chkBaseAuthorityId][$itemId]['show_lower_hierarchy'] > $loginUser['hierarchy']) {
							if($itemId == NC_ITEM_ID_USERNAME) {
								$users[$key]['UserItemLinkUsername']['content'] = '';
							} else if($itemId == NC_ITEM_ID_AUTHORITY_ID) {
								$users[$key]['AuthorityLang']['name'] = '';
							} else {
								$users[$key]['User'][$chk] = '';
							}
						}
					}
				}
			}
		}

		$this->set('page_num', $pageNum);
		$this->set('total', $total);
		$this->set('users', $users);
	}

/**
 * 検索画面表示
 * @param   void
 * @return  void
 * @since v 3.0.0.0
 */
	public function search() {
		$loginUser = $this->Auth->user();
		$userId = $loginUser['id'];

		include_once dirname(dirname(__FILE__)).'/Config/defines.inc.php';

		$items = $this->Item->findList();
		$this->set('items', $items);
		// 会員管理の管理者ならば、個人情報管理をみない。
		if($this->hierarchy < NC_AUTH_MIN_CHIEF) {
			$this->set('item_publics', $this->ItemAuthorityLink->findIsPublicForLoginUser());
		}
		$this->set('authorities', $this->Authority->findSelectList());
		$this->set('languages', $this->Language->findSelectList());

		// 参加コミュニティー
		if($this->hierarchy >= NC_AUTH_MIN_CHIEF) {
			// 会員管理のhierarchyが主担以上ならば、参加してないルームもセットし、すべてのルーム一覧を取得
			// TODO:すべてのコミュニティーの一覧の取得はコミュニティー数が増大すると重くなる。
			$userId = 'all';
		}
		$this->set('communities', $this->Page->findRoomList($userId, NC_SPACE_TYPE_GROUP));
	}

/**
 * 会員追加・編集
 * <pre>
 * 会員管理の編集は、会員管理が主担以上ならば許す。
 * </pre>
 * @param   integer $userId
 * @return  void
 * @since   v 3.0.0.0
 */
	public function edit($userId = null) {
		$loginUserId = $this->Auth->user('id');

		// 言語切替
		$this->_setLanguage();

		if($this->hierarchy < NC_AUTH_MIN_CHIEF) {
			$this->response->statusCode('403');
			$this->flash(__('Authority Error!  You do not have the privilege to access this page.'), '');
			return;
		}

		$user['User'] = array();
		$userItemLinks = array();
		$lang = Configure::read(NC_CONFIG_KEY.'.'.'language');
		if(!empty($userId)) {
			// 編集
			$user = $this->User->findById(intval($userId));
			if(!isset($user['User']) || ($loginUserId != $userId && $this->hierarchy < $user['Authority']['hierarchy'])) {
				$this->response->statusCode('403');
				$this->flash(__('Authority Error!  You do not have the privilege to access this page.'), '');
				return;
			}
		}

		// アバターアップロード
		if ($this->request->is('post') && !isset($this->request->data['User']['login_id'])) {
			$this->_uploadAvatar($user);
			return;
		}

		$items = $this->Item->findList();
		if ($this->request->is('post') && !isset($this->request->data['PageUserLink'])) {
			if(!isset($this->request->data['User'])) {
				throw new BadRequestException(__('Unauthorized request.<br />Please reload the page.'));
			}
			if(isset($user['User']['permalink'])) {
				$prePermalink = $user['User']['permalink'];
			}

			$user['User'] = array_merge($user['User'], $this->request->data['User']);
			if(isset($this->request->data['User']['password']) && $this->request->data['User']['password'] == '') {
				// パスワードが空ならば変更しない。
				unset($user['User']['password']);
			}

			// UserItemLink validate
			$ret = true;
			if(isset($this->request->data['UserItemLink'])) {
				$bufItems = array();
				foreach($items as $item) {
					$bufItems[$item['Item']['id']] = $item;
				}
				foreach($this->request->data['UserItemLink'] as $itemId => $userItemLink) {
					if(!isset($bufItems[$itemId])) {
						continue;
					}
					$this->request->data['UserItemLink'][$itemId]['user_id'] = empty($userId) ? 1 : $userId;
					$this->request->data['UserItemLink'][$itemId]['item_id'] = $itemId;
					if($bufItems[$itemId]['Item']['is_lang']) {
						$this->request->data['UserItemLink'][$itemId]['lang'] = $lang;
					} else {
						$this->request->data['UserItemLink'][$itemId]['lang'] = '';
					}
				}
				$ret = $this->UserItemLink->saveAll($this->request->data['UserItemLink'], array('items' => $items, 'validate' => 'only'));
			}

			$this->User->set($user);
			$fieldList = array('login_id', 'password', 'handle', 'authority_id', 'is_active', 'permalink', 'avatar', 'lang', 'timezone_offset', 'email', 'mobile_email');
			if($this->User->validates(array('items' => $items), $fieldList) && $ret) {
				$isAdd = empty($user['User']['id']) ? true : false;
				
				if(!empty($user['User']['id']) && empty($user['User']['avatar'])) {
					// Uploadデータ削除
					$user['User']['avatar']['remove'] = _ON;
				}

				if(!$this->User->save($user, false, $fieldList)) {
					throw new InternalErrorException(__('Failed to update the database, (%s).', 'users'));
				}
				$user['User']['id'] = $this->User->id;
				if (isset($user['User']['avatar']['remove'])) {
					$user['User']['avatar'] = null;
				}

				// UserItemLink Save
				if(isset($this->request->data['UserItemLink'])) {
					if(empty($userId)) {
						foreach($this->request->data['UserItemLink'] as $itemId => $userItemLink) {
							$this->request->data['UserItemLink'][$itemId]['user_id'] = $this->User->id;
						}
					}
					$ret = $this->UserItemLink->saveAll($this->request->data['UserItemLink'], array('validate' => false));
				}

				// マイポータル作成, マイルーム作成, ルーム参加
				if($isAdd) {
					$ret = $this->Page->createDefaultEntry($user);
					if($ret === false) {
						throw new InternalErrorException(__('Failed to register the database, (%s).', 'pages'));
					}
					$fields = array(
						'myportal_page_id' => $ret[0],
						'private_page_id' => $ret[1]
					);
					$conditions = array(
						"User.id" => $user['User']['id']
					);
					if(!$this->User->updateAll($fields, $conditions)) {
						throw new InternalErrorException(__('Failed to update the database, (%s).', 'users'));
					}
				}

				// Page.permalink更新
				if(!$isAdd && $prePermalink != $user['User']['permalink']) {
					if(!$this->Page->updPermalinks($user['User']['myportal_page_id'], $user['User']['permalink'])
						|| !$this->Page->updPermalinks($user['User']['private_page_id'], $user['User']['permalink'])) {
						throw new InternalErrorException(__('Failed to update the database, (%s).', 'pages'));
					}
				}

				$userId = $this->User->id;
				// メッセージ表示
				if($isAdd) {
					$this->Session->setFlash(__('Has been successfully registered.'));
				} else {
					$this->Session->setFlash(__('Has been successfully updated.'));
				}
				$this->set('is_add', $isAdd);
			}
		} else {
			// Itemテーブルからのvalidateをセットするため
			$this->User->validates();
		}
		if(isset($userId)) {
			$params = array(
				'conditions' => array(
					'UserItemLink.user_id' => $userId,	//$user['User']['id'],
					'UserItemLink.lang' => array("", $lang)
				)
			);
			$userItemLinks = $this->UserItemLink->find('all', $params);
			$bufUserItemLinks = array();
			foreach($userItemLinks as $userItemLink) {
				$bufUserItemLinks[$userItemLink['UserItemLink']['item_id']] = $userItemLink;
			}
			$userItemLinks = $bufUserItemLinks;
		}

		$userId = isset($userId) ? intval($userId) : 0;
		$this->set('id', $this->id.'-'.$userId);	// top idをuser_id単位に設定
		$this->set('user_id', $userId);
		$this->set('user', $user);
		$this->set('user_item_links', $userItemLinks);
		$this->set("items", $items);
		$this->set('authorities', $this->Authority->findSelectList());
		$this->set('languages', $this->Language->findSelectList());
	}

/**
 * アバターアップロード処理
 * @param   array User 
 * @return  void
 * @since   v 3.0.0.0
 */
	protected function _uploadAvatar($user = array()) {
		$userId = isset($user['User']['id']) ? intval($user['User']['id']) : 0;
		if(!empty($userId)) {
			$this->request->data['User']['id'] = $userId;
		}

		if($this->User->uploadFile($this->request->data)) {
			$this->set('avatar', $this->User->getUploadFileNames('avatar'));
			if(!empty($user['User']['id'])) {
				$this->Session->setFlash(__('Has been successfully updated.'));
			}
		}
		
		
		$this->set('id', $this->id.'-'.$userId);	// top idをuser_id単位に設定
		$this->set('name', 'User.avatar');
		$this->set('item', $this->Item->findList('first', array('Item.id' => NC_ITEM_ID_AVATAR)));
		$this->render('/Elements/avatar');
		return;
	}

/**
 * 参加ルーム選択
 * @param   integer
 * @return  void
 * @since   v 3.0.0.0
 */
	public function select_group($userId = null) {
		$loginUserId = $this->Auth->user('id');
		if(empty($userId)) {
			throw new BadRequestException(__('Unauthorized request.<br />Please reload the page.'));
		}
		$user = $this->User->findById(intval($userId));
		if(!isset($user['User']) || ($loginUserId != $userId && $this->hierarchy < $user['Authority']['hierarchy'])) {
			$this->response->statusCode('403');
			$this->flash(__('Authority Error!  You do not have the privilege to access this page.'), '');
			return;
		}

		// TODO:ルーム数が増大していくと、表示が重くなる。
		// limit:1001件等で取得し、1000件を超えた場合は、次ページのリンクを出す等といった
		// 対応が望ましいが対処しない。
		$addParams = array(
			'conditions' => array('Page.space_type' => array(NC_SPACE_TYPE_PUBLIC, NC_SPACE_TYPE_GROUP)),
			'order' => array(
				'Page.space_type' => "ASC",
				'Page.lang' => "ASC",
				'Page.root_id' => "ASC",
				'Page.thread_num' => "ASC",
				'Page.display_sequence' => "ASC"
			)
		);
		$options = array(
			'autoLang' => false
		);
		$rooms = $this->Page->findViewableRoom('all', 'all', $addParams, $options);

		// 子グループの親room_idを取得
		// 全ルーム一覧　子グループ　親ルームも選択
		// 参加させるルーム　親ルーム　子グループも選択
		$parentRoomList = array();
		foreach($rooms as $roomId => $room) {
			if($room['Page']['thread_num'] < 2) {
				continue;
			}
			if(isset($rooms[$room['Page']['parent_id']])) {
				$rooms[$roomId]['Page']['parent_room_id'] = $rooms[$room['Page']['parent_id']]['Page']['room_id'];
				$rooms[$roomId]['Page']['parent_page_name'] = $rooms[$room['Page']['parent_id']]['Page']['page_name'];
			} else {
				$parentRoomList[$roomId] = $room['Page']['parent_id'];
			}
		}
		if(count($parentRoomList) > 0) {
			$params = array(
				'fields' => array(
					'Page.id','Page.room_id',
				),
				'conditions' => array(
					'Page.id' => $parentRoomList
				),
				'recursive' => -1,
			);
			$parentPages = $this->Page->find('list', $params);
			foreach($parentRoomList as $roomId => $parentId) {
				$rooms[$roomId]['Page']['parent_room_id'] = $parentPages[$parentId];
				$rooms[$roomId]['Page']['parent_page_name'] = $rooms[$parentPages[$parentId]]['Page']['page_name'];
			}
		}

		if ($this->request->is('post') && isset($this->request->data['PageUserLink'])) {
			// 前へ、次へ時
			$enrollRoomIds = array();
			foreach($this->request->data['PageUserLink'] as $key => $pageUserLink) {
				$enrollRoomIds[$key] = $key;
			}
		} else {
			$enrollRoomIds = $this->Page->findViewableRoom('list', $userId, $addParams, $options);
		}

		$this->set('id', $this->id.'-'.$userId);	// top idをuser_id単位に設定
		$this->set('user_id', $userId);
		$this->set('user', $user);
		$this->set('rooms', $rooms);
		$this->set('enrollRoomIds', $enrollRoomIds);
	}

/**
 * 参加権限選択
 * @param   integer
 * @return  void
 * @since   v 3.0.0.0
 */
	public function select_auth($userId = null) {
		$loginUserId = $this->Auth->user('id');
		if(empty($userId)) {
			throw new BadRequestException(__('Unauthorized request.<br />Please reload the page.'));
		}
		$user = $this->User->findById($userId);
		if(!isset($user['User']) || ($loginUserId != $userId && $this->hierarchy < $user['Authority']['hierarchy'])) {
			$this->response->statusCode('403');
			$this->flash(__('Authority Error!  You do not have the privilege to access this page.'), '');
			return;
		}

		$addParams = array(
			'conditions' => array('Page.space_type' => array(NC_SPACE_TYPE_PUBLIC, NC_SPACE_TYPE_GROUP))
		);
		$options = array(
			'autoLang' => false
		);
		$enrollRooms = $this->Page->findViewableRoom('all', $userId, $addParams, $options);

		$pageUserLinks = array();
		if(isset($this->request->data['PageUserLink'])) {

			$pageIdArr = array();
			$pageUserLinkRoomIds = array();
			foreach($this->request->data['PageUserLink'] as $pageId => $PageUserLink) {
				if(empty($PageUserLink['room_id'])) {
					continue;
				}
				$pageName = null;
				$spaceType = null;
				$threadNum = null;
				$rootId = null;
				$authorityId = null;
				$publicationRangeFlag = null;
				if(!empty($PageUserLink['authority_id'])) {
					$authorityId = $PageUserLink['authority_id'];
					$pageIdArr[] = $pageId;
				} else if(isset($enrollRooms[$pageId])) {
					$pageName = $enrollRooms[$pageId]['Page']['page_name'];
					$spaceType = $enrollRooms[$pageId]['Page']['space_type'];
					$threadNum = $enrollRooms[$pageId]['Page']['thread_num'];
					$rootId = $enrollRooms[$pageId]['Page']['root_id'];
					$authorityId = $enrollRooms[$pageId]['PageAuthority']['id'];
					$publicationRangeFlag = $enrollRooms[$pageId]['Community']['publication_range_flag'];
				} else {
					$pageIdArr[] = $pageId;
				}
				$pageUserLinkRoomIds[] = $pageId;
				$pageUserLinks[$pageId] = array(
					'PageUserLink' => array(
						'room_id' => $PageUserLink['room_id'],
						'user_id' => $userId,
						'authority_id' => $authorityId,
					),
					'Page' => array(
						'page_name' => $pageName,
						'space_type' => $spaceType,
						'thread_num' => $threadNum,
						'root_id' => $rootId,
					),
					'Community' => array(
						'publication_range_flag' => $publicationRangeFlag
					),
					'Authority' => $user['Authority'],
				);
			}
			// spaceTypeよりデフォルトの権限設定
			if(count($pageIdArr) > 0) {
				$pages = $this->Page->findAuthById($pageIdArr, $userId);
				foreach($pageUserLinks as $pageId => $PageUserLink) {
					if(!isset($PageUserLink['PageUserLink']['authority_id'])) {
						$pageUserLinks[$pageId]['PageUserLink']['authority_id'] = $pages[$PageUserLink['PageUserLink']['room_id']]['PageAuthority']['id'];
					}

					if(!isset($PageUserLink['Page']['page_name'])) {
						$pageUserLinks[$pageId]['Page']['page_name'] = $pages[$PageUserLink['PageUserLink']['room_id']]['Page']['page_name'];
						$pageUserLinks[$pageId]['Page']['space_type'] = $pages[$PageUserLink['PageUserLink']['room_id']]['Page']['space_type'];
						$pageUserLinks[$pageId]['Page']['thread_num'] = $pages[$PageUserLink['PageUserLink']['room_id']]['Page']['thread_num'];
						$pageUserLinks[$pageId]['Page']['root_id'] = $pages[$PageUserLink['PageUserLink']['room_id']]['Page']['root_id'];
						$pageUserLinks[$pageId]['Community']['publication_range_flag'] = $pages[$PageUserLink['PageUserLink']['room_id']]['Community']['publication_range_flag'];
					}
				}
			}

			if($this->request->is('post') && !empty($this->request->data['submit'])) {
				$insertPageUserLinks = $pageUserLinks;
				foreach($enrollRooms as $pageId => $enrollRoom) {
					if(isset($insertPageUserLinks[$pageId])) {
						continue;
					}
					// 不参加
					$insertPageUserLinks[$pageId] = array(
						'PageUserLink' => array(
							'room_id' => $enrollRoom['Page']['room_id'],
							'user_id' => $userId,
							'authority_id' => NC_AUTH_OTHER,
						),
						'Page' => array(
							'page_name' => $enrollRoom['Page']['page_name'],
							'space_type' => $enrollRoom['Page']['space_type'],
							'thread_num' => $enrollRoom['Page']['thread_num'],
							'root_id' => $enrollRoom['Page']['root_id'],
						),
						'Community' => array(
							'publication_range_flag' => $enrollRoom['Community']['publication_range_flag']
						),
					);
					$pageUserLinkRoomIds[] = $pageId;
				}

				// 登録処理
				// room_id => array(id => authority_id)
				$pageUserLinkList = $this->PageUserLink->find('list', array(
					'fields' => array('PageUserLink.id', 'PageUserLink.authority_id', 'PageUserLink.room_id'),
					'conditions' => array(
						'room_id' => $pageUserLinkRoomIds,
						'user_id' => $userId,
					)
				));

				foreach($insertPageUserLinks as $pageId => $insertPageUserLink) {
					$insertPageUserLink['Authority'] = $user['Authority'];
					$defaultAuthorityId = $this->Page->getDefaultAuthorityId($insertPageUserLink);
					// パブリックは不参加->デフォルト値へ
					if($insertPageUserLink['Page']['space_type'] == NC_SPACE_TYPE_PUBLIC &&
						$insertPageUserLink['PageUserLink']['authority_id'] == NC_AUTH_OTHER_ID) {
						$insertPageUserLink['PageUserLink']['authority_id'] = $defaultAuthorityId;
					}
					$bufAuthorityId = null;
					if(isset($pageUserLinkList[$insertPageUserLink['PageUserLink']['room_id']])) {
						$buf = array_keys($pageUserLinkList[$insertPageUserLink['PageUserLink']['room_id']]);
						$bufAuthorityId = $pageUserLinkList[$insertPageUserLink['PageUserLink']['room_id']][$buf[0]];
						$insertPageUserLink['PageUserLink']['id'] = $buf[0];
					}

					$this->PageUserLink->create();
					if($insertPageUserLink['PageUserLink']['authority_id'] == $defaultAuthorityId) {
						// 参加するルームのデフォルト値->削除
						if(isset($insertPageUserLink['PageUserLink']['id']) && !$this->PageUserLink->delete($insertPageUserLink['PageUserLink']['id'])) {
							throw new InternalErrorException(__('Failed to delete the database, (%s).', 'page_user_links'));
						}
					} else if($insertPageUserLink['PageUserLink']['authority_id'] !== $bufAuthorityId) {
						// 変更があれば更新-追加
						if(!$this->PageUserLink->save($insertPageUserLink)) {
							throw new InternalErrorException(__('Failed to register the database, (%s).', 'page_user_links'));
						}
					}
				}

				// 会員一覧に唯一の主担が消された場合、メッセージを表示する
				$successMessage = '';
				$uniqueChiefRoomIds = $this->UserCommon->isUniqueChief($insertPageUserLinks);
				if(count($uniqueChiefRoomIds) > 0) {
					foreach($uniqueChiefRoomIds as $uniqueChiefRoomId) {
						$this->User->invalidate('authority_id', __d('user', 'In the [%1$s], the only chief did not exist. When do not appoint a chief again, in the [%2$s] cannot edit it.',
							$insertPageUserLinks[$uniqueChiefRoomId]['Page']['page_name'], $insertPageUserLinks[$uniqueChiefRoomId]['Page']['page_name']));
					}
				}

				$this->Session->setFlash(__('Has been successfully registered.'));
				$this->set('is_success', true);
			}
		}

		$this->set('id', $this->id.'-'.$userId);	// top idをuser_id単位に設定
		$this->set('user_id', $userId);
		$this->set('user', $user);
		$this->set('user_authority_name', $this->Authority->getUserAuthorityName($user['Authority']['hierarchy']));
		$this->set('page_user_links', $pageUserLinks);
		$this->set('auth_list',$this->Authority->findAuthSelect());
	}

/**
 * 項目設定
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function display_setting() {
		if ($this->request->is('post')) {
			foreach($this->request->data as $data) {
				if(!isset($data['Item'])) {
					throw new BadRequestException(__('Unauthorized request.<br />Please reload the page.'));
				}
				// 会員管理管理者しか使用しないため、リクエストを信用してupdate
				$fields = array(
					'Item.list_num'=>intval($data['Item']['list_num']),
					'Item.col_num'=>intval($data['Item']['col_num']),
					'Item.row_num'=>intval($data['Item']['row_num']),
				);
				$conditions = array(
					"Item.id" => intval($data['Item']['id'])
				);
				if(!$this->Item->updateAll($fields, $conditions)) {
					throw new InternalErrorException(__('Failed to update the database, (%s).', 'items'));
				}
			}
			$this->Session->setFlash(__('Has been successfully updated.'));
		}
		$this->set("items", $this->Item->findList('all'));
	}

/**
 * 項目追加・編集
 * @param   integer $itemId
 * @return  void
 * @since   v 3.0.0.0
 */
	public function add_item($itemId = null) {
		if(isset($itemId)) {
			$conditions = array('Item.id' => $itemId);
			$item = $this->Item->findList('first', $conditions);
			if(!isset($item['Item'])) {
				throw new BadRequestException(__('Unauthorized request.<br />Please reload the page.'));
			}
		} else {
			$item = $this->Item->findDefault();
		}

		if ($this->request->is('post')) {
// TODO: 未作成
		}
		$this->set('item', $item);
	}

/**
 * インポート
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function import() {

	}

/**
 * 言語切替
 * @param   string $renderElement
 * @return  string
 * @since   v 3.0.0.0
 */
	protected function _setLanguage($renderElement = null) {
		$activeLang = isset($this->request->named['language']) ? $this->request->named['language'] : null;
		if(isset($this->request->data['activeLang'])) {
			$activeLang = $this->request->data['activeLang'];
		}
		$languages = $this->Language->findSelectList();
		$this->set("language", $activeLang);
		$this->set("languages", $languages);
		if(isset($activeLang) && isset($languages[$activeLang])) {
			$this->Session->write(NC_SYSTEM_KEY.'.user.pre_lang', $this->Session->read(NC_CONFIG_KEY.'.language'));
			Configure::write(NC_CONFIG_KEY.'.'.'language', $activeLang);
			$this->Session->write(NC_CONFIG_KEY.'.language', $activeLang);
			if(!empty($renderElement)) {
				$this->render($renderElement);
			}
		}
		return $activeLang;
	}
}