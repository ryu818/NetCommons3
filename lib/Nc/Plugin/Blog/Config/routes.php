<?php


Router::connect(
	':permalink/:block_type/:block_id/blog/index/:year/:month/:day/:subject/*',
	array('plugin' => 'blog','controller' => 'blog', 'action' => 'index'),
	array(
		'permalink' => '.*',
		'block_type' => 'active-blocks',
		'block_id' => '[0-9]+',
		'year' => '[0-9]+',
		'month' => '[0-9]+',
		'day' => '[0-9]+',
	)
);

Router::connect(
	':permalink/:block_type/:block_id/blog/index/:year/:month/:day/*',
	array('plugin' => 'blog','controller' => 'blog', 'action' => 'index'),
	array(
		'permalink' => '.*',
		'block_type' => 'active-blocks',
		'block_id' => '[0-9]+',
		'year' => '[0-9]+',
		'month' => '[0-9]+',
		'day' => '[0-9]+',
	)
);

Router::connect(
	':permalink/:block_type/:block_id/blog/index/:year/:month/*',
	array('plugin' => 'blog','controller' => 'blog', 'action' => 'index'),
	array(
		'permalink' => '.*',
		'block_type' => 'active-blocks',
		'block_id' => '[0-9]+',
		'year' => '[0-9]+',
		'month' => '[0-9]+',
	)
);

Router::connect(
	':permalink/:block_type/:block_id/blog/index/:year/*',
	array('plugin' => 'blog','controller' => 'blog', 'action' => 'index'),
	array(
		'permalink' => '.*',
		'block_type' => 'active-blocks',
		'block_id' => '[0-9]+',
		'year' => '[0-9]+',
	)
);

Router::connect(
	':permalink/:block_type/:block_id/blog/index/:taxonomy/:name/*',
	array('plugin' => 'blog','controller' => 'blog', 'action' => 'index'),
	array(
		'permalink' => '.*',
		'block_type' => 'active-blocks',
		'block_id' => '[0-9]+',
		'taxonomy' => 'category|tag',
		'name' => '\/?[^\/]*',
	)
);

Router::connect(
	':permalink/:block_type/:block_id/blog/index/author/:author/*',
	array('plugin' => 'blog','controller' => 'blog', 'action' => 'index'),
	array(
		'permalink' => '.*',
		'block_type' => 'active-blocks',
		'block_id' => '[0-9]+',
		'author' => '[0-9]+',
	)
);