<?php
$nc_mode = $this->Session->read(NC_SYSTEM_KEY.'.mode');
$title = $this->fetch('title');
$title = (isset($title) && $title != '') ? $title : h($block['Block']['title']);
?>
<?php if(($block['Block']['show_title'] == _ON && $title != '') || ($hierarchy >= NC_AUTH_MIN_CHIEF && $nc_mode == NC_BLOCK_MODE)): ?>
<h1 id="<?php echo($id); ?>_block_title" class="<?php if(isset($parent_class_name)): ?><?php echo($parent_class_name.'_title '); ?><?php endif; ?><?php echo($block['Block']['theme_name']); ?>_title">
	<?php echo($title); ?>
</h1>
<?php endif; ?>