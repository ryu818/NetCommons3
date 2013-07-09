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
class PageMenusController extends PageAppController {
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
	public $uses = array('PageUserLink', 'Community', 'CommunityLang', 'CommunityTag', 'TempData',
			'Page.PageBlock', 'Page.PageMenuUserLink', 'Page.PageMenuCommunity', 'Block.BlockOperation');

/**
 * Component name
 *
 * @var array
 */
	public $components = array('Page.PageMenu');	// 権限チェックは、ここActionで行う。admin_hierarchyが管理者ならばすべて許すため。

/**
 * Helper name
 *
 * @var array
 */
	public $helpers = array('Page.PageMenu');

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
		$pre_lang = $this->Session->read(NC_SYSTEM_KEY.'.page_menu.pre_lang');
		if(isset($pre_lang)) {
			Configure::write(NC_CONFIG_KEY.'.'.'language', $pre_lang);
			$this->Session->write(NC_CONFIG_KEY.'.language', $pre_lang);
		}
	}

/**
 * ページ追加
 * @param   integer   parent page_id or current page_id
 * @param   string    inner or bottom(追加) $type
 * @return  void
 * @since   v 3.0.0.0
 */
	public function add($currentPageId, $type) {
		$userId = $this->Auth->user('id');
		$currentPageId = intval($currentPageId);
		$lang = Configure::read(NC_CONFIG_KEY.'.'.'language');

		$currentPage = $this->Page->findAuthById($currentPageId, $userId);

		if($currentPageId == 0 || !isset($currentPage['Page'])) {
			$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenus/add.001', '400');
			return;
		}

		if($type != 'inner') {
			$parentPage = $this->Page->findAuthById($currentPage['Page']['parent_id'], $userId);
		} else {
			$parentPage = $currentPage;
		}

		// デフォルトページ情報取得
		$page = $this->PageMenu->getDefaultPage($type, $currentPage, $parentPage);
		if(!isset($page['Page'])) {
			$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenus/add.002', '400');
			return;
		}

		// 権限チェック
		$adminHierarchy = $this->PageMenu->validatorPage($this->request, $currentPage, $parentPage);
		if(!$adminHierarchy) {
			return;
		}

		// Insert
		$insPage = $this->Page->setPageName($page, _ON);
		$this->Page->set($insPage);
		$this->Page->autoConvert = false;
		if(!$this->Page->save($insPage)) {
			$this->flash(__('Failed to register the database, (%s).', 'pages'), null, 'PageMenus/add.003', '500');
			return;
		}
		$page['Page']['id'] = $this->Page->id;

		// display_sequence インクリメント処理
		if(!$this->Page->incrementDisplaySeq($page, 1, array('not' => array('Page.id' => $page['Page']['id'])))) {
			$this->flash(__('Failed to update the database, (%s).', 'pages'), null, 'PageMenus/add.004', '500');
			return;
		}
		$this->_renderItem($page, $parentPage, $adminHierarchy);
	}

