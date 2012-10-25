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
		$this->set('content', '');	// 初期化
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
		if(isset($this->request->params['block'])) {
			$this->set('block', $this->request->params['block']);
		}
		if(isset($this->request->params['page'])) {
			$this->set('page', $this->request->params['page']);
		}
		$this->set('block_type', Configure::read(NC_SYSTEM_KEY.'.block_type'));
	}
}