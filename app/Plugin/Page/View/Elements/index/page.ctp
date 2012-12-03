<?php if(!empty($menus)): ?>
	<?php foreach ($menus as $menu): ?>
		<?php
			if($menu['display_flag'] != NC_DISPLAY_FLAG_ON && $menu['hierarchy'] < NC_AUTH_MIN_CHIEF) {
				// 非公開
				continue;
			}

		?>
		<?php $class = $this->element('index/init_page', array('menu' => $menu, 'is_edit' => _OFF)); ?>
		<?php $next_thread_num = $menu['thread_num']+1; ?>
		<li class="dd-item" data-id="<?php echo(h($menu['id'])); ?>">
			<div class="pages-menu-handle <?php echo($class); ?><?php if($menu['id'] == $page_id): ?> highlight<?php endif; ?>">
			<a href="<?php echo($this->webroot); ?><?php echo($menu['permalink']); ?>" title="<?php echo(h($menu['page_name'])); ?>">
				<?php echo(h($menu['page_name'])); ?>
			</a>
			<?php if($menu['display_flag'] == NC_DISPLAY_FLAG_OFF): ?>
				<span class="pages-menu-nonpublic"><?php echo(__d('page', '(Private)')); ?></span>
			<?php endif; ?>
			</div>
			<?php if(!empty($pages[$space_type][$next_thread_num][$menu['id']])): ?>
				<ol class="dd-list">
					<?php echo($this->element('index/page', array('pages' => $pages, 'menus' => $pages[$space_type][$next_thread_num][$menu['id']], 'page_id' => $page_id, 'space_type' => $space_type))); ?>
				</ol>
			<?php endif; ?>
		</li>
	<?php endforeach; ?>
<?php endif; ?>