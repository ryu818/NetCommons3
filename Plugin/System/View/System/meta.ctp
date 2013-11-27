<?php
/**
 * システム管理 メタ情報画面
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
	<ul class="lists">
		<?php
			echo $this->element('item', array('item' => $configs['meta_author']));
			echo $this->element('item', array('item' => $configs['meta_copyright']));
			echo $this->element('item', array('item' => $configs['meta_keywords']));
			echo $this->element('item', array('item' => $configs['meta_description']));
			echo $this->element('item', array('item' => $configs['meta_robots']));
			echo $this->element('item', array('item' => $configs['meta_rating']));
		?>
	</ul>
</fieldset>
<?php
	echo $this->element('footer');
?>