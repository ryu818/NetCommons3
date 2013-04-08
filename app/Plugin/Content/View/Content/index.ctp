<div id="nc-content-top<?php echo($id); ?>" data-width="750">
	<?php
		echo $this->Form->create('Content', array('type' => 'post', 'id' => 'FormContent'.$id, 'data-ajax-replace' => '#nc-content-top'.$id));
	?>
	<div class="top-description">
		<?php
			echo __d('content', 'Contents list in <span class=\'bold\'>[%1$s]</span>. Switching the content to display from the radio button, you can edit the content, or delete.', $room_name);
	 	?>
	</div>
	<div class="nc-content-selection-outer">
		<label for="nc-content-sel-module<?php echo($id); ?>"><?php echo __d('content', 'Selection module'); ?></label>
		<select id="nc-content-sel-module<?php echo($id); ?>" class="nc-content-sel-module nc-content-sel" name="sel_module" data-ajax-url="<?php echo($this->Html->url(array('module_id' => null))); ?>">
			<option value="0"><?php echo(__('All')); ?></option>
		<?php foreach ($modules as $module_id => $module): ?>
			<option<?php if($active_module_id == $module_id): ?> selected="selected"<?php endif; ?> value="<?php echo($module_id); ?>"><?php echo(h($module['Module']['module_name'])); ?></option>
		<?php endforeach; ?>
		</select>
	</div>
	<?php /* 承認済かどうかの選択ボックス、検索テキスト未作成 */ ?>
	<table id="nc-content-table<?php echo($id); ?>">
	</table>
	<?php
		/*
		echo $this->Html->div('btn-bottom align-right',
			$this->Form->button(__('Delete'), array('name' => 'delete', 'class' => 'common-btn', 'type' => 'button',
				'onclick' => 'return false;'))
		);
		*/
	?>
	<?php
		echo $this->Html->div('btn-bottom',
			$this->Form->button(__('Close'), array('name' => 'close', 'class' => 'common-btn', 'type' => 'button',
				'onclick' => '$(\'#nc-block-contents-list-dialog'.$block_id.'\').dialog(\'close\'); return false;'))
		);
	?>
	<?php
		echo $this->Html->css(array('plugins/flexigrid', 'Content.content/index'));
		echo $this->Html->script(array('plugins/flexigrid','Content.content/index'));

		//$all_checked = $this->Form->button(__('Select All'), array('name' => 'all_checked', 'class' => 'common-btn common-btn-min', 'type' => 'button', 'onclick' => ""));
	?>
	<script>
		$(function(){
			setTimeout(function(){
				// resizeのアイコンを適切な位置に移動させるため、setTimeout
				$("#nc-content-table<?php echo($id);?>").flexigrid ({
					url: '<?php echo($this->Html->url(array('action' => 'content_list','active_content_id' => $active_content_id, 'module_id' => $active_module_id))); ?>',
					method: 'POST',
					dataType: 'json',
					showToggleBtn: false,
					colModel :
					[
						{display: '<?php echo __('Content name'); ?>', name : 'title', width: 210, sortable : true, align: 'left' },
						{display: '<?php echo __('State'); ?>', name : 'display_flag', width: 100, sortable : true, align: 'center' },
						{display: '<?php echo __('Manage'); ?>', name : 'manage', width: 380, sortable : false, align: 'left' }
					],
					sortname: "title",
					sortorder: "asc",
					usepager: true,
					rp: 20,
					width:740,
					height: 200,
					singleSelect: true
				});

			}, 100);

			<?php
				if(isset($activeControllerAction)){
					$params = array();
					$controllerArr = explode('/', $activeControllerAction, 2);
					$params['plugin'] = $params['controller'] = $controllerArr[0];
					if(isset($controllerArr[1])) {
						$params['action'] = $controllerArr[1];
					}
					$params['module_id'] = null;
					$activeUrl = $this->Html->url($params);
				}
			?>
			$('#FormContent<?php echo($id); ?>').Content('<?php echo($id);?>', '<?php if(isset($activeUrl)): ?><?php echo($activeUrl);?><?php endif; ?>', '<?php if(isset($active_id)): ?><?php echo($active_id);?><?php endif; ?>');
		});
	</script>
</div>