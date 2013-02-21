<?php if(!empty($menus)): ?>
	<?php foreach ($menus as $page): ?>
		<?php echo($this->element('index/item', array('pages' => $pages, 'page' => $page, 'space_type' => $space_type,
			'page_id' => $page_id, 'admin_hierarchy' => $admin_hierarchy, 'is_child' => isset($is_child) ? $is_child : _OFF,
			'is_detail' => $is_detail, 'parent_page' => isset($parent_page) ? $parent_page : null,
			'community_params' => $community_params
		))); ?>
	<?php endforeach; ?>
<?php endif; ?>