<?php
/**
 * UserItemAuthorityLinkモデル
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Model
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class UserItemAuthorityLink extends AppModel
{
/**
 * data[user_authority_id][item_id][edit_lower_hierarchy|show_lower_hierarchy] = hierarchyとして取得
 * @param   string $type
 * @param   array  $params
 * @return  array data[user_authority_id][item_id][edit_lower_hierarchy|show_lower_hierarchy] = hierarchy
 * @since   v 3.0.0.0
 */
	public function findList($type = 'all', $params = array()) {
		return $this->_afterFindList($this->find($type, $params));
	}

	protected function _afterFindList($results) {
		$ret = array();

		$singleFlag = false;
		if(isset($results['UserItemAuthorityLink'])) {
			$singleFlag = true;
			$results[] = $results;
		}

		foreach ($results as $key => $result) {
			$userAuthorityId = $result['UserItemAuthorityLink']['user_authority_id'];
			$itemId = $result['UserItemAuthorityLink']['user_item_id'];
			$ret[$userAuthorityId][$itemId]['edit_lower_hierarchy'] = $result['UserItemAuthorityLink']['edit_lower_hierarchy'];
			$ret[$userAuthorityId][$itemId]['show_lower_hierarchy'] = $result['UserItemAuthorityLink']['show_lower_hierarchy'];
		}
		if($singleFlag) {
			return $ret[0];
		}
		return $ret;
	}

/**
 * ログインユーザーが閲覧する際の公開フラグを取得
 * @param   array $owner 情報の所有者の会員情報
 * 	$ownerが空ならば、全部の権限で閲覧権限がないならばfalse,1つでも閲覧権限があればtrue
 * @return  array data[item_id] = boolean
 *
 * @since   v 3.0.0.0
 */
	public function findIsPublicForLoginUser($owner = null) {
		$Authority = ClassRegistry::init('Authority');

		$loginUser = Configure::read(NC_SYSTEM_KEY.'.user');
		$loginUserId = isset($loginUser['id']) ? $loginUser['id'] : _OFF;
		if($loginUserId === _OFF) {
			$loginHierarchy = NC_AUTH_GUEST;
		} else {
			$loginHierarchy = $loginUser['hierarchy'];
		}

		if(isset($owner)) {
			$userAuthorityId = $Authority->getUserAuthorityId($owner['Authority']['hierarchy']);
			$params = array(
				'conditions'=>array(
					'UserItemAuthorityLink.user_authority_id' => $userAuthorityId
				)
			);
			$itemAuthorityLinks = $this->findList('all', $params);

			$rets = array();
			foreach($itemAuthorityLinks[$userAuthorityId] as $itemId => $itemAuthorityLink) {
				if($loginHierarchy >= $itemAuthorityLink['show_lower_hierarchy']) {
					$rets[$itemId] = true;
				} else {
					$rets[$itemId] = false;
				}
			}
		} else {
			$bufUserItemAuthorityLinks = $this->findList();
			$rets = array();
			foreach($bufUserItemAuthorityLinks as $userAuthorityId => $itemAuthorityLinks) {
				foreach($itemAuthorityLinks as $itemId => $itemAuthorityLink) {
					if(isset($rets[$itemId]) && $rets[$itemId]) {
						continue;
					}
					if($loginHierarchy >= $itemAuthorityLink['show_lower_hierarchy']) {
						$rets[$itemId] = true;
					} else {
						$rets[$itemId] = false;
					}
				}
			}
		}
		return $rets;
	}
}