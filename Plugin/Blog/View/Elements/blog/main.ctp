<?php if($is_add && (!isset($detail_type) || $detail_type != 'subject')): ?>
<div class="blog-add-link">
	<?php
		$addUrl = array(
			'controller' => 'blog_posts', 'action' => 'index', '#' => $id
		);
		if(isset($backQuery) && count($backQuery) > 0) {
			$addUrl['?'] = $backQuery;
		}
		echo $this->Html->link(__('Add Post'),
			$addUrl,
			array('title' =>__('Add Post'), 'data-pjax' => '#'.$id)
		);
	?>
</div>
<?php endif; ?>
<?php
if(isset($detail_type)) {
	if(isset($this->request->params['year'])) {
		$month = isset($this->request->params['month']) ? $this->request->params['month'] : '01';
		$day = isset($this->request->params['day']) ? $this->request->params['day'] : '01';
		$post_date = $this->TimeZone->date($this->request->params['year'].$month.$day);
		$int_post_date = strtotime($post_date);
	}
	switch($detail_type) {
		case 'none':
			$sub_title = __('Content not found.');
			break;
		case 'subject':
			$sub_title = h($this->request->params['subject']);
			break;
		case 'day':
			$sub_title = __d('blog', 'Daily Archives:') . date(__('(Y-m-d)'), $int_post_date);
			break;
		case 'month':
			$sub_title = __d('blog', 'Monthly Archives:') . date(__('(Y-m)'), $int_post_date);
			break;
		case 'year':
			$sub_title = __d('blog', 'Yearly Archives:') . date(__('(Y)'), $int_post_date);
			break;
		case 'author':
			$sub_title = __d('blog', 'Author Archives:') . (isset($blog_posts[0]['BlogPost']['created_user_name']) ? h($blog_posts[0]['BlogPost']['created_user_name']) : h($this->request->params['author']));
			break;
		case 'tag':
			$sub_title = __d('blog', 'Tag Archives:') .  h($this->request->params['name']);
			break;
		case 'category':
			$sub_title = __d('blog', 'Category Archives:') .  h($this->request->params['name']);
			break;
	}
	$this->element('Pages/title_assign', array('title' => $sub_title));
}
$queryOptions = array('url' => array('#' => $id), 'data-pjax' => '#'.$id);
?>
<?php if(!empty($detail_type) && $detail_type != 'none' && $detail_type != 'subject'): ?>
	<?php /* アーカイブタイトル */ ?>
	<div class="blog-archives-title">
		<?php
			echo (h($sub_title));
		?>
	</div>
<?php endif; ?>
<?php if(!empty($blog_posts)): ?>
	<?php if(isset($blog_style['BlogWidget'])): ?>
		<?php /* 表示件数等 */ ?>
		<?php $blog_style['BlogWidget'] = array_reverse($blog_style['BlogWidget']); ?>
		<?php foreach ($blog_style['BlogWidget'] as $buf_blog_style): ?>
			<?php if(empty($detail_type) || $detail_type != 'subject'): ?>
				<?php $this->append('paginator_content_after', $this->element('blog/widget_area_detail', array('blog_style' => $buf_blog_style, 'type' => 'main'))); ?>
			<?php endif; ?>
		<?php endforeach; ?>
	<?php endif; ?>
	<?php
		if(!isset($detail_type) || $detail_type != 'subject') {
			echo($this->element('/common/paginator', array('options' => $queryOptions)));
		}
	?>
	<?php foreach ($blog_posts as $post): ?>
		<?php echo($this->element('blog/detail', array('blog_post' => $post, 'detail_type' => isset($detail_type) ? $detail_type : null))); ?>
	<?php endforeach; ?>
	<?php
		if(!isset($detail_type) || $detail_type != 'subject') {
			echo($this->element('/common/paginator', array('options' => $queryOptions)));
		}
	?>
<?php else: ?>
	<div class="blog-post-not-found">
		<?php
			echo (__('Content not found.'));
		?>
	</div>
<?php endif; ?>
<?php if(isset($detail_type)): ?>
	<?php
		/* 一覧へ戻る */
		if($detail_type == 'subject') {
			$backId = 'blog-post' . $id. '-' . $blog_posts[0]['BlogPost']['id'];
		} else {
			$backId = $id;
		}
		$backUrl = array('controller' => 'blog', '#' => $backId);
		if(isset($this->request->query['back_query'])) {
			$backUrl = array_merge($backUrl, explode('/', $this->request->query['back_query']));
		}
		$backUrl['limit'] = isset($this->request->query['back_limit']) ? $this->request->query['back_limit'] : null;
		$backUrl['page'] = isset($this->request->query['back_page']) ? $this->request->query['back_page'] : null;

		echo $this->Html->div('link-bottom', $this->Html->link(__('To list'), $backUrl,
			array('title' => __('To list'),'data-pjax' => '#'.$id, 'class' => 'link-back',
			'rel' => 'prev'))
		);
	?>
<?php endif; ?>