<?php
/**
 * システム管理 閉鎖設定画面
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
		<?php echo __d('system', 'Turn your site off');?>
	</legend>
	<ul class="lists">
		<?php
			echo $this->element('item', array('item' => $configs['is_maintenance']));
			echo $this->element('item', array('item' => $configs['maintenance_text']));
		?>
	</ul>
</fieldset>
<fieldset class="form system-fieldset">
	<legend>
		<?php echo __d('system', 'Closed site');?>
	</legend>
	<ul class="lists">
		<?php
			echo $this->element('item', array('item' => $configs['is_closed_site']));
		?>
	</ul>
</fieldset>
<?php
	echo $this->element('footer');
?>