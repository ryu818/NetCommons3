<?php
/**
 * アップロード画面(URLから参照)
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.Upload.View
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
?>
<div id="<?php echo $id; ?>-ref_url-outer" class="upload-fileinfo-type-image">
	<div class="upload-ref-url">

	</div>
	<?php
		if ($popup_type == 'image') {
			$onclick = '$.Upload.addUploadForImage(\'ref_url\');';
		} else {
			$onclick = '$.Upload.addUploadForFile(\'ref_url\');';
		}
		$btnName = ($is_wysiwyg) ? __d('upload', 'Insert into Post') : __('Ok');
		echo $this->Html->div('submit',
			$this->Form->button($btnName, array(
				'name' => 'ok',
				'class' => 'common-btn',
				'type' => 'button',
				'onclick' => $onclick
			)).
			$this->Form->button(__('Cancel'), array('name' => 'cancel', 'class' => 'common-btn', 'type' => 'button',
				'onclick' => '$.Upload.closeDialog(event); return false;'))
		);
	?>
	<?php echo($this->element('library_list/template', array('name' => 'ref_url'))); ?>
	<script>
	$(function(){
		$.Upload.setData(0, 'real_url', '<?php echo (isset($upload['Upload']['real_url'])) ? $upload['Upload']['real_url'] : $src; ?>');
		$.Upload.initRefUrlTab('<?php echo $id; ?>');
	});
	</script>
</div>
