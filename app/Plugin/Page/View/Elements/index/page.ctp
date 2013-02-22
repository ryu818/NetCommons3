<?php if(!empty($menus)): ?>
	<?php foreach ($menus as $page): ?>
		<?php
			$active_lang = $this->Session->read(NC_SYSTEM_KEY.'.page_menu.pre_lang');
			$lang = Configure::read(NC_CONFIG_KEY.'.'.'language');
			$parameter = '';
			if(isset($active_lang) && $active_lang != $lang) {
				$parameter = '?lang='.$lang;
			}
		?>
		<?php $class = $this->element('index/init_page', array('page' => $page, 'is_edit' => _OFF)); ?>
		<?php $next_thread_num = $page['Page']['thread_num']+1; ?>
		<li class="dd-item" data-id="<?php echo(h($page['Page']['id'])); ?>">
			<div class="pages-menu-handle <?php echo($class); ?><?php if($page['Page']['id'] == $page_id): ?> highlight<?php endif; ?><?php if($page['Page']['thread_num'] == 1): ?> pages-menu-handle-top<?php endif; ?>">
			<a href="<?php echo($this->webroot); ?><?php echo($page['Page']['permalink'].$parameter); ?>" title="<?php echo(h($page['Page']['page_name'])); ?>">
				<?php echo(h($page['Page']['page_name'])); ?>
			</a>
			<?php if($page['Page']['display_flag'] == NC_DISPLAY_FLAG_OFF): ?>
				<span class="pages-menu-nonpublic"><?php echo(__d('page', '(Private)')); ?></span>
			<?php endif; ?>
			</div>
			<?php if(!empty($pages[$space_type][$next_thread_num][$page['Page']['id']])): ?>
				<ol class="dd-list">
					<?php echo($this->element('index/page', array('pages' => $pages, 'menus' => $pages[$space_type][$next_thread_num][$page['Page']['id']], 'page_id' => $page_id, 'space_type' => $space_type))); ?>
				</ol>
			<?php endif; ?>
		</li>
	<?php endforeach; ?>
<?php endif; ?>