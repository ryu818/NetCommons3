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
	public $uses = array('Page.PageOperation');

/**
 * Component name
 *
 * @var array
 */
	public $components = array('Page.PageMenu');

/**
 * Helper name
 *
 * @var array
 */
	public $helpers = array('TimeZone');

/**
 * ページ追加 表示・登録
 * @param   integer   parent page_id or current page_id
 * @param   string    inner or bottom(追加) $type
 * @return  void
 * @since   v 3.0.0.0
 */
	public function add($current_page_id, $type) {
		$user_id = $this->Auth->user('id');
		$current_page_id = intval($current_page_id);
		$lang = Configure::read(NC_CONFIG_KEY.'.'.'language');

		$current_page = $this->Page->findAuthById($current_page_id, $user_id);

		if($current_page_id == 0 || !isset($current_page['Page'])) {
			$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenu/add.001', '400');
			return;
		}

		if($type != 'inner') {
			$parent_page = $this->Page->findAuthById($current_page['Page']['parent_id'], $user_id);
		} else {
			$parent_page = $current_page;
		}

		// デフォルトページ情報取得
		$page = $this->PageOperation->defaultPage($type, $current_page, $parent_page);
		if(!isset($page['Page'])) {
			$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenu/add.002', '400');
			return;
		}

		// 権限チェック
		$admin_hierarchy = $this->PageMenu->validatorPage($this->request, $current_page, $parent_page);
		if(!$admin_hierarchy) {
			return;
		}

		// Insert
		$this->Page->set($page);
		$this->Page->autoConvert = false;
		if(!$this->Page->save($page)) {
			$this->flash(__('Failed to register the database, (%s).', 'pages'), null, 'PageMenu/add.003', '400');
			return;
		}
		$page['Page']['id'] = $this->Page->id;

		// display_sequence インクリメント処理
		$fields = array('Page.display_sequence'=>'Page.display_sequence+1');
		$conditions = array(
			'Page.id !=' => $page['Page']['id'],
			'Page.root_id' => $page['Page']['root_id'],
			'Page.space_type' => $page['Page']['space_type'],
			'Page.lang' => array("", $lang),
			'Page.position_flag' => _ON,
			'Page.display_sequence >=' => $page['Page']['display_sequence']
		);
		$ret = $this->Page->updateAll($fields, $conditions);
		if(!$ret) {
			$this->flash(__('Failed to update the database, (%s).', 'pages'), null, 'PageMenu/add.004', '400');
			return;
		}

		$this->set('page', $page);
		//$this->set('parent_page', $parent_page);
		$this->set('admin_hierarchy', $admin_hierarchy);
	}

