<?php
/**
 * ライブラリから追加画面 ファイル選択部分
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.Upload.View
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
?>
<div class="upload-selection">
	<div class="upload-selection-label">
		<?php
			echo $this->Form->button(__d('upload', 'Delete all'), array(
				'name' => 'delete',
				'id' => $id . '-delete-all',
				'class' => 'common-btn-min nc-button-red',
				'type' => 'button',
				'data-ajax-confirm' => __d('upload', 'Deleting the select file. <br />Are you sure to proceed?'),
				'data-ajax-type' => 'post',
				'data-ajax' => 'this',
				'data-ajax-url' => $this->Html->url(array('action' => 'delete')),
				'data-ajax-callback' => '$.Upload.deleteSuccess(res);',
				'escape' => _ON,
			));
			echo $this->Form->button(__('Clear'), array(
				'name' => 'clear',
				'class' => 'common-btn-min nc-button',
				'type' => 'button',
				'onclick' => '$.Upload.clearSelection();'
			));
		?>
	</div>
	<div class="upload-selection-list">
		<div id="<?php echo $id; ?>-selection" class="upload-attachment table">
		</div>
	</div>
</div>
