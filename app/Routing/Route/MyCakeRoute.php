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
		$route['pass'] = $route['named'] = array();

		// Add Start Ryuji.M
		//$route = parent::parse($url);
		//if (!$route) {
		//	return false;
		//}
		//if(isset($route['plugin']) && !isset($route['controller'])) {
		//	$route['controller'] = $route['plugin'];
		//}
		if(isset($route['plugin'])) {
			$block_type = Configure::read(NC_SYSTEM_KEY.'.block_type');
			if(!isset($block_type) && isset($route['block_type']) && $route['block_type'] == 'blocks') {
				return false;
			}
		}
		// Conrtollerがあるかどうか判断し、なければ、Actionとする。
		// コントローラ名は常にpluginName + controllerにて判断する。
		// 例：active-blocks/(block_id)/announcement/edits
		//     -> Plugin/AnnouncementEditsController.phpがあれば、AnnouncementEditsController->indexメソッドへ
		//     -> なければ、Plugin/AnnouncementsController.php->editメソッドへ
		if(isset($route['active_plugin'])) {
			$plugin_name = 'active_plugin';
			$controller_name = 'active_controller';
			$actin_name = 'active_action';
			if(isset($route[$plugin_name])) {
				$route[$plugin_name] = $this->_formatUrl($route[$plugin_name]);
			}
			if(isset($route[$controller_name])) {
				$route[$controller_name] = $this->_formatUrl($route[$controller_name]);
			}
			if(isset($route[$actin_name])) {
				$route[$actin_name] = $this->_formatUrl($route[$actin_name]);
			}
		} else {
			$plugin_name = 'plugin';
			$controller_name = 'controller';
			$actin_name = 'action';
		}

		if(isset($route[$controller_name])) {
			$pluginName = Inflector::camelize($route[$plugin_name]);
			if($route[$plugin_name] == $route[$controller_name]) {
				$controller = Inflector::camelize($route[$controller_name]);
				$buf_controller = $route[$controller_name];
			} else {
				$controller = $pluginName . Inflector::camelize($route[$controller_name]);
				$buf_controller = $route[$plugin_name] .'_'. $route[$controller_name];
			}

			$file_path = App::pluginPath($pluginName) . 'Controller' . DS .$controller.'Controller.php';
			if (!file_exists($file_path)) {
				$buf_action = isset($route[$actin_name]) ? $route[$actin_name] : null;
				$route[$actin_name] = $route[$controller_name];

				$route[$controller_name] = $route[$plugin_name];
				if(isset($buf_action)) {
					if(isset($route['_args_'])) {
						$route['_args_'] = $buf_action .'/'. $route['_args_'];
					} else {
						$route['_args_'] = $buf_action;
					}
				}
			} else {
				$route[$controller_name] = $buf_controller;
			}
		}
		// Add End Ryuji.M

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

/**
 * Urlの体裁を整える
 *
 * @param   string $url
 * @return  string $url
 * @since   v 3.0.0.0
 */
	protected function _formatUrl($url) {
		return trim($url, '/');
	}

	/**
	 * Reverse route plugin shortcut urls. If the plugin and controller
	 * are not the same the match is an auto fail.
	 *
	 * @param array $url Array of parameters to convert to a string.
	 * @return mixed either false or a string url.
	 */
	public function match($url) {
		if(isset($url['plugin']) && !isset($url['block_type'])) {
			$block_type = Configure::read(NC_SYSTEM_KEY.'.block_type');
			$url['block_type'] = isset($block_type) ? $block_type : 'active-blocks';
		}

		if(isset($url['block_type']) && $url['block_type'] != 'active-controls' && $url['block_type'] != 'active-contents' && !isset($url['block_id'])) {
			$block_id = Configure::read(NC_SYSTEM_KEY.'.block_id');
			if(isset($block_id) && intval($block_id) > 0) {
				$url['block_id'] = $block_id;
			}
		}
		if(isset($url['block_id']) && $url['block_id'] == 0) {
			unset($url['block_id']);
		}
		if(isset($url['block_type']) && $url['block_type'] == 'active-contents' && !isset($url['content_id'])) {
			$content_id = Configure::read(NC_SYSTEM_KEY.'.content_id');
			if(isset($content_id) && intval($content_id) > 0) {
				$url['content_id'] = $content_id;
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

		if(isset($url['block_type']) && $url['block_type'] != 'active-controls' && $url['block_type'] != 'active-contents' && !isset($url['permalink'])) {
			$permalink = Configure::read(NC_SYSTEM_KEY.'.permalink');
			if($permalink) {
				$url['permalink'] = $permalink;
			} else {
				$url['permalink'] = '';
			}
		}

		if(isset($url['action']) && $url['action'] == 'index') {
			// namedやpassの情報がはいった場合、actionを省略しない
			$chk_url = $url;
			unset($chk_url['block_id']);
			unset($chk_url['permalink']);
			unset($chk_url['plugin']);
			unset($chk_url['controller']);
			unset($chk_url['action']);
			if(count($chk_url) == 0) {
				// 省略可能
				$url['action'] = '';
			}
		}

		//if (isset($url['controller']) && isset($url['plugin']) && $url['plugin'] != $url['controller']) {
		//	return false;
		//}
		$this->defaults['controller'] = $url['controller'];

		$result = parent::match($url);
		unset($this->defaults['controller']);

		return $result;
	}

/**
 * Finds URL for specified action.
 *
 * Returns an URL pointing to a combination of controller and action. Param
 * $url can be:
 *
 * - Empty - the method will find address to actual controller/action.
 * - '/' - the method will find base URL of application.
 * - A combination of controller/action - the method will find url for it.
 *
 * There are a few 'special' parameters that can change the final URL string that is generated
 *
 * - `base` - Set to false to remove the base path from the generated url. If your application
 *   is not in the root directory, this can be used to generate urls that are 'cake relative'.
 *   cake relative urls are required when using requestAction.
 * - `?` - Takes an array of query string parameters
 * - `#` - Allows you to set url hash fragments.
 * - `full_base` - If true the `FULL_BASE_URL` constant will be prepended to generated urls.
 *
 * @param string|array $url Cake-relative URL, like "/products/edit/92" or "/presidents/elect/4"
 *   or an array specifying any of the following: 'controller', 'action',
 *   and/or 'plugin', in addition to named arguments (keyed array elements),
 *   and standard URL arguments (indexed array elements)
 * @param bool|array $full If (bool) true, the full base URL will be prepended to the result.
 *   If an array accepts the following keys
 *    - escape - used when making urls embedded in html escapes query string '&'
 *    - full - if true the full base URL will be prepended.
 * @return string Full translated URL with base path.
 */
	public static function url($url = null, $full = false) {
		//
		// block_typeをページ表示中ならば、block になるように修正
		//
		if (empty($url)) {
			$block_type = Configure::read(NC_SYSTEM_KEY.'.block_type');
			if(isset($block_type) && $block_type == 'blocks') {
				$request = self::$_requests[count(self::$_requests) - 1];
				$here = $request->here;
				$here = preg_replace('/(.*)\/active-blocks\/([0-9]*)/i', '$1/blocks/$2', $here);

				$url = isset($here) ? $here : '/';
			}

			if ($full && defined('FULL_BASE_URL')) {
				$url = FULL_BASE_URL . $url;
			}
		}
		return parent::url($url, $full);
	}
}
