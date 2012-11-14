<?php
if($menu['space_type'] == NC_SPACE_TYPE_PUBLIC) {
	$class = 'nc_page_handle_public';
} else if($menu['space_type'] == NC_SPACE_TYPE_MYPORTAL) {
	$class = 'nc_page_handle_myportal';
} else if($menu['space_type'] == NC_SPACE_TYPE_PRIVATE) {
	$class = 'nc_page_handle_private';
} else {
	$class = 'nc_page_handle_community';
}
echo $class;
?>