/**
 * ページ編集
 * @return  void
 * @since   v 3.0.0.0
 */
	public function edit() {
		$user_id = $this->Auth->user('id');
		$page = $this->request->data;
		$lang = Configure::read(NC_CONFIG_KEY.'.'.'language');
		$current_page = $this->Page->findAuthById($page['Page']['id'], $user_id);
		$is_detail = false;
		$is_error = false;

		if(!isset($current_page['Page'])) {
			$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenu/edit.001', '400');
			return;
		}
		$parent_page = $this->Page->findById($current_page['Page']['parent_id']);
		if(!isset($parent_page['Page'])) {
			$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenu/edit.002', '400');
			return;
		}

		// 権限チェック
		$admin_hierarchy = $this->PageMenu->validatorPage($this->request, $current_page);
		if(!$admin_hierarchy) {
			return;
		}

		if(isset($page['Page']['permalink'])) {
			// 詳細画面表示
			$is_detail = true;
		}

		if(isset($page['Page']['permalink'])) {
			$page['Page']['permalink'] = trim($page['Page']['permalink'], '/');
			$input_permalink = $page['Page']['permalink'];
			if($parent_page['Page']['permalink'] != '' && $current_page['Page']['display_sequence'] != 1) {
				$page['Page']['permalink'] = $parent_page['Page']['permalink'].'/'.$page['Page']['permalink'];
			}
		}
		if($page['Page']['display_flag'] == _ON) {
			// 既に公開ならば、公開日付fromを空にする
			$page['Page']['display_from_date'] = '';
		}
		$child_pages = $this->Page->findChilds('all', $current_page, $user_id);
		$fieldList = array('page_name', 'permalink', 'display_from_date', 'display_to_date', 'display_apply_subpage');
		foreach($fieldList as $field) {
			if(isset($page['Page'][$field])) {
				$current_page['Page'][$field] = $page['Page'][$field];
			}
		}
		$current_page['parentPage'] = $parent_page['Page'];
		$this->Page->set($current_page);

		// 編集ページ以下のページ取得
		$fetch_params = array(
			'active_page_id' => $current_page['Page']['id']
		);
		$thread_pages = $this->Page->afterFindMenu($child_pages, $fetch_params);

		if ($this->Page->validates(array('fieldList' => $fieldList))) {
			// 子供の更新処理
			// 現在のpage下のページすべてのpermalink更新処理
			// display_flagはここでは更新しない。
			// From公開日付は、親の日付で更新
			// To公開日付は、子供がセットしていないか、親よりも古い日付でならば、親の日付で更新
			$display_from_date = $current_page['Page']['display_from_date'];
			$display_to_date = $current_page['Page']['display_to_date'];
			foreach($child_pages as $key => $child_page) {
				$permalink_arr = explode('/', $child_page['Page']['permalink']);
				$upd_permalink = $current_page['Page']['permalink'] . '/' . $permalink_arr[count($permalink_arr)-1];

				$this->Page->create();
				$fieldChildList = array('permalink');

				$child_page['Page']['permalink'] = $upd_permalink;
				if(!empty($display_from_date) && $current_page['Page']['display_apply_subpage'] == _ON) {
					$child_page['Page']['display_from_date'] = $display_from_date;
					$fieldChildList['display_from_date'] = true;
				}
				if(!empty($display_from_date) && !empty($child_page['Page']['display_from_date']) &&
						strtotime($child_page['Page']['display_from_date']) < strtotime($display_from_date)) {
					$child_page['Page']['display_from_date'] = $display_from_date;
					$fieldChildList['display_from_date'] = true;
				}
				if(!empty($display_to_date)) {
					if(empty($child_page['Page']['display_to_date']) || strtotime($child_page['Page']['display_to_date']) > strtotime($display_to_date)) {
						$child_page['Page']['display_to_date'] = $display_to_date;
						$fieldChildList['display_to_date'] = true;
					}
				}
				//$this->Page->set($child_page);
				if(!$this->Page->save($child_page, true, $fieldChildList)) {
					$this->flash(__('Failed to update the database, (%s).', 'pages'), null, 'PageMenu/edit.003', '400');
					return;
				}
			}
			// 登録処理
			if (!$this->Page->save($page, false, $fieldList)) {
				$this->flash(__('Failed to update the database, (%s).', 'pages'), null, 'PageMenu/edit.004', '400');
				return;
			}
			$is_detail = false;
			$this->Session->setFlash(__('Has been successfully registered.'));
		}else {
			$is_error = true;
			//$this->Page->validationErrors;
		}

		if(isset($input_permalink)) {
			$current_page['Page']['permalink'] = $input_permalink;
		}
		$this->set('page', $current_page);
		$this->set('parent_page', $parent_page);
		$this->set('pages', $thread_pages);
		$this->set('admin_hierarchy', $admin_hierarchy);
		$this->set('is_detail', $is_detail);
		$this->set('is_error', $is_error);
	}

