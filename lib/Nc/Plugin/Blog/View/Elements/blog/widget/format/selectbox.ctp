<aside class="<?php if(!isset($type)): ?>blog-widget<?php else: ?>blog-widget-inline<?php endif; ?> <?php echo($class); ?>">
	<?php if(isset($title)): ?>
	<h3 class="blog-widget-title">
		<?php echo($title); ?>
	</h3>
	<?php endif; ?>
	<?php
		echo $this->Form->input($name, array(
			'options' => $values,
			'selected' => $value,
			'label' => false,
			'div' => false,
			'id' => false,
			'style' => ($class=='blog-widget blog-widget-number-posts') ? 'width:80px' : 'width:120px',
			'class' => 'blog-widget-selectbox',
			'data-pjax' => '#'.$id,
		));
	?>
</aside>