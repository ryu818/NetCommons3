<?php
$page['Page']['hierarchy'] = $page['Authority']['hierarchy'];
echo($this->element('index/item', array('menu' => $page['Page'], 'space_type' => $page['Page']['space_type'], 'page_id' => $page['Page']['id'], 'admin_hierarchy' => $admin_hierarchy)));
 ?>