/**
 * ページ詳細設定表示
 * @param   integer   親page_id or カレントpage_id $page_id
 * @return  void
 * @since   v 3.0.0.0
 */
	public function detail($page_id) {
		$user_id = $this->Auth->user('id');
		$page = $this->Page->findAuthById($page_id, $user_id);

		// 権限チェック
		$admin_hierarchy = $this->PageMenu->validatorPage($this->request, $page);
		if(!$admin_hierarchy) {
			return;
		}

		$parent_page = $this->Page->findById($page['Page']['parent_id']);
		if(!isset($parent_page['Page'])) {
			$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenu/detail.002', '400');
			return;
		}

		$permalink_arr = explode('/', $page['Page']['permalink']);
		if(count($permalink_arr) > 0) {
			$page['Page']['permalink'] = $permalink_arr[count($permalink_arr) - 1];
		} else {
			$page['Page']['permalink'] = '';
		}

		$this->set('page', $page);
		$this->set('parent_page', $parent_page);
	}

/**
 * ページ詳細設定表示
 * @return  void
 * @since   v 3.0.0.0
 */
	public function display() {
		$user_id = $this->Auth->user('id');
		$page = $this->request->data;
		if(!isset($page['Page']['id']) || !isset($page['Page']['display_flag'])) {
			$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenu/display.001', '400');
			return;
		}

		$current_page = $this->Page->findAuthById($page['Page']['id'], $user_id);
		if(!isset($current_page['Page'])) {
			$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenu/display.002', '400');
			return;
		}

		// 権限チェック
		$admin_hierarchy = $this->PageMenu->validatorPage($this->request, $current_page);
		if(!$admin_hierarchy) {
			return;
		}

		// 更新処理
		$this->Page->id = $page['Page']['id'];
		if(!$this->Page->saveField('display_flag', $page['Page']['display_flag'])) {
			$this->flash(__('Failed to update the database, (%s).', 'pages'), null, 'PageMenu/display.003', '400');
			return;
		}

		$child_pages = $this->Page->findChilds('all', $current_page);
		// 子供の更新処理
		foreach($child_pages as $key => $child_page) {
			$this->Page->id = $child_page['Page']['id'];
			if(!$this->Page->saveField('display_flag', $page['Page']['display_flag'])) {
				$this->flash(__('Failed to update the database, (%s).', 'pages'), null, 'PageMenu/display.004', '400');
				return;
			}
		}

		// 正常終了
		$this->autoRender = false;
	}

/**
 * ページ削除
 * @return  void
 * @since   v 3.0.0.0
 */
	public function delete() {
		$user_id = $this->Auth->user('id');
		$page = $this->request->data;
		$lang = Configure::read(NC_CONFIG_KEY.'.'.'language');
		if(!isset($page['Page']['id'])) {
			$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenu/delete.001', '400');
			return;
		}

		$current_page = $this->Page->findAuthById($page['Page']['id'], $user_id);
		if(!isset($current_page['Page'])) {
			$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenu/delete.002', '400');
			return;
		}

		// 権限チェック
		$admin_hierarchy = $this->PageMenu->validatorPage($this->request, $current_page);
		if(!$admin_hierarchy) {
			return;
		}

		// 編集ページ以下のページ取得
		$child_pages = $this->Page->findChilds('all', $current_page, $user_id);

		foreach($child_pages as $child_page) {
			if(!$this->PageMenu->validatorPageDetail($this->request, $child_page)) {
				return;
			}
			// 削除処理
			if(!$this->Page->deletePage($child_page['Page']['id'], intval($page['all_delete']))) {
				$this->flash(__('Failed to delete the database, (%s).', 'pages'), null, 'PageMenu/delete.003', '400');
				return;
			}
		}
		// 削除処理
		if(!$this->Page->deletePage($current_page['Page']['id'], intval($page['all_delete']), count($child_pages))) {
			$this->flash(__('Failed to delete the database, (%s).', 'pages'), null, 'PageMenu/delete.004', '400');
			return;
		}
		$this->Session->setFlash(__('Has been successfully deleted.'));
		$this->render(false, 'ajax');
	}
}
