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
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
	Router::connect(
    	'/users/:action/*',
		array('controller' => 'users')
	);

	Router::connect(
    	'/controls/*',
		array('controller' => 'controls')
	);

	if ($plugins = CakePlugin::loaded()) {

		foreach ($plugins as $key => $value) {
			$plugins[$key] = Inflector::underscore($value);
		}
		$pluginPattern = implode('|', $plugins);
		$match = array('plugin' => $pluginPattern);

		Router::connect(
	    	':permalink/active-blocks/:block_id',
			array('controller' => 'pages', 'action' => 'index', 'plugin_name' => '', 'block_type' => 'active-blocks'),
			array(
				'permalink' => '.*',
	        	'block_id' => '[0-9]+'
	    	) + $match
		);

		Router::connect(
	    	':permalink/active-blocks/:block_id/:plugin',
			array('action' => 'index', 'block_type' => 'active-blocks'),
			array(
	        	'permalink' => '.*',
	        	'block_id' => '[0-9]+'
	    	) + $match
		);

		//App::import('Routing/Route', 'MyCakeRoute');
		App::uses('MyCakeRoute', 'Routing/Route');

		Router::connect(
	    	':permalink/active-blocks/:block_id/:plugin/:controller',
			array('block_type' => 'active-blocks'),
			array(
			'routeClass' => 'MyCakeRoute',
	        	'permalink' => '.*',
	        	'block_id' => '[0-9]+'
	    	) + $match
		);
		Router::connect(
	    	':permalink/active-blocks/:block_id/:plugin/:controller/:action/*',
			array('block_type' => 'active-blocks'),
			array(
			'routeClass' => 'MyCakeRoute',
	        	'permalink' => '.*',
	        	'block_id' => '[0-9]+'
	    	) + $match
		);
	}

	Router::connect(
    	':permalink:column/blocks/:block_id:active_plugin:active_controller:active_action/*',
		array('controller' => 'pages', 'action' => 'index', 'block_type' => 'blocks'),
		array(
        	'permalink' => '.*',
			'column' => '(\/headercolumn|\/leftcolumn|\/centercolumn|\/rightcolumn|\/footercolumn)?',
			'block_id' => '[0-9]+',
        	'active_plugin' => '\/?[^\/]*',
			'active_controller' => '\/?[^\/]*',
			'active_action' => '\/?[^\/]*'
    	)
	);

/**
 * Here, we are connecting '/' (base path) to controller called 'Pages',
 * its action called 'display', and we pass a param to select the view file
 * to use (in this case, /app/View/Pages/home.ctp)...
 */
	//Router::connect('/', array('controller' => 'pages', 'action' => 'display', 'home'));
	Router::connect(
		'/:permalink',
		array('controller' => 'pages', 'action' => 'index', 'block_type' => 'blocks'),
		array(
        	'permalink' => '.*'
    	)
	);
	Router::connect(
		'/',
		array('controller' => 'pages', 'action' => 'index', 'permalink' => '', 'block_type' => 'blocks')
	);

/**
 * ...and connect the rest of 'Pages' controller's urls.
 */
	//Router::connect('/pages/*', array('controller' => 'pages', 'action' => 'display'));

/**
 * Load all plugin routes.  See the CakePlugin documentation on
 * how to customize the loading of plugin routes.
 */
	CakePlugin::routes();

/**
 * Load the CakePHP default routes. Remove this if you do not want to use
 * the built-in default routes.
 */
	require CAKE . 'Config' . DS . 'routes.php';
