<?php
$this->extend('/Frame/block');
?>
<?php if(!empty($htmlarea['Htmlarea']['content'])): ?>
	<?php echo ($htmlarea['Htmlarea']['content']);?>
	<?php if($is_chief): ?>
	<?php
		echo $this->Html->script('Announcement.index');
	?>
	<script>
	$('#<?php echo($id); ?>').Announcement('<?php echo ($id);?>', <?php echo ($is_chief);?>);
	</script>
	<?php endif; ?>
<?php endif; ?>