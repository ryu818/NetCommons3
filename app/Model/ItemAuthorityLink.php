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
	public $name = 'ItemAuthorityLink';

/**
 * data[user_authority_id][higher|self|lower][item_id] = $public_flagとして取得
 * @param   string $type
 * @param   array  $params
 * @return  array data[user_authority_id][higher|self|lower][item_id] = $public_flag
 * @since   v 3.0.0.0
 */
	public function findList($type = 'all', $params = array()) {
		return $this->afterFindList($this->find($type, $params));
	}

	protected function _afterFindList($results) {
		$ret = array();

		$single_flag = false;
		if(isset($results['ItemsAuthoritiesLink'])) {
			$single_flag = true;
			$results[] = $results;
		}

		foreach ($results as $key => $result) {
			$ret[$result['ItemsAuthoritiesLink']['user_authority_id']]['higher'][$result['ItemsAuthoritiesLink']['item_id']] = $result['ItemsAuthoritiesLink']['higher_public_flag'];
			$ret[$result['ItemsAuthoritiesLink']['user_authority_id']]['self'][$result['ItemsAuthoritiesLink']['item_id']] = $result['ItemsAuthoritiesLink']['self_public_flag'];
			$ret[$result['ItemsAuthoritiesLink']['user_authority_id']]['lower'][$result['ItemsAuthoritiesLink']['item_id']] = $result['ItemsAuthoritiesLink']['lower_public_flag'];
		}
		if($single_flag) {
			return $ret[0];
		}
		return $ret;
	}
}