/**
 * コミュニティー追加
 * @param   integer   current page_id
 * @return  void
 * @since   v 3.0.0.0
 */
	public function add_community($currentPageId) {
		$user = $this->Auth->user();
		// モデレータ以上
		$adminHierarchy = $this->ModuleSystemLink->findHierarchyByPluginName($this->request->params['plugin'], $user['authority_id']);
		if($adminHierarchy <= NC_AUTH_GENERAL || !$this->request->is('post')) {
			$this->flash(__('Forbidden permission to access the page.'), null, 'PageMenus/add_community.001', '403');
			return;
		}

		// Page Insert
		$allCommunityCnt = $this->Page->findCommunityCount();
		$insPage = $this->PageMenu->getDefaultCommunityPage($currentPageId, $allCommunityCnt);
		$this->Page->create();
		if(!$this->Page->save($insPage)) {
			$this->flash(__('Failed to register the database, (%s).', 'pages'), null, 'PageMenus/add_community.002', '500');
			return;
		}
		$insPage['Page']['id'] = $insPage['Page']['root_id'] = $insPage['Page']['room_id'] = $this->Page->id;

		// room_id, root_id Update
		$fieldList = array(
			'root_id',
			'room_id'
		);
		if(!$this->Page->save($insPage, true, $fieldList)) {
			$this->flash(__('Failed to update the database, (%s).', 'pages'), null, 'PageMenus/add_community.003', '500');
			return;
		}

		// display_sequence インクリメント処理
		if( $allCommunityCnt + 1 != $insPage['Page']['display_sequence']) {
			// インクリメント
			if(!$this->Page->incrementDisplaySeq($insPage, 1, array('not' => array('Page.id' => $insPage['Page']['id'])))) {
				$this->flash(__('Failed to update the database, (%s).', 'pages'), null, 'PageMenus/add_community.004', '500');
				return;
			}
		}

		// コミュニティーTopページInsert
		$insTopPage = $insPage;
		unset($insTopPage['Page']['id']);
		$insTopPage['Page']['parent_id'] = $insPage['Page']['id'];
		$insTopPage['Page']['thread_num'] = 2;
		$insTopPage['Page']['display_sequence'] = 1;
		$insTopPage['Page']['lang'] = Configure::read(NC_CONFIG_KEY.'.'.'language');
		$insTopPage['Page']['page_name'] = 'Community Top';
		$this->Page->create();
		if(!$this->Page->save($insTopPage)) {
			$this->flash(__('Failed to register the database, (%s).', 'pages'), null, 'PageMenus/add_community.005', '500');
			return;
		}

		// page_user_links Insert
		$insPageUserLink = array('PageUserLink' => array(
			'room_id' => $insPage['Page']['id'],
			'user_id' => $user['id'],
			'authority_id' => NC_AUTH_CHIEF_ID
		));
		if(!$this->PageUserLink->save($insPageUserLink)) {
			$this->flash(__('Failed to register the database, (%s).', 'page_user_links'), null, 'PageMenus/add_community.006', '500');
			return;
		}

		// Community Insert
		$insCommunity = $this->Community->getDefault();
		$insCommunity['Community']['room_id'] = $insPage['Page']['id'];
		if(!$this->Community->save($insCommunity)) {
			$this->flash(__('Failed to register the database, (%s).', 'communities'), null, 'PageMenus/add_community.007', '500');
			return;
		}

		// CommunityLang Insert
		$insCommunityLang = $this->CommunityLang->getDefault($insPage['Page']['page_name'], $insPage['Page']['id']);
		if(!$this->CommunityLang->save($insCommunityLang)) {
			$this->flash(__('Failed to register the database, (%s).', 'community_langs'), null, 'PageMenus/add_community.008', '500');
			return;
		}

		$permalink = (NC_SPACE_GROUP_PREFIX != '') ? NC_SPACE_GROUP_PREFIX  . '/'. $insPage['Page']['permalink'] : $insPage['Page']['permalink'];
		$this->Session->setFlash(__d('page','Has been successfully added community.'));

		echo Router::url('/', true). $permalink . '/blocks/page/index?is_edit=1&is_detail=1';	// URL固定
		// コミュニティ追加時にlocation.hrefを行うため、layoutは表示しない。そうしないと、URLに$.Common.flashが表示されてしまう。
		$this->render(false, false);
	}

