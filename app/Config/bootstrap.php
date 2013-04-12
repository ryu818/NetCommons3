<?php
/**
 * This file is loaded automatically by the app/webroot/index.php file after core.php
 *
 * This file should load/create any application wide configuration settings, such as
 * Caching, Logging, loading additional configuration files.
 *
 * You should also use this file to include any files that provide global functions/constants
 * that your application uses.
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Config
 * @since         CakePHP(tm) v 0.10.8.2117
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

/*
 * NC用
 */
require 'defines.inc.php';

// Setup a 'default' cache configuration for use in the application.
Cache::config('default', array('engine' => 'File'));

/**
 * The settings below can be used to set additional paths to models, views and controllers.
 *
 * App::build(array(
 *     'Model'                     => array('/path/to/models', '/next/path/to/models'),
 *     'Model/Behavior'            => array('/path/to/behaviors', '/next/path/to/behaviors'),
 *     'Model/Datasource'          => array('/path/to/datasources', '/next/path/to/datasources'),
 *     'Model/Datasource/Database' => array('/path/to/databases', '/next/path/to/database'),
 *     'Model/Datasource/Session'  => array('/path/to/sessions', '/next/path/to/sessions'),
 *     'Controller'                => array('/path/to/controllers', '/next/path/to/controllers'),
 *     'Controller/Component'      => array('/path/to/components', '/next/path/to/components'),
 *     'Controller/Component/Auth' => array('/path/to/auths', '/next/path/to/auths'),
 *     'Controller/Component/Acl'  => array('/path/to/acls', '/next/path/to/acls'),
 *     'View'                      => array('/path/to/views', '/next/path/to/views'),
 *     'View/Helper'               => array('/path/to/helpers', '/next/path/to/helpers'),
 *     'Console'                   => array('/path/to/consoles', '/next/path/to/consoles'),
 *     'Console/Command'           => array('/path/to/commands', '/next/path/to/commands'),
 *     'Console/Command/Task'      => array('/path/to/tasks', '/next/path/to/tasks'),
 *     'Lib'                       => array('/path/to/libs', '/next/path/to/libs'),
 *     'Locale'                    => array('/path/to/locales', '/next/path/to/locales'),
 *     'Vendor'                    => array('/path/to/vendors', '/next/path/to/vendors'),
 *     'Plugin'                    => array('/path/to/plugins', '/next/path/to/plugins'),
 * ));
 *
 */

/**
 * Custom Inflector rules, can be set to correctly pluralize or singularize table, model, controller names or whatever other
 * string is passed to the inflection functions
 *
 * Inflector::rules('singular', array('rules' => array(), 'irregular' => array(), 'uninflected' => array()));
 * Inflector::rules('plural', array('rules' => array(), 'irregular' => array(), 'uninflected' => array()));
 *
 */

/**
 * Plugins need to be loaded manually, you can either load them one by one or all of them in a single call
 * Uncomment one of the lines below, as you need. make sure you read the documentation on CakePlugin to use more
 * advanced ways of loading plugins
 *
 * CakePlugin::loadAll(); // Loads all plugins at once
 * CakePlugin::load('DebugKit'); //Loads a single plugin named DebugKit
 *
 */
//CakePlugin::loadAll();
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
	'MyAssetDispatcher',
	'CacheDispatcher'
));

/**
 * Configures default file logging options
 */
App::uses('CakeLog', 'Log');
CakeLog::config('debug', array(
	'engine' => 'FileLog',
	'types' => array('notice', 'info', 'debug'),
	'file' => 'debug',
));
CakeLog::config('error', array(
	'engine' => 'FileLog',
	'types' => array('warning', 'error', 'critical', 'alert', 'emergency'),
	'file' => 'error',
));

if (file_exists(dirname(__FILE__) . DS . NC_INSTALL_INC_FILE)) {
	include_once dirname(__FILE__) . DS . NC_INSTALL_INC_FILE;
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
	'Model'                     => array(CUSTOM . 'Model' . DS, APP . 'Model' . DS),
	'Model/Behavior'            => array(CUSTOM . 'Model' . DS . 'Behavior' . DS, APP . 'Model' . DS . 'Behavior' . DS),
	'Model/Datasource'          => array(CUSTOM . 'Model' . DS . 'Datasource' . DS, APP.'Model' . DS . 'Datasource' . DS),
	//'Model/Datasource/Database' => array('/path/to/databases', '/next/path/to/database'),
	//'Model/Datasource/Session'  => array('/path/to/sessions', '/next/path/to/sessions'),
	'Controller'                => array(CUSTOM . 'Controller' . DS, APP . 'Controller' . DS),
	'Controller/Component'      => array(CUSTOM . 'Controller' . DS . 'Component' . DS, APP . 'Controller' . DS . 'Component' . DS),
	//'Controller/Component/Auth' => array('/path/to/auths', '/next/path/to/auths'),
	//'Controller/Component/Acl'  => array('/path/to/acls', '/next/path/to/acls'),
	'View'                      => array(CUSTOM . 'View' . DS, APP . 'View' . DS),
	'View/Helper'               => array(CUSTOM . 'View' . DS . 'Helper' . DS, APP . 'View' . DS . 'Helper' . DS),
	//'Console'                   => array('/path/to/consoles', '/next/path/to/consoles'),
	//'Console/Command'           => array('/path/to/commands', '/next/path/to/commands'),
	//'Console/Command/Task'      => array('/path/to/tasks', '/next/path/to/tasks'),
	//'Lib'                       => array('/path/to/libs', '/next/path/to/libs'),
	'Locale'                    => array(CUSTOM . 'Locale' . DS, APP . 'Locale' . DS),
	'Vendor'                    => array(CUSTOM . 'Vendor' . DS, APP . 'Vendor' . DS),
	'Plugin'                    => array(CUSTOM . 'Plugin' . DS, APP . 'Plugin' . DS),
));
// ブロック用テーマ
App::build(array(
	'Frame'                    => array(CUSTOM . 'Frame' . DS, APP . 'Frame' . DS),
), App::RESET);

CakePlugin::loadAll(array(array('routes' => true)));	// Loads all plugins at once		// array(array('routes' => true))     'Blog' => array('routes' => true)

Configure::write('Session', array(
	'defaults' => 'database',
	'handler' => array(
        'model' => 'Session'
    ),
    'cookie' => 'nc_session',	// 初期値
	/*'ini' => Array(
        'session.cookie_lifetime' => 2580000,
        'session.gc_maxlifetime' => 2580000,
        'session.gc_probability' => 1,
        'session.gc_divisor' => 100
    )*/
));