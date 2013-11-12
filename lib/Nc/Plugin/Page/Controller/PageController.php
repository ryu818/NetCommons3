<?php
/**
 * PageControllerクラス
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
	public $components = array('Security', 'Page.PageMenu');

/**
 * Model name
 * @var array
 */
	public $uses = array('Community', 'CommunityLang', 'CommunityTag', 'Background');

/**
 * Heilper name
 * @var array
 */
	public $helpers = array(
		'Paginator', 'Page.PageLayout'
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

		$activeLang = isset($this->request->named['active_lang']) ? $this->request->named['active_lang'] : null;
		$sessLang = $this->Session->read(NC_CONFIG_KEY.'.language');
		if(!isset($activeLang)) {
			$activeLang = $this->Session->read(NC_SYSTEM_KEY.'.page_menu.activeLang');
		}

		$this->Session->write(NC_SYSTEM_KEY.'.page_menu.lang', $sessLang);
		parent::beforeFilter();
		$languages = Configure::read(NC_CONFIG_KEY.'.'.'languages');
		if(isset($activeLang) && isset($languages[$activeLang])) {
			Configure::write(NC_CONFIG_KEY.'.'.'language', $activeLang);
			$this->Session->write(NC_CONFIG_KEY.'.language', $activeLang);
			$this->Session->write(NC_SYSTEM_KEY.'.page_menu.activeLang', $activeLang);
		}

		if ($this->action == 'index') {
			$this->Security->validatePost = false;
			$this->Security->csrfUseOnce = false;
		} else if ($this->action == 'background') {
			if ($this->request->is('post') && in_array($this->request->data['type'], array('search', 'patterns_search', 'images_search'))) {
				$this->Security->csrfUseOnce = false;
			}
			$this->Security->unlockedFields = array('type', 'isRedirect','Background', 'PageStyle.style','PageStyle.original_background_image','PageStyle.original_background_repeat');
		} else if($this->action == 'display_position') {
			$this->Security->unlockedFields = array('type', 'isRedirect',
				'PageStyle.width-custom',
				'PageStyle.height-custom',
				'PageStyle.style.margin-left',
				'PageStyle.style.margin-right',
			);
		} else if($this->action == 'layout') {
			$this->Security->unlockedFields = array('type', 'isRedirect','PageLayout.layouts');
		} else {
			$this->Security->unlockedFields = array('type', 'isRedirect');
		}

		$loginUserId = $this->Auth->user('id');
		if(((empty($loginUserId) && $this->action != 'index' && $this->action != 'close' && $this->action != 'display')) ||
			(empty($loginUserId) && !Configure::read(NC_CONFIG_KEY.'.'.'display_page_menu'))) {
			throw new BadRequestException(__('Unauthorized request.<br />Please reload the page.'));
		}
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
		if($this->action != 'index' && $this->action != 'close') {
			$this->Session->delete(NC_SYSTEM_KEY.'.page_menu.lang');
			$this->Session->delete(NC_SYSTEM_KEY.'.page_menu.activeLang');
		}
		$preLang = $this->Session->read(NC_SYSTEM_KEY.'.page_menu.lang');
		if(isset($preLang)) {
			Configure::write(NC_CONFIG_KEY.'.'.'language', $preLang);
			$this->Session->write(NC_CONFIG_KEY.'.language', $preLang);
		}
	}

