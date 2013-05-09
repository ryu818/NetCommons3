<?php
/**
 * AnnouncementPostsControllerクラス
 *
 * <pre>
 * お知らせ編集画面表示用コントローラ
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       App.Controller
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class AnnouncementPostsController extends AnnouncementAppController {
/**
 * block_id
 * @var integer
 */
	public $block_id = null;

/**
 * content_id
 * @var integer
 */
	public $content_id = null;

/**
 * Component name
 *
 * @var array
 */
	public $components = array('RevisionList', 'CheckAuth' => array('allowAuth' => NC_AUTH_GENERAL));

/**
 * Model name
 *
 * @var array
 */
	public $uses = array('Revision');

/**
 * Helper name
 *
 * @var array
 */
	public $helpers = array('TimeZone');

/**
 * お知らせ編集画面表示・登録
 * @param   integer $postId
 * @return  void
 * @since   v 3.0.0.0
 */
	public function index($postId = null) {
		// TODO:権限チェックが未作成
		// TODO:email送信未実装

		// 自動保存前処理
		$autoRegistParams = $this->RevisionList->beforeAutoRegist($postId);
		$postId = $autoRegistParams['id'];
		$isAutoRegist = $autoRegistParams['isAutoRegist'];
		$status = $autoRegistParams['status'];
		$revisionName = $autoRegistParams['revision_name'];

		$announcementEdit = $this->AnnouncementEdit->findByContentId($this->content_id);
		if(!isset($announcementEdit['AnnouncementEdit'])) {
			$announcementEdit = $this->AnnouncementEdit->findDefault($this->content_id);
		}
		$announcement = $this->Announcement->findByContentId($this->content_id);

		if(isset($postId)) {
			// 編集
			if(!isset($announcement['Announcement']) || $announcement['Announcement']['id'] != $postId) {
				$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'AnnouncementPosts.index.001', '500');
				return;
			}
			// 自動保存等で最新のデータがあった場合、表示
			$revision = $this->Revision->findRevisions(null, $announcement['Announcement']['revision_group_id'], 1);
			if(isset($revision[0])) {
				$announcement['Revision'] = $revision[0]['Revision'];
			}
		} else {
			$announcement = $this->Announcement->findDefault($this->content_id);
		}

		if($this->request->is('post')) {
			if(!isset($this->request->data['Content']['title']) || !isset($this->request->data['Revision']['content'])) {
				$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'AnnouncementPosts.index.002', '500');
				return;
			}
			// 登録処理
			$content['Content'] = array(
				'id' => $this->content_id,
				'title' => $this->request->data['Content']['title']
			);
			if($this->nc_block['Content']['display_flag'] == NC_DISPLAY_FLAG_DISABLE) {
				$content['Content']['display_flag'] = NC_DISPLAY_FLAG_ON;
			}
			if(isset($this->request->data['Announcement'])) {
				// リクエストからの変更は許さない。
				unset($this->request->data['Announcement']['id']);
				unset($this->request->data['Announcement']['revision_group_id']);
				unset($this->request->data['Announcement']['status']);
				//unset($this->request->data['Announcement']['is_approved']);
				$announcement['Announcement'] = array_merge($announcement['Announcement'], $this->request->data['Announcement']);
			}

			$announcement['Announcement']['content_id'] = $this->content_id;
			$announcement['Announcement']['status'] = isset($status) ? $status : $announcement['Announcement']['status'];
			$announcement['Announcement']['is_approved'] = _ON;

			$announcement['Revision']['content'] = $this->request->data['Revision']['content'];


			$isApproved = _ON;
			if(!$isAutoRegist &&
				$announcementEdit['AnnouncementEdit']['approved_flag'] == _ON && $this->hierarchy  <= NC_AUTH_MODERATE) {
				// 承認機能On
				$announcement['Announcement']['pre_change_flag'] = (!$isAutoRegist && $announcementEdit['AnnouncementEdit']['approved_pre_change_flag'] == _ON) ? _ON : _OFF;
				$announcement['Announcement']['pre_change_date'] = null;
				$announcement['Announcement']['is_approved'] = _OFF;
				$isApproved = _OFF;
			}

			$pointer = _OFF;
			if(empty($announcement['Announcement']['pre_change_flag']) &&
				(!isset($announcement['Revision']['id']) || !$isAutoRegist)) {
				$pointer = _ON;
			}

			$revision = array(
				'Revision' => array(
					'group_id' => $announcement['Announcement']['revision_group_id'],
					'pointer' => $pointer,
					'is_approved_pointer' => ($pointer == _ON) ? $isApproved : _OFF,
					'revision_name' => $revisionName,
					'content_id' => $this->content_id,
					'content' => $this->request->data['Revision']['content']
				)
			);

			$fieldList = array(
				'content_id', 'revision_group_id', 'status', 'is_approved', 'pre_change_flag', 'pre_change_date',
			);

			$fieldListRevision = array(
				'group_id', 'pointer', 'is_approved_pointer', 'revision_name', 'content_id', 'content',
			);

			$this->Content->set($content);
			$this->Revision->set($revision);
			$this->Announcement->set($announcement);
			if($this->Content->validates(array('fieldList' => array('title'))) &&
				$this->Announcement->validates(array('fieldList' => $fieldList)) && $this->Revision->validates(array('fieldList' => $fieldListRevision))) {
				$this->Content->save($content, false, array('title', 'display_flag'));
				$this->Revision->save($revision, false, $fieldListRevision);
				$announcement['Revision']['id'] = $this->Revision->id;
				if(empty($announcement['Announcement']['revision_group_id'])) {
					$announcement['Announcement']['revision_group_id'] = $this->Revision->id;
				}

				$this->Announcement->save($announcement, false, $fieldList);

				if($isAutoRegist) {
					// 自動保存時後処理
					$this->RevisionList->afterAutoRegist($this->Announcement->id);
					return;
				}

				if(empty($announcement['Announcement']['id'])) {
					$this->Session->setFlash(__('Has been successfully registered.'));
				} else {
					$this->Session->setFlash(__('Has been successfully updated.'));
				}

				if($status == NC_STATUS_PUBLISH) {
					// 決定の場合、メイン画面にリダイレクト
					$this->redirect(array('controller' => 'announcement', '#' => $this->id));
					return;
				} else if(!isset($postId)) {
					// 新規投稿ならば、編集画面リダイレクト
					$this->redirect(array('controller' => 'announcement_posts', $this->Announcement->id, '#' => $this->id));
					return;
				}
			}
		}

		// 履歴情報
		if(isset($announcement['Revision']['id'])) {
			$this->set('revisions', $this->Revision->findRevisions($announcement['Revision']['id']));
		}
		$this->set('announcement_edit', $announcementEdit);
		$this->set('announcement', $announcement);
	}

