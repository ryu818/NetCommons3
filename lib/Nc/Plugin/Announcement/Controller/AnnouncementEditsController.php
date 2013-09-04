<?php
/**
 * AnnouncementEditsControllerクラス
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
class AnnouncementEditsController extends AnnouncementAppController {
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
	public $components = array('Security', 'CheckAuth' => array('allowAuth' => NC_AUTH_CHIEF));

/**
 * 実行前処理
 * <pre>書き換えが発生するhidden項目をSecurityComponentのチェック対象除外にする処理</pre>
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function beforeFilter() {
		parent::beforeFilter();
		$this->Security->disabledFields = array('AnnouncementEdit.post_hierarchy');
	}

/**
 * お知らせ編集画面表示・登録
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function index() {
		$announcementEdit = $this->AnnouncementEdit->findByContentId($this->content_id);
		if(empty($announcementEdit)) {
			$announcementEdit = $this->AnnouncementEdit->findDefault($this->content_id);
		}

		if ($this->request->is('post')) {
			if(!isset($this->request->data['AnnouncementEdit'])) {
				throw new BadRequestException(__('Unauthorized request.<br />Please reload the page.'));
			}

			// 登録処理
			$announcementEdit['AnnouncementEdit'] = array_merge($announcementEdit['AnnouncementEdit'], $this->request->data['AnnouncementEdit']);
			$announcementEdit['AnnouncementEdit']['content_id'] = $this->content_id;
			// エラー時：アクティブタブに移動させるため
			$fieldList = array(
				'content_id', 'post_hierarchy', 'approved_flag', 'approved_pre_change_flag',
				'approved_mail_flag', 'approved_mail_subject', 'approved_mail_body',
			);

			$this->AnnouncementEdit->set($announcementEdit);
			if($this->AnnouncementEdit->validates(array('fieldList' => $fieldList))) {
				if($this->nc_block['Content']['display_flag'] == NC_DISPLAY_FLAG_DISABLE) {
					$content['Content'] = array(
						'id' => $this->content_id,
						'display_flag' => NC_DISPLAY_FLAG_ON
					);
					$this->Content->save($content, true, array('display_flag'));
				}
				$this->AnnouncementEdit->save($announcementEdit, false, $fieldList);
				if(empty($announcementEdit['AnnouncementEdit']['id'])) {
					$this->Session->setFlash(__('Has been successfully registered.'));
				} else {
					$this->Session->setFlash(__('Has been successfully updated.'));
				}
				$this->redirect(array('controller' => 'announcement', '#' => $this->id));
				return;
			} else {
				// error
				$this->nc_block['Content']['title'] = $this->request->data['Content']['title'];
			}
		}

		$this->set('announcement_edit', $announcementEdit);
	}
}