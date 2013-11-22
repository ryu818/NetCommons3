<?php
/**
 * システム管理 一般設定画面
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.User.View
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
?>
<?php
	$loginUser = $this->Session->read(NC_AUTH_KEY.'.'.'User');
	echo $this->element('header');
?>
<fieldset class="form system-fieldset">
	<legend>
		<?php echo __d('system', 'System');?>
	</legend>
	<ul class="nc-lists">
		<?php
			echo $this->element('item', array('item' => $configs['sitename']));
			$options[''] = __('Auto');
			foreach($languages as $key => $value) {
				$options[$key] = __($value);
			}
			echo $this->element('item', array('item' => $configs['language'], 'options' => $options));
			echo $this->element('item', array('item' => $configs['default_TZ']));
			echo $this->element('item', array('item' => $configs['first_startpage_id']));
		?>
	</ul>
</fieldset>
<fieldset class="form system-fieldset">
	<legend>
		<?php echo __d('system', 'Default member setting');?>
	</legend>
	<ul class="nc-lists">
		<?php
			echo $this->element('item', array('item' => $configs['default_entry_public_authority_id']));
			echo $this->element('item', array('item' => $configs['default_entry_myportal_authority_id']));
			echo $this->element('item', array('item' => $configs['default_entry_group_authority_id']));
			echo $this->element('item', array('item' => $configs['myportal_space_name']));
			echo $this->element('item', array('item' => $configs['private_space_name'], 'descriptionArgs' => $loginUser['handle']));
		?>
	</ul>
</fieldset>
<?php
	echo $this->element('footer');
?>