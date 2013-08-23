<?php if (!empty($trackbacks)): ?>
	<article id="<?php echo $id?>-trackbacks" class="blog-trackback-group">
		<h3 class="blog-trackback-title"><?php echo __d('blog', 'TrackBack')?></h3>
		<ul class="lists">
			<?php echo $this->element('blog/trackback_detail'); ?>
		</ul>

		<?php if(!empty($trackback_is_max)): ?>
			<div class="blog-trackback-all">
				<?php
					echo $this->Html->link(__('Show All'),
						array('controller' => 'blog', 'action' => 'moreTrackback', $blog_post['BlogPost']['id']),
						array('title' =>__('Show All'), 'data-ajax' => '#' . $id. '-trackbacks',  'data-ajax-type' => 'get'
					));
				?>
			</div>
		<?php endif; ?>
	</article>
<?php endif; ?>