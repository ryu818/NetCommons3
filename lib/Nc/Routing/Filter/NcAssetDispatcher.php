<?php
/**
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright	  Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link		  http://cakephp.org CakePHP(tm) Project
 * @package		  Cake.Routing
 * @since		  CakePHP(tm) v 2.2
 * @license		  MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

App::uses('AssetDispatcher', 'Routing/Filter');

/**
 * Filters a request and tests whether it is a file in the webroot folder or not and
 * serves the file to the client if appropriate.
 *
 * @package Cake.Routing.Filter
 */
class NcAssetDispatcher extends AssetDispatcher {
/**
 * Builds asset file path based off url
 *
 * @param string $url
 * @return string Absolute path for asset file
 */
	protected function _getAssetFile($url) {
		$parts = explode('/', $url);
		if ($parts[0] === 'theme') {
			$themeName = $parts[1];
			unset($parts[0], $parts[1]);
			$fileFragment = urldecode(implode(DS, $parts));
			$path = App::themePath($themeName) . 'webroot' . DS;
			return $path . $fileFragment;
// Add Stert Ryuji.M ブロックテーマのwebrootのパスをRouting
		} else if($parts[0] === 'frame') {
			$frameName = $parts[1];
			unset($parts[0], $parts[1]);
			$fileFragment = urldecode(implode(DS, $parts));
			$frameDir = Inflector::camelize($frameName) . DS .'webroot' . DS;
			$paths = App::path('Frame');
			$framePath = null;
			foreach ($paths as $path) {
				if (is_dir($path . 'Plugin' . DS . $frameDir)) {
					$framePath = $path . 'Plugin' . DS . $frameDir . DS;
					break;
				}
			}
			if (file_exists($framePath . $fileFragment)) {
				return $framePath . $fileFragment;
			}
// Add End Ryuji.M
		}
		$plugin = Inflector::camelize($parts[0]);
		if ($plugin && CakePlugin::loaded($plugin)) {
			unset($parts[0]);
			$fileFragment = urldecode(implode(DS, $parts));
			$pluginWebroot = CakePlugin::path($plugin) . 'webroot' . DS;
			return $pluginWebroot . $fileFragment;
		}
	}
}