/**
 * ページメニュー表示
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function index() {
		$this->Session->write(NC_SYSTEM_KEY.'.page_menu.action', $this->action);
		$this->Session->delete(NC_SYSTEM_KEY.'.page_menu.PageUserLink');

		$centerPage = Configure::read(NC_SYSTEM_KEY.'.'.'center_page');
		$loginUser = $this->Auth->user();
		$userId = $loginUser['id'];

		$isEdit = isset($this->request->query['is_edit']) ? intval($this->request->query['is_edit']) : _OFF;
		$isDetail = isset($this->request->query['is_detail']) ? intval($this->request->query['is_detail']) : _OFF;
		$activeTab = isset($this->request->query['active_tab']) ? intval($this->request->query['active_tab']) : null;
		$isPaginator = !empty($this->request->named['is_paginator']) ? intval($this->request->named['is_paginator']) : _OFF;
		//$paginatorPage = !empty($this->request->named['page']) ? intval($this->request->named['page']) : null;
		$limit = !empty($this->request->named['limit']) ? intval($this->request->named['limit']) : PAGES_COMMUNITY_LIMIT;
		$views = !empty($this->request->named['views']) ? intval($this->request->named['views']) : PAGES_COMMUNITY_VIEWS;
		$pageId = isset($this->request->query['page_id']) ? intval($this->request->query['page_id']) : (isset($centerPage) ? $centerPage['Page']['id'] : null);
		$participantPageId = isset($this->request->query['participant_page_id']) ? intval($this->request->query['participant_page_id']) : null;
		if(!empty($participantPageId)) {
			$participantPage = $this->Page->findById($participantPageId);
			if($participantPage && $participantPage['Page']['space_type'] == NC_SPACE_TYPE_GROUP) {
				$activeTab = 1;
			}
		}

		// 言語切替
		$languages = Configure::read(NC_CONFIG_KEY.'.'.'languages');
		$lang = Configure::read(NC_CONFIG_KEY.'.'.'language');

		$this->paginate['limit'] = $limit;

		if($isPaginator) {
			$activeTab = 1;
			if(!isset($this->request->named['page'])) {
				// set page
				$this->request->params['named']['page'] = 1;
			}
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
				if($isEdit && $page['Page']['thread_num'] == 2 && $page['Page']['display_sequence'] == 1) {
					// Topページ
					$pageId = $page['Page']['parent_id'];
					$page = $this->Page->findById($pageId);
				}
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
			// コピー、ペーストでコミュニティーへペーストした場合、コミュニティタブへ
			$activeTab = $selActiveTab;
		}

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
			if(!empty($loginUser['allow_creating_community'])) {
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
		if($isEdit && $loginUser['allow_creating_community'] == NC_ALLOW_CREATING_COMMUNITY_ADMIN) {
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
				//'isShowAllCommunity' => false,
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
 * Style initialize処理
 * 	権限チェック、言語設定
 * @param   void
 * @return  false|Model Page $centerPage
 * @since   v 3.0.0.0
 */
	protected function _initializeSetting() {
		// 言語切替
		$languages = Configure::read(NC_CONFIG_KEY.'.'.'languages');
		if($this->action == 'style' && !$this->request->is('post')) {
			$elementParams['languages'] = $languages;
			$this->set('element_params', $elementParams);
		} else {
			$this->set('languages', $languages);
		}

		$centerPage = $this->_checkAuthChief();
		$this->set('page', $centerPage);
		if($centerPage === false) {
			return false;
		}
		if(!$this->_checkAuthAction()) {
			return false;
		}

		return $centerPage;
	}

/**
 * Centerページが主担より小さいならばエラー
 * @param   void
 * @return  false|Model Page $centerPage
 * @since   v 3.0.0.0
 */
	protected function _checkAuthChief() {
		$centerPage = Configure::read(NC_SYSTEM_KEY.'.'.'center_page');

		if(!isset($centerPage['PageAuthority']) || $centerPage['PageAuthority']['hierarchy'] < NC_AUTH_MIN_CHIEF) {
			$this->Session->write(NC_SYSTEM_KEY.'.page_menu.action', 'index');
			$this->response->statusCode('403');
			$this->flash(__('Authority Error!  You do not have the privilege to access this page.'), '');
			return false;
		}
		return $centerPage;
	}

/**
 * 各設定画面の編集権限チェック
 * @param   void
 * @return  boolean
 * @since   v 3.0.0.0
 */
	protected function _checkAuthAction() {
		$loginUser = $this->Auth->user();
		$ret = true;

		switch($this->action) {
			case 'meta':
				if(!$loginUser['allow_meta_flag']) {
					$ret = false;
				}
				break;
			case 'theme':
				if(!$loginUser['allow_theme_flag']) {
					$ret = false;
				}
				break;
			case 'style':
			case 'background':
			case 'color':
			case 'display_position':
			case 'edit_css':
				if(!$loginUser['allow_style_flag']) {
					$ret = false;
				}
				break;
			case 'layout':
				if(!$loginUser['allow_layout_flag']) {
					$ret = false;
				}
				break;
		}
		if(!$ret) {
			$this->Session->write(NC_SYSTEM_KEY.'.page_menu.action', 'index');
			$this->response->statusCode('403');
			$this->flash(__('Authority Error!  You do not have the privilege to access this page.'), '');
			return $ret;
		}
		return $ret;
	}

