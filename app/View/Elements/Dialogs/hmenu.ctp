<?php
$nc_user = $this->Session->read(NC_AUTH_KEY.'.'.'User');
$nc_mode = $this->Session->read(NC_SYSTEM_KEY.'.'.'mode');
if($nc_mode == NC_BLOCK_MODE){
	$setting = __d('pages', 'Setting mode off');
	$setting_class = 'nc_hmenu_setting_end_btn';
	$setting_mode = NC_GENERAL_MODE;
} else {
	$setting = __d('pages', 'Setting mode on');
	$setting_class = 'nc_hmenu_setting_btn';
	$setting_mode = NC_BLOCK_MODE;
}
?>
<div id="nc_hmenu">
	<div id="nc_hmenu_l">
		<ul class="nc_hmenu_ul">
			<li class="nc_hmenu_li nc_hmenu_logo_li">
				<a title="NetCommons" href="<?php echo($this->Html->url()); ?>">
					<span class="nc_hmenu_logo"></span>
				</a>

			</li>
		</ul>
	</div>
	<div id="nc_hmenu_r">
		<ul class="nc_hmenu_ul">
			<li class="nc_hmenu_li">
				<?php if(empty($nc_user['id'])): ?>
					<?php echo $this->Html->link(__('Login'), '/users/login', array('id' => 'nc_login', 'aria-haspopup' => 'true')); ?>
				<?php else: ?>
					<?php echo $this->Html->link(__('Logout'), '/users/logout'); ?>
				<?php endif; ?>
			</li>
			<?php if(!empty($nc_user['id']) && $hierarchy >= NC_AUTH_MIN_CHIEF): ?>
			<li class="nc_hmenu_li nc_hmenu_setting_m">
				<a title="<?php echo($setting); ?>" href="<?php echo(rtrim($this->Html->url(), '/').'/?setting_mode='.$setting_mode); ?>">
					<span class="<?php echo($setting_class); ?>"></span>
				</a>
			</li>
			<?php endif; ?>
		</ul>
	</div>
	<div class="nc_hmenu_arrow">
		<a id="nc_hmenu_arrow" class="nc_arrow" href="#"></a>
	</div>
</div>