/**
 * ページ編集・コミュニティ編集
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function edit() {
		App::uses('Sanitize', 'Utility');
		$userId = $this->Auth->user('id');
		$page['Page'] = $this->request->data['Page'];

		$isDetail = false;
		$errorFlag = _OFF;
		$change_private = false;
		$currentPage = $this->Page->findAuthById($page['Page']['id'], $userId);
		if($currentPage['Page']['thread_num'] == 1 && $currentPage['Page']['space_type'] == NC_SPACE_TYPE_GROUP) {
			// コミュニティー
			$ret = $this->Community->getCommunityData($currentPage['Page']['room_id'], $this->request->data);
			if($ret === false) {
				$this->flash(__('Failed to obtain the database, (%s).', 'communities'), null, 'PageMenus/edit.001', '500');
				return;
			}
			list($community, $communityLang, $communityTag) = $ret;

			$fieldCommunityList = array('photo', 'upload_id', 'publication_range_flag', 'participate_flag',
										'invite_hierarchy', 'participate_notice_flag', 'participate_notice_hierarchy',
										'resign_notice_flag', 'resign_notice_hierarchy');
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
				$communityLang['CommunityLang'] = array_merge($communityLang['CommunityLang'], $this->request->data['CommunityLang']);
			}

			//if(isset($this->request->data['CommunityTag'])) {
			//	// merge
			//	$communityTag['CommunityTag'] = $this->request->data['CommunityTag'];
			//}
		}

		if(!isset($currentPage['Page'])) {
			$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenus/edit.002', '400');
			return;
		}
		$parentPage = $this->Page->findAuthById($currentPage['Page']['parent_id'], $userId);
		if(!isset($parentPage['Page'])) {
			$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenus/edit.003', '400');
			return;
		}

		// 権限チェック
		$adminHierarchy = $this->PageMenu->validatorPage($this->request, $currentPage);
		if(!$adminHierarchy) {
			return;
		}

		$permalinkArr = explode('/', $currentPage['Page']['permalink']);
		$currentPermalink = $permalinkArr[count($permalinkArr) - 1];
		if(isset($page['Page']['permalink'])) {
			// 詳細画面表示
			$isDetail = true;
		} else if((isset($communityLang) && $communityLang['CommunityLang']['community_name'] == $currentPage['Page']['permalink']) ||
				($currentPage['Page']['page_name'] == $currentPermalink)) {
			// ページ名称を変更する場合、変更前の名称が固定リンクと一致するならば、固定リンクも修正。
			// 但し、コミュニティー名称の場合、設定した言語と同じならば更新。
			$page['Page']['permalink'] = preg_replace(NC_PERMALINK_PROHIBITION, NC_PERMALINK_PROHIBITION_REPLACE, $page['Page']['page_name']);
		}

		if(isset($page['Page']['permalink'])) {
			$page['Page']['permalink'] = trim($page['Page']['permalink'], '/');
			$inputPermalink = $page['Page']['permalink'];
			if($parentPage['Page']['permalink'] != '' && $currentPage['Page']['display_sequence'] != 1) {
				$page['Page']['permalink'] = $parentPage['Page']['permalink'].'/'.$page['Page']['permalink'];
			}
		}
		if($page['Page']['display_flag'] == _ON) {
			// 既に公開ならば、公開日付fromを空にする
			$page['Page']['display_from_date'] = '';
		}
		$childPages = $this->Page->findChilds('all', $currentPage, null, $userId);
		if($currentPage['Page']['thread_num'] == 2 && $currentPage['Page']['display_sequence'] == 1) {
			// ページ名称のみ変更を許す
			$fieldList = array('page_name');
		} else {
			$fieldList = array('page_name', 'permalink', 'display_from_date', 'display_to_date', 'display_apply_subpage');
		}
		$currentPage['Page'] = array_merge($currentPage['Page'], $page['Page']);
		/*foreach($fieldList as $key => $field) {
			if(isset($page['Page'][$field])) {
				$currentPage['Page'][$field] = $page['Page'][$field];
			} else {
				unset($fieldList[$key]);
			}
		}*/

		$insPage = $this->Page->setPageName($currentPage, _ON);
		$this->Page->set($insPage);
		$currentPage['parentPage'] = $parentPage['Page'];

		if($currentPage['Page']['thread_num'] == 1 && $currentPage['Page']['space_type'] == NC_SPACE_TYPE_GROUP) {
			// コミュニティーならば
			$communityLang['CommunityLang']['community_name'] = $insPage['Page']['page_name'];

			$this->Community->set($community);
			$this->CommunityLang->set($communityLang);

			if (!$this->Community->validates(array('fieldList' => $fieldCommunityList))) {
				$errorFlag = 2;
			}
			if (!$this->CommunityLang->validates(array('fieldList' => $fieldCommunityLangList))) {
				$errorFlag = 2;
			}

			$community_params = array(
				'community' => $community,
				'community_lang' => $communityLang,
				'community_tag' => $communityTag,
				'photo_samples' => $this->PageMenu->getCommunityPhoto()
			);
			$this->set('community_params', $community_params);
		}

		// 編集ページ以下のページ取得
		$retChilds = $this->PageMenu->childsValidateErrors($this->action, $childPages, $currentPage);
		if($retChilds === false) {
			// 子ページエラーメッセージ保持
			$childsErrors = $this->Page->validationErrors;
		}

		$this->Page->set($insPage);
		$ret = $this->Page->validates(array('fieldList' => $fieldList));
		if ($ret && !$errorFlag && $retChilds !== false) {
			// 更新処理
			if (!$this->Page->save($insPage, false, $fieldList)) {
				$this->flash(__('Failed to update the database, (%s).', 'pages'), null, 'PageMenus/edit.004', '500');
				return;
			}
			// 子供更新処理
			if(is_array($retChilds)) {
				if (!$this->PageMenu->childsUpdate($this->action, $retChilds[0], $retChilds[1])) {
					$this->flash(__('Failed to update the database, (%s).', 'pages'), null, 'PageMenus/edit.005', '500');
					return;
				}
			}

			if($currentPage['Page']['thread_num'] == 1 && $currentPage['Page']['space_type'] == NC_SPACE_TYPE_GROUP) {
				// コミュニティーTop更新
				$fields = array(
					'permalink' => "'" . Sanitize::escape($insPage['Page']['permalink']) . "'"
				);
				$conditions = array(
					'room_id' => $insPage['Page']['id'],
					'thread_num' => 2,
					'display_sequence' => 1,
				);
				if (!$this->Page->updateAll($fields, $conditions)) {
					$this->flash(__('Failed to update the database, (%s).', 'pages'), null, 'PageMenus/edit.006', '500');
					return;
				}

				// コミュニティー登録処理
				if (!$this->Community->save($community, false, $fieldCommunityList)) {
					$this->flash(__('Failed to update the database, (%s).', 'communities'), null, 'PageMenus/edit.007', '500');
					return;
				}
				if (!$this->CommunityLang->save($communityLang, false, $fieldCommunityLangList)) {
					$this->flash(__('Failed to update the database, (%s).', 'community_langs'), null, 'PageMenus/edit.008', '500');
					return;
				}
			}
			$isDetail = false;
			$this->Session->setFlash(__('Has been successfully registered.'));
		} else if(!$errorFlag) {
			$errorFlag = _ON;
			if($ret && isset($childsErrors)) {
				// 親にエラーがなく子ページエラーメッセージ
				$childPrefix = __d('page', 'Lower pages');
				foreach($childsErrors as $filed => $childErrors) {
					foreach($childErrors as $i => $childError) {
						$childsErrors[$filed][$i] = $childPrefix.":".$childError;
					}
				}
				$this->Page->validationErrors = array_merge($this->Page->validationErrors, $childsErrors);
			}
			if(isset($this->Page->validationErrors['permalink']) ) {
				// 固定リンクのエラーならば詳細表示へ
				$isDetail = true;
			}
		}

		if(isset($inputPermalink)) {
			$currentPage['Page']['permalink'] = $inputPermalink;
		}

		$this->_renderItem($currentPage, $parentPage, $adminHierarchy, $isDetail, $errorFlag, $childPages);
	}

