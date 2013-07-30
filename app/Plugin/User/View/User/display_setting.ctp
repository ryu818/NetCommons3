<?php
/**
 * 会員管理 項目設定画面
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.User.View
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
// TODO: 作成途中
// ・編集・削除・
// ・OFF、OFFの設定
// ・プレビュー機能
?>
<div id="user-display-setting">
	<div class="top-description">
		<?php echo __d('user', 'You can add, edit, delete and change the display order of items of user profile.<br />The items to be displayed to other members are controled at Information Policy module.');?>
	</div>
	<div class="add-btn-link-outer">
		<?php
			echo $this->Html->link(__d('user', 'Add Item'), array('action' => 'add_item'), array(
				'title' => __d('user', 'Add Item'),
				'class' => 'add-btn-link',
				'data-ajax' =>'#user-add-item'.$id,
				'data-ajax-method' =>'inner',
				'data-ajax-dialog' => true,
				'data-ajax-effect' => 'fold',
				'data-ajax-dialog-options' => '{"title" : "'.$this->Js->escape(__d('user', 'Add Item')).'","modal": true, "resizable": true, "position":"mouse", "width":"440"}',
			));
		?>
	</div>
	<?php
		echo $this->Form->create('Item', array('data-ajax' => '#user-display-setting'));
		$itemLists = array();
		foreach ($items as $item) {
			$itemLists[intval($item['Item']['list_num'])][intval($item['Item']['col_num'])][intval($item['Item']['row_num'])] = $item;
		}
	?>
	<div class="user-display-setting">
		<?php foreach ($itemLists as $listNum => $itemList): ?>
		<div class="user-display-setting-list">
			<div class="user-display-setting-area-top-title nc-title-color">
				<?php /* TODO:大項目の名称を設定できるほうがよいが未対応 */ ?>&nbsp;
				<a class="nc-widget-area-title-arrow"><span class="nc-arrow"></span></a>
			</div>
			<div class="table widthmax">
				<div class="table-cell widthmax user-display-setting-area-list-outer">
					<div class="table widthmax">
					<?php
						$count = count($itemList);
						$width = floor(100 / $count);
						?>
					<?php if(count($itemList) > 0): ?>
						<?php foreach ($itemList as $colNum => $itemCol): ?>
						<div class="table-cell top user-display-setting-col<?php if($colNum != $count): ?> user-display-setting-right-line<?php endif; ?>" style="width:<?php echo $width;?>%;">
							<?php foreach ($itemCol as $rowNum => $item): ?>
							<div class="user-display-setting-area-outer" data-item-id="<?php echo $item['Item']['id']; ?>">
								<div class="user-display-setting-area-title nc-title-color">
									<?php if($item['Item']['display_flag'] == NC_DISPLAY_FLAG_ON): ?>
										<a class="user-display-setting-area-display-flag" href="#" title="<?php echo(__('To private')); ?>" onclick="$.User.display(event, '<?php echo($id); ?>', this, '<?php echo($this->Js->escape($this->Html->url(array('action' => 'display')))); ?>');">
											<img class="icon" alt="<?php echo(__('To private')); ?>" src="<?php echo($this->webroot); ?>img/icons/base/on.gif" data-alt="<?php echo(__('To public')); ?>" />
										</a>
									<?php else: ?>
										<a class="user-display-setting-area-display-flag"  href="#" title="<?php echo(__('To public')); ?>" onclick="$.User.display(event, '<?php echo($id); ?>', this, '<?php echo($this->Js->escape($this->Html->url(array('action' => 'display')))); ?>');">
											<img class="icon" alt="<?php echo(__('To public')); ?>" src="<?php echo($this->webroot); ?>img/icons/base/off.gif" data-alt="<?php echo(__('To private')); ?>" />
										</a>
									<?php endif; ?>
									<h4>
										<?php
											if($item['ItemLang']['lang'] == '') {
												echo(__d('user_items',$item['ItemLang']['name']));
											} else {
												echo($item['ItemLang']['name']);
											}
										 ?>
									</h4>
									<a class="nc-widget-area-title-arrow"><span class="nc-arrow"></span></a>
								</div>

							</div>
							<?php endforeach; ?>
						</div>
						<?php endforeach; ?>
					</div>
					<?php else: ?>
					<div class="table-cell top user-display-setting-col">
					</div>
					<?php endif; ?>
				</div>
				<div class="table-cell top">
					<a class="user-display-setting-right-btn" href="#" onclick="$.User.addCol(this, <?php echo NC_USER_MAX_COL_NUM ?>); return false;"<?php if($colNum >= NC_USER_MAX_COL_NUM): ?> style="visibility:hidden;"<?php endif; ?>>
						&nbsp;
					</a>
				</div>
			</div>
			<div>
				<a class="user-display-setting-bottom-btn" href="#" onclick="$.User.addList(this); return false;">
					&nbsp;
				</a>
			</div>
		</div>
		<?php endforeach; ?>
	</div>
	<?php
		echo $this->Html->div('submit',
			$this->Form->button(__('Edit display sequence'), array('name' => 'ok', 'class' => 'common-btn', 'type' => 'submit'))
			/* $this->Form->button(__('Reset'), array('name' => 'reset', 'class' => 'common-btn', 'type' => 'reset')) */
		);
		echo $this->Form->end();
	?>
	<?php /* リスト、列追加時使用Html */ ?>
	<div id="user-display-setting-dummy">
		<div class="user-display-setting-list">
			<div class="user-display-setting-area-top-title nc-title-color">
				&nbsp;
				<a class="nc-widget-area-title-arrow"><span class="nc-arrow"></span></a>
			</div>
			<div class="table widthmax">
				<div class="table-cell widthmax user-display-setting-area-list-outer">
					<div class="table widthmax">
						<div style="width:100%;" class="table-cell top user-display-setting-col">
						</div>
					</div>
				</div>
				<div class="table-cell top">
					<a href="#" class="user-display-setting-right-btn" onclick="$.User.addCol(this); return false;">&nbsp;</a>
				</div>
			</div>
			<div>
				<a href="#" class="user-display-setting-bottom-btn" onclick="$.User.addList(this); return false;">&nbsp;</a>
			</div>
		</div>
	</div>
	<script>
	$(function(){
		$.User.displaySettingInit();
	});
	</script>
</div>
