<?php
/**
 * ItemAuthorityLinkモデル
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Model
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class ItemAuthorityLink extends AppModel
{
/**
 * data[user_authority_id][higher|self|lower][item_id] = $public_flagとして取得
 * @param   string $type
 * @param   array  $params
 * @return  array data[user_authority_id][higher|self|lower][item_id] = $public_flag
 * @since   v 3.0.0.0
 */
	public function findList($type = 'all', $params = array()) {
		return $this->_afterFindList($this->find($type, $params));
	}

	protected function _afterFindList($results) {
		$ret = array();

		$single_flag = false;
		if(isset($results['ItemAuthorityLink'])) {
			$single_flag = true;
			$results[] = $results;
		}

		foreach ($results as $key => $result) {
			$ret[$result['ItemAuthorityLink']['user_authority_id']]['higher'][$result['ItemAuthorityLink']['item_id']] = $result['ItemAuthorityLink']['higher_public_flag'];
			$ret[$result['ItemAuthorityLink']['user_authority_id']]['self'][$result['ItemAuthorityLink']['item_id']] = $result['ItemAuthorityLink']['self_public_flag'];
			$ret[$result['ItemAuthorityLink']['user_authority_id']]['lower'][$result['ItemAuthorityLink']['item_id']] = $result['ItemAuthorityLink']['lower_public_flag'];
		}
		if($single_flag) {
			return $ret[0];
		}
		return $ret;
	}

/**
 * ログインユーザーが閲覧する際の公開フラグを取得
 * @param   array $owner 情報の所有者の会員情報
 * @return  array data[item_id] = $public_flag
 * @since   v 3.0.0.0
 */
	public function findPublicFlagForLoginUser($owner) {
		$loginUser = Configure::read(NC_SYSTEM_KEY.'.user');
		$loginUserId = isset($loginUser['id']) ? $loginUser['id'] : _OFF;

		$params = array(
			'conditions'=>array(
				'ItemAuthorityLink.user_authority_id'=>$owner['Authority']['id']
			)
		);
		$itemAuthorityLinks = $this->findList('all', $params);

		if ($loginUserId === _OFF) {
			$publicFlagKey = 'lower';
		} elseif ($loginUserId == $owner['User']['id']) {
			$publicFlagKey = 'self';
		} elseif ($loginUser['hierarchy'] >= $owner['Authority']['hierarchy']) {
			$publicFlagKey = 'higher';
		} else {
			$publicFlagKey = 'lower';
		}

		return $itemAuthorityLinks[$owner['Authority']['id']][$publicFlagKey];
	}
}