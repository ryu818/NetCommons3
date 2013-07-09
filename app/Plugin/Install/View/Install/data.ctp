<?php
$this->assign('title', __d('install', 'Create Tables'));
?>
<div class="install">
	<form method="post" action="<?php echo $this->Html->url(array('plugin' => 'install','controller' => 'install','action' => 'admin_setting')); ?>">
		<?php echo($this->element('hidden')); ?>
		<h1><?php echo $this->fetch('title'); ?></h1>
		<div>
			<?php
				$check = true;

				if(isset($failure_datas) && $failure_datas) {
					$check = false;
					$failure_sql = (isset($failure_sql)) ? '<br />'. h($failure_sql) : '';
					echo '<p class="error message-table">' . __d('install', 'Could not add data of database tables.') . $failure_sql . '</p>';
				}

				if(isset($not_tables) && count($not_tables) > 0) {
					foreach($not_tables as $not_table) {
						//$check = false; // 既に作成済かもなので、エラーとはしない。
						echo '<p class="error message-table">' . __d('install', 'Could not create database %s.', h($not_table)) . '</p>';
					}
				}

				if(isset($tables) && count($tables) > 0) {
					foreach($tables as $table) {
						echo '<p class="success message-table">' . __d('install', 'Database %s created!', h($table)) . '</p>';
					}
				}
				if(isset($failure_install_ini)) {
					if(!$failure_install_ini) {
						echo '<p class="success message-table">' . __d('install', 'install.inc.php file is writing!') . '</p>';
					} else {
						$check = false;
						echo '<p class="error message-table">' . __d('install', 'Could not write install.inc.php file.') . '</p>';
					}
				}

			?>
		</div>

		<div class="btn-bottom align-right">
			<input type="submit" value="<?php echo(__('Next&gt;&gt;')); ?>" name="next" <?php if(!$check || !$exists_database): ?>disabled="disabled" class="btn disabled"<?php else: ?>class="btn"<?php endif; ?>/>
		</div>
	</form>
</div>