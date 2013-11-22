<div>
	<div class="nc-top-description">
		<?php
			echo __d('content', 'Blocks list in <span class=\'bold\'>[%1$s]</span> content. By clicking on the block name, and then navigate to the destination.', $content['Content']['title']);
	 	?>
	</div>
	<table id="nc-content-block-table<?php echo($id); ?>-<?php echo($content['Content']['id']); ?>">
	</table>

	<?php
		echo $this->Html->div('nc-btn-bottom',
			$this->Form->button(__('Close'), array('name' => 'close', 'class' => 'nc-common-btn', 'type' => 'button',
				'onclick' => '$(\'#nc-content-block-list-'.$id.'-'.$content['Content']['id'].'\').dialog(\'close\'); return false;'))
		);
	?>
	<?php
		/* echo $this->Html->css(array('plugins/flexigrid')); */
	?>

	<script>
		$(function(){
			$("#nc-content-block-table<?php echo($id);?>-<?php echo($content['Content']['id']); ?>").flexigrid ({
				url: '<?php echo($this->Html->url(array('action' => 'block_list',$content['Content']['id']))); ?>',
				method: 'POST',
				dataType: 'json',
				showToggleBtn: false,
				colModel :
				[
					{display: '<?php echo __('Block name'); ?>', name : 'title', width: 210, sortable : true, align: 'left' },
					{display: '<?php echo __('Page name'); ?>', name : 'page_id', width: 180, sortable : false, align: 'left' },
					{display: '<?php echo __('Room name'); ?>', name : 'room_id', width: 180, sortable : false, align: 'left' }
					<?php /* TODO:削除処理未作成 */ ?>
				],
				sortname: "title",
				sortorder: "asc",
				usepager: true,
				rp: 20,
				width:610,
				height: 200,
				singleSelect: true
			});
		});
	</script>
</div>