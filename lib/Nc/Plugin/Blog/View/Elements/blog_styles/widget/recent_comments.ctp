<?php
$top_id = 'BlogStyle'.$id.'_'. $blog_style['BlogStyle']['widget_type'];
?>
<?php
$this->start('widget_content');
	$def_values = explode('|', BLOG_VISIBLE_ITEM_SELECTBOX);
	echo $this->element('blog_styles/visible_item', array('top_id' => $top_id, 'def_values' => $def_values, 'blog_style' => $blog_style));
$this->end();
echo $this->element('blog_styles/widget_content', array('top_id' => $top_id, 'blog_style' => $blog_style));
$this->assign('widget_content', '');
?>
