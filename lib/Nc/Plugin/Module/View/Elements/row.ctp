<?php foreach ($modules as $module): ?>
	<tr>
		<td>
			<?php echo(h($module['ModuleAdmin']['module_name'])); ?>
			(<?php echo(h($module['ModuleAdmin']['version'])); ?>)
		</td>
		<td>
			<?php if (isset($module['ModuleAdmin']['install_flag']) && $module['ModuleAdmin']['install_flag'] == _ON): ?>
				<?php $confirm = $this->Js->escape(__d('Module', 'Installing the module %s.<br />Are you sure to proceed?', $module['ModuleAdmin']['module_name'])); ?>
				<?php
					echo $this->Html->link(__d('Module', 'Install'), '#', array('title' => __d('Module', 'Install'),
						'onclick' => '$.Module.clickSubmit(event, \''.$module['ModuleAdmin']['dir_name'].'\', \'install\', \''.$confirm.'\');'));
				?>
			<?php else: ?>
				<?php $confirm = $this->Js->escape(__d('Module', 'Updating the module %s.<br />Are you sure to proceed?', $module['ModuleAdmin']['module_name'])); ?>
				<?php
					echo $this->Html->link(__d('Module', 'Update'), '#', array('title' => __d('Module', 'Update'),
						'onclick' => '$.Module.clickSubmit(event, \''.$module['ModuleAdmin']['dir_name'].'\', \'update\', \''.$confirm.'\');'));
				?>
			<?php endif; ?>
			<?php if (!isset($module['ModuleAdmin']['install_flag']) && (!isset($module['ModuleAdmin']['ini']['uninstall_flag']) || $module['ModuleAdmin']['ini']['uninstall_flag'] == _ON)): ?>
				&nbsp;&nbsp;|&nbsp;&nbsp;
				<?php $confirm = $this->Js->escape(__d('Module', 'Uninstalling the module %s.<br />Are you sure to proceed?', $module['ModuleAdmin']['module_name'])); ?>
				<?php $confirm_again = $this->Js->escape(__d('Module', 'All the data stored in %s will vanish.<br/>Are you sure to proceed?', $module['ModuleAdmin']['module_name'])); ?>
				<?php
					echo $this->Html->link(__d('Module', 'Uninstall'), '#', array('title' => __d('Module', 'Uninstall'),
						'onclick' => '$.Module.clickSubmit(event, \''.$module['ModuleAdmin']['dir_name'].'\', \'uninstall\', \''.$confirm.'\', \''.$confirm_again.'\');'));
				?>
			<?php endif; ?>
			<?php if (!isset($module['ModuleAdmin']['install_flag']) && (!isset($module['ModuleAdmin']['ini']['uninstall_flag']) || $module['ModuleAdmin']['ini']['uninstall_flag'] == _ON) && $module['ModuleAdmin']['disposition_flag'] == _ON): ?>
				&nbsp;&nbsp;|&nbsp;&nbsp;
				<?php /* TODO:未作成 */ ?>
				<a href="#" onclick="return false;">
					<?php echo(__d('Module', 'Authority setting'));?>
				</a>
			<?php endif; ?>
		</td>
	</tr>
<?php endforeach; ?>