/**
 * ページスタイル表示・登録(フォント設定)
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function style() {
		$centerPage = $this->_initializeSetting();
		if(!$centerPage) {
			return;
		}
		$pageStyles = $this->PageStyle->findScopeStyle('all', $centerPage);

		$pageStyles[NC_PAGE_TYPE_FONT_ID] = $this->_saveScope($this->PageStyle, isset($pageStyles[NC_PAGE_TYPE_FONT_ID]) ? $pageStyles[NC_PAGE_TYPE_FONT_ID] : null, $centerPage, NC_PAGE_TYPE_FONT_ID);
		$this->set('page_style', isset($pageStyles[NC_PAGE_TYPE_FONT_ID][0]) ? $pageStyles[NC_PAGE_TYPE_FONT_ID][0] : null);
		$this->set('id', 'pages-menu-style');
		if ($this->request->is('post')) {
			$this->render('/Elements/style/font');
		} else {
			$this->Session->write(NC_SYSTEM_KEY.'.page_menu.action', $this->action);
			$this->render('index');
		}
	}

/**
 * ページスタイル 登録処理
 * @param   Model   $Model
 * @param   array $pageStyles
 * @param   array $centerPage
 * @param   string $type
 * @return  void
 * @since   v 3.0.0.0
 */
	protected function _saveScope(Model $Model, $pageStyles, $centerPage, $type = _OFF) {
		if ($this->request->is('post')) {
			$loginUser = $this->Auth->user();
			$isAdmin = ($this->Authority->getUserAuthorityId($loginUser['hierarchy']) == NC_AUTH_ADMIN_ID) ? true : false;

			if($Model->name == 'PageStyle') {
				if(isset($this->request->data[$Model->alias]['width']) && $this->request->data[$Model->alias]['width'] == 'by hand') {
					$this->request->data[$Model->alias]['width'] = $this->request->data[$Model->alias]['width-custom'];
				}
				if(isset($this->request->data[$Model->alias]['height']) && $this->request->data[$Model->alias]['height'] == 'by hand') {
					$this->request->data[$Model->alias]['height'] = $this->request->data[$Model->alias]['height-custom'];
				}
			} else
				if($Model->name == 'PageLayout' && isset($this->request->data[$Model->alias]['layouts'])) {
				$layouts = explode('_', $this->request->data[$Model->alias]['layouts']);
				$this->request->data[$Model->alias]['is_display_header'] = intval($layouts[0]);
				$this->request->data[$Model->alias]['is_display_left'] = intval($layouts[1]);
				$this->request->data[$Model->alias]['is_display_right'] = intval($layouts[2]);
				$this->request->data[$Model->alias]['is_display_footer'] = intval($layouts[3]);
				unset($this->request->data[$Model->alias]['layouts']);
			}
			unset($this->request->data[$Model->alias]['content']);
			if($isAdmin && $Model->name == 'PageStyle' && isset($this->request->data[$Model->alias]['css'])) {
				$this->request->data[$Model->alias]['content'] = $this->request->data[$Model->alias]['css'];
			} else if($Model->name == 'PageStyle' && isset($this->request->data[$Model->alias]['style'])) {
				$this->autoLayout = false;
				$this->autoRender = false;

				if(isset($this->request->data[$Model->alias]['style']['body']['background-image'])) {
					$this->request->data[$Model->alias]['style']['body']['background-image'] = preg_replace('#^'.preg_quote(Router::url('/', true), '#').'#i', '../../', $this->request->data[$Model->alias]['style']['body']['background-image']);
				}

				if(isset($this->request->data[$Model->alias]['original_background_position'])) {
					$this->request->data[$Model->alias]['style']['body']['background-position'] = $this->request->data[$Model->alias]['original_background_position'];
				}
				if(isset($this->request->data[$Model->alias]['original_background_attachment'])) {
					$this->request->data[$Model->alias]['style']['body']['background-attachment'] = $this->request->data[$Model->alias]['original_background_attachment'];
				}
				if(isset($this->request->data[$Model->alias]['original_background_repeat'])) {
					$this->request->data[$Model->alias]['style']['body']['background-repeat'] = $this->request->data[$Model->alias]['original_background_repeat'];
				}

				if(isset($this->request->data[$Model->alias]['style']['#parent-container']['background-image'])) {
					$this->request->data[$Model->alias]['style']['#parent-container']['background-image'] = preg_replace('#^'.preg_quote(Router::url('/', true), '#').'#i', '../../', $this->request->data[$Model->alias]['style']['#parent-container']['background-image']);
				}
				if(isset($this->request->data[$Model->alias]['style']['#container']['margin-top'])) {
					$this->request->data[$Model->alias]['style']['#container']['margin-top'] = $this->request->data[$Model->alias]['style']['#container']['margin-top'] . 'px';
				}
				if(isset($this->request->data[$Model->alias]['style']['#container']['margin-right'])) {
					$this->request->data[$Model->alias]['style']['#container']['margin-right'] = $this->request->data[$Model->alias]['style']['#container']['margin-right'] . 'px';
				}
				if(isset($this->request->data[$Model->alias]['style']['#container']['margin-bottom'])) {
					$this->request->data[$Model->alias]['style']['#container']['margin-bottom'] = $this->request->data[$Model->alias]['style']['#container']['margin-bottom'] . 'px';
				}
				if(isset($this->request->data[$Model->alias]['style']['#container']['margin-left'])) {
					$this->request->data[$Model->alias]['style']['#container']['margin-left'] = $this->request->data[$Model->alias]['style']['#container']['margin-left'] . 'px';
				}
				if(isset($this->request->data[$Model->alias]['align'])) {
					if($this->request->data[$Model->alias]['align'] == 'center') {
						$this->request->data[$Model->alias]['style']['#container']['margin-left'] = 'auto';
						$this->request->data[$Model->alias]['style']['#container']['margin-right'] = 'auto';
					} else if($this->request->data[$Model->alias]['align'] == 'right') {
						$this->request->data[$Model->alias]['style']['#container']['float'] = 'right';
					}
				}
				if(isset($this->request->data[$Model->alias]['width'])) {
					if($this->request->data[$Model->alias]['width'] != 'auto' && $this->request->data[$Model->alias]['width'] != '100%') {
						$this->request->data[$Model->alias]['style']['#container']['width'] = $this->request->data[$Model->alias]['width'] . 'px';
					} else {
						$this->request->data[$Model->alias]['style']['#container']['width'] = $this->request->data[$Model->alias]['width'];
					}
				}
				if(isset($this->request->data[$Model->alias]['height'])) {
					if($this->request->data[$Model->alias]['height'] != 'auto' && $this->request->data[$Model->alias]['height'] != '100%') {
						$this->request->data[$Model->alias]['style']['#container']['height'] = $this->request->data[$Model->alias]['height'] . 'px';
					} else {
						$this->request->data[$Model->alias]['style']['#container']['height'] = $this->request->data[$Model->alias]['height'] ;
					}
				}

				$this->set('data', $this->request->data[$Model->alias]['style']);
				$content = $this->render('/Elements/style/regist_template');

				$this->request->data[$Model->alias]['content'] = $content->body();

				$this->autoLayout = true;
				$this->autoRender = true;
			}


			$pageStyles = $Model->saveScope($pageStyles, $centerPage, $this->request->data, $type);
			if($pageStyles !== false && count($Model->validationErrors) == 0) {
				if(empty($pageStyles[0][$Model->alias]['id'])) {
					$this->Session->setFlash(__('Has been successfully registered.'));
				} else {
					$this->Session->setFlash(__('Has been successfully updated.'));
				}
				if(!empty($this->request->data['isRedirect'])) {
					// Redirectするリクエストなのでexitして、Session->setFlashをRedirect後の画面に表示する。
					// debug情報も出力していない。
					$this->_stop();
				}
			}
		}
		return $pageStyles;
	}

