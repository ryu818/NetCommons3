<?php
$top_id = 'BlogStyle'.$id.'_'. $blog_style['BlogStyle']['widget_type'];
?>
<?php
$this->start('widget_content');
	$def_values = explode('|', BLOG_VISIBLE_ITEM_SELECTBOX);
	echo $this->element('blog_styles/visible_item', array('top_id' => $top_id, 'def_values' => $def_values, 'blog_style' => $blog_style));

	foreach($def_values as $def_value) {
		$values[$def_value] = __('%s cases', $def_value);
	}
	echo $this->Form->input('BlogStyle.visible_item_comments', array(
		'options' => $values,
		'selected' => intval($blog_style['BlogStyle']['visible_item_comments']),
		'label' => __d('blog', 'Display number of comments'),
		'id' => $top_id.'_'. $blog_style['BlogStyle']['widget_type'].'_visible_item_comments',
		'class' => 'blog-style-widget-single-input'
	));

	echo $this->Form->input('BlogStyle.position_comments', array(
		'options' => array(
			BLOG_POSITION_COMMENTS_LAST => __d('blog', 'Last'),
			BLOG_POSITION_COMMENTS_FIRST => __d('blog', 'First'),
		),
		'selected' => isset($blog_style['BlogStyle']['position_comments']) ? intval($blog_style['BlogStyle']['position_comments']) : BLOG_POSITION_COMMENTS_FIRST,
		'label' => __d('blog', 'Display position of comments'),
		'id' => $top_id.'_'. $blog_style['BlogStyle']['widget_type'].'_position_comments',
		'class' => 'blog-style-widget-single-input'
	));

	echo $this->Form->input('BlogStyle.order_comments', array(
		'options' => array(
			BLOG_ORDER_COMMENTS_OLDEST => __d('blog', 'Oldest'),
			BLOG_ORDER_COMMENTS_NEWEST => __d('blog', 'Newest'),
		),
		'selected' => isset($blog_style['BlogStyle']['order_comments']) ? intval($blog_style['BlogStyle']['order_comments']) : BLOG_ORDER_COMMENTS_OLDEST,
		'label' => __d('blog', 'Order of comments'),
		'id' => $top_id.'_'. $blog_style['BlogStyle']['widget_type'].'_order_comments',
		'class' => 'blog-style-widget-single-input'
	));

	echo $this->Form->input('BlogStyle.threaded_comments',array(
    	'type' => 'checkbox',
		'checked' => (isset($blog_style['BlogStyle']['threaded_comments']) && !$blog_style['BlogStyle']['threaded_comments']) ? false : true,
		'label' => __d('blog', 'Enable threaded (nested) comments.'),
        'id' => $top_id.'_'. $blog_style['BlogStyle']['widget_type'].'_threaded_comments',
		'class' => 'blog-style-widget-single-input'
	));
$this->end();
echo $this->element('blog_styles/widget_content', array('top_id' => $top_id, 'blog_style' => $blog_style));
$this->assign('widget_content', '');
?>