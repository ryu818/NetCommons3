<?php
	$is_chief = false;
	$is_chgseq = false;
	$is_edit = false;
	$is_edit_detail = false;
	$is_delete = false;
	$is_sel_members = false;
	$is_sel_modules = false;
	$is_contents = false;
	$is_display = false;
	$is_node_top_page = false;
	$is_top = false;
	$is_parent_chief = false;
	$is_operate_chief = false;
	if($page['Page']['display_sequence'] == 1 && $page['Page']['thread_num'] == 2) {
		$is_node_top_page = true;
	}
	if(($page['Page']['thread_num'] <= 1) ) {
		$is_top = true;
	}

	if($admin_hierarchy >= NC_AUTH_MIN_GENERAL && ($page['PageAuthority']['hierarchy'] >= NC_AUTH_MIN_CHIEF || $admin_hierarchy >= NC_AUTH_MIN_ADMIN)){
		$is_chief = true;
	}

	if($is_top) {
		switch($space_type) {
			case NC_SPACE_TYPE_PUBLIC:
			case NC_SPACE_TYPE_MYPORTAL:
			case NC_SPACE_TYPE_PRIVATE:
				if($admin_hierarchy >= NC_AUTH_MIN_ADMIN) {
					$is_parent_chief = true;
				}
				break;
			case NC_SPACE_TYPE_GROUP:
				// コミュニティ
				if($admin_hierarchy >= NC_AUTH_MIN_CHIEF) {
					$is_parent_chief = true;
				}
				break;
		}
	} else if(isset($parent_page) && $admin_hierarchy >= NC_AUTH_MIN_GENERAL && ($parent_page['PageAuthority']['hierarchy'] >= NC_AUTH_MIN_CHIEF || $admin_hierarchy >= NC_AUTH_MIN_ADMIN)) {
		$is_parent_chief = true;
	}

	if(($is_top && $is_chief) || (!$is_top && $is_parent_chief)) {
		$is_operate_chief = true;
	}

	$attr = '';
	$move = array();
	if($is_top) {
		// コミュニティー以外のTopならば、移動させない。
		$move['inner'] = true;
		if($space_type == NC_SPACE_TYPE_GROUP && $admin_hierarchy >= NC_AUTH_MIN_CHIEF) {
			$is_chgseq = true;
			$attr .= " data-dd-group = \"".$page['Page']['thread_num']."\"";
			$attr .= " data-dd-group-sequence = \"top-bottom-only\"";
		}
	} else {
		if($is_node_top_page) {
			$move['bottom'] = true;
		} else {
			$move = array(
				'top' => true,
				'bottom' => true,
				'inner' => true
			);
		}
		if($is_chief && $page['Page']['display_sequence'] != 1) {
			$is_chgseq = true;
		}
	}
	if(!$is_chief) {
		unset($move['inner']);
	}
	if(!$is_parent_chief) {
		unset($move['top']);
		unset($move['bottom']);
	}
	if(count($move) == 0) {
		$attr .= " data-dd-sequence = \"none\"";
	} else if(isset($move['top']) && isset($move['bottom']) && !isset($move['inner'])) {
		$attr .= " data-dd-sequence = \"top-bottom-only\"";
	} else if(!isset($move['top']) && !isset($move['bottom']) && isset($move['inner'])) {
		$attr .= " data-dd-sequence = \"inner-only\"";
	} else if(!isset($move['top']) && isset($move['bottom']) && !isset($move['inner'])) {
		$attr .= " data-dd-sequence = \"bottom-only\"";
	}

	if($is_chief && (!$is_top || $space_type == NC_SPACE_TYPE_GROUP)) {
		$is_edit = true;
	}

	if($is_chief && (($is_top && $page['Page']['space_type'] == NC_SPACE_TYPE_GROUP) || !$is_node_top_page)) {
		// 主坦ならばTopNodeでもなく、各ノードのトップページでなければ公開設定を許す
		$is_display = true;
	}

	if($is_operate_chief) {
		if(!$is_top || $space_type == NC_SPACE_TYPE_GROUP) {
			$is_edit = true;
			$is_delete = true;
		}
		if(!$is_node_top_page && $space_type != NC_SPACE_TYPE_MYPORTAL && $space_type != NC_SPACE_TYPE_PRIVATE ) {
			$is_sel_members = true;
			//$is_sel_modules = true;
		}
	}
	if($is_edit && !$is_node_top_page) {
		$is_edit_detail = true;
	}

	$is_editing_page_name = false;
	if($page['Page']['id'] == $page_id && ($is_detail || (isset($error_flag) && $error_flag))) {
		$is_editing_page_name = true;
	}
	$class = $this->element('index/init_page', array('page' => $page, 'is_edit' => _ON));
	$next_thread_num = $page['Page']['thread_num']+1;

	$class_link = '';
	if($page['Page']['display_flag'] == NC_DISPLAY_FLAG_OFF) {
		$class_link .= ' nonpublic';
	} else if(!empty($page['Page']['display_to_date']) && $is_parent_chief) {
    	$class_link .= ' to-nonpublic';
	}
	$tooltip_title = '';
	if($page['PageAuthority']['hierarchy'] >= NC_AUTH_MIN_CHIEF) {
		$tooltip_title = $this->TimeZone->getPublishedLabel($page['Page']['display_from_date'], $page['Page']['display_to_date']);
		if($tooltip_title != '') {
			$tooltip_title = ' ' . $tooltip_title;
		}
	}
