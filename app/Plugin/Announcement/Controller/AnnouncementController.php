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

	public function index() {
		$this->set('htmlarea', $this->Htmlarea->findByContentId($this->content_id, array('content')));
	}
}