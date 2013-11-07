<?php if(count($blog_archives) > 0): ?>
<?php
	$type = isset($type) ? $type : null;

	if(isset($blog_style['BlogStyle']['display_type']) && $blog_style['BlogStyle']['display_type'] == BLOG_DISPLAY_TYPE_SELECTBOX) {
		$fileName = 'selectbox';
		$values = array($this->Html->url(array('limit' => $limit, '#' => $id)) => __d('blog', 'Archives'));
	} else {
		$fileName = 'list';
		$values = array();
	}
	foreach($blog_archives as $archive) {
		/* TODO:タイムゾーンによっては正しいデータが取得されていないかも。要テスト */
		$year = sprintf('%04d',$archive[0]['year']);
		$month = sprintf('%02d',$archive[0]['month']);
		$date = __('(%1$s-%2$s)', $year, $month);

		$title = $date;
		$urlArr = array($year, $month, 'limit' => $limit, '#' => $id);
		if(isset($blog_style['BlogStyle']['display_type']) && $blog_style['BlogStyle']['display_type'] == BLOG_DISPLAY_TYPE_SELECTBOX) {
			$values[$this->Html->url($urlArr)] = $title;
		} else {
			$values[$this->Html->url($urlArr)] = $this->Html->link($title,
				$urlArr,
				array('title' => $title, 'data-pjax' => '#'.$id)
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
		'data-pjax' => '#'.$id,
	);
	if(!isset($type)) {
		$params['title'] =__d('blog', 'Archives');
	}

	echo($this->element('blog/widget/format/'.$fileName, $params));
?>
<?php endif; ?>