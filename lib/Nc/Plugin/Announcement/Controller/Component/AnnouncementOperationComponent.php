<?php
/**
 * AnnouncementOperationComponentクラス
 *
 * <pre>
 * お知らせ用削除、コピー、移動、ショートカット等操作クラス
 * 削除用関数等は、親から呼ばれるため、Model等のクラスは、このクラス内で完結している
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Plugin.Announcement.Component
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class AnnouncementOperationComponent extends Component {

	public $Content = null;
	public $Revision = null;

	public $Announcement = null;
	public $AnnouncementEdit = null;

/**
 * 初期処理
 *
 * @param   Controller $controller Controller with components to startup
 * @return  void
 * @since   v 3.0.0.0
 */
	public function startup(Controller $controller) {
		$this->Content = ClassRegistry::init('Content');
		$this->Revision = ClassRegistry::init('Revision');
		$this->Archive = ClassRegistry::init('Archive');
		$this->Announcement = ClassRegistry::init('Announcement.Announcement');
		$this->AnnouncementEdit = ClassRegistry::init('Announcement.AnnouncementEdit');
		$this->Announcement->unbindModel( array( 'belongsTo' => array_keys( $this->Announcement->belongsTo ) ) );
	}

/**
 * ブロック削除実行時に呼ばれる関数
 *
 * @param   array Model Block   削除ブロック
 * @param   array Model Content 削除コンテンツ
 * @param   array Model Page    削除先ページ
 * @return  boolean
 * @since   v 3.0.0.0
 */
	//	public function delete_block($block, $content, $toPage) {
	//		return true;
	//	}

/**
 * コンテンツ削除時に呼ばれる関数
 *
 * @param   array Model Content 削除コンテンツ $content
 * @return  boolean
 * @since   v 3.0.0.0
 */
// 	public function delete($content) {
// 		return true;
// 	}

/**
 * ショートカット実行時に呼ばれる関数
 *
 * @param   array Model Block   移動元ブロック
 * @param   array Model Block   移動先ブロック
 * @param   array Model Content 移動元コンテンツ
 * @param   array Model Content 移動先コンテンツ
 * @param   array Model Page    移動元ページ
 * @param   array Model Page    移動先ページ
 * @return  boolean
 * @since   v 3.0.0.0
 */
//	public function shortcut($fromBlock, $toBlock, $fromContent, $toContent, $fromPage, $toPage) {
//		return true;
//	}


/**
 * コピー(ペースト)実行時に呼ばれる関数
 *
 * @param   array Model Block   移動元ブロック
 * @param   array Model Block   移動先ブロック
 * @param   array Model Content 移動元コンテンツ
 * @param   array Model Content 移動先コンテンツ
 * @param   array Model Page    移動元ページ
 * @param   array Model Page    移動先ページ
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function paste($fromBlock, $toBlock, $fromContent, $toContent, $fromPage, $toPage) {
		$tables = array('Revision', 'Announcement', 'AnnouncementEdit', 'Archive');
		$newGroupIdArr = array();
		$newArchiveUniqueIdArr = array();
		$groupId = $newGroupId = 0;
		foreach($tables as $table) {
			$condition = array($table.'.content_id' => $fromContent['Content']['master_id']);
			$datas = $this->{$table}->find('all', array(
				'conditions' => $condition,
				'recursive' => -1
			));

			foreach($datas as $data) {

				if($table == 'Revision') {
					if($groupId != $data[$table]['group_id']) {
						$groupId = $data[$table]['group_id'];
						$data[$table]['group_id'] = 0;
					} else {
						$data[$table]['group_id'] = $newGroupId;
					}
					} else if($table == 'Announcement') {
					$data[$table]['revision_group_id'] = $newGroupIdArr[$data[$table]['revision_group_id']];
				} else if($table == 'Archive') {
					$data[$table]['parent_id'] = $data[$table]['unique_id'] = $newArchiveUniqueIdArr[$data[$table]['unique_id']];
				}
				$id = $data[$table]['id'];
				unset($data[$table]['id']);
				$data[$table]['content_id'] = $toContent['Content']['id'];
				$this->{$table}->create();
				if(!$this->{$table}->save($data)) {
					return false;
				}

				if($table == 'Revision' && $data[$table]['group_id'] == 0) {
					$newGroupId = $this->{$table}->id;
					$newGroupIdArr[$groupId] = $newGroupId;
				} else if($table == 'Announcement') {
					$newArchiveUniqueIdArr[$id] = $this->{$table}->id;
				}
			}
		}
		return true;
	}

/**
 * ブロック追加実行時に呼ばれる関数
 *
 * @param   array Model Block   追加ブロック
 * @param   array Model Content 追加コンテンツ
 * @param   array Model Page    追加先ページ
 * @return  boolean
 * @since   v 3.0.0.0
 */
//	public function add_block($block, $content, $toPage) {
//		return true;
//	}

/**
 * 別ルームに移動実行時に呼ばれる関数
 *
 * @param   array Model Block   移動元ブロック
 * @param   array Model Block   移動先ブロック
 * @param   array Model Content 移動元コンテンツ
 * @param   array Model Content 移動先コンテンツ
 * @param   array Model Page    移動元ページ
 * @param   array Model Page    移動先ページ
 * @return  boolean
 * @since   v 3.0.0.0
 */
//	public function move($fromBlock, $toBlock, $fromContent, $toContent, $fromPage, $toPage) {
//		return true;
//	}
}