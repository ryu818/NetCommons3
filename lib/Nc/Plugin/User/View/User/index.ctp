<div data-width="1110">
<div id="user-init-tab">
	<ul id="user-init-tab-ul">
		<li data-width="1110"><a href="#user-init-tab-list"><?php echo(__d('user', 'List of members'));?></a></li>
		<?php if ($hierarchy >= NC_AUTH_MIN_CHIEF): ?>
		<li class="user-init-tab-add" data-width="800"><a href="<?php echo $this->Html->url(array('action' => 'edit'));?>"><?php echo(__d('user', 'Add new member'));?></a></li>
		<li data-width="800"><a href="<?php echo $this->Html->url(array('action' => 'display_setting'));?>"><?php echo(__d('user', 'User info display setting'));?></a></li>
		<li><a href="<?php echo $this->Html->url(array('action' => 'import'));?>"><?php echo(__d('user', 'Import'));?></a></li>
		<?php endif; ?>
	</ul>
	<div id="user-init-tab-list">
	<?php
		echo $this->element('list');
	?>
	</div>
</div>
<?php
	echo $this->Html->div('nc-btn-bottom',
		$this->Form->button(__('Close'), array('name' => 'cancel', 'class' => 'nc-common-btn', 'type' => 'button',
			'onclick' => '$(\'#'.$id.'\').dialog(\'close\'); return false;'))
	);
?>
</div>
