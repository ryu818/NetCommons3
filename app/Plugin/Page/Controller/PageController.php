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
 * Component name
 * @var array
 */
	public $components = array('Page.PageMenu');

/**
 * Model name
 * @var array
 */
	public $uses = array('Community', 'CommunityLang', 'CommunityTag');

/**
 * Heilper name
 * @var array
 */
	public $helpers = array(
		'Paginator'
	);

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
 * コミュニティーのページ移動の設定
 * @var array
 */
	public $paginate = array(
		'fields' => array('Page.room_id'),
		'conditions' => array(
			'Page.thread_num' => 1
		)
	);

/**
 * 表示前処理
 * <pre>
 * 	ページメニューの言語切替の値を選択言語としてセット
 * </pre>
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function beforeFilter()
	{
		$active_lang = $this->Session->read(NC_SYSTEM_KEY.'.page_menu.lang');
		if(isset($active_lang)) {
			Configure::write(NC_CONFIG_KEY.'.'.'language', $active_lang);
			$this->Session->write(NC_CONFIG_KEY.'.language', $active_lang);
		}
		parent::beforeFilter();
	}

/**
 * 表示後処理
 * <pre>
 * 	セッションにセットしてあった言語を元に戻す。
 * </pre>
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function afterFilter()
	{
		parent::afterFilter();
		if($this->action != 'index') {
			$this->Session->delete(NC_SYSTEM_KEY.'.page_menu.lang');
			$this->Session->delete(NC_SYSTEM_KEY.'.page_menu.pre_lang');
		}
		$pre_lang = $this->Session->read(NC_SYSTEM_KEY.'.page_menu.pre_lang');
		if(isset($pre_lang)) {
			Configure::write(NC_CONFIG_KEY.'.'.'language', $pre_lang);
			$this->Session->write(NC_CONFIG_KEY.'.language', $pre_lang);
			$this->Session->delete(NC_SYSTEM_KEY.'.page_menu.pre_lang');
		}

	}

/**
 * ページメニュー表示
 * @param   string $active_lang
 * @return  void
 * @since   v 3.0.0.0
 */
	public function index($active_lang = null) {
		$this->Session->write(NC_SYSTEM_KEY.'.page_menu.action', $this->action);
		$this->Session->delete(NC_SYSTEM_KEY.'.page_menu.PageUserLink');

		include_once dirname(dirname(__FILE__)).'/Config/defines.inc.php';

		$login_user = $this->Auth->user();
		$user_id = $login_user['id'];

		$is_edit = isset($this->request->query['is_edit']) ? intval($this->request->query['is_edit']) : _OFF;
		$is_detail = isset($this->request->query['is_detail']) ? intval($this->request->query['is_detail']) : _OFF;
		$active_tab = isset($this->request->query['active_tab']) ? intval($this->request->query['active_tab']) : null;
		$limit = !empty($this->request->named['limit']) ? intval($this->request->named['limit']) : PAGES_COMMUNITY_LIMIT;
		$views = !empty($this->request->named['views']) ? intval($this->request->named['views']) : PAGES_COMMUNITY_VIEWS;
		$page_id = isset($this->request->query['page_id']) ? intval($this->request->query['page_id']) : intval($this->page_id);

		// 言語切替
		$languages = $this->Language->findLists();
		//$active_lang = isset($active_lang) ? $active_lang : $this->Session->read(NC_SYSTEM_KEY.'.page_menu.lang');
		//$this->_sess_language = null;
		if(isset($active_lang) && isset($languages[$active_lang])) {
			$this->Session->write(NC_SYSTEM_KEY.'.page_menu.pre_lang', $this->Session->read(NC_CONFIG_KEY.'.language'));

			Configure::write(NC_CONFIG_KEY.'.'.'language', $active_lang);
			$this->Session->write(NC_CONFIG_KEY.'.language', $active_lang);
			$this->Session->write(NC_SYSTEM_KEY.'.page_menu.lang', $active_lang);
		}
		$lang = Configure::read(NC_CONFIG_KEY.'.'.'language');

		$this->paginate['limit'] = $limit;

		if(isset($this->request->named['page']) || isset($this->request->named['limit'])) {
			$active_tab = 1;
		} else if(!isset($active_tab)) {
			$active_tab = ($this->nc_page['Page']['space_type'] == NC_SPACE_TYPE_GROUP) ? 1 : 0;
		}

		$element_params = array(
			'is_edit' => $is_edit,
			'community_params' => array()
		);

		// カレント会員取得
		$center_page = Configure::read(NC_SYSTEM_KEY.'.'.'center_page');
		$current_user = $this->User->currentUser($center_page, $login_user);
		if($current_user === false) {
			$this->flash(__('Failed to obtain the database, (%s).','users'), null, 'Page/index.001', '500');
			return;
		} else if($current_user === '') {
			$current_user = array('User' => $login_user);
		}
		$sel_active_tab = 0;
		if($center_page['Page']['space_type'] == NC_SPACE_TYPE_GROUP) {
			$sel_active_tab = 1;
		}

		$params = null;
		if($is_edit) {
			$params = array(
				'conditions' => array(
					'Page.lang' => array('', $lang)
				)
			);
		}
		// activeなページがコミュニティーならば、コミュニティー一覧の何ページ目かにあるかを設定
		if(!isset($this->request->named['page'])) {
			if(isset($page_id)) {
				$page = $this->Page->findById($page_id);
				$community_page = $this->Page->findById($page['Page']['root_id']);
			} else if($center_page['Page']['space_type'] == NC_SPACE_TYPE_GROUP) {
				$community_page = $this->Page->findById($center_page['Page']['root_id']);
			}
			if(isset($community_page) && $community_page['Page']['space_type'] == NC_SPACE_TYPE_GROUP) {
				$this->paginate['page'] = ceil(intval($community_page['Page']['display_sequence'])/$this->paginate['limit']);
				$sel_active_tab = 1;
			} else {
				$sel_active_tab = 0;
			}
		}
		if(isset($this->request->query['page_id'])) {
			// コピー、ペーストでコミュニティへペーストした場合、コミュニティタブへ
			$active_tab = $sel_active_tab;
		}

		// 管理系の権限を取得
		if($user_id) {
			$admin_hierarchy = $this->ModuleSystemLink->findHierarchy(Inflector::camelize($this->request->params['plugin']), $login_user['authority_id']);
		} else {
			$admin_hierarchy = NC_AUTH_OTHER;
		}
		$element_params['admin_hierarchy'] = $admin_hierarchy;

		$parent_page = $this->Page->findAuthById($center_page['Page']['parent_id'], $user_id);
		if(!isset($parent_page['Page'])) {
			$this->flash(__('Failed to obtain the database, (%s).','pages'), null, 'Page/index.002', '500');
			return;
		}
		if($is_edit) {
			// active pageでページ追加をactiveにするかどうか。
			$is_add = false;
			$is_add_community = false;

			if($center_page['Page']['thread_num'] <= 1) {
				if($center_page['Authority']['hierarchy'] >= NC_AUTH_MIN_CHIEF) {
					$is_add = true;
				}
			} else if($parent_page['Authority']['hierarchy'] >= NC_AUTH_MIN_CHIEF) {
				$is_add = true;
			}
			if($admin_hierarchy >= NC_AUTH_MIN_MODERATE) {
				$is_add_community = true;
			}
			if(($center_page['Page']['space_type'] != NC_SPACE_TYPE_GROUP && $active_tab == 1) ||
					($center_page['Page']['space_type'] == NC_SPACE_TYPE_GROUP && $active_tab == 0)) {
				$is_add = false;
			}
			$element_params['is_add'] = $is_add;
			$element_params['is_add_community'] = $is_add_community;
		}

		if($is_detail) {
			$buf_thread_num = $center_page['Page']['thread_num'];
			if($center_page['Page']['thread_num'] == 2 && $center_page['Page']['display_sequence'] == 1) {
				// Topページ
				$parent_page = $this->Page->findAuthById($parent_page['Page']['parent_id'], $user_id);	// 再取得
				if(!isset($parent_page['Page'])) {
					$this->flash(__('Failed to obtain the database, (%s).','pages'), null, 'Page/index.003', '500');
					return;
				}
				$page_id = $center_page['Page']['parent_id'];
				$buf_thread_num --;
			}

			if($buf_thread_num == 1 && $center_page['Page']['space_type'] == NC_SPACE_TYPE_GROUP) {
				// コミュニティーならば
				$ret = $this->Community->getCommunityData($center_page['Page']['room_id']);
				if($ret === false) {
					$this->flash(__('Failed to obtain the database, (%s).', 'communities'), null, 'PageMenu/detail.002', '500');
					return;
				}
				list($community, $community_lang, $community_tag) = $ret;

				$element_params['community_params']['community'] = $community;
				$element_params['community_params']['community_lang'] = $community_lang;
				$element_params['community_params']['community_tag'] = $community_tag;
				$element_params['community_params']['photo_samples'] = $this->PageMenu->getCommunityPhoto();
			}
		}

		$fetch_params = array(
			'active_page_id' => $page_id
		);
		$pages = $this->Page->findMenu('all', $user_id, NC_SPACE_TYPE_PUBLIC, $current_user, $params, null, $fetch_params, true);
		$private_pages = $this->Page->findMenu('all', $user_id, array(NC_SPACE_TYPE_MYPORTAL, NC_SPACE_TYPE_PRIVATE), $current_user, $params, null, $fetch_params);
		if(isset($private_pages[NC_SPACE_TYPE_MYPORTAL])) {
			$pages[NC_SPACE_TYPE_MYPORTAL] = $private_pages[NC_SPACE_TYPE_MYPORTAL];
		}
		if(isset($private_pages[NC_SPACE_TYPE_PRIVATE])) {
			$pages[NC_SPACE_TYPE_PRIVATE] = $private_pages[NC_SPACE_TYPE_PRIVATE];
		}

		// コミュニティー数
		//$this->paginate['extra'] = array('user_id' => $user_id);
		$this->paginate['user_id'] = $user_id;
		if($is_edit && $admin_hierarchy >= NC_AUTH_MIN_ADMIN) {
			$this->paginate['is_all'] = true;
		} else {
			$this->paginate['is_all'] = false;
		}
		$pages_top_group = $this->paginate('Page');
		$pages_group = array();
		if(count($pages_top_group) > 0) {
			$params['conditions']['Page.root_id'] = $pages_top_group;
			$pages_group = $this->Page->findMenu('all', $user_id, NC_SPACE_TYPE_GROUP, null, $params, null, $fetch_params, $this->paginate['is_all']);
		}
		$copy_page_id = $this->Session->read('Pages.'.'copy_page_id');
		if(isset($copy_page_id)) {
			$copy_page = $this->Page->findAuthById($copy_page_id, $user_id);
			$element_params['copy_page_id'] = $copy_page_id;
			$element_params['copy_page'] = $copy_page;
		} else {
			$element_params['copy_page_id'] = 0;
		}

		$element_params['languages'] = $languages;
		$element_params['pages'] = $pages;
		$element_params['pages_group'] = $pages_group;
		$element_params['page_id'] = $page_id;
		$element_params['is_detail'] = $is_detail;
		$element_params['parent_page'] = $parent_page;
		////$element_params['pages_group_total_count'] = $pages_group_total_count;
		$element_params['active_tab'] = $active_tab;
		$element_params['sel_active_tab'] = $sel_active_tab;
		$element_params['views'] = $views;
		$element_params['limit'] = $limit;
		$element_params['limit_select_values'] = explode('|', PAGES_COMMUNITY_LIMIT_SELECT);

		$this->set('element_params', $element_params);
	}

