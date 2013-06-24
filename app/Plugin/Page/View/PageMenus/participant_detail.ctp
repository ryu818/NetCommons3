<?php
	$ret = array();
	$ret['page'] = $page_num;
	$ret['total'] = $total;
	foreach($users as $key => $val) {
		list($authorityId, $hierarchy) = $this->PageMenu->getAuth($auth_list, $page, $val, $page_user_links, $default_authority_id);
		// デフォルト値とそうでない場合で色分けを行う。
		// TODO:今後、ハンドルをリンクにする。
		if($authorityId != NC_AUTH_OTHER_ID && (isset($val['Authority']['hierarchy']) || (isset($val['PageUserLinkParent']['authority_id']) && $page['Page']['id'] != $page['Page']['room_id']))) {
			$existsPageUserLink = true;
			$handle = "<span class=\"pages-menu-select-show-user\">".h($val['User']['handle'])."</span>";
		} else {
			$existsPageUserLink = false;
			$handle = "<span class=\"pages-menu-default-show-user\">".h($val['User']['handle'])."</span>";
		}
		$isNoneMember = false;
		if(isset($val['PageUserLinkParent']['authority_id']) && $val['PageUserLinkParent']['authority_id'] == NC_AUTH_OTHER_ID) {
			$isNoneMember = true;
		}

		$ret['rows'][$key]['cell'][] = $handle."<input type=\"hidden\" name=\"data[PageUserLink][".$val['User']['id']."][user_id]\" value=\"".$val['User']['id']."\" />";
		$ret['rows'][$key]['cell'][] = $this->element('index/auth_list', array('auth' => $auth_list[NC_AUTH_CHIEF],   'user_id' => $val['User']['id'], 'prefix' => 'pages-menu-edit-participant', 'selauth'=> true,  'radio'=> true, 'def_hierarchy' => NC_AUTH_CHIEF,    'def_authority_id' => NC_AUTH_CHIEF_ID,    'authority_id' => $authorityId, 'hierarchy' => $hierarchy, 'room_id' => $room_id, 'active_user_id' => $user_id, 'existsPageUserLink' => $existsPageUserLink, 'isNoneMember' => $isNoneMember));
		$ret['rows'][$key]['cell'][] = $this->element('index/auth_list', array('auth' => $auth_list[NC_AUTH_MODERATE],'user_id' => $val['User']['id'], 'prefix' => 'pages-menu-edit-participant', 'selauth'=> true,  'radio'=> true, 'def_hierarchy' => NC_AUTH_MODERATE, 'def_authority_id' => NC_AUTH_MODERATE_ID, 'authority_id' => $authorityId, 'hierarchy' => $hierarchy, 'room_id' => $room_id, 'active_user_id' => $user_id, 'existsPageUserLink' => $existsPageUserLink, 'isNoneMember' => $isNoneMember));
		$ret['rows'][$key]['cell'][] = $this->element('index/auth_list', array('auth' => $auth_list[NC_AUTH_GENERAL], 'user_id' => $val['User']['id'], 'prefix' => 'pages-menu-edit-participant', 'selauth'=> true,  'radio'=> true, 'def_hierarchy' => NC_AUTH_GENERAL,  'def_authority_id' => NC_AUTH_GENERAL_ID,  'authority_id' => $authorityId, 'hierarchy' => $hierarchy, 'room_id' => $room_id, 'active_user_id' => $user_id, 'existsPageUserLink' => $existsPageUserLink, 'isNoneMember' => $isNoneMember));
		$ret['rows'][$key]['cell'][] = $this->element('index/auth_list', array('auth' => $auth_list[NC_AUTH_GUEST],   'user_id' => $val['User']['id'], 'prefix' => 'pages-menu-edit-participant', 'selauth'=> false, 'radio'=> true, 'def_hierarchy' => NC_AUTH_GUEST,    'def_authority_id' => NC_AUTH_GUEST_ID,    'authority_id' => $authorityId, 'hierarchy' => $hierarchy, 'room_id' => $room_id, 'active_user_id' => $user_id, 'existsPageUserLink' => $existsPageUserLink, 'isNoneMember' => $isNoneMember));
		if($page['Page']['space_type'] != NC_SPACE_TYPE_PUBLIC && (!isset($page['Community']) || $page['Community']['publication_range_flag'] != NC_PUBLICATION_RANGE_FLAG_ALL)) {
			$ret['rows'][$key]['cell'][] = $this->element('index/auth_list', array('auth' => $auth_list[NC_AUTH_OTHER],   'user_id' => $val['User']['id'], 'prefix' => 'pages-menu-edit-participant', 'selauth'=> false, 'radio'=> true, 'def_hierarchy' => NC_AUTH_OTHER,    'def_authority_id' => NC_AUTH_OTHER_ID,    'authority_id' => $authorityId, 'hierarchy' => $hierarchy, 'room_id' => $room_id, 'active_user_id' => $user_id, 'existsPageUserLink' => $existsPageUserLink, 'isNoneMember' => $isNoneMember));
		}
	}
?>
<?php echo $this->Js->object($ret); ?>