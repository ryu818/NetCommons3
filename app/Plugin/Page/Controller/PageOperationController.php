<?php
/**
 * PageOperationControllerクラス
 *
 * <pre>
 * ページ移動、ショートカット作成、ペースト
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       App.Controller
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class PageOperationController extends PageAppController {
/**
 * page_id
 * @var integer
 */
	public $page_id = null;

/**
 * Component name
 *
 * @var array
 */
	public $components = array('Page.PageMenu', 'CheckAuth' => array('allowAuth' => NC_AUTH_CHIEF, 'checkOrder' => array("request", "url")));

/**
 * Model name
 * @var array
 */
	public $uses = array('TempData');

/**
 * ページコピー
 * @param   integer   copy_page_id リクエストを正にするため使用しない
 * @return  void
 * @since   v 3.0.0.0
 */
	public function copy() {
		$copy_page_id = $this->request->query['page_id'];

		$this->Session->write('Pages.'.'copy_page_id', $copy_page_id);
		$this->Session->setFlash(__d('page', 'Select the page to which the movement, create a shortcut, paste, Can you please run from the [%s].', __d('page', 'Other operations')));
		$this->render(false, 'ajax');
	}

/**
 * ページ移動
 * @param   integer   copy_page_id
 * @return  void
 * @since   v 3.0.0.0
 */
	public function move($copy_page_id) {
		$move_page_id = $this->request->query['page_id'];
		$is_confirm = isset($this->request->data['is_confirm']) ? intval($this->request->data['is_confirm']) : _OFF;

		$results = $this->PageMenu->operatePage($this->action, $is_confirm, $copy_page_id, $move_page_id);
		if($results === true) {
			// 確認メッセージ
			return;
		} else if(!$results) {
			echo $this->PageMenu->getErrorStr();
			$this->cancel();
			return;
		}
		$this->TempData->gc();

		// 正常終了
		$this->Session->setFlash(__('Has been successfully registered.'));
		echo "<script>$.PageMenu.reload(".$ins_pages[0]['Page']['id'].");</script>";
		$this->cancel();
	}

/**
 * ページ ショートカット作成
 * @param   integer   copy_page_id
 * @return  void
 * @since   v 3.0.0.0
 */
	public function shortcut($copy_page_id) {
		$move_page_id = $this->request->query['page_id'];
		$is_confirm = isset($this->request->data['is_confirm']) ? intval($this->request->data['is_confirm']) : _OFF;

		$results = $this->PageMenu->operatePage($this->action, $is_confirm, $copy_page_id, $move_page_id);
		if($results === true) {
			// 確認メッセージ
			return;
		} else if(!$results) {
			echo $this->PageMenu->getErrorStr();
			$this->cancel();
			return;
		}
		$this->TempData->gc();

		// 正常終了
		$this->Session->setFlash(__('Has been successfully registered.'));
		echo "<script>$.PageMenu.reload(".$ins_pages[0]['Page']['id'].");</script>";
		$this->cancel();
	}

/**
 * ページペースト
 * @param   integer   copy_page_id
 * @return  void
 * @since   v 3.0.0.0
 */
	public function paste($copy_page_id) {
		$user_id = $this->Auth->User('id');
		$move_page_id = $this->request->query['page_id'];
		$is_confirm = isset($this->request->data['is_confirm']) ? intval($this->request->data['is_confirm']) : _OFF;

		$results = $this->PageMenu->operatePage($this->action, $is_confirm, $copy_page_id, $move_page_id);
		if($results === true) {
			// 確認メッセージ
			return;
		} else if(!$results) {
			echo $this->PageMenu->getErrorStr();
			$this->cancel();
			return;
		}
		$this->TempData->gc();
		$hash_key = $this->PageMenu->getOperationKey($copy_page_id, $move_page_id);
		if($this->TempData->read($hash_key) !== false) {
			// 既に実行中
			$this->flash(__d('page', 'I\'m already running. Please try again at a later time.'), null, 'Page.paste.001', '200');
			return;
		}

		// ブロック処理開始
		list($copy_page_id_arr, $copy_pages, $ins_pages) = $results;

		$blocks = $this->Block->findByPageIds($copy_page_id_arr, $user_id, "");
		$total = count($blocks);
		if($total > 0) {
			$percent = 0;
			$page_num = 0;
			$total_page = count($copy_page_id);
			$pages_indexs = array();
			foreach($copy_pages as $key => $copy_page) {
				$pages_indexs[$copy_page['Page']['id']] = $key;
			}

			$count = 0;
			$pre_page_id = 0;
			foreach($blocks as $block) {
				$count++;
				if($block['Block']['page_id'] != $pre_page_id) {
					$pre_page_id = $block['Block']['page_id'];
					$page_num++;
				}
				if($block['Block']['title'] == "{X-CONTENT}") {
					$title = $block['Content']['title'];
				} else {
					$title = $block['Block']['title'];
				}
				$title .= ' - ' . $copy_pages[$pages_indexs[$block['Block']['page_id']]]['Page']['page_name'];
				$percent = floor(($count / $total)*100);
				$data = array(
					'percent' => $percent,
					'title' => $title,
					'total' => $total_page,
					'page_num' => $page_num
				);
				$this->TempData->write($hash_key, serialize($data));
				
				

				sleep(1);	// TODO:test
			}
			$this->TempData->destroy($hash_key);
		}

		// 正常終了
		$this->Session->setFlash(__('Has been successfully registered.'));
		echo "<script>$.PageMenu.reload(".$ins_pages[0]['Page']['id'].");</script>";
		$this->cancel();
	}

/**
 * ページ操作キャンセル
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function cancel() {
		$this->Session->delete('Pages.'.'copy_page_id');
		$this->render(false, 'ajax');
	}

/**
 * チェック処理 プログレスバーを表示するため
 * @param   integer   copy_page_id
 * @return  void
 * @since   v 3.0.0.0
 */
	public function check($copy_page_id) {
		$move_page_id = $this->request->query['page_id'];
		$hash_key = $this->PageMenu->getOperationKey($copy_page_id, $move_page_id);
		$data = $this->TempData->read($hash_key);
		if(!isset($data)) {
			$this->render(false, 'ajax');
			return;
		} else {
			$data = unserialize($data);
		}
		if($data['percent'] >= 100) {
			$this->TempData->destroy($hash_key);
		}
		$this->set('data', $data);
	}
}
