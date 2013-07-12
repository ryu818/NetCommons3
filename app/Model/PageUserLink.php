<?php
/**
 * PageUserLinkモデル
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Model
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class PageUserLink extends AppModel
{
	public $actsAs = array('Page', 'Auth' => array('joins' => false, 'afterFind' => false));

/**
 * 参加会員修正時のPageUserLink登録・更新処理
 * @param  Model Page  $page
 * @param  boolean     $isParticipantOnly  参加会員のみ falseの場合、参加者の追加も許す
 * @param  array       $postPageUserLinks 変更後Sessionデータ array(0 => array('user_id', 'authority_id'))
 * @param  array       $prePageUserLinks  変更前DBデータ array(0 => 'PageUserLink' => array('user_id', 'authority_id')))
 * @param  array       $preParentPageUserLinks 変更前親ルームDBデータ array(0 => 'PageUserLink' => array('user_id', 'authority_id')))
 * @param  integer     $participantType
 * 						0:参加者のみ表示　
 * 						1:すべての会員表示（変更不可)
 * 						2:すべての会員表示（PageUserLinkにない会員は、default値と不参加のみ変更可）
 * 						3:すべての会員表示（変更可）
 * @return boolean
 * @since  v 3.0.0.0
 */
	public function saveParticipant($page, $postPageUserLinks, $prePageUserLinks, $preParentPageUserLinks = null, $participantType = null) {
		$roomId = $page['Page']['id'];
		$delUserIdArr = array();
		$preUserIdArr = array();
		$postUserIdArr = array();
		$preParentUserIdArr = array();
		$bufPreParentUserIdArr = array();

		foreach($postPageUserLinks as $pageUserLink) {
			$postUserIdArr[$pageUserLink['user_id']] = $pageUserLink['authority_id'];
		}
		foreach($prePageUserLinks as $pageUserLink) {
			$preUserIdArr[$pageUserLink['PageUserLink']['user_id']] = $pageUserLink['PageUserLink']['authority_id'];
		}
		if(isset($preParentPageUserLinks)) {
			foreach($preParentPageUserLinks as $pageUserLink) {
				$bufPreParentUserIdArr[$pageUserLink['PageUserLink']['user_id']] = $pageUserLink['PageUserLink']['authority_id'];
			}
		}
		if(isset($preParentPageUserLinks) && isset($participantType) && $participantType == 0) {
			// 子グループ
			//foreach($preParentPageUserLinks as $pageUserLink) {
			//	$preParentUserIdArr[$pageUserLink['PageUserLink']['user_id']] = $pageUserLink['PageUserLink']['authority_id'];
			//}
			$preParentUserIdArr = $bufPreParentUserIdArr;
		} else if(isset($participantType) && $participantType == 0){
			$preParentUserIdArr = $preUserIdArr;
		} else if(isset($participantType) && $participantType != 0){
			$preParentUserIdArr = $postUserIdArr;
		} else {
			return false;
		}

		unset($page['Authority']);

		foreach($preParentUserIdArr as $userId => $parentAuthorityId) {
			$authorityId = isset($postUserIdArr[$userId]) ? intval($postUserIdArr[$userId]) : null;
			$preAuthorityId = isset($preUserIdArr[$userId]) ? intval($preUserIdArr[$userId]) : null;
			$page['PageUserLink']['user_id'] = $userId;
			$defaultAuthorityId = $this->getDefaultAuthorityId($page);
			if(!isset($authorityId) || $preAuthorityId === $authorityId || ($authorityId == $defaultAuthorityId && $authorityId != NC_AUTH_OTHER_ID)) {
				// 既にあるデータか、default値といっしょなので、Insertしない。
				if($authorityId == $defaultAuthorityId && $authorityId != $preAuthorityId) {
					// delete
					$conditions = array(
						"PageUserLink.user_id" => $userId,
						"PageUserLink.room_id" => $roomId
					);
					if(!$this->deleteAll($conditions)) {
						return false;
					}
				}
				continue;
			}
			if($page['Page']['space_type'] == NC_SPACE_TYPE_PUBLIC && $authorityId == NC_AUTH_OTHER_ID) {
				// パブリックで不参加にはできない。
				continue;
			}
			if(!isset($bufPreParentUserIdArr[$userId])) {
				if($preAuthorityId == null) {
					if($participantType <= 1) {
						// 変更不可
						continue;
					} else if($participantType == 2 && $authorityId != NC_AUTH_OTHER_ID) {
						continue;
					}
				} else if($preAuthorityId != null && $participantType == 2 && !($authorityId == NC_AUTH_OTHER_ID || $authorityId == $defaultAuthorityId)) {
					// default値と不参加のみ変更可
					continue;
				}
			}

			if($authorityId == NC_AUTH_OTHER_ID) {
				$delUserIdArr[] = $userId;
			}
			if($authorityId == NC_AUTH_OTHER_ID && $defaultAuthorityId == NC_AUTH_OTHER_ID) {
				// 会員のみ参加ルームで不参加にしたため、削除
				$conditions = array(
					"PageUserLink.user_id" => $userId,
					"PageUserLink.room_id" => $roomId
				);
				if(!$this->deleteAll($conditions)) {
					return false;
				}
			} else if(isset($preAuthorityId)) {
				// 更新
				$fields = array(
					'authority_id' => $authorityId
				);
				$conditions = array(
					'room_id' => $roomId,
					'user_id' => $userId
				);
				if(!$this->updateAll($fields, $conditions)) {
					return false;
				}
			} else {
				// 登録
				$this->create();
				$pageUserLink = array(
					'PageUserLink' => array(
						'room_id' => $roomId,
						'user_id' => $userId,
						'authority_id' => $authorityId
					)
				);
				if(!$this->save($pageUserLink)) {
					return false;
				}
			}
		}

		//
		// 公開ルームではないならば、子供のルームを求め不参加会員を削除する。
		// 		親ルームで参加していた会員を不参加に変更すると、親ルームには参加していないが、子ルームに参加している
		//		状態になってしまうため
		// 公開ルームならば、子供のルームも不参加として登録
		if(count($delUserIdArr) == 0) {
			return true;
		}
		App::uses('Page', 'Model');
		$Page = new Page();
		$roomIdListArr = array();
		$child_pages = $Page->findChilds('all', $page);
		if(count($child_pages) > 0) {
			foreach($child_pages as $child_page) {
				if($child_page['Page']['id'] == $child_page['Page']['room_id']) {
					$roomIdListArr[] = $child_page['Page']['id'];
				}
			}
		}
		if(count($roomIdListArr) == 0) {
			return true;
		}
		if($defaultAuthorityId == NC_AUTH_OTHER_ID) {
			// 非公開ルーム
			$conditions = array(
				"PageUserLink.user_id" => $delUserIdArr,
				"PageUserLink.room_id" => $roomIdListArr
			);
			$this->deleteAll($conditions);
		} else {
			// 公開ルーム
			foreach($roomIdListArr as $child_room_id) {
				foreach($delUserIdArr as $userId) {
					$params = array(
						'fields' => array(
								'PageUserLink.id'
						),
						'conditions' => array(
								'PageUserLink.room_id' => $child_room_id,
								'PageUserLink.user_id' => $userId
						),
					);

					$pageUserLink = $this->find('first', $params);
					if(!isset($pageUserLink['PageUserLink'])) {
						// 登録
						$this->create();
						$pageUserLink = array(
							'PageUserLink' => array(
								'room_id' => $child_room_id,
								'user_id' => $userId,
								'authority_id' => NC_AUTH_OTHER_ID
							)
						);
						if(!$this->save($pageUserLink)) {
							return false;
						}
					} else {
						// 更新
						$fields = array(
							'authority_id' => NC_AUTH_OTHER_ID
						);
						$conditions = array(
							'room_id' => $child_room_id,
							'user_id' => $userId
						);
						if(!$this->updateAll($fields, $conditions)) {
							return false;
						}
					}
				}
			}
		}

		return true;
	}
}