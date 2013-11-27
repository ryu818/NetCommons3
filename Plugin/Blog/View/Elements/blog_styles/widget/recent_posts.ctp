<?php
$top_id = 'BlogStyle'.$id.'_'. $blog_style['BlogStyle']['widget_type'];
//$this->extend('blog_styles/widget_content', array('top_id' => $top_id, 'blog_style' => $blog_style));
?>
<?php
$this->start('widget_content');
	$top_id = 'BlogStyle'.$id.'_'. $blog_style['BlogStyle']['widget_type'];
	$def_values = explode('|', BLOG_VISIBLE_ITEM_SELECTBOX);
	echo $this->element('blog_styles/visible_item', array('top_id' => $top_id, 'def_values' => $def_values, 'blog_style' => $blog_style));

	echo $this->Form->input('BlogStyle.display_post_date',array(
    	'type' => 'checkbox',
		'checked' => isset($blog_style['BlogStyle']['display_post_date']) ? intval($blog_style['BlogStyle']['display_post_date']) : false,
		'label' => __d('blog', 'Display post date.'),
        'id' => $top_id.'_'. $blog_style['BlogStyle']['widget_type'].'_display_post_date',
		'class' => 'blog-style-widget-single-input'
	));
$this->end();
echo $this->element('blog_styles/widget_content', array('top_id' => $top_id, 'blog_style' => $blog_style));
$this->assign('widget_content', '');
?>