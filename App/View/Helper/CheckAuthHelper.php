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
 * @param   integer $myselfContentHierarchy ログイン会員のroomにおけるhierarchy
 * @param   integer $postHierarchy 編集画面における記事投稿権限hierarchy
 * @param   integer $ownerUserId 編集対象者のuser_id
 * @param   integer $ownerContentHierarchy 編集対象者のhierarchy
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function isEdit($myselfContentHierarchy, $postHierarchy = null, $ownerUserId = null, $ownerContentHierarchy = null) {
		// CheckAuthComponentのisEditファンクションを呼び出し
		App::uses('CheckAuthComponent', 'Controller/Component');
		$CheckAuthComponent = new CheckAuthComponent(new ComponentCollection());
		return $CheckAuthComponent->isEdit($myselfContentHierarchy, $postHierarchy, $ownerUserId, $ownerContentHierarchy);
	}

/**
 * 会員への編集権限があるかどうか
 * @param   integer $ownerHierarchy 参照相手のhierarchy
 * @param   integer $myselfHierarchy 参照する会員のhierarchy
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function isEditForUser($ownerHierarchy, $myselfHierarchy = null) {
		// CheckAuthComponentのisEditForUserファンクションを呼び出し
		App::uses('CheckAuthComponent', 'Controller/Component');
		$CheckAuthComponent = new CheckAuthComponent(new ComponentCollection());
		return $CheckAuthComponent->isEditForUser($ownerHierarchy, $myselfHierarchy);
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
		$CheckAuthComponent = new CheckAuthComponent(new ComponentCollection());
		return $CheckAuthComponent->checkAuth($hierarchy, $allowAuth);
	}
}