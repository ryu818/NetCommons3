<?php
/**
 * CheckAuthComponentクラス
 *
 * <pre>
 * 権限をチェックするComponentクラス
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       App.Controllers.Components
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class CheckAuthComponent extends Component {
/**
 * Controller
 *
 * @var     object
 */
	protected $_controller = null;

/**
 * Other components utilized by Component
 *
 * @var     array
 */
	public $components = array('Auth', 'Session');	// , 'Init'

/**
 * 許す権限
 *
 * @var integer
 * default Auto
 */
	public $allowAuth = null;

/**
 * 許す会員権限
 *
 * @var integer
 * default 誰でも閲覧可
 */
	public $allowUserAuth = NC_AUTH_OTHER;

/**
 * チェックしないURL(正規表現にて記述)
 *
 * @var  string
 */
	public $prohibitionURL = NC_PROHIBITION_URL;

/**
 * パーマリンクからのPageとblock_idのはってあるページが一致しているかどうかのチェックするかどうか
 * @var boolean
 */
	public $chkMovedPermanently = true;

/**
 * block_idのプラグイン名称とパスからのプラグイン名称が一致しているかどうかのチェックするかどうか
 * @var boolean
 */
	public $chkPlugin = true;

/**
 * 一般モジュールでblock_idをチェックするかどうか
 * @var boolean
 */
	public $chkBlockId = true;

/**
 * pageデータを取得する条件の順番
 * 取得に失敗した場合、次を行う
 * @var array
 * url:URLから取得
 *      permalink OR
 *		module(act)/$active_plugin/$active_controller/$block_id OR
 *		module(act)/$block_id
 * request:リクエストパラメータのpage_id($requestPageId)から取得
 */
	public $checkOrder = array("url", "request");

/**
 * リクエストのpage_id名称
 * @var string
 */
	public $requestPageId = "page_id";

/**
 * Constructor
 *
 * @param ComponentCollection $collection A ComponentCollection this component can use to lazy load its components
 * @param array $settings Array of configuration settings.
 */
	public function __construct(ComponentCollection $collection, $settings = array()) {
		$this->_controller = $collection->getController();
		parent::__construct($collection, $settings);
	}

