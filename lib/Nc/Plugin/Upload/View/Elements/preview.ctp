<?php
/**
 * アップロードプレビュー画面
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.Upload.View
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
?>
<?php 
	$error = $this->Form->error('UploadLibrary.file_name');
	if(!empty($error)) {
		echo $error;
		return;
	}
?>
<div class="table">
	<div class="table-cell">
		<ul id="upload-preview-list<?php echo $id; ?>" class="upload-preview-list upload-library-list">
		<li class="upload-attachment">
			<a onclick="return $.Upload.clickItem(event, this, 'preview');" href="#" class="upload-preview upload-type-<?php echo $upload['UploadLibrary']['file_type']; ?> upload-<?php echo $upload['UploadLibrary']['orientation']; ?>" data-upload-id="<?php echo $upload['UploadLibrary']['id']; ?>">
				<div class="upload-thumbnail">
					<div class="upload-centered">
						<img src="<?php echo $upload['UploadLibrary']['url']; ?>" alt="" />
					</div>
				</div>
				<div class="upload-filename">
					<div><?php echo h($upload['UploadLibrary']['file_name']); ?></div>
				</div>
				<div class="upload-check"></div>
			</a>
			<script>
				$(function(){
					if ($.Upload) {
						$.Upload.data[<?php echo $upload['UploadLibrary']['id']; ?>] = <?php echo json_encode($upload['UploadLibrary']); ?>;
					}
				});
			</script>
		</li>
		</ul>
	</div>
	<div class="upload-library-fileinfo upload-preview-fileinfo table-cell">
		<?php
			echo $this->Form->create('UploadDetail', array('data-ajax' => 'this', 'id' => $id.'-library-fileinfo'));
		?>

		<?php
			echo $this->Form->end();
		?>
	</div>
</div>