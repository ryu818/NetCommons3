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
			$global_count = Configure::read(NC_SYSTEM_KEY.'.global_count');
			if($global_count === null) {
				$global_count = 0;
			} else {
				$global_count++;
			}
			Configure::write(NC_SYSTEM_KEY.'.global_count', $global_count);

			$current_url = $controller->here;
			$current_urls = Configure::read(NC_SYSTEM_KEY.'.current_urls');
			if(!isset($current_urls)) {
				$current_urls = array($current_url);
			} else {
				$current_urls[] = $current_url;
			}
			Configure::write(NC_SYSTEM_KEY.'.current_urls', $current_urls);
			if(!empty($controller->request->query)) {
				$form_get_values = Configure::read(NC_SYSTEM_KEY.'.form_get_values');
				if(!isset($form_get_values)) {
					$form_get_values = array($controller->request->query);
				} else {
					$form_get_values[] = $controller->request->query;
				}
				Configure::write(NC_SYSTEM_KEY.'.form_get_values', $form_get_values);

			}
			if(!empty($controller->request->data)) {
				$form_post_values = Configure::read(NC_SYSTEM_KEY.'.form_post_values');
				if(!isset($form_post_values)) {
					$form_post_values = array($controller->request->data);
				} else {
					$form_post_values[] = $controller->request->data;
				}
				Configure::write(NC_SYSTEM_KEY.'.form_post_values', $form_post_values);
			}

			$this->_format = Debugger::addFormat('js', array('callback' => array($this, 'formatCallack')));
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
	public function formatCallack($data, $strings) {
		$insertOpts = array('before' => '{:', 'after' => '}');
		$error = String::insert($this->_format['error'], $strings + $data, $insertOpts);

		$php_logs = Configure::read(NC_SYSTEM_KEY.'.php_logs');
		if(!isset($php_logs)) {
			$php_logs = array($error);
		} else {
			$php_logs[] = $error;
		}
		Configure::write(NC_SYSTEM_KEY.'.php_logs', $php_logs);
	}
}