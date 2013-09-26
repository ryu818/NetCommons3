<?php
/**
 * Requests collector.
 *
 *  This file collects requests if:
 *	- no mod_rewrite is available or .htaccess files are not supported
 *  - requires App.baseUrl to be uncommented in app/Config/core.php
 *	- app/webroot is not set as a document root.
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 *  Get Cake's root directory
 */
define('APP_DIR', 'app');
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(__FILE__));
define('WEBROOT_DIR', 'webroot');
define('WWW_ROOT', ROOT . DS . APP_DIR . DS . WEBROOT_DIR . DS);
define('VENDORS', ROOT . DS . 'lib' . DS . 'Nc' . DS . 'Vendor' . DS);

/**
 * This only needs to be changed if the "cake" directory is located
 * outside of the distributed structure.
 * Full path to the directory containing "cake". Do not add trailing directory separator
 */
if (!defined('CAKE_CORE_INCLUDE_PATH')) {
// Modify for NetCommons Extentions By R.Ohga --START
// composerを利用するため、vendors下のCakePHPをincludeするように修正
// ※CakePHPのバージョンアップの際に、パスが変わっていないことを確認すること
	define('CAKE_CORE_INCLUDE_PATH', VENDORS  . 'pear-pear.cakephp.org' . DS . 'CakePHP');
// 	define('CAKE_CORE_INCLUDE_PATH', ROOT . DS . 'lib');
// Modify for NetCommons Extentions By R.Ohga --E N D
}

require APP_DIR . DS . WEBROOT_DIR . DS . 'index.php';
