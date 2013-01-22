<?php
/**
 * PageMenuControllerクラス
 *
 * <pre>
 * ページ追加、編集、削除
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       App.Controller
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class PageMenuController extends PageAppController {
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
 * Model name
 *
 * @var array
 */
	public $uses = array('PageUserLink', 'Community', 'CommunityLang', 'CommunityTag');

/**
 * Component name
 *
 * @var array
 */
	public $components = array('Page.PageMenu');

/**
 * Helper name
 *
 * @var array
 */
	public $helpers = array('TimeZone', 'Page.PageMenu');

/**
 * セッションの言語保持 _sess_language
 * @var string
 */
	private $_sess_language = null;

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
		$this->_sess_language = null;
		$active_lang = $this->Session->read(NC_SYSTEM_KEY.'.page_menu.lang');
		if(isset($active_lang)) {
			$this->_sess_language = $this->Session->read(NC_CONFIG_KEY.'.language');
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
		if(isset($this->_sess_language)) {
			$this->Session->write(NC_CONFIG_KEY.'.language', $this->_sess_language);
		}
	}

/**
 * ページ追加
 * @param   integer   parent page_id or current page_id
 * @param   string    inner or bottom(追加) $type
 * @return  void
 * @since   v 3.0.0.0
 */
	public function add($current_page_id, $type) {
		$user_id = $this->Auth->user('id');
		$current_page_id = intval($current_page_id);
		$lang = Configure::read(NC_CONFIG_KEY.'.'.'language');

		$current_page = $this->Page->findAuthById($current_page_id, $user_id);

		if($current_page_id == 0 || !isset($current_page['Page'])) {
			$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenu/add.001', '400');
			return;
		}

		if($type != 'inner') {
			$parent_page = $this->Page->findAuthById($current_page['Page']['parent_id'], $user_id);
		} else {
			$parent_page = $current_page;
		}

		// デフォルトページ情報取得
		$page = $this->PageMenu->getDefaultPage($type, $current_page, $parent_page);
		if(!isset($page['Page'])) {
			$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenu/add.002', '400');
			return;
		}

		// 権限チェック
		$admin_hierarchy = $this->PageMenu->validatorPage($this->request, $current_page, $parent_page);
		if(!$admin_hierarchy) {
			return;
		}

		// Insert
		$ins_page = array('Page' => $this->Page->setPageName($page['Page'], _ON));
		$this->Page->set($ins_page);
		$this->Page->autoConvert = false;
		if(!$this->Page->save($ins_page)) {
			$this->flash(__('Failed to register the database, (%s).', 'pages'), null, 'PageMenu/add.003', '500');
			return;
		}
		$page['Page']['id'] = $this->Page->id;

		// display_sequence インクリメント処理
		if(!$this->Page->incrementDisplaySeq($page, 1, array('not' => array('Page.id' => $page['Page']['id'])))) {
			$this->flash(__('Failed to update the database, (%s).', 'pages'), null, 'PageMenu/add.004', '500');
			return;
		}
		$this->set('page', $page);
		$this->set('space_type', $page['Page']['space_type']);
		$this->set('page_id', $page['Page']['id']);
		$this->set('admin_hierarchy', $admin_hierarchy);
		$this->set('is_detail', false);
		$this->render('Elements/index/item');
	}

/**
 * コミュニティー追加
 * @param   integer   current page_id
 * @return  void
 * @since   v 3.0.0.0
 */
	public function add_community($current_page_id) {
		$user = $this->Auth->user();
		// モデレータ以上
		$admin_hierarchy = $this->ModuleSystemLink->findHierarchy(Inflector::camelize($this->request->params['plugin']), $user['authority_id']);
		if($admin_hierarchy <= NC_AUTH_GENERAL || !$this->request->is('post')) {
			$this->flash(__('Forbidden permission to access the page.'), null, 'PageMenu/add_community.001', '403');
			return;
		}

		// Page Insert
		$all_community_cnt = $this->Page->findCommunityCount();
		$ins_page = $this->PageMenu->getDefaultCommunityPage($current_page_id, $all_community_cnt);
		$this->Page->create();
		if(!$this->Page->save($ins_page)) {
			$this->flash(__('Failed to register the database, (%s).', 'pages'), null, 'PageMenu/add_community.002', '500');
			return;
		}
		$ins_page['Page']['id'] = $ins_page['Page']['root_id'] = $ins_page['Page']['room_id'] = $this->Page->id;

		// room_id, root_id Update
		$fieldList = array(
			'root_id',
			'room_id'
		);
		if(!$this->Page->save($ins_page, true, $fieldList)) {
			$this->flash(__('Failed to update the database, (%s).', 'pages'), null, 'PageMenu/add_community.003', '500');
			return;
		}

		// display_sequence インクリメント処理
		if( $all_community_cnt + 1 != $ins_page['Page']['display_sequence']) {
			// インクリメント
			if(!$this->Page->incrementDisplaySeq($ins_page, 1, array('not' => array('Page.id' => $ins_page['Page']['id'])))) {
				$this->flash(__('Failed to update the database, (%s).', 'pages'), null, 'PageMenu/add_community.004', '500');
				return;
			}
		}

		// コミュニティーTopページInsert
		$ins_top_page = $ins_page;
		unset($ins_top_page['Page']['id']);
		$ins_top_page['Page']['parent_id'] = $ins_page['Page']['id'];
		$ins_top_page['Page']['thread_num'] = 2;
		$ins_top_page['Page']['display_sequence'] = 1;
		$ins_top_page['Page']['lang'] = Configure::read(NC_CONFIG_KEY.'.'.'language');
		$ins_top_page['Page']['page_name'] = 'Community Top';
		$this->Page->create();
		if(!$this->Page->save($ins_top_page)) {
			$this->flash(__('Failed to register the database, (%s).', 'pages'), null, 'PageMenu/add_community.005', '500');
			return;
		}

		// page_user_links Insert
		$ins_page_user_link = array('PageUserLink' => array(
			'room_id' => $ins_page['Page']['id'],
			'user_id' => $user['id'],
			'authority_id' => NC_AUTH_CHIEF_ID
		));
		if(!$this->PageUserLink->save($ins_page_user_link)) {
			$this->flash(__('Failed to register the database, (%s).', 'page_user_links'), null, 'PageMenu/add_community.006', '500');
			return;
		}

		// Community Insert
		$ins_community = $this->Community->getDefault();
		$ins_community['Community']['room_id'] = $ins_page['Page']['id'];
		if(!$this->Community->save($ins_community)) {
			$this->flash(__('Failed to register the database, (%s).', 'communities'), null, 'PageMenu/add_community.007', '500');
			return;
		}

		// CommunityLang Insert
		$ins_community_lang = $this->CommunityLang->getDefault($ins_page['Page']['page_name'], $ins_page['Page']['id']);
		if(!$this->CommunityLang->save($ins_community_lang)) {
			$this->flash(__('Failed to register the database, (%s).', 'community_langs'), null, 'PageMenu/add_community.008', '500');
			return;
		}

		$permalink = (NC_SPACE_GROUP_PREFIX != '') ? NC_SPACE_GROUP_PREFIX  . '/'. $ins_page['Page']['permalink'] : $ins_page['Page']['permalink'];
		$this->Session->setFlash(__d('page','Has been successfully added community.'));

		echo Router::url('/', true). $permalink . '/blocks/page/index?is_edit=1&is_detail=1';	// URL固定
		$this->render(false, false);	// デバッグ情報を表示していない。
	}

