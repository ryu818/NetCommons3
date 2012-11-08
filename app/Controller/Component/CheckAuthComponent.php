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
 * Other components utilized by Component
 *
 * @var     array
 */
	public $components = array('Auth', 'Session');	// , 'Init'

/**
 * 許す権限
 *
 * @var int
 * default 誰でも閲覧可
 */
	public $allowAuth = NC_AUTH_OTHER;

/**
 * 許す会員権限
 *
 * @var int
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
 * Start CheckAuthComponent for use in the controller
 *
 * @param Controller $controller
 * @return  void
 * @since   v 3.0.0.0
 */
	public function startup(Controller $controller) {
		
		$this->_setControllerPrams($controller);

		if (isset($controller->nc_block['Block'])) {
			// 既に権限チェック済
			return;
		}

		//$controller->set('title_for_layout',  '');
		$user = $this->Auth->user();//認証済みユーザを取得
		$user_id = isset($user['id']) ? intval($user['id']) : 0;
		$lang = $this->Session->read(NC_CONFIG_KEY.'.'.'language');

		$authority_id = isset($user['authority_id']) ? intval($user['authority_id']) : NC_AUTH_OTHER;

		$url = $controller->request->url;

		$permalink = isset($controller->request->params['permalink']) ? $controller->request->params['permalink'] : '';

		$block_id = isset($controller->request->params['block_id']) ? intval($controller->request->params['block_id']) : 0;

		$plugin_name = $controller->request->params['plugin'];
		$controller_name = $controller->request->params['controller'];
		$action_name = $controller->request->params['action'];
		
		$permalink = $this->formatUrl($permalink);

		Configure::write(NC_SYSTEM_KEY.'.permalink', $permalink);
		$controller->id = '_'. $block_id;
		$controller->block_id = $block_id;

		if($user_id == 0)
			$redirect_url = '/users/login';
		else
			$redirect_url = null;
		
		if(!preg_match($this->prohibitionURL, $url)) {
			if($block_id > 0) {
				$block = $this->_getBlock($controller, $block_id, $user_id);
				if($block === false) {
					// 置いているpluginが異なる
					$controller->flash(__('Content not found.'), '', 'CheckAuth.001', '404');
					return ;
				}

				$active_page = $controller->Page->findById($block['Block']['page_id']);
				if(!$active_page) {
					// ブロックIDに対応したページが存在しない
					$controller->flash(__('Page not found.'), $redirect_url, 'CheckAuth.002', '404');
					return ;
				}
				$controller->nc_page = $active_page;

				//
				// title
				//
				//if(isset($block['Block']['title'])) {
				//	// TODO:Module.module_nameも考慮するべき？
				//	$controller->set('title_for_layout',  $block['Block']['title']);
				//}
			}

			$page = $this->_getPage($controller, $permalink, $user_id, $lang);
			if($permalink != '' && isset($active_page) && $active_page['Page']['position_flag'] &&
				($page === false || $page['Page']['id'] != $active_page['Page']['id'])) {
				// パーマリンクからのPageとblock_idのはってあるページが一致しなければエラー
				// ３０１をかえして、その後、移動後のページへ遷移させる
				// TODO:中央カラムのみにはってあるブロックのみチェックしてあるが、ヘッダーのブロックのチェックできる
				$redirect_url = preg_replace('$^'.$permalink.'$i', $active_page['Page']['permalink'], $url);
				$controller->flash(__('Moved Permanently.'), $redirect_url, 'CheckAuth.003', '301');
				return ;
			}
			if($page === false) {
				// ページが存在しない
				$controller->flash(__('Page not found.'), $redirect_url, 'CheckAuth.004', '404');
				return ;
			}

			if(isset($block) && !is_null($block['Block']['hierarchy'])) {
				$controller->hierarchy = intval($block['Block']['hierarchy']);
			} else if(!is_null($page['Authority']['hierarchy'])) {
				$controller->hierarchy = intval($page['Authority']['hierarchy']);
			}
			if(isset($block) && !is_null($block['Content']['master_id'])) {
				$controller->content_id = intval($block['Content']['master_id']);
			}

			if($plugin_name == '' && $controller_name == 'pages') {
				// pages
				if(isset($controller->request->params['active_plugin'])) {
					$controller->request->offsetSet('active_plugin', $this->formatUrl($controller->request->params['active_plugin']));
				}
				if(isset($controller->request->params['active_controller'])) {
					$controller->request->offsetSet('active_controller', $this->formatUrl($controller->request->params['active_controller']));
				}
				if(isset($controller->request->params['active_action'])) {
					$controller->request->offsetSet('active_action', $this->formatUrl($controller->request->params['active_action']));
				}
			}

			/*if($plugin_name != '' && $plugin_name != 'group') {// && $plugin_name != 'block'
				// plugin
				$module = $this->_getModule($controller, $plugin_name, $authority_id);
				if($module === false) {
					$controller->flash(__('Content not found.'), '', 'CheckAuth.006', 404);
					return ;
				}
				if($block && $block['Block']['module_id'] != $module['Module']['id']) {
					// block_idから取得するpluginと、plugin_nameから取得したpluginの名前が一致しない。
					$controller->flash(__('Unauthorized request.<br />Please reload the page.'), '', 'CheckAuth.005', '400');
					return ;
				}
			}*/
			// TODO:test センターカラム以外のpege_idも入ってくる可能性あるため、修正予定。
			$center_page = $page;
			
			$current_user = $controller->User->currentUser($center_page, $user);
			if($current_user === '') {
				$current_user = array('User' => $user);
			}
			if(isset($current_user['User'])) {
				$myportal_name = __('Myportal of %s', $current_user['User']['handle']);
				$private_name = __('Private room of %s', $current_user['User']['handle']);
				if( $center_page['Page']['display_sequence'] == 1 ) {
					if($center_page['Page']['space_type'] == NC_SPACE_TYPE_MYPORTAL) {
						$page['Page']['page_name'] = $myportal_name;
						$center_page['Page']['page_name'] = $myportal_name;
					} else if($center_page['Page']['space_type'] == NC_SPACE_TYPE_PRIVATE) {
						$page['Page']['page_name'] = $private_name;
						$center_page['Page']['page_name'] = $private_name;
					}
				}
				Configure::write(NC_AUTH_KEY.'.'.'myportal_name', $myportal_name);
				Configure::write(NC_AUTH_KEY.'.'.'private_name', $private_name);
			}
			
			
			$controller->page_id = intval($page['Page']['id']);
			Configure::write(NC_SYSTEM_KEY.'.'.'Center_Page', $center_page);

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
			$controller->page_id = $page['Page']['id'];

			// TODO:ログインに遷移する場合、以下を記述
			//$controller->Auth->deny();
		}
		
		if($controller->request->header('X-NC-PAGE') || ($controller->request->params['plugin'] == '' && $controller->request->params['controller'] == 'pages')) {
			Configure::write(NC_SYSTEM_KEY.'.block_type', 'blocks');
		}
		

		/*
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
		} else if($user_id == 0 && !empty($mode)) {
			$this->Session->delete(NC_SYSTEM_KEY.'.mode');
		}
	}
/**
 * Module取得
 *
 * @param   Controller $controller Instantiating controller
 * @param   string  $url
 * @param   string  $dir_name
 * @param   integer $authority_id
 * @return  array($module_id, $system_flag) エラーの場合、false
 * @since   v 3.0.0.0
 */
	/*protected function _getModule(Controller $controller, $plugin_name, $authority_id) {
		$modules = array();
		if(isset($controller->request->params['modules'])) {
			$modules =  $controller->request->params['modules'];
			if(isset($modules[$plugin_name])) {
				return $modules[$plugin_name];
			}
		}
		//if($plugin_name == 'blocks' || $plugin_name == 'contents') {
		//	$module = $controller->Module->findByDirname($plugin_name);
		//} else {
			$module = $controller->Module->findByDirname($plugin_name, $authority_id);
		//}
		if(empty($module['Module'])) {
			return false;
		}
		$modules[$plugin_name] = $module;

		$controller->request->offsetSet('modules', $modules);

		return $module;
	}*/

/**
 * Page取得
 * <pre>
 * Page情報を取得する
 * </pre>
 *
 * @param   Controller $controller Instantiating controller
 * @param   string  $permalink
 * @param   integer $user_id
 * @param   string  $lang
 * @return  array $page エラーの場合、false
 * @since   v 3.0.0.0
 */
	protected function _getPage(Controller $controller, $permalink, $user_id, $lang) {
		$page = array();
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
		if(empty($user_id)) {
			$page_params = array(
				'fields' => array(
									'Page.*'
				)
			);
		} else {
			$page_params = array(
				'fields' => array(
									'Page.*,'.
									'Authority.hierarchy'
				),
				'joins' => array(
	                                     array("type" => "LEFT",
	                                           "table" => "pages_users_links",
	                                           "alias" => "PagesUsersLink",
	                                           "conditions" => "`Page`.`room_id`=`PagesUsersLink`.`room_id`".
	                                     		" AND `PagesUsersLink`.`user_id` =".$user_id
	                                          ),
	                                     array("type" => "LEFT",
	                                           "table" => "authorities",
	                                           "alias" => "Authority",
	                                           "conditions" => "`Authority`.id``=`PagesUsersLink`.`authority_id`"
	                                          )
	                            )
			);
		}
		if($permalink == "") {
			// トップページ
			$page_params['conditions'] = array(
				'Page.permalink' => '',
				'Page.position_flag' => _ON,
				'Page.space_type' => $space_type,
				'Page.display_sequence' => 1,
				'Page.root_sequence' => 1,
				'Page.thread_num' => 2
			);
		} else {
			$page_params['conditions'] = array(
				'Page.permalink' => $permalink,
				'Page.position_flag' => _ON,
				'Page.space_type' => $space_type,
				'Page.display_sequence !=' => 0
			);
		}

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
		}
		if(!isset($page)) {
			return false;
		}
		$page = $controller->Page->afterFindIds($page);
		return $page;
	}

