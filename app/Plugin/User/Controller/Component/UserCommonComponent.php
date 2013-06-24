<?php
/**
 * UserCommonComponentクラス
 *
 * <pre>
 * 会員管理共通コンポーネント
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Plugin.Blog.Component
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class UserCommonComponent extends Component {
/**
 * 唯一の主坦であるかどうかのチェック
 * @param   Model PageUserLinks
 * @return  array(room_id) 唯一の主坦のIDのリスト
 * @since   v 3.0.0.0
 */
	public function isUniqueChief($pageUserLinks) {
		App::uses('Authority', 'Model');
		App::uses('PageUserLink', 'Model');
		$Authority = new Authority();
		$PageUserLink = new PageUserLink();

		$rets = array();
		$params = array(
			'fields' => array('Authority.id'),
			'conditions' => array('Authority.hierarchy >=' => NC_AUTH_MIN_CHIEF),
		);
		$chiefAuthorities = $Authority->find('list', $params);

		$PageUserLinkParams = array(
			'authority_id' => $chiefAuthorities
		);

		foreach($pageUserLinks as $pageUserLink) {
			if(isset($chiefAuthorities[$pageUserLink['PageUserLink']['authority_id']])) {
				// 主坦として登録されるデータ
				continue;
			}
			$PageUserLinkParams = array('conditions' => array(
				'PageUserLink.authority_id' => $chiefAuthorities,
				'PageUserLink.user_id !=' => $pageUserLink['PageUserLink']['user_id'],
				'PageUserLink.room_id' => $pageUserLink['PageUserLink']['room_id'],
			));
			if($PageUserLink->find('count', $PageUserLinkParams) == 0) {
				$rets[$pageUserLink['PageUserLink']['room_id']] = $pageUserLink['PageUserLink']['room_id'];
			}
		}
		return $rets;
	}
}