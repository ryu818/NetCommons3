<?php
/**
 * システム管理 サーバー設定画面
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
		<?php echo __d('system', 'System Configuration');?>
	</legend>
	<ul class="nc-lists">
		<?php
			echo $this->element('item', array('item' => $configs['memory_limit']));
			echo $this->element('item', array('item' => $configs['script_compress_gzip']));
		?>
	</ul>
</fieldset>
<fieldset class="form system-fieldset">
	<legend>
		<?php echo __d('system', 'Session');?>
	</legend>
	<ul class="nc-lists">
		<?php
			echo $this->element('item', array('item' => $configs['session_name']));
			echo $this->element('item', array('item' => $configs['session_auto_regenerate']));
		?>
	</ul>
</fieldset>
<fieldset class="form system-fieldset">
	<legend>
		<?php echo __d('system', 'Proxy server');?>
	</legend>
	<ul class="nc-lists">
		<?php
			echo $this->element('item', array('item' => $configs['proxy_mode']));
			echo $this->element('item', array('item' => $configs['proxy_host']));
			echo $this->element('item', array('item' => $configs['proxy_port']));
			echo $this->element('item', array('item' => $configs['proxy_user']));
			echo $this->element('item', array('item' => $configs['proxy_pass']));
		?>
	</ul>
</fieldset>
<fieldset class="form system-fieldset">
	<legend>
		<?php echo __d('system', 'Security Communication');?>
	</legend>
	<ul class="nc-lists">
		<?php
			echo $this->element('item', array('item' => $configs['use_ssl'], 'separator'=> true));
		?>
	</ul>
</fieldset>
<?php
	echo $this->element('footer');
?>