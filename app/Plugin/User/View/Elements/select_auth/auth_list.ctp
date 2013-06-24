<?php
if(empty($auth[$authority_id]['authority_name'])) {
	$authorityName = $auth[$def_authority_id]['authority_name'];
} else {
	$authorityName = $auth[$authority_id]['authority_name'];
}
if(!empty($auth[$authority_id])) {
	$value = $authority_id;
} else {
	$value = $def_authority_id;
}

if($authority_id == NC_AUTH_OTHER_ID) {
	$authority_id = $defaultEntryAuthorityId;
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
			$selectAfterHtml .= "<option value=\"".$data['id']."\"".$selected.">".h(__($data['authority_name']))."</option>";
		}
	$selectAfterHtml .= "</select>";
}
if(!empty($all_selected) && $all_selected) {
	$selectAfterHtml .= '<br /><input type="button" onclick="$.User.allChecked('.$user_id.','.$authority_id.', this); return false;" value="'.__('Select All').'" />';
}

if($radio) {
	$id = $prefix.'-'.$user_id.'-'.$room_id.'-'.$def_authority_id;
	$name = 'data[PageUserLink]['.$room_id.'][authority_id]';

	$checked = "";

	if($value == $authority_id) {
		$checked .= " checked=\"checked\"";
	}

	$html = sprintf("<label ".(($selectAfterHtml == '') ? "class=\"pages-menu-auth-listbox-lbl\"" : '')." for=\"%s\">".
					"<input id=\"%s\" class=\"user-auth-listbox-name-".$def_authority_id."\" type=\"radio\" name=\"%s\" value=\"%d\" %s />&nbsp;&nbsp;%s</label>%s", $id, $id, $name, $value, $checked, $selectHtml, $selectAfterHtml);
} else {
	$name = 'data[PageUserLinkHeader]['.$authority_id.'][authority_id]';
	$hidden_detail_html = "<input type=\"hidden\" name=\"".$name."\" value=\"".$value."\" />";
	$html = $selectHtml.$hidden_detail_html.$selectAfterHtml;
}
echo($html);
?>