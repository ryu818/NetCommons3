<?php if(count($blog_archives) > 0): ?>
<?php
	$type = isset($type) ? $type : null;

	if(isset($blog_style['BlogStyle']['display_type']) && $blog_style['BlogStyle']['display_type'] == BLOG_DISPLAY_TYPE_SELECTBOX) {
		$file_name = 'selectbox';
		$values = array($this->Html->url(array('limit' => $limit, '#' => $id)) => __d('blog', 'Archives'));
	} else {
		$file_name = 'list';
		$values = array();
	}
	foreach($blog_archives as $archive) {
		/* タイムゾーンによっては正しいデータが取得されていないかも。要テスト */
		$year = sprintf('%04d',$archive[0]['year']);
		$month = sprintf('%02d',$archive[0]['month']);
		$date = __('(%1$s-%2$s)', $year, $month);

		$title = $date;
		$url_arr = array($year, $month, 'limit' => $limit, '#' => $id);
		if(isset($blog_style['BlogStyle']['display_type']) && $blog_style['BlogStyle']['display_type'] == BLOG_DISPLAY_TYPE_SELECTBOX) {
			$values[$this->Html->url($url_arr)] = $title;
		} else {
			$values[$this->Html->url($url_arr)] = $this->Html->link($title,
				$url_arr,
				array('title' => $title)
			);
		}
	}
	$year = null;
	$month = null;
	if(isset($this->request->params['year']) && isset($this->request->params['month'])) {
		$year = $this->request->params['year'];
		$month = $this->request->params['month'];

	}
	$params = array(
		'type' => $type,
		'class' => 'blog-widget blog-widget-archive',
		'values' => $values,
		'name' => 'year_month',
		'value' => $this->Html->url(array($year, $month, 'limit' => $limit, '#' => $id)),
	);
	if(!isset($type)) {
		$params['title'] =__d('blog', 'Archives');
	}

	echo($this->element('blog/widget/format/'.$file_name, $params));
?>
<?php endif; ?>