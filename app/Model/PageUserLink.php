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
	public $name = 'PageUserLink';

	public $actsAs = array('Page');

/**
 * 参加会員修正時のPageUserLink登録・更新処理
 * @param  Model Page  $page
 * @param  boolean     $is_participant_only  参加会員のみ falseの場合、参加者の追加も許す
 * @param  array       $post_page_user_links 変更後Sessionデータ array(0 => array('user_id', 'authority_id'))
 * @param  array       $pre_page_user_links  変更前DBデータ array(0 => 'PageUserLink' => array('user_id', 'authority_id')))
 * @param  array       $pre_parent_page_user_links 変更前親ルームDBデータ array(0 => 'PageUserLink' => array('user_id', 'authority_id')))
 * @param  boolean     $is_participant_only  参加会員のみ falseの場合、参加者の追加も許す
 * @return boolean
 * @since  v 3.0.0.0
 */
	public function saveParticipant($page, $post_page_user_links, $pre_page_user_links, $pre_parent_page_user_links = null, $is_participant_only = null) {
		$room_id = $page['Page']['id'];
		$default_authority_id = $this->getDefaultAuthorityId($page, true);
		$del_user_id_arr = array();
		$pre_user_id_arr = array();
		$post_user_id_arr = array();
		$pre_parent_user_id_arr = array();

		foreach($post_page_user_links as $page_user_link) {
			$post_user_id_arr[$page_user_link['user_id']] = $page_user_link['authority_id'];
		}
		foreach($pre_page_user_links as $page_user_link) {
			$pre_user_id_arr[$page_user_link['PageUserLink']['user_id']] = $page_user_link['PageUserLink']['authority_id'];
		}
		if(isset($pre_parent_page_user_links) && isset($is_participant_only) && $is_participant_only) {
			// 子グループ
			foreach($pre_parent_page_user_links as $page_user_link) {
				$pre_parent_user_id_arr[$page_user_link['PageUserLink']['user_id']] = $page_user_link['PageUserLink']['authority_id'];
			}
		} else if(isset($is_participant_only) && $is_participant_only){
			$pre_parent_user_id_arr = $pre_user_id_arr;
		} else if(isset($is_participant_only) && !$is_participant_only){
			$pre_parent_user_id_arr = $post_user_id_arr;
		} else {
			return false;
		}

		foreach($pre_parent_user_id_arr as $user_id => $parent_authority_id) {
			$authority_id = isset($post_user_id_arr[$user_id]) ? intval($post_user_id_arr[$user_id]) : null;
			$pre_authority_id = isset($pre_user_id_arr[$user_id]) ? intval($pre_user_id_arr[$user_id]) : null;

			if(!isset($authority_id) || $pre_authority_id === $authority_id || ($authority_id == $default_authority_id && $authority_id != NC_AUTH_OTHER_ID)) {
				// 既にあるデータか、default値といっしょなので、Insertしない。
				if($authority_id == $default_authority_id && $authority_id != $pre_authority_id) {
					// delete
					$conditions = array(
						"PageUserLink.user_id" => $user_id,
						"PageUserLink.room_id" => $room_id
					);
					if(!$this->deleteAll($conditions)) {
						return false;
					}
				}
				continue;
			}
			if($page['Page']['space_type'] == NC_SPACE_TYPE_PUBLIC && $authority_id == NC_AUTH_OTHER_ID) {
				// パブリックで不参加にはできない。
				continue;
			}
			if($authority_id == NC_AUTH_OTHER_ID) {
				$del_user_id_arr[] = $user_id;
			}
			if($authority_id == NC_AUTH_OTHER_ID && $default_authority_id == NC_AUTH_OTHER_ID) {
				// 会員のみ参加ルームで不参加にしたため、削除
				$conditions = array(
					"PageUserLink.user_id" => $user_id,
					"PageUserLink.room_id" => $room_id
				);
				if(!$this->deleteAll($conditions)) {
					return false;
				}
			} else if(isset($pre_authority_id)) {
				// 更新
				$fields = array(
					'authority_id' => $authority_id
				);
				$conditions = array(
					'room_id' => $room_id,
					'user_id' => $user_id
				);
				if(!$this->updateAll($fields, $conditions)) {
					return false;
				}
			} else {
				// 登録
				$this->create();
				$page_user_link = array(
					'PageUserLink' => array(
						'room_id' => $room_id,
						'user_id' => $user_id,
						'authority_id' => $authority_id
					)
				);
				if(!$this->save($page_user_link)) {
					return false;
				}
			}
		}

		//
		// 公開ルームではないならば、子供のルームを求め不参加会員を削除する。
		// 		親ルームで参加していた会員を不参加に変更すると、親ルームには参加していないが、子ルームに参加している
		//		状態になってしまうため
		// 公開ルームならば、子供のルームも不参加として登録
		if(count($del_user_id_arr) == 0) {
			return true;
		}
		App::uses('Page', 'Model');
		$Page = new Page();
		$room_id_list_arr = array();
		$child_pages = $Page->findChilds('all', $page);
		if(count($child_pages) > 0) {
			foreach($child_pages as $child_page) {
				if($child_page['Page']['id'] == $child_page['Page']['room_id']) {
					$room_id_list_arr[] = $child_page['Page']['id'];
				}
			}
		}
		if(count($room_id_list_arr) == 0) {
			return true;
		}
		if($default_authority_id == NC_AUTH_OTHER_ID) {
			// 非公開ルーム
			$conditions = array(
				"PageUserLink.user_id" => $del_user_id_arr,
				"PageUserLink.room_id" => $room_id_list_arr
			);
			$this->deleteAll($conditions);
		} else {
			// 公開ルーム
			foreach($room_id_list_arr as $child_room_id) {
				foreach($del_user_id_arr as $user_id) {
					$params = array(
						'fields' => array(
								'PageUserLink.id'
						),
						'conditions' => array(
								'PageUserLink.room_id' => $child_room_id,
								'PageUserLink.user_id' => $user_id
						),
					);

					$page_user_link = $this->find('first', $params);
					if(!isset($page_user_link['PageUserLink'])) {
						// 登録
						$this->create();
						$page_user_link = array(
							'PageUserLink' => array(
								'room_id' => $child_room_id,
								'user_id' => $user_id,
								'authority_id' => NC_AUTH_OTHER_ID
							)
						);
						if(!$this->save($page_user_link)) {
							return false;
						}
					} else {
						// 更新
						$fields = array(
							'authority_id' => NC_AUTH_OTHER_ID
						);
						$conditions = array(
							'room_id' => $child_room_id,
							'user_id' => $user_id
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