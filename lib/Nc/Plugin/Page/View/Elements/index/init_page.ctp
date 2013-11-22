<?php
/**
 * ページメニュークラス設定
 * スペースタイプ毎にクラス指定
 * コミュニティーの場合
 *		不参加
 *			公開：普通
 *			非公開：薄く
 *		参加
 *			公開OR強制参加＋参加：濃い
 *			非公開：普通
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.Page.View
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
if($page['Page']['space_type'] == NC_SPACE_TYPE_PUBLIC) {
	$class = 'pages-menu-handle-public';
} else if($page['Page']['space_type'] == NC_SPACE_TYPE_MYPORTAL) {
	$class = 'pages-menu-handle-myportal';
} else if($page['Page']['space_type'] == NC_SPACE_TYPE_PRIVATE) {
	$class = 'pages-menu-handle-private';
} else {
	$class = 'pages-menu-handle-community';
	if($page['PageAuthority']['hierarchy'] <= NC_AUTH_GUEST) {
		if($page['Community']['publication_range_flag'] == NC_PUBLICATION_RANGE_FLAG_ONLY_USER && !isset($page['PageUserLink']['authority_id'])) {
			$class .= '-light';
		}
	} else {
		if($page['Community']['publication_range_flag'] >= NC_PUBLICATION_RANGE_FLAG_LOGIN_USER ||
			!empty($page['Community']['participate_force_all_users'])) {
			$class .= '-dark';
		}
	}
}
if($is_edit == _OFF && $page['Page']['thread_num'] == 1) {
	$class .= ' ' . $class . '-topnode';
}
echo $class;
?>