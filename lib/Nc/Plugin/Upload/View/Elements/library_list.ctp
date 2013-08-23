<?php
/**
 * ライブラリから追加 - ライブラリ部分
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.Upload.View
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
?>
<ul id="upload-library-list<?php echo $id; ?>" class="upload-library-list clearfix">
	<?php if(count($search_results) > 0): ?>
		<?php foreach($search_results as $key=> $data): ?>
			<?php echo($this->element('library_list/item', array('data' => $data['UploadSearch']))); ?>
		<?php endforeach; ?>
	<?php endif; ?>

	<?php if($has_more): ?>
	<?php echo($this->element('library_list/more', array('page' => $page+1))); ?>
	<?php endif; ?>
</ul>
<script>
	$(function(){
		$.Upload.setDatas(<?php echo json_encode($search_results); ?>);
	});
</script>