?>
<li id="pages-menu-edit-item-<?php echo(h($page['Page']['id'])); ?>" class="pages-menu-edit-item dd-item dd-drag-item<?php if($page['Page']['id']==$page['Page']['room_id']){echo(' '.$class);} ?>" data-id="<?php echo(h($page['Page']['id'])); ?>" data-room-id="<?php echo(h($page['Page']['room_id'])); ?>" data-is-top="<?php if($is_top){echo(_ON);}else{echo(_OFF);} ?>" data-space-type="<?php echo($page['Page']['space_type']); ?>" data-is-chief="<?php if($is_chief){echo(_ON);} else {echo(_OFF);} ?>" data-is-parent-chief="<?php if($is_parent_chief){echo(_ON);} else {echo(_OFF);} ?>"<?php echo($attr); ?>>
	<?php if($is_chgseq): ?>
	<div class="dd-handle dd-drag-handle"></div>
	<?php endif; ?>
	<?php if($is_top): ?>
	<div class="pages-menu-top <?php echo($class); ?>-room"></div>
	<?php elseif($page['Page']['id'] == $page['Page']['room_id']): ?>
	<div class="pages-menu-room <?php echo($class); ?>-room"></div>
	<?php endif; ?>
	<?php
	echo $this->Form->create(null, array('url' => array('plugin' => 'page', 'controller' => 'page_menus', 'action' => 'edit'), 'id' => 'PagesMenuForm-'.$page['Page']['id'], 'class' => 'pages-menu-edit-form','data-ajax' => '#pages-menu-edit-item-'.$page['Page']['id']));
	?>
	<input type="hidden" name="data[Page][id]" value="<?php echo(intval($page['Page']['id'])); ?>" />
	<div class="dd-drag-content pages-menu-edit-content clearfix">
		<?php if($is_display): ?>
			<?php if($page['Page']['display_flag'] == NC_DISPLAY_FLAG_ON): ?>
				<a class="pages-menu-display-flag" href="#" title="<?php echo(__('To private')); ?>">
					<img class="icon" alt="<?php echo(__('To private')); ?>" src="<?php echo($this->webroot); ?>img/icons/base/on.gif" data-alt="<?php echo(__('To public')); ?>" />
				</a>
			<?php else: ?>
				<a class="pages-menu-display-flag"  href="#" title="<?php echo(__('To public')); ?>">
					<img class="icon" alt="<?php echo(__('To public')); ?>" src="<?php echo($this->webroot); ?>img/icons/base/off.gif" data-alt="<?php echo(__('To private')); ?>" />
				</a>
			<?php endif; ?>
		<?php else: ?>
			<?php if($page['Page']['display_flag'] == NC_DISPLAY_FLAG_ON): ?>
				<img class="icon disable-lbl" alt="<?php echo(__('To private')); ?>" src="<?php echo($this->webroot); ?>img/icons/base/on.gif" />
			<?php else: ?>
				<img class="icon disable-lbl" alt="<?php echo(__('To public')); ?>" src="<?php echo($this->webroot); ?>img/icons/base/off.gif" />
			<?php endif; ?>
		<?php endif; ?>
		<input type="hidden" name="data[Page][display_flag]" value="<?php echo(intval($page['Page']['display_flag'])); ?>" />

		<a class="pages-menu-edit-title<?php echo($class_link);?>" href="<?php echo($this->webroot); ?><?php echo($page['Page']['permalink']); ?>" title="<?php echo(h($page['Page']['page_name'])); ?><?php echo(h($tooltip_title)); ?>"<?php if($is_editing_page_name): ?> style="display:none;"<?php endif; ?>>
			<?php echo(h($page['Page']['page_name'])); ?>
		</a>
		<?php
		if($is_edit == true) {
			$settings = array(
				'id' => null,
				'class' => "pages-menu-edit-title text",
				'value' => $page['Page']['page_name'] ,
				'label' => false,
				'div' => false,
				'maxlength' => NC_VALIDATOR_TITLE_LEN
			);
			if(!$is_editing_page_name) {
				$settings['style'] = "display:none;";
			}
			if(isset($is_child)) {
				$settings['error'] = false;
			} else {
				$settings['error'] = array('attributes' => array(
					'popup' => true,
					'selector' => $this->Js->escape("$('[name=data\\[Page\\]\\[page_name\\]]', $('#PagesMenuForm-".$page['Page']['id']."'))")
				));
			}
			echo $this->Form->input('Page.page_name', $settings);
		}
		?>
	</div>
	<?php if($is_edit_detail): ?>
	<div id="pages-menu-edit-detail-<?php echo(h($page['Page']['id'])); ?>" class="pages-menu-edit-view"<?php if(!empty($is_child) || (!$is_detail || $page_id != $page['Page']['id'])): ?> style="display:none;"<?php endif; ?>>
		<?php
			if($is_detail && $page_id == $page['Page']['id']) {
				if($page['Page']['thread_num'] == 1 && $page['Page']['space_type'] == NC_SPACE_TYPE_GROUP) {
					$community_params = array(
						'community' => $community_params['community'],
						'community_lang' => $community_params['community_lang'],
						'community_tag' => $community_params['community_tag'],
						'photo_samples' => $community_params['photo_samples']
					);
					echo($this->element('index/community', array('page' => $page, 'parent_page' => $parent_page,
						'community_params' => $community_params)));
				} else {
					echo($this->element('index/detail', array('page' => $page, 'parent_page' => $parent_page)));
				}
			}
		 ?>
	</div>

	<?php endif; ?>
	</form>
	<?php if($is_sel_members): ?>
	<div id="pages-menu-edit-participant-<?php echo(h($page['Page']['id'])); ?>" class="pages-menu-edit-view  pages-menu-edit-participant-outer" style="display:none;">
	</div>
	<?php endif; ?>
	<?php if(isset($pages) && !empty($pages[$space_type][$next_thread_num][$page['Page']['id']])): ?>
		<ol class="dd-list">
			<?php echo($this->element('index/edit_page', array('pages' => $pages, 'menus' => $pages[$space_type][$next_thread_num][$page['Page']['id']],
				'page_id' => $page_id, 'space_type' => $space_type, 'admin_hierarchy' => $admin_hierarchy,
				'is_child' => true, 'is_display' => $is_display, 'is_detail' => $is_detail,
				'parent_page' => $page, 'community_params' => isset($community_params) ? $community_params : null,
				'is_root_parent_chief' => isset($is_root_parent_chief) ? $is_root_parent_chief : true))); ?>
		</ol>
	<?php endif; ?>
	<?php if($is_edit || $is_delete || $is_chief || $is_sel_modules || $is_sel_members): ?>
	<div class="pages-menu-edit-operation clearfix"<?php if($page['Page']['id'] != $page_id): ?> style="display:none;"<?php endif; ?>>
	<?php
		if($is_edit_detail) {
			echo $this->Html->link('', array('plugin' => 'page', 'controller' => 'page_menus', 'action' => 'detail'),
				array('title' => __('Edit'), 'class' => 'pages-menu-edit-icon' . ' nc-tooltip',
				'data-ajax-inner' => '#pages-menu-edit-detail-'.$page['Page']['id'], 'data-page-edit-id' => $page['Page']['id']));
		} else {
			echo $this->Html->link('', '#',
				array('title' => __('Edit'), 'class' => 'pages-menu-edit-icon disable-lbl',
				'onclick' => 'return false;'));
		}
		if($is_parent_chief || $is_chief || $is_sel_modules || $is_sel_members) {
			$copy_page_id = $this->Session->read('Pages.'.'copy_page_id');
			if(isset($copy_page_id)) {
				$operation_class = ' pages-menu-edit-highlight-icon';
			} else {
				$operation_class = '';
			}
			echo $this->Html->link('', '#',
				array('title' => __d('page', 'Other operations'), 'class' => 'pages-menu-other-icon' . ' nc-tooltip'.$operation_class,
				'data-ajax-inner' => '#pages-menu-edit-view-'.$page['Page']['id'],
				'data-is-parent-chief' => $is_parent_chief,
				'data-is-chief' => $is_chief,
				'data-is-sel-modules' => $is_sel_modules,
				'data-is-sel-members' => $is_sel_members,
				'data-is-contents' => $is_contents,
				'onclick' => '$.PageMenu.clkOtherOperation(event);return false;' ));

		} else {
			echo $this->Html->link('', '#',
				array('title' => __d('page', 'Other operations'), 'class' => 'pages-menu-other-icon disable-lbl',
				'onclick' => 'return false;'));
		}
		if($is_delete) {
			echo $this->Html->link('', array('plugin' => 'page', 'controller' => 'page_menus', 'action' => 'delete'),
				array('title' => ($page['Page']['id'] == $page['Page']['room_id']) ? __d('page', 'Delete room') : __d('page', 'Delete page'), 'class' => 'pages-menu-delete-icon' . ' nc-tooltip',
				'data-ajax' => '#pages-menu-edit-item-'.$page['Page']['id'], 'data-ajax-type' => 'POST', 'data-ajax-data' => '#pages-menu-edit-item-' . $page['Page']['id']));
		} else {
			echo $this->Html->link('', '#',
				array('title' => ($page['Page']['id'] == $page['Page']['room_id']) ? __d('page', 'Delete room') : __d('page', 'Delete page'), 'class' => 'pages-menu-delete-icon disable-lbl',
				'onclick' => 'return false;'));
		}
	?>
	</div>
	<?php endif; ?>
	<?php if((!isset($error_flag) || !$error_flag) && (!isset($is_child) || !$is_child) && isset($pre_permalink)): ?>
	<script>
	$(function(){
		$.PageMenu.itemInit(<?php echo($page['Page']['id']); ?>, '<?php echo($this->Html->url('/', true).$this->Js->escape($pre_permalink)); ?>', '<?php echo($this->Html->url('/', true).$this->Js->escape($permalink)); ?>');
	});
	</script>
	<?php endif; ?>
</li>