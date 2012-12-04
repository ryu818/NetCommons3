<?php
$page['Page']['hierarchy'] = $page['Authority']['hierarchy'];
echo($this->element('index/item', array('pages' => $pages, 'menu' => $page['Page'], 'space_type' => $page['Page']['space_type'], 'page_id' => $page['Page']['id'], 'admin_hierarchy' => $admin_hierarchy, 'is_detail' => $is_detail, 'is_error' => $is_error)));
 ?>