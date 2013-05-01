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
		// TODO:承認機能未実装
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
				$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'AnnouncementPosts.index.002', '500');
				return;
			}
			// 自動保存で最新のデータがあった場合、表示
			$revision = $this->Revision->findRevisions(null, $announcement['Revision']['group_id'], 1);
			if(isset($revision[0])) {
				$announcement['Revision'] = $revision[0]['Revision'];
			} else {
				$announcement['Revision'] = array('content' => '');
			}
		} else {
			$announcement = $this->Announcement->findDefault($this->content_id);
		}

		if($this->request->is('post')) {
			// 登録処理
			if(!isset($this->request->data['Revision']['content'])) {
				$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'AnnouncementPosts.index.002', '500');
				return;
			}
			if(isset($this->request->data['Announcement'])) {
				// リクエストからの変更は許さない。
				unset($this->request->data['Announcement']['id']);
				unset($this->request->data['Announcement']['is_approved']);
				unset($this->request->data['Announcement']['revision_group_id']);
				$announcement['Announcement'] = array_merge($announcement['Announcement'], $this->request->data['Announcement']);
			}

			$announcement['Announcement']['content_id'] = $this->content_id;

			$announcement['Revision']['content'] = $this->request->data['Revision']['content'];

			$fieldList = array(
				'content_id', 'revision_group_id', 'status', 'is_approved', 'pre_change_flag', 'pre_change_date',
			);

			$pointer = _OFF;
			if(empty($announcement['Announcement']['pre_change_flag']) && ($announcement['Announcement']['revision_group_id'] == 0 || !$isAutoRegist)) {
				$pointer = _ON;
			}

			$revision = array(
				'Revision' => array(
					'group_id' => $announcement['Announcement']['revision_group_id'],
					'pointer' => $pointer,
					'revision_name' => $revisionName,
					'content_id' => $this->content_id,
					'content' => $this->request->data['Revision']['content']
				)
			);

			$fieldListRevision = array(
				'group_id', 'pointer', 'revision_name', 'content_id', 'content',
			);

			$this->Revision->set($revision);
			$this->Announcement->set($announcement);
			if($this->Announcement->validates(array('fieldList' => $fieldList)) && $this->Revision->validates(array('fieldList' => $fieldListRevision))) {
				$this->Revision->save($revision, false, $fieldListRevision);
				if(empty($announcement['Announcement']['revision_group_id'])) {
					$announcement['Announcement']['revision_group_id'] = $this->Revision->id;
				}

				$this->Announcement->save($announcement, false, $fieldList);

				if($isAutoRegist) {
					// 自動保存時後処理
					$this->RevisionList->afterAutoRegist($this->Announcement->id);
					return;
				}

				// お知らせの一時保存の場合、blockテーブルのdisplay_flagを変更
				if(isset($status)) {
					if($status == NC_STATUS_PUBLISH) {
						$display_flag = NC_DISPLAY_FLAG_ON;
					} else {
						$display_flag = NC_DISPLAY_FLAG_OFF;
					}


					$fields = array(
						'Block.display_flag' => $display_flag
					);
					$conditions = array(
						'Block.content_id' => $this->content_id
					);
					if(!$this->Block->updateAll($fields, $conditions)) {
						$this->flash(__('Failed to update the database, (%s).', 'blocks'), null, 'AnnouncementPosts.index.003', '500');
						return;
					}
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
 * 履歴情報表示
 * @param   integer $postId
 * @return  void
 * @since   v 3.0.0.0
 */
	public function revision($postId) {
		// TODO:BlogPostsのrevisionアクションとほぼ同一なので、共通化できるかどうか検討する。
		$announcement = $this->Announcement->findById($postId);
		if(!isset($announcement['Announcement'])) {
			$this->flash(__('Content not found.'), null, 'AnnouncementPosts.revision.001', '404');
			return;
		}
		// 自動保存で最新のデータがあった場合、表示
		$revision = $this->Revision->findRevisions(null, $announcement['Revision']['group_id'], 1);
		if(isset($revision[0])) {
			$announcement['Revision'] = $revision[0]['Revision'];
		} else {
			$announcement['Revision'] = array('content' => '');
		}

		$cancelUrl = array('action' => 'index', $postId, '#' => $this->id);

		$ret = $this->RevisionList->setDatas($this->nc_block['Content']['title'], $announcement, array($postId), $cancelUrl);
		if($ret === false) {
			$this->flash(__('Content not found.'), null, 'AnnouncementPosts.revision.002', '404');
			return;
		}

		if($this->request->is('post')) {
			// TODO:復元のバリデートでそのrevision番号が本当に戻せるかどうか確認すること auto-drafutのデータ等
			$this->redirect($cancelUrl);
			return;
		}
		$this->render('/Revisions/index');
	}
}