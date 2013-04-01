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
 * 編集画面か否か（セッティングモードONの場合の上部、編集ボタンをリンク先を変更するため）
 * Default:false
 * @var boolean
 */
	protected $nc_is_edit = true;

/**
 * Component name
 *
 * @var array
 */
	public $components = array('CheckAuth' => array('allowAuth' => NC_AUTH_CHIEF));

/**
 * お知らせ編集画面表示・登録
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function index() {
		$htmlarea = $this->Htmlarea->findByContentId($this->content_id);
		if ($this->request->is('post')) {
			$content['Content'] = array(
				'id' => $this->content_id,
				'title' => $this->request->data['Content']['title'],
				'display_flag' => NC_DISPLAY_FLAG_ON,
			);


			$htmlarea['Htmlarea'] = array(
				'id' => isset($htmlarea['Htmlarea']['id']) ? $htmlarea['Htmlarea']['id'] : null,
				'content_id' => $this->content_id,
				'content' => $this->request->data['Htmlarea']['content']
			);
			$fieldListContent = array('title');
			$fieldListHtml = array('content_id', 'content');
			$this->Content->set($content);
			$this->Htmlarea->set($htmlarea);

			if($this->Content->validates(array('fieldList' => $fieldListContent)) && $this->Htmlarea->validates(array('fieldList' => $fieldListHtml))) {
				if (!$this->Content->save($content, false, $fieldListContent)) {
					$this->flash(__('Failed to register the database, (%s).', 'content'), null, 'AnnouncementEdits.index.001', '500');
					return;
				}
				if (!$this->Htmlarea->save($htmlarea, false, $fieldListHtml) ) {
					$this->flash(__('Failed to register the database, (%s).', 'htmlarea'), null, 'AnnouncementEdits.index.002', '500');
					return;
				}

				if(empty($htmlarea['Blog']['id'])) {
					$this->Session->setFlash(__('Has been successfully registered.'));
				} else {
					$this->Session->setFlash(__('Has been successfully updated.'));
				}
				$this->redirect(array('plugin' => 'announcement', 'controller' => 'announcement', '#' => $this->id));
			}
		}

		$this->set('htmlarea', $htmlarea);
	}
}