/**
 * 権限チェック
 *
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function check() {
		$controller = $this->_controller;
		//$this->_setControllerParams($controller);
		$user = $this->Auth->user();//認証済みユーザーを取得
		$controller_name = $controller->request->params['controller'];
		$action_name = $controller->request->params['action'];
		$plugin_name = $controller->request->params['plugin'];

		$userId = isset($user['id']) ? intval($user['id']) : 0;
		$handle = isset($user['handle']) ? $user['handle'] : '';

		Configure::write(NC_SYSTEM_KEY.'.isLogin', isset($user['id']) ? true : false);

		if(!isset($user['id'])) {
			$user = array(
				'id' => 0,
				'handle' => '',
				'hierarchy' => NC_AUTH_OTHER,
				'myportal_page_id' => 0,
				'private_page_id' => 0,
				'myportal_use_flag' => _OFF,
				'allow_myportal_viewing_hierarchy' => NC_AUTH_OTHER,
				'private_use_flag' => _OFF,
			);
		}
		Configure::write(NC_SYSTEM_KEY.'.user', $user);
		$redirect_url = ($userId == 0) ? '/users/login' : null;
		$block_type = isset($controller->request->params['block_type']) ? $controller->request->params['block_type'] : null;
		$isActiveContent = (isset($controller->request->params['block_type']) && $controller->request->params['block_type'] == 'active-contents') ? true : false;

		if(isset($plugin_name) && $plugin_name != 'group') {
			$camel_plugin_name = Inflector::camelize($plugin_name);
			$module = $controller->Module->findByDirname($camel_plugin_name);
			if(isset($module['Module']['dir_name']) && $module['Module']['dir_name'] != $camel_plugin_name) {
				$controller->flash(__('Content not found.'), '', 'CheckAuth.check.001', '404');
				return;
			}
			$controller->nc_module = $module;
			Configure::write(NC_SYSTEM_KEY.'.Modules.'.$camel_plugin_name, $module);
		}

		if($plugin_name == 'page' || $controller_name == 'pages' || $plugin_name == 'group' ||
			$controller_name == 'users' || $module['Module']['system_flag'] == _OFF) {
			// 一般系モジュール
			$page = $this->checkGeneral();
			if($page === false) {
				return;
			}
		} else {
			// 管理系モジュール
			if(!$this->checkAdmin($module['Module']['id'])) {
				return;
			}
		}

		if($block_type == 'active-controls' || $block_type == 'active-contents') {
			Configure::write(NC_SYSTEM_KEY.'.block_type', $block_type);
		} else if($controller->request->header('X-NC-PAGE') || ($controller->request->params['plugin'] == '' && $controller->request->params['controller'] == 'pages')) {
			Configure::write(NC_SYSTEM_KEY.'.block_type', 'blocks');
		}

		// 権限チェック
		if(!isset($this->allowAuth) && isset($page['Page'])) {
			if($page['Page']['space_type'] == NC_SPACE_TYPE_PUBLIC) {
				$this->allowAuth = NC_AUTH_OTHER;
			} else if($page['Page']['space_type'] == NC_SPACE_TYPE_MYPORTAL ||
				$page['Page']['space_type'] == NC_SPACE_TYPE_PRIVATE) {
				$currentUser = $this->_controller->User->currentUser($page, $user);
				if(!isset($currentUser['User'])) {
					// 不参加
					$controller->hierarchy = NC_AUTH_OTHER;
				} else {
					if($page['Page']['space_type'] == NC_SPACE_TYPE_MYPORTAL) {
						if($currentUser['User']['myportal_use_flag'] == NC_MYPORTAL_USE_ALL) {
							// 使用
							$this->allowAuth = NC_AUTH_OTHER;
						} else {
							// 未使用
							$controller->hierarchy = NC_AUTH_OTHER;
						}
						if($currentUser['User']['myportal_use_flag'] == NC_MYPORTAL_MEMBERS) {
							$this->allowUserAuth = $currentUser['User']['allow_myportal_viewing_hierarchy'];
						}
					} else {
						if($currentUser['User']['private_use_flag'] == _ON) {
							// 使用
							$this->allowAuth = NC_AUTH_CHIEF;
						} else {
							// 未使用
							$controller->hierarchy = NC_AUTH_OTHER;
						}
					}
				}
			} else {
				// コミュニティ
				$params = array(
					'fields' => array(
						'Community.publication_range_flag'
					),
					'conditions' => array('room_id' => $page['Page']['root_id']),
				);
				$current_community = $controller->Community->find('first', $params);
				if($current_community['Community']['publication_range_flag'] == NC_PUBLICATION_RANGE_FLAG_ALL) {
					// 公開
					$this->allowAuth = NC_AUTH_OTHER;
				} else {
					$this->allowAuth = NC_AUTH_GUEST;
				}
			}
		}

		if(isset($controller->nc_block['Block'])) {
			// 画面タイプ設定
			$controllerAction = null;
			if($action_name == 'index') {
				$controller_arr = explode('_', $controller_name, 2);
				$controllerAction = $controller_arr[0];
				if(isset($controller_arr[1])) {
					$controllerAction .= '/'. $controller_arr[1];
				}
			}

			if($controller->nc_block['Module']['edit_controller_action'] != '' && (($controller->nc_block['Module']['edit_controller_action'] == $controller_name .'/'.$action_name) ||
				($controller->nc_block['Module']['edit_controller_action'] == $controller_name && $action_name == 'index') ||
				($controller->nc_block['Module']['edit_controller_action'] == $controllerAction))) {
				$ncType = 'edit';
			} else if($controller->nc_block['Module']['style_controller_action'] != '' && (($controller->nc_block['Module']['style_controller_action'] == $controller_name .'/'.$action_name) ||
				($controller->nc_block['Module']['style_controller_action'] == $controller_name && $action_name == 'index') ||
				($controller->nc_block['Module']['style_controller_action'] == $controllerAction))) {
				$ncType = 'style';
			} else {
				$ncType = $controller->ncType;
			}
			$controller->ncType = $ncType;
		} //else {
			//$ret = $this->checkAuth($controller->hierarchy, NC_AUTH_CHIEF);
		//}
		//$controller->nc_show_edit = $ret;
		$checkHierarchy = ($controller->ncType == 'style' || (isset($controller->blockHierarchy) && $plugin_name == 'block')) ? $controller->blockHierarchy : $controller->hierarchy;
		if(!$this->checkAuth($checkHierarchy)) {
			if($userId == 0) {
				$this->Session->setFlash(__('Forbidden permission to access the page.'), 'default', array(), 'auth');
			}
			$controller->flash(__('Forbidden permission to access the page.'), $redirect_url, 'CheckAuth.check.003', '403');
			return;
		}

		if($isActiveContent) {
			// 参照のみなのでゲスト固定
			// 権限チェック後セット
			$controller->hierarchy = NC_AUTH_GUEST;
		}

		// 権限チェック(会員権限)
		if(!$this->checkUserAuth($user['hierarchy'])) {
			if($userId == 0) {
				$this->Session->setFlash(__('Forbidden permission to access the page.'), 'default', array(), 'auth');
			}
			$controller->flash(__('Forbidden permission to access the page.'), $redirect_url, 'CheckAuth.check.004', '403');
			return;
		}

		if($controller->hierarchy >= NC_AUTH_MIN_CHIEF) {
			$controller->isChief = true;
		} else {
			$controller->isChief = false;
		}
	}

/**
 * 管理系権限チェック
 *
 * @param   integer $module_id
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function checkAdmin($module_id) {
		$controller = $this->_controller;
		$user = $this->Auth->user();
		$userId = isset($user['id']) ? intval($user['id']) : 0;
		$redirect_url = ($userId == 0) ? '/users/login' : null;
		$url = $controller->request->url;
		$authority_id = isset($user['authority_id']) ? intval($user['authority_id']) : NC_AUTH_OTHER;

		// メンバ変数セット
		$controller->id = '_system'. $module_id;
		$controller->module_id = $module_id;
		Configure::write(NC_SYSTEM_KEY.'.id', $controller->id);		// $this->request->hereで使用するため

		$controller->hierarchy = $controller->ModuleSystemLink->findHierarchy($module_id, $authority_id);

		return true;
	}

/**
 * 一般系系権限チェック
 *
 * @param   Model Module $module
 * @return  boolean|Model Page
 * @since   v 3.0.0.0
 */
	public function checkGeneral() {
		$controller = $this->_controller;
		$user = $this->Auth->user();
		$userId = isset($user['id']) ? intval($user['id']) : 0;
		$page = true;
		$this->_setControllerParams($controller);
		if (isset($controller->nc_block['Block'])) {
			// 既に権限チェック済 - PageからrequestActionよりブロックを表示した場合
			return true;
		}
		$redirect_url = ($userId == 0) ? '/users/login' : null;
		$url = $controller->request->url;

		$permalink = isset($controller->request->params['permalink']) ? $controller->request->params['permalink'] : '';
		$block_id = isset($controller->request->params['block_id']) ? intval($controller->request->params['block_id']) : 0;
		$data_page_id = !empty($controller->request->data[$this->requestPageId]) ? $controller->request->data[$this->requestPageId] : 0;
		$named_page_id = !empty($controller->request->named[$this->requestPageId]) ? $controller->request->named[$this->requestPageId] : 0;
		$url_page_id = !empty($controller->request->query[$this->requestPageId]) ? $controller->request->query[$this->requestPageId] : 0;
		$page_id = !empty($data_page_id) ? $data_page_id : (!empty($named_page_id) ? $named_page_id : (!empty($url_page_id) ? $url_page_id : 0));

		$plugin_name = isset($controller->request->params['plugin']) ? $controller->request->params['plugin'] :
			(isset($controller->request->params['active_plugin']) ? $controller->request->params['active_plugin'] : '');
		$controller_name = $controller->request->params['controller'];
		$action_name = $controller->request->params['action'];
		$lang = $this->Session->read(NC_CONFIG_KEY.'.'.'language');
		$isActiveContent = (isset($controller->request->params['block_type']) && $controller->request->params['block_type'] == 'active-contents') ? true : false;

		$permalink = trim($permalink, '/');
		Configure::write(NC_SYSTEM_KEY.'.permalink', $permalink);
		Configure::write(NC_SYSTEM_KEY.'.block_id', $block_id);

		// メンバ変数セット
		$controller->id = '_'. $block_id;
		$controller->block_id = $block_id;
		Configure::write(NC_SYSTEM_KEY.'.id', $controller->id);	// $this->request->hereで使用するため

		if(!preg_match($this->prohibitionURL, $url)) {
			if($isActiveContent) {
				// Contentのみの表示は主担に限る
				$this->allowAuth = NC_AUTH_CHIEF;
				$contentId = isset($controller->request->params['content_id']) ? intval($controller->request->params['content_id']) : 0;
				$content =  $controller->Content->findAuthById($contentId, $userId);
				if(!isset($content['Content'])) {
					// 置いているpluginが異なる
					$controller->flash(__('Content not found.'), '', 'CheckAuth.checkGeneral.001', '404');
					return false;
				}
				$controller->content_id = intval($content['Content']['master_id']);
				if($this->chkPlugin && $plugin_name != 'group' && $content['Module']['dir_name'] != Inflector::camelize($plugin_name)) {
					// 置いているpluginが異なる（dir_nameから）
					$controller->flash(__('Content not found.'), '', 'CheckAuth.checkGeneral.002', '404');
					return false;
				}
				$page = $controller->Page->findAuthById($content['Content']['room_id'], $userId);
				if(isset($page['PageAuthority']['hierarchy'])) {
					$controller->hierarchy = $page['PageAuthority']['hierarchy'];
				}

				//$content['Block']['id'] = 0;
				$controller->nc_block = $content;

				Configure::write(NC_SYSTEM_KEY.'.content_id', $controller->content_id);

			} else {
				if($block_id > 0) {
					$block =  $controller->Block->findAuthById($block_id, $userId);
					if($block === false || !isset($block['Block'])) {
						// 置いているpluginが異なる
						$controller->flash(__('Content not found.'), '', 'CheckAuth.checkGeneral.003', '404');
						return false;
					}
					if($this->chkPlugin && $plugin_name != 'group' && $block['Module']['dir_name'] != Inflector::camelize($plugin_name)) {
						// 置いているpluginが異なる（dir_nameから）
						$controller->flash(__('Content not found.'), '', 'CheckAuth.checkGeneral.004', '404');
						return false;
					}
					$controller->nc_block = $block;
					$controller->module_id = $block['Block']['module_id'];

					$active_page = $controller->Page->findIncludeComunityLang($block['Block']['page_id']);
					if(!$active_page) {
						// ブロックIDに対応したページが存在しない
						$controller->flash(__('Page not found.'), $redirect_url, 'CheckAuth.checkGeneral.005', '404');
						return false;
					}
				}

				$result = $this->_getPage($controller, $permalink, $page_id, $userId, $lang);

				if($result === false) {
					$page = false;
					$center_page = false;
				} else {
					list($center_page, $page) = $result;
				}
				Configure::write(NC_SYSTEM_KEY.'.'.'center_page', $center_page);
				if($this->chkMovedPermanently && isset($active_page) && $active_page['Page']['position_flag'] &&
						($page === false || $page['Page']['id'] != $active_page['Page']['id'])) {
					// パーマリンクからのPageとblock_idのはってあるページが一致しなければエラー
					// ３０１をかえして、その後、移動後のページへ遷移させる
					// ただし、pパラメータがある場合、短縮URLとしてエラーとしない。（p固定）
					// TODO:中央カラムのみにはってあるブロックのみチェックしているが、ヘッダーのブロックのチェックもするべき
					$activePermalink = $controller->Page->getPermalink($active_page['Page']['permalink'], $active_page['Page']['space_type']);
					if(isset($controller->request->query['p'])) {
						$controller->redirect(array(
							'permalink' => trim($activePermalink, '/'),
							'block_type' => 'blocks',
							'plugin' => $plugin_name,
							'controller' => $plugin_name,
							'action' => $action_name,
							'block_id' => $block_id,
							'?' => array('p' => intval($controller->request->query['p']))
						));
						return;
					}
					if($permalink == '') {
						$redirect_url = $activePermalink. '/' .$url;
					} else {
						$redirect_url = preg_replace('$^'.$permalink.'$i', $activePermalink, $url);
					}
					$redirect_url .= '#_'.$block_id;
					$controller->flash(__('Moved Permanently.'), $redirect_url, 'CheckAuth.checkGeneral.006', '301');
					return false;
				}

				if($page === false || $center_page === false) {
					// ページが存在しない
					$controller->flash(__('Page not found.'), $redirect_url, 'CheckAuth.checkGeneral.007', '404');
					return false;
				}

				$controller->nc_page = isset($active_page) ? $active_page : $page;	// blockから取得できるPage優先
				$controller->nc_current_page = $page;								// pageから取得できるPage
				if($page['Page']['display_flag'] == NC_DISPLAY_FLAG_DISABLE ||
						($page['Page']['display_flag'] == NC_DISPLAY_FLAG_OFF && $page['PageAuthority']['hierarchy'] < NC_AUTH_MIN_CHIEF)) {
					$controller->flash(__('Content not found.'), $redirect_url, 'CheckAuth.checkGeneral.008', '404');
					return false;
				}

				// PageとBlockのhierarchyの低いほうをセット
				$page_hierarchy = (isset($page) && !is_null($page['PageAuthority']['hierarchy'])) ? intval($page['PageAuthority']['hierarchy']) : NC_AUTH_OTHER;
				$block_hierarchy = (isset($block) && !is_null($block['Block']['hierarchy'])) ? intval($block['Block']['hierarchy']) : $page_hierarchy;
				$controller->hierarchy = min($block_hierarchy, $page_hierarchy);
				if(isset($block) && !is_null($block['Block']['block_hierarchy'])) {
					$controller->blockHierarchy = intval($block['Block']['block_hierarchy']);
				}

				if(isset($block) && !is_null($block['Content']['master_id'])) {
					$controller->content_id = intval($block['Content']['master_id']);
				}

				$controller->page_id = isset($block['Block']['page_id']) ? intval(($block['Block']['page_id'])) : intval($page['Page']['id']);

				//TODO:test
				/*
				 * ページ
				*/
				if($controller_name == 'pages') {
					// test
					$page_id_arr = array($page['Page']['id'], 5, 6, 7, 8);
					// ページ内のカラムページのリストをセット
					$controller->page_id_arr = $page_id_arr;
				}
				//$controller->page_id = $page['Page']['id'];
			}
			// TODO:ログインに遷移する場合、以下を記述
			//$controller->Auth->deny();
		}

		// block_idチェック
		if($this->chkBlockId && !$isActiveContent) {
			if(!isset($block_id) || $block_id == 0) {
				$controller->flash(__('Content not found.'), $redirect_url, 'CheckAuth.checkGeneral.009', '404');
				return false;
			}
		}

		/**
		 * Setting Mode
		 */
		$mode = $this->Session->read(NC_SYSTEM_KEY.'.mode');
		if($controller->hierarchy >= NC_AUTH_MIN_CHIEF) {
			if(isset($controller->request->query['setting_mode']) &&
					!is_null($controller->request->query['setting_mode'])) {
				switch ($controller->request->query['setting_mode']) {
					case NC_GENERAL_MODE:
						$this->Session->delete(NC_SYSTEM_KEY.'.mode');
						break;
					case NC_BLOCK_MODE:
						$this->Session->write(NC_SYSTEM_KEY.'.mode', NC_BLOCK_MODE);
						break;
				}
			}
		} else if($userId == 0 && !empty($mode)) {
			$this->Session->delete(NC_SYSTEM_KEY.'.mode');
		}

		return $page;
	}

