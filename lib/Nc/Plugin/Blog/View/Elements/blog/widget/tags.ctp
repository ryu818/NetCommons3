<?php if(count($blog_tags) > 0): ?>
<?php
	$type = isset($type) ? $type : null;
	if($blog_tag_taxonomy == 'category') {
		$widget_title = __d('blog', 'Categories');
		$name = isset($category) ? $category : null;
	} else {
		$widget_title = __d('blog', 'Tags');
		$name = isset($tag) ? $tag : null;
	}

	if(isset($blog_style['BlogStyle']['display_type']) && $blog_style['BlogStyle']['display_type'] == BLOG_DISPLAY_TYPE_SELECTBOX) {
		$file_name = 'selectbox';
		$values = array($this->Html->url(array('limit' => $limit, '#' => $id)) => $widget_title);
	} else {
		$file_name = 'list';
		$values = array();
	}
	foreach($blog_tags as $blog_tag) {
		$title = $blog_tag['BlogTerm']['name'];
		$url_arr = array($blog_tag_taxonomy, $blog_tag['BlogTerm']['slug'], 'limit' => $limit, '#' => $id);
		if(isset($blog_style['BlogStyle']['display_type']) && $blog_style['BlogStyle']['display_type'] == BLOG_DISPLAY_TYPE_SELECTBOX) {
			$values[$this->Html->url($url_arr)] = $title;
		} else {
			$values[$this->Html->url($url_arr)] = $this->Html->link($title,
				$url_arr,
				array('title' => $title, 'data-pjax' => '#'.$id)
			);
		}
	}

	$params = array(
		'type' => $type,
		'class' => 'blog-widget blog-widget-tags',
		'values' => $values,
		'name' => 'tags',
		'value' => $this->Html->url(array($blog_tag_taxonomy, $name, 'limit' => $limit, '#' => $id)),
		'data-pjax' => '#'.$id,
	);
	if(!isset($type)) {
		$params['title'] = $widget_title;
	}

	echo($this->element('blog/widget/format/'.$file_name, $params));
?>
<?php endif; ?>

