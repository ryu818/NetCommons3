<?php
/**
 * PageMenuComponentクラス
 *
 * <pre>
 * ページ操作用コンポーネント
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       App.Plugin.Controller
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class PageMenuComponent extends Component {
/**
 * _controller
 *
 * @var Controller
 */
	protected $_controller = null;

/**
 * startup
 *
 * @param Controller $controller
 */
	public function startup(Controller $controller) {
		$this->_controller = $controller;
	}

/**
 * ページ追加・削除、編集、表示順変更等バリデータ処理
 *
 * @param  CakeRequest $request
 * @param  Page Model  $parent_page
 * @param  Page Model  $page
 * @return mixed $admin_hierarchy or false + flash
 * @access	public
 */
	public function validatorPage($request, $page = null, $parent_page = null) {
		$login_user = $this->_controller->Auth->user();
		$user_id = $login_user['id'];

		$admin_hierarchy = $this->_controller->ModuleSystemLink->findHierarchy(Inflector::camelize($request->params['plugin']), $login_user['authority_id']);
		if($admin_hierarchy <= NC_AUTH_GENERAL) {
			$this->_controller->flash(__('Forbidden permission to access the page.'), null, 'PageMenu.001', '403');
			return false;
		}

		if(is_null($page)) {
			return $admin_hierarchy;
		}
		if(!isset($page['Page'])) {
			$this->_controller->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenu.002', '400');
			return false;
		}

		$chk_page = isset($parent_page) ? $parent_page : $page;
		if(!$this->_controller->CheckAuth->chkAuth($chk_page['Authority']['hierarchy'], NC_AUTH_CHIEF)) {
			//if($user_id == 0) {
			//	$this->Session->setFlash(__('Forbidden permission to access the page.'), 'default', array(), 'auth');
			//}
			$this->_controller->flash(__('Forbidden permission to access the page.'), null, 'PageMenu.003', '403');
			return false;
		}

		switch($request->params['action']) {
			case 'add':
			case 'edit':
				if(!$request->is('post')) {
					$this->_controller->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenu.005', '400');
					return false;
				}
				if($page['Page']['thread_num'] <= 1) {
					//Top Nodeの編集は許さない
					$this->_controller->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenu.004', '400');
					return false;
				}
				break;
			case 'display':
				// 親がOFFならば変更を許さない。
				$parent_page = $this->_controller->Page->findById($page['Page']['parent_id']);
				if(!isset($parent_page['Page']) || $parent_page['Page']['display_flag'] != NC_DISPLAY_FLAG_ON) {
					$this->_controller->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenu.006', '400');
					return false;
				}
				if($page['Page']['thread_num'] <= 1 && $page['Page'] != NC_SPACE_TYPE_GROUP) {
					//コミュニティ以外のTop Nodeの編集は許さない
					$this->_controller->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenu.006', '400');
					return false;
				}
				break;
		}
		return $admin_hierarchy;
	}
}