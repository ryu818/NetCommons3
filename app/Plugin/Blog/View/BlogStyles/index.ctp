<div data-width="850">
	<div class="top-description">
		<?php echo(__d('blog', 'You drag & drop the title bar to where you want to place the sidebar / main area. In addition, you can edit widget, click on their title bars to expand them.')); ?>
	</div>
	<div id="blog-style-widget-area<?php echo($id); ?>" class="table-row" data-ajax-url="<?php echo($this->Html->url()); ?>">
		<div class="blog-style-widget-area table-cell" data-col-num="1">
			<div class="blog-style-widget-area-title nc-panel-color">
				<h3><?php echo(__d('blog', 'Left sidebar')); ?></h3>
			</div>
			<div class="blog-style-widget-area-content blog-style-widget-area-content-parent">
				<?php if(isset($blog_styles[1])): ?>
					<?php echo($this->element('blog_styles/widget_area', array('blog_style_rows' => $blog_styles[1]))); ?>
				<?php endif; ?>
			</div>
		</div>
		<div class="blog-style-widget-area table-cell" data-col-num="2">
			<div class="blog-style-widget-area-title nc-panel-color">
				<h3><?php echo(__d('blog', 'Main area')); ?></h3>
			</div>
			<div class="blog-style-widget-area-content blog-style-widget-area-content-parent">
				<?php if(isset($blog_styles[2])): ?>
					<?php echo($this->element('blog_styles/widget_area', array('blog_style_rows' => $blog_styles[2]))); ?>
				<?php endif; ?>
			</div>
		</div>
		<div class="blog-style-widget-area table-cell" data-col-num="3">
			<div class="blog-style-widget-area-title nc-panel-color">
				<h3><?php echo(__d('blog', 'Right sidebar')); ?></h3>
			</div>
			<div class="blog-style-widget-area-content blog-style-widget-area-content-parent">
				<?php if(isset($blog_styles[3])): ?>
					<?php echo($this->element('blog_styles/widget_area', array('blog_style_rows' => $blog_styles[3]))); ?>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<?php
		echo $this->Html->div('btn-bottom',
		$this->Form->button(__('Close'), array('name' => 'close', 'class' => 'common-btn', 'type' => 'button',
			'onclick' => '$(\'#nc-block-display-styles-dialog'.$block_id.'\').dialog(\'close\'); return false;'))
		);
	?>
</div>
<?php
	echo $this->Html->css(array('Blog.blog_styles/index'));
	echo $this->Html->script(array('Blog.blog_styles/index'));
?>
<script>
$(function(){
	$('#blog-style-widget-area<?php echo($id); ?>').BlogStyles('<?php echo($id); ?>');
});
</script>