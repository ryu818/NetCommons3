<?php
	$ret = array();
	$ret['page'] = $page_num;
	$ret['total'] = $total;
	foreach($users as $key => $val) {
		list($authority_id, $hierarchy) = $this->PageMenu->getAuth($auth_list, $page, $val, $page_user_links, $default_authority_id);

		$ret['rows'][$key]['cell'][] = h($val['User']['handle'])."<input type=\"hidden\" name=\"data[PageUserLink][".$val['User']['id']."][user_id]\" value=\"".$val['User']['id']."\" />";
		$ret['rows'][$key]['cell'][] = $this->element('index/auth_list', array('auth' => $auth_list[NC_AUTH_CHIEF],   'user_id' => $val['User']['id'], 'prefix' => 'pages-menu-edit-participant', 'selauth'=> true,  'radio'=> true, 'def_hierarchy' => NC_AUTH_CHIEF,    'def_authority_id' => NC_AUTH_CHIEF_ID,    'authority_id' => $authority_id, 'hierarchy' => $hierarchy, 'room_id' => $room_id, 'active_user_id' => $user_id));
		$ret['rows'][$key]['cell'][] = $this->element('index/auth_list', array('auth' => $auth_list[NC_AUTH_MODERATE],'user_id' => $val['User']['id'], 'prefix' => 'pages-menu-edit-participant', 'selauth'=> true,  'radio'=> true, 'def_hierarchy' => NC_AUTH_MODERATE, 'def_authority_id' => NC_AUTH_MODERATE_ID, 'authority_id' => $authority_id, 'hierarchy' => $hierarchy, 'room_id' => $room_id, 'active_user_id' => $user_id));
		$ret['rows'][$key]['cell'][] = $this->element('index/auth_list', array('auth' => $auth_list[NC_AUTH_GENERAL], 'user_id' => $val['User']['id'], 'prefix' => 'pages-menu-edit-participant', 'selauth'=> true,  'radio'=> true, 'def_hierarchy' => NC_AUTH_GENERAL,  'def_authority_id' => NC_AUTH_GENERAL_ID,  'authority_id' => $authority_id, 'hierarchy' => $hierarchy, 'room_id' => $room_id, 'active_user_id' => $user_id));
		$ret['rows'][$key]['cell'][] = $this->element('index/auth_list', array('auth' => $auth_list[NC_AUTH_GUEST],   'user_id' => $val['User']['id'], 'prefix' => 'pages-menu-edit-participant', 'selauth'=> false, 'radio'=> true, 'def_hierarchy' => NC_AUTH_GUEST,    'def_authority_id' => NC_AUTH_GUEST_ID,    'authority_id' => $authority_id, 'hierarchy' => $hierarchy, 'room_id' => $room_id, 'active_user_id' => $user_id));
		$ret['rows'][$key]['cell'][] = $this->element('index/auth_list', array('auth' => $auth_list[NC_AUTH_OTHER],   'user_id' => $val['User']['id'], 'prefix' => 'pages-menu-edit-participant', 'selauth'=> false, 'radio'=> true, 'def_hierarchy' => NC_AUTH_OTHER,    'def_authority_id' => NC_AUTH_OTHER_ID,    'authority_id' => $authority_id, 'hierarchy' => $hierarchy, 'room_id' => $room_id, 'active_user_id' => $user_id));
	}
?>
<?php echo $this->Js->object($ret); ?>