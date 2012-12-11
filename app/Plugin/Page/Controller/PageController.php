<?php
/**
 * PageControllerクラス
 *
 * <pre>
 * ページメニュー表示用コントローラ
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       App.Controller
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class PageController extends PageAppController {
/**
 * page_id
 * @var integer
 */
	public $page_id = null;

/**
 * hierarchy
 * @var integer
 */
	public $hierarchy = null;

/**
 * ページメニュー表示
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function index() {
		include_once dirname(dirname(__FILE__)).'/Config/defines.inc.php';

		$login_user = $this->Auth->user();
		$user_id = $login_user['id'];
		$lang = Configure::read(NC_CONFIG_KEY.'.'.'language');
		$is_edit = isset($this->request->query['is_edit']) ? intval($this->request->query['is_edit']) : _OFF;
		$page = isset($this->request->query['page']) ? intval($this->request->query['page']) : 1;
		$limit = isset($this->request->query['limit']) ? intval($this->request->query['limit']) : PAGES_COMMUNITY_LIMIT;
		$active_tab = isset($this->request->query['active_tab']) ? intval($this->request->query['active_tab']) : null;
		if(!isset($active_tab)) {
			$active_tab = ($this->nc_page['Page']['space_type'] == NC_SPACE_TYPE_GROUP) ? 1 : 0;
		}

		$element_params = array(
			'page_id' => $this->page_id,
			'is_edit' => $is_edit
		);

		// カレント会員取得
		$center_page = Configure::read(NC_SYSTEM_KEY.'.'.'Center_Page');
		$current_user = $this->User->currentUser($center_page, $login_user);
		if($current_user === false) {
			$this->flash(sprintf(__('Failed to obtain the database, (%s).',true),'users'), null, 'pagesmenu_index.002');
			return;
		} else if($current_user === '') {
			$current_user = array('User' => $login_user);
		}

		$fetch_params = array(
			'active_page_id' => $this->page_id
		);
		$params = null;	// TODO:後に削除するかも$params
		/*if($is_edit) {
			$params = array(
					'conditions' => array(
							'Page.lang' => array('', $lang)
					)
			);
		}*/
		// 管理系の権限を取得
		if($user_id) {
			$admin_hierarchy = $this->ModuleSystemLink->findHierarchy(Inflector::camelize($this->request->params['plugin']), $login_user['authority_id']);
		} else {
			$admin_hierarchy = NC_AUTH_OTHER;
		}
		$element_params['admin_hierarchy'] = $admin_hierarchy;

		if($is_edit) {
			// active pageでページ追加をactiveにするかどうか。
			$is_add = false;

			if($center_page['Page']['thread_num'] <= 1) {
				if($center_page['Authority']['hierarchy'] >= NC_AUTH_MIN_CHIEF) {
					$is_add = true;
				}
			} else {
				$parent_page = $this->Page->findAuthById($center_page['Page']['parent_id'], $user_id);
				if($parent_page['Authority']['hierarchy'] >= NC_AUTH_MIN_CHIEF) {
					$is_add = true;
				}
			}
			$element_params['is_add'] = $is_add;
		}

		$pages = $this->Page->findMenu('all', $user_id, NC_SPACE_TYPE_PUBLIC, $current_user, $params, null, $fetch_params);
		$private_pages = $this->Page->findMenu('all', $user_id, array(NC_SPACE_TYPE_MYPORTAL, NC_SPACE_TYPE_PRIVATE), $current_user, $params, null, $fetch_params);
		if(isset($private_pages[NC_SPACE_TYPE_MYPORTAL])) {
			$pages[NC_SPACE_TYPE_MYPORTAL] = $private_pages[NC_SPACE_TYPE_MYPORTAL];
		}
		if(isset($private_pages[NC_SPACE_TYPE_PRIVATE])) {
			$pages[NC_SPACE_TYPE_PRIVATE] = $private_pages[NC_SPACE_TYPE_PRIVATE];
		}

		// コミュニティ数
		$conditions = array(
			'Page.thread_num' => 1
		);
		$top_params = array(
			'conditions' => $conditions
		);
		$pages_group_total_count = $this->Page->findMenu('count', $user_id, NC_SPACE_TYPE_GROUP, $current_user, $top_params);

		$pages_group = array();
		if($pages_group_total_count != 0) {
			// コミュニティ取得
			$conditions = array(
				'Page.thread_num' => 1
			);
			$top_group_params = array(
				'fields' => array('Page.room_id'),
				'conditions' => $conditions,
				'page' => $page,
				'limit' => $limit
			);
			if($is_edit && $admin_hierarchy >= NC_AUTH_MIN_ADMIN) {
				// 管理者で編集モードならばコミュニティすべて表示
				$pages_top_group = $this->Page->findMenu('list', $user_id, NC_SPACE_TYPE_GROUP, $current_user, $top_group_params, null, null, true);
			} else {
				$pages_top_group = $this->Page->findMenu('list', $user_id, NC_SPACE_TYPE_GROUP, $current_user, $top_group_params);
			}
			$params['conditions']['Page.room_id'] = $pages_top_group;
			$pages_group = $this->Page->findMenu('all', $user_id, NC_SPACE_TYPE_GROUP, $current_user, $params, null, $fetch_params, true);
		}

		$element_params['pages'] = $pages;
		$element_params['pages_group'] = $pages_group;
		$element_params['pages_group_total_count'] = $pages_group_total_count;
		$element_params['active_tab'] = $active_tab;
		$this->set('element_params', $element_params);
	}

/**
 * よく見るページ表示
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function favorite() {
		$this->render('index');
	}

/**
 * ページ情報表示・登録
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function meta() {
		$this->render('index');
	}

/**
 * ページスタイル表示・登録
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function style() {
		// ページ情報を取得
		$page = $this->Page->findById($this->page_id);
		// TODO ノードを基にスタイル情報を取得
		$page_style = $this->PageStyle->findByStylePageId($this->page_id);

		if ($this->request->is('post')) {
			$content = (isset($this->request->data['css'])) ? $this->request->data['css'] : '' ;
			// 既存のCSSファイルを削除
			if (!empty($page_style['PageStyle']['file'])) {
				$this->PageStyle->deleteCssFile($page_style['PageStyle']['file']);
			}
			// webroot/theme/page_styles/下にCSSファイルを生成
			$file = $this->PageStyle->createCssFile($content);
			$data = array(
					'id' => (isset($page_style['PageStyle']['id'])) ? $page_style['PageStyle']['id'] : null,
					'style_page_id' => $this->page_id,
					'file' => $file
			);
			$this->PageStyle->save($data);
			// スタイル情報を再取得
			// TODO 他に良い方法がないか検討
			$page_style = $this->PageStyle->findByStylePageId($this->page_id);
		}

		$file_content = file_get_contents($this->PageStyle->getPath().$page_style['PageStyle']['file']);
		$this->set('file_content', $file_content);
		$this->set('page', $page['Page']);
		$this->set('page_style', $page_style['PageStyle']);
		$this->render('index');
	}

/**
 * ページテーマ表示・登録
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function theme() {
		$this->render('index');
	}

/**
 * ページレイアウト表示・登録
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function layout() {
		$this->render('index');
	}
}
