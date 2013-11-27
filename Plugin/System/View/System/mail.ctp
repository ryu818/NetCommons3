<?php
/**
 * システム管理 メール設定画面
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
			echo $this->element('item', array('item' => $configs['from']));
			echo $this->element('item', array('item' => $configs['fromname']));
			echo $this->element('item', array('item' => $configs['htmlmail']));
			echo $this->element('item', array('item' => $configs['mobile_htmlmail']));
			echo $this->element('item', array('item' => $configs['mailmethod']));
			echo $this->element('item', array('item' => $configs['sendmailpath']));
			echo $this->element('item', array('item' => $configs['smtphost']));
			echo $this->element('item', array('item' => $configs['smtpuser']));
			echo $this->element('item', array('item' => $configs['smtppass']));
			echo $this->element('item', array('item' => $configs['smtptls']));
			echo $this->element('item', array('item' => $configs['mailheader']));
			echo $this->element('item', array('item' => $configs['mailfooter']));
		?>
	</ul>
</fieldset>
<?php
	echo $this->element('footer');
?>