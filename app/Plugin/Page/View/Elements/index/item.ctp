<?php
	$is_top = false;
	$is_chief = false;
	$is_chgseq = false;
	$is_edit = false;
	$is_add = false;
	$is_delete = false;
	$is_sel_modules = false;
	$is_sel_users = false;

	if($menu['hierarchy'] >= NC_AUTH_MIN_CHIEF){
		$is_chief = true;
		$is_add = true;
	}
	if($menu['display_sequence'] == 1 && $menu['space_type'] == NC_SPACE_TYPE_GROUP) {
		// コミュニティのトップページの編集は許さない
		$is_chief = false;
	}

	$attr = '';
	if(($menu['thread_num'] <= 1 && $menu['id'] != 0) ) {
		$is_top = true;
	}
	if($space_type != NC_SPACE_TYPE_GROUP && $menu['thread_num'] <= 1 && $menu['id'] != 0) {
		// コミュニティ以外のTopならば、移動させない。
		$attr = " data-dd-sequence = \"inner-only\"";
	} else {
		$is_chgseq = true;
	}

	if($is_chief || $menu['id'] == 0) {
		$is_edit = true;
	}
?>
<?php $class = $this->element('index/init_page', array('menu' => $menu, 'is_edit' => _ON)); ?>
<?php $next_thread_num = $menu['thread_num']+1; ?>
<li id="pages-menu-edit-item-<?php echo(h($menu['id'])); ?>" class="dd-item dd-drag-item<?php if($menu['thread_num']==1){echo(' '.$class);} ?>" data-id="<?php echo(h($menu['id'])); ?>"<?php echo($attr); ?>>
	<?php if($is_chgseq): ?>
	<div class="dd-handle dd-drag-handle"></div>
	<?php elseif($is_top): ?>
	<div class="pages-menu-top <?php echo($class); ?>-top"></div>
	<?php endif; ?>
	<?php
	// TODO:name属性をふよすること。
	echo $this->Form->create(null, array('data-ajax-replace' => '#pages-menu-edit-item-'.h($menu['id'])));
	?>
	<div class="dd-drag-content pages-menu-edit-content clearfix">

		<?php if($menu['display_flag'] == NC_DISPLAY_FLAG_ON): ?>
			<a class="pages-menu-display-flag" href="#" title="<?php echo(__d('page', 'To private')); ?>">
				<img class="icon" alt="<?php echo(__d('page', 'To private')); ?>" src="<?php echo($this->webroot); ?>/img/icons/base/on.gif" />
			</a>
		<?php else: ?>
			<a class="pages-menu-display-flag"  href="#" title="<?php echo(__d('page', 'To public')); ?>">
				<img class="icon" alt="<?php echo(__d('page', 'To public')); ?>" src="<?php echo($this->webroot); ?>/img/icons/base/off.gif" />
			</a>
		<?php endif; ?>

		<a class="pages-menu-edit-title" href="<?php echo($this->webroot); ?><?php echo($menu['permalink']); ?>" title="<?php echo(h($menu['page_name'])); ?>"<?php if($menu['id'] == $page_id): ?> style="display:none;"<?php endif; ?>>
			<?php echo(h($menu['page_name'])); ?>
		</a>
		<input class="pages-menu-edit-title text" type="text" name="data[Page][page_name]" value="<?php echo(h($menu['page_name'])); ?>" <?php if($menu['id'] != $page_id): ?> style="display:none;"<?php endif; ?>/>
	</div>
	<?php if(1!= 0 && $is_edit): ?>
	<div id="pages-menu-edit-detail-<?php echo(h($menu['id'])); ?>" class="pages-menu-edit-view" style="display:none;">
	</div>
	<?php endif; ?>
	</form>
	<?php if(isset($pages) && !empty($pages[$space_type][$root_sequence][$next_thread_num][$menu['id']])): ?>
		<ol class="dd-list">
			<?php echo($this->element('index/edit_page', array('pages' => $pages, 'menus' => $pages[$space_type][$root_sequence][$next_thread_num][$menu['id']], 'page_id' => $page_id, 'space_type' => $space_type, 'root_sequence' => $root_sequence, 'admin_hierarchy' => $admin_hierarchy))); ?>
		</ol>
	<?php endif; ?>
	<?php if($is_edit || $is_sel_modules || $is_sel_users || $is_delete): ?>
	<div class="pages-menu-edit-operation clearfix"<?php if($menu['id'] != $page_id): ?> style="display:none;"<?php endif; ?>>
	<?php
		echo $this->Html->link('', array('plugin' => 'page', 'controller' => 'page_menu', 'action' => 'detail'),
			array('title' => __('Detail setting'), 'class' => 'pages-menu-edit-icon' . ' nc_tooltip',
			'data-ajax' => '#pages-menu-edit-detail-'.$menu['id'], 'data-page-edit-id' => $menu['id']));

		echo $this->Html->link('', array('plugin' => 'page', 'controller' => 'page_menu', 'action' => 'delete'),
			array('title' => __d('page', 'Delete page'), 'class' => 'pages-menu-delete-icon' . ' nc_tooltip',
			'data-ajax' => '#pages-menu-edit-view-'.$menu['id']));
		echo $this->Html->link('', array('plugin' => 'page', 'controller' => 'page_menu', 'action' => 'delete'),
			array('title' => __d('page', 'Other operation'), 'class' => 'pages-menu-other-icon' . ' nc_tooltip',
			'data-ajax' => '#pages-menu-edit-view-'.$menu['id']));
	?>
	</div>
	<?php endif; ?>
</li>