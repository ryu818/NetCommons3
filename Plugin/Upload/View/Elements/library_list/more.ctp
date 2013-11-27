<?php
/**
 * ライブラリから追加 - ライブラリ ページ送り(More)
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.Upload.View
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
?>
<li id ="upload-library-has-more<?php echo $id;?>">
	<?php
		$hasMoreUrl =  array(
			$plugin,
			'page' => $page,
		);
		echo $this->Html->link(__d('upload', 'More'), $hasMoreUrl, array(
			'class' => 'display-none',
			'onclick' => "var form = $('#Form".$id."'); $('input[name=\"data[UploadSearch][page]\"]:first', form).val(".($page)."); form.submit(); return false;",
		));
	?>
</li>