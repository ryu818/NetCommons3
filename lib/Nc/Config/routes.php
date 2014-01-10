<?php
/**
 * Routes configuration
 *
 * In this file, you set up routes to your controllers and their actions.
 * Routes are very important mechanism that allows you to freely connect
 * different urls to chosen controllers and their actions (functions).
 *
 * <pre>
 * ・URL設計
 * 	・blocksがつけば、そこからブロック配置のモジュールのURL
 * 		(home/)blocks/1/bbs/          	:homeページ中のblock_id=1の掲示板を表示
 * 		(home/)blocks/1/bbs/articles/1	:homeページ中のblock_id=1の掲示板の記事1を表示
 * 		(home/)blocks/1/bbs/add			:homeページ中のblock_id=1の掲示板に新規記事を追加
 * 		(home/)blocks/1/bbs/edit/1		:homeページ中のblock_id=1の掲示板の記事1を編集
 * 		(home/)blocks/1/bbs/delete/1		:homeページ中のblock_id=1の掲示板の記事1を削除
 * 			※blocksをactive-blocksにすれば、そのblockのみ表示
 * 			※blocks/1でも表示できるようにする
 *
 * 	・centercolumnがつけば、センターカラムにcentercolumn以下のURLを描画
 * 		(news/)centercolumn/blocks/1/bbs/articles/1	:newsページ中のセンターカラムにblock_id=1の掲示板の記事1を表示
 *
 * 	・permalinkに禁止する文字
 * 		blocks,active-blocks,centercolumn,leftcolumn,rightcolumn,hedercolumn,footercolumn
 * </pre>
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
 * @package       app.Config
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
if(defined('NC_INSTALLED') && NC_INSTALLED == false) {
	Configure::write('Session', array(
		'defaults' => 'php'
	));
	//Configure::write('Session.start', false);
	Router::connect('/', array('plugin' => 'install' ,'controller' => 'install'));
	Router::connect(
	'/install/:action/*',
	array('plugin' => 'install' ,'controller' => 'install')
	);

	Router::connect('/*', array('plugin' => 'install' ,'controller' => 'install' , 'action' => 'index'));
	return;
}
/**
 * Load all plugin routes.  See the CakePlugin documentation on
 * how to customize the loading of plugin routes.
 */
CakePlugin::routes();
// $prefixes = Router::prefixes();

Router::connect(
	'/users/:action/*',
	array('controller' => 'users')
);

Router::connect(
	'/nc-downloads/tex/*',
	array('controller' => 'nc_downloads', 'action' => 'tex')
);
Router::connect(
	'/nc-downloads/*',
	array('controller' => 'nc_downloads', 'action' => 'index')
);

Router::connect(
	'/controls/*',
	array('controller' => 'controls')
);

