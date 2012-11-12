<?php
$this->assign('title', __d('install', 'Introduction'));
?>
<div class="install">
	<form method="post" action="<?php echo $this->Html->url(array('plugin' => 'install','controller' => 'install','action' => 'check')); ?>">
		<?php echo($this->element('hidden')); ?>
		<h1><?php echo $this->fetch('title'); ?></h1>
		<div class="top_description">
			<?php echo(__d('install', 'introduction description')); ?>
		</div>
		<div class="btn-bottom align-right">
			<input type="button" value="<?php echo(__('&lt;&lt;Back')); ?>" name="next" class="btn" onclick="history.back();" />
			<input type="submit" value="<?php echo(__('Next&gt;&gt;')); ?>" name="next" class="btn" />
		</div>
	</form>
</div>