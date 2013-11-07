<?php
/**
 * SystemCommonComponentクラス
 *
 * <pre>
 * システム管理共通コンポーネント
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Plugin.Blog.Component
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class SystemCommonComponent extends Component {
/**
 * _controller
 *
 * @var Controller
 */
	protected $_controller = null;

/**
 * startup
 *
 * @param Controller $controller
 */
	public function startup(Controller $controller) {
		$this->_controller = $controller;
	}

/**
 * 言語切替
 * @param   string $renderElement
 * @return  string
 * @since   v 3.0.0.0
 */
	public function setLanguage($renderElement = null) {
		$activeLang = isset($this->_controller->request->named['language']) ? $this->_controller->request->named['language'] : null;
		if(isset($this->_controller->request->data['activeLang'])) {
			$activeLang = $this->_controller->request->data['activeLang'];
		}
		if($activeLang == '') {
			$activeLang = null;
		}

		$preLang = Configure::read(NC_CONFIG_KEY.'.'.'system.preLanguage');
		if(isset($preLang)) {
			return $activeLang;
		}
		$languages = Configure::read(NC_CONFIG_KEY.'.'.'languages');
		$this->_controller->set("language", $activeLang);
		$this->_controller->set("languages", $languages);
		if(isset($activeLang) && isset($languages[$activeLang])) {
			Configure::write(NC_CONFIG_KEY.'.'.'system.preLanguage', Configure::read(NC_CONFIG_KEY.'.'.'language'));

			Configure::write(NC_CONFIG_KEY.'.'.'language', $activeLang);
			$this->_controller->Session->write(NC_CONFIG_KEY.'.language', $activeLang);
			if(!empty($renderElement)) {
				$this->_controller->render($renderElement);
			}
		}
		return $activeLang;
	}
}