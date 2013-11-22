<?php
/**
 * システム管理 入会退会設定画面
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
		<?php echo __d('system', 'User registration');?>
	</legend>
	<ul class="nc-lists">
		<?php
			echo $this->element('item', array('item' => $configs['mail_add_user_subject']));
			echo $this->element('item', array('item' => $configs['mail_add_user_body']));
		?>
	</ul>
</fieldset>
<fieldset class="form system-fieldset">
	<legend>
		<?php echo __d('system', 'Notification of password');?>
	</legend>
	<ul class="nc-lists">
		<?php
			echo $this->element('item', array('item' => $configs['mail_get_password_subject']));
			echo $this->element('item', array('item' => $configs['mail_get_password_body']));
			echo $this->element('item', array('item' => $configs['mail_new_password_subject']));
			echo $this->element('item', array('item' => $configs['mail_new_password_body']));
		?>
	</ul>
</fieldset>
<fieldset class="form system-fieldset">
	<legend>
		<?php echo __d('system', 'Membership cancellation');?>
	</legend>
	<ul class="nc-lists">
		<?php
			echo $this->element('item', array('item' => $configs['withdraw_membership_use']));
			echo $this->element('item', array('item' => $configs['withdraw_disclaimer']));
			echo $this->element('item', array('item' => $configs['withdraw_membership_send_admin']));
			echo $this->element('item', array('item' => $configs['mail_withdraw_membership_subject']));
			echo $this->element('item', array('item' => $configs['mail_withdraw_membership_body']));
		?>
	</ul>
</fieldset>
<?php
	echo $this->element('footer');
?>