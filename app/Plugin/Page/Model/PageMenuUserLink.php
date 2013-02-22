<?php
/**
 * ページメニュー用PageUserLinkモデル
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Plugin.Block.Model
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class PageMenuUserLink extends AppModel {
	public $name = 'PageMenuUserLink';
    public $useTable = 'page_user_links';
    public $alias = 'PageUserLink';

/**
 * 参加情報コピー処理
 *
 * @param array $ins_room_id_arr
 * @param array $copy_room_id_arr
 *
 * @return  boolean
 * @since  v 3.0.0.0
 */
	public function copyPageUserLink($ins_room_id_arr, $copy_room_id_arr) {
		if(count($copy_room_id_arr) > 0) {

			$fields = array('PageUserLink.user_id','PageUserLink.room_id','PageUserLink.authority_id');
			$conditions = array(
				'PageUserLink.room_id' => $copy_room_id_arr
			);
			$page_user_links = $this->find('all', array('fields' => $fields, 'conditions' => $conditions));
			$set_room_id_arr = array();
			$index = 0;
			foreach($ins_room_id_arr as $ins_room_id) {
				$set_room_id_arr[$copy_room_id_arr[$index]] = $ins_room_id;
				$index++;
			}
			foreach($page_user_links as $page_user_link) {
				$this->create();
				$ins_page_user_link = array('PageUserLink' => array(
					'room_id' => $set_room_id_arr[$page_user_link['PageUserLink']['room_id']],
					'user_id' => $page_user_link['PageUserLink']['user_id'],
					'authority_id' => $page_user_link['PageUserLink']['authority_id'],
				));
				if(!$this->save($ins_page_user_link)) {
					return false;
				}
			}
		}
		return true;
	}

/**
 * 権限解除で、PageUserLink,ModuleLinkテーブル削除処理
 *
 * @param integer $room_id
 *
 * @return  boolean
 * @since  v 3.0.0.0
 */
	public function deallocationRoom($room_id) {
		App::uses('ModuleLink', 'Model');
		$ModuleLink = new ModuleLink();

		$conditions = array(
			"PageUserLink.room_id" => $room_id
		);
		if(!$this->deleteAll($conditions)) {
			return false;
		}
		$conditions = array(
			"ModuleLink.room_id" => $room_id
		);
		if(!$ModuleLink->deleteAll($conditions)) {
			return false;
		}
		return true;
	}
}