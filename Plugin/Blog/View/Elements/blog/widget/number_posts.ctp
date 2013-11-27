<?php
	$type = isset($type) ? $type : null;
	$values = array();
	if($blog_style['BlogStyle']['display_type'] == BLOG_DISPLAY_TYPE_SELECTBOX) {
		$file_name = 'selectbox';
	} else {
		$file_name = 'list';
	}
	$named = $this->request->params['named'];
	if(isset($named['page'])) {
		unset($named['page']);
	}
	$url = array_merge($this->request->params['pass'], $named);
	$url['#'] = $id;
	foreach(explode('|', BLOG_VISIBLE_ITEM_SELECTBOX) as $v) {
		$title = __('%s cases', $v);
		$url['limit'] = $v;
		if($blog_style['BlogStyle']['display_type'] == BLOG_DISPLAY_TYPE_SELECTBOX) {
			$values[$this->Html->url($url)] = $title;
		} else {
			$values[$this->Html->url($url)] = $this->Html->link($title,
				$url,
				array('title' => $title, 'data-pjax' => '#'.$id)
			);
		}
	}

	$url['limit'] = $limit;
	$params = array(
		'type' => $type,
		'class' => 'blog-widget blog-widget-number-posts',
		'values' => $values,
		'name' => 'limit',
		'value' => $this->Html->url($url),
	);
	if(!isset($type)) {
		$params['title'] = __('Results per page');
	}

	echo($this->element('blog/widget/format/'.$file_name, $params));
?>
