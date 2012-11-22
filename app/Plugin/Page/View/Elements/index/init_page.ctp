<?php
if($menu['space_type'] == NC_SPACE_TYPE_PUBLIC) {
	$class = 'pages-menu-handle-public';
} else if($menu['space_type'] == NC_SPACE_TYPE_MYPORTAL) {
	$class = 'pages-menu-handle-myportal';
} else if($menu['space_type'] == NC_SPACE_TYPE_PRIVATE) {
	$class = 'pages-menu-handle-private';
} else {
	$class = 'pages-menu-handle-community';
}
echo $class;
?>