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
 * @param integer $newRoomId
 * @param integer $copyRoomId
 * @param integer $renameCount 0より大きいならばprefixつきでコミュニティ名称をリネーム
 *
 * @return  boolean
 * @since  v 3.0.0.0
 */
	public function copyCommunity($newRoomId, $copyRoomId, $renameCount = 0) {
		$CommunityLang = ClassRegistry::init('CommunityLang');
		$CommunityTag = ClassRegistry::init('CommunityTag');
		$UploadLink = ClassRegistry::init('UploadLink');
		$uploadLinkFields = array(
			'UploadLink.upload_id','UploadLink.plugin','UploadLink.content_id','UploadLink.model_name',
			'UploadLink.field_name', 'UploadLink.access_hierarchy', 'UploadLink.is_use', 'UploadLink.download_password', 'UploadLink.check_component_action'
		);
		$conditions = array(
			'Community.room_id' => $copyRoomId
		);
		$community = $this->find('first', $conditions);
		if(!isset($community['Community'])) {
			return false;
		}
		$oldCommunityId = $community['Community']['id'];

		unset($community['Community']['id']);
		$community['Community']['room_id'] = $newRoomId;
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

		if($community['Community']['is_upload']) {
			// Community.photoのuploadLinkコピー
			$uploadLinks = $UploadLink->find('all', array(
				'fields' => $uploadLinkFields,
				'conditions' => array(
					'UploadLink.plugin' => 'Page',
					'UploadLink.model_name' => 'Community',
					'UploadLink.field_name' => 'photo',
					'UploadLink.unique_id' => $oldCommunityId,
				),
				'recursive' => -1,
				'order' => array('UploadLink.id'),
			));
			foreach($uploadLinks as $uploadLink) {
				$UploadLink->create();
				$uploadLink['UploadLink']['unique_id'] = $this->id;
				if(!$UploadLink->save($uploadLink)) {
					return false;
				}
			}
		}

		$fields = array('CommunityLang.lang','CommunityLang.community_name','CommunityLang.summary','CommunityLang.revision_group_id');
		$conditions = array(
			'CommunityLang.room_id' => $copyRoomId
		);
		$communityLangs = $CommunityLang->find('all', array('fields' => $fields, 'conditions' => $conditions));
		foreach($communityLangs as $communityLang) {
			// Revisionコピー
			if($communityLang['CommunityLang']['revision_group_id'] > 0) {
				$Revision = ClassRegistry::init('Revision');
				$revisions = $Revision->find('all', array(
					'fields' => array('Revision.pointer','Revision.is_approved_pointer','Revision.revision_name','Revision.content_id','Revision.content'),
					'conditions' => array('Revision.group_id' => $communityLang['CommunityLang']['revision_group_id']),
					'recursive' => -1,
					'order' => array('Revision.id'),
				));
				$newGroupId = 0;
				foreach($revisions as $revision) {
					$Revision->create();
					$revision['Revision']['group_id'] = $newGroupId;
					if(!$Revision->save($revision)) {
						return false;
					}
					if($newGroupId == 0) {
						$newGroupId = $Revision->id;
						if(!$Revision->saveField('group_id', $newGroupId)) {
							return false;
						}
					}
				}

				// RevisionのuploadLinkコピー
				$uploadLinks = $UploadLink->find('all', array(
					'fields' => $uploadLinkFields,
					'conditions' => array(
						'UploadLink.plugin' => 'Page',
						'UploadLink.model_name' => 'Revision',
						'UploadLink.field_name' => 'content',
						'UploadLink.unique_id' => $communityLang['CommunityLang']['revision_group_id'],
					),
					'recursive' => -1,
					'order' => array('UploadLink.id'),
				));
				foreach($uploadLinks as $uploadLink) {
					$UploadLink->create();
					$uploadLink['UploadLink']['unique_id'] = $newGroupId;
					if(!$UploadLink->save($uploadLink)) {
						return false;
					}
				}
				$communityLang['CommunityLang']['revision_group_id'] = $newGroupId;
			}

			$CommunityLang->create();
			$communityLang['CommunityLang']['room_id'] = $newRoomId;
			if($renameCount > 0) {
				$community_name = preg_replace('/^\[copy[0-9]+\](.*)/', "$1", $communityLang['CommunityLang']['community_name']);
				$communityLang['CommunityLang']['community_name'] = __d('pages', '[copy%s]%s', $renameCount, $community_name) ;
			}
			if(!$CommunityLang->save($communityLang)) {
				return false;
			}

			$tagValues = $CommunityTag->findCommaDelimitedTags($copyRoomId, $communityLang['CommunityLang']['lang']);
			if($tagValues != '') {
				if(!$CommunityTag->saveTags($newRoomId, $communityLang['CommunityLang']['lang'], $tagValues)) {
					return false;
				}
			}
		}

		return true;
	}
}