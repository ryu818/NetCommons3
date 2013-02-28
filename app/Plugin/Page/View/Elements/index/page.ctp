<?php if(!empty($menus)): ?>
	<?php foreach ($menus as $page): ?>
		<?php
			if($page['Page']['display_flag'] != NC_DISPLAY_FLAG_ON && $page['Authority']['hierarchy'] < NC_AUTH_MIN_CHIEF) {
				// 非公開
				continue;
			}
			$active_lang = $this->Session->read(NC_SYSTEM_KEY.'.page_menu.pre_lang');
			$lang = Configure::read(NC_CONFIG_KEY.'.'.'language');
			$parameter = '';
			if(isset($active_lang) && $active_lang != $lang) {
				$parameter = '?lang='.$lang;
			}
			$class = $this->element('index/init_page', array('page' => $page, 'is_edit' => _OFF));
			$next_thread_num = $page['Page']['thread_num']+1;

			if($page['Page']['display_flag'] == NC_DISPLAY_FLAG_OFF) {
				$class .= ' nonpublic';
			} else if(!empty($page['Page']['display_to_date']) && $page['Authority']['hierarchy'] >= NC_AUTH_MIN_CHIEF) {
    			$class .= ' to-nonpublic';
			}
			$tooltip_title = '';
			if($page['Authority']['hierarchy'] >= NC_AUTH_MIN_CHIEF) {
				$tooltip_title = $this->TimeZone->getPublishedLabel($page['Page']['display_from_date'], $page['Page']['display_to_date']);
				if($tooltip_title != '') {
					$tooltip_title = ' title="' . $tooltip_title . '"';
				}
			}
		?>
		<li class="dd-item<?php if($tooltip_title != ''): ?> nc-tooltip<?php endif; ?>" data-id="<?php echo(h($page['Page']['id'])); ?>"<?php echo($tooltip_title); ?>>
			<div class="pages-menu-handle <?php echo($class); ?><?php if($page['Page']['id'] == $page_id): ?> highlight<?php endif; ?><?php if($page['Page']['thread_num'] == 1): ?> pages-menu-handle-top<?php endif; ?>">
			<a href="<?php echo($this->webroot); ?><?php echo($page['Page']['permalink'].$parameter); ?>" title="<?php echo(h($page['Page']['page_name'])); ?>">
				<?php echo(h($page['Page']['page_name'])); ?>
			</a>
			<?php if($page['Page']['display_flag'] == NC_DISPLAY_FLAG_OFF): ?>
				<span class="nonpublic-lbl"><?php echo(__('(Private)')); ?></span>
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