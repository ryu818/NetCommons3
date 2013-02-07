<div id="pages-menu-edit-other-operation" class="nc-drop-down"<?php if($copy_page_id > 0){ echo(' data-copy-page-id="'.intval($copy_page_id).'"'.' data-id="'.intval($copy_page_id).'"');} ?> data-url="<?php echo $this->Html->url(array('plugin' => 'page', 'controller' => 'page_operation', 'action' => 'check'));?>">
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
				echo $this->Html->link(__('Copy'), array('plugin' => 'page', 'controller' => 'page_operation', 'action' => 'copy'),
					array('title' => __('Copy'), 'class' => 'link hover-highlight', 'onclick' => '$.PageMenu.clkCopy(event);', 'data-ajax' => '', 'data-ajax-type' => 'post'));
			?>
		</li>
		<li  data-operation="copy-after">
			<?php
				echo $this->Html->link(__('Move'), array('plugin' => 'page', 'controller' => 'page_operation', 'action' => 'move'),
					array('title' => __('Move'), 'class' => 'link hover-highlight','data-ajax' => '', 'data-ajax-type' => 'post'));
			?>
		</li>
		<li  data-operation="copy-after">
			<?php
				echo $this->Html->link(__('Create shortcut'), array('plugin' => 'page', 'controller' => 'page_operation', 'action' => 'shortcut'),
					array('title' => __('Create shortcut'), 'class' => 'link hover-highlight','data-ajax' => '', 'data-ajax-type' => 'post'));
			?>
		</li>
		<li  data-operation="copy-after">
			<?php
				echo $this->Html->link(__('Paste'), array('plugin' => 'page', 'controller' => 'page_operation', 'action' => 'paste'),
					array('title' => __('Paste'), 'class' => 'link hover-highlight','data-ajax' => '', 'data-ajax-type' => 'post'));
			?>
		</li>
		<li class="nc-drop-down-border" data-operation="copy-after" data-name="cancel">
			<?php
				echo $this->Html->link(__('Cancel copy'), array('plugin' => 'page', 'controller' => 'page_operation', 'action' => 'cancel'),
					array('title' => __('Cancel copy'), 'class' => 'link hover-highlight', 'onclick' => '$.PageMenu.clkCancel(event);', 'data-ajax' => '', 'data-ajax-type' => 'post'));
			?>
		</li>
		<li>
			<a class="link hover-highlight" href="#">
				<?php /* TODO:未実装 */ echo(__d('page', 'Edit members'));?>
			</a>
		</li>
		<li>
			<a class="link hover-highlight" href="#">
				<?php /* TODO:未実装 */ echo(__d('page', 'Modules to use'));?>
			</a>
		</li>
		<li>
			<a class="link hover-highlight" href="#">
				<?php /* TODO:未実装 */ echo(__('Contents list'));?>
			</a>
		</li>
	</ul>
</div>