/**
 * ページ編集・コミュニティ編集
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function edit() {
		$user_id = $this->Auth->user('id');
		$page['Page'] = $this->request->data['Page'];

		$is_detail = false;
		$error_flag = _OFF;
		$current_page = $this->Page->findAuthById($page['Page']['id'], $user_id);
		if($current_page['Page']['thread_num'] == 1 && $current_page['Page']['space_type'] == NC_SPACE_TYPE_GROUP) {
			// コミュニティー
			$ret = $this->PageMenu->getCommunityData($current_page['Page']['room_id']);
			if($ret === false) {
				$this->flash(__('Failed to obtain the database, (%s).', 'communities'), null, 'PageMenu/edit.001', '500');
				return;
			}
			list($community, $community_lang, $community_tag) = $ret;

			$fieldCommunityList = array('photo', 'upload_id', 'publication_range_flag', 'participate_flag',
										'invite_authority', 'participate_notice_flag', 'participate_notice_authority',
										'resign_notice_flag', 'resign_notice_authority');
			// TODO:descriptionは使用しなくなる可能性あり。
			$fieldCommunityLangList = array('room_id', 'community_name', 'lang', 'summary', 'description');
			// TODO:CommunityTagについては現状、未作成
			//$fieldCommunityTagList = array('tag_value');

			if(isset($this->request->data['Community'])) {
				// merge
				$community['Community'] = array_merge($community['Community'], $this->request->data['Community']);
			}
			if(isset($this->request->data['CommunityLang'])) {
				// merge
				if(isset($this->request->data['CommunityLang']['lang'])) {
					unset($this->request->data['CommunityLang']['lang']);
				}
				if(isset($this->request->data['CommunityLang']['room_id'])) {
					unset($this->request->data['CommunityLang']['room_id']);
				}
				$community_lang['CommunityLang'] = array_merge($community_lang['CommunityLang'], $this->request->data['CommunityLang']);
			}

			//if(isset($this->request->data['CommunityTag'])) {
			//	// merge
			//	$community_tag['CommunityTag'] = $this->request->data['CommunityTag'];
			//}
		}

		if(!isset($current_page['Page'])) {
			$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenu/edit.002', '400');
			return;
		}
		$parent_page = $this->Page->findById($current_page['Page']['parent_id']);
		if(!isset($parent_page['Page'])) {
			$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenu/edit.003', '400');
			return;
		}

		// 権限チェック
		$admin_hierarchy = $this->PageMenu->validatorPage($this->request, $current_page);
		if(!$admin_hierarchy) {
			return;
		}

		$permalink_arr = explode('/', $current_page['Page']['permalink']);
		$current_permalink = $permalink_arr[count($permalink_arr) - 1];
		if(isset($page['Page']['permalink'])) {
			// 詳細画面表示
			$is_detail = true;
		} else if((isset($community_lang) && $community_lang['CommunityLang']['community_name'] == $current_page['Page']['permalink']) ||
				($current_page['Page']['page_name'] == $current_permalink)) {
			// ページ名称を変更する場合、変更前の名称が固定リンクと一致するならば、固定リンクも修正。
			// 但し、コミュニティー名称の場合、設定した言語と同じならば更新。
			$page['Page']['permalink'] = $page['Page']['page_name'];
		}

		if(isset($page['Page']['permalink'])) {
			$page['Page']['permalink'] = trim($page['Page']['permalink'], '/');
			$input_permalink = $page['Page']['permalink'];
			if($parent_page['Page']['permalink'] != '' && $current_page['Page']['display_sequence'] != 1) {
				$page['Page']['permalink'] = $parent_page['Page']['permalink'].'/'.$page['Page']['permalink'];
			}
		}
		if($page['Page']['display_flag'] == _ON) {
			// 既に公開ならば、公開日付fromを空にする
			$page['Page']['display_from_date'] = '';
		}
		$child_pages = $this->Page->findChilds('all', $current_page, $user_id);
		if($current_page['Page']['thread_num'] == 2 && $current_page['Page']['display_sequence'] == 1) {
			// ページ名称のみ変更を許す
			$fieldList = array('page_name');
		} else {
			$fieldList = array('page_name', 'permalink', 'display_from_date', 'display_to_date', 'display_apply_subpage');
		}
		$current_page['Page'] = array_merge($current_page['Page'], $page['Page']);
		/*foreach($fieldList as $key => $field) {
			if(isset($page['Page'][$field])) {
				$current_page['Page'][$field] = $page['Page'][$field];
			} else {
				unset($fieldList[$key]);
			}
		}*/
		$current_page['parentPage'] = $parent_page['Page'];
		$ins_page = array('Page' => $this->Page->setPageName($current_page['Page'], _ON));
		$this->Page->set($ins_page);

		if($current_page['Page']['thread_num'] == 1 && $current_page['Page']['space_type'] == NC_SPACE_TYPE_GROUP) {
			// コミュニティーならば
			$community_lang['CommunityLang']['community_name'] = $ins_page['Page']['page_name'];

			$this->Community->set($community);
			$this->CommunityLang->set($community_lang);

			if (!$this->Community->validates(array('fieldList' => $fieldCommunityList))) {
				$error_flag = 2;
			}
			if (!$this->CommunityLang->validates(array('fieldList' => $fieldCommunityLangList))) {
				$error_flag = 2;
			}

			$community_params = array(
				'community' => $community,
				'community_lang' => $community_lang,
				'community_tag' => $community_tag,
				'photo_samples' => $this->PageMenu->getCommunityPhoto()
			);
			$this->set('community_params', $community_params);
		}

		// 編集ページ以下のページ取得
		$fetch_params = array(
			'active_page_id' => $current_page['Page']['id']
		);
		$thread_pages = $this->Page->afterFindMenu($child_pages, $fetch_params);
		if ($this->Page->validates(array('fieldList' => $fieldList)) && !$error_flag) {
			// 子供の更新処理
			if(!$this->PageMenu->childsUpdate($child_pages, $current_page)) {
				$this->flash(__('Failed to update the database, (%s).', 'pages'), null, 'PageMenu/edit.004', '500');
				return;
			}

			// 登録処理
			if (!$this->Page->save($ins_page, false, $fieldList)) {
				$this->flash(__('Failed to update the database, (%s).', 'pages'), null, 'PageMenu/edit.005', '500');
				return;
			}
			if($current_page['Page']['thread_num'] == 1 && $current_page['Page']['space_type'] == NC_SPACE_TYPE_GROUP) {
				// コミュニティーTop更新
				$fields = array(
					'permalink' => '"'.addslashes($ins_page['Page']['permalink']).'"'
				);
				$conditions = array(
					'room_id' => $ins_page['Page']['id'],
					'thread_num' => 2,
					'display_sequence' => 1,
				);
				if (!$this->Page->updateAll($fields, $conditions)) {
					$this->flash(__('Failed to update the database, (%s).', 'pages'), null, 'PageMenu/edit.006', '500');
					return;
				}

				// コミュニティー登録処理
				if (!$this->Community->save($community, false, $fieldCommunityList)) {
					$this->flash(__('Failed to update the database, (%s).', 'communities'), null, 'PageMenu/edit.007', '500');
					return;
				}
				if (!$this->CommunityLang->save($community_lang, false, $fieldCommunityLangList)) {
					$this->flash(__('Failed to update the database, (%s).', 'community_langs'), null, 'PageMenu/edit.008', '500');
					return;
				}
			}
			$is_detail = false;
			$this->Session->setFlash(__('Has been successfully registered.'));
		} else if(!$error_flag) {
			$error_flag = _ON;
			//$this->Page->validationErrors;
		}

		if(isset($input_permalink)) {
			$current_page['Page']['permalink'] = $input_permalink;
		}

		$current_page['Page']['hierarchy'] = $current_page['Authority']['hierarchy'];
		$this->set('pages', $thread_pages);
		$this->set('parent_page', $parent_page);
		$this->set('page', $current_page);
		$this->set('space_type', $current_page['Page']['space_type']);
		$this->set('page_id', $current_page['Page']['id']);
		$this->set('admin_hierarchy', $admin_hierarchy);
		$this->set('is_detail', $is_detail);
		$this->set('error_flag', $error_flag);

		$this->render('Elements/index/item');
	}

