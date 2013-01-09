<?php
$this->assign('title', __d('install', 'Welcome'));
?>
<div class="install">
	<form method="post" action="<?php echo $this->Html->url(array('plugin' => 'install','controller' => 'install','action' => 'introduction')); ?>">
		<?php echo($this->element('hidden')); ?>
		<h1><?php echo $this->fetch('title'); ?></h1>
		<div class="top-description">
			<?php echo(__d('install', 'Choose language to be used for the installation process.')); ?>
		</div>
		<div class="align-center">
			<select name="select_lang">
			<?php foreach ($lang_list as $lang_name): ?>
				<option value="<?php echo($lang_name); ?>"<?php if ($lang_name == $select_lang): ?> selected="selected"<?php endif; ?>>
					<?php echo($lang_name); ?>
				</option>
			<?php endforeach; ?>
			</select>
		</div>

		<div class="btn-bottom align-right">
			<input type="submit" value="<?php echo(h(__('Next>>'))); ?>" name="next" class="btn" />
		</div>
	</form>
</div>