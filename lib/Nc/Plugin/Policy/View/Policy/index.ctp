<div data-width="1100">
	<?php
		echo $this->Html->css(array('Policy.index/'));
		echo $this->Html->script(array('Policy.index/'));
	?>
	<div id="policy-init-tab">
		<ul id="policy-init-tab-ul">
			<li><a href="#policy-init-tab-admin"><?php echo __('Administrator');?></a></li>
			<li><a href="<?php echo $this->Html->url(array('action' => 'chief'));?>"><?php echo __('Room Manager');?></a></li>
			<li><a href="<?php echo $this->Html->url(array('action' => 'moderate'));?>"><?php echo __('Moderator');?></a></li>
			<li><a href="<?php echo $this->Html->url(array('action' => 'general'));?>"><?php echo __('Common User');?></a></li>
			<li><a href="<?php echo $this->Html->url(array('action' => 'guest'));?>"><?php echo __('Guest');?></a></li>
		</ul>
		<div id="policy-init-tab-admin">
		<?php
			echo $this->element('list');
		?>
		</div>
	</div>
<?php
	echo $this->Html->div('btn-bottom',
		$this->Form->button(__('Close'), array('name' => 'cancel', 'class' => 'common-btn', 'type' => 'button',
			'onclick' => '$(\'#'.$id.'\').dialog(\'close\'); return false;'))
	);
?>
</div>