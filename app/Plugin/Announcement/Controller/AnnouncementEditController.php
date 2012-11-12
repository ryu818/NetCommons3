<?php
/**
 * AnnouncementEditControllerクラス
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
class AnnouncementEditController extends AnnouncementAppController {
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
	public $is_edit = true;

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
			$data = array(
				'id' => isset($htmlarea['Htmlarea']['id']) ? $htmlarea['Htmlarea']['id'] : null,
				'content_id' => $this->content_id,
				'content' => $this->request->data['Htmlarea']['content']
			);
			if ($this->Htmlarea->save($data)) {
				$this->Session->setFlash(__('Your post has been saved.'));
				$this->redirect(array('plugin' => 'announcement', 'controller' => 'announcement', 'block_id' => $this->block_id, '#' => $this->id));
			}
			$htmlarea['Htmlarea'] = $data;
		}

		$this->set('htmlarea', $htmlarea);
	}
}