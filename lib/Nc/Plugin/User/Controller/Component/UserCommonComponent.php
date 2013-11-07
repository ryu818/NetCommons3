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
 * 唯一の主担であるかどうかのチェック
 * @param   Model User
 * @param   Model PageUserLinks
 * @return  array(room_id => エラーメッセージ) エラーRoomIDのリスト
 * @since   v 3.0.0.0
 */
	public function isUniqueChief($user, $pageUserLinks) {
		$Authority = ClassRegistry::init('Authority');
		$PageUserLink = ClassRegistry::init('PageUserLink');

		$rets = array();
		$params = array(
			'fields' => array('Authority.id', 'Authority.allow_creating_community', 'Authority.allow_new_participant'),
			'conditions' => array('Authority.hierarchy >=' => NC_AUTH_MIN_CHIEF),
		);
		$bufChiefAuthorities = $Authority->find('all', $params);
		$chiefAuthorities = array();
		$chiefAuthoritiesCreateCommunity = array();
		$chiefAuthoritiesNewParticipant = array();
		foreach($bufChiefAuthorities as $chiefAuthority) {
			$chiefAuthorities[$chiefAuthority[$Authority->alias]['id']] = $chiefAuthority[$Authority->alias]['id'];
			if($chiefAuthority[$Authority->alias]['allow_creating_community'] != NC_ALLOW_CREATING_COMMUNITY_OFF) {
				$chiefAuthoritiesCreateCommunity[$chiefAuthority[$Authority->alias]['id']] = $chiefAuthority[$Authority->alias]['id'];
				if($chiefAuthority[$Authority->alias]['allow_new_participant'] == _ON) {
					$chiefAuthoritiesNewParticipant[$chiefAuthority[$Authority->alias]['id']] = $chiefAuthority[$Authority->alias]['id'];
				}
			}
		}
		unset($bufChiefAuthorities);

		foreach($pageUserLinks as $pageUserLink) {
			if(isset($chiefAuthorities[$pageUserLink['PageUserLink']['authority_id']])) {
				continue;
			}
			if($pageUserLink['Page']['space_type'] == NC_SPACE_TYPE_GROUP) {
				if($pageUserLink['Community']['participate_flag'] != NC_PARTICIPATE_FLAG_ONLY_USER) {
					$bufchiefAuthorities = $chiefAuthoritiesCreateCommunity;
					$errorStr = __d('user', '<span class="bold">[%s]</span>:Chief who can edit the community has to set the one. When do not appoint a chief again, cannot edit it.',
							h($pageUserLink['Page']['page_name']));
				} else {
					$bufchiefAuthorities = $chiefAuthoritiesNewParticipant;
					$errorStr = __d('user', '<span class="bold">[%s]</span>:Chief who "can add a participation member freely" has to set the one, if community of "only participating users". When do not appoint a chief again, cannot edit it.',
							h($pageUserLink['Page']['page_name']));
				}
				$pageUserLinkParams = array(
					'conditions' => array(
						'Authority.id' => $bufchiefAuthorities,
						'PageUserLink.user_id !=' => $pageUserLink['PageUserLink']['user_id'],
						'PageUserLink.room_id' => $pageUserLink['PageUserLink']['room_id'],
					),
					'joins' => array(
						array(
							"type" => "INNER",
							"table" => "users",
							"alias" => "User",
							"conditions" => "`User`.`id`=`PageUserLink`.`user_id`"
						),
						array(
							"type" => "INNER",
							"table" => "authorities",
							"alias" => "Authority",
							"conditions" => "`Authority`.`id`=`User`.`authority_id`"
						)
					)
				);
			} else {
				$bufchiefAuthorities = $chiefAuthorities;
				$errorStr = __d('user', '<span class="bold">[%s]</span>:Chief has to set the one. When do not appoint a chief again, cannot edit it.',
					h($pageUserLink['Page']['page_name']));
				$pageUserLinkParams = array('conditions' => array(
					'PageUserLink.authority_id' => $bufchiefAuthorities,
					'PageUserLink.user_id !=' => $pageUserLink['PageUserLink']['user_id'],
					'PageUserLink.room_id' => $pageUserLink['PageUserLink']['room_id'],
				));
			}

			if($PageUserLink->find('count', $pageUserLinkParams) == 0) {
				$rets[$pageUserLink['PageUserLink']['room_id']] = $errorStr;
			}
		}
		return $rets;
	}
}