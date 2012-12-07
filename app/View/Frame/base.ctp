<div class="nc-base">
	<section>
		<h1 class="nc-base-title">
			<?php echo($this->fetch('title')); ?>
		</h1>
		<div class="nc-base-content">
			<?php echo($this->fetch('content'));?>
		</div>
	</section>
</div>
<?php
echo $this->Html->css('common/editable/frame/base');
?>