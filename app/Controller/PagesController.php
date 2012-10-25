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
	public $uses = array('ModulesLink');

/**
 * page_id配列
 *
 * @var array
 */
	public $page_id_arr = null;

/**
 * ページ表示
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function index() {
		$mode = $this->Session->read(NC_SYSTEM_KEY.'.mode');
		$user = $this->Auth->user();
		$blocks = $this->Block->findByPageIds($this->page_id_arr, intval($user['id']));
		$pages = $this->Page->findByIds($this->page_id_arr, intval($user['id']));
		$authority_id = isset($user['authority_id']) ? $user['authority_id'] : 0;
		
		$add_modules = array();
		if($mode == NC_BLOCK_MODE) {
			// 追加モジュールリスト取得
			foreach($this->page_id_arr as $page_id) {
				$room_id = $pages[$page_id]['Page']['room_id'];
				$space_type = $pages[$page_id]['Page']['space_type'];
				if($page_id == 0 || isset($add_module_list[$room_id])) {
					continue;
				}

				$add_modules[$room_id] = $this->ModulesLink->findModuleslinks($room_id, $authority_id, $space_type);
			}
		}

		$this->set("blocks", $blocks);
		$this->set("pages", $pages);
		$this->set("page_id_arr", $this->page_id_arr);
		$this->set("add_modules", $add_modules);

		//$this->render('responsive');
	}
}
