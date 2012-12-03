<?php
	$is_top = false;
	$is_chief = false;
	$is_chgseq = false;
	$is_edit = false;
	$is_edit_detail = false;
	$is_add = false;
	$is_delete = false;
	$is_sel_modules = false;
	$is_sel_users = false;
	if($menu['display_sequence'] == 1 || !isset($is_display) || !$is_display) {
		$is_display = false;
	}

	if($menu['hierarchy'] >= NC_AUTH_MIN_CHIEF){
		$is_chief = true;
		$is_add = true;
	}
	if($menu['display_sequence'] == 1 && $menu['space_type'] == NC_SPACE_TYPE_GROUP) {
		// コミュニティのトップページの編集は許さない
		$is_chief = false;
	}
	if($menu['display_sequence'] != 1) {
		$is_edit_detail = true;
	}

	$attr = '';
	if(($menu['thread_num'] <= 1) ) {
		$is_top = true;
	}
	if($space_type != NC_SPACE_TYPE_GROUP && $menu['thread_num'] <= 1 && $menu['id'] != 0) {
		// コミュニティ以外のTopならば、移動させない。
		$attr = " data-dd-sequence = \"inner-only\"";
	} else {
		if($menu['display_sequence'] == 1) {
			$attr = " data-dd-sequence = \"bottom-only\"";
		}
		if($is_chief && $menu['display_sequence'] != 1) {
			$is_chgseq = true;
		}
	}

	if($is_chief && $menu['thread_num'] > 1) {
		$is_edit = true;
	}
	if($is_chief && !($space_type == NC_SPACE_TYPE_PUBLIC && $menu['thread_num'] == 1) && $menu['display_sequence'] != 1) {
		// TopNodeでもなく、各ノードのトップページでなければ。
		$is_display = true;
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
	echo $this->Form->create(null, array('url' => array('plugin' => 'page', 'controller' => 'page_menu', 'action' => 'edit'), 'id' => 'PagesMenuForm-'.$menu['id'], 'data-ajax-replace' => '#pages-menu-edit-item-'.h($menu['id'])));
	?>
	<input type="hidden" name="data[Page][id]" value="<?php echo(intval($menu['id'])); ?>" />
	<div class="dd-drag-content pages-menu-edit-content clearfix">
		<?php if($is_display): ?>
			<?php if($menu['display_flag'] == NC_DISPLAY_FLAG_ON): ?>
				<a class="pages-menu-display-flag" href="#" title="<?php echo(__d('page', 'To private')); ?>">
					<img class="icon" alt="<?php echo(__d('page', 'To private')); ?>" src="<?php echo($this->webroot); ?>/img/icons/base/on.gif" />
				</a>
			<?php else: ?>
				<a class="pages-menu-display-flag"  href="#" title="<?php echo(__d('page', 'To public')); ?>">
					<img class="icon" alt="<?php echo(__d('page', 'To public')); ?>" src="<?php echo($this->webroot); ?>/img/icons/base/off.gif" />
				</a>
			<?php endif; ?>
		<?php endif; ?>
		<input type="hidden" name="data[Page][display_flag]" value="<?php echo(intval($menu['display_flag'])); ?>" />

		<a class="pages-menu-edit-title" href="<?php echo($this->webroot); ?><?php echo($menu['permalink']); ?>" title="<?php echo(h($menu['page_name'])); ?>"<?php if($is_edit && $menu['id'] == $page_id && (!isset($is_detail) || $is_detail) && (!isset($is_error) || $is_error)): ?> style="display:none;"<?php endif; ?>>
			<?php echo(h($menu['page_name'])); ?>
		</a>
		<?php
		if($is_edit == true) {
			$settings = array(
				'id' => null,
				'class' => "pages-menu-edit-title text",
				'value' => $menu['page_name'] ,
				'label' => false,
				'div' => false,
			);
			if($menu['id'] != $page_id || (isset($is_detail) && !$is_detail) && (isset($is_error) && !$is_error)) {
				$settings['style'] = "display:none;";
			}
			if(isset($is_child)) {
				$settings['error'] = false;
			} else {
				$settings['error'] = array('attributes' => array(
					'popup' => true,
					'selector' => $this->Js->escape("$('[name=data\\[Page\\]\\[page_name\\]]', $('#PagesMenuForm-".$menu['id']."'))")
				));
			}
			echo $this->Form->input('Page.page_name', $settings);
		}
		?>
	</div>
	<?php if($is_edit): ?>
	<div id="pages-menu-edit-detail-<?php echo(h($menu['id'])); ?>" class="pages-menu-edit-view"<?php if(isset($is_child) || (!isset($is_detail) || !$is_detail)): ?> style="display:none;"<?php endif; ?>>
		<?php
			if((!isset($is_child) && isset($is_detail) && $is_detail)) {
				echo($this->element('index/detail', array('page' => $page, 'parent_page' => $parent_page)));
			}
		 ?>
	</div>
	<?php endif; ?>
	</form>
	<?php if(isset($pages) && !empty($pages[$space_type][$next_thread_num][$menu['id']])): ?>
		<ol class="dd-list">
			<?php echo($this->element('index/edit_page', array('pages' => $pages, 'menus' => $pages[$space_type][$next_thread_num][$menu['id']], 'page_id' => $page_id, 'space_type' => $space_type, 'admin_hierarchy' => $admin_hierarchy, 'is_child' => true, 'is_display' => $is_display))); ?>
		</ol>
	<?php endif; ?>
	<?php if($is_edit || $is_sel_modules || $is_sel_users || $is_delete): ?>
	<div class="pages-menu-edit-operation clearfix"<?php if($menu['id'] != $page_id): ?> style="display:none;"<?php endif; ?>>
	<?php
		if($is_edit_detail) {
			echo $this->Html->link('', array('plugin' => 'page', 'controller' => 'page_menu', 'action' => 'detail'),
				array('title' => __('Detail setting'), 'class' => 'pages-menu-edit-icon' . ' nc_tooltip',
				'data-ajax' => '#pages-menu-edit-detail-'.$menu['id'], 'data-page-edit-id' => $menu['id']));
		}
		echo $this->Html->link('', array('plugin' => 'page', 'controller' => 'page_menu', 'action' => 'operation'),
			array('title' => __d('page', 'Other operation'), 'class' => 'pages-menu-other-icon' . ' nc_tooltip',
			'data-ajax' => '#pages-menu-edit-view-'.$menu['id']));
		echo $this->Html->link('', array('plugin' => 'page', 'controller' => 'page_menu', 'action' => 'delete'),
			array('title' => __d('page', 'Delete page'), 'class' => 'pages-menu-delete-icon' . ' nc_tooltip',
			'data-ajax' => '#pages-menu-edit-view-'.$menu['id']));
	?>
	</div>
	<?php endif; ?>
</li>