<?php
$this->assign('title', __d('install', 'Setting Check'));
?>
<div class="install">
	<form method="post" action="<?php echo $this->Html->url(array('plugin' => 'install','controller' => 'install','action' => 'database')); ?>">
		<?php echo($this->element('hidden')); ?>
		<h1><?php echo $this->fetch('title'); ?></h1>
		<div class="top-description">
			<?php echo(__d('install', 'I checked a condition of the installation environment.<br />It is necessary to solve all the red items to advance next.')); ?>
		</div>
		<div>
			<?php
				$check = true;

				// tmp is writable
				if (is_writable(TMP)) {
					echo '<p class="success message">' . __d('install', '[%s] is writable.', 'app/tmp')  . '</p>';
					if (!is_writable(CACHE)) {
						$check = false;
						echo '<p class="error message">' . __d('install', '[%s] is NOT writable.', 'app/tmp/cache')  . '</p>';
					}
					if (!is_writable(LOGS)) {
						$check = false;
						echo '<p class="error message">' . __d('install', '[%s] is NOT writable.', 'app/tmp/logs')  . '</p>';
					}
				} else {
					$check = false;
					echo '<p class="error message">' . __d('install', '[%s] is NOT writable.', 'app/tmp')  . '</p>';
				}

				// uploads is writable
				if (is_writable(NC_UPLOADS_DIR)) {
					echo '<p class="success message">' . __d('install', '[%s] is writable.', 'uploads') . '</p>';
				} else {
					$check = false;
					echo '<p class="error message">' . __d('install', '[%s] is NOT writable.', 'uploads') . '</p>';
				}

				// install.inc.php is writable
				if (is_writable(APP . 'Config' . DS . NC_INSTALL_INC_FILE)) {
					echo '<p class="success message">' . __d('install', '[%s] is writable.', 'app/Config/install.inc.php') . '</p>';
				} else {
					$check = false;
					echo '<p class="error message">' . __d('install', '[%s] is NOT writable.', 'app/Config/install.inc.php') . '</p>';
				}

				// webroot/theme is writable
				if (is_writable(WWW_ROOT.'theme')) {
					echo '<p class="success message">' . __d('install', '[%s] is writable.', 'app/webroot/theme')  . '</p>';
					if (!is_writable(WWW_ROOT.'theme'.DS.'assets')) {
						$check = false;
						echo '<p class="error message">' . __d('install', '[%s] is NOT writable.', 'app/webroot/theme/assets')  . '</p>';
					}
					if (!is_writable(WWW_ROOT.'theme'.DS.'page_styles')) {
						$check = false;
						echo '<p class="error message">' . __d('install', '[%s] is NOT writable.', 'app/webroot/theme/page_styles')  . '</p>';
					}
				} else {
					$check = false;
					echo '<p class="error message">' . __d('install', '[%s] is NOT writable.', 'app/webroot/theme')  . '</p>';
				}

				//mimetex
				$mimetex_path = VENDORS . 'mimetex' . '/';
				if (substr(PHP_OS, 0, 3) == 'WIN') {
					$mimetex = "mimetex.exe";
				} else {
					$mimetex = "mimetex.cgi";
				}
				if (is_executable($mimetex_path. $mimetex)) {
					echo '<p class="success message">' . __d('install', '[%s] is executable.', 'vendors/'.$mimetex) . '</p>';
				} else {
					$check = false;
					echo '<p class="error message">' . __d('install', '[%s] is NOT executable.', 'vendors/'.$mimetex) . '</p>';
				}

				// mbstring
				if (extension_loaded('mbstring') && function_exists('mb_convert_encoding')) {
					echo '<p class="success message">' . __d('install', 'function %s exists.', 'mb_convert_encoding()') . '</p>';
				} elseif (function_exists("mb_detect_order")) {
					$check = false;
					echo '<p class="error message">' . __d('install', 'Call to undefined function %s.', 'mb_convert_encoding()') . '</p>';
				}

				// 画像処理関数
				if (extension_loaded('imagick')) {
					echo '<p class="success message">' . __d('install', 'function %s exists.', 'Imagick') . '</p>';
				} elseif (extension_loaded('gd')) {
					echo '<p class="success message">' . __d('install', 'function %s exists.', 'GD') . '</p>';
				} else {
					$check = false;
					echo '<p class="error message">' . __d('install', 'Call to undefined function Imagick or GD.') . '</p>';
				}

				// php version
				if (version_compare(phpversion(), '5.2.8', '>=')) {
					echo '<p class="success message">' . __d('install', 'PHP version %s >= 5.2.8', phpversion()) . '</p>';
				} else {
					$check = false;
					echo '<p class="error message">' . __d('install', 'PHP version %s < 5.2.8', phpversion()) . '</p>';
				}
			?>
		</div>
		<div class="btn-bottom align-right">
			<input type="button" value="<?php echo(__d('install', 'Reload')); ?>" name="reload" class="btn" onclick="location.href='<?php echo $this->Html->url(array('plugin' => 'install','controller' => 'install','action' => 'check')); ?>';" />
			<input type="button" value="<?php echo(__('&lt;&lt;Back')); ?>" name="back" class="btn" onclick="history.back();" />
			<input type="submit" value="<?php echo(__('Next&gt;&gt;')); ?>" name="next" <?php if(!$check): ?>disabled="disabled" class="btn disabled"<?php else: ?>class="btn"<?php endif; ?>/>
		</div>
	</form>
</div>