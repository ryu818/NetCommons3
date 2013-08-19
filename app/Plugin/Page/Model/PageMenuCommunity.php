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
class PageMenuCommunity extends AppModel {
	public $useTable = 'communities';
	public $alias = 'Community';

/**
 * 参加情報コピー処理
 *
 * @param integer $new_room_id
 * @param integer $copy_room_id
 * @param integer $rename_count 0より大きいならばprefixつきでコミュニティ名称をリネーム
 *
 * @return  boolean
 * @since  v 3.0.0.0
 */
	public function copyCommunity($new_room_id, $copy_room_id, $rename_count = 0) {
		$CommunityLang = ClassRegistry::init('CommunityLang');
		$CommunityTag = ClassRegistry::init('CommunityTag');
		$conditions = array(
			'Community.room_id' => $copy_room_id
		);
		$community = $this->find('first', $conditions);
		if(!isset($community['Community'])) {
			return false;
		}

		// TODO: upload_idが0でないならば、upload_idを新規に登録しなおす必要がある。
		// 現状、アップロード処理がないため未作成。
		unset($community['Community']['id']);
		$community['Community']['room_id'] = $new_room_id;
		unset($community['Community']['created']);
		unset($community['Community']['created_user_id']);
		unset($community['Community']['created_user_name']);
		unset($community['Community']['modified']);
		unset($community['Community']['modified_user_id']);
		unset($community['Community']['modified_user_name']);

		$this->create();
		if(!$this->save($community)) {
			return false;
		}

		$fields = array('CommunityLang.lang','CommunityLang.community_name','CommunityLang.summary','CommunityLang.description');
		$conditions = array(
			'CommunityLang.room_id' => $copy_room_id
		);
		$community_langs = $CommunityLang->find('all', array('fields' => $fields, 'conditions' => $conditions));
		foreach($community_langs as $community_lang) {
			$CommunityLang->create();
			$community_lang['CommunityLang']['room_id'] = $new_room_id;
			if($rename_count > 0) {
				$community_name = preg_replace('/^\[copy[0-9]+\](.*)/', "$1", $community_lang['CommunityLang']['community_name']);
				$community_lang['CommunityLang']['community_name'] = __d('pages', '[copy%s]%s', $rename_count, $community_name) ;
			}
			if(!$CommunityLang->save($community_lang)) {
				return false;
			}
		}

		$fields = array('CommunityTag.tag_id','CommunityTag.tag_value','CommunityTag.display_sequence');
		$conditions = array(
			'CommunityTag.room_id' => $copy_room_id
		);
		$community_tags = $CommunityTag->find('all', array('fields' => $fields, 'conditions' => $conditions));
		foreach($community_tags as $community_tag) {
			$CommunityTag->create();
			$community_tag['CommunityTag']['room_id'] = $new_room_id;
			if(!$CommunityTag->save($community_tag)) {
				return false;
			}
		}

		// TODO:tagsテーブルの更新処理未作成

		return true;
	}
}