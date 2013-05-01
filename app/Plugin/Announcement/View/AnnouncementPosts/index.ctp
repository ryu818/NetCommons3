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
?>
<div class="table widthmax">
<div class="table-cell">
<fieldset class="form">
	<ul class="lists">
		<li>
			<?php
				echo($this->Form->error('Revision.content'));
				echo $this->Form->textarea('Revision.content', array('escape' => false, 'required' => false, 'class' => 'nc-wysiwyg', 'value' => $announcement['Revision']['content']));
			?>
		</li>
	</ul>
</fieldset>
<?php
	echo $this->Form->hidden('is_temporally' , array('name' => 'is_temporally', 'value' => _OFF));
	echo $this->Html->div('submit',
		$this->Form->button(__('Save temporally'), array('name' => 'temporally', 'class' => 'common-btn',
			'type' => 'button', 'onclick' => "$('#AnnouncementIsTemporally".$id."').val(1);$(this.form).submit();")).
		$this->Form->button(__('Ok'), array('name' => 'ok', 'class' => 'common-btn', 'type' => 'submit')).
		$this->Form->button(__('Cancel'), array('name' => 'cancel', 'class' => 'common-btn', 'type' => 'button',
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
				<div class="small outer">
				<?php
					$settings = array(
						'value' => _ON,
						'checked' => !empty($announcement['Announcement']['pre_change_flag']) ? true : false,
						'label' =>__('You display the contents of the change before.'),
						'type' => 'checkbox',
						'div' => false
					);
					echo $this->Form->input('Announcement.pre_change_flag', $settings);
				?>
				<div class="note indent"<?php if(!$this->Form->isFieldError('Announcement.pre_change_date') && empty($announcement['Announcement']['pre_change_flag'])): ?> style="display:none;"<?php endif; ?>>
					<?php
						if($this->request->is('post') && !empty($announcement['Announcement']['pre_change_date'])) {
							if($this->Form->isFieldError('Announcement.pre_change_date')) {
								$pre_change_date = $announcement['Announcement']['pre_change_date'];
							} else {
								$pre_change_date = date(__('Y-m-d H:i'), strtotime($announcement['Announcement']['pre_change_date']));
							}
						} else if(!empty($announcement['Announcement']['pre_change_date'])) {
							$pre_change_date = $this->TimeZone->date($announcement['Announcement']['pre_change_date'], __('Y-m-d H:i'));
						} else {
							$pre_change_date = '';
						}
						$settings = array(
							'type' => 'text',
							'value' => $pre_change_date,
							'label' => false,
							'div' => false,
							'maxlength' => 16,
							'size' => 15,
							'class' => 'nc-datetime text normal-text',
							'error' => array('attributes' => array(
								'selector' => true
							)),
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
echo $this->Form->end();
echo ($this->Html->script(array('Announcement.announcement_posts/index', 'plugins/jquery.nc_wysiwyg.js','plugins/jquery-ui-timepicker-addon.js', 'locale/'.$locale.'/plugins/jquery-ui-timepicker.js')));
echo ($this->Html->css(array('plugins/jquery.nc_wysiwyg.css', 'plugins/jquery-ui-timepicker-addon.css')));
?>
<script>
$(function(){
	$('#<?php echo($id); ?>').AnnouncementPosts('<?php echo ($id);?>');
});
</script>
</div>