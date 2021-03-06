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
	public $uses = array('PageUserLink', 'Revision', 'Community', 'CommunityLang', 'CommunityTag', 'TempData',
		'CommunityInvitation', 'Page.PageBlock', 'Page.PageMenuUserLink', 'Page.PageMenuCommunity', 'Block.BlockOperation');

/**
 * Component name
 *
 * @var array
 */
	public $components = array('RevisionList', 'Security', 'Page.PageMenu', 'Page.PageMailCommunity', 'Mail');

/**
 * Helper name
 *
 * @var array
 */
	public $helpers = array('Paginator', 'Page.PageMenu');

/**
 *会員招待->会員選択実行時のページ移動の設定
 * @var array
 */
	public $paginate = array(
		'fields' => array(
			'User.id',
			'User.handle',
			'User.avatar',
			'PageUserLink.id',
		),
		'conditions' => array(),
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
		$activeLang = $this->Session->read(NC_SYSTEM_KEY.'.page_menu.activeLang');
		if(isset($activeLang)) {
			Configure::write(NC_CONFIG_KEY.'.'.'language', $activeLang);
			$this->Session->write(NC_CONFIG_KEY.'.language', $activeLang);
		}
		parent::beforeFilter();

		// Token
		$this->Security->validatePost = false;
		if($this->action != 'invite_community' && $this->request->is('post')) {
			$this->Security->csrfUseOnce = false;
		}
		if($this->request->is('post') && $this->action != 'edit' && $this->action != 'participant' && $this->action != 'select_member'
			 && $this->action != 'invite_community') {
			// 手動でTokenチェック
			$this->Security->csrfCheck = false;
			if($this->action == 'participant_detail' || $this->action == 'participant_cancel') {
				// 参加者詳細、参加者修正画面 キャンセルボタンでは、登録していないため、チェックはしない。
				return;
			}
			$requestToken = isset($this->request->data['token']) ? $this->request->data['token'] : null;
			$csrfTokens = $this->Session->read('_Token.csrfTokens');
			if (!isset($csrfTokens[$requestToken]) || $csrfTokens[$requestToken] < time()) {
				$this->errorToken();
				return;
			}
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
		$lang = $this->Session->read(NC_SYSTEM_KEY.'.page_menu.lang');
		if(isset($lang)) {
			Configure::write(NC_CONFIG_KEY.'.'.'language', $lang);
			$this->Session->write(NC_CONFIG_KEY.'.language', $lang);
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
			$this->response->statusCode('403');
			$this->flash(__('Forbidden permission to access the page.'), '');
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
			throw new BadRequestException(__('Unauthorized request.<br />Please reload the page.'));
		}

		// 権限チェック
		if(!$this->PageMenu->validatorPage($this->request, $currentPage, $parentPage)) {
			return;
		}

		// Insert
		$insPage = $this->Page->setPageName($page, _ON);
		$this->Page->set($insPage);
		$this->Page->autoConvert = false;
		if(!$this->Page->save($insPage)) {
			throw new InternalErrorException(__('Failed to register the database, (%s).', 'pages'));
		}
		$page['Page']['id'] = $this->Page->id;

		// display_sequence インクリメント処理
		if(!$this->Page->incrementDisplaySeq($page, 1, array('not' => array('Page.id' => $page['Page']['id'])))) {
			throw new InternalErrorException(__('Failed to update the database, (%s).', 'pages'));
		}
		$this->_renderItem($page, $parentPage);
	}

/**
 * コミュニティー追加
 * @param   integer   current page_id
 * @return  void
 * @since   v 3.0.0.0
 */
	public function add_community($currentPageId) {
		$user = $this->Auth->user();
		if(empty($user['allow_creating_community']) || !$this->request->is('post')) {
			$this->response->statusCode('403');
			$this->flash(__('Forbidden permission to access the page.'), '');
			return;
		}

		// Page Insert
		$allCommunityCnt = $this->Page->findCommunityCount();
		$insPage = $this->PageMenu->getDefaultCommunityPage($currentPageId, $allCommunityCnt);
		$this->Page->create();
		if(!$this->Page->save($insPage)) {
			throw new InternalErrorException(__('Failed to register the database, (%s).', 'pages'));
		}
		$insPage['Page']['id'] = $insPage['Page']['root_id'] = $insPage['Page']['room_id'] = $this->Page->id;

		// room_id, root_id Update
		$fieldList = array(
			'root_id',
			'room_id'
		);
		if(!$this->Page->save($insPage, true, $fieldList)) {
			throw new InternalErrorException(__('Failed to update the database, (%s).', 'pages'));
		}

		// display_sequence インクリメント処理
		if( $allCommunityCnt + 1 != $insPage['Page']['display_sequence']) {
			// インクリメント
			if(!$this->Page->incrementDisplaySeq($insPage, 1, array('not' => array('Page.id' => $insPage['Page']['id'])))) {
				throw new InternalErrorException(__('Failed to update the database, (%s).', 'pages'));
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
			throw new InternalErrorException(__('Failed to register the database, (%s).', 'pages'));
		}

		// page_user_links Insert
		$insPageUserLink = array('PageUserLink' => array(
			'room_id' => $insPage['Page']['id'],
			'user_id' => $user['id'],
			'authority_id' => NC_AUTH_CHIEF_ID
		));
		if(!$this->PageUserLink->save($insPageUserLink)) {
			throw new InternalErrorException(__('Failed to register the database, (%s).', 'page_user_links'));
		}

		// Community Insert
		$insCommunity = $this->Community->getDefault();
		$insCommunity['Community']['room_id'] = $insPage['Page']['id'];
		if(!$this->Community->save($insCommunity)) {
			throw new InternalErrorException(__('Failed to register the database, (%s).', 'communities'));
		}

		// CommunityLang Insert
		$insCommunityLang = $this->CommunityLang->getDefault($insPage['Page']['page_name'], $insPage['Page']['id']);
		if(!$this->CommunityLang->save($insCommunityLang)) {
			throw new InternalErrorException(__('Failed to register the database, (%s).', 'community_langs'));
		}

		$permalink = (NC_SPACE_GROUP_PREFIX != '') ? NC_SPACE_GROUP_PREFIX  . '/'. $insPage['Page']['permalink'] : $insPage['Page']['permalink'];
		$this->Session->setFlash(__d('page','Has been successfully added community.'));

		echo Router::url('/', true). $permalink . '/blocks/page/index?is_edit=1&is_detail=1';	// URL固定
		// コミュニティ追加時にlocation.hrefを行うため、layoutは表示しない。そうしないと、URLに$.Common.flashが表示されてしまう。
		$this->render(false, false);
	}

/**
 * コミュニティー情報表示
 * @param   integer   current page_id
 * @return  void
 * @since   v 3.0.0.0
 */
	public function community_inf($currentPageId) {
		$user = $this->Auth->user();
		$ret = $this->Community->getCommunityData($currentPageId);
		if($ret === false) {
			throw new InternalErrorException(__('Failed to obtain the database, (%s).', 'communities'));
		}
		list($community, $communityLang, $communityTag) = $ret;

		$currentPage = $this->Page->findById($currentPageId);
		if(!$currentPage) {
			$this->response->statusCode('404');
			$this->flash(__('Page not found.'), '');
			return;
		}

		$isParticipate = $this->PageUserLink->isParticipate($currentPageId, $user['id']);
		if($isParticipate) {
			$authority = $this->Authority->findById($isParticipate);
			if(!$authority) {
				throw new InternalErrorException(__('Failed to obtain the database, (%s).', 'authorities'));
			}
			$minHierarchy = $this->Authority->getMinHierarchy($authority['Authority']['hierarchy']);
			$this->set('min_hierarchy', $minHierarchy);
		}

		$this->set('community', $community);
		$this->set('community_lang', $communityLang);
		$this->set('community_tag', $communityTag);
		$this->set('is_participate', $isParticipate);
		$this->set('page', $currentPage);
	}

/**
 * コミュニティーから退会
 * @param   integer   current page_id
 * @return  void
 * @since   v 3.0.0.0
 */
	public function resign_community($currentPageId) {
		$user = $this->Auth->user();
		$ret = $this->Community->getCommunityData($currentPageId);
		if($ret === false) {
			throw new InternalErrorException(__('Failed to obtain the database, (%s).', 'communities'));
		}
		list($community, $communityLang, $communityTag) = $ret;
		$communityName = $communityLang['CommunityLang']['community_name'];

		if (empty($user['id']) || !$this->request->is('post') || $community['Community']['participate_flag'] == NC_PARTICIPATE_FLAG_ONLY_USER) {
			$this->response->statusCode('403');
			$this->flash(__('Authority Error!  You do not have the privilege to access this page.'), '');
			return;
		}

		$currentPage = $this->Page->findById($currentPageId);
		if(!$currentPage) {
			$this->response->statusCode('404');
			$this->flash(__('Page not found.'), '');
			return;
		}

		// 退会可能かどうか
		if(!$this->PageUserLink->isResign($currentPageId, $user['id'])) {
			$this->flash(__d('page', 'Because you are the only chief, membership removal of %s is not possible.<br />Please identify a different chief first before removing membership.', $communityLang['CommunityLang']['community_name']), '');
			return;
		}

		$configs = $this->Config->findList('list', 0, NC_COMMUNITY_CATID);
		if(!$configs) {
			throw new InternalErrorException(__('Failed to obtain the database, (%s).', 'configs'));
		}

		// 退会処理
		$conditions = array(
			'room_id' => $currentPageId,
			'user_id' => $user['id'],
		);
		if(!$this->PageUserLink->deleteAll($conditions)) {
			throw new InternalErrorException(__('Failed to delete the database, (%s).', 'page_user_links'));
		}

		// 退会通知メール送信
		if($community['Community']['is_resign_notice']) {
			if(!$this->PageMailCommunity->sendNotification($currentPageId, $communityName, array('User' => $user),
					$configs['community_mail_withdraw_subject'], $configs['community_mail_withdraw_body'], true)) {
				return;
			}
		}
		$redirectUrl =Router::url('/', true);
		$this->flash(__d('page', 'Remove membership of %s.', $communityName), $redirectUrl);
		return;
	}

/**
 * コミュニティーに参加する
 * @param   integer   current page_id
 * @return  void
 * @since   v 3.0.0.0
 */
	public function participate_community($currentPageId) {
		$ret = $this->Community->getCommunityData($currentPageId);
		if($ret === false) {
			throw new InternalErrorException(__('Failed to obtain the database, (%s).', 'communities'));
		}

		list($community, $communityLang, $communityTag) = $ret;

		$currentPage = $this->Page->findById($currentPageId);
		if(!$currentPage) {
			$this->response->statusCode('404');
			$this->flash(__('Page not found.'), '');
			return;
		}
		$communityName = $communityLang['CommunityLang']['community_name'];

		if(!$this->CommunityInvitation->gc()) {
			throw new InternalErrorException(__('Failed to delete the database, (%s).', 'community_invitations'));
		}

		if(isset($this->request->named['activate_key'])) {
			// 会員招待メールからの参加
			$activateUser = $this->CommunityInvitation->findActivateUser($currentPageId, $this->request->named['activate_key']);
			if (!$activateUser) {
				$this->response->statusCode('403');
				$this->flash(__d('page', 'The approval key is invalid, or it is expired.'), '');
				return;
			}
			$user = $activateUser['User'];
			$communityInvitationId = $activateUser['CommunityInvitation']['id'];
			$isPendingApprovalMail = $activateUser['CommunityInvitation']['is_pending_approval_mail'];
			$isCheck = (!$currentPage || empty($user['id']) || $community['Community']['participate_flag'] == NC_PARTICIPATE_FLAG_ONLY_USER);
		} else {
			$user = $this->Auth->user();
			$activateUser = array('User' => $user);
			$isCheck = (!$currentPage || empty($user['id']) || !$this->request->is('post')
					|| ($community['Community']['participate_flag'] != NC_PARTICIPATE_FLAG_FREE && $community['Community']['participate_flag'] != NC_PARTICIPATE_FLAG_ACCEPT));
		}

		if ($isCheck) {
			$this->response->statusCode('403');
			$this->flash(__('Authority Error!  You do not have the privilege to access this page.'), '');
			return;
		}

		// 既に参加中かどうかチェック
		$redirectUrl =Router::url('/', true) . NC_SPACE_GROUP_PREFIX . '/'. $currentPage['Page']['permalink'];
		if($this->PageUserLink->isParticipate($currentPageId, $user['id'])) {
			// 既に参加中
			$this->flash(__d('page', 'Already participating.'), $redirectUrl);
			return;
		}

		$configs = $this->Config->findList('list', 0, NC_COMMUNITY_CATID);
		if(!$configs) {
			throw new InternalErrorException(__('Failed to obtain the database, (%s).', 'configs'));
		}

		// 承認メール送信
		if((!isset($isPendingApprovalMail) || $isPendingApprovalMail == _OFF) && $community['Community']['participate_flag'] == NC_PARTICIPATE_FLAG_ACCEPT) {
			if($this->CommunityInvitation->findAwaitingUser($currentPageId, $user['id'])) {
				$this->flash(__d('page', 'It is awaiting approval already.Please wait until it is approved.'), '');
				return;
			}
			// 参加受付制（主担の承認が必要）の場合、主担に承認メール送信 -> CommunityInvitation対象データ削除
			if($this->PageMailCommunity->sendApproval($currentPageId, $communityName, $activateUser,
					$configs['community_mail_confirm_approval_subject'], $configs['community_mail_confirm_approval_body'])) {
				// 成功
				if(isset($communityInvitationId) && !$this->CommunityInvitation->delete($communityInvitationId)) {
					throw new InternalErrorException(__('Failed to delete the database, (%s).', 'community_invitations'));
				}
			} else {
				// sendApprovalでflash
				return;
			}
			// 承認待ちメール送信
			if($this->PageMailCommunity->sendNotification($currentPageId, $communityName, $activateUser,
				$configs['community_mail_wait_approval_subject'], $configs['community_mail_wait_approval_body'])) {
				// 成功
				$this->flash($this->Mail->sendTextBody, '');
			}
			return;
		}

		// ゲストOR一般として参加
		// page_user_links Insert
		list($minHierarchy, $maxHierarchy) = $this->Authority->getHierarchyByUserAuthorityId($user['authority_id']);
		$insPageUserLink = array('PageUserLink' => array(
			'room_id' => $currentPageId,
			'user_id' => $user['id'],
			'authority_id' => ($maxHierarchy == NC_AUTH_GUEST) ? NC_AUTH_GUEST_ID : NC_AUTH_GENERAL_ID
		));
		$this->PageUserLink->create();
		if(!$this->PageUserLink->save($insPageUserLink)) {
			throw new InternalErrorException(__('Failed to register the database, (%s).', 'page_user_links'));
		}

		// 参加通知メール送信
		if($community['Community']['is_participate_notice']) {
			if(!$this->PageMailCommunity->sendNotification($currentPageId, $communityName, $activateUser,
					$configs['community_mail_participate_announce_subject'], $configs['community_mail_participate_announce_body'], true)) {
				return;
			}
		}

		if(isset($isPendingApprovalMail) && $isPendingApprovalMail == _ON) {
			if(!$this->CommunityInvitation->delete($communityInvitationId)) {
				throw new InternalErrorException(__('Failed to delete the database, (%s).', 'community_invitations'));
			}

			// 参加完了通知メール送信
			if(!$this->PageMailCommunity->sendNotification($currentPageId, $communityName, $activateUser,
					$configs['community_mail_approved_subject'], $configs['community_mail_approved_body'])) {
				return;
			}

			$this->flash(__d('page', '%1$s\'s participation for %2$s was approved.', $user['handle'], $communityName), $redirectUrl);
			return;
		} else {
			$this->flash(__d('page', 'Has jointed %s.', $communityName), $redirectUrl);
			return;
		}

		return;
	}

/**
 * このコミュニティーへの招待
 * @param   integer   current page_id
 * @return  void
 * @since   v 3.0.0.0
 */
	public function invite_community($currentPageId) {
		$user = $this->Auth->user();
		$ret = $this->Community->getCommunityData($currentPageId);
		if($ret === false) {
			throw new InternalErrorException(__('Failed to obtain the database, (%s).', 'communities'));
		}

		list($community, $communityLang, $communityTag) = $ret;
		$currentPage = $this->Page->findById($currentPageId);
		if(!$currentPage) {
			$this->response->statusCode('404');
			$this->flash(__('Page not found.'), '');
			return;
		}
		$communityName = $communityLang['CommunityLang']['community_name'];

		if (empty($user['id']) || $community['Community']['participate_flag'] == NC_PARTICIPATE_FLAG_ONLY_USER) {
			$this->response->statusCode('403');
			$this->flash(__('Authority Error!  You do not have the privilege to access this page.'), '');
			return;
		}

		$isParticipate = $this->PageUserLink->isParticipate($currentPageId, $user['id']);
		if($isParticipate) {
			$authority = $this->Authority->findById($isParticipate);
			if(!$authority) {
				throw new InternalErrorException(__('Failed to obtain the database, (%s).', 'authorities'));
			}
			$minHierarchy = $this->Authority->getMinHierarchy($authority['Authority']['hierarchy']);
			if ($minHierarchy < $community['Community']['invite_hierarchy']) {
				$this->response->statusCode('403');
				$this->flash(__('Authority Error!  You do not have the privilege to access this page.'), '');
				return;
			}
		} else {
			// 参加していないのに招待
			$this->response->statusCode('403');
			$this->flash(__('Authority Error!  You do not have the privilege to access this page.'), '');
			return;
		}

		if ($this->request->is('post')) {
			// 招待メール送信処理
			if($this->PageMailCommunity->sendInvite($currentPageId, $communityName, $this->request->data)) {
				// 成功
				$this->Session->setFlash(__d('page', 'Transmission processing of invitation mails has been started.'));
			}
			$configs['invite_members'] = $this->request->data['invite_members'];
			$configs['community_mail_invite_subject'] = $this->request->data['invite_mail_subject'];
			$configs['community_mail_invite_body'] = $this->request->data['invite_mail_body'];
		} else {
			// コミュニティーConfig関連取得
			$configs = $this->Config->findList('list', 0, NC_COMMUNITY_CATID);
			if(!$configs) {
				throw new InternalErrorException(__('Failed to obtain the database, (%s).', 'configs'));
			}
			$this->Mail->assignedTags['{X-SITE_NAME}'] = Configure::read(NC_CONFIG_KEY.'.'.'sitename');
			$this->Mail->assignedTags['{X-ROOM}'] = $communityName;
			$configs['community_mail_invite_subject'] = $this->Mail->replaceTags($configs['community_mail_invite_subject'], 'text');
			$configs['community_mail_invite_body'] = $this->Mail->replaceTags($configs['community_mail_invite_body'], 'text');
		}

		$this->set('configs', $configs);
		$this->set('community', $community);
		$this->set('community_lang', $communityLang);
	}

/**
 * ページ編集・コミュニティ編集
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function edit() {
		App::uses('Sanitize', 'Utility');
		$loginUser = $this->Auth->user();
		$userId = $loginUser['id'];
		$page['Page'] = $this->request->data['Page'];

		$isDetail = false;
		$errorFlag = _OFF;
		$change_private = false;
		$currentPage = $this->Page->findAuthById($page['Page']['id'], $userId);
		if(!$currentPage) {
			throw new InternalErrorException(__('Failed to obtain the database, (%s).', 'pages'));
		}
		$lang = Configure::read(NC_CONFIG_KEY.'.'.'language');
		if($currentPage['Page']['thread_num'] == 1 && $currentPage['Page']['space_type'] == NC_SPACE_TYPE_GROUP) {
			// コミュニティー
			$ret = $this->Community->getCommunityData($currentPage['Page']['room_id']);
			if($ret === false) {
				throw new InternalErrorException(__('Failed to obtain the database, (%s).', 'communities'));
			}
			list($community, $communityLang, $communityTag) = $ret;
			$fieldCommunityList = array('photo', 'is_upload', 'publication_range_flag', 'participate_force_all_users','participate_flag',
					'invite_hierarchy', 'is_participate_notice', 'participate_notice_hierarchy',
					'is_resign_notice', 'resign_notice_hierarchy');
			$fieldCommunityLangList = array('room_id', 'community_name', 'lang', 'summary', 'revision_group_id');

			if(isset($this->request->data['Community'])) {
				// merge
				$community['Community'] = array_merge($community['Community'], $this->request->data['Community']);
				if(!empty($community['Community']['participate_force_all_users']) && $community['Community']['participate_flag'] != NC_PARTICIPATE_FLAG_ONLY_USER) {
					// 「全会員を強制的に参加させる。」にチェックがついているのに、参加方法が「参加会員のみ」になっていない場合、強制的にOFFへ
					$community['Community']['participate_force_all_users'] = _OFF;
				}
			}
			if(isset($this->request->data['CommunityLang'])) {
				// merge
				unset($this->request->data['CommunityLang']['lang']);
				unset($this->request->data['CommunityLang']['room_id']);
				unset($this->request->data['CommunityLang']['revision_group_id']);
				$communityLang['CommunityLang'] = array_merge($communityLang['CommunityLang'], $this->request->data['CommunityLang']);
			}
			if(isset($this->request->data['Revision']['content'])) {
				$communityLang['Revision']['content'] = $this->request->data['Revision']['content'];
			}
			if(isset($this->request->data['CommunityTag']['tag_value'])) {
				$communityTag['CommunityTag']['tag_value'] = $this->request->data['CommunityTag']['tag_value'];
			}
		}

		if(!isset($currentPage['Page'])) {
			$this->response->statusCode('404');
			$this->flash(__('Page not found.'), '');
			return;
		}
		$bufCurrentPermalink = $currentPage['Page']['permalink'];
		$parentPage = $this->Page->findAuthById($currentPage['Page']['parent_id'], $userId);
		if(!isset($parentPage['Page'])) {
			$this->response->statusCode('404');
			$this->flash(__('Page not found.'), '');
			return;
		}

		// 権限チェック
		$bufParentPage = ($currentPage['Page']['thread_num'] > 1) ? $parentPage : null;
		if(!$this->PageMenu->validatorPage($this->request, $currentPage, $bufParentPage)) {
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

		$childPages = $this->Page->findChilds('all', $currentPage, $lang, $userId);
		if($currentPage['Page']['thread_num'] == 2 && $currentPage['Page']['display_sequence'] == 1) {
			// ページ名称のみ変更を許す
			$fieldList = array('page_name');
		} else {
			$fieldList = array('page_name', 'permalink', 'display_from_date', 'display_to_date', 'display_apply_subpage');
		}
		$currentPage['Page'] = array_merge($currentPage['Page'], $page['Page']);

		$insPage = $this->Page->setPageName($currentPage, _ON);
		$this->Page->set($insPage);
		$currentPage['parentPage'] = $parentPage['Page'];

		if($currentPage['Page']['thread_num'] == 1 && $currentPage['Page']['space_type'] == NC_SPACE_TYPE_GROUP) {
			// コミュニティーならば
			$communityLang['CommunityLang']['community_name'] = $insPage['Page']['page_name'];

			$community_params = array(
				'community' => $community,
				'community_lang' => $communityLang,
				'community_tag' => $communityTag,
				'photo_samples' => $this->PageMenu->getCommunityPhoto()
			);
			$this->set('community_params', $community_params);
			$this->Community->set($community);
			$this->CommunityLang->set($communityLang);
			if (!$this->Community->validates(array('fieldList' => $fieldCommunityList))) {
				$errorFlag = 2;
			}
			if (!$this->CommunityLang->validates(array('fieldList' => $fieldCommunityLangList))) {
				$errorFlag = 2;
			}
			if(isset($this->request->data['Revision']['content'])) {
				// 自動保存
				$autoRegistParams = $this->RevisionList->beforeAutoRegist(isset($communityLang['CommunityLang']['id']) ? $communityLang['CommunityLang']['id'] : null);

				$isAutoRegist = $autoRegistParams['isAutoRegist'];
				$revisionName = $autoRegistParams['revision_name'];
				$pointer = _OFF;
				if(empty($communityLang['CommunityLang']['revision_group_id']) || !$isAutoRegist) {
					$pointer = _ON;
				}
				$revision = array(
					'Revision' => array(
						'group_id' => $communityLang['CommunityLang']['revision_group_id'],
						'pointer' => $pointer,
						'is_approved_pointer' => _ON,
						'revision_name' => $revisionName,
						'content_id' => 0,
						'content' => $this->request->data['Revision']['content']
					)
				);

				$fieldListRevision = array(
					'group_id', 'pointer', 'is_approved_pointer', 'revision_name', 'content_id', 'content',
				);
				unset($this->Revision->validate['content']['notEmpty']);
				$this->Revision->set($revision);
				if (!$this->Revision->validates(array('fieldList' => $fieldListRevision))) {
					$errorFlag = 3;
				}
				if (!$this->CommunityTag->validateTags($communityTag['CommunityTag']['tag_value'])) {
					$errorFlag = 3;
				}

				if(!$errorFlag && $this->request->is('post') &&
					(!empty($communityLang['CommunityLang']['revision_group_id']) || $this->Revision->isNotEmptyContent($revision[$this->Revision->alias]))) {
					if (!$this->Revision->save($revision, false, $fieldListRevision) && count($this->Revision->valodationErrors) > 0) {
						throw new InternalErrorException(__('Failed to update the database, (%s).', 'revisions'));
					}
					if(empty($communityLang['CommunityLang']['revision_group_id'])) {
						$communityLang['CommunityLang']['revision_group_id'] = $this->Revision->id;
						$this->CommunityLang->set($communityLang);	// 再セット
					}
					if ($isAutoRegist && !$this->CommunityLang->save($communityLang, false, $fieldCommunityLangList)) {
						throw new InternalErrorException(__('Failed to update the database, (%s).', 'community_langs'));
					}
				}

				if($isAutoRegist) {
					// 自動保存時後処理
					$this->RevisionList->afterAutoRegist($this->Revision->id);
					return;
				}
			}
			// 以前の値が同じか、langがenならば、Page.page_nameを更新。
			$params = array(
				'fields' => array('page_name'),
				'conditions' => array('id' => $page['Page']['id']),
				'recursive' => -1,
			);
			$pageNamePage = $this->Page->find('first', $params);
			if($pageNamePage['Page']['page_name'] != $communityLang['CommunityLang']['community_name'] && $lang != 'en') {
				$insPage['Page']['page_name'] = $pageNamePage['Page']['page_name'];
			}
		}

		// 編集ページ以下のページ取得
		$retChilds = $this->PageMenu->childsValidateErrors($this->action, $childPages, $currentPage);
		if($retChilds === false) {
			// 子ページエラーメッセージ保持
			$childsErrors = $this->Page->validationErrors;
		}

		$this->Page->set($insPage);
		$ret = $this->Page->validates(array('fieldList' => $fieldList));
		if ($this->request->is('post') && $ret && !$errorFlag && $retChilds !== false) {
			// 更新処理
			if (!$this->Page->save($insPage, false, $fieldList)) {
				throw new InternalErrorException(__('Failed to update the database, (%s).', 'pages'));
			}
			// 子供更新処理
			if(is_array($retChilds)) {
				if (!$this->PageMenu->childsUpdate($this->action, $retChilds[0], $retChilds[1])) {
					throw new InternalErrorException(__('Failed to update the database, (%s).', 'pages'));
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
					throw new InternalErrorException(__('Failed to update the database, (%s).', 'pages'));
				}

				// コミュニティー登録処理
				if (!$this->Community->save($community, false, $fieldCommunityList)) {
					throw new InternalErrorException(__('Failed to update the database, (%s).', 'communities'));
				}

				if (!$this->CommunityLang->save($communityLang, false, $fieldCommunityLangList)) {
					throw new InternalErrorException(__('Failed to update the database, (%s).', 'community_langs'));
				}

				if (isset($communityTag) && !$this->CommunityTag->saveTags($insPage['Page']['id'], $lang, $communityTag['CommunityTag']['tag_value'])) {
					throw new InternalErrorException(__('Failed to update the database, (%s).', 'community_tags'));
				}

				// Community.publication_range_flagが一部公開（すべてのログイン会員が閲覧可能）以外に更新されたら、PageUserLinkの不参加会員削除
				if($community['Community']['publication_range_flag'] != NC_PUBLICATION_RANGE_FLAG_LOGIN_USER) {
					$conditions = array(
						"PageUserLink.room_id" => $insPage['Page']['id'],
						"PageUserLink.authority_id" => NC_AUTH_OTHER_ID
					);
					if(!$this->PageUserLink->deleteAll($conditions)) {
						throw new InternalErrorException(__('Failed to delete the database, (%s).', 'page_user_links'));
					}
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

		$this->_renderItem($currentPage, $parentPage, $isDetail, $errorFlag, $childPages, $bufCurrentPermalink);
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
		if(!$page) {
			throw new InternalErrorException(__('Failed to obtain the database, (%s).', 'pages'));
		}

		$parentPage = $this->Page->findAuthById($page['Page']['parent_id']);
		if(!isset($parentPage['Page'])) {
			$this->response->statusCode('404');
			$this->flash(__('Page not found.'), '');
			return;
		}
		// 権限チェック
		$bufParentPage = ($page['Page']['thread_num'] > 1) ? $parentPage : null;
		if(!$this->PageMenu->validatorPage($this->request, $page, $bufParentPage)) {
			return;
		}

		$this->set('page', $page);
		$this->set('parent_page', $parentPage);

		if($page['Page']['thread_num'] == 1 && $page['Page']['space_type'] == NC_SPACE_TYPE_GROUP) {
			// コミュニティーならば
			$ret = $this->Community->getCommunityData($page['Page']['room_id']);
			if($ret === false) {
				throw new InternalErrorException(__('Failed to obtain the database, (%s).', 'communities'));
			}
			list($community, $communityLang, $communityTag) = $ret;
			// 自動保存等で最新のデータがあった場合、表示
			$revision = $this->Revision->findRevisions(null,$communityLang['CommunityLang']['revision_group_id'], 1);
			if(isset($revision[0])) {
				$communityLang['Revision'] = $revision[0]['Revision'];
			}
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
		$lang = Configure::read(NC_CONFIG_KEY.'.'.'language');
		$page = $this->request->data;
		if(!isset($page['Page']['id']) || !isset($page['Page']['display_flag'])) {
			throw new BadRequestException(__('Unauthorized request.<br />Please reload the page.'));
		}

		$currentPage = $this->Page->findAuthById($page['Page']['id'], $userId);
		if(!isset($currentPage['Page'])) {
			$this->response->statusCode('404');
			$this->flash(__('Page not found.'), '');
			return;
		}

		$parentPage = $this->Page->findAuthById($currentPage['Page']['parent_id']);
		if(!isset($parentPage['Page'])) {
			$this->response->statusCode('404');
			$this->flash(__('Page not found.'), '');
			return;
		}
		// 権限チェック
		$bufParentPage = ($currentPage['Page']['thread_num'] > 1) ? $parentPage : null;
		if(!$this->PageMenu->validatorPage($this->request, $currentPage, $bufParentPage)) {
			return;
		}

		// 更新処理
		$this->Page->id = $page['Page']['id'];
		if(!$this->Page->saveField('display_flag', $page['Page']['display_flag'])) {
			throw new InternalErrorException(__('Failed to update the database, (%s).', 'pages'));
		}

		$childPages = $this->Page->findChilds('all', $currentPage, $lang);
		// 子供の更新処理
		foreach($childPages as $key => $childPage) {
			$this->Page->id = $childPage['Page']['id'];
			if(!$this->Page->saveField('display_flag', $page['Page']['display_flag'])) {
				throw new InternalErrorException(__('Failed to update the database, (%s).', 'pages'));
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
		$lang = Configure::read(NC_CONFIG_KEY.'.'.'language');

		if(!isset($page['Page']['id']) ) {
			throw new BadRequestException(__('Unauthorized request.<br />Please reload the page.'));
		}

		$currentPage = $this->Page->findAuthById($page['Page']['id'], $userId);
		if(!isset($currentPage['Page'])) {
			$this->response->statusCode('404');
			$this->flash(__('Page not found.'), '');
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
		if(!$this->PageMenu->validatorPage($this->request, $currentPage, $parentPage)) {
			return;
		}

		// 編集ページ以下のページ取得
		$childPages = $this->Page->findChilds('all', $currentPage, $lang);

		foreach($childPages as $childPage) {
			if(!$this->PageMenu->validatorPageDetail($this->request, $childPage, null, true)) {
				return;
			}
			if($childPage['Page']['id'] == $this->page_id) {
				$isRedirect = true;
			}
		}

		// 削除処理
		if(!$this->Page->deletePage($currentPage['Page']['id'], $allDelete, $childPages, $parentRoomId)) {
			throw new InternalErrorException(__('Failed to delete the database, (%s).', 'pages'));
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
			throw new BadRequestException(__('Unauthorized request.<br />Please reload the page.'));
		}

		$currentPage = $this->Page->findAuthById($page['Page']['id'], $userId);
		$parentPage = $this->Page->findAuthById($currentPage['Page']['parent_id']);
		if(!isset($parentPage['Page'])) {
			$this->response->statusCode('404');
			$this->flash(__('Page not found.'), '');
			return;
		}
		// 権限チェック
		$bufParentPage = ($currentPage['Page']['thread_num'] > 1) ? $parentPage : null;
		if(!$this->PageMenu->validatorPage($this->request, $currentPage, $bufParentPage)) {
			return;
		}

		$this->TempData->gc();

		$hashKey = $this->PageMenu->getOperationKey($page['Page']['id'], $page['DropPage']['id']);
		if($this->TempData->readData($hashKey) !== false) {
			// 既に実行中
			$this->response->statusCode('200');
			$this->flash(__d('page', 'You are already running. Please try again at a later time.'), '');
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
			throw new InternalErrorException(__('Failed to execute the %s.', __('Move')));
		}

		$this->Session->setFlash(__('Has been successfully updated.'));

		// 再取得
		$page = $this->Page->findAuthById($insPages[0]['Page']['id'], $userId);
		$parentPage = $this->Page->findAuthById($page['Page']['parent_id'], $userId);
		$childPages = $this->Page->findChilds('all', $page, $lang, $userId);
		//$page = $insPages[0];
		//$parentPage = $this->Page->findById($page['Page']['parent_id']);
		//$childPages = $insPages;

		$this->_renderItem($page, $parentPage, false, false, $childPages);
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
		$lang = Configure::read(NC_CONFIG_KEY.'.'.'language');
		$page = $this->Page->findAuthById($pageId, $userId);
		$parentPage = null;
		$bufParentPage = $this->Page->findAuthById($page['Page']['parent_id'], $userId);
		if($page['Page']['thread_num'] > 1) {
			// 子グループ
			$parentPage = $bufParentPage;
		}

		// 権限チェック
		if(!$this->PageMenu->validatorPage($this->request, $page, $parentPage)) {
			return;
		}

		$authList = $this->Authority->findAuthSelect();
		if($this->request->is('post') && !isset($this->request->data['isSearch'])) {
			// 登録処理
			$authority = $this->Authority->findById($user['authority_id']);
			if(!isset($authority['Authority'])) {
				throw new BadRequestException(__('Unauthorized request.<br />Please reload the page.'));
			}
			$pageUserLinks = $this->PageMenu->participantSession($this->request, $pageId, $authList);
			$result = 'success';
			if(!empty($pageUserLinks['PageUserLink'])) {
				// 主担は一人は設定しているかどうかのチェック
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
				if($result == 'error') {
					throw new InternalErrorException(__('Failed to register the database, (%s).', 'page_user_links'));
				}
			}
			if($result == 'success') {
				$childPages = $this->Page->findChilds('all', $page, $lang, $userId);
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
						throw new InternalErrorException(__('Failed to update the database, (%s).', 'pages'));
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

				$this->_renderItem($page, $parentPage, false, false, $childPages);
				return;
			}
		}
		if(!$this->request->is('post') || (isset($this->request->data['isSearch']) && $this->request->data['isSearch'])) {
			// 権限割り当てのSession情報クリア
			$this->Session->delete(NC_SYSTEM_KEY.'.page_menu.PageUserLink['.$pageId.']');

		}

		$this->set('page', $page);
		$this->set('auth_list', $authList);
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
		if(!$this->PageMenu->validatorPage($this->request, $page, $parentPage)) {
			return;
		}

		$pageNum = empty($this->request->data['page']) ? 1 : intval($this->request->data['page']);
		$rp = empty($this->request->data['rp']) ? null : intval($this->request->data['rp']);
		$sortname = (!empty($this->request->data['sortname']) && ($this->request->data['sortname'] == "handle" || $this->request->data['sortname'] == "chief")) ? $this->request->data['sortname'] : null;
		$sortorder = (!empty($this->request->data['sortorder']) && ($this->request->data['sortorder'] == "asc" || $this->request->data['sortorder'] == "desc")) ? $this->request->data['sortorder'] : "asc";

		// 会員絞り込み
		$adminUserHierarchy = $this->ModuleSystemLink->findHierarchyByPluginName('User', $user['authority_id']);
		list($conditions, $joins) = $this->User->getRefineSearch($this->request, $adminUserHierarchy);

		$participantType = $this->Authority->getParticipantType($user['authority_id'], $page);
		$defaultAuthorityId = $this->Page->getDefaultAuthorityId($page);

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
		$this->set('page_user_links', $this->PageMenu->participantSession($this->request, $pageId));
		$this->set('default_authority_id', $defaultAuthorityId);
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
		if(!$this->PageMenu->validatorPage($this->request, $page, $parentPage)) {
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
		$lang = Configure::read(NC_CONFIG_KEY.'.'.'language');
		$page = $this->Page->findAuthById($pageId, $userId);
		$parentPage = $this->Page->findAuthById($page['Page']['parent_id'], $userId);
		// 権限チェック
		if($page['Page']['root_id'] != $page['Page']['room_id']) {
			// 子グループ
			$ret = $this->PageMenu->validatorPage($this->request, $page, $parentPage);
		} else {
			$ret = $this->PageMenu->validatorPage($this->request, $page);
		}
		if(!$ret) {
			return;
		}
		$childPages = $this->Page->findChilds('all', $page, $lang, $userId);

		// 登録処理
		$pageIdListArr = $this->_getIdList($page, $childPages);
		if(!$this->PageMenuUserLink->deallocation($page['Page']['room_id'], $pageIdListArr, $parentPage)) {
			throw new InternalErrorException(__('Failed to delete the database, (%s).', 'page_user_links'));
		}
		$page['Page']['room_id'] = $parentPage['Page']['room_id'];

		$this->Session->setFlash(__('Has been successfully updated.'));
		$this->_renderItem($page, $parentPage, false, false, $childPages);
	}

/**
 * 会員招待->会員選択実行時
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function select_member($currentPageId) {
		$user = $this->Auth->user();
		$userId = $user['id'];
		$currentPage = $this->Page->findAuthById($currentPageId, $userId);

		$pageNum = empty($this->request->data['page']) ? 1 : intval($this->request->data['page']);
		$limit = empty($this->request->data['limit']) ? PAGES_INVITE_COMMUNITY_SELECT_MEMBERS_LIMIT : intval($this->request->data['limit']);

		// 権限チェック
		if($currentPageId == 0 || !isset($currentPage['Page']) || $currentPage['Page']['thread_num'] != 1 ||
			$currentPage['Page']['space_type'] != NC_SPACE_TYPE_GROUP ||
			!$this->PageMenu->checkAuth($currentPage) || !$this->PageUserLink->isParticipate($currentPageId, $userId)) {
			$this->response->statusCode('403');
			$this->flash(__('Forbidden permission to access the page.'), '');
			return;
		}

		// 会員絞り込み
		$adminUserHierarchy = $this->ModuleSystemLink->findHierarchyByPluginName('User', $user['authority_id']);
		list($conditions, $joins) = $this->User->getRefineSearch($this->request, $adminUserHierarchy);
		$joins[] = array(
			"type" => 'LEFT',
			"table" => "page_user_links",
			"alias" => "PageUserLink",
			"conditions" => "`User`.`id`=`PageUserLink`.`user_id`".
			" AND `PageUserLink`.`room_id` =".intval($currentPageId)
		);
		$conditions['is_active'] = NC_USER_IS_ACTIVE_ON;

		$this->paginate['page'] = $pageNum;
		$this->paginate['limit'] = $limit;
		$this->paginate['conditions'] = $conditions;
		$this->paginate['joins'] = $joins;
		$this->paginate['order'] = array('User.handle' => 'asc');
		$users = $this->paginate('User');
		$users = $this->User->convertAvatarDisplay($users);

		$this->set('users', $users);
		$this->set('page', $currentPage);
	}

/**
 * ページのitemのrenderを行う
 * @param   Model Page    $page
 * @param   Model Pages   $parentPage
 * @param   boolean       $isDetail
 * @param   boolean       $errorFlag
 * @param   Model Pages   $childPages
 * @param   string        $prePermalink
 * @return  void
 * @since   v 3.0.0.0
 */
	private function _renderItem($page, $parentPage = null, $isDetail = false, $errorFlag = false, $childPages = null, $prePermalink = null) {
		$isParticipant = isset($this->request->data['is_participant']) ? intval($this->request->data['is_participant']) : _OFF;
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
		$this->set('is_detail', $isDetail);
		$this->set('error_flag', $errorFlag);
		$this->set('is_participant', $isParticipant);
		if(isset($prePermalink) && $prePermalink != $page['Page']['permalink']) {
			$this->set('permalink', rtrim($this->Page->getPermalink(str_replace('%2F', '/', urlencode($page['Page']['permalink'])), $page['Page']['space_type']), '/'));
			$this->set('pre_permalink', rtrim($this->Page->getPermalink(str_replace('%2F', '/', urlencode($prePermalink)), $page['Page']['space_type']), '/'));
		} else {
			$this->set('permalink', '');
			$this->set('pre_permalink', '');
		}
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
