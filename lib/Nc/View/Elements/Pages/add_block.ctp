<?php if(isset($copy_content)): ?>
	<?php echo($this->element('Block.copy', array('copy_content' => $copy_content))); ?>
<?php endif; ?>
<div id="<?php echo($id); ?>" class="nc-add-block-outer"<?php if(isset($copy_content)): ?> style="display:none;"<?php endif; ?>>
	<select class="nc-add-block" name="add_block" data-placeholder="<?php echo(__('Select module')); ?>">
		<option value=""><?php echo(__('Select module')); ?></option>
	<?php foreach ($add_modules as $module_id => $module): ?>
		<option value="<?php echo($module_id); ?>"><?php echo(h($module['Module']['module_name'])); ?></option>
	<?php endforeach; ?>
	</select>
</div>
