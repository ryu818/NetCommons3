<?php
$ncMode = $this->Session->read(NC_SYSTEM_KEY.'.mode');
$content = $this->fetch('content');
$setting_class_name = '';
if($block['Block']['controller_action'] == 'group' && $ncMode == NC_BLOCK_MODE && $hierarchy >= NC_AUTH_MIN_CHIEF) {
	// 内部ブロックと重なるため上下paddingをとる
	$setting_class_name = ' nc-content-group-setting nc-columns';
}
?>
<?php if($content != '' || ($hierarchy >= NC_AUTH_MIN_CHIEF && $ncMode == NC_BLOCK_MODE)): ?>
	<div id="<?php echo($id); ?>-content" class="<?php if(isset($parent_class_name)): ?><?php echo($parent_class_name.'-content '); ?><?php endif; ?><?php echo($block['Block']['theme_name']); ?>-content nc-content<?php echo($setting_class_name); ?>">
		<?php echo($content);?>
	</div>
<?php endif; ?>