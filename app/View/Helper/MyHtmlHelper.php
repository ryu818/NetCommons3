<?php
/**
 * HtmlHelperのNc版(Asset Pipeline機能の実装)
 *     ・script,cssの書き出す時に1ファイルにまとめて出力するようにする。
 *		 そのため、inlineオプションをajaxでなければdefault:falseに修正
 *			->余計なコメントを取り除き、includeを一度に行うことで表示速度をあげるため
 *			->フォルダ指定で、それ以下すべてを読み込む
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.View.Helper
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
App::uses('HtmlHelper', 'View/Helper');
class MyHtmlHelper extends HtmlHelper {
/**
 * instance
 *
 * @var array
 */
	protected $__instance = null;

	public function __construct(View $View, $settings = array()) {
		parent::__construct($View, $settings);
		static $instance;

        if ($instance === NULL) {
        	$instance = new MyHtmlHelperInstance($View, $settings, $this);
        }
        $this->__instance =& $instance;

        // HTML5
        $this->_tags['javascriptblock'] = '<script%s>%s</script>';
        $this->_tags['javascriptstart'] = '<script>';
        $this->_tags['javascriptlink'] = '<script src="%s"%s></script>';
        $this->_tags['css'] = '<link rel="%s" type="text/css" href="%s" %s>';
	}

	public function css($path, $rel = null, $options = array()) {
		return $this->__instance->css($path, $rel, $options);
	}

	public function script($url, $options = array()) {
		return $this->__instance->script($url, $options);
	}

	public function fetchScript($name, $attributes = null) {
		return $this->__instance->fetchScript($name, $attributes);
	}

	public function fetchCss($name, $attributes = null) {
		return $this->__instance->fetchCss($name, $attributes);
	}
}

class MyHtmlHelperInstance extends AppHelper {
/**
 * Holds the model references already loaded by this helper
 * product of trying to inspect them out of field names
 *
 * @var array
 */
	protected $_models = array();

/**
 * block毎のscriptリスト
 *
 * @var array
 */
	protected $_blockScripts = array();

/**
 * Names of script files that have been included once
 *
 * @var array
 */
	protected $_includedScripts = array();

/**
 * Names of css files that have been included once
 *
 * @var array
 */
	protected $_includedCsses = array();

/**
 * htmlヘルパー
 *
 * @var object
 */
	protected $_htmlHelper = null;

/**
 * block毎のcssリスト
 *
 * @var array
 */
	protected $_blockCsses = array();

