<?php
$this->assign('title', __d('install', 'Installation Complete'));
?>
<div class="install">
	<form method="post" action="<?php echo($this->webroot); ?>">
		<h1><?php echo $this->fetch('title'); ?></h1>
		<div class="nc-top-description">
			<?php echo(__d('install', "<u><b>Your site</b></u><p>Click <a href='%s'>HERE</a> to see the home page of your site.</p><u><b>Way to use</b></u><p>[not yet]</p><u><b>Support</b></u><p>Visit <a href='http://www.netcommons.org/' target='_blank'>NetCommons.org</a></p>", $this->webroot)); ?>
		</div>
		<div class="nc-btn-bottom align-right">
			<input type="button" value="<?php echo(__d('install', 'Top')); ?>" name="back" class="btn" onclick="location.href='<?php echo($this->webroot); ?>';" />
		</div>
	</form>
</div>