/**
 * 権限チェック
 *
 * @param   integer $hierarchy
 * @param   integer $allowAuth
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function checkAuth($hierarchy, $allowAuth = null) {
		if(!isset($allowAuth)) {
			$allowAuth = ($this->allowAuth == null) ? NC_AUTH_OTHER : $this->allowAuth;
		}
		App::uses('Authority', 'Model');
		$Authority = new Authority();
		$allowAuth = $Authority->getMinHierarchy($allowAuth);
		if($hierarchy < $allowAuth) {	// $page_id != 0 &&
			return false;
		}
		return true;
	}

/**
 * 権限チェック
 *
 * @param   integer $hierarchy
 * @param   integer $allowUserAuth
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function checkUserAuth($hierarchy, $allowUserAuth = null) {
		if(!isset($allowUserAuth)) {
			$allowUserAuth = ($this->allowUserAuth == null) ? NC_AUTH_OTHER : $this->allowUserAuth;
		}
		App::uses('Authority', 'Model');
		$Authority = new Authority();
		$allowUserAuth = $Authority->getMinHierarchy($allowUserAuth);
		if(intval($hierarchy) < $allowUserAuth) {
			return false;
		}
		return true;
	}

/**
 * Page取得
 * <pre>
 * Page情報を取得する
 * </pre>
 *
 * @param   Controller $controller Instantiating controller
 * @param   string  $permalink
 * @param   integer $userId
 * @param   string  $lang
 * @return  array ($center_page, $page) エラーの場合、false
 * @since   v 3.0.0.0
 */
	protected function _getPage(Controller $controller, $permalink, $page_id, $userId, $lang) {
		$page = null;
		$request_page = null;
		$center_page = false;
		$lang = Configure::read(NC_CONFIG_KEY.'.'.'language');
		if(empty($userId)) {
			$page_params = array(
				'fields' => $controller->Page->getFieldsArray($userId),
				'joins' => $controller->Page->getJoinsArray($userId),
			);
		} else {
			$page_params = array(
				'fields' => $controller->Page->getFieldsArray($userId),
				'joins' => $controller->Page->getJoinsArray($userId),
			);
		}

		foreach($this->checkOrder as $order) {
			unset($page_params['conditions']);
			switch($order) {
				case "url":
					$space_type = NC_SPACE_TYPE_PUBLIC;
					if($permalink != '') {
						if(NC_SPACE_PUBLIC_PREFIX != '' && substr($permalink, 0, strlen(NC_SPACE_PUBLIC_PREFIX) + 1) == NC_SPACE_PUBLIC_PREFIX.'/') {
							$permalink = substr($permalink, strlen(NC_SPACE_PUBLIC_PREFIX) + 1);
						} else if(NC_SPACE_MYPORTAL_PREFIX != '' && substr($permalink, 0, strlen(NC_SPACE_MYPORTAL_PREFIX) + 1) == NC_SPACE_MYPORTAL_PREFIX.'/') {
							$space_type = NC_SPACE_TYPE_MYPORTAL;
							$permalink = substr($permalink, strlen(NC_SPACE_MYPORTAL_PREFIX) + 1);
						} else if(NC_SPACE_PRIVATE_PREFIX != '' && substr($permalink, 0, strlen(NC_SPACE_PRIVATE_PREFIX) + 1) == NC_SPACE_PRIVATE_PREFIX.'/') {
							$space_type = NC_SPACE_TYPE_PRIVATE;
							$permalink = substr($permalink, strlen(NC_SPACE_PRIVATE_PREFIX) + 1);
						} else if(NC_SPACE_GROUP_PREFIX != '' && substr($permalink, 0, strlen(NC_SPACE_GROUP_PREFIX) + 1) == NC_SPACE_GROUP_PREFIX.'/') {
							$space_type = NC_SPACE_TYPE_GROUP;
							$permalink = substr($permalink, strlen(NC_SPACE_GROUP_PREFIX) + 1);
						}
					}
					if($permalink == "") {
						// トップページ
						$page_params['conditions'] = array(
							'Page.permalink' => '',
							'Page.position_flag' => _ON,
							'Page.space_type' => $space_type,
							'Page.display_sequence' => 1,
							'Page.thread_num' => 2
						);
					} else {
						$page_params['conditions'] = array(
							'Page.permalink' => $permalink,
							'Page.position_flag' => _ON,
							'Page.space_type' => $space_type,
							'Page.display_sequence !=' => 0,
							'Page.thread_num >' => 1
						);
					}
					break;
				case "request":
					if($page_id == 0) {
						continue;
					} else {
						$page_params['conditions'] = array('Page.id' => $page_id);
					}
					break;
			}

			if(empty($page_params['conditions']))
				continue;

			$pages = $controller->Page->find('all', $page_params);
			$page = null;
			if(isset($pages[0])) {
				$active_lang = null;
				foreach($pages as $current_page) {
					if($current_page['Page']['lang'] == $lang) {
						$page = $current_page;
						break;
					} else if(($active_lang === null) ||
							($active_lang !== '' && $active_lang != 'en')) {
						// 英語を優先的に表示する
						$active_lang = $current_page['Page']['lang'];
						$page = $current_page;
					}
				}
				if(isset($page['Page'])) {
					if($order == 'url') {
						$center_page  = $page;
						break;
					} else {
						$request_page = $page;
					}
				}
			}
		}
		if($request_page) {
			$page = $request_page;
		}

		if(!isset($page)) {
			return false;
		}
		$page = $controller->Page->afterFindIds($page, $userId);

		return array($center_page, $page);
	}

	protected function _setControllerParams(Controller $controller) {
		$blocks = Configure::read(NC_SYSTEM_KEY.'.blocks');
		$block = Configure::read(NC_SYSTEM_KEY.'.block');
		$page = Configure::read(NC_SYSTEM_KEY.'.page');

		$controller->hierarchy = NC_AUTH_OTHER;	// 初期化
		$controller->content_id = 0;			// 初期化

		if(isset($blocks)) {
			$controller->nc_blocks = $blocks;
			Configure::delete(NC_SYSTEM_KEY.'.blocks');
		}

		if(isset($block)) {
			$controller->nc_block = $block;
			Configure::delete(NC_SYSTEM_KEY.'.block');

			$controller->id = '_'.intval($block['Block']['id']);
			$controller->block_id = intval($block['Block']['id']);
			$controller->module_id = intval($block['Block']['module_id']);
			Configure::write(NC_SYSTEM_KEY.'.block_id', intval($block['Block']['id']));

			if(!is_null($block['Block']['hierarchy'])) {
				$controller->hierarchy = intval($block['Block']['hierarchy']);
			}
			if(!is_null($block['Block']['block_hierarchy'])) {
				$controller->blockHierarchy = intval($block['Block']['block_hierarchy']);
			}
			if(!is_null($block['Content']['master_id'])) {
				$controller->content_id = intval($block['Content']['master_id']);
			}
		}

		if(isset($page)) {
			$controller->nc_page = $page;
			Configure::delete(NC_SYSTEM_KEY.'.page');
		}
	}

