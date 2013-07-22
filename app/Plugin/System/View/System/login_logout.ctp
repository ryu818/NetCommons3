<?php
/**
 * システム管理 ログインとログアウト画面
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
		<?php echo __('Login');?>
	</legend>
	<ul class="lists">
		<?php
			echo $this->element('item', array('item' => $configs['autologin_use']));
			echo $this->element('item', array('item' => $configs['autologin_cookie_name']));
			echo $this->element('item', array('item' => $configs['autologin_expires']));
			echo $this->element('item', array('item' => $configs['login_autocomplete']));
			echo $this->element('item', array('item' => $configs['use_ssl']));
		?>
	</ul>
</fieldset>
<fieldset class="form system-fieldset">
	<legend>
		<?php echo __('Logout');?>
	</legend>
	<ul class="lists">
		<?php
			echo $this->element('item', array('item' => $configs['session_timeout']));
		?>
	</ul>
</fieldset>
<?php
	echo $this->element('footer');
?>