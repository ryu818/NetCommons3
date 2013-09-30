#!/usr/bin/php -q
<?php
/**
 * Command-line code generation utility to automate programmer chores.
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
 * @package       app.Console
 * @since         CakePHP(tm) v 2.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
$ds = DIRECTORY_SEPARATOR;
$dispatcher = 'Cake' . $ds . 'Console' . $ds . 'ShellDispatcher.php';

// Add for NetCommons Extentions By Ryuji.M --START
// composerを利用するため、vendors下のCakePHPを使用するように修正
define('ROOT', dirname(dirname(dirname(__FILE__))));
define('APP', ROOT.$ds.'app'.$ds);
define('VENDORS', ROOT . $ds . 'lib' . $ds . 'Nc' . $ds . 'Vendor' . $ds);
// Add for NetCommons Extentions By Ryuji.M --E N D

if (function_exists('ini_set')) {
// Modify for NetCommons Extentions By Ryuji.M --START
// composerを利用するため、vendors下のCakePHPを使用するように修正
	$root = VENDORS . 'pear-pear.cakephp.org' . $ds . 'CakePHP';
	//$root = dirname(dirname(dirname(__FILE__)));
// Modify for NetCommons Extentions By Ryuji.M --E N D

	// the following line differs from its sibling
	// /lib/Cake/Console/Templates/skel/Console/cake.php
// Modify for NetCommons Extentions By Ryuji.M --START
// composerを利用するため、vendors下のCakePHPを使用するように修正
	ini_set('include_path', $root . PATH_SEPARATOR . ini_get('include_path'));
	//ini_set('include_path', $root . $ds . 'lib' . PATH_SEPARATOR . ini_get('include_path'));
// Modify for NetCommons Extentions By Ryuji.M --E N D

}

if (!include($dispatcher)) {
	trigger_error('Could not locate CakePHP core files.', E_USER_ERROR);
}
unset($paths, $path, $dispatcher, $root, $ds);

return ShellDispatcher::run($argv);