/**
 * ページ詳細設定表示
 * @param   integer   親page_id or カレントpage_id $page_id
 * @return  void
 * @since   v 3.0.0.0
 */
	public function detail($pageId) {
		$userId = $this->Auth->user('id');
		$page = $this->Page->findAuthById($pageId, $userId);

		// 権限チェック
		$adminHierarchy = $this->PageMenu->validatorPage($this->request, $page);
		if(!$adminHierarchy) {
			return;
		}

		$parentPage = $this->Page->findById($page['Page']['parent_id']);
		if(!isset($parentPage['Page'])) {
			$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenus/detail.001', '400');
			return;
		}

		$this->set('page', $page);
		$this->set('parent_page', $parentPage);

		if($page['Page']['thread_num'] == 1 && $page['Page']['space_type'] == NC_SPACE_TYPE_GROUP) {
			// コミュニティーならば
			$ret = $this->Community->getCommunityData($page['Page']['room_id']);
			if($ret === false) {
				$this->flash(__('Failed to obtain the database, (%s).', 'communities'), null, 'PageMenus/detail.002', '500');
				return;
			}
			list($community, $communityLang, $communityTag) = $ret;
			$community_params = array(
				'community' => $community,
				'community_lang' => $communityLang,
				'community_tag' => $communityTag,
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
		$userId = $this->Auth->user('id');
		$page = $this->request->data;
		if(!isset($page['Page']['id']) || !isset($page['Page']['display_flag'])) {
			$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenus/display.001', '400');
			return;
		}

		$currentPage = $this->Page->findAuthById($page['Page']['id'], $userId);
		if(!isset($currentPage['Page'])) {
			$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenus/display.002', '400');
			return;
		}

		// 権限チェック
		$adminHierarchy = $this->PageMenu->validatorPage($this->request, $currentPage);
		if(!$adminHierarchy) {
			return;
		}

		// 更新処理
		$this->Page->id = $page['Page']['id'];
		if(!$this->Page->saveField('display_flag', $page['Page']['display_flag'])) {
			$this->flash(__('Failed to update the database, (%s).', 'pages'), null, 'PageMenus/display.003', '500');
			return;
		}

		$childPages = $this->Page->findChilds('all', $currentPage);
		// 子供の更新処理
		foreach($childPages as $key => $childPage) {
			$this->Page->id = $childPage['Page']['id'];
			if(!$this->Page->saveField('display_flag', $page['Page']['display_flag'])) {
				$this->flash(__('Failed to update the database, (%s).', 'pages'), null, 'PageMenus/display.004', '500');
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
		$userId = $this->Auth->user('id');
		$page = $this->request->data;
		$allDelete = isset($this->request->data['all_delete']) ? intval($this->request->data['all_delete']) : _OFF;
		$isRedirect = false;

		if(!isset($page['Page']['id']) ) {
			$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenus/delete.001', '400');
			return;
		}

		$currentPage = $this->Page->findAuthById($page['Page']['id'], $userId);
		if(!isset($currentPage['Page'])) {
			$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenus/delete.002', '400');
			return;
		}
		if($currentPage['Page']['id'] == $this->page_id) {
			$isRedirect = true;
		}

		if($currentPage['Page']['root_id'] != $currentPage['Page']['room_id']) {
			// 親のルームがあれば、ルームが含まれていても親の持ち物に変換
			if($allDelete == _OFF) {
				$allDelete = NC_DELETE_MOVE_PARENT;
			}
		} else if($currentPage['Page']['id'] == $currentPage['Page']['room_id']) {
			// ルームならば必ずすべて削除。
			$allDelete = _ON;
		}
		$parentPage = null;
		$parentRoomId = null;
		if($currentPage['Page']['root_id'] != $currentPage['Page']['room_id']) {
			// 子グループならば親の権限に従う
			$parentPage = $this->Page->findAuthById($currentPage['Page']['parent_id'], $userId);
			$parentRoomId = $parentPage['Page']['room_id'];
		}

		// 権限チェック
		$adminHierarchy = $this->PageMenu->validatorPage($this->request, $currentPage, $parentPage);
		if(!$adminHierarchy) {
			return;
		}

		// 編集ページ以下のページ取得
		$childPages = $this->Page->findChilds('all', $currentPage, '');

		foreach($childPages as $childPage) {
			if(!$this->PageMenu->validatorPageDetail($this->request, $childPage, null, null, true)) {
				return;
			}
			if($childPage['Page']['id'] == $this->page_id) {
				$isRedirect = true;
			}
		}

		// 削除処理
		if(!$this->Page->delPage($currentPage['Page']['id'], $allDelete, $childPages, $parentRoomId)) {
			$this->flash(__('Failed to delete the database, (%s).', 'pages'), null, 'PageMenus/delete.004', '500');
			return;
		}

		$this->Session->setFlash(__('Has been successfully deleted.'));
		if($isRedirect) {
			// 削除対象が現在表示中のページならばリダイレクト
			// 		コミュニティーならばTop
			// 		ページ,ルームならば、ルームの親ページへリダイレクト
			if($currentPage['Page']['parent_id'] != NC_TOP_GROUP_ID) {
				$redirectPage = $this->Page->findById($currentPage['Page']['parent_id']);
				$permalink = $redirectPage['Page']['permalink'];
			}
			if(isset($permalink)) {
				$permalink = $this->Page->getPermalink($permalink, $currentPage['Page']['space_type']);
				$redirectUrl = Router::url(array('permalink' => $permalink, 'plugin' => 'page', 'controller' => 'page', '?' => 'is_edit=1'));

			} else {
				$redirectUrl = Router::url(array('permalink' => '', 'plugin' => 'page', 'controller' => 'page', '?' => 'is_edit=1'));
			}
			echo "<script>location.href='".$redirectUrl."';</script>";
			$this->render(false, 'ajax');
		} else {
			$this->render(false, 'ajax');
		}
	}

/**
 * ページ表示順変更
 * @return  void
 * @since   v 3.0.0.0
 */
	public function chgsequence() {
		include_once dirname(dirname(__FILE__)).'/Config/defines.inc.php';
		set_time_limit(PAGES_OPERATION_TIME_LIMIT);
		// メモリ最大サイズ設定
		ini_set('memory_limit', PAGES_OPERATION_MEMORY_LIMIT);

		$userId = $this->Auth->user('id');
		$page = $this->request->data;
		$lang = Configure::read(NC_CONFIG_KEY.'.'.'language');
		$position = $this->request->data['position'];
		$isConfirm = isset($this->request->data['is_confirm']) ? intval($this->request->data['is_confirm']) : _OFF;
		if(!isset($page['Page']['id']) || !isset($page['DropPage']['id']) || $page['Page']['id'] == $page['DropPage']['id'] ) {
			$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenus/chgsequence.001', '400');
			return;
		}

		// 権限チェック
		$currentPage = $this->Page->findAuthById($page['Page']['id'], $userId);
		$adminHierarchy = $this->PageMenu->validatorPage($this->request, $currentPage);
		if(!$adminHierarchy) {
			return;
		}

		$this->TempData->gc();

		$hashKey = $this->PageMenu->getOperationKey($page['Page']['id'], $page['DropPage']['id']);
		if($this->TempData->read($hashKey) !== false) {
			// 既に実行中
			$this->flash(__d('page', 'You are already running. Please try again at a later time.'), null, 'PageMenus/chgsequence.002', '200');
			return;
		}

		$results = $this->PageMenu->operatePage('move', $isConfirm, $page['Page']['id'], $page['DropPage']['id'], $position);
		if($results === true) {
			// 確認メッセージ
			return;
		} else if($results === false) {
			echo $this->PageMenu->getErrorStr();
			$this->render(false, 'ajax');
			return;
		}

		// ブロック処理開始
		list($copyPageIdArr, $copyPages, $insPages) = $results;

		if(!$this->PageMenu->operateBlock('move', $hashKey, $userId, $copyPageIdArr, $copyPages, $insPages)) {
			$this->flash(__('Failed to execute the %s.', __('Move')), null, 'PageMenus/chgsequence.003', '500');
			return;
		}

		$this->Session->setFlash(__('Has been successfully updated.'));

		// 再取得
		$page = $this->Page->findAuthById($insPages[0]['Page']['id'], $userId);
		$parentPage = $this->Page->findAuthById($page['Page']['parent_id'], $userId);
		$childPages = $this->Page->findChilds('all', $page);
		//$page = $insPages[0];
		//$parentPage = $this->Page->findById($page['Page']['parent_id']);
		//$childPages = $insPages;

		$this->_renderItem($page, $parentPage, $adminHierarchy, false, false, $childPages);
	}

/**
 * 参加者修正画面表示・登録
 * @param   integer   カレントpage_id $pageId
 * @return  void
 * @since   v 3.0.0.0
 */
	public function participant($pageId) {

		include_once dirname(dirname(__FILE__)).'/Config/defines.inc.php';

		$user = $this->Auth->user();
		$userId = $user['id'];
		$page = $this->Page->findAuthById($pageId, $userId);
		$parentPage = null;
		$bufParentPage = $this->Page->findAuthById($page['Page']['parent_id'], $userId);
		if($page['Page']['thread_num'] > 1) {
			// 子グループ
			$parentPage = $bufParentPage;
		}

		// 権限チェック
		$adminHierarchy = $this->PageMenu->validatorPage($this->request, $page, $parentPage);
		if(!$adminHierarchy) {
			return;
		}

		$authList = $this->Authority->findAuthSelect();
		if($this->request->is('post') && !isset($this->request->data['isSearch'])) {
			// 登録処理
			$authority = $this->Authority->findById($user['authority_id']);
			if(!isset($authority['Authority'])) {
				$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenus/participant.001', '400');
				return;
			}
			$pageUserLinks = $this->PageMenu->participantSession($this->request, $pageId, $adminHierarchy, $authList);

			if(!empty($pageUserLinks['PageUserLink'])) {
				$isSetChief = false;
				foreach($pageUserLinks['PageUserLink'] as $bufPageUserLink) {
					if(isset($authList[NC_AUTH_CHIEF][$bufPageUserLink['authority_id']])) {
						$isSetChief = true;
					}
				}
				if(!$isSetChief) {
					echo __d('page', 'Chief has to set the one.Please try again.');
					$this->render(false, 'ajax');
					return;
				}
				list($total, $users) = $this->User->findParticipant($page, null, array(), array(), null, null);
				$participantType = $this->Authority->getParticipantType($user['authority_id'], $page);
				if($page['Page']['thread_num'] <= 1) {
					$result = $this->PageUserLink->saveParticipant($page, $pageUserLinks['PageUserLink'], $users, null, $participantType);
				} else {
					// サブグループ
					list($parentTotal, $parentUsers) = $this->User->findParticipant($this->Page->findAuthById($parentPage['Page']['room_id'], $userId), null, array(), array(), null, null);
					$result = $this->PageUserLink->saveParticipant($page, $pageUserLinks['PageUserLink'], $users, $parentUsers, $participantType);
				}
				if(!$result) {
					$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenus/participant.002', '400');
					return;
				}
			}
			$childPages = $this->Page->findChilds('all', $page);
			if($page['Page']['id'] != $page['Page']['room_id']) {
				// 新規ルーム
				$pageIdListArr = $this->_getIdList($page, $childPages);

				$fields = array(
					'room_id' => $page['Page']['id']
				);
				$conditions = array(
					'id' => $pageIdListArr
				);
				if(!$this->Page->updateAll($fields, $conditions)) {
					$this->flash(__('Failed to update the database, (%s).', 'pages'), null, 'PageMenus/participant.003', '500');
					return;
				}
				if($page['Page']['parent_id'] > 0) {
					// 権限の割り当てで、子ルームを割り当てると、そこにはってあったブロックの変更処理
					$result = $this->PageBlock->addAuthBlock($pageIdListArr, $bufParentPage['Page']['room_id']);
				}
				$page['Page']['room_id'] = $page['Page']['id'];
			}

			// 処理終了-再描画
			$this->Session->delete(NC_SYSTEM_KEY.'.page_menu.PageUserLink['.$pageId.']');
			$this->Session->setFlash(__('Has been successfully updated.'));

			$this->_renderItem($page, $parentPage, $adminHierarchy, false, false, $childPages);
			return;
		}
		if(!$this->request->is('post') || (isset($this->request->data['isSearch']) && $this->request->data['isSearch'])) {
			// 権限割り当てのSession情報クリア
			$this->Session->delete(NC_SYSTEM_KEY.'.page_menu.PageUserLink['.$pageId.']');

		}

		$this->set('page', $page);
		$this->set('auth_list', $authList);
		$this->set('admin_hierarchy', $adminHierarchy);
	}

/**
 * 参加者修正画面Grid表示
 * @param   integer   カレントpage_id $pageId
 * @return  void
 * @since   v 3.0.0.0
 */
	public function participant_detail($pageId) {
		$user = $this->Auth->user();
		$userId = $user['id'];
		$page = $this->Page->findAuthById($pageId, $userId);

		// 権限チェック
		$parentPage = null;
		if($page['Page']['root_id'] != $page['Page']['room_id']) {
			// 子グループ
			$parentPage = $this->Page->findAuthById($page['Page']['parent_id'], $userId);
		}
		$adminHierarchy = $this->PageMenu->validatorPage($this->request, $page, $parentPage);
		if(!$adminHierarchy) {
			return;
		}

		$pageNum = empty($this->request->data['page']) ? 1 : intval($this->request->data['page']);
		$rp = empty($this->request->data['rp']) ? null : intval($this->request->data['rp']);
		$sortname = (!empty($this->request->data['sortname']) && ($this->request->data['sortname'] == "handle" || $this->request->data['sortname'] == "chief")) ? $this->request->data['sortname'] : null;
		$sortorder = (!empty($this->request->data['sortorder']) && ($this->request->data['sortorder'] == "asc" || $this->request->data['sortorder'] == "desc")) ? $this->request->data['sortorder'] : "asc";

		// 会員絞り込み
		$userAuthorityId = $this->Authority->getUserAuthorityId($user['hierarchy']);
		$adminUserHierarchy = $this->ModuleSystemLink->findHierarchyByPluginName('User', $user['authority_id']);
		list($conditions, $joins) = $this->User->getRefineSearch($this->request, $userAuthorityId, $adminUserHierarchy);

		$participantType = $this->Authority->getParticipantType($user['authority_id'], $page);
		$defaultAuthorityId = $this->Page->getDefaultAuthorityId($page, true);

		if($defaultAuthorityId == NC_AUTH_OTHER_ID && $page['Page']['thread_num'] > 1) {
			// 親が非公開ルームで子グループならば、不参加会員は非表示
			//$conditions['PageUserLink.authority_id !='] = NC_AUTH_OTHER_ID;
			$conditions['PageUserLinkParent.authority_id >'] = NC_AUTH_OTHER_ID;
		}

		list($total, $users) = $this->User->findParticipant($page, $participantType, $conditions, $joins, $pageNum, $rp, $sortname, $sortorder);

		$this->set('room_id', $pageId);
		$this->set('page_num', $pageNum);
		$this->set('total', $total);
		$this->set('users', $users);
		$this->set('auth_list',$this->Authority->findAuthSelect());
		$this->set('user_id', $userId);
		$this->set('page', $page);
		$this->set('page_user_links', $this->PageMenu->participantSession($this->request, $pageId, $adminHierarchy));
		$this->set('default_authority_id', $defaultAuthorityId);
		$this->set('admin_hierarchy', $adminHierarchy);
		$this->set('participant_type', $participantType);
	}

/**
 * 参加者修正画面 キャンセルボタン
 * @param   integer   カレントpage_id $pageId
 * @return  void
 * @since   v 3.0.0.0
 */
	public function participant_cancel($pageId) {
		$userId = $this->Auth->user('id');
		$page = $this->Page->findAuthById($pageId, $userId);

		// 権限チェック
		$parentPage = null;
		if($page['Page']['root_id'] != $page['Page']['room_id']) {
			// 子グループ
			$parentPage = $this->Page->findAuthById($page['Page']['parent_id'], $userId);
		}
		$adminHierarchy = $this->PageMenu->validatorPage($this->request, $page, $parentPage);
		if(!$adminHierarchy) {
			return;
		}
		$this->Session->delete(NC_SYSTEM_KEY.'.page_menu.PageUserLink['.$pageId.']');
		$this->render(false, 'ajax');
	}

/**
 * 参加者割り当て解除
 * @param   integer   カレントpage_id $pageId
 * @return  void
 * @since   v 3.0.0.0
 */
	public function deallocation($pageId) {
		$user = $this->Auth->user();
		$userId = $user['id'];
		$page = $this->Page->findAuthById($pageId, $userId);
		$parentPage = $this->Page->findAuthById($page['Page']['parent_id'], $userId);
		// 権限チェック
		if($page['Page']['root_id'] != $page['Page']['room_id']) {
			// 子グループ
			$adminHierarchy = $this->PageMenu->validatorPage($this->request, $page, $parentPage);
		} else {
			$adminHierarchy = $this->PageMenu->validatorPage($this->request, $page);
		}
		if(!$adminHierarchy) {
			return;
		}
		$childPages = $this->Page->findChilds('all', $page);

		// 登録処理
		$pageIdListArr = $this->_getIdList($page, $childPages);

		$fields = array(
			'room_id' => $parentPage['Page']['room_id']
		);
		$conditions = array(
			'id' => $pageIdListArr
		);
		if(!$this->Page->updateAll($fields, $conditions)) {
			$this->flash(__('Failed to update the database, (%s).', 'pages'), null, 'PageMenus/deallocation.001', '500');
			return;
		}
		if(!$this->PageMenuUserLink->deallocationRoom($page['Page']['room_id'])) {
			$this->flash(__('Failed to delete the database, (%s).', 'page_user_links'), null, 'PageMenus/deallocation.002', '500');
			return false;
		}
		$page['Page']['room_id'] = $parentPage['Page']['room_id'];
		// 権限の割り当て解除で、そこにはってあったブロックの変更処理
		if(!$this->PageBlock->deallocationBlock($pageIdListArr, $parentPage['Page']['room_id'])) {
			$this->flash(__('Failed to update the database, (%s).', 'pages'), null, 'PageMenus/deallocation.003', '500');
			return;
		}

		$this->Session->setFlash(__('Has been successfully updated.'));
		$this->_renderItem($page, $parentPage, $adminHierarchy, false, false, $childPages);
	}

/**
 * ページのitemのrenderを行う
 * @param   Model Page    $page
 * @param   Model Pages   $parentPage
 * @param   integer       $adminHierarchy
 * @param   boolean       $isDetail
 * @param   boolean       $errorFlag
 * @param   Model Pages   $childPages
 * @return  void
 * @since   v 3.0.0.0
 */
	private function _renderItem($page, $parentPage = null, $adminHierarchy, $isDetail = false, $errorFlag = false, $childPages = null) {
		if(isset($parentPage)) {
			$this->set('parent_page', $parentPage);
		}
		if(isset($childPages) && count($childPages) > 0) {
			$pages = array();
			foreach($childPages as $childPage) {
				$pages[$childPage['Page']['space_type']][$childPage['Page']['thread_num']][$childPage['Page']['parent_id']][$childPage['Page']['display_sequence']] = $childPage;
			}
			$this->set('pages', $pages);
		}
		$this->set('page', $page);
		$this->set('space_type', $page['Page']['space_type']);
		$this->set('page_id', $page['Page']['id']);
		$this->set('admin_hierarchy', $adminHierarchy);
		$this->set('is_detail', $isDetail);
		$this->set('error_flag', $errorFlag);
		$this->render('Elements/index/item');
	}

/**
 * ページIDのリストを取得
 * @param   Model Page    $page
 * @param   Model Pages   $childPages
 * @return  array(array $pageIdListArr, array $room_id_list_arr)
 * @since   v 3.0.0.0
 */
	private function _getIdList($page, $childPages) {
		$pageIdListArr = array($page['Page']['id']);
		foreach($childPages as $childPage) {
			if($childPage['Page']['room_id'] == $page['Page']['room_id']) {
				// 親のルームと等しいpageリスト
				$pageIdListArr[] = $childPage['Page']['id'];
			}
		}
		return $pageIdListArr;
	}
}
