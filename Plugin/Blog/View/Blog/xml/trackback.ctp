<?xml version="1.0" encoding="<?php echo Configure::read('App.encoding'); ?>" ?>
<response>
	<error><?php echo $error?></error>
	<?php if(!empty($error_message)):?>
		<message><?php echo $error_message ?></message>
	<?php endif;?>
</response>