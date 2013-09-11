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
		<?php  echo __d('upload', 'Please drag-and-drop or select a file to upload.'); ?>
	</div>
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
echo $this->Form->input('UploadLibrary.file_name', array(
	'type' => 'file',
	//'id' => 'nc-upload-inputfile-'.$id,
	'class' => 'nc-upload-inputfile',
	'label' => false,
	'div' => false,
	'multiple' => true,
));
?>
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
echo $this->Html->div('btn-bottom',
	$this->Form->button(__d('upload', 'Insert into Post'), array(
		'name' => 'ok',
		'class' => 'common-btn',
		'type' => 'button',
		'onclick' => $onclick.'return false;'
	)).
	$this->Form->button(__('Cancel'), array('name' => 'cancel', 'class' => 'common-btn', 'type' => 'button',
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