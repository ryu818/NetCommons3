<?php foreach ($blog_style_rows as $blog_style): ?>
	<?php
		echo($this->element('blog/widget_area_detail', array('blog_style' => $blog_style)));
	?>
<?php endforeach; ?>