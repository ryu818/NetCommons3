<?php
/**
 * ヘッダーメニュー画面
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       View
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
$ncUser = $this->Session->read(NC_AUTH_KEY.'.'.'User');
$ncMode = $this->Session->read(NC_SYSTEM_KEY.'.'.'mode');
if($ncMode == NC_BLOCK_MODE){
	$setting = __d('pages', 'Setting mode off');
	$tooltipSetting = __d('pages', 'I exit edit mode block.');
	$settingClass = 'nc-hmenu-setting-end-btn';
	$settingMode = NC_GENERAL_MODE;
} else {
	$setting = __d('pages', 'Setting mode on');
	$tooltipSetting = __d('pages', 'I move to block editing mode. You can add a block, edit, delete, and resize.');
	$settingClass = 'nc-hmenu-setting-btn';
	$settingMode = NC_BLOCK_MODE;
}
// ページ設定
$pageMenu = $this->Session->read(NC_SYSTEM_KEY.'.page_menu.action');
if(isset($pageMenu)) {
	$action = "close";
	$subAction = "index";
} else {
	$action = "index";
	$subAction = "close";
}
$isControls = ($this->request->controller == 'controls') ? true : false;
$canShowControls = (isset($ncUser)) ? true : false;
$displayHeaderMenu = Configure::read(NC_CONFIG_KEY.'.'.'display_header_menu');
$canShowPageSetting = false;
$showPageSetting = false;

if(!$isControls && (isset($ncUser) || Configure::read(NC_CONFIG_KEY.'.'.'display_page_menu') != _OFF)) {
	$canShowPageSetting = true;
}

$params = array('plugin' => 'page', 'controller' => 'page', 'action' => 'index', 'block_id' => 0);
$options = array('return', 'requested' => _OFF);	// Tokenがrequested=1の場合、セットされないため1をセット
if($canShowPageSetting && isset($pageMenu)) {
	$showPageSetting = true;
	if(isset($ncUser)) {
		$params['action'] = $pageMenu;
	}
}
if($canShowPageSetting && !empty($this->params['active_plugin']) && $this->params['active_plugin'] == 'page' && $this->params['active_action'] == 'index') {
	$showPageSetting = true;
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


?>
<div id="nc-hmenu" class="nc-panel-color"<?php if($isControls || $ncMode == NC_BLOCK_MODE || $displayHeaderMenu == NC_HEADER_MENU_ALWAYS){ echo(' style="top:0;"'); } ?><?php if(isset($ncUser)): ?> data-user-id="<?php echo $ncUser['id']; ?>"<?php endif; ?>>
	<div id="nc-hmenu-l" class="nc-panel-color">
		<ul class="nc-hmenu-ul">
			<li class="nc-hmenu-li nc-hmenu-logo-li">
				<a class="nc-hmenu-menu-a" title="NetCommons" href="<?php echo($this->Html->url('/')); ?>">
					<span class="nc-hmenu-logo"></span>
				</a>
			</li>
			<?php if($canShowPageSetting): ?>
			<li class="nc-hmenu-li">
				<?php echo $this->Html->link(__('Pages settings'), array('plugin' => 'page', 'controller' => 'page', 'action' => $action, 'block_id' => 0), array('id' => 'nc-pages-setting', 'class' => 'nc-hmenu-menu-a', 'aria-haspopup' => 'true', 'data-page-setting-url' => $this->Html->url(array('plugin' => 'page',  'controller' => 'page', 'action' => $subAction)))); ?>
			</li>
			<li class="nc-hmenu-li">
				<div id="nc-pages-menu-path">
					<span><?php echo trim($this->element('Pages/breadcrumb', array('pages_list' => $pages_list)),"\n\t "); ?></span>
				</div>
			</li>
			<?php endif; ?>
		</ul>
	</div>
	<div id="nc-hmenu-r" class="nc-panel-color">
		<ul class="nc-hmenu-ul">
			<?php if($canShowControls): ?>
			<li class="nc-hmenu-li">
				<?php /* TODO:リンク先が未作成 */  ?>
				<?php
					echo $this->Html->link($ncUser['handle'], '#',
					array('class' => 'nc-tooltip nc-hmenu-menu-a', 'title' => __('To the Member information screen.')));
				?>
			</li>
			<li class="nc-hmenu-li">
				<?php /* コントロールパネル */ ?>
				<?php if(!$isControls): ?>
					<?php echo $this->Html->link(__('Admin menu'), array('controller' => 'controls', 'action' => 'index'), array('class' => 'nc-tooltip nc-hmenu-menu-a', 'title' => __('To the Site Management screen.'))); ?>
				<?php else: ?>
					<?php echo $this->Html->link(__('End admin menu'),  $referer, array('class' => 'nc-hmenu-menu-a', 'title' => __('End admin menu'))); ?>
				<?php endif; ?>
			</li>
			<?php endif; ?>
			<li class="nc-hmenu-li">
				<?php /*言語切替 */ ?>
				<select id="nc-languages" class="language">
				<?php
					$lang = Configure::read(NC_CONFIG_KEY.'.'.'language');
					$languages = Configure::read(NC_CONFIG_KEY.'.'.'languages');
					foreach($languages as $key => $value) {
						$selected = ($key == $lang) ? ' selected="selected"' : '';
						echo("<option value=\"".$this->Html->url(array('?' => array('lang' => $key)))."\"".$selected.">".h(__($value))."</option>\n");
					}
				?>
				</select>
				<script>
				$(function(){
					$("#nc-languages").select2({
						minimumResultsForSearch:-1,
						width: 'element'
					}).change( function(e){
						location.href =  $(this).val();
					});
				});
				</script>
			</li>
			<li class="nc-hmenu-li">
				<?php if(empty($ncUser['id'])): ?>
					<?php
						$loginUrl = array('controller' => 'users', 'action' => 'login');
						if (Configure::read(NC_CONFIG_KEY.'.'.'use_ssl') != NC_USE_SSL_NO_USE) {
							echo $this->Html->link(__('Sign in'), $loginUrl, array('id' => 'nc-login-ssl', 'class' => 'nc-hmenu-menu-a'));
						} else {
							echo $this->Html->link(__('Sign in'), $loginUrl, array('id' => 'nc-login', 'class' => 'nc-hmenu-menu-a', 'aria-haspopup' => 'true'));
							$loginStr = $this->requestAction(array_merge($loginUrl, array('popup' => _ON)), array('return'));
							echo '<div id="nc-login-popup" style="display:none;">'.$loginStr.'</div>';
						}
					?>
					<script>
					$(function(){
						$("#nc-login-popup").dialog({
							title:'<?php echo __('Sign in'); ?>',
							autoOpen: false
						});
					});
					</script>
				<?php else: ?>
					<?php echo $this->Html->link(__('Sign out'), array('controller' => 'users', 'action' => 'logout'), array('class' => 'nc-hmenu-menu-a')); ?>
				<?php endif; ?>
			</li>
			<?php if(!$isControls && !empty($ncUser['id']) && $hierarchy >= NC_AUTH_MIN_CHIEF): ?>
			<li class="nc-hmenu-li nc-hmenu-setting-m">
				<a class="nc-tooltip nc-hmenu-menu-a" title="<?php echo(h($setting)); ?>" data-tooltip-desc="<?php echo(h($tooltipSetting)); ?>" href="#" onclick="location.href= $._current_url + '?setting_mode=<?php echo($settingMode); ?>'; return false;">
					<span class="<?php echo($settingClass); ?>"></span>
				</a>
			</li>
			<?php endif; ?>
		</ul>
	</div>
	<?php if(!$isControls): ?>
	<div class="nc-hmenu-arrow">
		<a id="nc-hmenu-arrow" class="nc-arrow<?php if($ncMode == NC_BLOCK_MODE || $displayHeaderMenu == NC_HEADER_MENU_ALWAYS){ echo(' nc-arrow-up'); } ?>" href="#"></a>
	</div>
	<?php endif; ?>
</div>
<?php
if($showPageSetting) {
	$c = $this->requestAction($params, $options);	// $this->Html->url($params,true)
	echo('<div id="nc-pages-setting-dialog-outer">'.$c.'</div>');
}
?>