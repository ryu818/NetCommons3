<?php
/**
 * BlockOperationControllerクラス
 *
 * <pre>
 * ブロック操作（コピー、ショートカット作成、ペースト）
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       App.Controller
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class BlockOperationController extends BlockAppController {

/**
 * Component name
 *
 * @var array
 */
	public $components = array('CheckAuth' => array('allowAuth' => NC_AUTH_CHIEF, 'chkMovedPermanently' => false, 'checkOrder' => array("request", "url")));

/**
 * Model Block Content Module
 *
 * @var integer
 */
	public $nc_block = null;

/**
 * ブロックコピー
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function copy() {
		$user_id = $this->Auth->user('id');
		$block_id = $this->nc_block['Block']['id'];
		$content_id = $this->nc_block['Content']['id'];
		$content = $this->nc_block;

		/*TODO:後に削除
		 * if(!$this->nc_block['Content']['is_master']) {
			// ショートカット
			// ショートカットのコピーは、ショートカット元のコンテンツが主担以上であれば、許す。
			// そうでなければショートカットのコピーは認めない。
			$master_content = $this->Content->findAuthById($this->nc_block['Content']['master_id'], $user_id);
			if(isset($master_content['Content'])) {
				$content_id = $master_content['Content']['id'];
				$content = $master_content;
			} else {
				// error
				echo __d('block','Because an origin of contents is deleted, You can\'t copy it.');
				$this->render(false, 'ajax');
				return;
			}
			if($master_content['Authority']['hierarchy'] < NC_AUTH_MIN_CHIEF) {
				echo __d('block','Because there is not the room editing authority of the origin of contents, You can\'t copy it.');
				$this->render(false, 'ajax');
				return;
			}
		}*/

		/* TODO:lock_authority_idが入力されているロックされたブロックはコピー不可とする */

		$this->Session->write('Blocks.'.'copy_block_id', $block_id);
		$this->Session->write('Blocks.'.'copy_content_id', $content_id);

		$this->set('copy_content', $content);

		$this->render('Elements/copy', 'ajax');

	}

/**
 * ブロックショートカット作成
 * <pre>
 * ・ショートカットブロックは、メールの設定で送信される会員はあくまでもコンテンツ元のルームに参加している会員で、貼りつけた先のルームの影響を受けない。
 * 		TODO:master_idをみて、送信先をSelectする必要がある。
 * ・権限を付与して貼り付けるかどうかを設定可能とする。
 * </pre>
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function shortcut() {
		$user_id = $this->Auth->user('id');
		$block = array('Block' => $this->nc_block['Block']);	// 移動元Block
		$content = array('Content' => $this->nc_block['Content']);
		$module = array('Module' => $this->nc_block['Module']);
		$page = $this->nc_page;	// 移動先Page
		$pre_page = $this->Page->findAuthById(intval($block['Block']['page_id']), $user_id);

		$shortcut_flag = isset($this->request->data['shortcut_flag']) ? _ON : _OFF;
		if($pre_page['Page']['room_id'] != $content['Content']['room_id']) {
			// ショートカット
			$shortcut_flag = _OFF;
		} else if(!$content['Content']['is_master']) {
			// 権限が付与されたショートカット
			$shortcut_flag = _ON;
		}

		if(!$this->validatorRequest($this->request, $module['Module']['dir_name'])) {
			$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'BlockOperation.shortcut.001', '403');
			return;
		}

		if($block['Block']['module_id'] == 0) {
			// グループのショートカットは許さない。
			$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'BlockOperation.shortcut.002', '500');
			return;
		}

		// 確認メッセージ表示
		if(!$this->showConfirm($this->request, $content, $page, $pre_page)) {
			return;
		}

		// ショートカット処理
		$url = array(
			'plugin' => 'block',
			'controller' => 'block',
			'action' => 'add_block',
			'block_id' => $block['Block']['id']
		);
		$params = array(
			'data' => array(
				'module_id' => $block['Block']['module_id'],
				'show_count' => $this->request->data['show_count'],
				'page_id' => $page['Page']['id'],
				'shortcut_flag' => $shortcut_flag
			),
			'return'
		);
		$ret_add_block = $this->requestAction($url, $params);
		$add_block =  $this->Block->findAuthById(intval($ret_add_block), $user_id, false);
		if(!isset($add_block['Block'])) {
			$this->flash(__('The server encountered an internal error and was unable to complete your request.'), null, 'BlockOperation.shortcut.003', '500');
			return;
		}
		$to_block = array('Block' => $add_block['Block']);	// 移動元Block
		$to_content = array('Content' => $add_block['Content']);

		/* args
		 * @param   array 移動元ブロック   $block
		 * @param   array 移動先ブロック   $block
		 * @param   array 移動元コンテンツ $content
		 * @param   array 移動先コンテンツ $content
		 * @param   array 移動元ページ     $page
		 * @param   array 移動先ページ     $page
		 */
		$args = array(
			$block,
			$add_block,
			$pre_page,
			$page
		);
		if(!$this->operationAction($module['Module']['dir_name'], $args)) {
			$this->flash(__d('block', 'Failed to execute the %s.', __('Create shortcut')), null, 'BlockOperation.shortcut.004', '500');
			return;
		}

		$this->cancel();
	}

