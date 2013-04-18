<?php
$this->extend('/Frame/block');
?>
<?php if(!empty($htmlarea['Htmlarea']['content'])): ?>
	<?php echo ($htmlarea['Htmlarea']['content']);?>
	<?php if($is_chief && $nc_show_edit): ?>
	<?php
		echo $this->Html->script('Announcement.index');
	?>
	<script>
	$(function(){
		$('#<?php echo($id); ?>').Announcement('<?php echo ($id);?>', '<?php echo($this->Html->url(array('controller' => 'edits', '#' => $id))); ?>');
	});
	</script>
	<?php endif; ?>
<?php endif; ?>