<div id="pages-menu-edit-other-operation" class="nc-drop-down"<?php if($copy_page_id > 0){ echo(' data-copy-page-id="'.intval($copy_page_id).'"'.' data-id="'.intval($copy_page_id).'"'.' data-copy-space-type="'.$copy_page['Page']['space_type'].'"'.' data-copy-is-top="'.(($copy_page['Page']['thread_num'] <= 1) ? _ON : _OFF).'"');} ?> data-ajax-url="<?php echo $this->Html->url(array('plugin' => 'page', 'controller' => 'page_operations', 'action' => 'check'));?>">
	<ul>
		<li id="pages-menu-edit-other-operation-title" class="nc-drop-down-border" data-operation="copy-after">
			<?php /* コピー後タイトル */  ?>
			<?php if($copy_page_id > 0): ?>
				<?php
					echo("[".__('Copy')."]".h($copy_page['Page']['page_name']));
				?>
			<?php endif; ?>
		</li>
		<li class="nc-drop-down-border" data-operation="copy" data-name="copy">
			<?php
				echo $this->Html->link(__('Copy'), array('plugin' => 'page', 'controller' => 'page_operations', 'action' => 'copy'),
					array('title' => __('Copy'), 'class' => 'link hover-highlight', 'onclick' => '$.PageMenu.clkCopy(event);', 'data-ajax' => '', 'data-ajax-type' => 'post'));
			?>
		</li>
		<li  data-operation="copy-after" data-name="move">
			<?php
				echo $this->Html->link(__('Move'), array('plugin' => 'page', 'controller' => 'page_operations', 'action' => 'move'),
					array('title' => __('Move'), 'class' => 'link hover-highlight','data-ajax' => '', 'data-ajax-type' => 'post'));
			?>
		</li>
		<li  data-operation="copy-after">
			<?php
				echo $this->Html->link(__('Create shortcut'), array('plugin' => 'page', 'controller' => 'page_operations', 'action' => 'shortcut'),
					array('title' => __('Create shortcut'), 'class' => 'link hover-highlight','data-ajax' => '', 'data-ajax-type' => 'post'));
			?>
		</li>
		<li  data-operation="copy-after">
			<?php
				echo $this->Html->link(__('Paste'), array('plugin' => 'page', 'controller' => 'page_operations', 'action' => 'paste'),
					array('title' => __('Paste'), 'class' => 'link hover-highlight','data-ajax' => '', 'data-ajax-type' => 'post'));
			?>
		</li>
		<li class="nc-drop-down-border" id="pages-menu-edit-other-operation-copy-cancel" data-name="cancel">
			<?php
				echo $this->Html->link(__('Cancel copy'), array('plugin' => 'page', 'controller' => 'page_operations', 'action' => 'cancel'),
					array('title' => __('Cancel copy'), 'class' => 'link hover-highlight', 'onclick' => '$.PageMenu.clkCancel(event);', 'data-ajax' => '', 'data-ajax-type' => 'post'));
			?>
		</li>
		<li id="pages-menu-edit-other-operation-add-members">
			<?php
				echo $this->Html->link(__d('page', 'Add members'), array('plugin' => 'page', 'controller' => 'page_menus', 'action' => 'participant'),
					array('title' => __d('page', 'Add members'), 'class' => 'link hover-highlight', 'data-page-edit-id' => '','data-ajax-replace' => '#pages-menu-edit-participant'));
			?>
		</li>
		<li id="pages-menu-edit-other-operation-members">
			<?php
				echo $this->Html->link(__d('page', 'Edit members'), array('plugin' => 'page', 'controller' => 'page_menus', 'action' => 'participant'),
					array('title' => __d('page', 'Edit members'), 'class' => 'link hover-highlight', 'data-page-edit-id' => '','data-ajax-replace' => '#pages-menu-edit-participant'));
			?>
		</li>
		<li id="pages-menu-edit-other-operation-unassign-members">
			<?php
				echo $this->Html->link(__d('page', 'Unassign members'), array('plugin' => 'page', 'controller' => 'page_menus', 'action' => 'deallocation'),
					array('title' => __d('page', 'Unassign members'), 'class' => 'link hover-highlight', 'data-page-edit-id' => '','data-ajax-replace' => '#pages-menu-edit-item',
						'data-ajax-confirm' => h(__d('page','Unassign members of [%s]. Are you sure?',$page['Page']['page_name'])), 'data-ajax-type' => 'post'
				));
			?>
		</li>
		<li id="pages-menu-edit-other-operation-modules">
			<a class="link hover-highlight" href="#">
				<?php /* TODO:未実装 */ echo(__d('page', 'Modules to use'));?>
			</a>
		</li>
		<li id="pages-menu-edit-other-operation-contents">
			<a class="link hover-highlight" href="#">
				<?php /* TODO:未実装 */ echo(__('Contents list'));?>
			</a>
		</li>
	</ul>
</div>