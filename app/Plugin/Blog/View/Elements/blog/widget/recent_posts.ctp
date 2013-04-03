<?php if(count($blog_recent_posts) > 0): ?>
<aside class="blog-widget blog-widget-recent-entries">
	<h3 class="blog-widget-title">
		<?php echo(__d('blog', 'Recent Posts')); ?>
	</h3>
	<ul>
		<?php foreach ($blog_recent_posts as $post): ?>
			<?php
				$title = $post['BlogPost']['title'];
				$permalink = $post['BlogPost']['permalink'];
				$dates = $this->TimeZone->date_values($post['BlogPost']['post_date']);
			?>
			<li>
			<?php
				echo $this->Html->link($title, array('plugin' => 'blog', 'controller' => 'blog', $dates['year'], $dates['month'], $dates['day'], $permalink, '#' => $id),
					array('title' => __d('blog', 'Permalink to %s', $title),
				));
			?>
			</li>
		<?php endforeach; ?>
	</ul>
</aside>
<?php endif; ?>