/**
 * Block取得
 * <pre>
 * Page情報を取得する
 * </pre>
 *
 * @param   Controller $controller Instantiating controller
 * @param   integer $block_id
 * @param   integer $user_id
 * @return  array $block エラーの場合、false
 * @since   v 3.0.0.0
 */
	protected function _getBlock(Controller $controller, $block_id, $user_id) {
		$block =  $controller->Block->findByUserId($block_id, $user_id);
		if(!isset($block)) {
			return false;
		}
		$controller->nc_block = $block;
		return $block;
	}

/**
 * Urlの体裁を整える
 *
 * @param   string $url
 * @return  string $url
 * @since   v 3.0.0.0
 */
	public function formatUrl($url) {
		$url_len = strlen($url);
		// 最初の「/」を除去
		if(substr($url, 0, 1) == "/") {
			$url = substr($url, 1, $url_len);
			$url_len = strlen($url);
		}

		// 最後の「/」を除去
		if(substr($url, $url_len-1, $url_len) == '/') {
			$url = substr($url, 0, -1);
		}

		return $url;
	}
	
	protected function _setControllerPrams(Controller $controller) {
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

			if(!is_null($block['Block']['hierarchy'])) {
				$controller->hierarchy = intval($block['Block']['hierarchy']);
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
}