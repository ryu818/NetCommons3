<?php
if(isset($nc_error_flag) && $nc_error_flag) {
	$add_class_name = 'nc-cancel-toolbox';

} else if(!$nc_show_edit) {
	$add_class_name = 'nc-shortcut-toolbox';
}
/* 削除アクションがあるかどうか */
if($page['Page']['room_id'] != $block['Content']['room_id'] || !$block['Content']['is_master']) {
	$all_delete = _OFF;	// ショートカット
} else {
	App::uses($block['Module']['dir_name'].'OperationComponent', 'Plugin/'.$block['Module']['dir_name'].'/Controller/Component');
	$operation_class_name = $block['Module']['dir_name'].'OperationComponent';
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
if($nc_is_edit) {
	$title =  __('Quit');
	$controller_action = $block['Module']['controller_action'];
	$edit_class_name = "nc-block-header-setting-end-icon";
} else {
	$title = __('Edit');
	$controller_action = $block['Module']['edit_controller_action'];
	$edit_class_name = "nc-block-header-setting-icon";
}
$tooltip_title = '';
if($hierarchy >= NC_AUTH_MIN_CHIEF) {
	$tooltip_title = $this->TimeZone->getPublishedLabel($block['Block']['display_from_date'], $block['Block']['display_to_date']);
	if($tooltip_title != '') {
		$tooltip_title = ' title="' . $tooltip_title . '"';
	}
}
?>
<div class="nc-block-mode">
	<div id="nc-block-move<?php echo($id); ?>" class="nc-block-move">
		<a href="#" title="<?php echo(__('Hide header'));?>" class="nc-block-header-display nc-tooltip" onclick="$.PagesBlock.toggleBlockHeader(event, this); return false;">
			<span class="nc-arrow-up"></span>
		</a>
		<span id="nc-block-header-page-name<?php echo($id); ?>" class="nc-block-header-page-name<?php if($tooltip_title != ''): ?> nc-tooltip<?php endif; ?>"<?php echo($tooltip_title); ?>>
			<?php echo($block['Block']['title']); ?>
			<?php echo($this->element('Frame/block_published_lbl')); ?>
		</span>
		<ul class="nc-block-toolbox<?php if(isset($add_class_name)){ echo(' '. $add_class_name); }?>">
			<?php if(!isset($add_class_name) || $add_class_name != 'nc-cancel-toolbox'): ?>
			<?php if($nc_show_edit): ?>
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
						'class' => 'nc-block-toolbox-link nc-tooltip',
						'data-pjax' => '#'.$id
					)
				); ?>
			</li>
			<?php endif; ?>
			<li class="nc-block-header-list-icon">
				<a href="#" onclick="$.PagesBlock.toggleOperation(event, '<?php echo($id); ?>');return false;" title="<?php echo(__('Operation'));?>" class="nc-block-toolbox-link nc-tooltip">
				</a>

			</li>
			<?php endif; ?>
			<li class="nc-block-header-close-icon">
				<a href="#" onclick="$.PagesBlock.delBlockConfirm(event, <?php echo($block['Block']['id']); ?>, <?php echo($all_delete); ?>,'<?php $title = (!empty($block['Block']['title'])) ? h($block['Block']['title']) : __('Block'); $confirm = __('Deleting %s. <br />Are you sure to proceed?', $title); echo($confirm);?>'); return false;" title="<?php echo(__('Delete'));?>" class="nc-block-toolbox-link nc-tooltip">
				</a>
			</li>
		</ul>
	</div>
	<div class="nc-block-hide-header" style="display:none;">
		<a href="#" title="<?php echo(__('Show header'));?>" class="nc-block-header-display nc-tooltip" onclick="$.PagesBlock.toggleBlockHeader(event, this); return false;">
			<span class="nc-arrow"></span>
		</a>
	</div>
</div>
<div id="nc-block-header-operation<?php echo($id); ?>" class="nc-drop-down">
	<ul>
		<li class="nc-drop-down-border">
			<?php
				/* TODO:lock_authority_idが入力されているロックされたブロックはコピー不可とする */
								echo $this->Html->link(__('Copy'), array('plugin' => 'block', 'controller' => 'block_operations', 'action' => 'copy', 'block_id' => $block['Block']['id']),
								array('title' => __('Copy'), 'id' => 'nc-block-header-copy'.$id, 'class' => 'link hover-highlight','data-ajax' => '', 'data-ajax-type' => 'post'));
							?>
		</li>
		<li>
			<?php
				echo $this->Html->link(__('Block style'), array('plugin' => 'block', 'controller' => 'block_styles', 'action' => 'index', 'block_id' => $block['Block']['id']),
						array('title' => __('Block style'), 'id' => 'nc-block-styles-link'.$id, 'class' => 'link hover-highlight','data-ajax' => '', 'data-block-styles-dialog-id' => 'nc-block-styles-dialog'.$block['Block']['id']));
			?>
		</li>
		<?php if($hierarchy >= NC_AUTH_MIN_CHIEF && $block['Module']['style_controller_action'] != '' && !isset($nc_error_flag)): ?>
		<li>
			<?php
				$params = array('block_id' => $block['Block']['id']);	// 'block_type' => 'active-blocks',
				$controller_arr = explode('/', $block['Module']['style_controller_action'], 2);
				$params['plugin'] = $params['controller'] = $controller_arr[0];
				if(isset($controller_arr[1])) {
					$params['action'] = $controller_arr[1];
				}
				echo $this->Html->link(__('Display style'), $params,
						array('title' => __('Display style'), 'id' => 'nc-block-display-styles-link'.$id, 'class' => 'link hover-highlight','data-ajax' => '', 'data-block-display-styles-dialog-id' => 'nc-block-display-styles-dialog'.$block['Block']['id']));
			?>
		</li>
		<?php endif; ?>
		<li>
			<a class="link hover-highlight" href="#">
				<?php /* TODO:未実装 */ echo(__('Contents list'));?>
			</a>
		</li>
	</ul>
</div>