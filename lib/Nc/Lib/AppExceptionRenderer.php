<?php
//App::import('Error', 'ExceptionRenderer');

class AppExceptionRenderer extends ExceptionRenderer {
	protected function _getController($exception) {
		App::uses('CakeErrorController', 'Controller');
		if (!$request = Router::getRequest(true)) {
			$request = new CakeRequest();
		}
		$response = new CakeResponse(array('charset' => Configure::read('App.encoding')));
		try {
			if (class_exists('AppController')) {
				$controller = new CakeErrorController($request, $response);
			}
		} catch (Exception $e) {
		}
		if (empty($controller)) {
			$controller = new Controller($request, $response);
			$controller->viewPath = 'Errors';
		}
// Add for NetCommons Extentions By Ryuji.M --START
// 例外がおこったら、layoutをdefaultに設定
		$controller->layout = 'default';
// Add for NetCommons Extentions By Ryuji.M --E N D
		return $controller;
	}
}