<?php
/**
 * PageMenuControllerクラス
 *
 * <pre>
 * ページ追加、編集、削除
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       App.Controller
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class PageMenuController extends PageAppController {
/**
 * page_id
 * @var integer
 */
	public $page_id = null;

/**
 * hierarchy
 * @var integer
 */
	public $hierarchy = null;

/**
 * Model name
 *
 * @var array
 */
	public $uses = array('Page.PageMenu');

/**
 * ページ追加 表示・登録
 * @param   integer   parent page_id or current page_id
 * @param   string    inner or bottom(追加) $type
 * @return  void
 * @since   v 3.0.0.0
 */
	public function add($current_page_id, $type) {
		$login_user = $this->Auth->user();
		$user_id = $login_user['id'];
		$current_page_id = intval($current_page_id);

		$current_page = $this->Page->findById($current_page_id);

		if($current_page_id == 0 || !isset($current_page['Page']) || !$this->request->is('post')) {
			$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenu/add.001', '400');
			return;
		}

		if($type != 'inner') {
			$parent_page = $this->Page->findByIds($current_page['Page']['parent_id'], $user_id);
		} else {
			$parent_page = $current_page;
		}

		$page = $this->PageMenu->defaultPage($type, $current_page, $parent_page);
		if(!isset($page['Page'])) {
			$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenu/add.002', '400');
			return;
		}
// TODO:権限チェック

		$this->Page->set($page);
		if(!$this->Page->save($page)) {
			$this->flash(__('Failed to insert the database, (%s).', 'pages'), null, 'PageMenu/add.003', '400');
			return;
		}
		$page['Page']['id'] = $this->Page->id;

		$admin_hierarchy = $this->ModuleSystemLink->findHierarchy(Inflector::camelize($this->request->params['plugin']), $login_user['authority_id']);
		$this->set('page', $page);
		//$this->set('parent_page', $parent_page);
		$this->set('admin_hierarchy', $admin_hierarchy);
	}

/**
 * ページ詳細設定表示
 * @param   integer   親page_id or カレントpage_id $page_id
 * @param   string    id名postfix $postfix
 * @return  void
 * @since   v 3.0.0.0
 */
	public function detail($page_id, $postfix = '') {
		$page = $this->Page->findById($page_id);
		if(!isset($page['Page'])) {
			$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenu/detail.001', '400');
			return;
		}
		$parent_page = $this->Page->findById($page['Page']['parent_id']);
		if(!isset($parent_page['Page'])) {
			$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenu/detail.002', '400');
			return;
		}
		$this->set('page', $page);
		$this->set('parent_page', $parent_page);
	}
}
