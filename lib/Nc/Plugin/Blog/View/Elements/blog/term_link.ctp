<?php
/* カテゴリー、タグ */
$str = '';
if(isset($blog_posts_terms[$post_id])) {
	if($taxonomy == 'category') {
		$title = __d('blog', 'Posted in:');
	} else {
		$title = __d('blog', 'Tagged:');
	}
	foreach ($blog_posts_terms[$post_id] as $blog_term) {
		if($blog_term['BlogTerm']['taxonomy'] != $taxonomy) {
			continue;
		}
		if($str != '') {
			$str .= __d('blog', ',');
		}
		$str .= $this->Html->link($blog_term['BlogTerm']['name'], array(
					'plugin' => 'blog', 'controller' => 'blog', 'action' => 'index',
					$taxonomy,
					$blog_term['BlogTerm']['slug'],
					'limit' => $limit,
					'#' => $id
				),
				array('title' => __d('blog', 'View all posts by %s', $blog_term['BlogTerm']['name']), 'data-pjax' => '#'.$id,
				'rel' => 'tag', 'class' => 'blog-term'));
	}
}
if($str != '') {
	echo('&nbsp;|&nbsp;'. $title.$str);
}
?>