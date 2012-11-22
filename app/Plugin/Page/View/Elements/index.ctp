<?php /* ページメニュー */ ?>
<?php
if(isset($is_edit) && $is_edit == _ON){
	$setting = __d('page', 'Exit page editor');
	$tooltip_setting = '';
	$setting_class = 'nc_hmenu_setting_end_btn';
} else {
	$setting = __d('page', 'Switching on page editor');
	$tooltip_setting = __d('page', 'I can add pages, community, edit, and delete.');
	$setting_class = 'nc_hmenu_setting_btn';
	$is_edit = _OFF;
}
?>
<div class="nc-pages-setting-title nc_popup_color clearfix" data-pages-header="true">
	<?php echo(__d('page', 'Pages menu')); ?>
	<a class="pages-menu-edit-btn nc_tooltip" title="<?php echo(h($setting)); ?>" data-powertip="<?php echo(h($tooltip_setting)); ?>" href="<?php echo($this->Html->url(array('plugin' => 'page', 'controller' => 'page', 'action' => 'index', '?' => array('is_edit' => !$is_edit)))); ?>" data-ajax-replace="#nc-pages-setting-dialog">
		<span class="<?php echo($setting_class); ?>"></span>
	</a>
</div>
<?php if($is_edit): ?>
<div class="nc-pages-setting-menu nc_popup_color" data-pages-header="true">
	<?php
	echo $this->Html->link(__d('page', 'Add page'), array('plugin' => 'page', 'controller' => 'page_menu', 'action' => 'add'),
		array('title' => __d('page', 'Add page'), 'id' => 'pages-menu-add-btn' ,
		'data-ajax-replace' => '#pages-menu-add-temp', 'data-ajax-type' => 'POST'));
	 ?>
</div>
<?php endif; ?>
<div id="pages-menu-tab">
	<ul data-pages-header="true">
		<li><a href="#pages-menu-page"><span><?php echo(__d('page', 'Page list'));?></span></a></li>
		<li><a href="#pages-menu-community"><span><?php echo(__d('page', 'Participation community'));?></span></a></li>
	</ul>

	<div id="pages-menu-page" class="nc-pages-setting-content">
		<?php if($is_edit): ?>
		<?php
			$thread_num = 1;
			$display_sequence = 0;
		?>
		<ol class="pages-menu-list dd-list">
			<?php if(!empty($pages[NC_SPACE_TYPE_PUBLIC])): ?>
				<?php $parent_id = 1; ?>
				<?php echo($this->element('index/edit_page', array('pages' => $pages, 'menus' => $pages[NC_SPACE_TYPE_PUBLIC][1][$thread_num][$parent_id], 'space_type' => NC_SPACE_TYPE_PUBLIC, 'page_id' => $page_id, 'root_sequence' => 1, 'admin_hierarchy' => $admin_hierarchy))); ?>
			<?php endif; ?>
			<?php if(!empty($pages[NC_SPACE_TYPE_MYPORTAL])): ?>
				<?php $parent_id = 2;?>
				<?php echo($this->element('index/edit_page', array('pages' => $pages, 'menus' => $pages[NC_SPACE_TYPE_MYPORTAL][1][$thread_num][$parent_id], 'space_type' => NC_SPACE_TYPE_MYPORTAL, 'page_id' => $page_id, 'root_sequence' => 1, 'admin_hierarchy' => $admin_hierarchy))); ?>
			<?php endif; ?>
			<?php if(!empty($pages[NC_SPACE_TYPE_PRIVATE])): ?>
				<?php $parent_id = 3;?>
				<?php echo($this->element('index/edit_page', array('pages' => $pages, 'menus' => $pages[NC_SPACE_TYPE_PRIVATE][1][$thread_num][$parent_id], 'space_type' => NC_SPACE_TYPE_PRIVATE, 'page_id' => $page_id, 'root_sequence' => 1, 'admin_hierarchy' => $admin_hierarchy))); ?>
			<?php endif; ?>
		</ol>
		<?php else: ?>
		<?php
			$thread_num = 2;
			$display_sequence = 1;
		?>
		<ol class="dd-list">
			<?php if(!empty($pages[NC_SPACE_TYPE_PUBLIC])): ?>
				<?php $parent_id = $pages[NC_SPACE_TYPE_PUBLIC][1][1][1][0]['id']; ?>
				<?php echo($this->element('index/page', array('pages' => $pages, 'menus' => $pages[NC_SPACE_TYPE_PUBLIC][1][$thread_num][$parent_id], 'space_type' => NC_SPACE_TYPE_PUBLIC, 'page_id' => $page_id, 'root_sequence' => 1))); ?>
			<?php endif; ?>
			<?php if(!empty($pages[NC_SPACE_TYPE_MYPORTAL])): ?>
				<?php $parent_id = $pages[NC_SPACE_TYPE_MYPORTAL][1][1][2][0]['id'];?>
				<?php echo($this->element('index/page', array('pages' => $pages, 'menus' => $pages[NC_SPACE_TYPE_MYPORTAL][1][$thread_num][$parent_id], 'space_type' => NC_SPACE_TYPE_MYPORTAL, 'page_id' => $page_id, 'root_sequence' => 1))); ?>
			<?php endif; ?>
			<?php if(!empty($pages[NC_SPACE_TYPE_PRIVATE])): ?>
				<?php $parent_id = $pages[NC_SPACE_TYPE_PRIVATE][1][1][3][0]['id'];?>
				<?php echo($this->element('index/page', array('pages' => $pages, 'menus' => $pages[NC_SPACE_TYPE_PRIVATE][1][$thread_num][$parent_id], 'space_type' => NC_SPACE_TYPE_PRIVATE, 'page_id' => $page_id, 'root_sequence' => 1))); ?>
			<?php endif; ?>
		</ol>
		<?php endif; ?>
	</div>
	<div id="pages-menu-community" class="nc-pages-setting-content">
	</div>
</div>
<?php
	echo $this->Html->css(array('plugins/jquery.nestable.css', 'Page.index/index.css'));
	echo $this->Html->script(array('plugins/jquery.nestable.js', 'Page.index/index.js'));
?>
<script>
	$('#pages-menu-tab').PageMenu(<?php echo($is_edit);?>, <?php echo($page_id);?>);
</script>