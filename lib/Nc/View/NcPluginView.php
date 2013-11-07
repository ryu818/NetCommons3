<?php
/**
 * NcPluginViewクラス
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

class NcPluginView extends View {
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
		$file = $plugin = null;

		if (isset($options['plugin'])) {
			$name = Inflector::camelize($options['plugin']) . '.' . $name;
		}

		if (!isset($options['callbacks'])) {
			$options['callbacks'] = false;
		}

		if (isset($options['cache'])) {
			$contents = $this->_elementCache($name, $data, $options);
			if ($contents !== false) {
				return $contents;
			}
		}

// Modify for NetCommons Extentions By Ryuji.M --START
// 		$file = $this->_getElementFilename($name);
		if (isset($options['frame'])) {
			$file = $this->_getFrameFileName($name, $options['frame']);
		} else {
			$file = $this->_getElementFileName($name);
		}
// Modify for NetCommons Extentions By Ryuji.M --START
		if ($file) {
			return $this->_renderElement($file, $data, $options);
		}

		if (empty($options['ignoreMissing'])) {
			list ($plugin, $name) = pluginSplit($name, true);
			$name = str_replace('/', DS, $name);
			$file = $plugin . 'Elements' . DS . $name . $this->ext;
			trigger_error(__d('cake_dev', 'Element Not Found: %s', $file), E_USER_NOTICE);
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
			if (file_exists($path . 'Plugin' . DS . $framePath)) {
				return $path . 'Plugin' . DS . $framePath;
			}
		}
		return false;
	}

/**
 * プラグインDir下のThemedを対象にする。
 * <pre>
 * pluginを作成するとnc/View/Themed/xxxx/にファイルを置く必要があるが、
 * plugin単位で完全に分離したいため、nc/Plugins/(plugin)/View/Themed/(theme)/下にまとめるようにする。
 * </pre>
 *
 * @param string $plugin Optional plugin name to scan for view files.
 * @param boolean $cached Set to true to force a refresh of view paths.
 * @return array paths
 */
	protected function _paths($plugin = null, $cached = true) {
		$paths = parent::_paths($plugin, $cached);
		if (!is_string($plugin) || empty($plugin) || empty($this->theme))
			return $paths;
		// add app/plugins/PLUGIN/View/Themed/THEME path
		$plugin_path = App::pluginPath($plugin);	// App::pluginPath(Inflector::camelize($plugin));

		$dirPath = $plugin_path . 'View' . DS . 'Themed' . DS . $this->theme . DS;
		if (is_dir($dirPath))
			$paths = array_merge(array($dirPath), $paths);
		return $paths;
	}
}
