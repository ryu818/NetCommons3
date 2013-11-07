<?php
$this->extend('/Frame/block');
if(isset($blog_styles[1])) {
	$left_widget = trim($this->element('blog/widget_area', array('blog_style_rows' => $blog_styles[1])));
}
if(isset($blog_styles[2])) {
	$center_widget = trim($this->element('blog/widget_area', array('blog_style_rows' => $blog_styles[2])));
}
if(isset($blog_styles[3])) {
	$right_widget = trim($this->element('blog/widget_area', array('blog_style_rows' => $blog_styles[3])));
}
?>
<div class="table widthmax">
<?php if(!empty($left_widget)): ?>
	<div role="complementary" class="blog-widget-area blog-widget-area-left table-cell">
		<?php echo($left_widget); ?>
	</div>
<?php endif; ?>
<?php if(!empty($center_widget)): ?>
	<div class="blog-outer table-cell">
		<?php echo($center_widget); ?>
	</div>
<?php endif; ?>
<?php if(!empty($right_widget)): ?>
	<div role="complementary" class="blog-widget-area blog-widget-area-right table-cell">
		<?php echo($right_widget); ?>
	</div>
<?php endif; ?>
</div>
<?php
	echo $this->Html->script('Blog.blog/index');
	echo $this->Html->css('Blog.blog/index');
?>
<script>
$(function(){
	$('#<?php echo($id); ?>').Blog('<?php echo($id); ?>');
});
</script>