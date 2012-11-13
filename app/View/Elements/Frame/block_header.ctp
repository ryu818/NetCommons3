<?php
/* 削除アクションがあるかどうか */
if($page['Page']['room_id'] != $block['Content']['room_id']) {
	$all_delete = _OFF;	// ショートカット
} else {
	App::uses($block['Module']['dir_name'].'OperationsComponent', 'Plugin/'.$block['Module']['dir_name'].'/Controller/Component');
	$operation_class_name = $block['Module']['dir_name'].'OperationsComponent';
	if(class_exists($operation_class_name) && method_exists($operation_class_name, 'delete')) {
		// ブロック削除アクション
		$all_delete = _ON;
	} else {
		$all_delete = _OFF;
	}
	// ブロック追加し、削除するときにContentが存在しなければ「完全に削除する。」非表示
	if(!isset($block['Content'])) {
		$all_delete = _OFF;
	}
}
if($is_edit) {
	$title =  __('Quit');
	$controller_action = $block['Module']['controller_action'];
	$edit_class_name = "nc_block_header_setting_end_icon";
} else {
	$title = __('Edit');
	$controller_action = $block['Module']['edit_controller_action'];
	$edit_class_name = "nc_block_header_setting_icon";
}
?>
<div class="nc_block_mode">
	<div id="nc_block_move<?php echo($id); ?>" class="nc_block_move">
		<a href="#" title="<?php echo(__('Hide header'));?>" class="nc_block_header_display">
			<span class="nc_arrow_up"></span>
		</a>
		<span id="nc_block_header_page_name<?php echo($id); ?>" class="nc_block_header_page_name">
			<?php echo($block['Block']['title']); ?>
		</span>
		<ul class="nc_block_toolbox">
			<li class="<?php echo($edit_class_name); ?>">
				<?php 
				$controller_arr = explode('/', $controller_action, 2);
				$plugin = $controller = $controller_arr[0];
				$action = null;
				if(isset($controller_arr[1])) {
					$action = $controller_arr[1];
				}
				echo $this->Html->link('', array('block_id' => $block_id, 'plugin' => $plugin, 'controller' => $controller, 'action' => $action, '#' => $id),
					array(
						'title' => $title, 
						'class' => 'link ', 
						'data-pjax' => '#'.$id
					)
				); ?>
			</li>
			<li class="nc_block_header_list_icon">
				<a href="#" title="<?php echo(__('Operation'));?>" class="link">
				</a>
			</li>
			<li class="nc_block_header_close_icon">
				<a href="#" onclick="$.PagesBlock.delBlockConfirm(event, <?php echo($block['Block']['id']); ?>, <?php echo($all_delete); ?>,'<?php $title = (!empty($block['Block']['title'])) ? h($block['Block']['title']) : __('Block'); $confirm = __('Deleting %s. <br />Are you sure to proceed?', $title); echo($confirm);?>'); return false;" title="<?php echo(__('Delete'));?>" class="link">
				</a>
			</li>
		</ul>
	</div>
</div>