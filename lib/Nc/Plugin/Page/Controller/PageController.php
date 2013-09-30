<?php
/**
 * PageControllerクラス
 * TODO:Tokenチェックもおこなっていない。
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
		include_once dirname(dirname(__FILE__)).'/Config/defines.inc.php';
		
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

		$centerPage = Configure::read(NC_SYSTEM_KEY.'.'.'center_page');
		$loginUser = $this->Auth->user();
		$userId = $loginUser['id'];

		$isEdit = isset($this->request->query['is_edit']) ? intval($this->request->query['is_edit']) : _OFF;
		$isDetail = isset($this->request->query['is_detail']) ? intval($this->request->query['is_detail']) : _OFF;
		$activeTab = isset($this->request->query['active_tab']) ? intval($this->request->query['active_tab']) : null;
		$limit = !empty($this->request->named['limit']) ? intval($this->request->named['limit']) : PAGES_COMMUNITY_LIMIT;
		$views = !empty($this->request->named['views']) ? intval($this->request->named['views']) : PAGES_COMMUNITY_VIEWS;
		$pageId = isset($this->request->query['page_id']) ? intval($this->request->query['page_id']) : (isset($centerPage) ? $centerPage['Page']['id'] : null);

		// 言語切替
		$languages = $this->Language->findSelectList();
		if(isset($active_lang) && isset($languages[$active_lang])) {
			$preLang = $this->Session->read(NC_SYSTEM_KEY.'.page_menu.pre_lang');
			if(!isset($preLang)) {
				$this->Session->write(NC_SYSTEM_KEY.'.page_menu.pre_lang', $this->Session->read(NC_CONFIG_KEY.'.language'));
			}
			$this->Session->write(NC_SYSTEM_KEY.'.page_menu.lang', $active_lang);

			Configure::write(NC_CONFIG_KEY.'.'.'language', $active_lang);
			$this->Session->write(NC_CONFIG_KEY.'.language', $active_lang);
		}
		$lang = Configure::read(NC_CONFIG_KEY.'.'.'language');

		$this->paginate['limit'] = $limit;

		if(isset($this->request->named['page']) || isset($this->request->named['limit'])) {
			$activeTab = 1;
		} else if(!isset($activeTab)) {
			$activeTab = ($this->nc_page['Page']['space_type'] == NC_SPACE_TYPE_GROUP) ? 1 : 0;
		}

		$elementParams = array(
			'is_edit' => $isEdit,
			'community_params' => array()
		);

		$selActiveTab = 0;
		if($centerPage['Page']['space_type'] == NC_SPACE_TYPE_GROUP) {
			$selActiveTab = 1;
		}

		$params = null;
		if($isEdit) {
			$params = array(
				'conditions' => array(
					'Page.lang' => array('', $lang)
				)
			);
		}
		// activeなページがコミュニティーならば、コミュニティー一覧の何ページ目かにあるかを設定
		if(!isset($this->request->named['page'])) {
			if(isset($pageId)) {
				$page = $this->Page->findById($pageId);
				$communityPage = $this->Page->findById($page['Page']['root_id']);
			} else if($centerPage['Page']['space_type'] == NC_SPACE_TYPE_GROUP) {
				$communityPage = $this->Page->findById($centerPage['Page']['root_id']);
			}

			if(isset($communityPage) && $communityPage['Page']['space_type'] == NC_SPACE_TYPE_GROUP) {
				$this->paginate['page'] = ceil(intval($communityPage['Page']['display_sequence'])/$this->paginate['limit']);
				$selActiveTab = 1;
			} else {
				$selActiveTab = 0;
			}
		}
		if(isset($this->request->query['page_id'])) {
			// コピー、ペーストでコミュニティへペーストした場合、コミュニティタブへ
			$activeTab = $selActiveTab;
		}

		// 管理系の権限を取得
		if($userId) {
			$adminHierarchy = $this->ModuleSystemLink->findHierarchyByPluginName($this->request->params['plugin'], $loginUser['authority_id']);
		} else {
			$adminHierarchy = NC_AUTH_OTHER;
		}
		$elementParams['admin_hierarchy'] = $adminHierarchy;

		$parentPage = $this->Page->findAuthById($centerPage['Page']['parent_id'], $userId);
		if(!isset($parentPage['Page'])) {
			$this->response->statusCode('404');
			$this->flash(__('Page not found.'), '');
			return;
		}
		if($isEdit) {
			// active pageでページ追加をactiveにするかどうか。
			$isAdd = false;
			$isAddCommunity = false;

			if($centerPage['Page']['thread_num'] <= 1) {
				if($centerPage['PageAuthority']['hierarchy'] >= NC_AUTH_MIN_CHIEF) {
					$isAdd = true;
				}
			} else if($parentPage['PageAuthority']['hierarchy'] >= NC_AUTH_MIN_CHIEF) {
				$isAdd = true;
			}
			if($adminHierarchy >= NC_AUTH_MIN_MODERATE) {
				$isAddCommunity = true;
			}
			if(($centerPage['Page']['space_type'] != NC_SPACE_TYPE_GROUP && $activeTab == 1) ||
					($centerPage['Page']['space_type'] == NC_SPACE_TYPE_GROUP && $activeTab == 0)) {
				$isAdd = false;
			}
			$elementParams['is_add'] = $isAdd;
			$elementParams['is_add_community'] = $isAddCommunity;
		}

		if($isDetail) {
			$bufThreadNum = $centerPage['Page']['thread_num'];
			if($centerPage['Page']['thread_num'] == 2 && $centerPage['Page']['display_sequence'] == 1) {
				// Topページ
				$parentPage = $this->Page->findAuthById($parentPage['Page']['parent_id'], $userId);	// 再取得
				if(!isset($parentPage['Page'])) {
					$this->response->statusCode('404');
					$this->flash(__('Page not found.'), '');
					return;
				}
				$pageId = $centerPage['Page']['parent_id'];
				$bufThreadNum --;
			}

			if($bufThreadNum == 1 && $centerPage['Page']['space_type'] == NC_SPACE_TYPE_GROUP) {
				// コミュニティーならば
				$ret = $this->Community->getCommunityData($centerPage['Page']['room_id']);
				if($ret === false) {
					throw new InternalErrorException(__('Failed to obtain the database, (%s).', 'communities'));
				}
				list($community, $communityLang, $communityTag) = $ret;

				$elementParams['community_params']['community'] = $community;
				$elementParams['community_params']['community_lang'] = $communityLang;
				$elementParams['community_params']['community_tag'] = $communityTag;
				$elementParams['community_params']['photo_samples'] = $this->PageMenu->getCommunityPhoto();
			}
		}

		$addParams = array(
			'conditions' => array(
				'Page.space_type' => array(NC_SPACE_TYPE_PUBLIC, NC_SPACE_TYPE_MYPORTAL, NC_SPACE_TYPE_PRIVATE)
			)
		);
		$options = array(
			'isShowAllCommunity' => false,
			'isMyPortalCurrent' => true,
			'ativePageId' => $pageId,
		);
		$pages = $this->Page->findViewable('menu', $userId, $addParams, $options);

		// コミュニティー数
		//$this->paginate['extra'] = array('user_id' => $userId);
		$this->paginate['user_id'] = $userId;
		if($isEdit && $adminHierarchy >= NC_AUTH_MIN_ADMIN) {
			$this->paginate['is_all'] = true;
		} else {
			$this->paginate['is_all'] = false;
		}
		$pagesTopGroup = $this->paginate('Page');
		$pagesGroup = array();
		if(count($pagesTopGroup) > 0) {
			$addParams = array(
				'conditions' => array(
					'Page.root_id' => $pagesTopGroup,
					'Page.space_type' => NC_SPACE_TYPE_GROUP,
				)
			);
			$options = array(
				'isShowAllCommunity' => true,
				'isMyPortalCurrent' => false,
				'ativePageId' => $pageId,
			);
			$pagesGroup = $this->Page->findViewable('menu', ($this->paginate['is_all']) ? 'all' : $userId, $addParams, $options);
		}
		$copyPageId = $this->Session->read('Pages.'.'copy_page_id');
		if(isset($copyPageId)) {
			$copyPage = $this->Page->findAuthById($copyPageId, $userId);
			$elementParams['copy_page_id'] = $copyPageId;
			$elementParams['copy_page'] = $copyPage;
		} else {
			$elementParams['copy_page_id'] = 0;
		}

		$elementParams['languages'] = $languages;
		$elementParams['pages'] = $pages;
		$elementParams['pages_group'] = $pagesGroup;
		$elementParams['page_id'] = $pageId;
		$elementParams['is_detail'] = $isDetail;
		$elementParams['parent_page'] = $parentPage;
		////$elementParams['pages_group_total_count'] = $pagesGroupTotalCount;
		$elementParams['active_tab'] = $activeTab;
		$elementParams['sel_active_tab'] = $selActiveTab;
		$elementParams['views'] = $views;
		$elementParams['limit'] = $limit;
		$elementParams['limit_select_values'] = explode('|', PAGES_COMMUNITY_LIMIT_SELECT);

		$this->set('element_params', $elementParams);
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
 * ページスタイル表示・登録(フォント設定)
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function style() {
		// 言語切替
		$languages = $this->Language->findSelectList();
		
		$this->Session->write(NC_SYSTEM_KEY.'.page_menu.action', $this->action);
		
		$centerPage = Configure::read(NC_SYSTEM_KEY.'.'.'center_page');
		if($centerPage['PageAuthority']['hierarchy'] < NC_AUTH_MIN_CHIEF) {
			$this->Session->write(NC_SYSTEM_KEY.'.page_menu.action', 'index');
			$this->response->statusCode('403');
			$this->flash(__('Authority Error!  You do not have the privilege to access this page.'), '');
			return;
		}
		$pageStyles = $this->PageStyle->findScope($centerPage);
		if ($this->request->is('post') && $this->request->data['type'] == 'reset' && isset($pageStyles[NC_PAGE_TYPE_FONT_ID]['PageStyle'])) {
			// リセット処理
			if(!$this->PageStyle->delete($pageStyles[NC_PAGE_TYPE_FONT_ID]['PageStyle']['id'])) {
				throw new InternalErrorException(__('Failed to delete the database, (%s).', 'page_styles'));
			}
			array_shift($pageStyles[NC_PAGE_TYPE_FONT_ID]);
			$this->Session->setFlash(__('Has been successfully updated.'));
		} else if ($this->request->is('post') && $this->request->data['type'] == 'submit' &&
				isset($this->request->data['PageStyle']['style'])) {

			// 削除処理
			$savePageStyle = $this->request->data;
			$savePageStyle['PageStyle']['type'] = NC_PAGE_TYPE_FONT_ID;
			if($savePageStyle['PageStyle']['lang'] == 'all') {
				$savePageStyle['PageStyle']['lang'] = '';
			}

			if($savePageStyle['PageStyle']['scope'] == NC_PAGE_SCOPE_SITE) {
				$savePageStyle['PageStyle']['space_type'] = _OFF;
			} else {
				$savePageStyle['PageStyle']['space_type'] = $centerPage['Page']['space_type'];
			}
			if(in_array($savePageStyle['PageStyle']['scope'], array(NC_PAGE_SCOPE_SITE, NC_PAGE_SCOPE_SPACE)) ) {
				$savePageStyle['PageStyle']['page_id'] = _OFF;
			} else {
				$savePageStyle['PageStyle']['page_id'] = $centerPage['Page']['id'];
			}
			$this->PageStyle->set($savePageStyle);
			if($this->PageStyle->validates()) {
				
				$id = null;
				if(isset($pageStyles[NC_PAGE_TYPE_FONT_ID])) {
					// 現在、設定中のものより優先順位が高いものが既に登録してあったら、削除。
					foreach($pageStyles[NC_PAGE_TYPE_FONT_ID] as $pageStyle) {
						if($pageStyle['scope'] == $savePageStyle['PageStyle']['scope'] &&
							$pageStyle['space_type'] == $savePageStyle['PageStyle']['space_type'] &&
							$pageStyle['lang'] == $savePageStyle['PageStyle']['lang'] &&
							$pageStyle['page_id'] == $savePageStyle['PageStyle']['page_id']) {
							/////(empty($centerPage['Page']['page_style_id']) || $pageStyle['id'] == $centerPage['Page']['page_style_id'])) {
							$id = $pageStyle['id'];
						} else if($pageStyle['scope'] > $savePageStyle['PageStyle']['scope']) {
							if(!$this->PageStyle->delete($pageStyle['id'])) {
								throw new InternalErrorException(__('Failed to delete the database, (%s).', 'page_styles'));
							}
						}
					}
				}
				
				// 登録処理
				$this->autoLayout = false;
				$this->autoRender = false;
				$savePageStyle['PageStyle']['id'] = $id;
				
				$this->set('data', $savePageStyle['PageStyle']['style']);
				$content = $this->render('/Elements/style/regist_template');
				
				$savePageStyle['PageStyle']['content'] = $content->body();
				if(!$this->PageStyle->save($savePageStyle)) {
					throw new InternalErrorException(__('Failed to register the database, (%s).', 'page_styles'));
				}
				$this->autoLayout = true;
				$this->autoRender = true;
				$pageStyles[NC_PAGE_TYPE_FONT_ID] = $savePageStyle;
				if(empty($id)) {
					$this->Session->setFlash(__('Has been successfully registered.'));
				} else {
					$this->Session->setFlash(__('Has been successfully updated.'));
				}
			}
		}
		
		$this->set('page', $centerPage);
		$this->set('page_styles', $pageStyles);
		if ($this->request->is('post')) {
			$this->set('languages', $languages);
			$this->render('/Elements/style/font');
		} else {
			$elementParams['languages'] = $languages;
			$this->set('element_params', $elementParams);
			$this->render('index');
		}
	}
	

/**
 * ページスタイル表示・登録(背景)
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function background() {
		$this->render('/Elements/style/background');
	}


/**
 * ページスタイル表示・登録(表示位置)
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function display_position() {
		$this->render('/Elements/style/display_position');
	}
	

/**
 * ページスタイル表示・登録(カスタム設定)
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function custom() {
		$this->render('/Elements/style/custom');
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