/**
 * ページ詳細設定表示
 * @param   integer   親page_id or カレントpage_id $page_id
 * @return  void
 * @since   v 3.0.0.0
 */
	public function detail($page_id) {
		$user_id = $this->Auth->user('id');
		$page = $this->Page->findAuthById($page_id, $user_id);

		// 権限チェック
		$admin_hierarchy = $this->PageMenu->validatorPage($this->request, $page);
		if(!$admin_hierarchy) {
			return;
		}

		$parent_page = $this->Page->findById($page['Page']['parent_id']);
		if(!isset($parent_page['Page'])) {
			$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenu/detail.001', '400');
			return;
		}

		$this->set('page', $page);
		$this->set('parent_page', $parent_page);

		if($page['Page']['thread_num'] == 1 && $page['Page']['space_type'] == NC_SPACE_TYPE_GROUP) {
			// コミュニティーならば
			$ret = $this->PageMenu->getCommunityData($page['Page']['room_id']);
			if($ret === false) {
				$this->flash(__('Failed to obtain the database, (%s).', 'communities'), null, 'PageMenu/detail.002', '500');
				return;
			}
			list($community, $community_lang, $community_tag) = $ret;
			$community_params = array(
				'community' => $community,
				'community_lang' => $community_lang,
				'community_tag' => $community_tag,
				'photo_samples' => $this->PageMenu->getCommunityPhoto()
			);
			$this->set('community_params', $community_params);
			$this->render('Elements/index/community');
		} else {
			$this->render('Elements/index/detail');
		}
	}

