<?php
	$title = $blog_post['BlogPost']['title'];
	$permalink = $blog_post['BlogPost']['permalink'];

	$dates = $this->TimeZone->date_values($blog_post['BlogPost']['post_date']);
	$isEdit = $this->CheckAuth->isEdit($hierarchy, $blog_post['Authority']['hierarchy']);
?>
<article class="blog-post" id="blog-post<?php echo($id.'-'.$blog_post['BlogPost']['id']); ?>">
	<header class="blog-entry-header">
		<h1 class="blog-entry-title">
			<?php
				$titleUrl = array(
					'plugin' => 'blog', 'controller' => 'blog', $dates['year'], $dates['month'], $dates['day'], $permalink, '#' => $id
				);
				if(isset($backQuery) && count($backQuery) > 0) {
					$titleUrl['?'] = $backQuery;
				}

				echo $this->Html->link($title, $titleUrl,
					array('title' => __d('blog', 'Permalink to %s', $title),'data-pjax' => '#'.$id,
					'rel' => 'bookmark'));
				/* TODO:New記号 */
			?>
		</h1>
		<div class="blog-entry-meta">
			<?php echo(__d('blog', 'Submitted on:')); ?>
			<?php
				echo $this->Html->link('<time datetime="' . $dates['atom_date'] . '" class="blog-entry-date">'.$dates['date'].'</time>',
					array('plugin' => 'blog', 'controller' => 'blog', $dates['year'], $dates['month'], $dates['day'], 'limit' => $limit, '#' => $id),
					array('title' =>$dates['time'], 'data-pjax' => '#'.$id,
					'rel' => 'bookmark', 'escape' => false));
			?>
			<span class="blog-by-author">
				&nbsp;|&nbsp;
				<?php echo(__d('blog', 'Author:')); ?>
				<?php
					echo $this->Html->link($blog_post['BlogPost']['created_user_name'], array(
							'plugin' => 'blog', 'controller' => 'blog', 'action' => 'index',
							'author', $blog_post['BlogPost']['created_user_id'],
							'limit' => $limit,
							'#' => $id
						),
						array('title' => __d('blog', 'View all posts by %s', $blog_post['BlogPost']['created_user_name']), 'data-pjax' => '#'.$id,
							'rel' => 'author'));
				?>
			</span>
			<?php echo($this->element('blog/term_link', array('blog_posts_terms' => $blog_posts_terms, 'post_id' => $blog_post['BlogPost']['id'], 'taxonomy' => 'category'))); ?>
			<?php echo($this->element('blog/term_link', array('blog_posts_terms' => $blog_posts_terms, 'post_id' => $blog_post['BlogPost']['id'], 'taxonomy' => 'tag'))); ?>

			<?php /* TODO:Twitter facebook等のアイコン */ ?>
		</div>
	</header>
	<?php if($isEdit): ?>
	<div class="blog-entry-content blog-entry-content-highlight" data-edit-id="#blog-edit-link<?php echo($id.'-'.$blog_post['BlogPost']['id']); ?>">
	<?php else: ?>
	<div class="blog-entry-content">
	<?php endif; ?>
		<?php echo ($blog_post['Htmlarea']['content']);?>
	</div>

	<?php
		echo trim($this->element('blog/detail_footer', array('blog_post' => $blog_post)));
	?>

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
				$prevEates = $this->TimeZone->date_values($blog_prev_post['BlogPost']['post_date']);

				echo $this->Html->link('<span>'.h(__('<')).'</span>'.h($title), array('plugin' => 'blog', 'controller' => 'blog', $prevEates['year'], $prevEates['month'], $prevEates['day'], $permalink, '#' => $id),
					array('title' => __d('blog', 'Permalink to %s', $title), 'data-pjax' => '#'.$id,
					'rel' => 'prev','escape' => false));
			}
		?>
	</span>
	<span class="blog-nav-next">
		<?php
			if(isset($blog_next_post['BlogPost'])) {
				$title = $blog_next_post['BlogPost']['title'];
				$permalink = $blog_next_post['BlogPost']['permalink'];
				$nextDates = $this->TimeZone->date_values($blog_next_post['BlogPost']['post_date']);

				echo $this->Html->link(h($title).'<span>'.h(__('>')).'</span>', array('plugin' => 'blog', 'controller' => 'blog', $nextDates['year'], $nextDates['month'], $nextDates['day'], $permalink, '#' => $id),
					array('title' => __d('blog', 'Permalink to %s', $title), 'data-pjax' => '#'.$id,
					'rel' => 'prev','escape' => false));
			}
		?>
	</span>
</nav>
<?php endif; ?>
<?php /* TODO:コメント投稿、コメント一覧 */ ?>