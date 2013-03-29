<?php
/**
 * 権限チェック用ヘルパー
 *
 * <pre>
 * 記事の編集権限等の取得
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       App.Controllers.Components
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class CheckAuthHelper extends AppHelper {
	public $helpers = array('Form');

/**
 * 編集権限があるかどうか
 * @param   integer $room_hierarchy ログイン会員のroomにおけるhierarchy
 * @param   integer $post_hierarchy 記事投稿者hierarchy
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function isEdit($room_hierarchy, $post_hierarchy) {
		if($room_hierarchy >= NC_AUTH_MIN_MODERATE) {
			if($room_hierarchy >= $post_hierarchy) {
				$is_edit = true;
			} else {
				$is_edit = false;
			}
		} else {
			if($room_hierarchy > $post_hierarchy) {
				$is_edit = true;
			} else {
				$is_edit = false;
			}
		}
		return $is_edit;
    }
}