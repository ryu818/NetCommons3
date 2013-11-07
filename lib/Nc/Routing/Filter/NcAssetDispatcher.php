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
// Add for NetCommons Extentions By Ryuji.M --START
// ブロックテーマのwebrootのパスをRouting
		} else if($parts[0] === 'frame') {
			$frameName = $parts[1];
			unset($parts[0], $parts[1]);
			$fileFragment = implode(DS, $parts);
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
// Add for NetCommons Extentions By Ryuji.M --E N D
		}
		$plugin = Inflector::camelize($parts[0]);
// Add for NetCommons Extentions By Ryuji.M --START
// webrootのパスをRouting
		if($plugin =='Js' || $plugin =='Css' || $plugin =='Img') {
			// TODO: imgもNc/webroot下に移動し、読み込むようにしてあるが、
			// Dispatcherを経由するため効率が非常に悪い。
			// app/webroot下にファイルがなければ、コピーするような処理をすれば
			// 高速になるが、同じファイルが２つ存在してしまう。
			$paths = App::path('webroot');
			foreach ($paths as $path) {
				$path = $path . $url;
				if (file_exists($path)) {
					return $path;
				}
			}
		}
// Add for NetCommons Extentions By Ryuji.M --E N D
		if ($plugin && CakePlugin::loaded($plugin)) {
			unset($parts[0]);
			$fileFragment = implode(DS, $parts);
			$pluginWebroot = CakePlugin::path($plugin) . 'webroot' . DS;
			return $pluginWebroot . $fileFragment;
		}
	}
}