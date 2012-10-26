<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         CakePHP(tm) v 1.3
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

//App::uses('CakeRoute', 'Routing/Route');

/**
 * NC Plugin用Routes
 *
 * <pre>
 * 		blocks(active-blocks)/(block_id)/(plugin)/(controller)/XXXX -> コントローラがあれば、そのコントローラを呼ぶ、なければ
 * 		アクション名と判断する（(plugin)/(pluginController)/(action)）。
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Routing.Route
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class MyCakeRoute extends CakeRoute {
/**
 * Checks to see if the given URL can be parsed by this route.
 * If the route can be parsed an array of parameters will be returned; if not
 * false will be returned. String urls are parsed if they match a routes regular expression.
 *
 * @param string $url The url to attempt to parse.
 * @return mixed Boolean false on failure, otherwise an array or parameters
 */
	public function parse($url) {
		if (!$this->compiled()) {
			$this->compile();
		}
		if (!preg_match($this->_compiledRoute, $url, $route)) {
			return false;
		}
		foreach ($this->defaults as $key => $val) {
			$key = (string)$key;
			if ($key[0] === '[' && preg_match('/^\[(\w+)\]$/', $key, $header)) {
				if (isset($this->_headerMap[$header[1]])) {
					$header = $this->_headerMap[$header[1]];
				} else {
					$header = 'http_' . $header[1];
				}
				$header = strtoupper($header);

				$val = (array)$val;
				$h = false;

				foreach ($val as $v) {
					if (env($header) === $v) {
						$h = true;
					}
				}
				if (!$h) {
					return false;
				}
			}
		}
		array_shift($route);
		$count = count($this->keys);
		for ($i = 0; $i <= $count; $i++) {
			unset($route[$i]);
		}

// Add Start Ryuji.M
// Conrtollerがあるかどうか判断し、なければ、Actionとする。
// コントローラ名は常にpluginName + controllerにて判断する。
// 例：active-blocks/(block_id)/announcement/edit
//     -> Plugin/AnnouncementEditController.phpがあれば、AnnouncementEditController->indexメソッドへ
//     -> なければ、Plugin/AnnouncementController.php->editメソッドへ
		if(isset($route['controller'])) {
			$pluginName = Inflector::camelize($route['plugin']);
			$controller = $pluginName . Inflector::camelize($route['controller']);
			$file_path = App::pluginPath($pluginName) . 'Controller' . DS .$controller.'Controller.php';
			if (!file_exists($file_path)) {
				$buf_action = isset($route['action']) ? $route['action'] : null;
				$route['action'] = $route['controller'];
				$route['controller'] = $route['plugin'];
				if(isset($buf_action)) {
					if(isset($route['_args_'])) {
						$route['_args_'] = $buf_action .'/'. $route['_args_'];
					} else {
						$route['_args_'] = $buf_action;
					}
				}
			} else {
				$route['controller'] = $controller;
			}
		}
// Add End Ryuji.M

		$route['pass'] = $route['named'] = array();

		// Assign defaults, set passed args to pass
		foreach ($this->defaults as $key => $value) {
			if (isset($route[$key])) {
				continue;
			}
			if (is_integer($key)) {
				$route['pass'][] = $value;
				continue;
			}
			$route[$key] = $value;
		}

		foreach ($this->keys as $key) {
			if (isset($route[$key])) {
				$route[$key] = rawurldecode($route[$key]);
			}
		}

		if (isset($route['_args_'])) {
			list($pass, $named) = $this->_parseArgs($route['_args_'], $route);
			$route['pass'] = array_merge($route['pass'], $pass);
			$route['named'] = $named;
			unset($route['_args_']);
		}

		if (isset($route['_trailing_'])) {
			$route['pass'][] = rawurldecode($route['_trailing_']);
			unset($route['_trailing_']);
		}

		// restructure 'pass' key route params
		if (isset($this->options['pass'])) {
			$j = count($this->options['pass']);
			while ($j--) {
				if (isset($route[$this->options['pass'][$j]])) {
					array_unshift($route['pass'], $route[$this->options['pass'][$j]]);
				}
			}
		}
		return $route;
	}

}
