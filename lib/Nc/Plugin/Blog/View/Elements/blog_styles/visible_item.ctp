<?php
$values = array();
foreach($def_values as $def_value) {
	if($def_value == 0) {
		$values[$def_value] = __('All');
	} else {
		$values[$def_value] = __('%s cases', $def_value);
	}
}
echo $this->Form->input('BlogStyle.visible_item', array(
	'options' => $values,
	'selected' => intval($blog_style['BlogStyle']['visible_item']),
	'label' => __('Results per page'),
	'id' => $top_id.'_'. $blog_style['BlogStyle']['widget_type'].'_visible_item',
	'class' => 'blog-style-widget-single-input'
));
?>