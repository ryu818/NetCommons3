<?php
/**
 * システム管理 表示設定画面
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.User.View
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
?>
<?php
	echo $this->element('header');
?>
<fieldset class="form system-fieldset">
	<ul class="nc-lists">
		<?php
			echo $this->element('item', array('item' => $configs['display_header_menu']));
			echo $this->element('item', array('item' => $configs['display_page_menu']));
			echo $this->element('item', array('item' => $configs['pagemenu_community_limit']));
			echo $this->element('item', array('item' => $configs['pagemenu_display_public_community']));
			// echo $this->element('item', array('item' => $configs['temp_name']));
		?>
	</ul>
</fieldset>
<?php
	echo $this->element('footer');
?>