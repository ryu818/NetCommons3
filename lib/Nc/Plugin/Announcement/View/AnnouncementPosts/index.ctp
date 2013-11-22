<?php
/**
 * お知らせ投稿画面
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.Announcement.View
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
$this->extend('/Frame/block');
$locale = Configure::read(NC_SYSTEM_KEY.'.locale');
echo $this->Form->create('Announcement', array('data-pjax' => '#'.$id));
if($announcement_edit['AnnouncementEdit']['approved_flag'] == _ON && $hierarchy  <= NC_AUTH_MODERATE) {
	$isApprovalSystem = true;
} else {
	$isApprovalSystem = false;
}
?>
<div class="nc-top-outer">
<div class="table widthmax ">
<div class="table-cell">
<fieldset class="form">
	<ul class="nc-lists">
		<li>
			<dl>
				<dt>
					<?php
						echo $this->Form->label('Content.title', __d('announcement', 'Announcement name'));
					?>
					<?php if(!empty($announcement['Announcement']['id']) && $announcement['Announcement']['status'] != NC_STATUS_PUBLISH): ?>
						<span class="nc-temporary">
							<?php echo __('Temporary...'); ?>
						</span>
					<?php endif; ?>
					<?php if($isApprovalSystem): ?>
						<span class="nc-temporary">
							<?php echo __('Approval system'); ?>
						</span>
					<?php endif; ?>
				</dt>
				<dd>
					<?php
						$settings = array(
							'type' => 'text',
							'value' => $block['Content']['title'],
							'label' => false,
							'div' => false,
							'maxlength' => NC_VALIDATOR_BLOCK_TITLE_LEN,
							'class' => 'nc-title',
							'size' => 35,
							'error' => array('attributes' => array(
								'selector' => true
							))
						);
						echo $this->Form->input('Content.title', $settings);
					?>
				</dd>
			</dl>
		</li>
		<li>
			<?php
				echo($this->Form->error('Revision.content'));
				echo $this->Form->textarea('Revision.content', array('escape' => false, 'required' => false, 'class' => 'nc-wysiwyg', 'value' => $announcement['Revision']['content']));
			?>
		</li>
	</ul>
</fieldset>
<?php
	echo $this->Form->hidden('AutoRegist.status' , array('value' => NC_STATUS_PUBLISH));
	echo $this->Html->div('submit',
		$this->Form->button(__('Save temporally'), array('name' => 'temporally', 'class' => 'nc-common-btn',
			'type' => 'button', 'onclick' => "$('#AutoRegistStatus".$id."').val(".NC_STATUS_TEMPORARY.");$(this.form).submit();")).
		$this->Form->button(__('Ok'), array('name' => 'ok', 'class' => 'nc-common-btn', 'type' => 'submit')).
		$this->Form->button(__('Cancel'), array('name' => 'cancel', 'class' => 'nc-common-btn', 'type' => 'button',
			'data-pjax' => '#'.$id, 'data-ajax-url' =>  $this->Html->url(array('controller' => 'announcement', '#' => $id))))
	);
?>
</div>
<?php if($announcement['Announcement']['id'] > 0): ?>
<div class="table-cell nc-widget-area-outer">
	<div id="announcement-posts-widget-area<?php echo ($id);?>" class="nc-widget-area">
		<?php /* 履歴情報 */ ?>
		<div class="nc-widget-area">
			<div class="nc-widget-area-title nc-panel-color">
				<h4><?php echo(__('Revisions')); ?></h4>
				<a class="nc-widget-area-title-arrow"><span class="nc-arrow"></span></a>
			</div>
			<div id="announcement-posts-widget-area-content<?php echo ($id);?>" class="nc-widget-area-content">
				<?php if(isset($revisions) && count($revisions) > 0): ?>
				<?php
					echo $this->element('/common/revisions', array('url' => array($announcement['Announcement']['id'])));
				?>
				<?php endif; ?>
				<?php /* 変更前のコンテンツを表示する */ ?>
				<div class="nc-small nc-outer">
				<?php
					if($isApprovalSystem) {
						$disabled = true;
						$preChangeFlag = ($announcement_edit['AnnouncementEdit']['approved_pre_change_flag']) ? true : false;
					} else {
						if($announcement['Announcement']['is_approved'] == _OFF) {
							// 承認制で未承認コンテンツを主担が更新しようとした場合
							$preChangeFlag = false;
						} else {
							$preChangeFlag = ($announcement['Announcement']['pre_change_flag']) ? true : false;
						}
						$disabled = false;
					}

					$settings = array(
						'value' => _ON,
						'checked' => $preChangeFlag,
						'label' =>__('You display the contents of the change before.'),
						'type' => 'checkbox',
						'div' => false,
						'disabled' => $disabled,
					);
					echo $this->Form->input('Announcement.pre_change_flag', $settings);
				?>
				<div class="note nc-indent"<?php if(!$this->Form->isFieldError('Announcement.pre_change_date') && empty($announcement['Announcement']['pre_change_flag'])): ?> style="display:none;"<?php endif; ?>>
					<?php
						if($this->request->is('post') && !empty($announcement['Announcement']['pre_change_date'])) {
							if($this->Form->isFieldError('Announcement.pre_change_date')) {
								$preChangeDate = $announcement['Announcement']['pre_change_date'];
							} else {
								$preChangeDate = date(__('Y-m-d H:i'), strtotime($announcement['Announcement']['pre_change_date']));
							}
						} else if($isApprovalSystem) {
							$preChangeDate = '';
						} else if(!empty($announcement['Announcement']['pre_change_date'])) {
							$preChangeDate = $this->TimeZone->date($announcement['Announcement']['pre_change_date'], __('Y-m-d H:i'));
						} else {
							$preChangeDate = '';
						}
						$settings = array(
							'type' => 'text',
							'value' => $preChangeDate,
							'label' => false,
							'div' => false,
							'maxlength' => 16,
							'size' => 15,
							'class' => 'nc-datetime text nc-normal-text',
							'error' => array('attributes' => array(
								'selector' => true
							)),
							'disabled' => $disabled,
						);
						echo __('Published to %s automatically.', $this->Form->input('Announcement.pre_change_date', $settings));
					?>
				</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php endif; ?>
<?php
echo ($this->Html->script(array('Announcement.announcement_posts/index', 'plugins/jquery.nc_wysiwyg.js','plugins/jquery-ui-timepicker-addon.js', 'locale/'.$locale.'/plugins/jquery-ui-timepicker.js')));
echo ($this->Html->css(array('plugins/jquery.nc_wysiwyg.css', 'plugins/jquery-ui-timepicker-addon.css')));
?>
<script>
$(function(){
	$('#<?php echo($id); ?>').AnnouncementPosts('<?php echo ($id);?>');
});
</script>
</div>
</div>
<?php
echo $this->Form->end();
?>