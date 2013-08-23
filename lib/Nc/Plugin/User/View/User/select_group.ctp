<?php
/**
 * 会員管理 会員編集->ルーム選択画面
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.User.View
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
?>
<?php
	$options = array();
	$enrollOptions = array();
	foreach ($rooms as $room) {
		$optionName = '';
		$optionNameSpace = '';
		for ($i = 1; $i < $room['Page']['thread_num']; $i++) {
			$optionNameSpace .= '&nbsp;&nbsp;';
		}
		$optionName .= h($room['Page']['page_name']);
		if(isset($room['Page']['parent_page_name'])) {
			$optionName .= '('.h($room['Page']['parent_page_name']).')';
		}

		$option = array('value' => $room['Page']['id']);
		if($room['Page']['space_type'] == NC_SPACE_TYPE_PUBLIC) {
			$option['disabled'] = true;
			$option['class'] = 'disable-lbl';
			$optionName .= '*';
		}
		$option['name'] = $optionNameSpace.$optionName;
		$option['title'] = $optionName;
		if(isset($room['Page']['parent_room_id'])) {
			$option['data-parent-id'] = $room['Page']['parent_room_id'];
		}
		$option['onclick'] = "$.User.clickGroupOption(this);";
		if(isset($enrollRoomIds[$room['Page']['id']])) {
			$enrollOptions[] = $option;
		} else {
			$options[] = $option;
		}
	}
?>
<div id="<?php echo $id ?>">
	<?php
		echo $this->Form->create('User', array('url' => array('action' => 'select_auth', $user_id), 'data-ajax' => '#'.$id));
	?>
	<?php
		echo $this->Html->link(__d('user', 'Edit member info[%s]', $user['User']['handle']), array('action' => 'edit', $user_id), array('data-ajax' => '#'.$id, 'data-ajax-type' => 'post', 'data-ajax-serialize' => true, 'onclick'=> "$.Common.frmAllReleaseList($('#NoEnrollPageUserLinkPageId".$id."'));$.Common.frmAllReleaseList($('#EnrollPageUserLinkPageId".$id."'));", 'class' => 'bold'));
	?>
	&nbsp;>>&nbsp;
	<h3 class="bold display-inline">
		<?php echo __d('user', 'Select Groups to join'); ?>
	</h3>
	<div class="top-description">
		<?php echo __d('user', 'Select room(s) in which you want make this member join, and press [%1$s]. <br />Select room(s) displayed from [%2$s], and press [%3$s].<br />The rooms marked by [*] are rooms in %4$s.', __('Next&gt;&gt;'), __d('user', 'All the groups'),  __('Add&gt;&gt;'), __('Public room'));?>
	</div>
	<table summary="<?php echo __('Select form'); ?>">
		<tr>
			<th class="nowrap align-center" scope="col">
				<?php echo __d('user', 'All the groups'); ?>
			</th>
			<td rowspan="2" class="user-selectlist-arrow-btn-area nowrap align-center">
				<?php /* 追加 */ ?>
				<input class="common-btn-min" type="button" value="<?php echo __('Add&gt;&gt;'); ?>" onclick="$.Common.frmTransValue($('#NoEnrollPageUserLinkPageId<?php echo $id?>'),$('#EnrollPageUserLinkPageId<?php echo $id?>'));" />
				<br />
				<br />
				<?php /* 削除 */ ?>
				<input class="common-btn-min" type="button" value="<?php echo __('&lt;&lt;Delete'); ?>" onclick="$.Common.frmTransValue($('#EnrollPageUserLinkPageId<?php echo $id?>'),$('#NoEnrollPageUserLinkPageId<?php echo $id?>'));" />
			</td>
			<th class="nowrap align-center" scope="col">
				<?php echo __d('user', 'Groups to join'); ?>
			</th>
		</tr>
		<tr>
			<td class="user-selectlist nowrap align-center top">
				<div>
					<input class="common-btn-min" type="button" value="<?php echo __('Select All'); ?>" onclick="$.Common.frmAllSelectList($('#NoEnrollPageUserLinkPageId<?php echo $id?>'));" />
					<input class="common-btn-min" type="button" value="<?php echo __('Release All'); ?>" onclick="$.Common.frmAllReleaseList($('#NoEnrollPageUserLinkPageId<?php echo $id?>'));" />
				</div>
				<?php
					$settings = array(
						//'value' => false,
						'label' => false,
						'div' => false,
						'type' =>'select',
						'class' => 'user-selectlist',
						'options' => $options,
						'multiple' => true,
						'size' => 14,
						'escape' => false,
						'onclick' => '$.User.clickGroupOption($(this).children(\':last\'));',
					);
					echo $this->Form->input('NoEnrollPageUserLink.page_id', $settings);
				?>
			</td>
			<td class="user-selectlist nowrap align-center top">
				<div>
					<input class="common-btn-min" type="button" value="<?php echo __('Select All'); ?>" onclick="$.Common.frmAllSelectList($('#EnrollPageUserLinkPageId<?php echo $id?>'));" />
					<input class="common-btn-min" type="button" value="<?php echo __('Release All'); ?>" onclick="$.Common.frmAllReleaseList($('#EnrollPageUserLinkPageId<?php echo $id?>'));" />
				</div>
				<?php
					$settings = array(
						//'value' => false,
						'label' => false,
						'div' => false,
						'type' =>'select',
						'class' => 'user-selectlist user-selectlist-enroll',
						'options' => $enrollOptions,
						'multiple' => true,
						'size' => 14,
						'escape' => false,
						'onclick' => '$.User.clickGroupOption($(this).children(\':last\'));',
					);
					echo $this->Form->input('EnrollPageUserLink.page_id', $settings);
				?>
			</td>
		</tr>
	</table>
	<div class="note">
		<?php echo __('[Hold down the Ctrl-key (Windows) / Command-key (Macintosh) while click for multiple selections.]'); ?>
	</div>
	<?php
		echo $this->Html->div('submit align-right',
			$this->Form->button(__('&lt;&lt;Back'), array('name' => 'back', 'class' => 'common-btn', 'type' => 'button', 'data-ajax' => '#'.$id, 'data-ajax-url' => $this->Html->url(array('action' => 'edit', $user_id)), 'data-ajax-type' => 'post', 'data-ajax-serialize' => true, 'onclick'=> "$.Common.frmAllReleaseList($('#NoEnrollPageUserLinkPageId".$id."'));$.Common.frmAllReleaseList($('#EnrollPageUserLinkPageId".$id."'));")).
			$this->Form->button(__('Next&gt;&gt;'), array('name' => 'next', 'class' => 'common-btn', 'type' => 'submit', 'onclick'=> "$.Common.frmAllReleaseList($('#NoEnrollPageUserLinkPageId".$id."'));$.Common.frmAllReleaseList($('#EnrollPageUserLinkPageId".$id."'));")).
			$this->Form->button(__('Cancel'), array('name' => 'cancel', 'class' => 'common-btn', 'type' => 'button', 'onclick' => '$.User.memberQuit('.$user_id.'); return false;'))
		);
		echo $this->Form->hiddenVars('PageUserLink', array('authority_id'));
		echo $this->Form->end();
	?>
	<script>
	$(function(){
		$.User.selectGroupInit('<?php echo $id; ?>');
	});
	</script>
</div>
