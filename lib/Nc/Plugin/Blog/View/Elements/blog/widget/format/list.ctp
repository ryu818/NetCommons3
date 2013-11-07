<aside class="blog-widget <?php echo($class); ?>">
	<?php if(isset($title)): ?>
	<h3 class="blog-widget-title">
		<?php echo($title); ?>
	</h3>
	<?php endif; ?>
	<ul class="clearfix">
		<?php foreach ($values as $post): ?>
			<li>
			<?php
				echo $post;
			?>
			</li>
		<?php endforeach; ?>
	</ul>
</aside>