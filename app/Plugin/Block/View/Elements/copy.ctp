<div class="nc-copy-block-outer">
<select class="nc-copy-block" data-placeholder="<?php echo(h($copy_content['Content']['title'])); ?>" data-copy-block-id="<?php echo($this->Session->read('Blocks.'.'copy_block_id')); ?>" data-copy-content-id="<?php echo($this->Session->read('Blocks.'.'copy_content_id')); ?>">
	<option value=""></option>
	<?php /* TODO:module_linksで移動先ルームに貼り付けることができるかどうか確認する。 */ ?>
	<?php /* TODO:ショートカットブロックの移動、ペースト、ショートカット作成をさせない。但し、ショートカット先コンテンツルームも主坦か、同じルーム内での操作であれば可能とする */ ?>
	<option value="<?php echo $this->Html->url(array('plugin' => 'block', 'controller' => 'block_operations', 'action' => 'move', 'block_id' => $this->Session->read('Blocks.'.'copy_block_id'), 'content_id' => $this->Session->read('Blocks.'.'copy_content_id')));?>"><?php echo(__('Move')); ?></option>
	<?php if($copy_content['Content']['module_id'] != 0): ?>
	<option value="<?php echo $this->Html->url(array('plugin' => 'block', 'controller' => 'block_operations', 'action' => 'shortcut', 'block_id' => $this->Session->read('Blocks.'.'copy_block_id'), 'content_id' => $this->Session->read('Blocks.'.'copy_content_id')));?>"><?php echo(__('Create shortcut')); ?></option>
	<option value="<?php echo $this->Html->url(array('plugin' => 'block', 'controller' => 'block_operations', 'action' => 'paste', 'block_id' => $this->Session->read('Blocks.'.'copy_block_id'), 'content_id' => $this->Session->read('Blocks.'.'copy_content_id')));?>"><?php echo(__('Paste')); ?></option>
	<?php endif; ?>
	<option value="<?php echo $this->Html->url(array('plugin' => 'block', 'controller' => 'block_operations', 'action' => 'cancel', 'block_id' => $this->Session->read('Blocks.'.'copy_block_id'), 'content_id' => $this->Session->read('Blocks.'.'copy_content_id')));?>"><?php echo(__('Cancel copy')); ?></option>
</select>
</div>
