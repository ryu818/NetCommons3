<?php
/**
 * 会員管理 会員編集・会員追加画面
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.User.View
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
?>
<div id="<?php echo $id ?>">
	<?php
		$options = array('data-ajax' => '#'.$id);
		if(!empty($user_id)) {
			$options['url'] = array($user_id);
		}
		echo $this->Form->create('User', $options);
		$itemLists = array();
		foreach ($items as $item) {
			$itemLists[intval($item['Item']['list_num'])][intval($item['Item']['col_num'])][intval($item['Item']['row_num'])] = $item;
		}
	?>
	<?php echo $this->element('language'); ?>
	<div class="top-description">
		<?php echo __d('user', "Input the user data, and press [%s] button.<br />Required items are marked by <span class='require'>*</span>.", __('Ok'));?>
	</div>
	<div class="user-edit">
		<?php foreach ($itemLists as $listNum => $itemList): ?>
		<fieldset class="form user-edit-list">
			<div class="table widthmax">
				<div class="table-cell widthmax user-edit-list-outer">
					<div class="table widthmax">
					<?php
						$count = count($itemList);
						$width = floor(100 / $count);
					?>
					<?php if(count($itemList) > 0): ?>
						<?php foreach ($itemList as $colNum => $itemCol): ?>
						<div class="table-cell top user-edit-col<?php if($colNum != $count): ?> user-edit-right-line<?php endif; ?>" style="width:<?php echo $width;?>%;">
							<ul class="lists user-edit-row">
								<?php foreach ($itemCol as $rowNum => $item): ?>
								<li>
									<?php
										echo $this->element('item', array('item' => $item, 'user' => $user, 'user_item_links' => $user_item_links, 'isEdit' => true));
									?>
								</li>
								<?php endforeach; ?>
							</ul>
						</div>
						<?php endforeach; ?>
					</div>
					<?php endif; ?>
				</div>
			</div>
		</fieldset>
		<?php endforeach; ?>
	</div>
	<?php
		$emailNotificationBtn = "";
		if(!empty($user_id) && $this->fetch('isEmail') == '1') {
			$emailNotificationBtn = $this->Form->button(__d('user', 'Email notification'), array('name' => 'email_notification', 'class' => 'common-btn', 'type' => 'button'));
		}
		if(!empty($user_id)) {
			$selectGroup = $this->Form->button(__d('user', 'Select Groups to join'), array('name' => 'select_groups', 'class' => 'common-btn', 'type' => 'button', 'data-ajax' => '#'.$id, 'data-ajax-url' => $this->Html->url(array('action' => 'select_group', $user['User']['id'])), 'data-ajax-type' => 'post', 'data-ajax-serialize' => true));
			$cancelBtn = $this->Form->button(__('Cancel'), array('name' => 'cancel', 'class' => 'common-btn', 'type' => 'button', 'onclick' => '$.User.memberQuit('.$user_id.'); return false;'));
		} else {
			$selectGroup = '';
			$cancelBtn = $this->Form->button(__('Reset'), array('name' => 'reset', 'class' => 'common-btn', 'type' => 'reset'));
		}
		echo $this->Html->div('submit',
			$this->Form->button(__('Ok'), array('name' => 'ok', 'class' => 'common-btn', 'type' => 'submit')).
			$selectGroup.
			$emailNotificationBtn.
			$cancelBtn
		);
		echo $this->Form->hiddenVars('PageUserLink');
		echo $this->Form->end();

		echo $this->Html->script(array('plugins/jquery.cj-object-scaler'));
	?>
	<script>
	$(function(){
		$.User.editInit('<?php echo $id; ?>', '<?php if(isset($is_add) && $is_add) {echo $user_id;} ?>', '<?php if(isset($is_add) && $is_add) {echo __d('user', 'Edit member info[%s]', $user['User']['handle']) ;} ?>', '<?php if(isset($is_add) && $is_add) {echo $this->Html->url(array('action' => 'edit'));} ?>');
	});
	</script>
</div>
