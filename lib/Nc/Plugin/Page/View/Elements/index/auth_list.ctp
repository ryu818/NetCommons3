<?php
/**
 * ページメニュー：参加者修正、権限セレクトボックスORラジオボタン
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.Page.View
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
if(empty($auth[$authority_id]['name'])) {
	$authorityName = $auth[$def_authority_id]['name'];
} else {
	$authorityName = $auth[$authority_id]['name'];
}
if(!empty($auth[$authority_id])) {
	$value = $authority_id;
} else if(isset($user_authority_id) && !empty($auth[$user_authority_id])) {
	$value = $user_authority_id;
} else {
	$value = $def_authority_id;
}
$disablStr = "";
if($radio) {
	$id = $prefix.'-'.$user_id.'-'.$room_id.'-'.$def_authority_id;
	$name = 'data[PageUserLink]['.$user_id.'][authority_id]';

	$checked = "";

	if($value == $authority_id) {
		$checked .= " checked=\"checked\"";
	}
	$hidden_detail_html = "";
	if($isNoneMember || ($active_user_id == $user_id && $page['Page']['thread_num'] == 1) ||
		(!$existsPageUserLink && ($participant_type == NC_PARTICIPANT_TYPE_DEFAULT_ENABLED && $value != $default_authority_id && $value != NC_AUTH_OTHER_ID))) {
		// participant_typeをみて、disabledを切替える。
		if($value == $authority_id) {
			$hidden_detail_html .= "<input type=\"hidden\" name=\"".$name."\" value=\"".$value."\" />";
		}
		$disablStr = " disabled=\"disabled\"";
	} else if(!$existsPageUserLink && $participant_type <= NC_PARTICIPANT_TYPE_DEFAULT_ENABLED) {
		$disablStr = " disabled=\"disabled\"";
	}

	if($user_id > 0 && $disablStr == '' && $def_hierarchy > ceil($users[$key]['Authority']['hierarchy']/100)*100 ) {
		// パブリック、自分自身以外のマイポータル、「参加者のみ」のコミュニティーでは、自分の権限以上にはなれない。
		// 但し、パブリック以外のルーム下に、さらにルームを作成した場合は、自分以上にもなれる（ゲスト以外）。
		if($page['Page']['space_type'] != NC_SPACE_TYPE_GROUP || $users[$key]['Authority']['hierarchy'] == NC_AUTH_GUEST ||
			($page['Page']['thread_num'] == 1 && $users[$key]['Community']['participate_flag'] == NC_PARTICIPATE_FLAG_ONLY_USER)) {
			$disablStr = " disabled=\"disabled\"";
		}
	}
}
if(count($auth) == 1 || $selauth == false) {
	$select_html = "<span class=\"pages-menu-auth-listbox-name\">".h(__($authorityName))."</span>";
	$select_after_html = '';
} else {
	$select_html = '';
	$select_after_html = "<select class=\"pages-menu-auth-listbox-name\" onchange=\"$.PageMenu.chgSelectAuth(this); return false;\"".$disablStr.">";
		foreach($auth as $data) {
			if($value == $data['id']) {
				$selected = " selected=\"selected\"";
			} else {
				$selected = "";
			}
			$select_after_html .= "<option value=\"".$data['id']."\"".$selected.">".h($data['name'])."</option>";
		}
	$select_after_html .= "</select>";
}
if(!empty($all_selected) && $all_selected) {
	$select_after_html .= '<br /><input type="button" onclick="$.PageMenu.allChecked('.$page['Page']['id'].','.$authority_id.', this); return false;" value="'.__('Select All').'" />';
}

if($radio) {
	$html = sprintf("<label ".(($select_after_html == '') ? "class=\"pages-menu-auth-listbox-lbl".($disablStr != '' ? ' disable-lbl' : '')."\"" : '')." for=\"%s\">".
					"<input id=\"%s\" class=\"pages-menu-auth-listbox-name-".$def_authority_id."\" type=\"radio\" name=\"%s\" value=\"%d\" %s />&nbsp;&nbsp;%s</label>%s", $id, $id, $name, $value, $checked.$disablStr, $select_html, $select_after_html).
					$hidden_detail_html;
} else {
	$name = 'data[PageUserLinkHeader]['.$authority_id.'][authority_id]';
	$hidden_detail_html = "<input type=\"hidden\" name=\"".$name."\" value=\"".$value."\" />";
	$html = $select_html.$hidden_detail_html.$select_after_html;
}
echo($html);
?>