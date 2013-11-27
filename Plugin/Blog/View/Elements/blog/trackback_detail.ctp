<?php foreach($trackbacks as $trackback) { ?>
<?php
	if(!empty($trackback['BlogComment']['title'])) {
		$title = $trackback['BlogComment']['title'];
		$blogName = !empty($trackback['BlogComment']['blog_name']) ? $trackback['BlogComment']['blog_name'] : '';
	} elseif(!empty($trackback['BlogComment']['blog_name'])) {
		$title = $trackback['BlogComment']['blog_name'];
		$blogName = '';
	} else {
		$title = $trackback['BlogComment']['author_url'];
		$blogName = '';
	}
	$excerpt = !empty($trackback['BlogComment']['comment']) ? $trackback['BlogComment']['comment'] : '';

	$titleHtml = $this->Html->link(h($title), $trackback['BlogComment']['author_url'],
		array('class' => 'nc-tooltip', 'title' => $title, 'target' => '_blank'));

	if($this->CheckAuth->checkAuth($hierarchy, NC_AUTH_CHIEF)) {
		$deleteUrl = array('controller' => 'comments', 'action' => 'delete',
			$trackback['BlogComment']['blog_post_id'], $trackback['BlogComment']['id'],
			'?' => array('comment_back_page' => $this->Paginator->current(), 'is_trackback' => 'isTrackback')
		);
		$deleteHtml = $this->Html->link(__('Delete'), $deleteUrl,
			array('title' =>__('Delete'), 'data-ajax-confirm' => __('Deleting %s. <br />Are you sure to proceed?', __d('blog', 'TrackBack')),
				'data-pjax' => '#'.$id, 'data-ajax-type' => 'post', 'data-ajax-data' => '{"data[_Token][key]": "'.$this->params['_Token']['key'].'"}'
		));
		if(!$trackback['BlogComment']['is_approved']) {
			$approveUrl = array('controller' => 'comments', 'action' => 'approve',
				$trackback['BlogComment']['blog_post_id'], $trackback['BlogComment']['id'], 'is_trackback',
				'?' => array('comment_back_page' => $this->Paginator->current(), 'is_trackback' => 'isTrackback')
			);
			$approveHtml = $this->Html->link(__('Approve'), $approveUrl,
				array('title' =>__('Approve'), 'data-ajax-confirm' => __('Approve to %s. <br />Are you sure to proceed?', __d('blog', 'TrackBack')),
					'data-pjax' => '#'.$id, 'data-ajax-type' => 'post', 'data-ajax-data' => '{"data[_Token][key]": "'.$this->params['_Token']['key'].'"}'
			));
		}
	}
	$dates = $this->TimeZone->dateValues($trackback['BlogComment']['created']);
?>

<li>
	<div class="blog-trackback-list">
		<article class="blog-trackback">
			<header>
				<span>
					<?php echo $titleHtml;?>
					<?php if(!$trackback['BlogComment']['is_approved']): ?>
						<span class="temporary">
							<?php echo __('Pending'); ?>
						</span>
					<?php endif; ?>
				</span>
				<?php if(isset($blogName)):?>
					<span class="blog-trackback-blogname">
						<?php echo '[&nbsp;' .$blogName. '&nbsp;]';?>
					</span>
				<?php endif; ?>
				<span  class="blog-trackback-date">
					<time datetime="<?php echo $dates['atom_date']?>">
						<?php echo date(__('(Y-m-d h:i A)'), strtotime($dates['full_date'])) ?>
					</time>
				</span>
				<?php if($this->CheckAuth->checkAuth($hierarchy, NC_AUTH_CHIEF)) : ?>
					<span>
						<?php
							echo $deleteHtml;
							if(!$trackback['BlogComment']['is_approved']) {
								echo '&nbsp;|&nbsp;';
								echo $approveHtml;
							}
						?>
					</span>
				<?php endif; ?>
			</header>
			<?php if(isset($excerpt)):?>
				<section class="blog-trackback-excerpt">
					<?php echo h($excerpt);?>
				</section>
			<?php endif;?>
		</article>
	</div>
</li>
<?php }?>
