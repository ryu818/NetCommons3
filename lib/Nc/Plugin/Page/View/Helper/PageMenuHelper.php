<?php
/**
 * ページメニュー用ヘルパー
 *
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       App.Plugin.Page.View.Helper
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class PageMenuHelper extends AppHelper {

/**
 * $authority_id, $hierarchy取得
 *
 * @param   array $authList
 * @param   Model Page $page
 * @param   Model PageUserLink $page_user_links
 * @param   Model User $user
 * @param   Model Authority $authority
 * @param   array $sessPageUserLinks
 * @param   integer $defaultAuthorityId
 * @return  array ($authorityId, $hierarchy)
 * @since   v 3.0.0.0
 */
	public function getAuth($authList, $page, $user, $sessPageUserLinks, $defaultAuthorityId) {
		if(!empty($sessPageUserLinks['PageUserLink'][$user['User']['id']])) {
			// セッションあり
			$authorityId = $sessPageUserLinks['PageUserLink'][$user['User']['id']]['authority_id'];
			$hierarchy = $this->_getHierarchy($authorityId, $authList);
		//} else if($user['User']['id'] == $user_id) {
		//	// コミュニティを作成する本人
		//	$authorityId = NC_AUTH_CHIEF_ID;
		//	$hierarchy = $authList[NC_AUTH_CHIEF][NC_AUTH_CHIEF_ID];
		} else if(isset($user['PageAuthority']['id'])) {
			// データあり
			$authorityId = $user['PageAuthority']['id'];
			//if(isset($user['PageAuthority']['hierarchy'])) {
				$hierarchy = $user['PageAuthority']['hierarchy'];
			//} else {
			//	$hierarchy = NC_AUTH_OTHER;
			//}
		} else if(isset($user['AuthorityParent']['id']) && $page['Page']['id'] != $page['Page']['room_id']) {
			// 新規
			$authorityId = $user['AuthorityParent']['id'];
			//if(isset($user['AuthorityParent']['hierarchy'])) {
				$hierarchy = $user['AuthorityParent']['hierarchy'];
			//} else {
			//	$hierarchy = NC_AUTH_OTHER;
			//}
		} else if(isset($user['PageUserLink']['authority_id']) && $user['PageUserLink']['authority_id'] == NC_AUTH_OTHER_ID) {
			$authorityId = NC_AUTH_OTHER_ID;
			$hierarchy = NC_AUTH_OTHER;
		} else {
			$authorityId = $defaultAuthorityId;
			$hierarchy = $this->_getHierarchy($authorityId, $authList);
		}

		return array($authorityId, $hierarchy);
	}

/**
 * authority_idからhierarchyを取得
 *
 * @param   integer $authorityId
 * @param   array $authList
 * @return  integer $hierarchy
 * @since   v 3.0.0.0
 */
	protected function _getHierarchy($authorityId, $authList) {
		if($authorityId == NC_AUTH_GUEST_ID)
			$hierarchy = NC_AUTH_GUEST;
		else if($authorityId == NC_AUTH_OTHER_ID)
			$hierarchy = NC_AUTH_OTHER;
		else if(!empty($authList[NC_AUTH_CHIEF][$authorityId]))
			$hierarchy = $authList[NC_AUTH_CHIEF][$authorityId]['hierarchy'];
		else if(!empty($authList[NC_AUTH_MODERATE][$authorityId]))
			$hierarchy = $authList[NC_AUTH_MODERATE][$authorityId]['hierarchy'];
		else if(!empty($authList[NC_AUTH_GENERAL][$authorityId]))
			$hierarchy = $authList[NC_AUTH_GENERAL][$authorityId]['hierarchy'];
		else
			$hierarchy = NC_AUTH_OTHER;
		return $hierarchy;
	}
}