/**
 * ページ詳細設定表示
 * @return  void
 * @since   v 3.0.0.0
 */
	public function display() {
		$user_id = $this->Auth->user('id');
		$page = $this->request->data;
		if(!isset($page['Page']['id']) || !isset($page['Page']['display_flag'])) {
			$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenu/display.001', '400');
			return;
		}

		$current_page = $this->Page->findAuthById($page['Page']['id'], $user_id);
		if(!isset($current_page['Page'])) {
			$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenu/display.002', '400');
			return;
		}

		// 権限チェック
		$admin_hierarchy = $this->PageMenu->validatorPage($this->request, $current_page);
		if(!$admin_hierarchy) {
			return;
		}

		// 更新処理
		$this->Page->id = $page['Page']['id'];
		if(!$this->Page->saveField('display_flag', $page['Page']['display_flag'])) {
			$this->flash(__('Failed to update the database, (%s).', 'pages'), null, 'PageMenu/display.003', '500');
			return;
		}

		$child_pages = $this->Page->findChilds('all', $current_page);
		// 子供の更新処理
		foreach($child_pages as $key => $child_page) {
			$this->Page->id = $child_page['Page']['id'];
			if(!$this->Page->saveField('display_flag', $page['Page']['display_flag'])) {
				$this->flash(__('Failed to update the database, (%s).', 'pages'), null, 'PageMenu/display.004', '500');
				return;
			}
		}

		// 正常終了
		$this->Session->setFlash(__('Has been successfully registered.'));
		$this->render(false, 'ajax');
	}

/**
 * ページ削除
 * @return  void
 * @since   v 3.0.0.0
 */
	public function delete() {
		$user_id = $this->Auth->user('id');
		$page = $this->request->data;
		$all_delete = isset($this->request->data['all_delete']) ? intval($this->request->data['all_delete']) : _OFF;
		$is_redirect = false;

		if(!isset($page['Page']['id']) ) {
			$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenu/delete.001', '400');
			return;
		}

		$current_page = $this->Page->findAuthById($page['Page']['id'], $user_id);
		if(!isset($current_page['Page'])) {
			$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenu/delete.002', '400');
			return;
		}
		if($current_page['Page']['id'] == $this->page_id) {
			$is_redirect = true;
		}
		if($current_page['Page']['id'] == $current_page['Page']['room_id']) {
			// ルームならば必ずすべて削除。
			$all_delete = _ON;
		}

		// 権限チェック
		$admin_hierarchy = $this->PageMenu->validatorPage($this->request, $current_page);
		if(!$admin_hierarchy) {
			return;
		}

		// 編集ページ以下のページ取得
		$child_pages = $this->Page->findChilds('all', $current_page, $user_id);

		foreach($child_pages as $child_page) {
			if(!$this->PageMenu->validatorPageDetail($this->request, $child_page, null, null, true)) {
				return;
			}
			if($child_page['Page']['id'] == $this->page_id) {
				$is_redirect = true;
			}
		}

		// 削除処理
		if(!$this->Page->deletePage($current_page['Page']['id'], $all_delete, $child_pages)) {
			$this->flash(__('Failed to delete the database, (%s).', 'pages'), null, 'PageMenu/delete.004', '500');
			return;
		}

		// $this->Session->setFlash(__('Has been successfully deleted.'));
		if($is_redirect) {
			// 削除対象が現在表示中のページならばリダイレクト
			// 		コミュニティーならばTop
			// 		ページ,ルームならば、ルームの親ページへリダイレクト
			if($current_page['Page']['parent_id'] != NC_TOP_GROUP_ID) {
				$redirect_page = $this->Page->findById($current_page['Page']['parent_id']);
				$permalink = $redirect_page['Page']['permalink'];
			}
			if(isset($permalink)) {
				$permalink = $this->Page->getPermalink($permalink, $current_page['Page']['space_type']);
				$redirect_url = Router::url('/', true). $permalink . 'blocks/page/index?is_edit=1';
			} else {
				$redirect_url = Router::url('/', true). 'blocks/page/index?is_edit=1';
			}
			// TODO: setFlashしてredirectしたかったが、layoutが表示されないままリダイレクトされたため、
			// controller->flashに変更。
			//$this->layout = 'default';
			//$this->redirect($redirect_url);
			$this->flash(__('Has been successfully deleted.'), $redirect_url);
		} else {
			$this->Session->setFlash(__('Has been successfully deleted.'));
			$this->render(false, 'ajax');
		}
	}

