<?php
$nc_mode = $this->Session->read(NC_SYSTEM_KEY.'.mode');
$title = $this->fetch('title');
$title = (isset($title) && $title != '') ? $title : h($block['Block']['title']);
$this->assign('title', $title);

$tooltip_title = '';
if($hierarchy >= NC_AUTH_MIN_CHIEF) {
	$tooltip_title = $this->TimeZone->getPublishedLabel($block['Block']['display_from_date'], $block['Block']['display_to_date']);
	if($tooltip_title != '') {
		$tooltip_title = ' title="' . $tooltip_title . '"';
	}
}
?>
<?php if($block['Block']['show_title'] == _ON && $title != ''): ?>
<h1 id="<?php echo($id); ?>-block-title" class="<?php if(isset($parent_class_name)): ?><?php echo($parent_class_name.'-title '); ?><?php endif; ?><?php echo($block['Block']['theme_name']); ?>-title<?php if($tooltip_title != ''): ?> nc-tooltip<?php endif; ?>"<?php echo($tooltip_title); ?>>
	<?php echo($title); ?>
	<?php echo($this->element('Frame/block_published_lbl')); ?>
</h1>
<?php endif; ?>