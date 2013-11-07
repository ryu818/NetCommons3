<?php
/**
 * 共通ヘルパー
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       App.Controllers.Components
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
App::uses('AppHelper', 'View/Helper');
class CommonHelper extends AppHelper {
/**
 * Page.controller_actionを分解し、配列を返す
 * @param   string  $controller_action
 * @return  array   $params['plugin', 'controller', 'action']
 * @since   v 3.0.0.0
 */
	public function explodeControllerAction($controllerAction) {
		$params = array();
		$controllerArr = explode('/', $controllerAction, 3);
		$params['plugin'] = $params['controller'] = $controllerArr[0];
		if(isset($controllerArr[2])) {
			$params['controller'] = $controllerArr[1];
			$params['action'] = $controllerArr[2];
		} elseif(isset($controllerArr[1])) {
			$params['controller'] = $controllerArr[1];
		}
		return $params;
	}
}