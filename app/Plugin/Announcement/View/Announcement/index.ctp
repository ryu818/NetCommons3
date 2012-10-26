<?php
$this->extend('/Frame/block');
?>
<?php if(!empty($content)): ?>
	<?php echo ($content);?>
<?php endif; ?>
<?php
echo $this->Html->script('Announcement.index');
?>
<script>
$('#<?php echo($id); ?>').Announcement('<?php echo ($id);?>', <?php echo ($is_chief);?>);
</script>