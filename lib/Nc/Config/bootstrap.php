<?php
/**
 * NC用
 */

require 'defines.inc.php';

/**
 * You can attach event listeners to the request lifecyle as Dispatcher Filter . By Default CakePHP bundles two filters:
 *
 * - AssetDispatcher filter will serve your asset files (css, images, js, etc) from your themes and plugins
 * - CacheDispatcher filter will read the Cache.check configure variable and try to serve cached content generated from controllers
 *
 * Feel free to remove or add filters as you see fit for your application. A few examples:
 *
 * Configure::write('Dispatcher.filters', array(
 *		'MyCacheFilter', //  will use MyCacheFilter class from the Routing/Filter package in your app.
 *		'MyPlugin.MyFilter', // will use MyFilter class from the Routing/Filter package in MyPlugin plugin.
 * 		array('callable' => $aFunction, 'on' => 'before', 'priority' => 9), // A valid PHP callback type to be called on beforeDispatch
 *		array('callable' => $anotherMethod, 'on' => 'after'), // A valid PHP callback type to be called on afterDispatch
 *
 * ));
 */
Configure::write('Dispatcher.filters', array(
	'NcAssetDispatcher',
	'CacheDispatcher'
));
Configure::write('Exception', array(
	'handler' => 'ErrorHandler::handleException',
	'renderer' => 'NcExceptionRenderer',
	'log' => true
));

if (file_exists(APP . 'Config' .DS . NC_INSTALL_INC_FILE)) {
	include_once APP . 'Config' .DS . NC_INSTALL_INC_FILE;
}
if (file_exists(dirname(__FILE__) . DS . NC_VERSION_FILE)) {
	include_once dirname(__FILE__) . DS . NC_VERSION_FILE;
}

if (!defined('PEAR')) {
    // PEAR定数が定義されていなければ定義する。
    define('PEAR', VENDORS . 'pear'.DS);
}
// include_path に pearのライブラリパスを追加
set_include_path(PEAR . PATH_SEPARATOR . get_include_path());

App::build(array(
	'Lib'                       => array(NC),
	'Console'                    => array(APP . 'Console' . DS, NC . 'Console'),
	'Console/Command'            => array(APP . 'Console' . DS . 'Command' . DS, NC . 'Console' . DS . 'Command' . DS),
	'Model'                     => array(APP . 'Model' . DS, NC . 'Model' . DS),
	'Model/Behavior'            => array(APP . 'Model' . DS . 'Behavior' . DS, NC . 'Model' . DS . 'Behavior' . DS),
	'Model/Datasource'          => array(APP.'Model' . DS . 'Datasource' . DS, NC . 'Model' . DS . 'Datasource' . DS),

	//'Model'                     => array(APP . 'Model' . DS, NC . 'Model' . DS),
	//'Model/Behavior'            => array(APP . 'Model' . DS . 'Behavior' . DS, NC . 'Model' . DS . 'Behavior' . DS),
	//'Model/Datasource'          => array(APP.'Model' . DS . 'Datasource' . DS, NC . 'Model' . DS . 'Datasource' . DS),
	//'Model/Datasource/Database' => array('/path/to/databases', '/next/path/to/database'),
	//'Model/Datasource/Session'  => array('/path/to/sessions', '/next/path/to/sessions'),
	'Controller'                => array(APP . 'Controller' . DS, NC . 'Controller' . DS),
	'Controller/Component'      => array(APP . 'Controller' . DS . 'Component' . DS, NC . 'Controller' . DS . 'Component' . DS),
	//'Controller/Component/Auth' => array('/path/to/auths', '/next/path/to/auths'),
	//'Controller/Component/Acl'  => array('/path/to/acls', '/next/path/to/acls'),
	'View'                      => array(APP . 'View' . DS, NC . 'View' . DS),
	'View/Helper'               => array(APP . 'View' . DS . 'Helper' . DS, NC . 'View' . DS . 'Helper' . DS),
	//'Console'                   => array('/path/to/consoles', '/next/path/to/consoles'),
	//'Console/Command'           => array('/path/to/commands', '/next/path/to/commands'),
	//'Console/Command/Task'      => array('/path/to/tasks', '/next/path/to/tasks'),
	//'Lib'                       => array('/path/to/libs', '/next/path/to/libs'),
	'Locale'                    => array(APP . 'Locale' . DS, NC . 'Locale' . DS),
	'Vendor'                    => array(APP . 'Vendor' . DS, NC . 'Vendor' . DS),
	'Plugin'                    => array(APP . 'Plugin' . DS, ROOT . DS . 'Plugin' . DS, NC . 'Plugin' . DS),
));
// ブロック用テーマ - webroot
App::build(array(
	'Frame'                    => array(APP . 'Frame' . DS, NC . 'Frame' . DS),
	'webroot'                    => array(APP . 'webroot' . DS, NC . 'webroot' . DS),
), App::RESET);

CakePlugin::loadAll(array(array('routes' => true)));	// Loads all plugins at once		// array(array('routes' => true))     'Blog' => array('routes' => true)
CakePlugin::load('MobileDetect');

// 同一ドメインに複数インストールしてあっても、他のサイトのクッキーを送信しないようにするため、
// cookie_pathを変更。
$path = '/';
$baseDir = str_replace('\\', '/', ROOT);
$bufPath = preg_replace('/^' . preg_quote($_SERVER['DOCUMENT_ROOT'], '/').'/i', '', $baseDir);
if ($baseDir != $bufPath && $bufPath != '') {
	$path = (substr($bufPath, 0, 1) == '/') ? $bufPath : '/'.$bufPath;
}
Configure::write('Session', array(
	'defaults' => 'database',
	'handler' => array(
		'model' => 'Session'
	),
	'cookie' => 'nc_session',	// 初期値
	'ini' => array(
		'session.cookie_path' => $path,
//		'session.cookie_lifetime' => 2580000,
//		'session.gc_maxlifetime' => 2580000,
//		'session.gc_probability' => 1,
//		'session.gc_divisor' => 100
	)
));
// DBに標準時で登録するため
date_default_timezone_set('UTC');

// composerを利用する変更の影響で、EmailConfigクラスをautoloadする際に、
// EmailConfigクラスが定義されているemail.phpのファイル名がクラス名と異なるためロードに失敗する。
// そのため、email.phpをEmailConfig.phpにリネームし、bootstrapでEmailConfigを読み込む
App::uses('EmailConfig', 'Config');

// composerのautoloadを読み込み
require VENDORS . 'autoload.php';

// CakePHPのオートローダーをいったん削除し、composerより先に評価されるように先頭に追加する
// https://github.com/composer/composer/commit/c80cb76b9b5082ecc3e5b53b1050f76bb27b127b を参照
spl_autoload_unregister(array('App', 'load'));
spl_autoload_register(array('App', 'load'), true, true);

unset($path, $baseDir, $bufPath);
