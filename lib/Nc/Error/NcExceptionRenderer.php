<?php
App::uses('ExceptionRenderer', 'Error');

class NcExceptionRenderer extends ExceptionRenderer {

/**
 * Convenience method to display a 400 series page.
 *
 * @param Exception $error
 * @return void
 */
	public function error400($error) {
		$message = $error->getMessage();
		if (!Configure::read('debug') && $error instanceof CakeException) {
// Modify for NetCommons Extentions By R.Ohga --START
			$message = __('Page not found.');
// 			$message = __d('cake', 'Not Found');
// Modify for NetCommons Extentions By R.Ohga --E N D
		}
		$url = $this->controller->request->here();
		$this->controller->response->statusCode($error->getCode());
		$this->controller->set(array(
// Modify for NetCommons Extentions By R.Ohga --START
// エスケープしないように修正
			'name' => $message,
// 			'name' => h($message),
// Modify for NetCommons Extentions By R.Ohga --E N D
			'url' => h($url),
			'error' => $error,
			'_serialize' => array('name', 'url')
		));
// Add for NetCommons Extentions By R.Ohga --START
// 画面全体を切り替えるように修正
		$this->controller->layout = 'default';
// Add for NetCommons Extentions By R.Ohga --E N D
		$this->_outputMessage('error400');
	}

/**
 * Convenience method to display a 500 page.
 *
 * @param Exception $error
 * @return void
 */
	public function error500($error) {
		$message = $error->getMessage();
		if (!Configure::read('debug')) {
// Modify for NetCommons Extentions By R.Ohga --START
			$message = __('The server encountered an internal error and was unable to complete your request.');
// 			$message = __d('cake', 'An Internal Error Has Occurred.');
// Modify for NetCommons Extentions By R.Ohga --E N D
		}
		$url = $this->controller->request->here();
		$code = ($error->getCode() > 500 && $error->getCode() < 506) ? $error->getCode() : 500;
		$this->controller->response->statusCode($code);
		$this->controller->set(array(
// Modify for NetCommons Extentions By R.Ohga --START
// エスケープしないように修正
			'name' => $message,
// 			'name' => h($message),
// Modify for NetCommons Extentions By R.Ohga --E N D
			'message' => h($url),
			'error' => $error,
			'_serialize' => array('name', 'message')
		));
// Add for NetCommons Extentions By R.Ohga --START
// 画面全体を切り替えるように修正
		$this->controller->layout = 'default';
// Add for NetCommons Extentions By R.Ohga --E N D
		$this->_outputMessage('error500');
	}
}