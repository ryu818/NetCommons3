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
$page_menu = $this->Session->read(NC_SYSTEM_KEY.'.page_menu.action');
if(isset($page_menu)) {
	$action = "close";
	$sub_action = "index";
} else {
	$action = "index";
	$sub_action = "close";
}
?>
<div id="nc-hmenu" class="nc-panel-color"<?php if($nc_mode == NC_BLOCK_MODE){ echo(' style="top:0;"'); } ?>>
	<div id="nc-hmenu-l" class="nc-panel-color">
		<ul class="nc-hmenu-ul ">
			<li class="nc-hmenu-li nc-hmenu-logo-li">
				<a class="nc-hmenu-menu-a" title="NetCommons" href="<?php echo($this->Html->url('/')); ?>">
					<span class="nc-hmenu-logo"></span>
				</a>
			</li>
			<li class="nc-hmenu-li">
				<div id="nc-pages-menu-path">
					<span><?php echo trim($this->element('Pages/breadcrumb', array('pages_list' => $pages_list)),"\n\t "); ?></span>
				</div>
			</li>
			<li class="nc-hmenu-li">
				<?php echo $this->Html->link(__('Pages settings'), array('plugin' => 'page', 'controller' => 'page', 'action' => $action), array('id' => 'nc-pages-setting', 'class' => 'nc-hmenu-menu-a', 'aria-haspopup' => 'true', 'data-page-setting-url' => $this->Html->url(array('plugin' => 'page',  'controller' => 'page', 'action' => $sub_action)))); ?>
			</li>
		</ul>
	</div>
	<div id="nc-hmenu-r" class="nc-panel-color">
		<ul class="nc-hmenu-ul">
			<li class="nc-hmenu-li">
				<a class="nc-tooltip nc-hmenu-menu-a" title="<?php echo(__('To the Site Management screen.')); ?>" href="#">
					<?php echo(__('Admin menu')); ?>
				</a>
			</li>
			<li class="nc-hmenu-li">
				<?php if(empty($nc_user['id'])): ?>
					<?php echo $this->Html->link(__('Login'), array('controller' => 'users', 'action' => 'login'), array('id' => 'nc-login', 'class' => 'nc-hmenu-menu-a', 'aria-haspopup' => 'true')); ?>
				<?php else: ?>
					<?php echo $this->Html->link(__('Logout'), array('controller' => 'users', 'action' => 'logout'), array('class' => 'nc-hmenu-menu-a')); ?>
				<?php endif; ?>
			</li>
			<?php if(!empty($nc_user['id']) && $hierarchy >= NC_AUTH_MIN_CHIEF): ?>
			<li class="nc-hmenu-li nc-hmenu-setting-m">
				<a class="nc-tooltip nc-hmenu-menu-a" title="<?php echo(h($setting)); ?>" data-tooltip-desc="<?php echo(h($tooltip_setting)); ?>" href="<?php echo(rtrim($this->Html->url(), '/').'/?setting_mode='.$setting_mode); ?>">
					<span class="<?php echo($setting_class); ?>"></span>
				</a>
			</li>
			<?php endif; ?>
		</ul>
	</div>
	<div class="nc-hmenu-arrow">
		<a id="nc-hmenu-arrow" class="nc-arrow<?php if($nc_mode == NC_BLOCK_MODE){ echo(' nc-arrow-up'); } ?>" href="#"></a>
	</div>
</div>
<?php
$show_page_setting = false;
$params = array('plugin' => 'page', 'controller' => 'page', 'action' => 'index');
$options = array('return');
if(isset($page_menu)) {
	$show_page_setting = true;
	$params['action'] = $page_menu;

}
if(!empty($this->params['active_plugin']) && $this->params['active_plugin'] == 'page' && $this->params['active_action'] == 'index') {
	$show_page_setting = true;
	$options['query'] = $this->params->query;
	//$options['url'] = $this->params->query;
	//$params['?'] = $this->params->query;
	$options['named'] = $this->params->named;
	$options['pass'] = $this->params->pass;

	if ($this->params->is('post')) {
		$params['data'] = $this->params->data;
	}
	if(!empty($this->params['active_controller'])) {
		$params['controller'] = $this->params['active_controller'];
	}
	if(!empty($this->params['active_action'])) {
		$params['action'] = $this->params['active_action'];
	}
	$params['block_type'] = 'active-blocks';
}

if($show_page_setting) {
	$c = $this->requestAction($params, $options);	// $this->Html->url($params,true)
	echo('<div id="nc-pages-setting-dialog-outer">'.$c.'</div>');
}
?>