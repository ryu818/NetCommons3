<!DOCTYPE html>
<html>
<head>
	<title><?php echo(__d('install', 'Install')); ?> - <?php echo $this->fetch('title'); ?> - <?php echo(__d('install', 'NetCommons')); ?></title>
	<?php echo $this->Html->charset(); ?>
	<?php
		echo $this->Html->css(array(
			'common/vendors/reset',
			'common/editable/common',
			'common/main/common',
			'/install/css/install',
		));
		echo $scripts_for_layout;
	?>
	<?php
		echo $this->Html->script(array(
			'jquery/jquery',
		));
		echo $scripts_for_layout;
	?>
</head>
<body>
	<div id="container">
		<div id="header">
			<h1>
				<img alt="<?php echo(__d('install', 'NetCommons')); ?>" src="<?php echo $this->Html->assetUrl('img/install/logo.gif') ?>" />
			</h1>
		</div>
		<div id="install">
			<div class="main">
				<?php echo $this->Session->flash(); ?>
				<?php echo $content_for_layout; ?>
			</div>
		</div>
		<div id="footer">
			Powered by <?php echo(__d('install', 'NetCommons')); ?> <a href="http://www.netcommons.org">The NetCommons Project</a>
		</div>
	</div>
</body>
</html>
