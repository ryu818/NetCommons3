<?php if(!empty($menus)): ?>
	<?php foreach ($menus as $menu): ?>
		<?php
			if($menu['display_flag'] != NC_DISPLAY_FLAG_ON && $menu['hierarchy'] < NC_AUTH_MIN_CHIEF) {
				// 非公開
				continue;
			}

			if($menu['space_type'] == NC_SPACE_TYPE_PUBLIC) {
				$class = 'nc_page_handle_public';
			} else if($menu['space_type'] == NC_SPACE_TYPE_MYPORTAL) {
				$class = 'nc_page_handle_myportal';
			} else if($menu['space_type'] == NC_SPACE_TYPE_PRIVATE) {
				$class = 'nc_page_handle_private';
			} else {
				$class = 'nc_page_handle_community';
			}
		?>
		<?php $next_thread_num = $menu['thread_num']+1; ?>
		<li class="dd-item">
			<div class="nc_page_handle <?php echo($class); ?><?php if(!empty($page_id) && $page_id == $menu['id']): ?> nc_page_handle_active<?php endif; ?>">
			<a href="<?php echo($this->webroot); ?><?php echo($menu['permalink']); ?>" title="<?php echo(h($menu['page_name'])); ?>">
				<?php echo(h($menu['page_name'])); ?>
			</a>
			<?php if($menu['display_flag'] == NC_DISPLAY_FLAG_OFF): ?>
				<span class="nc_page_nonpublic"><?php echo(__d('page', '(Private)')); ?></span>
			<?php endif; ?>
			</div>
			<?php if(!empty($pages[$space_type][$root_sequence][$next_thread_num][$menu['id']])): ?>
				<ol class="dd-list">
					<?php echo($this->element('page', array('pages' => $pages, 'menus' => $pages[$space_type][$root_sequence][$next_thread_num][$menu['id']], 'page_id' => $page_id, 'space_type' => $space_type, 'root_sequence' => $root_sequence))); ?>
				</ol>
			<?php endif; ?>
		</li>
	<?php endforeach; ?>
<?php endif; ?>