<?php
	echo $this->Form->create('BlogStyle', array('url' => array('action' => 'widget'), 'id' => $top_id, 'data-ajax' => '#'.$top_id));
?>
<?php echo $this->fetch('widget_content'); ?>
<?php
	echo $this->Form->hidden('BlogStyle.widget_type' , array('id' => false, 'value' => $blog_style['BlogStyle']['widget_type']));

	echo $this->Html->div('submit',
		$this->Form->button(__('Ok'), array('name' => 'ok', 'class' => 'common-btn', 'type' => 'submit')).
		$this->Form->button(__('Cancel'), array('name' => 'cancel', 'class' => 'common-btn', 'type' => 'button',
			'onclick' => '$.BlogStyles.clickWidgetChancel(event, this);'))
	);
	echo $this->Form->end();
?>
<?php if($this->request->is('post') && count($this->validationErrors['BlogStyle']) == 0): ?>
<script>
$.Common.reloadBlock(null, '<?php echo($id);?>');
$.BlogStyles.clickWidgetChancel(null, $('#<?php echo($top_id);?>'));
</script>
<?php endif; ?>