/**
 * ページ表示順変更
 * @return  void
 * @since   v 3.0.0.0
 */
	public function chgsequence() {
		$user_id = $this->Auth->user('id');
		$page = $this->request->data;
		$lang = Configure::read(NC_CONFIG_KEY.'.'.'language');
		$position = $this->request->data['position'];
		$is_confirm = isset($this->request->data['is_confirm']) ? intval($this->request->data['is_confirm']) : _OFF;
		if(!isset($page['Page']['id']) || !isset($page['DropPage']['id']) || $page['Page']['id'] == $page['DropPage']['id']) {
			$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenu/chgsequence.001', '400');
			return;
		}

		$drag_page = $pre_drag_page = $this->Page->findAuthById($page['Page']['id'], $user_id);
		$drop_page = $this->Page->findAuthById($page['DropPage']['id'], $user_id);
		if(!isset($drag_page['Page']) || !isset($drop_page['Page'])) {
			$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenu/chgsequence.002', '400');
			return;
		}

		// 権限チェック
		$admin_hierarchy = $this->PageMenu->validatorPage($this->request, $drag_page);
		if(!$admin_hierarchy) {
			return;
		}

		// 表示順変更権限チェック
		$insert_parent_page = $this->PageMenu->validatorMovePage($this->request, $drag_page, $drop_page, $position);
		if($insert_parent_page === false) {
			return;
		}

		if($drop_page['Page']['id'] == $drop_page['Page']['room_id']) {
			$drop_room_name = $drop_page['Page']['page_name'];
		} else {
			$drop_room_name = $insert_parent_page['Page']['page_name'];
		}
		$drag_page_id_arr = array($drag_page['Page']['id']);
		if($drag_page['Page']['id'] == $drag_page['Page']['room_id']) {
			$drag_room_id_arr = array($drag_page['Page']['id']);
		}
		// 移動元ページ以下のページ取得
		$child_drag_pages = $this->Page->findChilds('all', $drag_page, $user_id);
		$last_drag_page = $drag_page;
		foreach($child_drag_pages as $child_page) {
			if(!$this->PageMenu->validatorPageDetail($this->request, $child_page, null, $admin_hierarchy)) {
				return;
			}
			$drag_page_id_arr[] = $child_page['Page']['id'];
			if($child_page['Page']['id'] == $child_page['Page']['room_id']) {
				$drag_room_id_arr = array($child_page['Page']['id']);
			}
			// ノード中のもっとも多いdisplay_sequenceのPageを取得
			if($drag_page['Page']['thread_num'] != 1) {
				$last_drag_page = $child_page;
			}
		}

		$child_drop_pages = $this->Page->findChilds('all', $drop_page, $user_id);
		$last_drop_page = $drop_page;
		if($drop_page['Page']['thread_num'] != 1) {
			foreach($child_drop_pages as $child_page) {
				if(in_array($child_page['Page']['id'], $drag_page_id_arr)) {
					continue;
				}
				// ノード中のもっとも多いdisplay_sequenceのPageを取得
				$last_drop_page = $child_page;
			}
		}

		// 確認メッセージ表示
		if( !$is_confirm && $drag_page['Page']['thread_num'] != 1 && $drag_page['Page']['room_id'] != $insert_parent_page['Page']['room_id']) {
			// ページの移動で、移動元と移動先がちがうルームであっても、
			// 移動元が単一ページ（ルームではない）で、ブロックがはっていないならば、
			// 確認メッセージを出す必要性がない
			$buf_block = $this->Block->find('first', array(
				'recursive' => -1,
				'conditions' => array(
					'Block.page_id' => $drag_page_id_arr
				)
			));
			if(isset($buf_block['Block'])) {
				$echo_str = '<div>';
				if(count($drag_room_id_arr) > 0) {
					// 子グループあり
					$echo_str .= __d('page', 'You are about to move to [%s]. When you move, block placement will be moved as a shortcut, the assignment of rights will be canceled. Are you sure?', $drop_room_name);
				} else {
					// 子グループなし
					$echo_str .= __d('page', 'You are about to move to [%s]. When you move, block placement will be moved as a shortcut. Are you sure?', $drop_room_name);
				}
				$echo_str .= '</div>';
				echo $echo_str;
				$this->render(false, 'ajax');
				return;
			}
		}

// TODO:test他ルームへの移動はまだ実装していないためエラーを出すように仮実装：後に削除
if($drag_page['Page']['thread_num'] != 1 && $drag_page['Page']['room_id'] != $insert_parent_page['Page']['room_id']) {
	$buf_block = $this->Block->find('first', array(
			'recursive' => -1,
			'conditions' => array(
					'Block.page_id' => $drag_page_id_arr
			)
	));
	if(isset($buf_block['Block'])) {
		echo "TODO:まだブロックがはってあるページにおける他ルームへの移動は実装されていません。移動できませんでした。";
		$this->render(false, 'ajax');
		return;
	}
}
// TODO:test End

		$drop_root_id = $drop_page['Page']['root_id'];
		$drop_thread_num = $drop_page['Page']['thread_num'];
		$drop_space_type = $drop_page['Page']['space_type'];
		$drop_parent_id = $drop_page['Page']['parent_id'];

		if($drag_page['Page']['thread_num'] == 1) {
			if($position == 'bottom') {
				$display_sequence = $drop_page['Page']['display_sequence'] + 1;
			} else {
				$display_sequence = $drop_page['Page']['display_sequence'];
			}
			$drop_room_id = $drag_page['Page']['room_id'];
		} else if($position == 'inner') {
			$display_sequence = $last_drop_page['Page']['display_sequence'] + 1;
			$drop_parent_id = $drop_page['Page']['id'];
			$drop_thread_num++;
			$drop_room_id = $drop_page['Page']['room_id'];
		} else if($position == 'bottom') {
			$display_sequence = $last_drop_page['Page']['display_sequence'] + 1;
			$drop_room_id = $insert_parent_page['Page']['room_id'];
		} else {
			// top
			$display_sequence = $drop_page['Page']['display_sequence'];
			$drop_room_id = $insert_parent_page['Page']['room_id'];
		}

		// 登録処理
		$currentFieldList = array();
		$permalink_arr = explode('/', $drag_page['Page']['permalink']);
		if($insert_parent_page['Page']['permalink'] != '') {
			$permalink = $insert_parent_page['Page']['permalink'] . '/' . $permalink_arr[count($permalink_arr)-1];
		} else {
			$permalink = $permalink_arr[count($permalink_arr)-1];
		}
		if($permalink != $drag_page['Page']['permalink']) {
			$currentFieldList[] = 'permalink';
			$drag_page['Page']['permalink'] = $permalink;
		}
		if($drop_parent_id != $drag_page['Page']['parent_id']) {
			$currentFieldList[] = 'parent_id'; //$drop_parent_id;
			$drag_page['Page']['parent_id'] = $drop_parent_id;
		}

		$fields = array();
		if($drag_page['Page']['thread_num'] != 1) {
			if($display_sequence != $drag_page['Page']['display_sequence']) {
				$fields['Page.display_sequence'] = 'Page.display_sequence+('.($display_sequence - $drag_page['Page']['display_sequence']).')';
				$drag_page['Page']['display_sequence'] = $display_sequence;
			}
			if($drop_thread_num != $drag_page['Page']['thread_num']) {
				$fields['Page.thread_num'] = 'Page.thread_num+('.($drop_thread_num - $drag_page['Page']['thread_num']).')';
				$drag_page['Page']['thread_num'] = $drop_thread_num;
			}
			if($drop_root_id != $drag_page['Page']['root_id']) {
				$fields['Page.root_id'] = $drop_root_id;
				$drag_page['Page']['room_id'] = $drop_root_id;
			}
			if($drop_space_type != $drag_page['Page']['space_type']) {
				$fields['Page.space_type'] = $drop_space_type;
				$drag_page['Page']['space_type'] = $drop_space_type;
			}
			if($drop_room_id != $drag_page['Page']['room_id']) {
				$fields['Page.room_id'] = $drop_room_id;
				$drag_page['Page']['room_id'] = $drop_room_id;
			}
		} else if($display_sequence != $drag_page['Page']['display_sequence']) {
			$currentFieldList[] = 'display_sequence';
			$drag_page['Page']['display_sequence'] = $display_sequence;
		}
		if(count($currentFieldList) > 0) {
			if(!$this->Page->save($drag_page, true, $currentFieldList)) {
				$error = '';
				foreach($this->Page->validationErrors as $field => $errors) {
					if($field == 'permalink') {
						$error .= __('Permalink'). ':';
					}
					$error .= $errors[0];	// 最初の１つめ
				}
				echo $error;
				$this->render(false, 'ajax');
				return;
			}
		}

		if(count($fields) > 0) {
			$conditions = array(
				'Page.id' => $drag_page_id_arr
			);
			$ret = $this->Page->updateAll($fields, $conditions);
			if(!$ret) {
				$this->flash(__('Failed to update the database, (%s).', 'pages'), null, 'PageMenu/chgsequence.003', '500');
				return;
			}

			// カレント、子供、固定リンク,公開日付の更新
			if($permalink != $pre_drag_page['Page']['permalink'] && !$this->PageMenu->childsUpdate($child_drag_pages, $drag_page, $insert_parent_page)) {
				$this->flash(__('Failed to update the database, (%s).', 'pages'), null, 'PageMenu/chgsequence.004', '500');
				return;
			}
		}

		/*
		 * 移動先、移動元を更新
		 * TODO:移動するfrom-Toを計算し、一度のSQLでdisplay_sequenceの更新処理を行っているが
		 * 同じような処理が多いため、見直すほうが望ましい。
		 */
		if( $pre_drag_page['Page']['thread_num'] == 1) {
			$conditions = array(
				"Page.position_flag" => _ON,
				"Page.thread_num" => 1,
				"Page.space_type" => $pre_drag_page['Page']['space_type'],
				"Page.lang" => array("", $lang)
			);
			if($pre_drag_page['Page']['display_sequence'] < $drop_page['Page']['display_sequence']) {
				// 上から下へ デクリメント
				$upd_display_sequence = -1;
				$conditions["Page.display_sequence >"] = $last_drag_page['Page']['display_sequence'];
				if($position == 'bottom') {
					$conditions["Page.display_sequence <="] = $drag_page['Page']['display_sequence'];

					$next_conditions = array(
						'Page.thread_num' => 1,
						'Page.position_flag' => _ON,
						'Page.lang' => array('', $lang),
						'Page.display_sequence' => intval($drop_page['Page']['display_sequence']) + 1,
						'not' => array('Page.id' => $drag_page['Page']['id'])
					);
					$next_page = $this->Page->find('first', array('fields' => array('Page.id'), 'conditions' => $next_conditions));
					if(isset($next_page['Page'])) {
						// Dropの１つ下のページを取得し、そのページ以外
						$conditions['not'] = array('Page.id' => $next_page['Page']['id']);
					}
				} else {
					// top
					$conditions["Page.display_sequence <="] = $drop_page['Page']['display_sequence'];
					$conditions['not'] = array('Page.id' => $drop_page['Page']['id']);
				}
			} else {
				// 下から上へ インクリメント
				$upd_display_sequence = 1;
				$operation = ($position == 'top') ? '>=' : '>';
				$conditions["Page.display_sequence ".$operation] = $drop_page['Page']['display_sequence'];
				$conditions["Page.display_sequence <"] = $pre_drag_page['Page']['display_sequence'];
				$conditions['not'] = array('Page.id' => $pre_drag_page['Page']['id']);
			}
			$fields = array('Page.display_sequence'=>'Page.display_sequence+('.$upd_display_sequence.')');
			if(!$this->Page->updateAll($fields, $conditions)) {
				$this->flash(__('Failed to update the database, (%s).', 'pages'), null, 'PageMenu/chgsequence.005', '500');
				return;
			}
		} else {
			$conditions = array(
				"Page.position_flag" => _ON,
				"Page.thread_num >" => 1,
				"Page.lang" => array("", $lang),
				"Page.root_id" => $drop_page['Page']['root_id']
			);
			if($pre_drag_page['Page']['root_id'] != $drop_page['Page']['root_id']) {
				// 別ルート
				$upd_display_sequence = count($child_drag_pages) + 1;
				$conditions['not'] = array('Page.id' => $drag_page_id_arr);

				$pre_fields = array('Page.display_sequence'=>'Page.display_sequence+(-'.$upd_display_sequence.')');
				$pre_conditions = $conditions;
				$pre_conditions["Page.display_sequence >"] = $last_drag_page['Page']['display_sequence'];
				$pre_conditions["Page.root_id"] = $drag_page['Page']['root_id'];
				if(!$this->Page->updateAll($pre_fields, $pre_conditions)) {
					$this->flash(__('Failed to update the database, (%s).', 'pages'), null, 'PageMenu/chgsequence.006', '500');
					return;
				}
				if($position == 'bottom' || $position == 'inner') {
					$conditions["Page.display_sequence >="] = $display_sequence;
				} else {
					// top
					$conditions["Page.display_sequence >="] = $drop_page['Page']['display_sequence'];
				}
			} else if($pre_drag_page['Page']['display_sequence'] < $drop_page['Page']['display_sequence']) {
				// 上から下へ デクリメント
				$upd_display_sequence = -(count($child_drag_pages) + 1);
				$conditions["Page.display_sequence >"] = $last_drag_page['Page']['display_sequence'];

				if($position == 'bottom' || $position == 'inner') {
					$conditions["Page.display_sequence <="] = $drag_page['Page']['display_sequence'] + count($child_drag_pages);;

					$next_conditions = array(
						'Page.root_id' => $drop_page['Page']['root_id'],
						'Page.position_flag' => _ON,
						'Page.lang' => array('', $lang),
						'Page.display_sequence >=' => intval($last_drop_page['Page']['display_sequence']) + 1,
						'Page.display_sequence <' => intval($drag_page['Page']['display_sequence']) + count($child_drag_pages) + 2,
						'not' => array('Page.id' => $drag_page_id_arr)
					);
					$next_page = $this->Page->find('list', array('fields' => array('Page.id'), 'conditions' => $next_conditions));
					if(is_array($next_page) && count($next_page) > 0) {
						// Dropの下のページを取得し、そのページ以外
						$conditions['not'] = array('Page.id' => $next_page);
					}
				} else {
					// top
					$conditions["Page.display_sequence <="] = $drag_page['Page']['display_sequence'] + count($child_drag_pages);
					$next_conditions = array(
						'Page.root_id' => $drop_page['Page']['root_id'],
						'Page.position_flag' => _ON,
						'Page.lang' => array('', $lang),
						'Page.display_sequence >=' => $drag_page['Page']['display_sequence'],
						'Page.display_sequence <' => intval($drag_page['Page']['display_sequence']) + count($child_drag_pages) + 1,
						'not' => array('Page.id' => $drag_page_id_arr)
					);
					$next_page = $this->Page->find('list', array('fields' => array('Page.id'), 'conditions' => $next_conditions));
					if(is_array($next_page) && count($next_page) > 0) {
						// Dropの下のページを取得し、そのページ以外
						$conditions['not'] = array('Page.id' => $next_page);
					}
				}
			} else {
				// 下から上へ インクリメント
				$upd_display_sequence = count($child_drag_pages) + 1;
				if($position == 'top') {
					$conditions["Page.display_sequence >="] = $drop_page['Page']['display_sequence'];
				} else {
					$conditions["Page.display_sequence >"] = $last_drop_page['Page']['display_sequence'];
				}
				$conditions["Page.display_sequence <"] = $pre_drag_page['Page']['display_sequence'];
				$conditions['not'] = array('Page.id' => $drag_page_id_arr);
			}
			$fields = array('Page.display_sequence'=>'Page.display_sequence+('.$upd_display_sequence.')');
			if(!$this->Page->updateAll($fields, $conditions)) {
				$this->flash(__('Failed to update the database, (%s).', 'pages'), null, 'PageMenu/chgsequence.007', '500');
				return;
			}
		}
		//
		// TODO:異なるルームへの移動
		//
		if($drop_room_id != $drag_page['Page']['room_id']) {
			// 未実装
		}

		$this->Session->setFlash(__('Has been successfully updated.'));
		$this->render(false, 'ajax');
	}

