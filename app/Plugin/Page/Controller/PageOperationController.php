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
	public $uses = array('TempData', 'Block.BlockOperation', 'Module');

/**
 * 表示前処理
 * <pre>
 * 	ページメニューの言語切替の値を選択言語としてセット
 * </pre>
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function beforeFilter()
	{
		$active_lang = $this->Session->read(NC_SYSTEM_KEY.'.page_menu.lang');
		if(isset($active_lang)) {
			Configure::write(NC_CONFIG_KEY.'.'.'language', $active_lang);
			$this->Session->write(NC_CONFIG_KEY.'.language', $active_lang);
		}
		parent::beforeFilter();

		include_once dirname(dirname(__FILE__)).'/Config/defines.inc.php';
		set_time_limit(PAGES_OPERATION_TIME_LIMIT);
		// メモリ最大サイズ設定
		ini_set('memory_limit', PAGES_OPERATION_MEMORY_LIMIT);
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
		$pre_lang = $this->Session->read(NC_SYSTEM_KEY.'.page_menu.pre_lang');
		if(isset($pre_lang)) {
			Configure::write(NC_CONFIG_KEY.'.'.'language', $pre_lang);
			$this->Session->write(NC_CONFIG_KEY.'.language', $pre_lang);
		}
	}

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
		/*$move_page_id = $this->request->query['page_id'];
		$is_confirm = isset($this->request->data['is_confirm']) ? intval($this->request->data['is_confirm']) : _OFF;

		$results = $this->PageMenu->operatePage($this->action, $is_confirm, $copy_page_id, $move_page_id);
		if($results === true) {
			// 確認メッセージ
			return;
		} else if(!$results) {
			echo $this->PageMenu->getErrorStr();
			$this->cancel();
			return;
		}*/
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
		$this->paste($copy_page_id);
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
		$shortcut_flag = isset($this->request->data['shortcut_flag']) ? intval($this->request->data['shortcut_flag']) : null;
		if($this->action == "shortcut" && is_null($shortcut_flag)) {
			$shortcut_flag = _OFF;
		}
		$this->TempData->gc();

		$hash_key = $this->PageMenu->getOperationKey($copy_page_id, $move_page_id);
		if($this->TempData->read($hash_key) !== false) {
			// 既に実行中
			$this->flash(__d('page', 'I\'m already running. Please try again at a later time.'), null, 'PageOperation.'.$this->action.'.001', '200');
			return;
		}

		// 確認メッセージ表示、ページ処理開始
		$results = $this->PageMenu->operatePage($this->action, $is_confirm, $copy_page_id, $move_page_id);
		if($results === true) {
			// 確認メッセージ
			return;
		} else if(!$results) {
			echo $this->PageMenu->getErrorStr();
			$this->cancel();
			return;
		}

		// ブロック処理開始
		list($copy_page_id_arr, $copy_pages, $ins_pages) = $results;

		if(!$this->PageMenu->operateBlock($hash_key, $user_id, $copy_page_id_arr, $copy_pages, $ins_pages, $shortcut_flag)) {
			$this->flash(__('Failed to execute the %s.', __('Paste')), null, 'PageOperation.'.$this->action.'.002', '500');
			return;
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
