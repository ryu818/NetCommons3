<?php foreach ($modules as $module): ?>
	<tr>
		<td>
			<?php echo(h($module['Module']['module_name'])); ?>
			(<?php echo(h($module['Module']['version'])); ?>)
		</td>
		<td>
			<?php if (isset($module['Module']['install_flag']) && $module['Module']['install_flag'] == _ON): ?>
				<?php $confirm = $this->Js->escape(__d('module', 'Installing the module %s.<br />Are you sure to proceed?', $module['Module']['module_name'])); ?>
				<?php
					echo $this->Html->link(__d('module', 'Install'), '#', array('title' => __d('module', 'Install'),
						'onclick' => '$.Module.clickSubmit(event, \''.$module['Module']['dir_name'].'\', \'install\', \''.$confirm.'\');'));
				?>
			<?php else: ?>
				<?php $confirm = $this->Js->escape(__d('module', 'Updating the module %s.<br />Are you sure to proceed?', $module['Module']['module_name'])); ?>
				<?php
					echo $this->Html->link(__d('module', 'Update'), '#', array('title' => __d('module', 'Update'),
						'onclick' => '$.Module.clickSubmit(event, \''.$module['Module']['dir_name'].'\', \'update\', \''.$confirm.'\');'));
				?>
			<?php endif; ?>
			<?php if (!isset($module['Module']['install_flag']) && (!isset($module['Module']['ini']['uninstall_flag']) || $module['Module']['ini']['uninstall_flag'] == _ON)): ?>
				<?php echo(__('&nbsp;&nbsp;|&nbsp;&nbsp;'));?>
				<?php $confirm = $this->Js->escape(__d('module', 'Uninstalling the module %s.<br />Are you sure to proceed?', $module['Module']['module_name'])); ?>
				<?php $confirm_again = $this->Js->escape(__d('module', 'All the data stored in %s will vanish.<br/>Are you sure to proceed?', $module['Module']['module_name'])); ?>
				<?php
					echo $this->Html->link(__d('module', 'Uninstall'), '#', array('title' => __d('module', 'Uninstall'),
						'onclick' => '$.Module.clickSubmit(event, \''.$module['Module']['dir_name'].'\', \'uninstall\', \''.$confirm.'\', \''.$confirm_again.'\');'));
				?>
			<?php endif; ?>
			<?php if (!isset($module['Module']['install_flag']) && (!isset($module['Module']['ini']['uninstall_flag']) || $module['Module']['ini']['uninstall_flag'] == _ON) && $module['Module']['disposition_flag'] == _ON): ?>
				<?php echo(__('&nbsp;&nbsp;|&nbsp;&nbsp;'));?>
				<?php /* TODO:未作成 */ ?>
				<a href="#" onclick="return false;">
					<?php echo(__d('module', 'Authority setting'));?>
				</a>
			<?php endif; ?>
		</td>
	</tr>
<?php endforeach; ?>