/**
 * 参加者修正画面表示・登録
 * @return  void
 * @since   v 3.0.0.0
 */
	public function participant($page_id) {

		include_once dirname(dirname(__FILE__)).'/Config/defines.inc.php';

		$user = $this->Auth->user();
		$user_id = $user['id'];
		$page = $this->Page->findAuthById($page_id, $user_id);

		// 権限チェック
		$admin_hierarchy = $this->PageMenu->validatorPage($this->request, $page);
		if(!$admin_hierarchy) {
			return;
		}

		//$parent_page = $this->Page->findById($page['Page']['parent_id']);
		//if(!isset($parent_page['Page'])) {
		//	$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenu/detail.001', '400');
		//	return;
		//}

		if($this->request->is('post')) {
			// 登録処理
			$room_id = $page['Page']['room_id'];
			$authority = $this->Authority->findById($user['authority_id']);
			if(!isset($authority['Authority'])) {
				$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenu/participant.001', '400');
				return;
			}
			$page_user_links = $this->PageMenu->participantSession($this->request, $page_id, $admin_hierarchy);

			if(!empty($page_user_links['PageUserLink'])) {
				$is_participant_only = $this->Authority->isParticipantOnly($user['authority_id'], $page);
				list($total, $users) = $this->User->findParticipant($room_id, array(), true, null, null);

				if(!$this->PageUserLink->saveParticipant($page, $is_participant_only, $users, $page_user_links['PageUserLink'])) {
					$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenu/participant.002', '400');
					return;
				}

			}
			// TODO:未作成 後に作成
			/*if(isset($page_id_list_arr) && count($page_id_list_arr) > 0) {
				// 権限の割り当てで、子ルームを割り当てると、そこにはってあったブロックの変更処理
				$result = $this->Pagesmenu->addAuthBlock($page_id_list_arr, $parent_room_id);
			}*/

			$this->Session->delete(NC_SYSTEM_KEY.'.page_menu.PageUserLink['.$page_id.']');
			$this->Session->setFlash(__('Has been successfully updated.'));
		}

		$this->set('page', $page);
		$this->set('auth_list', $this->Authority->findAuthSelectHtml());
		$this->set('admin_hierarchy', $admin_hierarchy);


		//$this->set('parent_page', $parent_page);
	}

