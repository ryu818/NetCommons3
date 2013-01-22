<?php
/**
 * ページメニュー用ヘルパー
 *
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       App.Controllers.Components
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class PageMenuHelper extends AppHelper {

/**
 * $authority_id, $hierarchy取得
 *
 * @param   array $auth_list
 * @param   Model Page $page
 * @param   Model PageUserLink $page_user_links
 * @param   Model User $user
 * @param   Model Authority $authority
 * @param   array $sess_page_user_links
 * @param   integer $default_authority_id
 * @return  array ($authority_id, $hierarchy)
 * @since   v 3.0.0.0
 */
	public function getAuth($auth_list, $page, $user, $sess_page_user_links, $default_authority_id) {
		if(!empty($sess_page_user_links['PageUserLink'][$user['User']['id']])) {
			// セッションあり
			$authority_id = $sess_page_user_links['PageUserLink'][$user['User']['id']]['authority_id'];
			$hierarchy = $this->_getHierarchy($authority_id, $auth_list);
		//} else if($user['User']['id'] == $user_id) {
		//	// コミュニティを作成する本人
		//	$authority_id = NC_AUTH_CHIEF_ID;
		//	$hierarchy = $auth_list[NC_AUTH_CHIEF][NC_AUTH_CHIEF_ID];
		} else if(isset($user['PageUserLink']['authority_id'])) {
			// データあり
			$authority_id = $user['PageUserLink']['authority_id'];
			if(isset($user['Authority']['hierarchy'])) {
				$hierarchy = $user['Authority']['hierarchy'];
			} else {
				$hierarchy = NC_AUTH_OTHER;
			}
		} else {
			$authority_id = $default_authority_id;
			$hierarchy = $this->_getHierarchy($authority_id, $auth_list);
		}

		return array($authority_id, $hierarchy);
    }

/**
 * authority_idからhierarchyを取得
 *
 * @param   integer $authority_id
 * @param   array $auth_list
 * @return  integer $hierarchy
 * @since   v 3.0.0.0
 */
	protected function _getHierarchy($authority_id, $auth_list) {
		if($authority_id == NC_AUTH_GUEST_ID)
			$hierarchy = NC_AUTH_GUEST;
		else if($authority_id == NC_AUTH_OTHER_ID)
			$hierarchy = NC_AUTH_OTHER;
		else if(!empty($auth_list[NC_AUTH_CHIEF][$authority_id]))
			$hierarchy = $auth_list[NC_AUTH_CHIEF][$authority_id]['hierarchy'];
		else if(!empty($auth_list[NC_AUTH_MODERATE][$authority_id]))
			$hierarchy = $auth_list[NC_AUTH_MODERATE][$authority_id]['hierarchy'];
		else if(!empty($auth_list[NC_AUTH_GENERAL][$authority_id]))
			$hierarchy = $auth_list[NC_AUTH_GENERAL][$authority_id]['hierarchy'];
		else
			$hierarchy = NC_AUTH_OTHER;
		return $hierarchy;
	}
}