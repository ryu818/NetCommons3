<?php
$top_id = 'BlogStyle'.$id.'_'. $blog_style['BlogStyle']['widget_type'];
//$this->extend('blog_styles/widget_content', array('top_id' => $top_id, 'blog_style' => $blog_style));
?>
<?php
$this->start('widget_content');
	echo $this->Form->input('BlogStyle.display_type', array(
		'options' => array(
			BLOG_DISPLAY_TYPE_POST_ONLY => __d('blog', 'Post only'),
			BLOG_DISPLAY_TYPE_POST_AND_COMMENTS => __d('blog', 'Post and Comments'),
			BLOG_DISPLAY_TYPE_COMMENTS_ONLY => __d('blog', 'Comments only'),
		),
		'selected' => isset($blog_style['BlogStyle']['display_type']) ? intval($blog_style['BlogStyle']['display_type']) : false,
		'label' => __d('blog', 'Display format'),
		'id' => $top_id.'_'. $blog_style['BlogStyle']['widget_type'].'_display_type',
		'class' => 'blog-style-widget-single-input'
	));
$this->end();
echo $this->element('blog_styles/widget_content', array('top_id' => $top_id, 'blog_style' => $blog_style));
$this->assign('widget_content', '');
?>