/**
 * ページスタイル表示・登録(背景)
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function background() {
		$centerPage = $this->_initializeSetting();
		if(!$centerPage) {
			return;
		}
		$pageStyles = $this->PageStyle->findScopeStyle('all', $centerPage);
		if($this->request->is('post') && !in_array($this->request->data['type'], array('search', 'patterns_search', 'images_search'))) {
			$pageStyles[NC_PAGE_TYPE_BACKGROUND_ID] = $this->_saveScope($this->PageStyle, isset($pageStyles[NC_PAGE_TYPE_BACKGROUND_ID]) ? $pageStyles[NC_PAGE_TYPE_BACKGROUND_ID] : null, $centerPage, NC_PAGE_TYPE_BACKGROUND_ID);
		}
		$conditions = array();
		$category = (!isset($this->request->data['Background']['category']) || $this->request->data['Background']['category'] == 'all') ? null : $this->request->data['Background']['category'];
		$color = (!isset($this->request->data['Background']['color']) || $this->request->data['Background']['color'] == 'all') ? null : $this->request->data['Background']['color'];
		$limit = !isset($this->request->data['Background']['limit']) ? PAGES_STYLE_BACKGROUND_LIMIT : intval($this->request->data['Background']['limit']);
		if(isset($this->request->data['type']) && $this->request->data['type'] == 'submit') {
			$patternPage = 1;
			$imagePage = 1;
		} else {
			$patternPage = !isset($this->request->data['Background']['patterns_page']) ? 1 : intval($this->request->data['Background']['patterns_page']);
			$imagePage = !isset($this->request->data['Background']['images_page']) ? 1 : intval($this->request->data['Background']['images_page']);
		}
		if ($this->request->is('post') && in_array($this->request->data['type'], array('search', 'patterns_search', 'images_search'))) {
			// 絞り込み
			if(isset($category)) {
				$conditions['Background.category'] = $category;
			}
			if(isset($color)) {
				$conditions['Background.color'] = $color;
			}
			if(isset($this->request->data['PageStyle']['scope'])) {
				$pageStyles[NC_PAGE_TYPE_BACKGROUND_ID]['PageStyle']['scope'] = $this->request->data['PageStyle']['scope'];
			}
			if(isset($this->request->data['PageStyle']['lang'])) {
				$pageStyles[NC_PAGE_TYPE_BACKGROUND_ID]['PageStyle']['lang'] = $this->request->data['PageStyle']['lang'];
			}
		}
		$conditions['type'] = 'pattern';
		$patterns = $this->Background->findList('all', array('conditions' => $conditions, 'offset' => $limit *($patternPage-1), 'limit' => $limit+1));
		$this->set('patterns', $patterns);
		$conditions['type'] = 'image';
		$images = $this->Background->findList('all', array('conditions' => $conditions, 'offset' => $limit *($imagePage-1), 'limit' => $limit+1));
		$this->set('images', $images);

		$this->set('page_style', isset($pageStyles[NC_PAGE_TYPE_BACKGROUND_ID][0]) ? $pageStyles[NC_PAGE_TYPE_BACKGROUND_ID][0] : null);
		$this->set('id', 'pages-menu-background');
		$this->set('category', $category);
		$this->set('color', $color);

		$this->set('has_pattern', ($limit < count($patterns)) ? true : false);
		$this->set('has_image',($limit < count($images)) ? true : false);
		$this->set('pattern_page', $patternPage);
		$this->set('image_page', $imagePage);
		$this->set('limit', $limit);

		if ($this->request->is('post') && in_array($this->request->data['type'], array('search', 'patterns_search', 'images_search'))) {
			$this->render('/Elements/style/search_background');
		} else {
			$this->render('/Elements/style/background');
		}
	}
/**
 * ページスタイル - 色選択
 * @param   string  $type patterns or images
 * @param   integer $groupId
 * @return  void
 * @since   v 3.0.0.0
 */
	public function color($type, $groupId) {

		$params = array(
			'conditions' => array('group_id' => intval($groupId)),
		);
		if(!empty($this->request->named['category'])) {
			$params['conditions']['category'] = $this->request->named['category'];
		}
		if(!empty($this->request->named['color'])) {
			$params['conditions']['color'] = $this->request->named['color'];
		}
		$backgrounds = $this->Background->find('all', $params);
		if (count($backgrounds) == 0) {
			throw new InternalErrorException(__('Failed to obtain the database, (%s).', 'backgrounds'));
		}
		if($type != 'patterns' && $type != 'images') {
			$type = 'images';
		}
		$this->set('type', $type);
		$this->set('backgrounds', $backgrounds);
		$this->render('/Elements/style/color');
	}

