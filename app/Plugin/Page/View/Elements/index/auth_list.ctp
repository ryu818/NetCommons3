<?php
if(empty($auth[$authority_id]['authority_name'])) {
	$authorityName = $auth[$def_authority_id]['authority_name'];
} else {
	$authorityName = $auth[$authority_id]['authority_name'];
}
if(!empty($auth[$authority_id])) {
	$value = $authority_id;
} else if(isset($user_authority_id) && !empty($auth[$user_authority_id])) {
	$value = $user_authority_id;
} else {
	$value = $def_authority_id;
}
$isDisablStr = "";
if($radio) {
	$id = $prefix.'-'.$user_id.'-'.$room_id.'-'.$def_authority_id;
	$name = 'data[PageUserLink]['.$user_id.'][authority_id]';

	$checked = "";

	if($value == $authority_id) {
		$checked .= " checked=\"checked\"";
	}
	$hidden_detail_html = "";
	if($isNoneMember || ($active_user_id == $user_id && $admin_hierarchy < NC_AUTH_MIN_ADMIN) ||
		(!$existsPageUserLink && ($participant_type == 1 || ($participant_type == 2 && $value != $default_authority_id && $value != NC_AUTH_OTHER_ID)))) {
		// ページメニューの管理者ならば、変更を許可
		// participant_typeをみて、disabledを切替える。
		$checked .= " disabled=\"disabled\"";
		if($value == $authority_id) {
			$hidden_detail_html .= "<input type=\"hidden\" name=\"".$name."\" value=\"".$value."\" />";
		}
		$isDisablStr = " disabled=\"disabled\"";
	} else if(!$existsPageUserLink && $participant_type <= 2) {
		$isDisablStr = " disabled=\"disabled\"";
	}
}
if(count($auth) == 1 || $selauth == false) {
	$select_html = "<span class=\"pages-menu-auth-listbox-name\">".h(__($authorityName))."</span>";
	$select_after_html = '';
} else {
	$select_html = '';
	$select_after_html = "<select class=\"pages-menu-auth-listbox-name\" onchange=\"$.PageMenu.chgSelectAuth(this); return false;\"".$isDisablStr.">";
		foreach($auth as $data) {
			if($value == $data['id']) {
				$selected = " selected=\"selected\"";
			} else {
				$selected = "";
			}
			$select_after_html .= "<option value=\"".$data['id']."\"".$selected.">".h($data['authority_name'])."</option>";
		}
	$select_after_html .= "</select>";
}
if(!empty($all_selected) && $all_selected) {
	$select_after_html .= '<br /><input type="button" onclick="$.PageMenu.allChecked('.$page['Page']['id'].','.$authority_id.', this); return false;" value="'.__('Select All').'" />';
}

if($radio) {
	$html = sprintf("<label ".(($select_after_html == '') ? "class=\"pages-menu-auth-listbox-lbl\"" : '')." for=\"%s\">".
					"<input id=\"%s\" class=\"pages-menu-auth-listbox-name-".$def_authority_id."\" type=\"radio\" name=\"%s\" value=\"%d\" %s />&nbsp;&nbsp;%s</label>%s", $id, $id, $name, $value, $checked, $select_html, $select_after_html).
					$hidden_detail_html;
} else {
	$name = 'data[PageUserLinkHeader]['.$authority_id.'][authority_id]';
	$hidden_detail_html = "<input type=\"hidden\" name=\"".$name."\" value=\"".$value."\" />";
	$html = $select_html.$hidden_detail_html.$select_after_html;
}
echo($html);
?>