/**
 * 参加者修正画面Grid表示
 * @return  void
 * @since   v 3.0.0.0
 */
	public function participant_detail($page_id) {
		$user = $this->Auth->user();
		$user_id = $user['id'];
		$page = $this->Page->findAuthById($page_id, $user_id);

		// 権限チェック
		$admin_hierarchy = $this->PageMenu->validatorPage($this->request, $page);
		if(!$admin_hierarchy) {
			return;
		}

		$page_num = empty($this->request->data['page']) ? 1 : intval($this->request->data['page']);
		$rp = empty($this->request->data['rp']) ? null : intval($this->request->data['rp']);
		$sortname = (!empty($this->request->data['sortname']) && ($this->request->data['sortname'] == "handle" || $this->request->data['sortname'] == "chief")) ? $this->request->data['sortname'] : null;
		$sortorder = (!empty($this->request->data['sortorder']) && ($this->request->data['sortorder'] == "asc" || $this->request->data['sortorder'] == "desc")) ? $this->request->data['sortorder'] : "asc";

		$room_id = $page['Page']['room_id'];

		// TODO:会員絞り込み未作成
		////list($conditions, $joins) = $this->Common->getRefineSearch();
		$is_participant_only = $this->Authority->isParticipantOnly($user['authority_id'], $page);
		list($total, $users) = $this->User->findParticipant($room_id, array(), $is_participant_only, $page_num, $rp, $sortname, $sortorder);

		$this->set('room_id', $page_id);
		//$this->set('room_id', $room_id);
		$this->set('page_num', $page_num);
		$this->set('total', $total);
		$this->set('users', $users);
		$this->set('auth_list',$this->Authority->findAuthSelectHtml());
		$this->set('user_id', $user_id);
		$this->set('page', $page);
		$this->set('page_user_links', $this->PageMenu->participantSession($this->request, $page_id, $admin_hierarchy));
		$this->set('default_authority_id', $this->PageUserLink->getDefaultAuthorityId($page));
		$this->set('admin_hierarchy', $admin_hierarchy);
	}

/**
 * 参加者修正画面 キャンセルボタン
 * @return  void
 * @since   v 3.0.0.0
 */
	public function participant_cancel($page_id) {
		$user_id = $this->Auth->user('id');
		$page = $this->Page->findAuthById($page_id, $user_id);

		// 権限チェック
		$admin_hierarchy = $this->PageMenu->validatorPage($this->request, $page);
		if(!$admin_hierarchy) {
			return;
		}

		$this->Session->delete('pagesmenu.PageUserLink['.$page_id.']');
		$this->render(false, 'ajax');
	}
}
