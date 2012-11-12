<?php
$this->assign('title', __d('install', 'General configuration'));
?>
<div class="install">
	<form method="post" action="<?php echo $this->Html->url(array('plugin' => 'install','controller' => 'install','action' => 'database')); ?>">
		<?php echo($this->element('hidden')); ?>
		<h1><?php echo $this->fetch('title'); ?></h1>
		<?php
			$lists = array(
				array(
					'id' => 'DatabaseSiteName',
					'title' => __d('install', 'Site Name'),
					'value' => $this->Form->input('Database.site_name', array(
						'label' => '',
						'default' => $config['site_name']
					)),
				),
				array(
					'id' => 'DatabaseDriver',
					'title' => __d('install', 'Database'),
					'value' => $this->Form->input('Database.datasource', array(
						'label' => '',
						'default' => $config['datasource'],
						'empty' => false,
						'options' => array(
							'Database/Mysql' => 'Mysql',
							'Database/Sqlite' => 'Sqlite',
							'Database/Postgres' => 'Postgres',
							'Database/Sqlserver' => 'Sql server',
						),
					)),
					'note' =>  __d('install', 'Choose the database to be used.'),
				),
				array(
					'id' => 'DatabaseHost',
					'title' => __d('install', 'Database Hostname'),
					'value' => $this->Form->input('Database.host', array(
						'label' => '',
						'default' =>  h($config['host'])
					)),
					'note' =>  __d('install', 'Hostname of the database server. If you are unsure, \'localhost\' works in most cases.'),
				),
				array(
					'id' => 'DatabaseLogin',
					'title' => __d('install', 'Database Username'),
					'value' => $this->Form->input('Database.login', array(
						'label' => '',
						'default' =>  h($config['login'])
					)),
					'note' =>  __d('install', 'Your database user account on the host.'),
				),
				array(
					'id' => 'DatabasePassword',
					'title' => __d('install', 'Database Password'),
					'value' => $this->Form->input('Database.password', array(
						'label' => '',
						'default' =>  h($config['password'])
					)),
					'note' =>  __d('install', 'Password for your database user account.'),
				),
				array(
					'id' => 'DatabaseDatabase',
					'title' => __d('install', 'Database Name'),
					'value' => $this->Form->input('Database.database', array(
						'label' => '',
						'default' =>  h($config['database'])
					)),
					'note' =>  __d('install', 'The name of database on the host. The installer will attempt to create the database if not exist. <br />If the environment allows you to have only one database, please change the table prefix of the following item.'),
				),
				array(
					'id' => 'DatabasePrefix',
					'title' => __d('install', 'Table Prefix'),
					'value' => $this->Form->input('Database.prefix', array(
						'label' => '',
						'default' =>  h($config['prefix'])
					)),
					'note' =>  __d('install', 'This prefix will be added to all new tables created to avoid name conflict in the database. If you are unsure, just use the default.'),
				),
				array(
					'id' => 'DatabasePort',
					'title' => __d('install', 'Database Port'),
					'value' => $this->Form->input('Database.port', array(
						'label' => '',
						'default' => h($config['port'])
					)),
					'note' =>  __d('install', 'Leave blank if unknown.'),
				),
				array(
					'id' => 'DatabasePersistent',
					'title' => __d('install', 'Use persistent connection?'),
					'value' =>  '<label for="DatabasePersistent">'.
								'<input type="radio" value="true" id="DatabasePersistent" name="data[Database][persistent]" '.(($config['persistent']) ? ' checked="checked"' : '').'/>'.
								'&nbsp;'.__('Yes').'</label>&nbsp;&nbsp;'.
								'<label for="DatabasePersistentNo">'.
								'<input type="radio" value="false" id="DatabasePersistentNo" name="data[Database][persistent]" '.((!$config['persistent']) ? ' checked="checked"' : '').'/>'.
								'&nbsp;'.__('No').'</label>',
					'note' =>  __d('install', 'Default is \'NO\'. Choose \'NO\' if you are unsure.'),
				),
			);

		?>
		<ul class="lists install_lists">
			<?php foreach($lists as $list): ?>
			<li>
				<dl>
					<dt>
						<label<?php if(isset($list['id'])){echo(' for="'.$list['id'].'"');} ?>>
							<?php echo($list['title']); ?>
						</label>
					</dt>
					<dd>
						<?php if(isset($list['value'])){echo($list['value']);} ?>
						<?php if(isset($list['note'])){echo('<div class="note">'.$list['note'].'</div>');} ?>
					</dd>
				</dl>
			</li>
			<?php endforeach; ?>
		</ul>
		<input type="hidden" name="setting_db" value="<?php echo(_ON); ?>" />
		<div class="btn-bottom align-right clear">
			<input type="button" value="<?php echo(__('&lt;&lt;Back')); ?>" name="back" class="btn" onclick="location.href='<?php echo $this->Html->url(array('plugin' => 'install','controller' => 'install','action' => 'check')); ?>';" />
			<input type="submit" value="<?php echo(__('Next&gt;&gt;')); ?>" name="next" class="btn" />
		</div>
	</form>
</div>