<div class="module-current-version">
	<?php echo(__d('module', 'Version：'));?><span class="module-version"><?php echo($version); ?></span>
	<?php if ($version != NC_VERSION): ?>
		<span class="errorstr">-><?php echo(NC_VERSION); ?></span>
	<?php endif; ?>
</div>
<div id="<?php echo($id); ?>-nc-message" class="nc-message">
<?php
if ($version != NC_VERSION) {
	echo($this->element('message', array('success' => _OFF, 'message' => __d('module', 'Different versions found.Please update all the modules to arrange the version.'))));
}
if(isset($error_mes) && count($error_mes) > 0) {
	foreach($error_mes as $error) {
		echo($this->element('message', array('success' => _OFF, 'message' => $error)));
	}
}
if(isset($success_mes) && count($success_mes) > 0) {
	foreach($success_mes as $success) {
		echo($this->element('message', array('success' => _ON, 'message' => $success)));
	}
}
?>
</div>
<div id="module-init-tab">
	<ul>
		<li><a href="#module-init-tab-list"><span><?php echo(__d('module', 'Module Management'));?></span></a></li>
		<li><a href="#module-init-tab-displayseq"><span><?php echo(__d('module', 'Change display order'));?></span></a></li>
	</ul>

	<div id="module-init-tab-list">
		<?php
			echo $this->Form->create(null, array('id' => 'form'.$id, 'data-ajax' => '#'.$id, 'data-ajax-method' => 'inner', 'data-ajax-effect' => 'fold'));
		?>
		<div id="module-list-tab">
			<ul>
				<li><a href="#module-list-tab-install"><span><?php echo(__d('module', 'Installed modules'));?></span></a></li>
				<li><a href="#module-list-tab-not-install"><span><?php echo(__d('module', 'Modules not yet installed'));?></span></a></li>
				<li><a href="#module-list-tab-system"><span><?php echo(__d('module', 'System modules'));?></span></a></li>
			</ul>
			<div id="module-list-tab-install">
				<table id="module-list-install">
					<thead>
						<tr>
							<th scope="col" width="190"><?php echo(__d('module', 'Module name')); ?></th>
							<th scope="col" width="365"><?php echo(__('Manage')); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php echo($this->element('row', array('modules' => $general_modules))); ?>
					</tbody>
				</table>
			</div>
			<div id="module-list-tab-not-install">
				<table id="module-list-not-install">
					<thead>
						<tr>
							<th scope="col" width="190"><?php echo(__d('module', 'Module name')); ?></th>
							<th scope="col" width="365"><?php echo(__('Manage')); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php echo($this->element('row', array('modules' => $not_install_modules))); ?>
					</tbody>
				</table>
			</div>
			<div id="module-list-tab-system">
				<table id="module-list-system">
					<thead>
						<tr>
							<th scope="col" width="190"><?php echo(__d('module', 'Module name')); ?></th>
							<th scope="col" width="365"><?php echo(__('Manage')); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php echo($this->element('row', array('modules' => $system_modules))); ?>
					</tbody>
				</table>
			</div>
		</div>
		<div class="module-allupdate">
			<?php
				$confirm = $this->Js->escape(__d('module', 'Updating all the modules<br />Are you sure to proceed?'));
				echo $this->Html->link(__d('module', 'Update-All'), '#', array('title' => __d('module', 'Update-All'),
					'onclick' => '$.Module.clickSubmit(event, \'\', \'update-all\', \''.$confirm.'\');'));
			?>
		</div>
		<?php
			echo $this->Form->hidden('dir_name' , array('name' => 'dir_name', 'value' => ''));
			echo $this->Form->hidden('type' , array('name' => 'type', 'value' => ''));
			echo $this->Form->end();
		?>
	</div>
	<div id="module-init-tab-displayseq">
		<?php /*TODO:未作成*/ ?>
		表示順変更：未作成
	</div>
</div>
<?php
	echo $this->Html->div('btn-bottom',
		$this->Form->button(__('Close'), array('name' => 'cancel', 'class' => 'common-btn', 'type' => 'button',
			'onclick' => '$(\'#'.$id.'\').dialog(\'close\'); return false;'))
	);
	echo $this->Html->css(array('Module.index/', 'plugins/flexigrid'));
	echo $this->Html->script(array('Module.index/', 'plugins/flexigrid'));
?>
<script>
$(function(){
	$('#module-init-tab').Module('<?php echo($id);?>', <?php echo($active_tab);?>);
});
</script>