/**
 * 編集権限があるかどうか
 * @param   integer $roomHierarchy ログイン会員のroomにおけるhierarchy
 * @param   integer $editPostHierarchy 編集画面における記事投稿権限hierarchy
 * @param   integer $postUserId 記事投稿者user_id
 * @param   integer $postHierarchy 記事投稿者hierarchy
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function isEdit($roomHierarchy, $editPostHierarchy = null, $postUserId = null, $postHierarchy = null) {
		// Helperに同じメソッドが存在する。
		$isEdit = true;
		if(isset($editPostHierarchy)) {
			if($roomHierarchy < $editPostHierarchy) {
				return false;
			}
		}
		if(!empty($postUserId)) {
			$user = $this->Session->read(NC_AUTH_KEY.'.'.'User');
			if(isset($user) && $user['id'] == $postUserId) {
				return true;
			}
		}
		if(isset($postHierarchy)) {
			$postHierarchy = intval($postHierarchy);
			if($postHierarchy <= NC_AUTH_GUEST) {
				if($roomHierarchy >= NC_AUTH_MIN_CHIEF) {
					$isEdit = true;
				} else {
					$isEdit = false;
				}
			} else if($roomHierarchy >= NC_AUTH_MIN_MODERATE) {
				if($roomHierarchy >= $postHierarchy) {
					$isEdit = true;
				} else {
					$isEdit = false;
				}
			} else {
				if($roomHierarchy > $postHierarchy) {
					$isEdit = true;
				} else {
					$isEdit = false;
				}
			}
		}
		return $isEdit;
	}
}