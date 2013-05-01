<?php
/**
 * お知らせ編集画面
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.Announcement.View
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
$this->extend('/Frame/block');
?>
<?php
	echo $this->Form->create('AnnouncementEdit', array('data-pjax' => '#'.$id));
?>
<fieldset class="form">
	<ul class="lists nc-edits-lists">
		<li>
			<dl>
				<dt>
					<?php
						echo $this->Form->label('Content.title', __d('announcement', 'Announcement name'));
					?>
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
			<dl>
				<dt>
					<?php
						echo $this->Form->label('AnnouncementEdit.post_hierarchy', __('Authority to post root articles'));
					?>
				</dt>
				<dd>
					<?php
						echo $this->Form->authoritySlider('AnnouncementEdit.post_hierarchy', array('value' => $announcement_edit['AnnouncementEdit']['post_hierarchy']));
					?>
				</dd>
			</dl>
		</li>
		<li>
			<dl>
				<dt>
					<?php
						echo $this->Form->label('AnnouncementEdit.approved_flag', __('Post approval setting'));
					?>
				</dt>
				<dd>
					<?php
						echo $this->Form->input('AnnouncementEdit.approved_flag',array(
							'type' => 'radio',
							'options' => array(_ON => __('Need room manager approval'), _OFF => __('Automatic approval')),
							'value' => intval($announcement_edit['AnnouncementEdit']['approved_flag']),
							'div' => false,
							'legend' => false,
						));
						echo $this->Form->input('AnnouncementEdit.approved_pre_change_flag',array(
							'type' => 'checkbox',
							'value' => intval($announcement_edit['AnnouncementEdit']['approved_pre_change_flag']),
							'label' => __('If not approved, You display the contents of the change before.'),
						));
					?>

				</dd>
			</dl>
		</li>

		<li>
			<dl>
				<dt>
					<?php
						echo $this->Form->label('AnnouncementEdit.approved_mail_flag', __('Announce mail setting'));
					?>
				</dt>
				<dd>
					<?php
						echo $this->Form->input('AnnouncementEdit.approved_mail_flag',array(
							'type' => 'radio',
							'options' => array(_ON => __('Send email'), _OFF => __('Not send')),
							'value' => intval($announcement_edit['AnnouncementEdit']['approved_mail_flag']),
							'div' => false,
							'legend' => false,
						));
					?>
					<div class="hr">
					<?php
						$settings = array(
							'type' => 'text',
							'value' => $announcement_edit['AnnouncementEdit']['approved_mail_subject'],
							'label' => __('E-mail Subject:'),
							'maxlength' => NC_VALIDATOR_TITLE_LEN,
							'size' => 23,
							'error' => array('attributes' => array(
								'selector' => true
							)),
						);
						echo $this->Form->input('AnnouncementEdit.approved_mail_subject', $settings);
						$settings = array(
							'type' => 'textarea',
							'escape' => false,
							'value' => $announcement_edit['AnnouncementEdit']['approved_mail_body'],
							'label' => __('Message：'),
							'error' => array('attributes' => array(
								'selector' => true
							)),
						);
						echo $this->Form->input('AnnouncementEdit.approved_mail_body', $settings);
					?>
					</div>
				</dd>
			</dl>
		</li>
	</ul>
</fieldset>
<?php
	echo $this->Html->div('submit',
		$this->Form->button(__('Ok'), array('name' => 'ok', 'class' => 'common-btn', 'type' => 'submit')).
		$this->Form->button(__('Cancel'), array('name' => 'cancel', 'class' => 'common-btn', 'type' => 'button',
			'data-pjax' => '#'.$id, 'data-ajax-url' =>  $this->Html->url(array('controller' => 'announcement', '#' => $id))))
	);
	echo $this->Form->end();
?>
<?php
	echo $this->Html->script('Announcement.announcement_edits/index');
?>
<script>
$(function(){
	$('#Form<?php echo($id); ?>').AnnouncementEdits('<?php echo($id);?>');
});
</script>