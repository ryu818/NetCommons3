<?php
/**
 * PagesControllerクラス
 *
 * <pre>
 * ページ表示用コントローラ
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       App.Controller
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */

App::uses('AppController', 'Controller');

/**
 * Static content controller
 *
 * Override this controller by placing a copy in controllers directory of an application
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */
class PagesController extends AppController {

/**
 * Controller name
 *
 * @var string
 */
	public $name = 'Pages';

/**
 * Model name
 *
 * @var array
 */
	public $uses = array('Page','Block');

/**
 * page_id配列
 *
 * @var array
 */
	public $page_id_arr = null;

	public function index() {
		$user = $this->Auth->user();
		$blocks = $this->Block->findByPageIds($this->page_id_arr, intval($user['id']));
		$pages = $this->Page->findByIds($this->page_id_arr, intval($user['id']));

		$this->set("blocks", $blocks);
		$this->set("pages", $pages);
		$this->set("page_id_arr", $this->page_id_arr);

		//$this->render('responsive');
	}
}
