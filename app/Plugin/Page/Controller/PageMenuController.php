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
		if( $page['Page']['thread_num'] == 1) {
			// インクリメント処理
			if(!$this->Page->incrementRootDisplaySeq($page, 1, array('not' => array('Page.id' => $page['Page']['id'])))) {
				$this->flash(__('Failed to update the database, (%s).', 'pages'), null, 'PageMenu/add.004', '400');
				return;
			}
		} else if(!$this->Page->incrementDisplaySeq($page, 1)) {
			$this->flash(__('Failed to update the database, (%s).', 'pages'), null, 'PageMenu/add.005', '400');
			return;
		}
		/*$fields = array('Page.display_sequence'=>'Page.display_sequence+1');
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
		}*/

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
		if($current_page['Page']['thread_num'] == 2 && $current_page['Page']['display_sequence'] == 1) {
			// ページ名称のみ変更を許す
			$fieldList = array('page_name');
		} else {
			$fieldList = array('page_name', 'permalink', 'display_from_date', 'display_to_date', 'display_apply_subpage');
		}
		foreach($fieldList as $key => $field) {
			if(isset($page['Page'][$field])) {
				$current_page['Page'][$field] = $page['Page'][$field];
			} else {
				unset($fieldList[$key]);
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
			if(!$this->PageMenu->childsUpdate($child_pages, $current_page)) {
				$this->flash(__('Failed to update the database, (%s).', 'pages'), null, 'PageMenu/edit.003', '400');
				return;
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
		$this->Session->setFlash(__('Has been successfully registered.'));
		$this->render(false, 'ajax');
	}

/**
 * ページ削除
 * @return  void
 * @since   v 3.0.0.0
 */
	public function delete() {
		$user_id = $this->Auth->user('id');
		$page = $this->request->data;
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
		}

		// 削除処理
		foreach($child_pages as $child_page) {
			if(!$this->Page->deletePage($child_page['Page']['id'], intval($page['all_delete']))) {
				$this->flash(__('Failed to delete the database, (%s).', 'pages'), null, 'PageMenu/delete.003', '400');
				return;
			}
		}
		if(!$this->Page->deletePage($current_page['Page']['id'], intval($page['all_delete']), count($child_pages))) {
			$this->flash(__('Failed to delete the database, (%s).', 'pages'), null, 'PageMenu/delete.004', '400');
			return;
		}
		$this->Session->setFlash(__('Has been successfully deleted.'));
		$this->render(false, 'ajax');
	}

/**
 * ページ表示順変更
 * @return  void
 * @since   v 3.0.0.0
 */
	public function chgsequence() {
		$user_id = $this->Auth->user('id');
		$page = $this->request->data;
		$lang = Configure::read(NC_CONFIG_KEY.'.'.'language');
		$position = $this->request->data['position'];
		$is_confirm = isset($this->request->data['is_confirm']) ? intval($this->request->data['is_confirm']) : _OFF;
		if(!isset($page['Page']['id']) || !isset($page['DropPage']['id']) || $page['Page']['id'] == $page['DropPage']['id']) {
			$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenu/chgsequence.001', '400');
			return;
		}

		$drag_page = $this->Page->findAuthById($page['Page']['id'], $user_id);
		$drop_page = $this->Page->findAuthById($page['DropPage']['id'], $user_id);
		if(!isset($drag_page['Page']) || !isset($drop_page['Page'])) {
			$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenu/chgsequence.002', '400');
			return;
		}

		// 権限チェック
		$admin_hierarchy = $this->PageMenu->validatorPage($this->request, $drag_page);
		if(!$admin_hierarchy) {
			return;
		}

		// 表示順変更権限チェック
		$insert_parent_page = $this->PageMenu->validatorMovePage($this->request, $drag_page, $drop_page, $position);
		if($insert_parent_page === false) {
			return;
		}

		if($drop_page['Page']['id'] == $drop_page['Page']['room_id']) {
			$drop_room_name = $drop_page['Page']['page_name'];
		} else {
			$drop_room_name = $insert_parent_page['Page']['page_name'];
		}
		$drag_page_id_arr = array($drag_page['Page']['id']);
		if($drag_page['Page']['id'] == $drag_page['Page']['room_id']) {
			$drag_room_id_arr = array($drag_page['Page']['id']);
		}
		// 移動元ページ以下のページ取得
		$child_drag_pages = $this->Page->findChilds('all', $drag_page, $user_id);
		$last_drag_page = $drag_page;
		foreach($child_drag_pages as $child_page) {
			if(!$this->PageMenu->validatorPageDetail($this->request, $child_page, null, $admin_hierarchy)) {
				return;
			}
			$drag_page_id_arr[] = $child_page['Page']['id'];
			if($child_page['Page']['id'] == $child_page['Page']['room_id']) {
				$drag_room_id_arr = array($child_page['Page']['id']);
			}
			$last_drag_page = $child_page;
		}

		// 確認メッセージ表示
		if( !$is_confirm && $drag_page['Page']['room_id'] != $drop_page['Page']['room_id']) {
			// ページの移動で、移動元と移動先がちがうルームであっても、
			// 移動元が単一ページ（ルームではない）で、ブロックがはっていないならば、
			// 確認メッセージを出す必要性がない
			$buf_block = $this->Block->find('first', array(
				'recursive' => -1,
				'conditions' => array(
					'Block.page_id' => $drag_page_id_arr
				)
			));
			if(isset($buf_block['Block'])) {
				$echo_str = '<div class="info-message">';
				if(count($drag_room_id_arr) > 0) {
					// 子グループあり
					$echo_str .= __d('page', 'You are about to move to [%s]. When you move, block placement will be moved as a shortcut, the assignment of rights will be canceled. Are you sure?', $drop_room_name);
				} else {
					// 子グループなし
					$echo_str .= __d('page', 'You are about to move to [%s]. When you move, block placement will be moved as a shortcut. Are you sure?', $drop_room_name);
				}
				$echo_str .= '</div>';
				echo $echo_str;
				$this->render(false, 'ajax');
				return;
			}
		}

// TODO:test他ルームへの移動はまだ実装していないためエラーを出すように仮実装：後に削除
if($drag_page['Page']['room_id'] != $drop_page['Page']['room_id']) {
	$buf_block = $this->Block->find('first', array(
			'recursive' => -1,
			'conditions' => array(
					'Block.page_id' => $drag_page_id_arr
			)
	));
	if(isset($buf_block['Block'])) {
		echo "TODO:まだブロックがはってあるページにおける他ルームへの移動は実装されていません。移動できませんでした。";
		$this->render(false, 'ajax');
		return;
	}
}
// TODO:test End

		$child_drop_pages = $this->Page->findChilds('all', $drop_page, $user_id);
		//$drop_page = $this->Page->findAuthById($page['DropPage']['id'], $user_id);	// 「移動元の更新」で移動先が更新される場合があるため、再取得
		$drop_root_id = $drop_page['Page']['root_id'];
		$drop_thread_num = $drop_page['Page']['thread_num'];
		$drop_space_type = $drop_page['Page']['space_type'];
		$drop_parent_id = $drop_page['Page']['parent_id'];

		$drop_room_id = $insert_parent_page['Page']['room_id'];
		if($drag_page['Page']['thread_num'] == 1) {
			if($position == 'bottom') {
				$display_sequence = $drop_page['Page']['display_sequence'] + 1;
			} else {
				$display_sequence = $drop_page['Page']['display_sequence'] - 1;
				if($display_sequence == 0) {
					$display_sequence = 1;
				}
			}
		} else if($position == 'inner' || $position == 'bottom') {
			// ノード中のもっとも多いdisplay_sequenceのPageを取得
			$insert_page = $drop_page;
			if(count($child_drop_pages) > 0) {
				foreach($child_drop_pages as $child_drop_page) {
					if(in_array($child_drop_page['Page']['id'], $drag_page_id_arr)) {
						continue;
					}
					if($child_drop_page['Page']['display_sequence'] > $insert_page['Page']['display_sequence']) {
						$insert_page = $child_drop_page;
					}
				}
			}
			if($position == 'inner') {
				$drop_thread_num = $drop_page['Page']['thread_num'] + 1;
				$drop_parent_id = $drop_page['Page']['id'];
			}
			$display_sequence = $insert_page['Page']['display_sequence'] + 1;
		} else if($position == 'top') {
			// Dropノードの1つ前のpageを取得
			$conditions = array(
					'Page.root_id' => $drop_page['Page']['root_id'],
					'Page.position_flag' => $drop_page['Page']['position_flag'],
					'Page.display_sequence' => intval($drop_page['Page']['display_sequence']) - 1,
					'Page.lang' => array('', $lang)
			);
			$insert_page = $this->Page->find('first', array('conditions' => $conditions));
			$display_sequence = $insert_page['Page']['display_sequence'] + 1;
		}

		// 登録処理
		$currentFieldList = array();
		$permalink_arr = explode('/', $drag_page['Page']['permalink']);
		if($insert_parent_page['Page']['permalink'] != '') {
			$permalink = $insert_parent_page['Page']['permalink'] . '/' . $permalink_arr[count($permalink_arr)-1];
		} else {
			$permalink = $permalink_arr[count($permalink_arr)-1];
		}
		if($permalink != $drag_page['Page']['permalink']) {
			$drag_page['Page']['permalink'] = $permalink;
			$currentFieldList[] = 'permalink';
		}
		if($drop_parent_id != $drag_page['Page']['parent_id']) {
			$drag_page['Page']['parent_id'] = $drop_parent_id;
			$currentFieldList[] = 'parent_id'; //$drop_parent_id;
		}

		$fields = array();
		if($drag_page['Page']['thread_num'] != 1) {
			if($display_sequence != $drag_page['Page']['display_sequence']) {
				$fields['Page.display_sequence'] = 'Page.display_sequence+('.($display_sequence - $drag_page['Page']['display_sequence']).')';
			}
			if($drop_thread_num != $drag_page['Page']['thread_num']) {
				$fields['Page.thread_num'] = 'Page.thread_num+('.($drop_thread_num - $drag_page['Page']['thread_num']).')';
				$drag_page['Page']['thread_num'] = $insert_parent_page['Page']['thread_num'] + 1;	// バリデートで使用するため
			}
			if($drop_root_id != $drag_page['Page']['root_id']) {
				$fields['Page.root_id'] = $drop_root_id;
			}
			if($drop_space_type != $drag_page['Page']['space_type']) {
				$fields['Page.space_type'] = $drop_space_type;
				$drag_page['Page']['space_type'] = $drop_space_type;	// バリデートで使用するため
			}
			if($drop_room_id != $drag_page['Page']['room_id']) {
				$fields['Page.room_id'] = $drop_room_id;
			}
		} else if($display_sequence != $drag_page['Page']['display_sequence']) {
			$drag_page['Page']['display_sequence'] = $display_sequence;
			$currentFieldList[] = 'display_sequence';
		}
		if(count($currentFieldList) > 0) {
			if(!$this->Page->save($drag_page, true, $currentFieldList)) {
				$error = '';
				foreach($this->Page->validationErrors as $field => $errors) {
					if($field == 'permalink') {
						$error .= __('Permalink'). ':';
					}
					$error .= $errors[0];	// 最初の１つめ
				}
				echo $error;
				$this->render(false, 'ajax');
				return;
			}
		}

		if(count($fields) > 0) {
			$conditions = array(
				'Page.id' => $drag_page_id_arr
			);
			$ret = $this->Page->updateAll($fields, $conditions);
			if(!$ret) {
				$this->flash(__('Failed to update the database, (%s).', 'pages'), null, 'PageMenu/chgsequence.003', '400');
				return;
			}

			// カレント、子供、固定リンク,公開日付の更新
			if(!$this->PageMenu->childsUpdate($child_drag_pages, $drag_page, $insert_parent_page)) {
				$this->flash(__('Failed to update the database, (%s).', 'pages'), null, 'PageMenu/chgsequence.004', '400');
				return;
			}
		}

		/*
		 * 移動元と移動先を更新後、移動ノードを更新
		 */

		/*
		 * 移動先を更新
		 */

		//$display_sequence = $insert_page['Page']['display_sequence'];
		if( $drag_page['Page']['thread_num'] == 1) {
			// インクリメント処理
			if(!$this->Page->incrementRootDisplaySeq($drop_page, 1, array('not' => array('Page.id' => $drag_page['Page']['id'])))) {
				$this->flash(__('Failed to update the database, (%s).', 'pages'), null, 'PageMenu/chgsequence.005', '400');
				return;
			}
			// 移動元 前詰め処理
			if(!$this->Page->decrementRootDisplaySeq($drag_page, 1, array('not' => array('Page.id' => array($drop_page['Page']['id'], $drag_page['Page']['id']))))) {
				$this->flash(__('Failed to update the database, (%s).', 'pages'), null, 'PageMenu/chgsequence.006', '400');
				return;
			}
		} else {
			// インクリメント処理
			if(!$this->Page->incrementDisplaySeq($insert_page, count($child_drag_pages) + 1, array('not' => array('Page.id' => $drag_page_id_arr)))) {
				$this->flash(__('Failed to update the database, (%s).', 'pages'), null, 'PageMenu/chgsequence.007', '400');
				return;
			}

			// 移動元 前詰め処理
			if(!$this->Page->decrementDisplaySeq($last_drag_page, count($child_drag_pages) + 1)) {	//, array('not' => array('Page.id' => $drag_page_id_arr))
				$this->flash(__('Failed to update the database, (%s).', 'pages'), null, 'PageMenu/chgsequence.008', '400');
				return;
			}
		}
		//
		// TODO:異なるルームへの移動
		//
		if($drop_room_id != $drag_page['Page']['room_id']) {
			// 未実装
		}

		$this->Session->setFlash(__('Has been successfully updated.'));
		$this->render(false, 'ajax');
	}
}
