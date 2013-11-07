<?php
/**
 * システム管理 開発者向け画面
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
			echo $this->element('item', array('item' => $configs['debug']));
		?>
	</ul>
</fieldset>
<?php
	echo $this->element('footer');
?>