if ($plugins = CakePlugin::loaded()) {
	App::uses('NcCakeRoute', 'Routing/Route');
	foreach ($plugins as $key => $value) {
		$plugins[$key] = Inflector::underscore($value);
	}
	$pluginPattern = implode('|', $plugins);
	//$match = array('plugin' => $pluginPattern);
	$pluginParams = array('routeClass' => 'NcCakeRoute', 'plugin' => $pluginPattern);

	/*foreach ($prefixes as $prefix) {
		$params = array('prefix' => $prefix, $prefix => true);
	$indexParams = $params + array('action' => 'index');
	Router::connect("/{$prefix}/:plugin", $indexParams, $pluginParams);
	Router::connect("/{$prefix}/:plugin/:controller", $indexParams, $match);
	Router::connect("/{$prefix}/:plugin/:controller/:action/*", $params, $match);
	}
	Router::connect('/:plugin', array('action' => 'index'), $pluginParams);
	Router::connect('/:plugin/:controller', array('action' => 'index'), $match);
	Router::connect('/:plugin/:controller/:action/*', array(), $match);*/

	Router::connect(
		':permalink/:block_type/:block_id/:plugin/:controller/:action/*',
		array(),
		array(
			'permalink' => '.*',
			'block_id' => '[0-9]+',
			'block_type' => 'blocks|active-blocks',
		) + $pluginParams
	);

	Router::connect(
		':permalink/:block_type/:block_id/:plugin/:controller',
		array(),
		array(
			'permalink' => '.*',
			'block_id' => '[0-9]+',
			'block_type' => 'blocks|active-blocks',
		) + $pluginParams
	);

	Router::connect(
		':permalink/:block_type/:block_id/:plugin',
		array(),
		array(
			'permalink' => '.*',
			'block_id' => '[0-9]+',
			'block_type' => 'blocks|active-blocks',
		) + $pluginParams
	);

	/* 参照（ブロックID指定なしでプラグイン表示：主担以外表示不可。強制的にゲスト権限へ。） */
	Router::connect(
		'/:block_type/:content_id/:plugin/:controller/:action/*',
		array('block_type' => 'active-contents'),
		array(
			'block_type' => 'active-contents',
			'content_id' => '[0-9]+',
		) + $pluginParams
	);

	Router::connect(
		'/:block_type/:content_id/:plugin/:controller',
		array('block_type' => 'active-contents'),
		array(
			'block_type' => 'active-contents',
			'content_id' => '[0-9]+',
		) + $pluginParams
	);

	Router::connect(
		'/:block_type/:content_id/:plugin',
		array('block_type' => 'active-contents'),
		array(
			'block_type' => 'active-contents',
			'content_id' => '[0-9]+',
		) + $pluginParams
	);

	/* ページ設定 block_idが指定なしにplugin表示 */
	Router::connect(
		':permalink/:block_type/:plugin/:controller/:action/*',
		array(),
		array(
			'permalink' => '.*',
			'block_type' => 'blocks|active-blocks',
		) + $pluginParams
	);

	Router::connect(
		':permalink/:block_type/:plugin/:controller',
		array(),
		array(
			'permalink' => '.*',
			'block_type' => 'blocks|active-blocks',
		) + $pluginParams
	);

	Router::connect(
		':permalink/:block_type/:plugin',
		array(),
		array(
			'permalink' => '.*',
			'block_type' => 'blocks|active-blocks',
		) + $pluginParams
	);

	/* 管理系モジュール */
	/*Router::connect(
		'/:block_type/:plugin/*',
		array(),
		array(
			'block_type' => 'active-controls',
		) + $pluginParams
	);*/
	Router::connect(
		'/:block_type/:plugin/:controller/:action/*',
		array(),
		array(
			'block_type' => 'active-controls|modules'
		) + $pluginParams
	);

	Router::connect(
		'/:block_type/:plugin/:controller',
		array(),
		array(
			'block_type' => 'active-controls|modules'
		) + $pluginParams
	);

	Router::connect(
		'/:block_type/:plugin',
		array(),
		array(
			'block_type' => 'active-controls|modules'
		) + $pluginParams
	);
}
/*
 foreach ($prefixes as $prefix) {
$params = array('prefix' => $prefix, $prefix => true);
$indexParams = $params + array('action' => 'index');
Router::connect("/{$prefix}/:controller", $indexParams);
Router::connect("/{$prefix}/:controller/:action/*", $params);
}
Router::connect('/:controller', array('action' => 'index'));
Router::connect('/:controller/:action/*');
*/

Router::connect(
	':permalink:column/blocks/:block_id:active_plugin:active_controller:active_action/*',
	array('controller' => 'pages', 'action' => 'index'),
	array(
		'permalink' => '.*?',
		'column' => '(\/headercolumn|\/leftcolumn|\/centercolumn|\/rightcolumn|\/footercolumn)?',
		'block_id' => '[0-9]*',
		'active_plugin' => '\/?[^\/]*',
		'active_controller' => '\/?[^\/]*',
		'active_action' => '\/?[^\/]*'
	) + array('routeClass' => $pluginParams['routeClass'], 'active_plugin' => $pluginParams['plugin'])
);

/**
 * Here, we are connecting '/' (base path) to controller called 'Pages',
 * its action called 'display', and we pass a param to select the view file
 * to use (in this case, /app/View/Pages/home.ctp)...
*/
//Router::connect('/', array('controller' => 'pages', 'action' => 'display', 'home'));
Router::connect(
	'/:permalink',
	array('controller' => 'pages', 'action' => 'index'),
	array(
		'permalink' => '.*'
	)
);

Router::connect(
	'/',
	array('controller' => 'pages', 'action' => 'index')
);

$namedConfig = Router::namedConfig();
if ($namedConfig['rules'] === false) {
	Router::connectNamed(true);
}

unset($namedConfig, $params, $indexParams, $prefix, $pluginParams, // $prefixes, $match,
		$pluginPattern, $plugins, $key, $value);

/**
 * Load the CakePHP default routes. Only remove this if you do not want to use
 * the built-in default routes.
 */
//require CAKE . 'Config' . DS . 'routes.php';