/**
 * よく見るページ表示
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function favorite() {
		$this->Session->write(NC_SYSTEM_KEY.'.page_menu.action', $this->action);
		$this->render('index');
	}

/**
 * ページ情報表示・登録
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function meta() {
		$this->Session->write(NC_SYSTEM_KEY.'.page_menu.action', $this->action);
		$this->render('index');
	}

/**
 * ページスタイル表示・登録
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function style() {
		$this->Session->write(NC_SYSTEM_KEY.'.page_menu.action', $this->action);
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
		$this->Session->write(NC_SYSTEM_KEY.'.page_menu.action', $this->action);
		$this->render('index');
	}

/**
 * ページレイアウト表示・登録
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function layout() {
		$this->Session->write(NC_SYSTEM_KEY.'.page_menu.action', $this->action);
		$this->render('index');
	}

/**
 * ページ設定非表示
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function close() {
		$this->Session->delete(NC_SYSTEM_KEY.'.page_menu');
		$this->render(false, 'ajax');
	}

/**
 * ページ設定最小化-最大化
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function display() {
		$pos = isset($this->request->query['pos']) ? intval($this->request->query['pos']) : _OFF;
		if($pos == 0) {
			$this->Session->write(NC_SYSTEM_KEY.'.page_menu.pos', intval($pos));
		} else {
			$this->Session->delete(NC_SYSTEM_KEY.'.page_menu.pos');
		}
		$this->render(false, 'ajax');
	}
}
