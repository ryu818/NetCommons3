<?php
/**
 * アップロードメイン画面
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.Upload.View
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
$activeTab = 0;
$indexUrl = $this->Html->url(array('action' => 'index', 'is_tab' => _ON, $plugin, '?'=>$this->params['url']));
$libraryUrl = $this->Html->url(array('action' => 'library', 'is_tab' => _ON, $plugin, '?'=>$this->params['url']));
$refUrl = $this->Html->url(array('action' => 'ref_url', 'is_tab' => _ON, $plugin, '?'=>$this->params['url']));
switch($this->action) {
	case 'index':
		$indexUrl = '#' . $id . '-' . $this->action;
		break;
	case 'library':
		$libraryUrl = '#' . $id . '-' . $this->action;
		$activeTab = 1;
		break;
	case 'ref_url':
		$refUrl = '#' . $id. '-' . $this->action;
		$activeTab = 2;
		break;
}
?>
<?php
	$locale = Configure::read(NC_SYSTEM_KEY.'.locale');
	echo $this->Html->css(array('Upload.upload/index', 'plugins/colorbox'));
	echo $this->Html->script(array('Upload.upload/index', 'plugins/jquery.colorbox', 'locale/'.$locale.'/plugins/jquery.colorbox'));
?>
<script>
$(function(){
	$.Upload.init('<?php echo ($id);?>', '<?php echo ($dialog_id);?>', <?php if($multiple):?>true<?php else:?>false<?php endif;?>,$.Common.tempSetting.upload, <?php echo ($activeTab);?>,
		<?php echo UPLOAD_FILEINFO_OPTIONS; ?>
	);
});
</script>
<?php echo($this->element('library_list/template', array('name' => 'fileinfo'))); ?>
<div id="<?php echo ($id);?>">
	<ul>
		<li><a href="<?php echo $indexUrl; ?>"><?php echo(__d('upload', 'Upload'));?></a></li>
		<li data-width="max"><a href="<?php echo $libraryUrl; ?>"><?php echo(__d('upload', 'Add from library'));?></a></li>
		<?php if($popup_type == 'image' && $is_wysiwyg): ?>
		<li><a href="<?php echo $refUrl; ?>"><?php echo(__d('upload', 'Refer url'));?></a></li>
		<?php endif; ?>
	</ul>
	<div id="<?php echo ($id);?>-<?php echo ($this->action);?>">
		<?php echo($this->element($this->action)); ?>
	</div>
</div>
