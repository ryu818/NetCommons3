<?php
/**
 * ライブラリから追加 - ライブラリ Item
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.Upload.View
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
?>
<li id="<?php echo $id; ?>-item-<?php echo $data['id']; ?>" class="upload-attachment">
	<a onclick="return $.Upload.clickItem(event, this);" href="#" class="upload-preview upload-type-<?php echo $data['file_type']; ?> upload-<?php echo $data['orientation']; ?>" data-upload-id="<?php echo $data['id']; ?>">
		<div class="upload-thumbnail">
			<div class="nc-thumbnail-centered">
				<img src="<?php echo $data['url']; ?>" alt="" />
			</div>
		</div>
		<div class="upload-filename">
			<div><?php echo h($data['file_name']); ?></div>
		</div>
		<div class="upload-check"></div>
	</a>
</li>