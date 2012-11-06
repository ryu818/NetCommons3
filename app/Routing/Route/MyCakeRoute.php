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
		$route = parent::parse($url);
		if (!$route) {
			return false;
		}
		//if(isset($route['plugin']) && !isset($route['controller'])) {
		//	$route['controller'] = $route['plugin'];
		//}
		if(isset($route['plugin'])) {
			$block_type = Configure::read(NC_SYSTEM_KEY.'.block_type');
			if(!isset($block_type) && isset($route['block_type']) && $route['block_type'] == 'blocks') {
				return false;
			}
		}
		// Add Start Ryuji.M
		// Conrtollerがあるかどうか判断し、なければ、Actionとする。
		// コントローラ名は常にpluginName + controllerにて判断する。
		// 例：active-blocks/(block_id)/announcement/edit
		//     -> Plugin/AnnouncementEditController.phpがあれば、AnnouncementEditController->indexメソッドへ
		//     -> なければ、Plugin/AnnouncementController.php->editメソッドへ
		if(isset($route['controller'])) {
			$pluginName = Inflector::camelize($route['plugin']);
			if($route['plugin'] == $route['controller']) {
				$controller = Inflector::camelize($route['controller']);
			} else {
				$controller = $pluginName . Inflector::camelize($route['controller']);
			}
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
		return $route;
	}

	/**
	 * Reverse route plugin shortcut urls. If the plugin and controller
	 * are not the same the match is an auto fail.
	 *
	 * @param array $url Array of parameters to convert to a string.
	 * @return mixed either false or a string url.
	 */
	public function match($url) {
		if(!isset($url['permalink'])) {
			$permalink = Configure::read(NC_SYSTEM_KEY.'.permalink');
			if($permalink) {
				$url['permalink'] = $permalink;
			} else {
				$url['permalink'] = '';
			}
		}
		if(isset($url['controller'])) {
			$controller_arr = explode('_', $url['controller'], 2);
			if($controller_arr[0] == $url['plugin']) {
				if(isset($controller_arr[1])) {
					$url['controller'] = $controller_arr[1];
				} else {
					$url['controller'] = '';
				}
			}
		}

		if(isset($url['plugin']) && !isset($url['block_type'])) {
			$block_type = Configure::read(NC_SYSTEM_KEY.'.block_type');
			$url['block_type'] = isset($block_type) ? $block_type : 'active-blocks';
		}
	
		if(isset($url['action']) && $url['action'] == 'index') {
			$url['action'] = '';
		}
	
		//if (isset($url['controller']) && isset($url['plugin']) && $url['plugin'] != $url['controller']) {
		//	return false;
		//}
		$this->defaults['controller'] = $url['controller'];

		$result = parent::match($url);
		unset($this->defaults['controller']);

		return $result;
	}
}
