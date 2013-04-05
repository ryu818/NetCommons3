<?php
	$title = $blog_post['BlogPost']['title'];
	$permalink = $blog_post['BlogPost']['permalink'];
	$dates = $this->TimeZone->date_values($blog_post['BlogPost']['post_date']);
	$isEdit = $this->CheckAuth->isEdit($hierarchy, $blog_post['Authority']['hierarchy']);

	$user = $this->Session->read(NC_AUTH_KEY.'.'.'User');
	// 投票済みの場合は取得できる
	$votedSession = $this->Session->read('Blog.vote.'.$user['id'].'.'.$blog_post['BlogPost']['id']);
	$pos = strpos($blog_post['BlogPost']['vote'], $user['id']);
	if($pos === false && !isset($votedSession)){
		$isVoted = false;
	}else{
		$isVoted = true;
	}
?>
<footer id="blog-entry-meta<?php echo($id.'-'.$blog_post['BlogPost']['id']); ?>" class="blog-entry-meta">
	<span class="blog-edit-link">
		<?php if($isEdit): ?>
		<?php
			echo $this->Html->link(__('Edit'),
				array('controller' => 'blog_posts', 'action' => 'index', $blog_post['BlogPost']['id']),
				array('id' => 'blog-edit-link'.$id.'-'.$blog_post['BlogPost']['id'], 'title' =>__('Edit Post'), 'data-pjax' => '#'.$id
			));
		?>
		&nbsp;|&nbsp;
		<?php
			echo $this->Html->link(__('Delete'),
				array('controller' => 'blog_posts', 'action' => 'delete', $blog_post['BlogPost']['id']),
				array('title' =>__('Delete Post'), 'data-pjax' => '#'.$id
			));
		?>
		&nbsp;|&nbsp;
		<?php endif; ?>
		<?php
			if($isVoted){
				echo '<span class="blog-posts-voted">'.__d('blog', 'Voted').'</span>';
			}else{
				echo $this->Html->link(__d('blog', 'Vote'),
					array('controller' => 'blog', 'action' => 'vote', $blog_post['BlogPost']['id']),
					array('title' =>__d('blog', 'Vote'),
						'data-ajax-replace' => '#blog-entry-meta'.$id.'-'.$blog_post['BlogPost']['id'],
						'data-ajax-type' => 'post'
				));
			}
		?>
		&nbsp;|&nbsp;
	</span>
	<span class="blog-comments-link">
		<?php
			echo(__d('blog', 'Voted(%s)', intval($blog_post['BlogPost']['vote_count'])));
		?>
		&nbsp;|&nbsp;
		<?php
			echo $this->Html->link(__d('blog', 'Comments(%s)', intval($blog_post['BlogPost']['comment_count'])), array('controller' => 'blog', $dates['year'], $dates['month'], $dates['day'], $permalink, '#' => $id.'_comments'),
				array('title' => __d('blog', 'Comment on %s', $title),
				'rel' => 'bookmark'));
		?>
		&nbsp;|&nbsp;
		<?php
			echo $this->Html->link(__d('blog', 'Trackbacks(%s)', intval($blog_post['BlogPost']['trackback_count'])), array('controller' => 'blog', $dates['year'], $dates['month'], $dates['day'], $permalink, '#' => $id.'_trackbacks'),
				array('title' => __d('blog', 'Trackback on %s', $title),
				'rel' => 'bookmark'));
		?>
	</span>
</footer>