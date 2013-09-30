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
	public $uses = array('ModuleLink', 'PageStyle');

/**
 * Component name
 *
 * @var array
 */
	public $components = array('CheckAuth' => array('chkBlockId' => false));

/**
 * 中央カラムのpage_id
 *
 * @var int
 */
	public $page_id = null;

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
		$user_id = $user['id'];
		$blocks = $this->Block->findByPageIds($this->page_id_arr, intval($user['id']));
		$center_page = Configure::read(NC_SYSTEM_KEY.'.'.'center_page');
		$pages = $this->Page->findAuthById($this->page_id_arr, intval($user['id']));
		$authority_id = isset($user['authority_id']) ? $user['authority_id'] : 0;

		// js,Cssガーベージコレクション
		if(rand(0, NC_ASSET_GC_PROBABILITY) == 0) {
			$this->Asset->gc();
		}

		// パンくずリスト
		$pages_list = $this->Page->findBreadcrumb($pages[$this->page_id_arr[0]], $user_id);

		$add_modules = array();
		if($mode == NC_BLOCK_MODE) {
			// 追加モジュールリスト取得
			foreach($this->page_id_arr as $page_id) {
				$room_id = $pages[$page_id]['Page']['room_id'];
				$space_type = $pages[$page_id]['Page']['space_type'];
				if($page_id == 0 || isset($add_module_list[$room_id])) {
					continue;
				}

				$add_modules[$room_id] = $this->ModuleLink->findModulelinks($room_id, $authority_id, $space_type);
			}
		}

		// コピーコンテンツ
		$copy_content = null;
		$copy_content_id = $this->Session->read('Blocks.'.'copy_content_id');
		if(!empty($copy_content_id)) {
			$copy_content = $this->Content->findById($copy_content_id);
			if(!isset($copy_content['Content'])) {
				$copy_content = null;
			}
		}

		// ページタイトル取得
		$this->set("title", $this->_getPageTitle($pages_list));

		$this->set("blocks", $blocks);
		$this->set("pages", $pages);
		$this->set("page_id_arr", $this->page_id_arr);
		$this->set("add_modules", $add_modules);
		$this->set("copy_content", $copy_content );
		$this->set('pages_list', $pages_list);
		if(!empty($this->nc_page_styles)) {
			$this->set('nc_page_styles', $this->nc_page_styles);
		}
		//$this->render('responsive');
	}

/**
 * ページタイトル取得
 * @param   Model Pages $pages_list
 * @return  string $title
 * @since   v 3.0.0.0
 */
	protected function _getPageTitle($pages_list) {
		$count = count($pages_list);
		if($pages_list[$count - 1]['Page']['space_type'] == NC_SPACE_TYPE_PUBLIC) {
			if($pages_list[$count - 1]['Page']['display_sequence'] == 1 && $pages_list[$count - 1]['Page']['thread_num'] == 2) {
				$title = '';
			} else {
				$title = $pages_list[$count - 1]['Page']['page_name'];
			}
		} else if($pages_list[$count - 1]['Page']['id'] == $pages_list[$count - 1]['Page']['root_id']) {
			$title = $pages_list[$count - 1]['Page']['page_name'];
		} else {
			if($pages_list[$count - 1]['Page']['display_sequence'] == 1 && $pages_list[$count - 1]['Page']['thread_num'] == 2) {
				// マイポータル、マイルーム、コミュニティTop
				$title = $pages_list[0]['Page']['page_name'];
			} else {
				$title = $pages_list[$count - 1]['Page']['page_name'].NC_TITLE_SEPARATOR.$pages_list[0]['Page']['page_name'];
			}
		}

		return $title;
	}
}
