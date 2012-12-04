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

		if($page['Page']['position_flag'] != _ON) {
			$this->_controller->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenu.004', '400');
			return false;
		}

		if(!$this->validatorPageDetail($request, $page, $parent_page)) {
			return false;
		}

		return $admin_hierarchy;
	}

	/**
	 * ページ追加・削除、編集、表示順変更等バリデータ処理(詳細)
	 *
	 * @param  CakeRequest $request
	 * @param  Page Model  $parent_page
	 * @param  Page Model  $page
	 * @return boolean
	 * @access	public
	 */
	public function validatorPageDetail($request, $page = null, $parent_page = null) {
		switch($request->params['action']) {
			case 'add':
			case 'edit':
				if(!$request->is('post')) {
					$this->_controller->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenu.005', '400');
					return false;
				}
				if($page['Page']['thread_num'] <= 1) {
					//Top Nodeの編集は許さない
					$this->_controller->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenu.006', '400');
					return false;
				}
				break;
			case 'display':
				// 親がOFFならば変更を許さない。
				$parent_page = $this->_controller->Page->findById($page['Page']['parent_id']);
				if(!isset($parent_page['Page']) || $parent_page['Page']['display_flag'] != NC_DISPLAY_FLAG_ON) {
					$this->_controller->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenu.007', '400');
					return false;
				}
				if($page['Page']['thread_num'] <= 1 && $page['Page'] != NC_SPACE_TYPE_GROUP) {
					//コミュニティ以外のTop Nodeの編集は許さない
					$this->_controller->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenu.008', '400');
					return false;
				}
				break;
			case 'delete':
				// 各スペースタイプのトップページは最低１つ以上ないと削除不可。
				if ($page['Page']['display_sequence'] == 1) {
					// 該当ルームのTopページの数を取得
					$conditions = array(
							'Page.room_id' => $page['Page']['room_id'],
							'Page.display_sequence' => $page['Page']['display_sequence'],
							'Page.position_flag' => $page['Page']['position_flag']
					);
					$count = $this->_controller->Page->find('count', array('conditions' => $conditions));

					if($count == 1) {
						echo __d('page', 'Top of each page is required at least one page. <br />You can\'t delete.');
						$this->_controller->render(false, 'ajax');
						return false;
					}

					// トップページを削除する場合、本ルームのページをすべて削除後でなければ削除不可。
					$conditions = array(
							'Page.room_id' => $page['Page']['room_id'],
							'Page.position_flag' => $page['Page']['position_flag'],
							'Page.display_sequence >' => 1,
							'Page.lang' => array('', $lang)
					);
					$count = $this->_controller->Page->find('count', array('conditions' => $conditions));
					if($count >= 1) {
						echo __d('page', 'If you want to delete the top page, please run after deleting all the pages of this room.<br />You can\'t delete.');
						$this->_controller->render(false, 'ajax');
						return false;
					}
				}
				// ページロックしてあれば、削除不可。
				if($page['Page']['lock_authority_id'] > 0) {
					$page['Page'] = $this->_controller->Page->setPageName($page['Page']);
					echo __d('page', 'Because the [%s] page is locked, I can\'t be deleted. Please run after unlock the page.', $page['Page']['page_name']);
					$this->_controller->render(false, 'ajax');
					return false;
				}
				break;
		}
		return true;
	}
}