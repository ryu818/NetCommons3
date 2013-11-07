<?php
/**
 * システム管理 コミュニティー設定画面
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
			echo $this->element('item', array('item' => $configs['community_default_publication_range']));
			echo $this->element('item', array('item' => $configs['community_default_participate_flag']));
			echo $this->element('item', array('item' => $configs['community_participate_sendmail']));
			echo $this->element('item', array('item' => $configs['community_mail_participate_announce_subject']));
			echo $this->element('item', array('item' => $configs['community_mail_participate_announce_body']));
			echo $this->element('item', array('item' => $configs['community_withdraw_sendmail']));
			echo $this->element('item', array('item' => $configs['community_mail_withdraw_subject']));
			echo $this->element('item', array('item' => $configs['community_mail_withdraw_body']));
			echo $this->element('item', array('item' => $configs['community_mail_invite_subject']));
			echo $this->element('item', array('item' => $configs['community_mail_invite_body']));
			echo $this->element('item', array('item' => $configs['community_mail_wait_approval_subject']));
			echo $this->element('item', array('item' => $configs['community_mail_wait_approval_body']));
			echo $this->element('item', array('item' => $configs['community_mail_confirm_approval_subject']));
			echo $this->element('item', array('item' => $configs['community_mail_confirm_approval_body']));
			echo $this->element('item', array('item' => $configs['community_mail_approved_subject']));
			echo $this->element('item', array('item' => $configs['community_mail_approved_body']));
		?>
	</ul>
</fieldset>
<?php
	echo $this->element('footer');
?>