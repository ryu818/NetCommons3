<?php
/**
 * 背景 ページ送り(More)
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.Upload.View
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
?>
<li class ="pages-menu-hasmore display-none">
	<?php
		echo $this->Html->link(__d('page', 'More'), array(), array(
			'onclick' => "var form = $('#Form".$id."'); $('input[name=\"data[Background][".$type."_page]\"]:first', form).val(".($page)."); $.PageStyle.setConfirm('".$id."', '".$type."_search'); form.submit(); return false;",
		));
	?>
</li>