/**
 * ブロックペースト(コピー実行)
 *
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function paste() {
		$user_id = $this->Auth->user('id');
		$block = array('Block' => $this->nc_block['Block']);	// 移動元Block
		$content = array('Content' => $this->nc_block['Content']);
		$module = array('Module' => $this->nc_block['Module']);
		$page = $this->nc_page;	// 移動先Page
		$pre_page = $this->Page->findAuthById(intval($block['Block']['page_id']), $user_id);

		// ショートカットのペーストはショートカット作成と同意
		$room_id = $content['Content']['room_id'];
		if($pre_page['Page']['room_id'] != $room_id || !$content['Content']['is_master']) {
			$this->action = 'shortcut';
			$this->shortcut();
			return;
		}

		if(!$this->validatorRequest($this->request, $module['Module']['dir_name'])) {
			$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'BlockOperation.paste.001', '403');
			return;
		}

		if($block['Block']['module_id'] == 0) {
			// グループのコピーは許さない。
			$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'BlockOperation.paste.002', '500');
			return;
		}

		// 確認メッセージ表示
		if(!$this->showConfirm($this->request, $content, $page, $pre_page)) {
			return;
		}

		// ペースト処理
		$url = array(
			'plugin' => 'block',
			'controller' => 'block',
			'action' => 'add_block',
			'block_id' => $block['Block']['id']
		);
		$params = array(
			'data' => array(
				'module_id' => $block['Block']['module_id'],
				'show_count' => $this->request->data['show_count'],
				'page_id' => $page['Page']['id']
			),
			'return'
		);
		$ret_add_block = $this->requestAction($url, $params);
		$add_block =  $this->Block->findAuthById(intval($ret_add_block), $user_id, false);
		if(!isset($add_block['Block'])) {
			$this->flash(__('The server encountered an internal error and was unable to complete your request.'), null, 'BlockOperation.paste.003', '500');
			return;
		}
		$to_block = array('Block' => $add_block['Block']);	// 移動元Block
		$to_content = array('Content' => $add_block['Content']);

		/* args
		 * @param   array 移動元ブロック   $block
		 * @param   array 移動先ブロック   $block
		 * @param   array 移動元コンテンツ $content
		 * @param   array 移動先コンテンツ $content
		 * @param   array 移動元ページ     $page
		 * @param   array 移動先ページ     $page
		 */
		$args = array(
			$block,
			$to_block,
			$content,
			$to_content,
			$pre_page,
			$page
		);
		if(!$this->operationAction($module['Module']['dir_name'], $args)) {
			$this->flash(__d('block', 'Failed to execute the %s.', __('Paste')), null, 'BlockOperation.paste.004', '500');
			return;
		}

		$this->cancel();
	}

/**
 * ブロック移動
 * <pre>
 * ・ショートカットブロックの移動は、ショートカットのままで、移動先へ
 * ・移動先のコンテンツとして更新する。
 * ・
 * </pre>
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function move() {
		$user_id = $this->Auth->user('id');
		$block = array('Block' => $this->nc_block['Block']);	// 移動元Block
		$content = array('Content' => $this->nc_block['Content']);
		$module = array('Module' => $this->nc_block['Module']);
		$page = $this->nc_page;	// 移動先Page
		$pre_page = $this->Page->findAuthById(intval($block['Block']['page_id']), $user_id);

		if(!$this->validatorRequest($this->request, $module['Module']['dir_name'])) {
			$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'BlockOperation.move.001', '403');
			return;
		}

		// 確認メッセージ表示
		if(!$this->showConfirm($this->request, $content, $page, $pre_page)) {
			return;
		}

		// 移動処理
		$url = array(
			'plugin' => 'block',
			'controller' => 'block',
			'action' => 'insert_row',
			'block_id' => $block['Block']['id']
		);
		$params = array(
			'data' => array(
				'parent_id' => 0,
				'col_num' => 1,
				'row_num' => 1,
				'show_count' => $this->request->data['show_count'],
				'page_id' => $page['Page']['id']
			),
			'return'
		);
		if($this->requestAction($url, $params) != 'true') {
			$this->flash(__('The server encountered an internal error and was unable to complete your request.'), null, 'BlockOperation.move.002', '500');
			return;
		}

		/* args
		 * @param   array 移動元ブロック   $block
		 * @param   array 移動元コンテンツ $content
		 * @param   array 移動元ページ     $page
		 * @param   array 移動先ページ     $page
		 */
		$args =array(
			$block,
			$content,
			$pre_page,
			$page
		);
		if(!$this->operationAction($module['Module']['dir_name'], $args)) {
			$this->flash(__d('block', 'Failed to execute the %s.', __('Move')), null, 'BlockOperation.move.003', '500');
			return;
		}

		$this->cancel();
	}

