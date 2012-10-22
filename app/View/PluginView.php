<?php
/**
 * PluginsViewクラス
 *
 * <pre>
 * ・ブロックスタイル用にelementメソッドを拡張
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       App.View
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */

App::uses('View', 'View');

class PluginView extends View {
/**
 * Renders a piece of PHP with provided parameters and returns HTML, XML, or any other string.
 *
 * This realizes the concept of Elements, (or "partial layouts") and the $params array is used to send
 * data to be used in the element. Elements can be cached improving performance by using the `cache` option.
 *
 * ブロックスタイルのファイルをoption['frame']で読み込めるように修正 (App/Frame/$name/View/index.ctp)
 *
 * @param string $name Name of template file in the/app/View/Elements/ folder,
 *   or `MyPlugin.template` to use the template element from MyPlugin.  If the element
 *   is not found in the plugin, the normal view path cascade will be searched.
 * @param array $data Array of data to be made available to the rendered view (i.e. the Element)
 * @param array $options Array of options. Possible keys are:
 * - `cache` - Can either be `true`, to enable caching using the config in View::$elementCache. Or an array
 *   If an array, the following keys can be used:
 *   - `config` - Used to store the cached element in a custom cache configuration.
 *   - `key` - Used to define the key used in the Cache::write().  It will be prefixed with `element_`
 * - `plugin` - Load an element from a specific plugin.  This option is deprecated, see below.
 * - `callbacks` - Set to true to fire beforeRender and afterRender helper callbacks for this element.
 *   Defaults to false.
 * - `frame`
 * @return string Rendered Element
 * @deprecated The `$options['plugin']` is deprecated and will be removed in CakePHP 3.0.  Use
 *   `Plugin.element_name` instead.
 */
	public function element($name, $data = array(), $options = array()) {
		$file = $plugin = $key = null;
		$callbacks = false;

		if (isset($options['plugin'])) {
			$name = Inflector::camelize($options['plugin']) . '.' . $name;
		}

		if (isset($options['callbacks'])) {
			$callbacks = $options['callbacks'];
		}

		if (isset($options['cache'])) {
			$underscored = null;
			if ($plugin) {
				$underscored = Inflector::underscore($plugin);
			}
			$keys = array_merge(array($underscored, $name), array_keys($options), array_keys($data));
			$caching = array(
				'config' => $this->elementCache,
				'key' => implode('_', $keys)
			);
			if (is_array($options['cache'])) {
				$defaults = array(
					'config' => $this->elementCache,
					'key' => $caching['key']
				);
				$caching = array_merge($defaults, $options['cache']);
			}
			$key = 'element_' . $caching['key'];
			$contents = Cache::read($key, $caching['config']);
			if ($contents !== false) {
				return $contents;
			}
		}

		if (isset($options['frame'])) {
			$file = $this->_getFrameFileName($name, $options['frame']);
		} else {
			$file = $this->_getElementFileName($name);
		}
		if ($file) {
			if (!$this->_helpersLoaded) {
				$this->loadHelpers();
			}
			if ($callbacks) {
				$this->getEventManager()->dispatch(new CakeEvent('View.beforeRender', $this, array($file)));
			}

			$this->_currentType = self::TYPE_ELEMENT;
			$element = $this->_render($file, array_merge($this->viewVars, $data));

			if ($callbacks) {
				$this->getEventManager()->dispatch(new CakeEvent('View.afterRender', $this, array($file, $element)));
			}
			if (isset($options['cache'])) {
				Cache::write($key, $element, $caching['config']);
			}
			return $element;
		}
		if (!isset($options['frame'])) {
			$file = 'Elements' . DS . $name . $this->ext;
		}
		if (Configure::read('debug') > 0) {
			return __d('cake_dev', 'Element Not Found: %s', $file);
		}
	}

/**
 * Frameファイルをさがす。returns false on failure.
 * 拡張子はctp固定
 *
 * @param string $frameName フレーム名称
 * @return mixed
 */
	protected function _getFrameFileName($file, $frameName) {
		$framePath = Inflector::camelize($frameName) . DS .'View' . DS . $file .'.ctp';
		$paths = App::path('Frame');
		foreach ($paths as $path) {
			if (file_exists($path . $framePath)) {
				return $path . $framePath;
			}
		}
		return false;
	}
}
