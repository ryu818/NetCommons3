<?php
$this->extend('/Frame/block');
?>
<div class="align-right">
<?php echo $this->Html->link('編集', '/'.$block_type.'/'.$block_id.'/announcement/edit/#'.$id, array('escape' => false, 'data-pjax' => '#'.$id)); ?>
</div>
<?php if(!empty($content)): ?>
	<?php echo ($content);?>
<?php endif; ?>
<?php
echo $this->Html->script('Announcement.');
?>
<script>
<?php $url = $this->Html->url('/'.$block_type.'/'.$block_id.'/announcement/edit/#'.$id, array('escape' => false, 'data-pjax' => '#'.$id)); ?>
$('#<?php echo($id); ?>').Announcement(<?php if($hierarchy >= NC_AUTH_MIN_CHIEF): ?>'<?php echo($url); ?>'<?php else: ?>0<?php endif; ?>);
</script>