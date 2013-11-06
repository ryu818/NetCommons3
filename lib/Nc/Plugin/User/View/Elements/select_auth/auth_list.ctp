<?php
if(empty($auth[$authority_id]['name'])) {
	$authorityName = $auth[$def_authority_id]['name'];
} else {
	$authorityName = $auth[$authority_id]['name'];
}

if(!empty($auth[$authority_id])) {
	$value = $authority_id;
} else if(!empty($auth[$user['User']['authority_id']])) {
	$value = $user['User']['authority_id'];
} else {
	$value = $def_authority_id;
}

if($authority_id == NC_AUTH_OTHER_ID) {
	$authority_id = $value;
}

$room_id = isset($room_id) ? $room_id : '0';
if(count($auth) == 1 || $selauth == false) {
	$selectHtml = "<span class=\"user-auth-listbox-name\">".h(__($authorityName))."</span>";
	$selectAfterHtml = '';
} else {
	$selectHtml = '';
	$selectAfterHtml = "<select class=\"user-auth-listbox-name\" onchange=\"$.User.chgSelectAuth(this); return false;\" >";
		foreach($auth as $data) {
			if($value == $data['id']) {
				$selected = " selected=\"selected\"";
			} else {
				$selected = "";
			}
			$selectAfterHtml .= "<option value=\"".$data['id']."\"".$selected.">".h($data['name'])."</option>";
		}
	$selectAfterHtml .= "</select>";
}
if(!empty($all_selected) && $all_selected) {
	$selectAfterHtml .= '<br /><input type="button" onclick="$.User.allChecked('.$user_id.','.$authority_id.', this); return false;" value="'.__('Select All').'" />';
}
if($radio) {
	$id = $prefix.'-'.$user_id.'-'.$room_id.'-'.$def_authority_id;
	$name = 'data[PageUserLink]['.$authKey.'][authority_id]';

	$checked = "";
	$disabled = "";

	if($value == $authority_id) {
		$checked .= " checked=\"checked\"";
	}
	if($def_hierarchy > ceil($user['Authority']['hierarchy']/100)*100 ) {
		// パブリック、自分自身以外のマイポータル、「参加者のみ」のコミュニティーでは、自分の権限以上にはなれない。
		// 但し、パブリック以外のルーム下に、さらにルームを作成した場合は、自分以上にもなれる。
		// TODO:管理系のため、登録時のチェックはしていない。
		// 参加受付制コミュニティーの権限設定を表示した後、別ウィンドウで「参加会員のみ」のコミュニティーに変更して、一般を主担へ変更できてしまうため、
		// 登録時のチェックも必要。
		if($room['Page']['space_type'] != NC_SPACE_TYPE_GROUP || $user['Authority']['hierarchy'] == NC_AUTH_GUEST ||
			($room['Page']['thread_num'] == 1 && $room['Community']['participate_flag'] == NC_PARTICIPATE_FLAG_ONLY_USER)) {
			$disabled = " disabled=\"disabled\"";
		}
	}

	$html = sprintf("<label ".(($selectAfterHtml == '') ? "class=\"pages-menu-auth-listbox-lbl\"" : '')." for=\"%s\">".
					"<input id=\"%s\" class=\"user-auth-listbox-name-".$def_authority_id."\" type=\"radio\" name=\"%s\" value=\"%d\" %s%s />&nbsp;&nbsp;%s</label>%s", $id, $id, $name, $value, $checked, $disabled, $selectHtml, $selectAfterHtml);
} else {
	$name = 'data[PageUserLinkHeader]['.$authority_id.'][authority_id]';
	$hidden_detail_html = "<input type=\"hidden\" name=\"".$name."\" value=\"".$value."\" />";
	$html = $selectHtml.$hidden_detail_html.$selectAfterHtml;
}
echo($html);
?>