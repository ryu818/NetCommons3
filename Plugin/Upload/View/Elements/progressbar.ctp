<?php
/**
 * ファイルサイズプログレスバー画面
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.Upload.View
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
?>
<div class="upload-progressbar-outer">
	<div id="upload-progressbar<?php echo $id; ?>" class="upload-progressbar"><div class="upload-progress-label"><?php echo $file_size_rate; ?>%</div></div>
	<span class="upload-progress-caption"><?php echo __d('upload', '%1$s/%2$s Use', $file_size, $file_max_size);?></span>
<script>
$(function(){
	<?php
		$addClass = '';
		if($file_size_rate >= 90) {
			$addClass = ".addClass('upload-progressbar-red')";
		} else if($file_size_rate >= 70) {
			$addClass = ".addClass('upload-progressbar-orange')";
		}
	?>
	$('#upload-progressbar<?php echo $id;?>').progressbar( {"value": <?php echo $file_size_rate; ?>})<?php echo $addClass; ?>;
});
</script>
</div>