<?php
/**
 * InitComponentクラス
 *
 * <pre>
 * 初期処理
 * ・DEBUG情報のセット
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       App.Controllers.Components
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class InitComponent extends Component {
/**
 * Other components utilized by Component
 *
 * @var     array
 */
	public $components = array('Session');		// , 'Init', 'Auth'

/**
 * Debugger Format
 *
 * @var     array
 */
	protected $_format = array();

/**
  * Start InitComponent for use in the controller
 *
 * @param Controller $controller
 * @return  void
 * @since   v 3.0.0.0
 */
	public function initialize(Controller $controller) {
		if(Configure::read('debug') != _OFF) {
			$globalCount = $this->Session->read(NC_SYSTEM_KEY.'.debug.global_count');
			if(isset($globalCount)) {
				$sessCurrentUrls = $this->Session->read(NC_SYSTEM_KEY.'.debug.current_urls');
				$sessFormPostValues = $this->Session->read(NC_SYSTEM_KEY.'.debug.form_post_values');
				$sessFormGetValues = $this->Session->read(NC_SYSTEM_KEY.'.debug.form_get_values');
				Configure::write(NC_SYSTEM_KEY.'.sqls', $this->Session->read(NC_SYSTEM_KEY.'.debug.sqls'));
				Configure::write(NC_SYSTEM_KEY.'.method_type', $this->Session->read(NC_SYSTEM_KEY.'.debug.method_type'));
				$this->Session->delete(NC_SYSTEM_KEY.'.debug');
			} else {
				$globalCount = Configure::read(NC_SYSTEM_KEY.'.global_count');
			}
			if($globalCount === null) {
				$globalCount = 0;
			} else {
				$globalCount++;
			}
			Configure::write(NC_SYSTEM_KEY.'.global_count', $globalCount);

			$currentUrl = rawurldecode($controller->here);
			$currentUrls = Configure::read(NC_SYSTEM_KEY.'.current_urls');
			if(!isset($currentUrls)) {
				$currentUrls = array($currentUrl);
			} else {
				$currentUrls[] = $currentUrl;
			}

			if(isset($sessCurrentUrls)) {
				$currentUrls = array_merge($sessCurrentUrls, $currentUrls);
			}

			Configure::write(NC_SYSTEM_KEY.'.current_urls', $currentUrls);
			if(isset($sessFormGetValues)) {
				Configure::write(NC_SYSTEM_KEY.'.form_get_values', $sessFormGetValues);
			} else if(!empty($controller->request->query)) {
				$formGetValues = Configure::read(NC_SYSTEM_KEY.'.form_get_values');
				if(!isset($formGetValues)) {
					$formGetValues = array($controller->request->query);
				} else {
					$formGetValues[] = $controller->request->query;
				}
				Configure::write(NC_SYSTEM_KEY.'.form_get_values', $formGetValues);
			}
			if(isset($sessFormPostValues)) {
				Configure::write(NC_SYSTEM_KEY.'.form_post_values', $sessFormPostValues);
			} else if(!empty($controller->request->data)) {
				$formPostValues = Configure::read(NC_SYSTEM_KEY.'.form_post_values');
				if(!isset($formPostValues)) {
					$formPostValues = array($controller->request->data);
				} else {
					$formPostValues[] = $controller->request->data;
				}
				Configure::write(NC_SYSTEM_KEY.'.form_post_values', $formPostValues);
			}

			$this->_format = Debugger::addFormat('js', array('callback' => array($this, 'formatCallback')));
		}


	}

/**
 * DebuggerのFormatをすぐechoしないで、配列に保持
 * 		デバッグ情報を下部にまとめて出力させるため
 *
 * @param   array $data
 * @param   array $strings
 * @return  void
 * @since   v 3.0.0.0
 */
	public function formatCallback($data, $strings) {
		$insertOpts = array('before' => '{:', 'after' => '}');
		$error = String::insert($this->_format['error'], $strings + $data, $insertOpts);

		$phpLogs = Configure::read(NC_SYSTEM_KEY.'.php_logs');
		if(!isset($phpLogs)) {
			$phpLogs = array($error);
		} else {
			$phpLogs[] = $error;
		}
		Configure::write(NC_SYSTEM_KEY.'.php_logs', $phpLogs);
	}
}