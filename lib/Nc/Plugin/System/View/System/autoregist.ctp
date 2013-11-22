<?php
/**
 * システム管理 自動登録画面
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
			echo $this->element('item', array('item' => $configs['autoregist_use']));
			echo $this->element('item', array('item' => $configs['autoregist_approver']));
			echo $this->element('item', array('item' => $configs['autoregist_use_input_key']));
			foreach($autoregist_author as $key => $value) {
				$options[$key] = __($value);
			}
			echo $this->element('item', array('item' => $configs['autoregist_author'], 'options' => $options));
			echo $this->element('item', array('item' => $configs['autoregist_use_items']));
			echo $this->element('item', array('item' => $configs['autoregist_disclaimer']));
			echo $this->element('item', array('item' => $configs['mail_approval_subject']));
			echo $this->element('item', array('item' => $configs['mail_approval_body']));
		?>
	</ul>
</fieldset>
<?php
	echo $this->element('footer');
?>