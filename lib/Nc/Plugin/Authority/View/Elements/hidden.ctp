<?php
$fieldsList = array(
	'id',
	'system_flag',
	'hierarchy',
);
if($this->action != 'edit' || (isset($action) && $action == 'set_level')) {
	$fieldsList[] = 'default_name';
}
$detailColumns = array(
	'myportal_use_flag',
	'allow_myportal_viewing_hierarchy',
	'private_use_flag',
	'allow_htmltag_flag',
	'allow_meta_flag',
	'allow_theme_flag',
	'allow_style_flag',
	'allow_layout_flag',
	'allow_attachment',
	'allow_video',
	'max_size',
	'change_leftcolumn_flag',
	'change_rightcolumn_flag',
	'change_headercolumn_flag',
	'change_footercolumn_flag',
	'display_participants_editing',
	'allow_move_operation',
	'allow_copy_operation',
	'allow_shortcut_operation',
	'allow_operation_of_shortcut',
);
$detailColumns2 = array(
	'allow_creating_community',
	'allow_new_participant',
	'public_createroom_flag',
	'group_createroom_flag',
	'myportal_createroom_flag',
	'private_createroom_flag',
);
if($this->action != 'detail' && $this->action != 'detail2') {
	$fieldsList = array_merge($fieldsList, $detailColumns, $detailColumns2);
	echo $this->Form->hiddenVars('ModuleSystemLink');

} else {
	$bufDetailColumns = ($this->action == 'detail') ? $detailColumns : $detailColumns2;
	foreach($bufDetailColumns as $columnName) {
		if(isset($authorityDisabled['Authority'][$columnName]) && $authorityDisabled['Authority'][$columnName] == _ON) {
			$fieldsList[] = $columnName;
		}
	}
	$bufDetailColumnsReverse = ($this->action == 'detail') ? $detailColumns2 : $detailColumns;
	foreach($bufDetailColumnsReverse as $columnName) {
		$fieldsList[] = $columnName;
	}
	if($this->action == 'detail' && $authority['Authority']['system_flag']) {
		$fieldsList[] = 'display_participants_editing';
	}
}
if($this->action != 'usable_module') {
	echo $this->Form->hiddenVars('MyportalModuleLink');
	echo $this->Form->hiddenVars('PrivateModuleLink');
}
echo $this->Form->hiddenVars('Authority', $fieldsList, true, $authority);

echo $this->Form->hidden('activeLang' , array('name' => "activeLang", 'value' => $language));
?>