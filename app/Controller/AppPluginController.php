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
App::uses('AppController', 'Controller');
class AppPluginController extends AppController
{
/**
 * 編集画面か否か（セッティングモードONの場合の上部、編集ボタンをリンク先を変更するため）
 * Default:'' or 'edit' or 'style'
 * @var boolean
 */
	public $ncType = '';

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

		if(!empty($this->nc_block)) {
			$isError = false;
			if(!isset($this->nc_block['Content']['id'])) {
				$this->set('name', __('Content removed.'));
				$isError = true;
			} else if(!isset($this->nc_block['Module']['id']) &&
					isset($this->request->params['plugin']) && $this->request->params['plugin'] != 'group'
					 && $this->request->params['plugin'] != 'content' && $this->request->params['plugin'] != 'block') {
				$this->set('name', __('Module uninstalled.'));
				$isError = true;
			}
			if($isError) {
				$this->set('ncIsError' , true);
				$this->set('show_frame', isset($this->Frame) ? $this->Frame : true);
				$this->render("/Errors/block_error");
				return false;
			}
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

		if(isset($this->id) && !isset($this->viewVars['id'])) {
			$this->set('id', $this->id);
		}
		if(isset($this->block_id) && !isset($this->viewVars['block_id'])) {
			$this->set('block_id', $this->block_id);
		}
		if(isset($this->module_id) && !isset($this->viewVars['module_id'])) {
			$this->set('module_id', $this->module_id);
		}
		/*if(isset($this->request->params['block_id'])) {
			$this->set('block_id', $this->request->params['block_id']);
			$this->set('id', '_'.$this->request->params['block_id']);
		} else if(isset($this->request->params['module_id'])) {
			$this->set('module_id', $this->request->params['module_id']);
			$this->set('id', '_'.$this->request->params['module_id']);
		}*/
		if(!empty($this->nc_module) && !isset($this->viewVars['module'])) {
			$this->set('module', $this->nc_module);
		}
		if(!empty($this->nc_block)) {
			if(isset($this->nc_block['Block']) && $this->nc_block['Block']['temp_name'] != '') {
				$this->theme = $this->nc_block['Block']['temp_name'];
			}
			if(!isset($this->viewVars['block'])) {
				$this->set('block', $this->nc_block);
			}
		}
		if(!empty($this->nc_page) && !isset($this->viewVars['page'])) {
			$this->set('page', $this->nc_page);
		}
		if(isset($this->isChief) && !isset($this->viewVars['isChief'])) {
			$this->set('isChief', $this->isChief);
		}
		if(!isset($this->viewVars['block_hierarchy'])) {
			if(isset($this->blockHierarchy)) {
				$this->set('block_hierarchy', $this->blockHierarchy);
			} else {
				$this->set('block_hierarchy', null);
			}
		}
		$this->set('ncType', $this->ncType);
		$this->set('content_id', $this->content_id);
	}
}