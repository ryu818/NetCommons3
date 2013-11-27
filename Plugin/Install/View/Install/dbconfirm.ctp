<?php
$this->assign('title', __d('install', 'Confirm database settings'));
?>
<div class="install">
	<form method="post" action="<?php echo $this->Html->url(array('plugin' => 'install','controller' => 'install','action' => 'dbconfirm')); ?>">
		<?php echo($this->element('hidden')); ?>
		<h1><?php echo $this->fetch('title'); ?></h1>
		<?php
			if($config['persistent']) {
				$persistent = __('Yes');
			} else {
				$persistent = __('No');
			}
			$lists = array(
				array(
					'title' => __d('install', 'Site Name'),
					'value' => $config['site_name']
				),
				array(
					'title' => __d('install', 'Database'),
					'value' => $config['className']
				),
				array(
					'title' => __d('install', 'Database Hostname'),
					'value' => $config['host']
				),
				array(
					'title' => __d('install', 'Database Username'),
					'value' => $config['login']
				),
				array(
					'title' => __d('install', 'Database Password'),
					'value' => preg_replace('/.{1}/', '*', $config['password']),
				),
				array(
					'title' => __d('install', 'Database Name'),
					'value' => $config['database']
				),
				array(
					'title' => __d('install', 'Table Prefix'),
					'value' => $config['prefix']
				),
				array(
					'title' => __d('install', 'Database Port'),
					'value' => $config['port']
				),
				array(
					'title' => __d('install', 'Use persistent connection?'),
					'value' =>  $persistent
				),
			);

		?>
		<ul class="lists install-lists">
			<?php foreach($lists as $list): ?>
			<li>
				<dl>
					<dt>
						<label>
							<?php echo($list['title']); ?>
						</label>
					</dt>
					<dd>
						<?php if(isset($list['value'])){echo(h($list['value']));} ?>
					</dd>
				</dl>
			</li>
			<?php endforeach; ?>
		</ul>
		<div class="clear">
			<?php
				$check = true;

				// Exists database
				if ($exists_database) {
					echo '<p class="success message">' . __d('install', 'Database %s exists and connectable.', $config['database']) . '</p>';
				} else {
					$check = false;
					echo '<p class="error message">' . __d('install', 'Database %s does not exists.', $config['database']) . '</p>';
				}

				if ($exists_table) {
					echo '<p class="error message">' . __d('install', 'Tables for NetCommons already exist in your database.') . '</p>';
				}

			?>
		</div>
		<?php if(!$check): ?>
		<div>
			<?php if($failure_database): ?>
				<?php echo '<p class="error message">' . __d('install', 'Could not create database. Contact the server administrator for details.') . '</p>'; ?>
			<?php else: ?>
			<div class="install-create-db">
				<?php echo(__d('install', 'The following database was not found on the server:')); ?><span class="bold"><?php echo(h($config['database'])); ?></span>
				<br />
				<?php echo(__d('install', 'Attempt to create it?')); ?>
				<input style="width:50%;" type="submit" value="<?php echo(__d('install', 'Attempt to create database.')); ?>" name="create_db" class="btn" />
				<input type="hidden" name="create_db" value="<?php echo(_ON); ?>" />
			</div>
			<div class="note">
				<?php echo(__d('install', 'When a database name is wrong, please perform re-input of the setting. <br />When I cannot make this database in the set user account, I make it separately, and please perform re-reading of this page.')); ?>
			</div>
			<?php endif; ?>
		</div>
		<?php endif; ?>

		<div class="btn-bottom align-right">
			<input type="button" value="<?php echo(__d('install', 'Reload')); ?>" name="reload" class="btn" onclick="location.href='<?php echo $this->Html->url(array('plugin' => 'install','controller' => 'install','action' => 'dbconfirm')); ?>';" />
			<input type="button" value="<?php echo(__('&lt;&lt;Back')); ?>" name="back" class="btn" onclick="location.href='<?php echo $this->Html->url(array('plugin' => 'install','controller' => 'install','action' => 'database')); ?>';" />
			<input type="button" value="<?php echo(__('Next&gt;&gt;')); ?>" name="next" <?php if(!$check || $exists_table): ?>disabled="disabled" class="btn disabled"<?php else: ?>class="btn"<?php endif; ?> onclick="location.href='<?php echo $this->Html->url(array('plugin' => 'install','controller' => 'install','action' => 'data')); ?>';" />
		</div>
	</form>
</div>
