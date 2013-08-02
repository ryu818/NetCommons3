<?php
/**
 * システム管理 初期画面
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.User.View
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
?>
<div id="system-init-tab" data-width="750">
	<ul style="display:none;">
		<li><a href="#system-init-tab-general"><?php echo(__d('system', 'General setting'));?></a></li>

		<li><a href="<?php echo $this->Html->url(array('action' => 'login_logout'));?>"><?php echo(__d('system', 'Sign in and Sign out'));?></a></li>
		<li><a href="<?php echo $this->Html->url(array('action' => 'closed'));?>"><?php echo(__d('system', 'Turn your site off'));?></a></li>

		<li><a href="<?php echo $this->Html->url(array('action' => 'server'));?>"><?php echo(__d('system', 'Server setting'));?></a></li>
		<li><a href="<?php echo $this->Html->url(array('action' => 'mail'));?>"><?php echo(__d('system', 'Mail setting'));?></a></li>
		<li><a href="<?php echo $this->Html->url(array('action' => 'meta'));?>"><?php echo(__d('system', 'About this site'));?></a></li>
		<li><a href="<?php echo $this->Html->url(array('action' => 'display'));?>"><?php echo(__d('system', 'Display setting'));?></a></li>
		<li><a href="<?php echo $this->Html->url(array('action' => 'module'));?>"><?php echo(__d('system', 'Module setting'));?></a></li>
		<li><a href="<?php echo $this->Html->url(array('action' => 'membership'));?>"><?php echo(__d('system', 'Membership'));?></a></li>

		<li><a href="<?php echo $this->Html->url(array('action' => 'autoregist'));?>"><?php echo(__d('system', 'Automatic registration'));?></a></li>

		<li><a href="<?php echo $this->Html->url(array('action' => 'community'));?>"><?php echo(__d('system', 'Community setting'));?></a></li>
		<li><a href="<?php echo $this->Html->url(array('action' => 'developper'));?>"><?php echo(__d('system', 'For developpers'));?></a></li>
	</ul>
	<div id="system-init-tab-general">
	<?php
		echo $this->element('general');
	?>
	</div>
<?php
	echo $this->Html->div('btn-bottom',
		$this->Form->button(__('Close'), array('name' => 'cancel', 'class' => 'common-btn', 'type' => 'button',
			'onclick' => '$(\'#'.$dialog_id.'\').dialog(\'close\'); return false;'))
	);
?>
<?php
	echo $this->Html->css(array('System.index/'));
	echo $this->Html->script(array('System.index/'));
?>
<script>
$(function(){
	$('#system-init-tab').System('<?php echo($dialog_id);?>');
});
</script>
</div>