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
				$post_date = $this->TimeZone->date($post['BlogPost']['post_date']);
				$int_post_date = strtotime($post_date);

				$year = date('Y', $int_post_date);
				$month = date('m', $int_post_date);
				$day = date('d', $int_post_date);
			?>
			<li>
			<?php
				echo $this->Html->link($title, array('plugin' => 'blog', 'controller' => 'blog', $year, $month, $day, $permalink, '#' => $id),
					array('title' => __d('blog', 'Permalink to %s', $title),
				));
			?>
			</li>
		<?php endforeach; ?>
	</ul>
</aside>
<?php endif; ?>