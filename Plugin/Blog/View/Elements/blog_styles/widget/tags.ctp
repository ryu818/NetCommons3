<?php
$top_id = 'BlogStyle'.$id.'_'. $blog_style['BlogStyle']['widget_type'];
//$this->extend('blog_styles/widget_content', array('top_id' => $top_id, 'blog_style' => $blog_style));
?>
<?php
$this->start('widget_content');
	$top_id = 'BlogStyle'.$id.'_'. $blog_style['BlogStyle']['widget_type'];
	$def_values = explode('|', BLOG_VISIBLE_ITEM_ALL_SELECTBOX);
	echo $this->element('blog_styles/visible_item', array('top_id' => $top_id, 'def_values' => $def_values, 'blog_style' => $blog_style));

	echo $this->Form->input('BlogStyle.display_type', array(
		'options' => array(
			BLOG_DISPLAY_TYPE_LIST => __d('blog', 'Display as list'),
			BLOG_DISPLAY_TYPE_SELECTBOX => __d('blog', 'Display as dropdown'),
		),
		'selected' => isset($blog_style['BlogStyle']['display_type']) ? intval($blog_style['BlogStyle']['display_type']) : false,
		'label' => __d('blog', 'Display format'),
		'id' => $top_id.'_'. $blog_style['BlogStyle']['widget_type'].'_display_type',
		'class' => 'blog-style-widget-single-input'
	));

	echo $this->Form->input('BlogStyle.taxonomy', array(
		'options' => array(
			BLOG_DISPLAY_TYPE_TAGS => __d('blog', 'Tags'),
			BLOG_DISPLAY_TYPE_CATEGORIES => __d('blog', 'Categories'),
		),
		'selected' => isset($blog_style['BlogStyle']['taxonomy']) ? intval($blog_style['BlogStyle']['taxonomy']) : false,
		'label' => __d('blog', 'Taxonomy'),
		'id' => $top_id.'_'. $blog_style['BlogStyle']['widget_type'].'_taxonomy',
		'class' => 'blog-style-widget-single-input'
	));
$this->end();
echo $this->element('blog_styles/widget_content', array('top_id' => $top_id, 'blog_style' => $blog_style));
$this->assign('widget_content', '');
?>