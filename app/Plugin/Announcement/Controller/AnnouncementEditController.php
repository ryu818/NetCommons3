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
		$ret = $this->Htmlarea->findByContentId($this->content_id);
		if ($this->request->is('post')) {
			$data = array(
				'id' => isset($ret['Htmlarea']['id']) ? $ret['Htmlarea']['id'] : null,
				'content_id' => $this->content_id,
				'content' => $this->request->data['Htmlarea']['content']
			);
			if ($this->Htmlarea->save($data)) {
				// TODO:Ajaxなので、コメント メッセージの表示手段を調査するべき
				// $this->Session->setFlash('Your post has been saved.');
				
				$this->redirect('/blocks/' .$this->block_id. '/announcement/');
				//$this->redirect(array('block_type' => 'blocks','block_id' => 366, 'controller' => 'announcement', 'action' => 'index'));
			}// else {
				//$this->Session->setFlash('Unable to add your post.');
			//}
			$this->set('content', $this->request->data['Htmlarea']['content']);
			return;
		}
		if(!isset($ret['Htmlarea'])) {
			return;
		}
		$this->set('content', $ret['Htmlarea']['content']);
	}
}