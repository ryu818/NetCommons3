<?php
	$title = $post['BlogPost']['title'];
	$permalink = $post['BlogPost']['permalink'];

	$dates = $this->TimeZone->date_values($post['BlogPost']['post_date']);
	$is_edit = $this->CheckAuth->isEdit($hierarchy, $post['Authority']['hierarchy']);
?>
<article class="blog-post">
	<header class="blog-entry-header">
		<h1 class="blog-entry-title">
			<?php
				echo $this->Html->link($title, array('plugin' => 'blog', 'controller' => 'blog', $dates['year'], $dates['month'], $dates['day'], $permalink, '#' => $id),
					array('title' => __d('blog', 'Permalink to %s', $title),
					'rel' => 'bookmark'));
				/* TODO:New記号 */
			?>
		</h1>
		<div class="blog-entry-meta">
			<?php echo(__d('blog', 'Submitted on:')); ?>
			<?php
				echo $this->Html->link('<time datetime="' . $dates['atom_date'] . '" class="blog-entry-date">'.$dates['date'].'</time>',
					array('plugin' => 'blog', 'controller' => 'blog', $dates['year'], $dates['month'], $dates['day'], '#' => $id),
					array('title' =>$dates['time'],
					'rel' => 'bookmark', 'escape' => false));
			?>
			<span class="blog-by-author">
				&nbsp;|&nbsp;
				<?php echo(__d('blog', 'Author:')); ?>
				<?php
					echo $this->Html->link($post['BlogPost']['created_user_name'], array('plugin' => 'blog', 'controller' => 'blog', 'action' => 'index','author', $post['BlogPost']['created_user_id']),
						array('title' => __d('blog', 'View all posts by %s', $post['BlogPost']['created_user_name']),
							'rel' => 'author'));
				?>
			</span>
			<?php echo($this->element('blog/term_link', array('blog_posts_terms' => $blog_posts_terms, 'post_id' => $post['BlogPost']['id'], 'taxonomy' => 'category'))); ?>
			<?php echo($this->element('blog/term_link', array('blog_posts_terms' => $blog_posts_terms, 'post_id' => $post['BlogPost']['id'], 'taxonomy' => 'tag'))); ?>

			<?php /* TODO:Twitter facebook等のアイコン */ ?>
		</div>
	</header>
	<?php if($is_edit): ?>
	<div class="blog-entry-content blog-entry-content-highlight" data-edit-id="#blog-edit-link<?php echo($id.'-'.$post['BlogPost']['id']); ?>">
	<?php else: ?>
	<div class="blog-entry-content">
	<?php endif; ?>
		<?php echo ($post['Htmlarea']['content']);?>
	</div>
	<footer class="blog-entry-meta">
		<span class="blog-edit-link">
			<?php if($is_edit): ?>
			<?php
				echo $this->Html->link(__('Edit'),
					array('controller' => 'blog_posts', 'action' => 'index', $post['BlogPost']['id']),
					array('id' => 'blog-edit-link'.$id.'-'.$post['BlogPost']['id'], 'title' =>__('Edit Post'), 'data-pjax' => '#'.$id
				));
			?>
			&nbsp;|&nbsp;
			<?php
				echo $this->Html->link(__('Delete'),
					array('controller' => 'blog_posts', 'action' => 'delete', $post['BlogPost']['id']),
					array('title' =>__('Delete Post'), 'data-pjax' => '#'.$id
				));
			?>
			&nbsp;|&nbsp;
			<?php endif; ?>
			<?php
				echo $this->Html->link(__d('blog', 'Vote'),
					array('controller' => 'blog', 'action' => 'vote', $post['BlogPost']['id']),
					array('title' =>__d('blog', 'Vote'), 'data-pjax' => '#'.$id
				));
			?>
			&nbsp;|&nbsp;
		</span>
		<span class="blog-comments-link">
			<?php
				echo(__d('blog', 'Voted(%s)', intval($post['BlogPost']['vote_count'])));
			?>
			&nbsp;|&nbsp;
			<?php
				echo $this->Html->link(__d('blog', 'Comments(%s)', intval($post['BlogPost']['comment_count'])), array('controller' => 'blog', $dates['year'], $dates['month'], $dates['day'], $permalink, '#' => $id.'_comments'),
					array('title' => __d('blog', 'Comment on %s', $title),
					'rel' => 'bookmark'));
			?>
			&nbsp;|&nbsp;
			<?php
				echo $this->Html->link(__d('blog', 'Trackbacks(%s)', intval($post['BlogPost']['trackback_count'])), array('controller' => 'blog', $dates['year'], $dates['month'], $dates['day'], $permalink, '#' => $id.'_trackbacks'),
					array('title' => __d('blog', 'Trackback on %s', $title),
					'rel' => 'bookmark'));
			?>
		</span>
	</footer>
</article>
<?php /* 次の記事、前の記事 */ ?>
<?php if($detail_type == 'subject' && (isset($blog_prev_post['BlogPost']) || isset($blog_next_post['BlogPost']))): ?>
<nav class="blog-nav-paginator">
	<h3 class="blog-nav-title">
		<?php echo (__d('blog', 'Post navigation')); ?>
	</h3>
	<span class="blog-nav-previous">
		<?php
			if(isset($blog_prev_post['BlogPost'])) {
				$title = $blog_prev_post['BlogPost']['title'];
				$permalink = $blog_prev_post['BlogPost']['permalink'];
				$prev_dates = $this->TimeZone->date_values($blog_prev_post['BlogPost']['post_date']);

				echo $this->Html->link('<span>'.h(__('<')).'</span>'.h($title), array('plugin' => 'blog', 'controller' => 'blog', $prev_dates['year'], $prev_dates['month'], $prev_dates['day'], $permalink, '#' => $id),
					array('title' => __d('blog', 'Permalink to %s', $title),
					'rel' => 'prev','escape' => false));
			}
		?>
	</span>
	<span class="blog-nav-next">
		<?php
			if(isset($blog_next_post['BlogPost'])) {
				$title = $blog_next_post['BlogPost']['title'];
				$permalink = $blog_next_post['BlogPost']['permalink'];
				$next_dates = $this->TimeZone->date_values($blog_next_post['BlogPost']['post_date']);

				echo $this->Html->link(h($title).'<span>'.h(__('>')).'</span>', array('plugin' => 'blog', 'controller' => 'blog', $next_dates['year'], $next_dates['month'], $next_dates['day'], $permalink, '#' => $id),
					array('title' => __d('blog', 'Permalink to %s', $title),
					'rel' => 'prev','escape' => false));
			}
		?>
	</span>
</nav>
<?php endif; ?>
<?php /* TODO:コメント投稿、コメント一覧 */ ?>