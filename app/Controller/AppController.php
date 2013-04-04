<?php
/**
 * Application level Controller
 *
 * This file is application-wide controller file. You can put all
 * application-wide controller-related methods here.
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

App::uses('Controller', 'Controller');

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller {
/**
 * theme
 *
 * @var string
 */
	public $view = 'Theme';

/**
 * theme名称
 *
 * @var string
 */
	public $theme = '';

	public $hierarchy = null;

	public $is_chief = null;

	public $components = array(
		'Init', 'SetConfig', 'Session', 'Common', 'CheckAuth', 'Auth',
	);
	// 'DebugKit.Toolbar','Cookie', 'RequestHandler'

	public $helpers = array(
        'Session',
        'Form' => array(
            'className' => 'MyForm'
        ),
        'Html' => array(
            'className' => 'MyHtml'
        ),
		'Text',
		'Js',
        'Token',
		'TimeZone',
    );
    // , 'Common',

    public $uses = array('Page', 'Block', 'Content', 'Module', 'Language', 'Config', 'Authority',
    		'User', 'Passport', 'PageStyle', 'ModuleLink', 'ModuleSystemLink', 'Community', 'Asset');
    // , 'User', 'PageStyle', 'PageColumn', 'PageInf', 'SumPageView'
/**
 * 初期処理
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
    public function constructClasses() {
		$this->_setExecuteTimes();

		parent::constructClasses();

		// ******************************************************************************************
		// Config
		// Sessionのsession_gc_maxlifetimeをstart前にセットする必要があるため、別途取得
		// ******************************************************************************************

		/*
		 * 同じ処理を何度も実行させないため
		 */
		$configLanguage = Configure::read(NC_CONFIG_KEY.'.'.'config_language');
		if(isset($configLanguage)) {
			return;
		}

    	$conditions = array(
			'name' => array('session_gc_maxlifetime', 'session_name', 'language'),
			'module_id' => 0
		);
		$params = array(
			'fields' => array(
					'Config.name',
					'Config.value'
			),
			'conditions' => $conditions,
			'limit' => 4
			//'order' => 'Config.id'
		);
		$configs = $this->Config->find('all', $params);
		Configure::write(NC_CONFIG_KEY.'.'.'config_language', $configs['language']);
		Configure::write('Session.cookieTimeout', 0);
		Configure::write('Session.timeout', intval($configs['session_gc_maxlifetime']));

		Configure::write('Session.cookie', $configs['session_name']);
    }

/**
 * 実行前処理
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
    public function beforeFilter()
	{
		parent::beforeFilter();

		$controllerName = $this->request->params['controller'];
		//$blockType = isset($this->request->params['block_type']) ? $this->request->params['block_type'] : null;
    	$this->Common->initializeAuth();

		if ($this->request->is('ajax')) {
			$this->layout = 'ajax';
		}

    	if ($this->request->is('ajax')) {
    		$pluginName = isset($this->request->params['plugin']) ? $this->request->params['plugin'] :
    		(isset($this->request->params['active_plugin']) ? $this->request->params['active_plugin'] : '');
    		if($pluginName != '') {
    			// ajaxの場合、blocksのリンクが含まれていれば、active-blocksに置換する。
    			$replaceUrl = preg_replace('/(.*)\/blocks\/([0-9]*)/i', '$1/active-blocks/$2', $this->request->here);
    			if($replaceUrl != $this->request->here) {
    				$replaceUrl = preg_replace('%^'.$this->request->webroot.'%i', '', $replaceUrl);
    				echo $this->requestAction($replaceUrl, array('return', 'bare' => 0, 'requested' => 0));
    				$this->_stop();
    			}
    		}
    	}
    	// Configセット
    	if ($this->Components->enabled('Auth')) {
    		$this->SetConfig->set();
    	}

    	$isClosedSite = Configure::read(NC_CONFIG_KEY.'.'.'is_closed_site');
    	if(!$isClosedSite) {
    		$this->Auth->allow();
    	}

    	// 権限チェック
    	//if($blockType == 'active-controls') {
    	//	// 管理系モジュール
    	//} else
    	if ($controllerName != 'controls' && $this->Components->enabled('Auth')) {
    		$this->CheckAuth->check();
    	}
	}

/**
 * 表示前処理
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function beforeRender()
	{
		parent::beforeRender();

		$this->set('hierarchy', $this->hierarchy);
	}

/**
 * 表示後処理
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function afterFilter()
	{
		parent::afterFilter();

		$this->_setExecuteTimes('nc_execute_end_times');
	}

/**
 * リダイレクト画面表示
 * @param string  $message          エラーメッセージ
 * @param string  $url              リダイレクトURL
 * @param string  $errorIdStr     エラーID
 * @param string  $status           Optional HTTP status code (eg: 404) default: 200
 * @param integer $pause            リダイレクト画面表示時間(秒) default 2秒
 * @param string  $layout			レイアウト名称
 * @param boolean $stop 			処理を終了するかどうか
 * @return void Renders flash layout
 * @link http://book.cakephp.org/2.0/en/controllers.html#Controller::flash
 */
	public function flash($message, $url, $errorIdStr = '', $status = '200', $pause = 2, $layout = 'flash', $exit = true) {
		$this->autoRender = false;

		///404 Not Found 403 Forbidden 400 Bad Request
		if (is_array($status)) {
			extract($status, EXTR_OVERWRITE);
		} else {
			$codes = array_flip($this->response->httpCodes());
			if (isset($codes[$status])) {
				$status = $codes[$status];
			}
		}
		if ($status) {
			$this->response->statusCode($status);
		}

		if(!isset($url) || $url === '') {
			// 空ならばリファラから自動リダイレクト
			$url = $this->request->referer();
			if($url == Router::url('/', true).'users/login' && $this->Auth->user('id')) {
				$url = '/';
			}
		}
		$this->set('url', Router::url($url));
		$this->set('message', $message);
		$this->set('pause', $pause);
		$this->set('page_title', $message);
		if(Configure::read('debug') != _OFF) {
			$this->set('error_id_str', $errorIdStr);
		} else {
			$this->set('error_id_str', '');
		}
		$this->set('sub_message', __('The page will be automatically reloaded.If otherwise, please click <a href="%s">here</a>.'));
		if($exit) {
			$this->autoLayout = true;
			$this->render(false, $layout);
			echo $this->response->body();
			$this->_stop();
		} else {
			$this->render(false, $layout);
		}
	}