	public function __construct(View $View, $settings = array(), $_htmlHelper = null) {
		parent::__construct($View, $settings);
		$this->_htmlHelper = $_htmlHelper;
	}

/**
 * Creates a link element for CSS stylesheets.
 *
 * ### Usage
 *
 * Include one CSS file:
 *
 * `echo $this->Html->css('styles.css');`
 *
 * Include multiple CSS files:
 *
 * `echo $this->Html->css(array('one.css', 'two.css'));`
 *
 * Add the stylesheet to the `$scripts_for_layout` layout var:
 *
 * `$this->Html->css('styles.css', null, array('inline' => false));`
 *
 * Add the stylesheet to a custom block:
 *
 * `$this->Html->css('styles.css', null, array('block' => 'layoutCss'));`
 *
 * ### Options
 *
 * - `inline` If set to false, the generated tag will be appended to the 'css' block,
 *   and included in the `$scripts_for_layout` layout variable. Defaults to true.
 * - `block` Set the name of the block link/style tag will be appended to.  This overrides the `inline`
 *   option.
 * - `plugin` False value will prevent parsing path as a plugin
 *
 * @param string|array $path The name of a CSS style sheet or an array containing names of
 *   CSS stylesheets. If `$path` is prefixed with '/', the path will be relative to the webroot
 *   of your application. Otherwise, the path will be relative to your CSS path, usually webroot/css.
 * @param string $rel Rel attribute. Defaults to "stylesheet". If equal to 'import' the stylesheet will be imported.
 * @param array $options Array of HTML attributes.
 * @return string CSS <link /> or <style /> tag, depending on the type of link.
 * @link http://book.cakephp.org/2.0/en/core-libraries/helpers/html.html#HtmlHelper::css
 */
	public function css($path, $rel = null, $options = array()) {
		if($this->_htmlHelper->request->is('ajax')) {
			$inline = true;
		} else {
			$inline = false;
		}

		$options += array('block' => null, 'inline' => $inline, 'once' => true);
		if (!$options['inline'] && empty($options['block'])) {
			$options['block'] = __FUNCTION__;
		}
		unset($options['inline']);

		$paths_arr = array();
		if (is_array($path)) {
			$paths_arr = $path;
		} else {
			$paths_arr[] = $path;
		}

		if ($rel == null) {
			$rel = 'stylesheet';
		}
		$attributes = $this->_htmlHelper->_parseAttributes($options, array('frame','inline', 'block', 'once'), '', ' ');
		$block_key = empty($options['block'])  ? '_inline' : $options['block'];
		if($block_key == '_inline') {
			unset($this->_blockCsses[$block_key]);
		}

		$out = '';
		foreach($paths_arr as $path) {
			if ($options['once'] && isset($this->_includedCsses[$path])) {
				continue;
			}
			$this->_includedCsses[$path] = true;
			if ($rel == 'import') {
				$url = $path;
				$out .= "\t".sprintf($this->_htmlHelper->_tags['style'], $attributes, '@import url(' . $url . ');')."\n";
			} else if (strpos($path, '//') !== false) {
				$url = $path;
				$out .= "\t".sprintf($this->_htmlHelper->_tags['css'], $rel, $url, $attributes)."\n";
			} else {
				if(substr($path, -1) == '/' || substr($path, -1) == '.') {
					list($url, $plugin, $realPath) = $this->myAssetUrl($path, $options + array('pathPrefix' => CSS_URL));
				} else {
					list($url, $plugin, $realPath) = $this->myAssetUrl($path, $options + array('pathPrefix' => CSS_URL, 'ext' => '.css'));
				}
				if (Configure::read('Asset.filter.css')) {
					$pos = strpos($url, CSS_URL);
					if ($pos !== false) {
						$url = substr($url, 0, $pos) . 'ccss/' . substr($url, $pos + strlen(CSS_URL));
					}
				}
				$this->_blockCsses[$block_key][$rel][] = array(
					'url' => $url,
					'plugin' => $plugin,
					'realPath' => $realPath,
					'ext' => 'css',
					//'attributes' => $attributes,
				);
			}
		}

		$out .= $this->fetchCss($block_key, $attributes);

		if($out !== '') {
			if($block_key == '_inline') {
				return $out;
			} else {
				$this->_htmlHelper->_View->append($options['block'], $out);
				//$this->_htmlHelper->_View->assign($options['block'], $out);
			}
		}
	}

/**
 * Returns one or many `<script>` tags depending on the number of scripts given.
 *
 * If the filename is prefixed with "/", the path will be relative to the base path of your
 * application.  Otherwise, the path will be relative to your JavaScript path, usually webroot/js.
 *
 *
 * ### Usage
 *
 * Include one script file:
 *
 * `echo $this->Html->script('styles.js');`
 *
 * Include multiple script files:
 *
 * `echo $this->Html->script(array('one.js', 'two.js'));`
 *
 * Add the script file to the `$scripts_for_layout` layout var:
 *
 * `$this->Html->script('styles.js', array('inline' => false));`
 *
 * Add the script file to a custom block:
 *
 * `$this->Html->script('styles.js', null, array('block' => 'bodyScript'));`
 *
 * ### Options
 *
 * - `inline` Whether script should be output inline or into `$scripts_for_layout`. When set to false,
 *   the script tag will be appended to the 'script' view block as well as `$scripts_for_layout`.
 * - `block` The name of the block you want the script appended to.  Leave undefined to output inline.
 *   Using this option will override the inline option.
 * - `once` Whether or not the script should be checked for uniqueness. If true scripts will only be
 *   included once, use false to allow the same script to be included more than once per request.
 * - `plugin` False value will prevent parsing path as a plugin
 *
 * @param string|array $url String or array of javascript files to include
 * @param array|boolean $options Array of options, and html attributes see above. If boolean sets $options['inline'] = value
 * @return mixed String of `<script />` tags or null if $inline is false or if $once is true and the file has been
 *   included before.
 * @link http://book.cakephp.org/2.0/en/core-libraries/helpers/html.html#HtmlHelper::script
 */
	public function script($url, $options = array()) {
		if($this->_htmlHelper->request->is('ajax')) {
			$inline = true;
		} else {
			$inline = false;
		}

		if (is_bool($options)) {
			list($inline, $options) = array($options, array());
			$options['inline'] = $inline;
		}
		$options = array_merge(array('block' => null, 'inline' => $inline, 'once' => true), $options);
		if (!$options['inline'] && empty($options['block'])) {
			$options['block'] = __FUNCTION__;
		}
		unset($options['inline']);

		$urls_arr = array();
		if (is_array($url)) {
			$urls_arr = $url;
		} else {
			$urls_arr[] = $url;
		}

		//$attributes = $this->_htmlHelper->_parseAttributes($options, array('block', 'once'), ' ');
		$block_key = empty($options['block'])  ? '_inline' : $options['block'];
		if($block_key == '_inline') {
			unset($this->_blockScripts[$block_key]);
		}
		$out = '';
		$attributes = $this->_htmlHelper->_parseAttributes($options, array('block', 'once'), ' ');
		foreach($urls_arr as $url) {
			if ($options['once'] && isset($this->_includedScripts[$url])) {
				continue;
			}
			$this->_includedScripts[$url] = true;
			if (strpos($url, '//') === false) {
				if(substr($url, -1) == '/' || substr($url, -1) == '.') {
					list($url, $plugin, $realPath) = $this->myAssetUrl($url, $options + array('pathPrefix' => JS_URL));
				} else {
					list($url, $plugin, $realPath) = $this->myAssetUrl($url, $options + array('pathPrefix' => JS_URL, 'ext' => '.js'));
				}

				if (Configure::read('Asset.filter.js')) {
					$url = str_replace(JS_URL, 'cjs/', $url);
				}
				$this->_blockScripts[$block_key][] = array(
					'url' => $url,
					'plugin' => $plugin,
					'realPath' => $realPath,
					'ext' => 'js',
					//'attributes' => $attributes,
				);
			} else {
				$out .= "\t".sprintf($this->_htmlHelper->_tags['javascriptlink'], $url, $attributes)."\n";
			}
		}

		$out .= $this->fetchScript($block_key, $attributes);
		if($out !== '') {
			if($block_key == '_inline') {
				return $out;
			} else {
				$this->_htmlHelper->_View->append($options['block'], $out);
				//$this->_htmlHelper->_View->assign($options['block'], $out);
			}
		}
	}

/**
 * Fetch Script Block出力(View->fetchのAsset Pipeline版)
 *
 * @param string $name Name of the block
 * @param string $attributes
 * @return string Script Tag
 */
	public function fetchScript($name, $attributes = null) {
		$out = '';
		if(isset($this->_blockScripts[$name])) {
			$Asset = $this->_getModel('Asset');
			$rets = $Asset->findByUrls($this->_blockScripts[$name]);

			foreach($rets as $plugin => $ret) {
				if($ret) {
					$buf_attributes = $attributes;
					if(empty($buf_attributes)) {
						$buf_attributes = " data-title = \"$plugin\"";
					}
					$url = "{$this->_htmlHelper->request->webroot}theme/assets/" . $ret;
					$out .= "\t".sprintf($this->_htmlHelper->_tags['javascriptlink'], $url, $buf_attributes)."\n";
				}
			}

		}
		return $out;
	}

/**
 * Fetch Css Block出力(View->fetchのAsset Pipeline版)
 *
 * @param string $name Name of the block
 * @param string $attributes
 * @return string Script Tag
 */
	public function fetchCss($name, $attributes = null) {
		$out = '';
		if(isset($this->_blockCsses[$name])) {
			$Asset = $this->_getModel('Asset');

			if(count($this->_blockCsses[$name]) > 0) {

				foreach($this->_blockCsses[$name] as $rel => $blockCss) {
					$rets = $Asset->findByUrls($blockCss);
					foreach($rets as $plugin => $ret) {
						if($ret) {
							$buf_attributes = $attributes;
							if(empty($buf_attributes)) {
								$buf_attributes = " data-title = \"$plugin\"";
							}
							$url = "{$this->_htmlHelper->request->webroot}theme/assets/" . $ret;
							$out .= "\t".sprintf($this->_htmlHelper->_tags['css'], $rel, $url, $buf_attributes)."\n";
						}
					}
				}
			}
		}
		return $out;
	}
/**
 * Guess the location for a model based on its name and tries to create a new instance
 * or get an already created instance of the model
 * FormHelperと同様のメソッド
 *
 * @param string $model
 * @return Model model instance
 */
	protected function _getModel($model) {
		$object = null;
		if (!$model || $model === 'Model') {
			return $object;
		}

		if (array_key_exists($model, $this->_models)) {
			return $this->_models[$model];
		}

		if (ClassRegistry::isKeySet($model)) {
			$object = ClassRegistry::getObject($model);
		} elseif (isset($this->_htmlHelper->request->params['models'][$model])) {
			$plugin = $this->_htmlHelper->request->params['models'][$model]['plugin'];
			$plugin .= ($plugin) ? '.' : null;
			$object = ClassRegistry::init(array(
				'class' => $plugin . $this->_htmlHelper->request->params['models'][$model]['className'],
				'alias' => $model
			));
		} elseif (ClassRegistry::isKeySet($this->_htmlHelper->defaultModel)) {
			$defaultObject = ClassRegistry::getObject($this->_htmlHelper->defaultModel);
			if (in_array($model, array_keys($defaultObject->getAssociated()), true) && isset($defaultObject->{$model})) {
				$object = $defaultObject->{$model};
			}
		} else {
			$object = ClassRegistry::init($model, true);
		}

		$this->_models[$model] = $object;
		if (!$object) {
			return null;
		}

		$this->_htmlHelper->fieldset[$model] = array('fields' => null, 'key' => $object->primaryKey, 'validates' => null);
		return $object;
	}

/**
 * Generate url for given asset file. Depending on options passed provides full url with domain name.
 * Also calls Helper::assetTimestamp() to add timestamp to local files
 *
 * @param string|array Path string or url array
 * @param array $options Options array. Possible keys:
 *   `fullBase` Return full url with domain name
 *   `pathPrefix` Path prefix for relative urls
 *   `ext` Asset extension to append
 *   `plugin` False value will prevent parsing path as a plugin
 * @return string Generated url
 */
	public function myAssetUrl($path, $options = array()) {
		/*if (strpos($path, '.') === false && strpos($path, '/') === false && isset($path[0]) && ctype_upper($path[0])) {
			// PluginNameのみの指定でも読み込む
			$path .= '.';
		}*/
		if (array_key_exists('frame', $options) && $options['frame'] === true) {
			// ブロックテーマ
			list($frame, $path) = pluginSplit($path);
		} else if (!array_key_exists('plugin', $options) || $options['plugin'] !== false) {
			list($plugin, $path) = $this->_htmlHelper->_View->pluginSplit($path, false);
		}
		$is_pathPrefix = false;
		//if (!empty($options['pathPrefix']) && $path[0] !== '/') {
		if (!empty($options['pathPrefix']) && (!isset($path[0]) || $path[0] !== '/')) {
			$is_pathPrefix = true;
			$path = $options['pathPrefix'] . $path;
		}
		if (
			!empty($options['ext']) &&
			strpos($path, '?') === false &&
			substr($path, -strlen($options['ext'])) !== $options['ext']
		) {
			$path .= $options['ext'];
		}
		if (array_key_exists('frame', $options) && $options['frame'] === true) {
			// ブロックテーマ
			if (isset($frame)) {
				$path = Inflector::underscore($frame) . '/' . $path;
			}
			list($path, $plugin, $realPath) = h($this->_htmlHelper->assetTimestamp($this->myWebrootFrame($path)));
		} else {
			if (isset($plugin)) {
				$path = Inflector::underscore($plugin) . '/' . $path;
			}
			list($path, $plugin, $realPath) = h($this->_htmlHelper->assetTimestamp($this->myWebroot($path)));
		}
		if (!empty($options['fullBase'])) {
			$base = $this->_htmlHelper->url('/', true);
			$len = strlen($this->_htmlHelper->request->webroot);
			if ($len) {
				$base = substr($base, 0, -$len);
			}
			$path = $base . $path;
		}
		return array($path, $plugin, $realPath);
	}

/**
 * Checks if a file exists when theme is used, if no file is found default location is returned
 * Plugins - Themesの設定で、以下のパスを優先的にみるように修正(plugin内でテンプレートを完結させるため)。
 * 		app/Plugin/(Plugin Name)/View/Themed/(Theme Name)/webroot/css/hoge.css
 * 		app/Plugin/(Plugin Name)/webroot/theme/(theme name)/js/hoge.js
 * 注：Plugin NameでJsとCssは禁止
 *
 * @param string $file The file to create a webroot path to.
 * @return string Web accessible path to file.
 */
	public function myWebroot($file) {
		$asset = explode('?', $file);
		if(isset($asset[0][0]) && $asset[0][0] !== '/') {
			$asset[0] = '/'. $asset[0];
		}
		$asset[1] = isset($asset[1]) ? '?' . $asset[1] : null;
		$webPath = $asset[0];
		//$webPath = "{$this->_htmlHelper->request->webroot}" . $asset[0];
		$file = $asset[0];
		$realPath = $webPath;

		$file = trim($file, '/');
		if (DS === '\\') {
			$file = str_replace('/', '\\', $file);
		}
		$filePath = explode(DS, $file);
		$pluginName = array_shift($filePath);
		$plugin = Inflector::camelize($pluginName);

		$setPath = false;
		if (!empty($this->_htmlHelper->theme)) {
			//$file = trim($file, '/');
			$theme = $this->_htmlHelper->theme;	// . '/';

			//if (DS === '\\') {
			//	$file = str_replace('/', '\\', $file);
			//}

			//$filePath = explode(DS, $file);
			//$pluginName = array_shift($filePath);
			//$plugin = Inflector::camelize($pluginName);
			if($plugin != 'Js' && $plugin != 'Css' && CakePlugin::loaded($plugin)) {
				$pluginPath = CakePlugin::path($plugin);
				//$pluginPath = App::pluginPath($plugin);
				$buf_path = urldecode(implode(DS, $filePath));
				$path = $pluginPath . 'webroot' . DS . 'theme' . DS . $this->_htmlHelper->theme . DS . $buf_path;
				if (file_exists($path)) {
					$webPath = "/theme/" . $theme . $asset[0];
					//$webPath = "{$this->_htmlHelper->request->webroot}theme/" . $theme . $asset[0];
					$setPath = true;
					$realPath = $path;
				} else {
					$path = $pluginPath . 'View' . DS . 'Themed' . DS . $this->_htmlHelper->theme . DS . 'webroot' . DS . $buf_path;
					if (file_exists($path)) {
						$webPath = "/theme/" . $theme . $asset[0];
						//$webPath = "{$this->_htmlHelper->request->webroot}theme/" . $theme . $asset[0];
						$setPath = true;
						$realPath = $path;
					}
				}
			}
			if(!$setPath) {
				$path = Configure::read('App.www_root') . 'theme' . DS . $this->_htmlHelper->theme . DS . $file;
				if (file_exists($path)) {
					$webPath = "/theme/" . $theme . $asset[0];
					//$webPath = "{$this->_htmlHelper->request->webroot}theme/" . $theme . $asset[0];
					$setPath = true;
					$realPath = $path;
				} else {
					$themePath = App::themePath($this->_htmlHelper->theme);
					$path = $themePath . 'webroot' . DS . $file;
					if (file_exists($path)) {
						$webPath = "/theme/" . $theme . $asset[0];
						//$webPath = "{$this->_htmlHelper->request->webroot}theme/" . $theme . $asset[0];
						$setPath = true;
						$realPath = $path;
					}
				}
			}
		}
		if(!$setPath) {
			if($plugin != 'Js' && $plugin != 'Css' && CakePlugin::loaded($plugin)) {
				$pluginPath = CakePlugin::path($plugin);
				$buf_path = urldecode(implode(DS, $filePath));
				$path = $pluginPath . 'webroot' . DS . $buf_path;
				if (file_exists($path)) {
					$webPath = $asset[0];
					//$webPath = "{$this->_htmlHelper->request->webroot}" . $asset[0];
					$setPath = true;
					$realPath = $path;
				} else {
					$path = $pluginPath . 'View' . DS . 'webroot' . DS . $buf_path;
					if (file_exists($path)) {
						$webPath = $asset[0];
						//$webPath = "{$this->_htmlHelper->request->webroot}" . $asset[0];
						$setPath = true;
						$realPath = $path;
					}
				}
			} else {
				$path = Configure::read('App.www_root') . $file;
				if (file_exists($path)) {
					$webPath = $asset[0];
					//$webPath = "{$this->_htmlHelper->request->webroot}" . $asset[0];
					$setPath = true;
					$realPath = $path;
				}
			}
		}
		if (strpos($webPath, '//') !== false) {
			$webPath = str_replace('//', '/', $webPath . $asset[1]);
			return array($webPath . $asset[1], $plugin, $webPath);
			//return str_replace('//', '/', $webPath . $asset[1]);
		}

		// plugins名毎にScript、CSSを出力する。
		if(preg_match('%^/css/plugins/%', $webPath)) {
			$plugin = basename($webPath);
		}else if(preg_match('%^/js/plugins/%', $webPath)) {
			$plugin = basename($webPath);
		} else if(preg_match('%^/js/locale/%', $webPath)) {
			$plugin = 'Nc-Locale';
		}

		return array($webPath . $asset[1], $plugin, $realPath);
	}

/**
 * Checks if a file exists when theme is used, if no file is found default location is returned
 * Plugins - Themesの設定で、以下のパスを優先的にみるように修正(plugin内でテンプレートを完結させるため)。
 * 		app/Plugin/(Plugin Name)/View/Themed/(Theme Name)/webroot/css/hoge.css
 * 		app/Plugin/(Plugin Name)/webroot/theme/(theme name)/js/hoge.js
 * 注：Plugin NameでJsとCssは禁止
 *
 * @param string $file The file to create a webroot path to.
 * @return string Web accessible path to file.
 */
	public function myWebrootFrame($file) {
		$asset = explode('?', $file);
		if(isset($asset[0][0]) && $asset[0][0] !== '/') {
			$asset[0] = '/'. $asset[0];
		}
		$asset[1] = isset($asset[1]) ? '?' . $asset[1] : null;
		$webPath = $asset[0];
		$file = $asset[0];
		$realPath = $webPath;

		$file = trim($file, '/');
		if (DS === '\\') {
			$file = str_replace('/', '\\', $file);
		}
		$filePath = explode(DS, $file);
		$frameName = array_shift($filePath);
		$frame = Inflector::camelize($frameName);

		//unset($parts[0]);
		$fileFragment = urldecode(implode(DS, $filePath));
		$frameDir = $frame . DS .'webroot';
		$paths = App::path('Frame');
		$framePath = null;
		foreach ($paths as $path) {
			if (is_dir($path . 'Plugin' . DS . $frameDir)) {
				$framePath = $path . 'Plugin' . DS . $frameDir . DS;
				break;
			}
		}
		if (file_exists($framePath . $fileFragment)) {
			$realPath = $framePath . $fileFragment;
		}
		return array($webPath . $asset[1], 'Nc-Frame', $realPath);	// plugin名 Frame固定
		////return array($webPath . $asset[1], 'Frame:'.$frame, $realPath);
	}
}