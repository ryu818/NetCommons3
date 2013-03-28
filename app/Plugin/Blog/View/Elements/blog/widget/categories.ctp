<?php if(count($blog_categories) > 0): ?>
<?php
	$type = isset($type) ? $type : null;

	if(isset($blog_style['BlogStyle']['display_type']) && $blog_style['BlogStyle']['display_type'] == BLOG_DISPLAY_TYPE_SELECTBOX) {
		$file_name = 'selectbox';
		$values = array($this->Html->url(array('limit' => $limit, '#' => $id)) => __d('blog', 'Categories'));
	} else {
		$file_name = 'list';
		$values = array();
	}
	foreach($blog_categories as $blog_category) {
		$title = $blog_category['BlogTerm']['name'];
		$url_arr = array('category', $blog_category['BlogTerm']['slug'], 'limit' => $limit, '#' => $id);
		if(isset($blog_style['BlogStyle']['display_type']) && $blog_style['BlogStyle']['display_type'] == BLOG_DISPLAY_TYPE_SELECTBOX) {
			$values[$this->Html->url($url_arr)] = $title;
		} else {
			$values[$this->Html->url($url_arr)] = $this->Html->link($title,
				$url_arr,
				array('title' => $title)
			);
		}
	}

	$params = array(
		'type' => $type,
		'class' => 'blog-widget blog-widget-category',
		'values' => $values,
		'name' => 'categories',
		'value' => $this->Html->url(array('category',(isset($category) ? $category : null), 'limit' => $limit, '#' => $id)),
	);
	if(!isset($type)) {
		$params['title'] =__d('blog', 'Categories');
	}

	echo($this->element('blog/widget/format/'.$file_name, $params));
?>
<?php endif; ?>