<?php
/**
 * AnnouncementControllerクラス
 *
 * <pre>
 * お知らせメイン画面表示用コントローラ
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       App.Controller
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class AnnouncementController extends AnnouncementAppController {
/**
 * content_id
 * @var integer
 */
	public $content_id = null;

/**
 * hierarchy
 * @var integer
 */
	public $hierarchy = null;

/**
 * Component name
 *
 * @var array
 */
	public $components = array('RevisionList');

	public function index() {
		$announcement = $this->Announcement->findByContentId($this->content_id);
		if(isset($announcement['Revision']['content'])) {
			$announcement['Revision']['content'] = $this->RevisionList->updatePreChange($this->Announcement, $announcement);
		}

		$userId = $this->Auth->user('id');
		$isEdit = false;
		if(!empty($userId)) {
			// お知らせのみ、管理者が記述したお知らせ、一般会員が編集を許す
			//$postUserId = isset($announcement['Announcement']['created_user_id']) ? $announcement['Announcement']['created_user_id'] : null;
			//$postHierarchy = isset($announcement['PageAuthority']['hierarchy']) ? $announcement['PageAuthority']['hierarchy'] : null;
			$announcementEdit = $this->AnnouncementEdit->findByContentId($this->content_id, array(
				'AnnouncementEdit.post_hierarchy'));
			if(empty($announcementEdit)) {
				$announcementEdit = $this->AnnouncementEdit->findDefault($this->content_id);
			}
			$isEdit = $this->CheckAuth->isEdit($this->hierarchy, $announcementEdit['AnnouncementEdit']['post_hierarchy']);
		}

		if((!isset($announcement['Announcement']) || $announcement['Revision']['content'] == '') && $isEdit) {
			// コンテンツがない場合でもダブルクリックで編集させるため
			$announcement['Revision']['content'] = __('Content not found.');
		}
		$this->set('is_edit', $isEdit);
		$this->set('announcement', $announcement);
	}
}