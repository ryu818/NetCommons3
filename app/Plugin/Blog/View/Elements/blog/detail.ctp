<?php
	$title = $blog_post['BlogPost']['title'];
	$permalink = $blog_post['BlogPost']['permalink'];

	$dates = $this->TimeZone->dateValues($blog_post['BlogPost']['post_date']);
	$isEdit = $this->CheckAuth->isEdit($hierarchy, $blog['Blog']['post_hierarchy'], $blog_post['BlogPost']['created_user_id'],
		$blog_post['PageAuthority']['hierarchy']);
	$isApprove = false;
	if($blog_post['BlogPost']['status'] == NC_STATUS_PUBLISH && $blog_post['BlogPost']['is_approved'] != _ON) {
		$isApprove = true;
	}
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
		<?php if($isApprove): ?>
			<?php
				$approveUrl = array(
					'controller' => 'posts', 'action' => 'approve', $blog_post['BlogPost']['id']
				);
				echo $this->Html->link(__('Pending'), $approveUrl, array(
					'title' => __('Pending'),
					'class' => 'nc-button nc-button-red small',
					'data-ajax' =>'#blog-posts-approve-'.$id,
					'data-ajax-method' => 'inner',
					'data-ajax-dialog' => true,
					'data-ajax-effect' => 'fold',
					'data-ajax-dialog-options' => '{"title" : "'.$this->Js->escape(__('Pending [%s]', h($title))).'","modal": true, "resizable": true, "position":"mouse", "width":"600"}',
				));
			?>
		<?php elseif($blog_post['BlogPost']['status'] != NC_STATUS_PUBLISH): ?>
			<span class="temporary">
				<?php echo __('Temporary...'); ?>
			</span>
		<?php endif; ?>

		<?php if($detail_type == 'subject'):?>
			<?php // 短縮URLの取得
				$url = array('permalink' => '', 'plugin' => 'blog', 'controller' => 'blog', '?' => array('p' => $blog_post['BlogPost']['id']));
				echo $this->Html->link(__d('blog', 'Short URLs'), $this->html->url($url, true), array(
					'title' => __d('blog', 'Get %s', __d('blog', 'Short URLs')), 'class' => 'nc-button small',
					'onclick' => "prompt('" .__d('blog', 'Short URLs'). ':'. "'  ,$(this).attr('href')); return false;"
				));
			?>
			<?php // トラックバックURLの取得
				if($blog['Blog']['trackback_receive_flag']  == _ON) :
					$url = array('plugin' => 'blog', 'controller' => 'blog', 'action' => 'trackback', '?' => array('p' => $blog_post['BlogPost']['id']));
					echo $this->Html->link(__d('blog', 'TrackBack URLs'), $this->html->url($url, true), array(
							'title' => __d('blog', 'Get %s', __d('blog', 'TrackBack URLs')), 'class' => 'nc-button small',
							'onclick' => "prompt('" .__d('blog', 'TrackBack URLs'). ':'. "'  ,$(this).attr('href')); return false;"
					));
				endif;
			?>
		<?php endif;?>

		<div class="blog-entry-meta">
			<?php echo(__('Submitted on:')); ?>
			<?php
				echo $this->Html->link('<time datetime="' . $dates['atom_date'] . '" class="blog-entry-date">'.$dates['date'].'</time>',
					array('plugin' => 'blog', 'controller' => 'blog', $dates['year'], $dates['month'], $dates['day'], 'limit' => $limit, '#' => $id),
					array('title' =>$dates['time'], 'data-pjax' => '#'.$id,
					'rel' => 'bookmark', 'escape' => false));
			?>
			<span class="blog-by-author">
				&nbsp;|&nbsp;
				<?php echo(__('Author:')); ?>
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
	<div class="blog-entry-content blog-entry-content-highlight" title="<?php echo __('Double-Click to edit.'); ?>" data-edit-id="#blog-edit-link<?php echo($id.'-'.$blog_post['BlogPost']['id']); ?>">
	<?php else: ?>
	<div class="blog-entry-content">
	<?php endif; ?>
		<?php echo ($blog_post['Revision']['content']);?>
	</div>

	<?php
		echo trim($this->element('blog/detail_footer', array('blog_post' => $blog_post)));
	?>

</article>
<?php /* 次の記事、前の記事 */ ?>
<?php if($detail_type == 'subject' && (isset($blog_prev_post['BlogPost']) || isset($blog_next_post['BlogPost']))): ?>
<nav class="blog-nav-paginator">
	<h3 class="blog-nav-title">
		<?php echo (__('Post navigation')); ?>
	</h3>
	<span class="blog-nav-previous">
		<?php
			if(isset($blog_prev_post['BlogPost'])) {
				$title = $blog_prev_post['BlogPost']['title'];
				$permalink = $blog_prev_post['BlogPost']['permalink'];
				$prevEates = $this->TimeZone->dateValues($blog_prev_post['BlogPost']['post_date']);

				echo $this->Html->link('<span>'.__('&lt;').'</span>'.h($title), array('plugin' => 'blog', 'controller' => 'blog', $prevEates['year'], $prevEates['month'], $prevEates['day'], $permalink, '#' => $id),
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
				$nextDates = $this->TimeZone->dateValues($blog_next_post['BlogPost']['post_date']);

				echo $this->Html->link(h($title).'<span>'.__('&gt;').'</span>', array('plugin' => 'blog', 'controller' => 'blog', $nextDates['year'], $nextDates['month'], $nextDates['day'], $permalink, '#' => $id),
					array('title' => __d('blog', 'Permalink to %s', $title), 'data-pjax' => '#'.$id,
					'rel' => 'prev','escape' => false));
			}
		?>
	</span>
</nav>
<?php endif; ?>
<?php
	if(isset($detail_type) && $detail_type == 'subject'):
		echo $this->element('blog/comment', array('blog_post' => $blog_post));
		echo $this->element('blog/trackback', array('blog_post' => $blog_post));
	endif;
?>