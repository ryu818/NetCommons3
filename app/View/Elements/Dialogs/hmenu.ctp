<?php
$nc_user = $this->Session->read(NC_AUTH_KEY.'.'.'User');
$nc_mode = $this->Session->read(NC_SYSTEM_KEY.'.'.'mode');
if($nc_mode == NC_BLOCK_MODE){
	$setting = __d('pages', 'Setting mode off');
	$tooltip_setting = __d('pages', 'I exit edit mode block.');
	$setting_class = 'nc-hmenu-setting-end-btn';
	$setting_mode = NC_GENERAL_MODE;
} else {
	$setting = __d('pages', 'Setting mode on');
	$tooltip_setting = __d('pages', 'I move to block editing mode. You can add a block, edit, delete, and resize.');
	$setting_class = 'nc-hmenu-setting-btn';
	$setting_mode = NC_BLOCK_MODE;
}
?>
<div id="nc-hmenu" class="nc-panel-color">
	<div id="nc-hmenu-l" class="nc-panel-color">
		<ul class="nc-hmenu-ul ">
			<li class="nc-hmenu-li nc-hmenu-logo-li">
				<a title="NetCommons" href="<?php echo($this->Html->url('/')); ?>">
					<span class="nc-hmenu-logo"></span>
				</a>
			</li>
			<li class="nc-hmenu-li">
				<?php /* TODO:未作成 パンくずリスト */ ?>
				<div class="nc-pages-menu-path">
						ホーム(TODO:test) > ページ1 > ページ3 > ページ4 > ページ5
				</div>
			</li>
			<li class="nc-hmenu-li">
				<?php echo $this->Html->link(__('Pages setting'), array('plugin' => 'page', 'controller' => 'page'), array('id' => 'nc-pages-setting', 'aria-haspopup' => 'true')); ?>
			</li>
		</ul>
	</div>
	<div id="nc-hmenu-r" class="nc-panel-color">
		<ul class="nc-hmenu-ul">
			<li class="nc-hmenu-li">
				<a class="nc-tooltip" title="<?php echo(__('To the Site Management screen.')); ?>" href="#">
					<?php echo(__('Admin menu')); ?>
				</a>
			</li>
			<li class="nc-hmenu-li">
				<?php if(empty($nc_user['id'])): ?>
					<?php echo $this->Html->link(__('Login'), array('controller' => 'users', 'action' => 'login'), array('id' => 'nc-login', 'aria-haspopup' => 'true')); ?>
				<?php else: ?>
					<?php echo $this->Html->link(__('Logout'), array('controller' => 'users', 'action' => 'logout')); ?>
				<?php endif; ?>
			</li>
			<?php if(!empty($nc_user['id']) && $hierarchy >= NC_AUTH_MIN_CHIEF): ?>
			<li class="nc-hmenu-li nc-hmenu-setting-m">
				<a class="nc-tooltip" title="<?php echo(h($setting)); ?>" data-tooltip-desc="<?php echo(h($tooltip_setting)); ?>" href="<?php echo(rtrim($this->Html->url(), '/').'/?setting_mode='.$setting_mode); ?>">
					<span class="<?php echo($setting_class); ?>"></span>
				</a>
			</li>
			<?php endif; ?>
		</ul>
	</div>
	<div class="nc-hmenu-arrow">
		<a id="nc-hmenu-arrow" class="nc-arrow" href="#"></a>
	</div>
</div>
