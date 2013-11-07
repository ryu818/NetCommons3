<?php
/**
 * ライブラリから追加画面
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.Upload.View
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
?>

<div class="upload-selected clearfix">
	<?php echo($this->element('library_selected')); ?>
</div>
<div class="table">
	<div class="upload-library-main table-cell">
		<?php
			echo $this->Form->create('UploadSearch', array('data-ajax' => 'this'));
		?>
		<?php echo($this->element('search')); ?>
		<?php echo($this->element('progressbar')); ?>
		<?php echo($this->element('library_list')); ?>
		<?php
			echo $this->Form->end();
		?>
	</div>
	<div class="upload-library-fileinfo table-cell">
		<?php
			echo $this->Form->create('UploadDetail', array('data-ajax' => 'this', 'id' => $id.'-library-fileinfo'));
		?>

		<?php
			echo $this->Form->end();
		?>
	</div>
	<div class="upload-footer-btn">
		<?php
		if ($popup_type == 'image') {
			$onclick = '$.Upload.addUploadForImage(\'library\');';
		} else {
			$onclick = '$.Upload.addUploadForFile(\'library\');';
		}
		$btnName = ($is_wysiwyg) ? __d('upload', 'Insert into Post') : __('Ok');
		$registBtn = '';
		if($is_callback || $is_wysiwyg) {
			$registBtn = $this->Form->button($btnName, array(
				'name' => 'ok',
				'class' => 'common-btn',
				'type' => 'button',
				'onclick' => $onclick
			));
		}
		echo $registBtn;
		echo $this->Form->button(__('Cancel'), array('name' => 'cancel', 'class' => 'common-btn', 'type' => 'button',
			'onclick' => '$.Upload.closeDialog(event); return false;'));
		?>
	</div>
</div>
<script>
$(function(){
	$.Upload.initLibraryTab('<?php echo $id; ?>');
});
</script>
<?php echo($this->element('library_list/template', array('name' => 'item'))); ?>
<?php echo($this->element('library_list/template', array('name' => 'more'))); ?>