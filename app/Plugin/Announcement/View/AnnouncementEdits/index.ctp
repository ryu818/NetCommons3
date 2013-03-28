<?php
// ブロックテーマ表示
$this->extend('/Frame/block');

//$this->assign('title', "block_id:". $block_id);
echo ($this->Html->script(array('Announcement.edit', 'plugins/jquery.nc_wysiwyg.js')));
echo ($this->Html->css('plugins/jquery.nc_wysiwyg.css'));
?>
<?php
echo($this->Form->error('Htmlarea.content'));
echo $this->Form->create(null, array('data-pjax' => '#'.$id));
echo $this->Form->textarea('content', array('escape' => false, 'class' => 'nc-wysiwyg', 'value' => isset($htmlarea['Htmlarea']['content']) ? $htmlarea['Htmlarea']['content'] : ''));
echo $this->Html->div('submit',
	$this->Form->button(__('Ok'), array('name' => 'ok', 'class' => 'common-btn')).
	$this->Form->button(__('Cancel'), array('name' => 'cancel', 'class' => 'common-btn', 'type' => 'button'))
);
echo $this->Form->end();
?>
<script>
$(function(){
	$('#<?php echo($id); ?>').AnnouncementEdit('<?php echo ($id);?>');
});
</script>