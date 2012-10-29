<?php
$this->extend('/Frame/block');
?>
<?php if(!empty($htmlarea['Htmlarea']['content'])): ?>
	<?php echo ($htmlarea['Htmlarea']['content']);?>
<?php elseif($is_chief) : ?>
	<?php echo (__('Content not found.'));?>
<?php endif; ?>
<?php unset($htmlarea); ?>
<?php if($is_chief): ?>
<?php
echo $this->Html->script('Announcement.index');
?>
<script>
$('#<?php echo($id); ?>').Announcement('<?php echo ($id);?>', <?php echo ($is_chief);?>);
</script>
<?php endif; ?>