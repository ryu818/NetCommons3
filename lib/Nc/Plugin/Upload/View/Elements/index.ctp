<?php
/**
 * アップロード画面(ドラッグアンドドロップによるアップロード)
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.Upload.View
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
echo $this->Form->create('UploadLibrary', array('class' => 'upload-files', 'type' => 'file'));
?>
<div class="upload-dragndrop-area">
	<div class="upload-dragndrop-text">
	</div>
<script>
if ($.support.xhrFormDataFileUpload) {
	var text = "<?php echo __d('upload', 'Please drag-and-drop or select a file to upload.'); ?>";
} else {
	var text = "<?php echo __d('upload', 'Please select a file to upload.'); ?>";
}
$('div.upload-dragndrop-text:first', $('#Form' + '<?php echo $id;?>')).text(text);
</script>
<?php
if ($this->request->query['popup_type'] == 'image') {
	echo $this->Form->input('UploadLibrary.resolusion', array(
		'type' => 'select',
		'options' => array(
			'normal'=>__d('upload', 'Normal size'),
			'asis'=>__d('upload', 'Full-scale size'),
			'large'=>__d('upload', 'Large size'),
			'middle'=>__d('upload', 'Middle size'),
			'small'=>__d('upload', 'Small size'),
			'icon'=>__d('upload', 'Icon size'),
		),
		'label' => false,
		'div' => false,
	));
}
?>
<div class="nc-common-btn nc-upload-btn">
	<span><?php echo(__('Select file'));?></span>
<?php
echo $this->Form->input('UploadLibrary.file_name', array(
	'type' => 'file',
	'class' => 'nc-upload-inputfile nc-upload-btn-inputfile',
	'label' => false,
	'div' => false,
	'multiple' => true,
));
?>
</div>
</div>
<?php
	echo $this->Form->end();
?>
<?php
if ($this->request->query['popup_type'] == 'image') {
	$onclick = '$.Upload.addUploadForImage(\'index\');';
} else {
	$onclick = '$.Upload.addUploadForFile(\'index\');';
}
$btnName = ($is_wysiwyg) ? __d('upload', 'Insert into Post') : __('Ok');
$registBtn = '';
if($is_callback || $is_wysiwyg) {
	$registBtn = $this->Form->button($btnName, array(
		'name' => 'ok',
		'class' => 'nc-common-btn',
		'type' => 'button',
		'onclick' => $onclick.'return false;'
	));
}

echo $this->Html->div('nc-btn-bottom',
	$registBtn.
	$this->Form->button(__('Cancel'), array('name' => 'cancel', 'class' => 'nc-common-btn', 'type' => 'button',
		'onclick' => '$.Upload.closeDialog(event); return false;'))
);
?>
<script>
$(function(){
	if($.Upload) {
		$.Upload.initUploadTab('<?php echo ($id);?>');
	}
});
</script>