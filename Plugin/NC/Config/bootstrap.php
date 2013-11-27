<?php
use Cake\Core\App;
use Cake\Core\Configure;
use Cake\Core\Plugin;

require 'defines.inc.php';

// Load all NetCommons plugins
$plugins = [
	'Announcement', 'Authority', 'Block', 'Blog', 'Breadcrumb',
	'Content', 'Group', 'Install', 'Module', 'Page', 'Policy',
	'System', 'Upload', 'User', 'Whatsnew',
];
foreach ($plugins as $plugin) {
	Plugin::load($plugin);
}

if (file_exists(APP . 'Config' . DS . 'nc.php')) {
	Configure::load('nc.php');
}

if (Configure::read('NC.installed')) {
	return;
}
// Load Install plugin
if (Configure::read('Security.salt') == 'f78b12a5c38e9e5c6ae6fbd0ff1f46c77a1e3' ||
	Configure::read('Security.cipherSeed') == '60170779348589376') {
	$_securedInstall = false;
}
Configure::write('Install.secured', !isset($_securedInstall));
/* if (!Configure::read('NC.installed') || !Configure::read('Install.secured')) { */
/* 	Plugin::load('Install', array('routes' => true)); */
/* } */
