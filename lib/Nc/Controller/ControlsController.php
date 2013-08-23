<?php
/**
 * ControlsControllerクラス
 *
 * <pre>
 * コントロールパネル
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       App.Controller
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */

App::uses('AppController', 'Controller');

/**
 * Static content controller
 *
 * Override this controller by placing a copy in controllers directory of an application
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */
class ControlsController extends AppController {

/**
 * Controller name
 *
 * @var string
 */
	public $name = 'Controls';

/**
 * Component name
 *
 * @var array
 */
	public $components = array('CheckAuth' => array('allowUserAuth' => NC_AUTH_GUEST));
	
/**
 * コントロールパネル表示
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function index() {
		$user = $this->Session->read(NC_AUTH_KEY.'.'.'User');
		$modules = $this->Module->findSystemModule($user['authority_id']);
		$this->set('modules', $modules);

		$referer = $this->referer();
		$base_url = Router::url('/', true);
		// TODO:管理系の画面を１つでも動作させると、refererが管理系の画面となり管理終了時に元の画面には遷移しない。
		if(preg_match('/^'.preg_quote($base_url, '/').'/', $referer) && !preg_match('/\/users\/login$/', $referer)
			 && !preg_match('/'.preg_quote($base_url, '/').'active-controls\/.*/', $referer) 
			 && !preg_match('/'.preg_quote($base_url, '/').'controls\/{0,1}.*/', $referer)) {
			$this->set('referer', $referer);
		} else {
			$this->set('referer', $base_url);
		}
	}
}
