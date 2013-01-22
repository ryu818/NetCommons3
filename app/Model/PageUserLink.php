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

/**
 * 参加会員修正時のPageUserLink登録・更新処理
 * @param  Model Page  $page
 * @param  boolean     $is_participant_only
 * @param  array       $pre_page_user_links  変更前DBデータ array(0 => 'PageUserLink' => array('user_id', 'authority_id')))
 * @param  array       $post_page_user_links 変更後Sessionデータ array(0 => array('user_id', 'authority_id'))
 * @return boolean
 * @since  v 3.0.0.0
 */
	public function saveParticipant($page, $is_participant_only, $pre_page_user_links, $post_page_user_links) {
		$room_id = $page['Page']['room_id'];
		$default_authority_id = $this->getDefaultAuthorityId($page);

		foreach($pre_page_user_links as $page_user_link) {
			$pre_user_id_arr[$page_user_link['PageUserLink']['user_id']] = $page_user_link['PageUserLink']['authority_id'];
		}
		foreach($post_page_user_links as $page_user_link) {
			$post_user_id_arr[$page_user_link['user_id']] = $page_user_link['authority_id'];
		}

		if(!$is_participant_only) {
			// 追加を許す
			foreach($post_page_user_links as $page_user_link) {
				// Sessionデータ
				// 追加・削除
				if(isset($pre_user_id_arr[$page_user_link['user_id']]) || $page_user_link['authority_id'] == $default_authority_id) {
					// 既にあるデータか、default値といっしょなので、Insertしない。
					continue;
				}
				// insert
				$this->create();
				$page_user_link['room_id'] = $room_id;
				if(!$this->save($page_user_link)) {
					return false;
				}
			}
		}
		foreach($pre_page_user_links as $page_user_link) {
			// DBデータ(既存データ)
			$pre_authority_id = $page_user_link['PageUserLink']['authority_id'];
			if(isset($post_user_id_arr[$page_user_link['PageUserLink']['user_id']])) {
				$page_user_link['PageUserLink']['authority_id'] = $post_user_id_arr[$page_user_link['PageUserLink']['user_id']];
			}
			if($default_authority_id == $page_user_link['PageUserLink']['authority_id']) {
				// default値 - 削除処理
				if(!$this->delete($page_user_link['PageUserLink']['id'])) {
					return false;
				}
				continue;
			}
			if($pre_authority_id == $page_user_link['PageUserLink']['authority_id']) {
				// 変更なし
				continue;
			}
			// update
			$this->create();
			$page_user_link['PageUserLink']['room_id'] = $room_id;
			if(!$this->save($page_user_link)) {
				return false;
			}
		}

		//
		// パブリックルームではないならば、子供のルームを求め不参加会員を削除する。
		//
		// TODO:後に作成
		/*if(count($del_user_id) > 0 && $page['Page']['space_type'] != NC_SPACE_TYPE_PUBLIC) {
			// 子供のルーム取得
			$room_id_list_arr = $this->Page->findLowPage($page, _ON);
			if(count($room_id_list_arr) > 0) {
				$conditions = array(
						"PageUserLink.user_id" => $del_user_id,
						"PageUserLink.room_id" => $room_id_list_arr
				);
				$this->PageUserLink->deleteAll($conditions);
			}
		}*/

		return true;
	}

/**
 * ページにおけるデフォルトの権限を取得
 * @param  Model Page  $page

 * @return integer authority_id
 * @since  v 3.0.0.0
 */
	public function getDefaultAuthorityId($page) {
		$authority_id = NC_AUTH_OTHER;
		if($page['Page']['space_type'] == NC_SPACE_TYPE_PUBLIC) {
			$authority_id = Configure::read(NC_CONFIG_KEY.'.default_entry_public_authority_id');
		} else if($page['Page']['space_type'] == NC_SPACE_TYPE_MYPORTAL) {
			$authority_id = Configure::read(NC_CONFIG_KEY.'.default_entry_myportal_authority_id');
		} else if($page['Page']['space_type'] == NC_SPACE_TYPE_PRIVATE) {
			$authority_id = Configure::read(NC_CONFIG_KEY.'.default_entry_private_authority_id');
		} else {
			App::uses('Community', 'Model');
			$Community = new Community();
			$params = array(
				'fields' => array(
					'Community.publication_range_flag'
				),
				'conditions' => array('room_id' => $page['Page']['room_id']),
			);
			$current_community = $Community->find('first', $params);
			if($current_community['Community']['publication_range_flag'] == NC_PUBLICATION_RANGE_FLAG_ONLY_USER) {
				$authority_id = NC_AUTH_OTHER;
			} else {
				$authority_id = Configure::read(NC_CONFIG_KEY.'.default_entry_group_authority_id');
			}
		}
		return $authority_id;
	}
}