<?php
if($page['Page']['space_type'] == NC_SPACE_TYPE_PUBLIC) {
	$class = 'pages-menu-handle-public';
} else if($page['Page']['space_type'] == NC_SPACE_TYPE_MYPORTAL) {
	$class = 'pages-menu-handle-myportal';
} else if($page['Page']['space_type'] == NC_SPACE_TYPE_PRIVATE) {
	$class = 'pages-menu-handle-private';
} else {
	$class = 'pages-menu-handle-community';
}
if($is_edit == _OFF && $page['Page']['thread_num'] == 1) {
	$class .= ' ' . $class . '-topnode';
}
echo $class;
?>