/**
 * Redirects to given $url, after turning off $this->autoRender.
 * Script execution is halted after the redirect.
 *
 * <pre>
 * ・リダイレクト時にもDEBUG情報を出力するように修正。
 * ・pjaxならば、header Locationではなく、「X-PJAX-Location」ヘッダーを返し、そのURLを見て、再度、pjaxを呼ぶように修正。
 * 		そうすることで、URL欄を適切にリダイレクト先のURLに変換できるため。
 * </pre>
 *
 * @param string|array $url A string or array-based URL pointing to another location within the app,
 *     or an absolute URL
 * @param integer $status Optional HTTP status code (eg: 404)
 * @param boolean $exit If true, exit() will be called after the redirect
 * @return mixed void if $exit = false. Terminates script if $exit = true
 * @link http://book.cakephp.org/2.0/en/controllers.html#Controller::redirect
 */
	public function redirect($url, $status = null, $exit = true) {
		$this->autoRender = false;

		if (is_array($status)) {
			extract($status, EXTR_OVERWRITE);
		}
		$event = new CakeEvent('Controller.beforeRedirect', $this, array($url, $status, $exit));
		//TODO: Remove the following line when the events are fully migrated to the CakeEventManager
		list($event->break, $event->breakOn, $event->collectReturn) = array(true, false, true);
		$this->getEventManager()->dispatch($event);

		if ($event->isStopped()) {
			return;
		}
		$response = $event->result;
		extract($this->_parseBeforeRedirect($response, $url, $status, $exit), EXTR_OVERWRITE);

		if ($url !== null) {
// Edit Start Ryuji.M
			if($this->request->header('X-PJAX')) {
				if (!$status) {
					$this->response->statusCode('302');
				}
				$this->response->header('X-PJAX-Location', $this->Common->linkEncode(Router::url($url, true)));
			} else {
				if(Configure::read('debug') != 0) {
					$globalCount = Configure::read(NC_SYSTEM_KEY.'.global_count');
					$currentUrls = Configure::read(NC_SYSTEM_KEY.'.current_urls');
					$formGetValues = Configure::read(NC_SYSTEM_KEY.'.form_get_values');
					$formPostValues = Configure::read(NC_SYSTEM_KEY.'.form_post_values');
					$this->Session->write(NC_SYSTEM_KEY.'.debug.global_count', $globalCount);
					$this->Session->write(NC_SYSTEM_KEY.'.debug.current_urls', $currentUrls);
					$this->Session->write(NC_SYSTEM_KEY.'.debug.form_get_values', $formGetValues);
					$this->Session->write(NC_SYSTEM_KEY.'.debug.form_post_values', $formPostValues);
					if($this->request->is('post')) {
						$this->Session->write(NC_SYSTEM_KEY.'.debug.method_type', 'post');
					}
					$sources = ConnectionManager::sourceList();
					$logs = array();
					foreach($sources as $source) {
						$db = ConnectionManager::getDataSource($source);
						$logs[$source] = $db->getLog();
					}
					$this->Session->write(NC_SYSTEM_KEY.'.debug.sqls', $logs);
				}
				$this->response->header('Location', Router::url($url, true));
			}
			// $this->response->header('Location', Router::url($url, true));
// Edit End Ryuji.M
		}

		if (is_string($status)) {
			$codes = array_flip($this->response->httpCodes());
			if (isset($codes[$status])) {
				$status = $codes[$status];
			}
		}

		if ($status) {
			$this->response->statusCode($status);
		}

		if ($exit) {
// Add Start Ryuji.M
			if ($this->request->header('X-PJAX') && Configure::read('debug') != 0) {
				$this->render(false, 'redirect');
			}
// Add End Ryuji.M
			$this->response->send();
			$this->_stop();
		}
	}

/**
 * 実行時間計測用メソッド
 * @param string  $name
 * @return void
 */
	protected function _setExecuteTimes($name = 'nc_execute_start_times') {
		if(Configure::read('debug') != _OFF && isset($this->request->params['requested'])) {
			$ncExecuteTimes = Configure::read(NC_SYSTEM_KEY. '.'. $name);
			if(!isset($ncExecuteTimes)) {
				$ncExecuteTimes = array(microtime(true));
			} else {
				$ncExecuteTimes[] = microtime(true);
			}
			Configure::write(NC_SYSTEM_KEY. '.'. $name, $ncExecuteTimes);
		}
	}
}
