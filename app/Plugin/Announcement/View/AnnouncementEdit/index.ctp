<?php
$this->extend('/Frame/block');

//$this->assign('title', "block_id:". $block_id);
echo ($this->Html->script(array('Announcement.edit', 'plugins/jquery.nc_wysiwyg.js')));
echo ($this->Html->css('plugins/jquery.nc_wysiwyg.css'));
?>
<?php echo($this->Form->error('Htmlarea.content')); ?>
<form id="form<?php echo($id); ?>" method="post" action="<?php echo $this->Html->urlBlock($id, '/announcement/edit'); ?>" data-pjax="#<?php echo($id); ?>">
	<textarea name="data[Htmlarea][content]" id="<?php echo($id); ?>_wysiwyg" class="nc_wysiwyg" rows="15" cols="40">
		<?php echo($content); ?>
	</textarea>
	<div class="btn-bottom">
		<input type="submit" class="common_btn" name="ok" value="<?php echo( __('Ok')); ?>" />
		<input type="button" class="common_btn" name="cancel" value="<?php echo(__('Cancel')); ?>" />
	</div>
</form>
<script>
$('#<?php echo($id); ?>').AnnouncementEdit('<?php echo ($id);?>');
</script>