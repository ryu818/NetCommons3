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
	public $helpers = array('Session');

/**
 * 編集権限があるかどうか
 * @param   integer $roomHierarchy ログイン会員のroomにおけるhierarchy
 * @param   integer $adminPostHierarchy 管理画面における記事投稿権限hierarchy
 * @param   integer $postUserId 記事投稿者user_id
 * @param   integer $postHierarchy 記事投稿者hierarchy
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function isEdit($roomHierarchy, $adminPostHierarchy = null, $postUserId = null, $postHierarchy = null) {
		$isEdit = true;
		if(isset($adminPostHierarchy)) {
			if($roomHierarchy < $adminPostHierarchy) {
				return false;
			}
		}
		if(isset($postUserId)) {
			$user = $this->Session->read(NC_AUTH_KEY.'.'.'User');
			if(isset($user) && $user['id'] == $postUserId) {
				return true;
			}
		}
		if(isset($postHierarchy)) {
			if($roomHierarchy >= NC_AUTH_MIN_MODERATE) {
				if($roomHierarchy >= $postHierarchy) {
					$isEdit = true;
				} else {
					$isEdit = false;
				}
			} else {
				if($roomHierarchy > $postHierarchy) {
					$isEdit = true;
				} else {
					$isEdit = false;
				}
			}
		}
		return $isEdit;
	}

/**
 * 権限チェック
 *
 * @param   integer $hierarchy
 * @param   integer $allowAuth
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function checkAuth($hierarchy, $allowAuth = null) {
		// CheckAuthComponentのcheckAuthファンクションを呼び出し
		App::uses('CheckAuthComponent', 'Controller/Component');
		$checkAuthComp = new CheckAuthComponent(new ComponentCollection());
		return $checkAuthComp->checkAuth($hierarchy, $allowAuth);
	}
}