/**
 * ブロック操作キャンセル
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function cancel() {
		$this->Session->delete('Blocks.'.'copy_block_id');
		$this->Session->delete('Blocks.'.'copy_content_id');

		echo 'true';
		$this->render(false);
	}

/**
 * ブロック操作バリデータ
 * @param  CakeRequest $request
 * @param  string      $dir_name
 * @return boolean
 * @since   v 3.0.0.0
 */
	protected function validatorRequest($request, $dir_name) {
		if (!isset($request->data) || !isset($request->data['show_count']) || !isset($request->data['page_id'])) {
			// Error
			return false;
		}

		$block_id = $this->block_id;
		$copy_block_id = intval($this->Session->read('Blocks.'.'copy_block_id'));
		$copy_content_id = intval($this->Session->read('Blocks.'.'copy_content_id'));

		if(empty($copy_block_id) || $block_id != $copy_block_id || !isset($this->nc_block['Block']) || $this->nc_block['Block']['content_id'] != $copy_content_id) {
			return false;
		}

		// TODO:module_linksで移動先ルームに貼り付けることができるかどうか確認する。

		App::uses($dir_name.'OperationsComponent', 'Plugin/'.$dir_name.'/Controller/Component');
		$class_name = $dir_name.'OperationsComponent';
		if(!class_exists($class_name) || !method_exists($class_name, $this->action)) {
			// ショートカットと移動は関数がなくてもエラーとしない
			if($this->action != 'shortcut' && $this->action != 'move') {
				return false;
			}
		}

		return true;
	}

/**
 * ブロック操作バリデータ
 * @param  CakeRequest $request
 * @param  integer     $room_id	コンテンツ元room_id
 * @param  Model Page  $move_page 移動先（コピー先、ショートカット先）Page
 * @param  Model Page  $pre_page 移動元（コピー元、ショートカット元）Page
 * @return boolean     falseならば確認メッセージ表示
 * @since   v 3.0.0.0
 */
	protected function showConfirm($request, $content, $move_page, $pre_page = null) {
		$room_id = $content['Content']['room_id'];
		$move_room_id = $move_page['Page']['room_id'];
		$is_confirm = isset($request->data['is_confirm']) ? intval($request->data['is_confirm']) : _OFF;
		if( !$is_confirm && $room_id != $move_room_id) {
			if($pre_page['Page']['room_id'] == $move_room_id) {
				// 移動元と移動先が同じならば表示しない
				return true;
			}
			// 権限つきのショートカットの場合、元のルームへの操作でないかどうかチェックし、
			// 元のルームならば確認を出さない。
			/*if(!$content['Content']['is_master']) {
				$master_content = $this->Content->findById($content['Content']['master_id']);
				if( $move_room_id == $master_content['Content']['room_id']) {
					return true;
				}
			}*/

			// 移動元と移動先がちがうならば確認メッセージを表示
			$echo_str = '<div>';
			switch($this->action) {
				case 'move':
					// ルーム名取得
					if($pre_page['Page']['room_id'] != $room_id || !$content['Content']['is_master']) {
						// ショートカット
						$echo_str .= __d('block','You move a block to [%s]. Are you sure?', $move_page['Page']['page_name']);
					} else {
						$user_id = $this->Auth->user('id');
						$insert_room = $this->Page->findAuthById($move_room_id, $user_id);
						$room_name = $insert_room['Page']['page_name'];
						$echo_str .= __d('block','You move a block to [%s].<br />When you move, it becomes contents of [%s].Are you sure?', $move_page['Page']['page_name'], $room_name);
					}
					break;
				case 'paste':
					$echo_str .= __d('block','You create a copy to [%s]. Are you sure?', $move_page['Page']['page_name']);
					break;
				case 'shortcut':
					$echo_str .= __d('block','You create a shortcut to [%s]. Are you sure?', $move_page['Page']['page_name']);
					break;
			}
			$echo_str .= '</div>';

			if($this->action == 'shortcut' && $pre_page['Page']['room_id'] == $room_id && $content['Content']['is_master']) {
				// コピー元がショートカットではないならば、チェックボックス表示
				$echo_str .= '<label class="nc-block-confirm-shortcut" for="nc-block-confirm-shortcut">'.
					'<input id="nc-block-confirm-shortcut" type="checkbox" name="shortcut_flag" value="'._ON.'" />&nbsp;'.
					__d('block','Allow the room authority to view and edit.').
					'</label>';
			}

			echo $echo_str;
			$this->render(false, 'ajax');
			return false;
		}
		return true;
	}

/**
 * ブロック操作関数を実行
 * @param  string      $plugin
 * @param  string      $name	shortcut or move or paste
 * @param  string      $title
 * @return array       $args
 * @since   v 3.0.0.0
 */
	protected function operationAction($dir_name, $args) {
		$class_name = $dir_name.'OperationsComponent';
		if(!class_exists($class_name) || !method_exists($class_name, $this->action)) {
			return true;
		}

		eval('$class = new '.$class_name.'();');
		$class->startup();

		$ret = call_user_func_array(array($class, $this->action), $args);
		if(!$ret) {
			return false;
		}
		return true;
	}
}