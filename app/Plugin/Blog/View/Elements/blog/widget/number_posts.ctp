<?php
	$type = isset($type) ? $type : null;
	$values = array();
	if($blog_style['BlogStyle']['display_type'] == BLOG_DISPLAY_TYPE_SELECTBOX) {
		$file_name = 'selectbox';
	} else {
		$file_name = 'list';
	}
	foreach(explode('|', BLOG_VISIBLE_ITEM_SELECTBOX) as $v) {
		$title = __('%s cases', $v);
		if($blog_style['BlogStyle']['display_type'] == BLOG_DISPLAY_TYPE_SELECTBOX) {
			$values[$this->Html->url(array('limit' => $v, '#' => $id))] = $title;
		} else {
			$values[$this->Html->url(array('limit' => $v, '#' => $id))] = $this->Html->link($title,
				array('limit' => $v, '#' => $id),
				array('title' => $title)
			);
		}
	}

	$params = array(
		'type' => $type,
		'class' => 'blog-widget blog-widget-number-posts',
		'values' => $values,
		'name' => 'limit',
		'value' => $this->Html->url(array('limit' => $limit, '#' => $id)),
	);
	if(!isset($type)) {
		$params['title'] = __('Results per page');
	}

	echo($this->element('blog/widget/format/'.$file_name, $params));
?>