/**
 * ページスタイル表示・登録(表示位置)
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function display_position() {
		$centerPage = $this->_initializeSetting();
		if(!$centerPage) {
			return;
		}
		$pageStyles = $this->PageStyle->findScopeStyle('all', $centerPage);

		$pageStyles[NC_PAGE_TYPE_DISPLAY_ID] = $this->_saveScope($this->PageStyle, isset($pageStyles[NC_PAGE_TYPE_DISPLAY_ID]) ? $pageStyles[NC_PAGE_TYPE_DISPLAY_ID] : null, $centerPage, NC_PAGE_TYPE_DISPLAY_ID);
		$this->set('page_style', isset($pageStyles[NC_PAGE_TYPE_DISPLAY_ID][0]) ? $pageStyles[NC_PAGE_TYPE_DISPLAY_ID][0] : null);
		$this->set('id', 'pages-menu-display-position');

		$this->render('/Elements/style/display_position');
	}

/**
 * ページスタイル表示・登録(CSS編集)
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function edit_css() {
		App::uses('File', 'Utility');
		$centerPage = $this->_initializeSetting();
		if(!$centerPage) {
			return;
		}
		$pageStyles = $this->PageStyle->findScopeStyle('all', $centerPage);
		$pageStyles[NC_PAGE_TYPE_EDIT_CSS_ID] = $this->_saveScope($this->PageStyle, isset($pageStyles[NC_PAGE_TYPE_EDIT_CSS_ID]) ? $pageStyles[NC_PAGE_TYPE_EDIT_CSS_ID] : null, $centerPage, NC_PAGE_TYPE_EDIT_CSS_ID);
		$pageStyle = isset($pageStyles[NC_PAGE_TYPE_EDIT_CSS_ID][0]) ? $pageStyles[NC_PAGE_TYPE_EDIT_CSS_ID][0] : null;

		if(!isset($pageStyle['PageStyle']['content'])) {
			$paths = App::path('webroot');
			foreach ($paths as $path) {
				$pathCommon = $path . 'css' . DS . 'common' . DS . 'editable' . DS .'common.css';
				if(file_exists($pathCommon)) {
					$file = new File($pathCommon);
					$pageStyle['PageStyle']['content'] = $file->read();
					$file->close();
					break;
				}
			}
		}

		if(!$this->request->is('post') && isset($pageStyle['PageStyle']['file'])) {
			$file = new File( $this->PageStyle->getPath() . DS . $pageStyle['PageStyle']['file']);
			$pageStyle['PageStyle']['content'] = $file->read();
			$file->close();
		}
		$this->set('page_style', $pageStyle);
		$this->set('id', 'pages-menu-edit-css');
		$this->render('/Elements/style/edit_css');
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
		App::uses('Folder', 'Utility');
		App::uses('File', 'Utility');

		$filePath = App::pluginPath('Page') . 'webroot'. DS . 'img'. DS . 'layouts';
		$layoutDir = new Folder($filePath);
		$layoutFiles = $layoutDir->find('.*\.(jpg|gif|png)');
		$centerPage = $this->_initializeSetting();
		if(!$centerPage) {
			return;
		}
		$pageLayouts = $this->PageLayout->findScope('all', $centerPage);
		$pageLayouts = $this->_saveScope($this->PageLayout, $pageLayouts, $centerPage);

		$this->set('page_layout', isset($pageLayouts[0]) ? $pageLayouts[0] : null);
		$this->set('id', 'pages-menu-layout');
		$this->set('layoutFiles', $layoutFiles);
		if ($this->request->is('post')) {
			$this->render('/Elements/layout/layout');
		} else {
			$this->Session->write(NC_SYSTEM_KEY.'.page_menu.action', $this->action);
			$this->render('index');
		}
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
