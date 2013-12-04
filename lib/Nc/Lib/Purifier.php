<?php
/**
 * Purifier
 *
 * Nc用にPlugin/HtmlPurifier/Lib/Purifierを拡張
 * WYSIWYGでAuthority.allow_htmltag_flag=_ONならば、タグの整形（閉じ忘れ等の整形）をし、onclick等の属性もそのまま登録するため。
 * （基本的にPlugin/HtmlPurifier/Lib/Purifierと同等）
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Lib.Purifier
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class Purifier extends Object {
/**
 * Purifier configurations
 *
 * @var array
 */
	protected $_configs = array();

/**
 * HTMLPurifier instances
 *
 * @var array
 */
	protected $_instances = array();

/**
 * Return a singleton instance of the StorageManager.
 *
 * @return ClassRegistry instance
 */
	public static function &getInstance() {
		static $instance = array();
		if (!$instance) {
			$instance[0] = new Purifier();
		}
		return $instance[0];
	}

/**
 * Config
 *
 * @param string $markup
 * @param string $configName
 */
	public static function config($configName, $config = null) {
		$_this = Purifier::getInstance();

		if (empty($config)) {
			if (!isset($_this->_configs[$configName])) {
				throw new InvalidArgumentException(__('Configuration %s does not exist!', $configName));
			}
			return $_this->_configs[$configName];
		}

		if (is_array($config)) {
			$purifierConfig = HTMLPurifier_Config::createDefault();
			foreach ($config as $key => $value) {
				$purifierConfig->set($key, $value);
			}

			return $_this->_configs[$configName] = $purifierConfig;
		} elseif (is_object($config) && is_a($config, 'HTMLPurifier_Config')) {
			return $_this->_configs[$configName] = $config;
		} else {
			throw new InvalidArgumentException('Invalid config passed');
		}
	}

/**
 * Gets an instance of the purifier lib only when needed, lazy loading it
 *
 * @param string $configName
 * @return HTMLPurifier
 */
	public static function getPurifierInstance($configName = null) {
		$_this = Purifier::getInstance();

		if (!isset($_this->_instances[$configName])) {
			if (!isset($_this->_configs[$configName])) {
				throw new InvalidArgumentException(__('Configuration and instance %s does not exist!', $name));
			}
			$_this->_instances[$configName] = new HTMLPurifier($_this->_configs[$configName]);
		}

		return $_this->_instances[$configName];
	}

/**
 * Cleans Markup using a given config
 *
 * @param string $markup
 * @param string $configName None or Auto or Purify
 * 			Auto:  Authority.allow_htmltag_flag=_ONならば、None、そうでないならばPurifyを通す。
 * 			None:  タグの整形（閉じ忘れ等の整形）のみ
 * 			Purify:タグの整形、XSS攻撃の可能性があるタグの除去
 */
	public static function clean($markup, $configName = null) {
		$_this = Purifier::getInstance();
// Add for NetCommons Extentions By Ryuji.M --START
		$notPurify = false;
		if($configName == 'Auto') {
			$loginUser = Configure::read(NC_SYSTEM_KEY.'.user');
			$Authority = ClassRegistry::init('Authority');
			$allowHtmltagFlag = isset($loginUser['allow_htmltag_flag']) ? intval($loginUser['allow_htmltag_flag']) : _OFF;
			if($allowHtmltagFlag) {
				$configName = 'None';
			} else {
				$configName = 'Purify';
			}
		}
// Add for NetCommons Extentions By Ryuji.M --E N D
		if (!isset($_this->_configs[$configName])) {
			throw new InvalidArgumentException(__('Invalid configuration %s!', $configName));
		}
// Add for NetCommons Extentions By Ryuji.M --START
		if($configName == 'None') {
			// data-***属性をすべてOKにするため
			// 強制的にinfo_global_attrにセットすることにより対応。
			$instance = $_this->getPurifierInstance($configName);
			$config = $instance->config;
			$def = $config->getHTMLDefinition(true);
			$context = new HTMLPurifier_Context();

			$lexer = HTMLPurifier_Lexer::create($config);
			$markup = HTMLPurifier_Encoder::convertToUTF8($markup, $config, $context);
			$tokens = $lexer->tokenizeHTML(
				// un-purified HTML
				$markup,
				$config,
				$context
			);
			foreach($tokens as $token) {
				if(isset($token->attr) && count($token->attr) > 0) {
					foreach($token->attr as $key => $attr) {
						if(preg_match ('/^data-.?/',$key)) {
							// data-XXX属性をすべてOK
							$def->info_global_attr[$key] = new HTMLPurifier_AttrDef_Text();
						}
					}
				}
			}
			$generator = new HTMLPurifier_Generator($config, $context);
			$strategy = new HTMLPurifier_Strategy_Core();

			$markup =
			$generator->generateFromTokens(
					// list of tokens
					$strategy->execute(
						// list of un-purified tokens
						$tokens,
						$config,
						$context
					)
			);
			$markup = HTMLPurifier_Encoder::convertFromUTF8($markup, $config, $context);
			return $markup;
		} else {
			return $_this->getPurifierInstance($configName)->purify($markup);
		}
		//return $_this->getPurifierInstance($configName)->purify($markup);
// Add for NetCommons Extentions By Ryuji.M --E N D
	}

