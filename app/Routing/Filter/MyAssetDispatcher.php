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
class MyAssetDispatcher extends AssetDispatcher {

/**
 * Checks if a requested asset exists and sends it to the browser
 *
 * @param CakeEvent $event containing the request and response object
 * @return CakeResponse if the client is requesting a recognized asset, null otherwise
 */
	public function beforeDispatch($event) {
		$url = $event->data['request']->url;
		$response = $event->data['response'];

		if (strpos($url, '..') !== false || strpos($url, '.') === false) {
			return;
		}

		if ($result = $this->_filterAsset($event)) {
			$event->stopPropagation();
			return $result;
		}

		$pathSegments = explode('.', $url);
		$ext = array_pop($pathSegments);
		$parts = explode('/', $url);
		$assetFile = null;

		if ($parts[0] === 'theme') {
			$themeName = $parts[1];
			unset($parts[0], $parts[1]);
			$fileFragment = urldecode(implode(DS, $parts));
			$path = App::themePath($themeName) . 'webroot' . DS;
			if (file_exists($path . $fileFragment)) {
				$assetFile = $path . $fileFragment;
			}
		// Add Ryuji.M ブロックテーマのwebrootのパスをRouting
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
				$assetFile = $framePath . $fileFragment;
			}
		// Add End
		} else {
			$plugin = Inflector::camelize($parts[0]);
			if (CakePlugin::loaded($plugin)) {
				unset($parts[0]);
				$fileFragment = urldecode(implode(DS, $parts));
				$pluginWebroot = CakePlugin::path($plugin) . 'webroot' . DS;
				if (file_exists($pluginWebroot . $fileFragment)) {
					$assetFile = $pluginWebroot . $fileFragment;
				}
			}
		}

		if ($assetFile !== null) {
			$event->stopPropagation();
			$response->modified(filemtime($assetFile));
			if (!$response->checkNotModified($event->data['request'])) {
				$this->_deliverAsset($response, $assetFile, $ext);
			}
			return $response;
		}
	}
}