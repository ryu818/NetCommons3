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
class AnnouncementOperationComponent extends Object {

	public $Content = null;
	public $Revision = null;

	public $Announcement = null;
	public $AnnouncementEdit = null;

/**
 * 初期処理
 *
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function startup() {
		App::uses('Content', 'Model');
		App::uses('Revision', 'Model');
		App::uses('Announcement', 'Announcement.Model');
		App::uses('AnnouncementEdit', 'Announcement.Model');

		$this->Content = new Content();
		$this->Revision = new Revision();
		$this->Announcement = new Announcement();
		$this->AnnouncementEdit = new AnnouncementEdit();
		$this->Announcement->unbindModel( array( 'belongsTo' => array_keys( $this->Announcement->belongsTo ) ) );
	}

/**
 * ブロック削除実行時に呼ばれる関数
 *
 * @param   Model Block   削除ブロック
 * @param   Model Content 削除コンテンツ
 * @param   Model Page    削除先ページ
 * @return  boolean
 * @since   v 3.0.0.0
 */
	//	public function delete_block($block, $content, $to_page) {
	//		return true;
	//	}

/**
 * コンテンツ削除時に呼ばれる関数
 *
 * @param   Model Content 削除コンテンツ $content
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function delete($content) {
		if(isset($content['Content'])) {
			$conditions = array('Announcement.content_id' => $content['Content']['master_id']);
			if(!$this->Announcement->deleteAll($conditions)) {
				return false;
			}
			$conditions = array('AnnouncementEdit.content_id' => $content['Content']['master_id']);
			if(!$this->AnnouncementEdit->deleteAll($conditions)) {
				return false;
			}
			$conditions = array('Revision.content_id' => $content['Content']['master_id']);
			if(!$this->Revision->deleteAll($conditions)) {
				return false;
			}
		}
		return true;
	}

/**
 * ショートカット実行時に呼ばれる関数
 *
 * @param   Model Block   移動元ブロック
 * @param   Model Block   移動先ブロック
 * @param   Model Content 移動元コンテンツ
 * @param   Model Content 移動先コンテンツ
 * @param   Model Page    移動元ページ
 * @param   Model Page    移動先ページ
 * @return  boolean
 * @since   v 3.0.0.0
 */
//	public function shortcut($from_block, $to_block, $from_content, $to_content, $from_page, $to_page) {
//		return true;
//	}


/**
 * コピー(ペースト)実行時に呼ばれる関数
 *
 * @param   Model Block   移動元ブロック
 * @param   Model Block   移動先ブロック
 * @param   Model Content 移動元コンテンツ
 * @param   Model Content 移動先コンテンツ
 * @param   Model Page    移動元ページ
 * @param   Model Page    移動先ページ
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function paste($from_block, $to_block, $from_content, $to_content, $from_page, $to_page) {
		$tables = array('Revision', 'Announcement', 'AnnouncementEdit');
		$newGroupIdArr = array();
		$groupId = $newGroupId = 0;
		foreach($tables as $table) {
			$condition = array($table.'.content_id' => $from_content['Content']['master_id']);
			$datas = $this->{$table}->find('all', array(
				'conditions' => $condition,
				'recursive' => -1
			));

			foreach($datas as $data) {

				if($table == 'Revision') {
					if($groupId != $data['Revision']['group_id']) {
						$groupId = $data['Revision']['group_id'];
						$data['Revision']['group_id'] = 0;
					} else {
						$data['Revision']['group_id'] = $newGroupId;
					}
				} else if($table == 'Announcement') {
					$data['Announcement']['revision_group_id'] = $newGroupIdArr[$data['Announcement']['revision_group_id']];
				}


				unset($data[$table]['id']);
				$data[$table]['content_id'] = $to_content['Content']['id'];
				$this->{$table}->create();
				if(!$this->{$table}->save($data)) {
					return false;
				}

				if($table == 'Revision' && $data['Revision']['group_id'] == 0) {
					$newGroupId = $this->{$table}->id;
					$newGroupIdArr[$groupId] = $newGroupId;
				}
			}
		}
		return true;
	}

/**
 * ブロック追加実行時に呼ばれる関数
 *
 * @param   Model Block   追加ブロック
 * @param   Model Content 追加コンテンツ
 * @param   Model Page    追加先ページ
 * @return  boolean
 * @since   v 3.0.0.0
 */
//	public function add_block($block, $content, $to_page) {
//		return true;
//	}

/**
 * 別ルームに移動実行時に呼ばれる関数
 *
 * @param   Model Block   移動元ブロック
 * @param   Model Block   移動先ブロック
 * @param   Model Content 移動元コンテンツ
 * @param   Model Content 移動先コンテンツ
 * @param   Model Page    移動元ページ
 * @param   Model Page    移動先ページ
 * @return  boolean
 * @since   v 3.0.0.0
 */
//	public function move($from_block, $to_block, $from_content, $to_content, $from_page, $to_page) {
//		return true;
//	}
}