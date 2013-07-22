<?php
/**
 * システム管理 モジュール設定画面
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
	<legend>
		<?php echo __d('system', 'Module operation');?>
	</legend>
	<ul class="lists">
		<?php
			echo $this->element('item', array('item' => $configs['copy_operation_modules']));
			echo $this->element('item', array('item' => $configs['shortcut_operation_modules']));
			echo $this->element('item', array('item' => $configs['move_operation_modules']));
		?>
	</ul>
</fieldset>
<fieldset class="form system-fieldset">
	<legend>
		<?php echo __d('system', 'Upload');?>
	</legend>
	<ul class="lists">
		<?php
			echo $this->element('item', array('item' => $configs['upload_normal_width_size'], 'nextItem' => $configs['upload_normal_height_size']));
		?>
	</ul>
</fieldset>
<?php
	echo $this->element('footer');
?>