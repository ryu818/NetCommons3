<div class="nc_base">
	<section>
		<h1 class="nc_base_title">
			<?php echo($this->fetch('title')); ?>
		</h1>
		<div class="nc_base_content">
			<?php echo($this->fetch('content'));?>
		</div>
	</section>
</div>
<?php
echo $this->Html->css('common/editable/frame/base');
?>