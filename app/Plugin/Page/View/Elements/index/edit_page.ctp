<?php if(!empty($menus)): ?>
	<?php foreach ($menus as $menu): ?>
		<?php
		if($menu['display_flag'] != NC_DISPLAY_FLAG_ON && $menu['hierarchy'] < NC_AUTH_MIN_CHIEF) {
			// 非公開
			continue;
		}
		?>
		<?php echo($this->element('index/item', array('pages' => $pages, 'menu' => $menu, 'space_type' => $space_type, 'page_id' => $page_id, 'root_sequence' => $root_sequence, 'admin_hierarchy' => $admin_hierarchy))); ?>
	<?php endforeach; ?>
<?php endif; ?>