/**
 * 履歴情報表示・復元処理
 * @param   integer $postId
 * @return  void
 * @since   v 3.0.0.0
 */
	public function revision($postId) {
		// TODO:権限チェックが未作成
		// TODO:復元のバリデートでそのrevision番号が本当に戻せるかどうか確認すること auto-drafutのデータ等
		$announcementEdit = $this->AnnouncementEdit->findByContentId($this->content_id);
		if(!isset($announcementEdit['AnnouncementEdit'])) {
			$announcementEdit = $this->AnnouncementEdit->findDefault($this->content_id);
		}
		$announcement = $this->Announcement->findById($postId);
		if(!isset($announcement['Announcement'])) {
			$this->flash(__('Content not found.'), null, 'AnnouncementPosts.revision.001', '404');
			return;
		}

		$cancelUrl = array('action' => 'index', $postId, '#' => $this->id);
		if(!$this->RevisionList->showRegist($this->nc_block['Content']['title'], array($postId), $cancelUrl,
			$this->Announcement, $announcement, $this->hierarchy,
			$announcementEdit['AnnouncementEdit']['approved_flag'], $announcementEdit['AnnouncementEdit']['approved_pre_change_flag'])) {
			$this->flash(__('Content not found.'), null, 'AnnouncementPosts.revision.002', '404');
		}

		if($this->request->is('post')) {
			// 復元時
			$this->redirect($cancelUrl);
			return;
		}
		$this->render('/Revisions/index');
	}

/**
 * 承認画面表示・「承認する」、「承認しない」実行。
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function approve() {
		// TODO:記事編集権限チェックが未作成
		$announcement = $this->Announcement->findByContentId($this->content_id);
		if(empty($announcement)) {
			$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'AnnouncementEdits.approve.001', '500');
			return;
		}

		if ($this->request->is('post') && !$this->CheckAuth->checkAuth($this->hierarchy, NC_AUTH_CHIEF)) {
			$this->flash(__('Forbidden permission to access the page.'), null, 'AnnouncementEdit.approve.002', '403');
			return;
		}

		if(!$this->RevisionList->approve($this->Announcement, $announcement)) {
			$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'AnnouncementEdit.approve.003', '500');
			return;
		}

		$this->set('dialog_id', 'announcement-approve-'.$this->id);
		$this->render('/Approve/index');
	}
}