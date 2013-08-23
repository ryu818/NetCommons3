<?php
	echo $this->Form->create('User', array(
		'data-ajax' => '#user-init-tab-list',
		'data-ajax-method' => 'inner',
		'data-ajax-confirm' => __d('user', 'Deleting the selected user(s). <br />Are you sure?'),
		'data-ajax-confirm-again' => __d('user', 'All the data of this user will be completely deleted from the database.<br />Are you sure?'),
	));
?>
<?php echo $this->element('language', array('id' => "user-init-tab-list")); ?>
<div class="top-description">
	<?php echo __d('user', 'Click the link [%1$s] to edit the user data. Select a row and push [%2$s] to delete the user(s).', __('Edit'), __('Delete'));?>
</div>
<div class="users-search-outer">
	<?php
		echo $this->Form->button(__('Search for the members'), array('name' => 'search', 'class' => 'common-btn',
			'type' => 'button',
			'data-ajax' => "#user-init-tab-list-search",
			'data-ajax-dialog' => true,
			'data-ajax-dialog-options' => h('{"title" : "'.$this->Js->escape(__('Search for the members')).'","modal" : true, "resizable": true, "width":"800"}'),
			'data-ajax-effect' => 'fold',
			'data-ajax-url' => $this->Html->url(array('action' => 'search', 'language' => $language)),
		));
	?>
</div>
<table id="user-list" summary="<?php echo __d('user', 'Display a list of members.'); ?>" data-url="<?php echo $this->Html->url(array('action' => 'detail', 'language' => $language));?>">
</table>
<?php
	echo $this->Html->div('submit align-right',
		$this->Form->button(__('Delete'), array('name' => 'delete', 'class' => 'common-btn', 'type' => 'submit'))
	);
	echo $this->Form->hiddenVars('User', array(), false);
	echo $this->Form->hiddenVars('UserItemLink', array(), false);
	echo $this->Form->end();
?>
<script>
$(function(){
	$('#user-init-tab').User('<?php echo($id);?>',[
		'<?php echo __d('user_items', $items[NC_ITEM_ID_HANDLE]);?>'
		,'<?php echo __d('user_items', $items[NC_ITEM_ID_USERNAME]);?>'
		,'<?php echo __d('user_items', $items[NC_ITEM_ID_AUTHORITY_ID]);?>'
		,'<?php echo __d('user_items', $items[NC_ITEM_ID_IS_ACTIVE]);?>'
		,'<?php echo __d('user_items', $items[NC_ITEM_ID_CREATED]);?>'
		,'<?php echo __d('user_items', $items[NC_ITEM_ID_LAST_LOGIN]);?>'
		<?php if ($hierarchy >= NC_AUTH_MIN_CHIEF): ?>
		,'<?php echo __('Manage');?>'
		,'<input type="button" onclick="$.Common.allChecked(this, $(\'input:checkbox\',\'#user-list\')); return false;" value="<?php echo __('Select All'); ?>" data-switch-value="<?php echo __('Release All'); ?>" />'
		<?php endif; ?>
	]);
});
</script>