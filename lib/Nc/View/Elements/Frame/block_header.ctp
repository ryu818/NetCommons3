<?php
if(isset($ncIsError) && $ncIsError) {
	$addClassName = 'nc-cancel-toolbox';

} else if($hierarchy < NC_AUTH_MIN_CHIEF) {
	$addClassName = 'nc-shortcut-toolbox';
}
if($page['Page']['room_id'] != $block['Content']['room_id'] || $block['Content']['shortcut_type'] != NC_SHORTCUT_TYPE_OFF ||
	(isset($block['Content']['display_flag']) && $block['Content']['display_flag'] == NC_DISPLAY_FLAG_DISABLE)) {
	$allDelete = _OFF;	// ショートカット
} else {
	/* TODO:後にコメント部分を削除 削除アクションがあるかどうか：削除アクションがなくてもContentテーブルを削除するだけで完全に削除を許す。 */
	/*App::uses($block['Module']['dir_name'].'OperationComponent', 'Plugin/'.$block['Module']['dir_name'].'/Controller/Component');
	$operation_class_name = $block['Module']['dir_name'].'OperationComponent';
	if(class_exists($operation_class_name) && method_exists($operation_class_name, 'delete')) {
		// ブロック削除アクション
		$allDelete = _ON;
	} else {
		$allDelete = _OFF;
	}
	*/
	$allDelete = _ON;
	// ブロック追加し、削除するときにContentが存在しなければ「完全に削除する。」非表示
	if(!isset($block['Content'])) {
		$allDelete = _OFF;
	}
}
if($ncType != '') {
	$title =  __('Quit');
	$controllerAction = $block['Module']['controller_action'];
	$editClassName = "nc-block-header-setting-end-icon";
} else {
	$title = __('Edit');
	$controllerAction = $block['Module']['edit_controller_action'];
	$editClassName = "nc-block-header-setting-icon";
}
$tooltip_title = '';
if($page_hierarchy >= NC_AUTH_MIN_CHIEF) {
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
			<?php echo(h($block['Block']['title'])); ?>
			<?php echo($this->element('Frame/block_published_lbl')); ?>
		</span>
		<ul class="nc-block-toolbox<?php if(isset($addClassName)){ echo(' '. $addClassName); }?>">
			<?php if(!isset($addClassName) || $addClassName != 'nc-cancel-toolbox'): ?>
			<?php if($hierarchy >= NC_AUTH_MIN_CHIEF && $controllerAction != ''): ?>
			<li class="<?php echo($editClassName); ?>">
				<?php
				$controller_arr = explode('/', $controllerAction, 3);
				$plugin = $controller = $controller_arr[0];
				if(isset($controller_arr[2])) {
					$controller = $controller_arr[1];
					$action = $controller_arr[2];
				} elseif(isset($controller_arr[1])) {
					$controller = $controller_arr[1];
					$action = null;
				}
				$params = $this->Common->explodeControllerAction($controllerAction);
				$params['block_id'] = $block_id;
				$params['#'] = $id;
				echo $this->Html->link('', $params,
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
				<a href="#" onclick="$.PagesBlock.deleteBlockConfirm(event, this, <?php echo($block['Block']['id']); ?>, <?php echo($allDelete); ?>,'<?php $title = (!empty($block['Block']['title'])) ? h($block['Block']['title']) : __('Block'); $confirm = __('Deleting %s. <br />Are you sure to proceed?', $title); echo($confirm);?>'); return false;" title="<?php echo(__('Delete'));?>" class="nc-block-toolbox-link nc-tooltip">
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
								echo $this->Html->link(__('Copy'), array('plugin' => 'block', 'controller' => 'operations', 'action' => 'copy', 'block_id' => $block['Block']['id']),
								array('title' => __('Copy'), 'id' => 'nc-block-header-copy'.$id, 'class' => 'link nc-hover-highlight','data-ajax' => '', 'data-ajax-type' => 'post'));
							?>
		</li>
		<?php if($page_hierarchy >= NC_AUTH_MIN_CHIEF): ?>
		<li>
			<?php
				echo $this->Html->link(__('Block style'), array('plugin' => 'block', 'controller' => 'styles', 'action' => 'index', 'block_id' => $block['Block']['id']),
						array('title' => __('Block style'), 'id' => 'nc-block-styles-link'.$id, 'class' => 'link nc-hover-highlight','data-ajax' => '', 'data-block-styles-dialog-id' => 'nc-block-styles-dialog'.$block['Block']['id']));
			?>
		</li>
		<?php endif; ?>
		<?php if($page_hierarchy >= NC_AUTH_MIN_CHIEF && $block['Module']['style_controller_action'] != '' && !isset($ncIsError)): ?>
		<li>
			<?php
				$params = $this->Common->explodeControllerAction($block['Module']['style_controller_action']);
				$params['block_id'] = $block_id;

				echo $this->Html->link(__('Display style'), $params,
						array('title' => __('Display style'), 'id' => 'nc-block-display-styles-link'.$id, 'class' => 'link nc-hover-highlight','data-ajax' => '', 'data-block-display-styles-dialog-id' => 'nc-block-display-styles-dialog'.$block['Block']['id']));
			?>
		</li>
		<?php endif; ?>
		<?php
		/* その他メニューに追加する場合、ncOtherControllerActionに対してassignする。 */
		$ncOtherControllerAction = $this->fetch('ncOtherControllerAction');
		if(!empty($ncOtherControllerAction)) {
			if(is_array($ncOtherControllerAction)) {
				foreach($ncOtherControllerAction as $bufControllerAction) {
					//echo '<li>'.$bufControllerAction.'</li>';
				}
			} else {
				//echo '<li>'.$ncOtherControllerAction.'</li>';
			}
		}
		?>
		<?php /* TODO:コンテンツ一覧を表示する必要がないモジュールも存在する予定 */ ?>
		<?php if($hierarchy >= NC_AUTH_MIN_CHIEF && $block['Block']['controller_action'] != 'group'): ?>
		<li>
			<?php
				echo $this->Html->link(__('Contents list'), array('plugin' => 'content', 'controller' => 'content', 'action' => 'index', 'block_id' => $block['Block']['id'], $page['Page']['room_id']),
						array('title' => __('Contents list'), 'id' => 'nc-block-contents-list-link'.$id, 'class' => 'link nc-hover-highlight','data-ajax' => '', 'data-block-contents-list-dialog-id' => 'nc-block-contents-list-dialog'.$block['Block']['id']));
			?>
		</li>
		<?php endif; ?>
	</ul>
</div>