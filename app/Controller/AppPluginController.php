<?php
/**
 * AppPluginsControllerクラス
 *
 * <pre>
 * プラグイン（モジュール）のAppControllerクラス
 * ブロックテンプレートとブロックテーマつきで表示する
 *
 * public $view  = 'Theme';つきでpluginを作成するとnc/view/themed/xxxx/にファイルを
 * 置く必要があるが、plugin単位で完全に分離したいため、nc/plugins/(plugin)/view/(theme)/下にまとめるようにする。
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       App.Controller
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class AppPluginController extends AppController
{
/**
 * 編集画面か否か（セッティングモードONの場合の上部、編集ボタンをリンク先を変更するため）
 * Default:false
 * @var boolean
 */
	public $is_edit = false;

	public $viewClass = 'Plugin';

	public $uses = array();
/**
 * モジュール（プラグイン）の実行前処理
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function beforeFilter()
	{
		parent::beforeFilter();
	}

/**
 * Perform the startup process for this controller.
 * Fire the Components and Controller callbacks in the correct order.
 *
 * - Initializes components, which fires their `initialize` callback
 * - Calls the controller `beforeFilter`.
 * - triggers Component `startup` methods.
 * ブロックのショートカットを同じルーム内にはり、「完全に削除」した場合、
 * ショートカット元のコンテンツが表示できないため、エラーを表示するように修正。
 *
 * @return void
 */
	public function startupProcess() {

		$this->getEventManager()->dispatch(new CakeEvent('Controller.initialize', $this));
		$this->getEventManager()->dispatch(new CakeEvent('Controller.startup', $this));

		if(isset($this->nc_block) && !isset($this->nc_block['Content']['id'])) {
			// Contentテーブルにデータなし
			$this->set('nc_error_flag' , true);
			$this->set('show_frame', isset($this->Frame) ? $this->Frame : true);
			$this->set('name', __('Content removed.'));
			$this->render("/Errors/block_error");
			return false;
		}
	}

/**
 * モジュール（プラグイン）の表示前処理
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function beforeRender()
	{
		parent::beforeRender();

		if(isset($this->request->params['block_id'])) {
			$this->set('block_id', $this->request->params['block_id']);
			$this->set('id', '_'.$this->request->params['block_id']);
		} else if(isset($this->request->params['module_id'])) {
			$this->set('block_id', $this->request->params['module_id']);
			$this->set('id', '_'.$this->request->params['module_id']);
		}
		if(isset($this->nc_block) && !isset($this->viewVars['block'])) {
			$this->set('block', $this->nc_block);
		}
		if(isset($this->nc_page) && !isset($this->viewVars['page'])) {
			$this->set('page', $this->nc_page);
		}
		if(isset($this->hierarchy) && $this->hierarchy >= NC_AUTH_MIN_CHIEF) {
			$this->set('is_chief', _ON);
		} else {
			$this->set('is_chief', _OFF);
		}
		$this->set('is_edit', $this->is_edit);
		$this->set('content_id', $this->content_id);
	}
}