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
 * Style initialize処理
 * @param   void
 * @param   string $id
 * @return  Model Page $centerPage
 * @since   v 3.0.0.0
 */
	protected function _initializeStyle() {
		// 言語切替
		$languages = $this->Language->findSelectList();
		$centerPage = Configure::read(NC_SYSTEM_KEY.'.'.'center_page');
		if($centerPage['PageAuthority']['hierarchy'] < NC_AUTH_MIN_CHIEF) {
			$this->Session->write(NC_SYSTEM_KEY.'.page_menu.action', 'index');
			$this->response->statusCode('403');
			$this->flash(__('Authority Error!  You do not have the privilege to access this page.'), '');
			return;
		}
		$this->set('page', $centerPage);
		if($this->action == 'style' && !$this->request->is('post')) {
			$elementParams['languages'] = $languages;
			$this->set('element_params', $elementParams);
		} else {
			$this->set('languages', $languages);
		}
		return $centerPage;
	}
/**
 * ページスタイル表示・登録(フォント設定)
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function style() {
		$centerPage = $this->_initializeStyle();
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
			if($Model->name == 'PageStyle' && isset($this->request->data[$Model->alias]['style'])) {
				$this->autoLayout = false;
				$this->autoRender = false;
						
					
				if(isset($this->request->data[$Model->alias]['style']['body']['background-image'])) {
					$this->request->data[$Model->alias]['style']['body']['background-image'] = preg_replace('#^'.preg_quote(Router::url('/', true), '#').'#i', '../../', $this->request->data[$Model->alias]['style']['body']['background-image']);
				}
				
				$this->set('data', $this->request->data[$Model->alias]['style']);
				$content = $this->render('/Elements/style/regist_template');
				
				$this->request->data[$Model->alias]['content'] = $content->body();
				
				$this->autoLayout = true;
				$this->autoRender = true;
			} elseif($Model->name == 'PageLayout' && isset($this->request->data[$Model->alias]['layouts'])) {
				$layouts = explode('_', $this->request->data[$Model->alias]['layouts']);
				$this->request->data[$Model->alias]['is_display_header'] = intval($layouts[0]);
				$this->request->data[$Model->alias]['is_display_left'] = intval($layouts[1]);
				$this->request->data[$Model->alias]['is_display_right'] = intval($layouts[2]);
				$this->request->data[$Model->alias]['is_display_footer'] = intval($layouts[3]);
				unset($this->request->data[$Model->alias]['layouts']);
			}
			
			$pageStyles = $Model->saveScope($pageStyles, $centerPage, $this->request->data, $type);
			if($pageStyles !== false) {
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
		$centerPage = $this->_initializeStyle();
		$pageStyles = $this->PageStyle->findScopeStyle('all', $centerPage);
		$pageStyles[NC_PAGE_TYPE_BACKGROUND_ID] = $this->_saveScope($this->PageStyle, isset($pageStyles[NC_PAGE_TYPE_BACKGROUND_ID]) ? $pageStyles[NC_PAGE_TYPE_BACKGROUND_ID] : null, $centerPage, NC_PAGE_TYPE_BACKGROUND_ID);
		
		$conditions = array();
		$category = (!isset($this->request->data['Background']['category']) || $this->request->data['Background']['category'] == 'all') ? null : $this->request->data['Background']['category'];
		$color = (!isset($this->request->data['Background']['color']) || $this->request->data['Background']['color'] == 'all') ? null : $this->request->data['Background']['color'];
		$limit = !isset($this->request->data['Background']['limit']) ? PAGES_BACKGROUND_LIMIT : intval($this->request->data['Background']['limit']);
		$patternPage = !isset($this->request->data['Background']['patterns_page']) ? 1 : intval($this->request->data['Background']['patterns_page']);
		$imagePage = !isset($this->request->data['Background']['images_page']) ? 1 : intval($this->request->data['Background']['images_page']);
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
 * @param   integer $groupId
 * @return  void
 * @since   v 3.0.0.0
 */
	public function color($groupId) {
		
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
		App::uses('Folder', 'Utility');
		App::uses('File', 'Utility');

		$filePath = App::pluginPath('Page') . 'webroot'. DS . 'img'. DS . 'layouts';
		$layoutDir = new Folder($filePath);
		$layoutFiles = $layoutDir->find('.*\.(jpg|gif|png)');
		$centerPage = $this->_initializeStyle();
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