// Add for NetCommons Extentions By Ryuji.M --START
	/**
	 * Nc用Configセット処理
	 *
	 * @param void
	 */
	public static function setNcConfig()
	{
		Purifier::_setNcConfig('Purify');
		Purifier::_setNcConfig('None');
	}

	/**
	 * Nc用Config詳細セット処理
	 *
	 * @param string
	 */
	protected static function _setNcConfig($configName = 'Purify')
	{

		$config = HTMLPurifier_Config::createDefault();
		//$config->set('Core.LexerImpl', 'PH5P');
		$config->set('Cache.SerializerPath', CACHE . 'htmlpurifier');
		if($configName == 'None') {
			$config->set('HTML.Trusted', true);
		}
		Purifier::_setNcConfigAllowVideo($config);
		//$config->set('HTML.SafeObject', true);
		//$config->set('Output.FlashCompat', true);
		//$config->set('Core.Encoding', 'UTF-8'); // replace with your encoding

		//$config->set('HTML.TidyLevel', 'light');
		//$config->set('HTML.ForbiddenElements', array('script','style','applet'));

		$def = $config->getHTMLDefinition(true);

		Purifier::_setNcConfigAllowHtml5($def);

		$def->addAttribute('a', 'target', new HTMLPurifier_AttrDef_Enum(
			array('_blank','_self','_target','_top')
		));

		if($configName == 'None') {
			Purifier::_setNcConfigAllowScriptEvents($config, $def);
		}

		Purifier::config($configName, $config);
	}


	/**
	 * Nc用Configセット処理　Html5用
	 *
	 * @param object $def
	 */
	protected static function _setNcConfigAllowHtml5($def)
	{
		// Add Html5 elements
		// name,content set,allowed children,attribute collection
		$def->addElement('section', 'Block', 'Flow', 'Common');
		$def->addElement('article', 'Block', 'Flow', 'Common');
		$def->addElement('aside', 'Block', 'Flow', 'Common');
		$def->addElement('header', 'Block', 'Flow', 'Common');
		$def->addElement('footer', 'Block', 'Flow', 'Common');
		$def->addElement('nav', 'Block', 'Flow', 'Common');

		$def->addElement('figure', 'Block', 'Flow', 'Common');
		$def->addElement('mark', 'Block', 'Flow', 'Common');
		$def->addElement('time', 'Block', 'Flow', 'Common');
		$def->addElement('meter', 'Block', 'Flow', 'Common');
		$def->addElement('ruby', 'Block', 'Flow', 'Common');
		$def->addElement('rt', 'Block', 'Flow', 'Common');
		$def->addElement('rp', 'Block', 'Flow', 'Common');
		$def->addElement('menu', 'Block', 'Flow', 'Common');
		$def->addElement('address', 'Block', 'Flow', 'Common');

		// progress,audio,video,canvas,command,datagrid,details,datalist,keygen,output,area,base
	}

	/**
	 * Nc用Configセット処理　管理者用
	 *
	 * @param object $config
	 */
	protected static function _setNcConfigAllowVideo($config)
	{
		$loginUser = Configure::read(NC_SYSTEM_KEY.'.user');
		$allowVideo = _OFF;
		if(isset($loginUser['allow_video'])) {
			$allowVideo =  intval($loginUser['allow_video']);
		}
		if($allowVideo) {
			$config->set('Filter.YouTube', true);

			$config->set('HTML.SafeIframe', true);
			$config->set('URI.SafeIframeRegexp', '%^(https?:)?//(www\.youtube(?:-nocookie)?\.com/embed/|maps\.google\.com/|www\.slideshare\.net/)%');
			// 			$config->set('HTML.Trusted', true);
			// 			$config->set('HTML.DefinitionID', '1');
			// 			$config->set('HTML.SafeObject', 'true');
			// 			$config->set('Output.FlashCompat', 'true');

			// 			$config->set('HTML.FlashAllowFullScreen', 'true');
		}
	}


	/**
	 * Nc用Configセット処理　管理者用
	 *
	 * @param object $config
	 * @param object $def
	 */
	protected static function _setNcConfigAllowScriptEvents($config, $def)
	{
		$def->info_global_attr['onblur'] = new HTMLPurifier_AttrDef_Text();
		$def->info_global_attr['onchange'] = new HTMLPurifier_AttrDef_Text();
		$def->info_global_attr['onclick'] = new HTMLPurifier_AttrDef_Text();
		$def->info_global_attr['ondblclick'] = new HTMLPurifier_AttrDef_Text();
		$def->info_global_attr['onerror'] = new HTMLPurifier_AttrDef_Text();
		$def->info_global_attr['onfocus'] = new HTMLPurifier_AttrDef_Text();
		$def->info_global_attr['onkeydown'] = new HTMLPurifier_AttrDef_Text();
		$def->info_global_attr['onkeypress'] = new HTMLPurifier_AttrDef_Text();
		$def->info_global_attr['onkeyup'] = new HTMLPurifier_AttrDef_Text();
		$def->info_global_attr['onmousedown'] = new HTMLPurifier_AttrDef_Text();
		$def->info_global_attr['onmousemove'] = new HTMLPurifier_AttrDef_Text();
		$def->info_global_attr['onmouseout'] = new HTMLPurifier_AttrDef_Text();
		$def->info_global_attr['onmouseover'] = new HTMLPurifier_AttrDef_Text();
		$def->info_global_attr['onmouseup'] = new HTMLPurifier_AttrDef_Text();
		$def->info_global_attr['onresize'] = new HTMLPurifier_AttrDef_Text();
		$def->info_global_attr['onselect'] = new HTMLPurifier_AttrDef_Text();
		$def->info_global_attr['onunload'] = new HTMLPurifier_AttrDef_Text();

		///$def->info_global_attr['data-*'] = new HTMLPurifier_AttrDef_Text();



		//$def->addElement('script', 'Block', 'Flow', 'Common');


		//$allowed = $config->get('HTML.SafeScripting');
// 		$def->addAttribute('img', 'data-type', 'Text');
// 		$def->addElement(
// 				'script',
// 	            'Block',
// 	            'Flow',
// 	            null,
// 				array(
// 					// While technically not required by the spec, we're forcing
// 					// it to this value.
// 					'type' => 'Enum#text/javascript',
// 					'src' => new HTMLPurifier_AttrDef_Text(),
// 					'charset' => new HTMLPurifier_AttrDef_Text(),
// 				)
// 		);

		//$config->set('HTML.SafeScripting', array());

	}
// Add for NetCommons Extentions By Ryuji.M --E N D
}