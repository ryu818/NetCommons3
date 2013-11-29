<?php
/**
 * PageMenuComponentクラス
 *
 * <pre>
 * ページ操作用コンポーネント
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       App.Plugin.Page.Controller.Component
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class PageMenuComponent extends Component {
/**
 * _controller
 *
 * @var Controller
 */
	protected $_controller = null;

/**
 * startup
 *
 * @param Controller $controller
 */
	public function startup(Controller $controller) {
		$this->_controller = $controller;
	}

/**
 * ページ追加・削除、編集、表示順変更等バリデータ処理
 *
 * @param  CakeRequest $request
 * @param  Page Model  $page
 * @param  Page Model  $parentPage
 * @return boolean or false + flashメッセージ
 * @since   v 3.0.0.0
 */
	public function validatorPage($request, $page = null, $parentPage = null) {
		$loginUser = $this->_controller->Auth->user();
		$userId = $loginUser['id'];

		if(!$request->is('post') && $request->params['action'] != 'detail'
			&& $request->params['action'] != 'participant'
			&& $request->params['action'] != 'participant_cancel') {
			throw new BadRequestException(__('Unauthorized request.<br />Please reload the page.'));
		}
		if(!$request->is('ajax')
			&& ($request->params['action'] == 'detail' || $request->params['action'] == 'delete'
				|| $request->params['action'] == 'participant'
				|| $request->params['action'] == 'participant_detail'
				|| $request->params['action'] == 'participant_cancel'
				|| $request->params['action'] == 'deallocation'
				|| $request->params['action'] == 'copy' || $request->params['action'] == 'paste'
				|| $request->params['action'] == 'move' || $request->params['action'] == 'shortcut')
			) {
			throw new BadRequestException(__('Unauthorized request.<br />Please reload the page.'));
		}

		if(is_null($page)) {
			return false;
		}
		if(!isset($page['Page'])) {
			$this->_controller->response->statusCode('404');
			$this->_controller->flash(__('Page not found.'), '');
			return;
		}


		if(!$this->checkAuth($page, $parentPage) &&
			!(($request->params['action'] == 'delete' || $request->params['action'] == 'chgsequence') && $page['Page']['space_type'] == NC_SPACE_TYPE_GROUP
				&& $page['Page']['thread_num'] == 1 && $loginUser['allow_creating_community'] == NC_ALLOW_CREATING_COMMUNITY_ADMIN)) {
			$this->_controller->response->statusCode('403');
			$this->_controller->flash(__('Forbidden permission to access the page.'), '');
			return false;
		}

		if(!$this->validatorPageDetail($request, $page, $parentPage)) {
			return false;
		}

		return true;
	}

/**
 * 権限チェック
 * <pre>
 * TODO:Authority.allow_creating_communityによるチェックに切替予定
 *  管理者：コミュニティーの表示順変更、自分が主担でなくても追加・編集・削除・参加者修正、モジュール選択を許す。
 *  主担：コミュニティーの表示順変更(参加コミュニティのみ) TODO:未テスト。公開ルームの作成。
 *  モデレーター：コミュニティーの作成、編集、削除。（公開コミュニティーの作成。モデレーターのHierarchyを２つに分離する）
 *  一般：主担権限のルームへのページ追加、編集、削除
 *  	一般権限のHierarchyも２つに分離し、ページ操作、ブロック操作を行えるかどうかを追加するほうが望ましい。
 *  ゲスト：ページメニューをみるだけ。
 * </pre>
 * @param  Page Model  $page
 * @param  Page Model  $parentPage
 * @return boolean
 * @since   v 3.0.0.0
 */
	public function checkAuth($page = null, $parentPage = null) {
		$chkPage = isset($parentPage) ? $parentPage : $page;
		if(!$this->_controller->CheckAuth->checkAuth($chkPage['PageAuthority']['hierarchy'], NC_AUTH_CHIEF)) {
			return false;
		}

		if($page['Page']['position_flag'] != _ON) {
			return false;
		}
		return true;
	}

/**
 * ページ追加・削除、編集、表示順変更等バリデータ処理(詳細)
 *
 * @param  CakeRequest $request
 * @param  Page Model  $page
 * @param  Page Model  $parentPage
 * @param  boolean     $isChild
 *
 * @return boolean
 * @since   v 3.0.0.0
 */
	public function validatorPageDetail($request, $page = null, $parentPage = null, $isChild = false) {
		$user = $this->_controller->Auth->user();
		$lang = Configure::read(NC_CONFIG_KEY.'.'.'language');
		if($isChild == false && $page['Page']['lang'] != '' && $page['Page']['lang'] != $lang) {
			// 編集のlangと現在のlangが異なる
			throw new BadRequestException(__('Unauthorized request.<br />Please reload the page.'));
		}

		// コミュニティー作成権限がない会員は、コミュニティーで主担にしてもコミュニティー修正、参加者修正できなくする。->人的管理をしない。
		if($page['Page']['thread_num'] == 1 && $page['Page']['space_type'] == NC_SPACE_TYPE_GROUP) {
			if($user['allow_creating_community'] == NC_ALLOW_CREATING_COMMUNITY_OFF) {
				throw new BadRequestException(__('Unauthorized request.<br />Please reload the page.'));
			}
		}

		switch($request->params['action']) {
			case 'add':
				if($page['Page']['thread_num'] == 0) {
					throw new BadRequestException(__('Unauthorized request.<br />Please reload the page.'));
				}
				break;
			case 'display':
				// 親がOFFならば変更を許さない。
				$parentPage = $this->_controller->Page->findById($page['Page']['parent_id']);
				if(!isset($parentPage['Page']) || $parentPage['Page']['display_flag'] != NC_DISPLAY_FLAG_ON) {
					throw new BadRequestException(__('Unauthorized request.<br />Please reload the page.'));
				}
				if($page['Page']['thread_num'] == 2 && $page['Page']['display_sequence'] == 1) {
					//Top Nodeの公開・非公開の設定は許さない。
					throw new BadRequestException(__('Unauthorized request.<br />Please reload the page.'));
				}
				break;
			case 'delete':
			case 'chgsequence':
			case 'move':
				// 各スペースタイプのトップページは最低１つ以上ないと削除不可。
				if (!$isChild && $page['Page']['thread_num'] == 2 && $page['Page']['display_sequence'] == 1) {
					// 該当ルームのTopページの数を取得
					$conditions = array(
						'Page.room_id' => $page['Page']['room_id'],
						'Page.display_sequence' => $page['Page']['display_sequence'],
						'Page.thread_num' => $page['Page']['thread_num'],
						'Page.position_flag' => $page['Page']['position_flag']
					);
					$count = $this->_controller->Page->find('count', array('conditions' => $conditions));

					if($count == 1) {
						if($request->params['action'] == 'delete') {
							echo __d('page', 'Top of each page is required at least one page. <br />You can\'t delete.');
						} else {
							echo __d('page', 'Top of each page is required at least one page. <br />You can\'t move.');
						}
						$this->_controller->render(false, 'ajax');
						return false;
					}

					// トップページを削除する場合、本ルームのページをすべて削除後でなければ削除不可。
					$conditions = array(
						'Page.room_id' => $page['Page']['room_id'],
						'Page.position_flag' => $page['Page']['position_flag'],
						'Page.display_sequence >' => 1,
						'Page.lang' => array('', $lang)
					);
					$count = $this->_controller->Page->find('count', array('conditions' => $conditions));
					if($count > 1) {
						if($request->params['action'] == 'delete') {
							echo __d('page', 'If you want to delete the top page, please run after deleting all the pages of this room.<br />You can\'t delete.');
						} else {
							echo __d('page', 'If you want to move the top page, please run after deleting all the pages of this room.<br />You can\'t move.');
						}
						$this->_controller->render(false, 'ajax');
						return false;
					}
				}
				// break;
			case 'edit':
				if($page['Page']['thread_num'] == 0 || ($page['Page']['thread_num'] == 1 && $page['Page']['space_type'] != NC_SPACE_TYPE_GROUP)) {
					//Top Nodeの編集はコミュニティTop以外許さない
					throw new BadRequestException(__('Unauthorized request.<br />Please reload the page.'));
				}
				/*if($page['Page']['space_type'] != NC_SPACE_TYPE_PUBLIC && $page['Page']['thread_num'] == 2 && $page['Page']['display_sequence'] == 1) {
					//パブリック以外の各ノードのTopページの編集は許さない
					throw new BadRequestException(__('Unauthorized request.<br />Please reload the page.'));
				}*/
				break;
			case 'shortcut':
			case 'paste':
				// ページロックしてあれば、移動不可。
				if($page['Page']['lock_authority_id'] > 0) {
					$page = $this->_controller->Page->setPageName($page);
					echo __d('page', 'Because the [%s] page is locked, I can\'t be deleted. Please run after unlock the page.', $page['Page']['page_name']);
					$this->_controller->render(false, 'ajax');
					return false;
				}
				if($page['Page']['thread_num'] <= 1) {
					if($page['Page']['space_type'] != NC_SPACE_TYPE_GROUP || $user['allow_creating_community'] != NC_ALLOW_CREATING_COMMUNITY_ADMIN) {
						// TODO:後にパブリックのものをコミュニティへペースト等ができるようにしたほうが望ましい。
						throw new BadRequestException(__('Unauthorized request.<br />Please reload the page.'));
					}
				}
				break;
			case "participant":
			case "deallocation":
				if($page['Page']['space_type'] == NC_SPACE_TYPE_PRIVATE) {
					// プライベートスペースは権限を設定不可
					throw new BadRequestException(__('Unauthorized request.<br />Please reload the page.'));
				}
				if($page['Page']['thread_num'] == 0 || ($page['Page']['thread_num'] == 2 && $page['Page']['display_sequence'] == 1)) {
					throw new BadRequestException(__('Unauthorized request.<br />Please reload the page.'));
				}
				if($request->params['action'] == "deallocation" && $page['Page']['thread_num'] == 1) {
					throw new BadRequestException(__('Unauthorized request.<br />Please reload the page.'));
				}
		}
		return true;
	}

/**
 *  表示順変更 移動元-移動先権限チェック
 *
 * @param  Page Model  $page
 * @param  Page Model  $movePage
 * @param  string      $position inner or bottom or top
 * @param  Page Model  $parentPage
 * @return boolean
 * @since   v 3.0.0.0
 */
	public function validatorMovePage($page, $movePage, $position, $parentPage) {
		$userId = $this->_controller->Auth->user('id');

		if(($page['Page']['space_type'] != NC_SPACE_TYPE_GROUP && $page['Page']['thread_num'] == 1)
				|| $page['Page']['thread_num'] == 0
				|| $movePage['Page']['thread_num'] == 0
				|| ($page['Page']['space_type'] != NC_SPACE_TYPE_GROUP
						&& $movePage['Page']['thread_num'] == 1
						&& $position != 'inner')) {
			// 移動元がコミュニティー以外のノード,移動先が Top Node
			throw new BadRequestException(__('Unauthorized request.<br />Please reload the page.'));
		}

		if($position != 'inner' && $position != 'top' && $position != 'bottom') {
			throw new BadRequestException(__('Unauthorized request.<br />Please reload the page.'));
		}

		if($movePage['Page']['thread_num'] == 2
			&& $movePage['Page']['display_sequence'] == 1
			&& ($position == 'inner' || $position == 'top')) {
			// 各スペースタイプのトップページの中か上に移動しようとした。
			throw new BadRequestException(__('Unauthorized request.<br />Please reload the page.'));
		}

		if($position != 'inner'
			&& (($page['Page']['thread_num'] == 1 && $movePage['Page']['thread_num'] != 1)
				|| ($movePage['Page']['thread_num'] == 1 && $page['Page']['thread_num'] != 1))) {
			// コミュニティーTopノードへの移動チェック
			throw new BadRequestException(__('Unauthorized request.<br />Please reload the page.'));
		}
		return true;
	}

/**
 *  子ページバリデータ処理
 *  ページ表示順変更、編集時、ページペースト、ページ移動、ページショートカットの作成、子供編集処理
 *
 *  <pre>
 *  現在のpage下のページすべてのpermalink更新処理
 *  From公開日付は、「下位にも適用」の場合、親の日付で更新
 *  To公開日付は、子供がセットしていないか、親よりも古い日付ならば、親の日付で更新
 *  </pre>
 *
 * @param  string       $action edit or move or shortcut or paste
 * @param  Page Models  $childPages
 * @param  Page Model   $insPage 親ページ
 * @param  array        $appendField 更新カラム情報($action != 'edit'のみ)
 * @return 成功 array($retPages, $retFieldChildList) 失敗 false エラーメッセージ データなし true
 * @since   v 3.0.0.0
 */
	public function childsValidateErrors($action, $childPages, $insPage, $appendField = null) {

		// 表示順変更の場合、「下位にも適用」のFrom公開日付を１つ上の階層のものしかみていない。
		// このため、２つ以上、上位階層で「下位にも適用」でFrom公開日付を設定されていると
		// From公開日付が設定されないが、上位階層を表示すれば自動的に公開にはなるため、
		// 更新は行わない。
		$retPages = array();
		$retFieldChildList = array();

		$currentPermalinkArr = array(
			$insPage['Page']['id'] => $insPage['Page']['permalink']
		);
		foreach($childPages as $childPage) {
			$fieldChildList = array('permalink');
			if($action != 'edit' && isset($appendField)) {
				$fieldChildList = array_merge($fieldChildList, array_keys($appendField));
			}
			if(count($fieldChildList) > 1) {
				$bufAppendField = $appendField;
				$childPage['Page']['display_sequence'] = $childPage['Page']['display_sequence'] + $bufAppendField['display_sequence'];
				$childPage['Page']['thread_num'] = $childPage['Page']['thread_num'] + $bufAppendField['thread_num'];
				unset($bufAppendField['display_sequence']);
				unset($bufAppendField['thread_num']);
				$childPage['Page'] = array_merge($childPage['Page'], $bufAppendField);
				unset($bufAppendField);
			}
			$permalinkArr = explode('/', $childPage['Page']['permalink']);
			if($currentPermalinkArr[$childPage['Page']['parent_id']] != ''
					&& !($childPage['Page']['thread_num'] == 2 && $childPage['Page']['display_sequence'] == 1)) {
				$updPermalink = $currentPermalinkArr[$childPage['Page']['parent_id']] . '/' . $permalinkArr[count($permalinkArr)-1];
			} else {
				$updPermalink = $permalinkArr[count($permalinkArr)-1];
			}
			$childPage['Page']['permalink'] = $updPermalink;
			$currentPermalinkArr[$childPage['Page']['id']] = $updPermalink;

			list($childPage, $fieldChildList) = $this->setDisplay($childPage, $insPage, $fieldChildList);

			$this->_controller->Page->create();
			//if($action == 'paste' || $action == 'shortcut') {
			//	unset($childPage['Page']['id']);	// pageをinsertするため
			//	//$ret = $this->_controller->Page->save($childPage);
			//} //else {
				//$ret = $this->_controller->Page->save($childPage, false, $fieldChildList);	//親でUpdate処理実行後に更新するためバリデータを実行しない。
			//}
			$this->_controller->Page->set($childPage);
			$ret = $this->_controller->Page->validates(array('fieldList' => $fieldChildList));
			//$all_errors = $this->_controller->validateErrors($this->_controller->Page);
			//if(is_array($all_errors)) {
			if(!$ret) {
				return false;
			}
			$retPages[] = $childPage;
			$retFieldChildList[] = $fieldChildList;
		}
		if(count($retPages) == 0) {
			return true;
		}
		return array($retPages, $retFieldChildList);
	}

/**
 *  子ページ更新処理
 *  ページ表示順変更、編集時、ページペースト、ページ移動、ページショートカットの作成、子供編集処理
 *
 * @param  string       $action edit or move or shortcut or paste
 * @param  Page Models  $childPages
 * @param  array        $fieldChildsList
 * @param  integer      $oldParentId
 * @param  integer      $newParentId
 * @return boolean false or array new Model Pages $childPages
 * @since   v 3.0.0.0
 */
	public function childsUpdate($action, $childPages, $fieldChildsList = null, $oldParentId = null, $newParentId = null) {
		$newChildPages = array();
		if($oldParentId != $newParentId) {
			$parentIdArr = array(
				$oldParentId => $newParentId
			);
		}
		foreach($childPages as $i => $childPage) {
			$fieldChildList = $fieldChildsList[$i];
			$this->_controller->Page->create();
			if(isset($parentIdArr)) {
				if(isset($parentIdArr[$childPage['Page']['parent_id']])) {
					$childPage['Page']['parent_id'] = $parentIdArr[$childPage['Page']['parent_id']];
					if(!empty($fieldChildList)) {
						$fieldChildList[] = 'parent_id';
					}
				}
				/*if($childPage['Page']['parent_id'] == $oldParentId) {
					$childPage['Page']['parent_id'] = $newParentId;
					if(!empty($fieldChildList)) {
						$fieldChildList[] = 'parent_id';
					}
				} */
			}
			$old_id = $childPage['Page']['id'];
			if($action == 'paste' || $action == 'shortcut') {
				unset($childPage['Page']['id']);	// pageをinsertするため
			}
			if($childPage['Page']['display_sequence'] == 1) {
				if($childPage['Page']['space_type'] == NC_SPACE_TYPE_MYPORTAL) {
					$childPage['Page']['page_name'] = "Myportal Top";
				} else if($childPage['Page']['space_type'] == NC_SPACE_TYPE_PRIVATE) {
					$childPage['Page']['page_name'] = "Private Top";
				} else if($childPage['Page']['space_type'] == NC_SPACE_TYPE_GROUP) {
					$childPage['Page']['page_name'] = "Community Top";
				}
			}
			if(empty($childPage['Page']['id']) || empty($fieldChildList)) {
				$ret = $this->_controller->Page->save($childPage, false);
			} else {
				$ret = $this->_controller->Page->save($childPage, false, $fieldChildList);
			}
			$newId = $this->_controller->Page->id;
			if(!$ret) {
				return false;
			}
			$childPage['Page']['id'] = $newId;
			$newChildPages[] = $childPage;
			if(isset($parentIdArr)) {
				$parentIdArr[$old_id] = $newId;
			}
		}
		return $newChildPages;
	}

/**
 * Insert Page defaults
 * @param   string     edit(編集) or inner or bottom(追加) $type
 * @param   array      $currentPage
 * @param   array      $parentPage
 * @return  array      array($page, $parentPage)
 * @since   v 3.0.0.0
 */
	public function getDefaultPage($type, $currentPage, $parentPage = null) {
		$lang = Configure::read(NC_CONFIG_KEY.'.'.'language');
		if(!isset($parentPage)) {
			$parentPage = $currentPage;
		}

		$insPage = $currentPage;
		if($type == 'inner') {
			unset($insPage['Page']['id']);
			$insPage['Page']['parent_id'] = $currentPage['Page']['id'];
			$insPage['Page']['thread_num'] = $currentPage['Page']['thread_num'] + 1;

		} else if($type == 'bottom') {
			unset($insPage['Page']['id']);

			// hierarchy
			$insPage['PageAuthority']['hierarchy'] = $parentPage['PageAuthority']['hierarchy'];
		}
		if($type != 'inner') {
			$insPage['Page']['room_id'] = $parentPage['Page']['room_id'];
		}
		if($currentPage['Page']['thread_num'] == 1) {
			$displaySequence = 1;
		} else {
			$displaySequence = $currentPage['Page']['display_sequence'] + 1;
		}
		$conditions = array(
			'Page.position_flag' => _ON,
			'Page.space_type' => $currentPage['Page']['space_type'],
			'Page.lang' => array('', $lang)
		);
		if($currentPage['Page']['root_id'] != 0) {
			$conditions['Page.root_id'] = $currentPage['Page']['root_id'];
		}
		$displaySequence_results = $this->_controller->Page->find('all', array(
			'fields' => 'Page.id, Page.parent_id, Page.display_sequence',
			'recursive' => -1,
			'conditions' => $conditions,
			'order' => 'Page.display_sequence'
		));

		$displaySequence_pages = array();
		$parentIdArr[] = $currentPage['Page']['id'];
		foreach ($displaySequence_results as $key => $val) {
			if(in_array($val['Page']['parent_id'], $parentIdArr)) {
				$displaySequence_pages[$val['Page']['id']] = $val;
				$parentIdArr[] = $val['Page']['id'];
				$displaySequence = $val['Page']['display_sequence'] + 1;
			}
		}

		$spaceType = $insPage['Page']['space_type'];
		$threadNum = $insPage['Page']['thread_num'];

		$countFields = 'MAX(Page.display_sequence) as max_number';
		$countConditions = array(
			'Page.root_id' => $parentPage['Page']['root_id'],
			'Page.thread_num >' => 1,
			'Page.lang' => array('', $lang)
		);
		$result = $this->_controller->Page->find('first', array(
			'fields' => $countFields,
			'recursive' => -1,
			'conditions' => $countConditions
		));
		if(isset($result[0]['max_number'])) {
			$count = intval($result[0]['max_number']) + 1;
		} else {
			$count = 1;
		}

		if($displaySequence == 1) {
			// 各トップページ
			$permalink = '';
			if($parentPage['Page']['permalink'] != '') {
				$permalink = $parentPage['Page']['permalink'].$permalink;
			}
			if($spaceType == NC_SPACE_TYPE_MYPORTAL) {
				$pageName = __("Myportal Top");
			} else if($spaceType == NC_SPACE_TYPE_PRIVATE) {
				$pageName = __("Private Top");
			} else if($spaceType == NC_SPACE_TYPE_GROUP) {
				$pageName = __("Community Top");
			} else {
				$pageName = __d('page', "New page");
			}
		} else {
			$pageName = __d('page', "New page");
			list($pageName, $permalink) = $this->_getPageName($pageName, $count, $parentPage);
		}

		$insPage['Page']['display_sequence'] = $displaySequence;
		$insPage['Page']['page_name'] = $pageName;
		$insPage['Page']['permalink'] = $permalink;
		$insPage['Page']['show_count'] = 0;

		$insPage['Page']['display_flag'] = $parentPage['Page']['display_flag'];
		if(!empty($parentPage['Page']['display_from_date']) && $parentPage['Page']['display_apply_subpage'] == _ON) {
			$insPage['Page']['display_from_date'] = $parentPage['Page']['display_from_date'];
		} else {
			$insPage['Page']['display_from_date'] = null;
		}
		if(!empty($parentPage['Page']['display_to_date'])) {
			$insPage['Page']['display_to_date'] = $parentPage['Page']['display_to_date'];
		} else {
			$insPage['Page']['display_to_date'] = null;
		}

		if($spaceType == NC_SPACE_TYPE_PRIVATE || ($spaceType == NC_SPACE_TYPE_GROUP && $threadNum == 1)) {
			$insPage['Page']['lang'] = '';
		} else {
			$insPage['Page']['lang'] = $lang;
		}

		return $insPage;
	}

/**
 * Insert Community Page defaults
 * @param   integer    $currentPageId
 * @param   integer    $allCommunityCnt
 * @return  Model Page
 * @since   v 3.0.0.0
 */
	public function getDefaultCommunityPage($currentPageId, $allCommunityCnt) {
		// $currentPageIdがコミュニティーではなければ、一番後ろに追加。
		$currentPage = $this->_controller->Page->findById($currentPageId);
		if(isset($currentPage['Page']) && $currentPage['Page']['space_type'] == NC_SPACE_TYPE_GROUP) {
			if($currentPage['Page']['thread_num'] != 1) {
				$currentPage = $this->_controller->Page->findById($currentPage['Page']['root_id']);
			}
			$displaySequence = $currentPage['Page']['display_sequence'] + 1;
		} else {
			$displaySequence = $allCommunityCnt + 1;
		}
		$pageName = __d('page', "New community");
		list($pageName, $permalink) = $this->_getPageName($pageName, $allCommunityCnt + 1);

		$insPage = array('Page' =>array(
			'root_id' => 0,
			'parent_id' => NC_TOP_GROUP_ID,
			'thread_num' => 1,
			'display_sequence' => $displaySequence,
			'page_name' => $pageName,
			'permalink' => $permalink,
			'position_flag' => _ON,
			'lang' => '',
			'is_page_meta_node' => _OFF,
			'is_page_style_node' => _OFF,
			'is_page_layout_node' => _OFF,
			'is_page_theme_node' => _OFF,
			'is_page_column_node' => _OFF,
			'space_type' => NC_SPACE_TYPE_GROUP,
			'show_count' => 0,
			'display_flag' => _ON,
			'display_to_date' => null,
			'display_apply_subpage' => _ON,
			'display_reverse_permalink' => null,
			'lock_authority_id' => 0
		));
		return $insPage;
	}

/**
 * 固定リンクが同じではないページ名称,固定リンクを取得
 * @param   string        $pageName
 * @param   integer       $count カウント数初期値
 * @param   Model Page    $parentPage 親ページ
 * @return  array    array($pageName, $permalink)
 * @since   v 3.0.0.0
 */
	protected function _getPageName($pageName, $count, $parentPage = null) {
		$lang = Configure::read(NC_CONFIG_KEY.'.'.'language');
		$permalink = preg_replace(NC_PERMALINK_PROHIBITION, NC_PERMALINK_PROHIBITION_REPLACE, $pageName);
		if($parentPage['Page']['permalink'] != '') {
			$permalink = $parentPage['Page']['permalink']. '/'. $permalink;
		}
		while(1) {
			$conditions = array(
				'Page.permalink' => $permalink. '-' . $count,
				'Page.lang' => array('', $lang)
			);
			$result = $this->_controller->Page->find('first', array(
				'fields' => 'Page.id',
				'recursive' => -1,
				'conditions' => $conditions
			));
			if(isset($result['Page'])) {
				$count++;
			} else {
				break;
			}
		}
		return array($pageName. '-' . $count, $permalink. '-' . $count);
	}

/**
 * コミュニティーPhotoサンプル画像名称取得
 * @return  array
 * @since   v 3.0.0.0
 */
	public function getCommunityPhoto() {
		$photoSamples = array();
		$pluginPath = App::pluginPath('Page');
		$samplePath = $pluginPath . 'webroot' . DS .'img'. DS .'community'. DS;
		if(is_dir($samplePath)) {
			$dirArray = glob( $samplePath . DS . "*" );
			if(is_array($dirArray) && count($dirArray) > 0) {
				foreach( $dirArray as $childPath){
					if(!is_dir( $childPath )) {
						$fileName = basename($childPath);
						$photoSamples[$fileName] = $fileName;
					}
				}
			}
		}
		return $photoSamples;
	}

/**
 * PageUserLinksテーブルのデータをSessionにセット
 *
 * @param   CakeRequest $request
 * @param   integer     $pageId
 * @param   array       $authList	権限リストになければベースの権限でセットさせる
 * @return  array $pageUserLinks
 * @since   v 3.0.0.0
 */
	public function participantSession($request, $pageId, $authList = null) {
		$loginUser = $this->_controller->Auth->user();
		$userId = $loginUser['id'];

		$bufPageUserLinks = $this->_controller->Session->read(NC_SYSTEM_KEY.'.page_menu.PageUserLink['.$pageId.']');
		$writeFlag = false;

		if(!empty($request->data['PageUserLink'])) {
			// 権限項目
			$bufPageUserLinksParams['PageUserLink'] = $request->data['PageUserLink'];
			$pageUserLinks = $this->_controller->PageUserLink->findAllByRoomId($pageId);
			foreach($pageUserLinks as $pageUserLink) {
				if(!isset($bufPageUserLinksParams['PageUserLink'][$pageUserLink['PageUserLink']['user_id']])) {
					$bufPageUserLinksParams['PageUserLink'][$pageUserLink['PageUserLink']['user_id']] = $pageUserLink['PageUserLink'];
				}
			}

			if($loginUser['allow_creating_community'] != NC_ALLOW_CREATING_COMMUNITY_ADMIN) {
				// ページメニューが管理者権限でないならば、ログイン会員は必ず主担として参加
				$bufPageUserLinksParams['PageUserLink'][$userId] = array(
					'id' => 0,
					'room_id' => $pageId,
					'user_id' => $userId,
					'authority_id' => NC_AUTH_CHIEF_ID
				);
			}
			if(isset($authList)) {
				foreach($bufPageUserLinksParams['PageUserLink'] as $key => $bufData) {
					if(!isset($authList[NC_AUTH_CHIEF][$bufData['authority_id']]) && !isset($authList[NC_AUTH_MODERATE][$bufData['authority_id']]) &&
							!isset($authList[NC_AUTH_GENERAL][$bufData['authority_id']]) && $bufData['authority_id'] != NC_AUTH_GUEST_ID &&
							$bufData['authority_id'] != NC_AUTH_OTHER_ID) {
						// 存在しない権限に変更しようとした->ゲストにする
						$bufPageUserLinksParams['PageUserLink'][$key]['authority_id'] = NC_AUTH_GUEST_ID;
					}
				}
			}
			if(!empty($bufPageUserLinks)) {
				//$bufPageUserLinks['PageUserLink'] = array_merge($bufPageUserLinks['PageUserLink'], $bufPageUserLinksParams['PageUserLink']);
				foreach($bufPageUserLinksParams['PageUserLink'] as $key => $bufData) {
					$bufPageUserLinks['PageUserLink'][$key] = $bufData;
				}
			} else {
				$bufPageUserLinks = $bufPageUserLinksParams;
			}
			$writeFlag = true;
		}
		if($writeFlag) {
			$this->_controller->Session->write(NC_SYSTEM_KEY.'.page_menu.PageUserLink['.$pageId.']', $bufPageUserLinks);
		}

		return $this->_controller->Session->read(NC_SYSTEM_KEY.'.page_menu.PageUserLink['.$pageId.']');
	}

/**
 * 移動、ペースト、ショートカット作成時　Pageデータ取得
 *
 * @param   integer  $prePageId 移動元（コピー元、ショートカット元）PageId
 * @param   integer  $movePageId 移動先（コピー先、ショートカット先）PageId
 * @param   string   $position 移動位置 default:inner   inner or top or bottom
 * @return  array    array(Model Page 移動元Page, Model Page 移動先Page, Model Page 移動元親Page, Model Page 移動先親Page, string 移動先ルーム名称,
 * 							array 移動元ページID配列, array 移動元ルームのページID配列, Model Pages 移動元子供Pages,
 * 							Model Pages 移動先子供Pages,
 * 							Model Page 移動元のもっとも多いdisplay_sequenceのPage, Model Page 移動先のもっとも多いdisplay_sequenceのPage)
 * @since   v 3.0.0.0
 */
	public function getOperationPage($prePageId, $movePageId, $position = 'bottom') {
		$userId = $this->_controller->Auth->user('id');
		$prePage = $this->_controller->Page->findAuthById($prePageId, $userId);
		$movePage = $this->_controller->Page->findAuthById($movePageId, $userId);
		if(!isset($prePage['Page']) || !isset($movePage['Page'])) {
			return false;
		}

		$parentPrePage = $this->_controller->Page->findAuthById($prePage['Page']['parent_id'], $userId);
		if($position != 'inner') {
			$parentMovePage = $this->_controller->Page->findAuthById($movePage['Page']['parent_id'], $userId);
		} else {
			$parentMovePage = $movePage;
		}

		if($movePage['Page']['id'] == $movePage['Page']['room_id']) {
			$moveRoomName = $movePage['Page']['page_name'];
		} else if($movePage['Page']['room_id'] == $parentMovePage['Page']['id']) {
			$moveRoomName = $parentMovePage['Page']['page_name'];
		} else {
			$parentRoomPage = $this->_controller->Page->findAuthById($movePage['Page']['room_id'], $userId);
			$moveRoomName = $parentRoomPage['Page']['page_name'];
		}
		$prePageIdArr = array($prePage['Page']['id']);
		$preRoomIdArr = array();
		if($prePage['Page']['id'] == $prePage['Page']['room_id']) {
			$preRoomIdArr = array($prePage['Page']['id']);
		}
		// 移動元ページ以下のページ取得
		$childPrePages = $this->_controller->Page->findChilds('all', $prePage);
		$lastPrePage = $prePage;
		foreach($childPrePages as $childPage) {
			$prePageIdArr[] = $childPage['Page']['id'];
			if($childPage['Page']['id'] == $childPage['Page']['room_id']) {
				$preRoomIdArr[] = $childPage['Page']['id'];
			}
			// ノード中のもっとも多いdisplay_sequenceのPageを取得
			if($prePage['Page']['thread_num'] != 1 && $lastPrePage['Page']['display_sequence'] < $childPage['Page']['display_sequence']) {
				$lastPrePage = $childPage;
			}
		}

		$childMovePages = $this->_controller->Page->findChilds('all', $movePage);
		$lastMovePage = $movePage;
		if($movePage['Page']['thread_num'] != 1 || $position == 'inner') {
			foreach($childMovePages as $childPage) {
				if(in_array($childPage['Page']['id'], $prePageIdArr)) {
					continue;
				}
				// ノード中のもっとも多いdisplay_sequenceのPageを取得
				if($prePage['Page']['thread_num'] != 1 && $lastMovePage['Page']['display_sequence'] < $childPage['Page']['display_sequence']) {
					$lastMovePage = $childPage;
				}
			}
		}
		return array($prePage, $movePage, $parentPrePage, $parentMovePage, $moveRoomName, $prePageIdArr, $preRoomIdArr,
				$childPrePages, $childMovePages, $lastPrePage, $lastMovePage);
	}

/**
 * 確認メッセージ表示
 * @param  string $action Action名
 * @param  string $position
 * @param  Model Page  $prePage 移動元（コピー元、ショートカット元）Page
 * @param  Model Page  $movePage 移動先（コピー先、ショートカット先）Page
 * @param  string 移動先ルーム名称
 * @param  Model Page  $preParentPage
 * @param  Model Page  $moveParentPage
 * @param  array 移動元ルームのページID配列
 * @param  Model Blocks $blocks 移動元blocks
 * @return string dialog html string
 * @since   v 3.0.0.0
 */
	public function showConfirm($action, $position, $prePage, $movePage, $moveRoomName, $preParentPage, $moveParentPage, $preRoomIdArr, $blocks) {
		$preRoomId = $prePage['Page']['room_id'];
		$moveRoomId = $movePage['Page']['room_id'];

		$echoStr = '<div class="pages-menu-edit-confirm-desc">';
		switch($action) {
			case 'chgsequence':
			case 'move':
				if($position != 'inner' && $movePage['Page']['thread_num'] == 1) {
					// コミュニティ
					return '';	// 確認メッセージなし
				} else if(count($blocks) > 0 || (count($preRoomIdArr) > 0 && $preParentPage['Page']['room_id'] != $moveParentPage['Page']['room_id'])) {
					// ブロックあり
					if($preRoomId != $moveRoomId) {
						// 異なるルームへ
						$echoStr .= __d('page', 'You are about to move to [%s]. <br />When you move the contents of the [%s], it becomes contents of [%s].Are you sure?',
							$moveRoomName, $prePage['Page']['page_name'], $moveRoomName);
					} else {
						$echoStr .= __d('page','You are about to move to [%s]. Are you sure?', $moveRoomName);
					}
				} else {
					return '';	// 確認メッセージなし
				}
				break;
			case 'paste':
				$echoStr .= __d('page','You create a copy to [%s]. The copying of contents may take time to some extent. Are you sure?', $moveRoomName);
				break;
			case 'shortcut':
				$echoStr .= __d('page','You create a shortcut to [%s]. Are you sure?', $moveRoomName);
				break;
		}

		$echoStr .= '</div>';

		if($action == 'shortcut' && $preRoomId != $moveRoomId) {
			// 移動先が移動元と異なれば、チェックボックス表示
			$echoStr .= '<label class="pages-menu-edit-confirm-shortcut" for="pages-menu-edit-confirm-shortcut">'.
					'<input id="pages-menu-edit-confirm-shortcut" type="checkbox" name="shortcut_type" value="'._ON.'" />&nbsp;'.
					__('Allow the room authority to view and edit.').
					'</label>';
		}

		// 注釈
		$echoSubStr = '';
		if(count($preRoomIdArr) > 0 && $preParentPage['Page']['room_id'] != $moveParentPage['Page']['room_id']) {
			// ルーム、または、子グループあり
			$echoSubStr .= '<li>'.__d('page', 'The assignment of the rights of the room is released.').'</li>';
		}
		if($action != 'move' && $action != 'chgsequence') {
			$echoSubStr .= '<li>'.__d('page', 'Block shortcut is copied as is.').'</li>';
			$echoSubStr .= '<li>'.__d('page', 'Only a block located on the page is copied.').'</li>';
		}
		if($position != 'inner') {
			$echoSubStr .= '<li>'.__d('page', 'It is added under the page that you selected.').'</li>';
		} else {
			$echoSubStr .= '<li>'.__d('page', 'It is added to the page that you selected.').'</li>';
		}
		$echoStr .= '<div class="align-right"><a class="pages-menu-edit-confirm-note" href="#" onclick="$(\'#pages-menu-edit-confirm-ul\').toggle();return false;">'.__('Note')
				.'</a></div><ul id="pages-menu-edit-confirm-ul">'.$echoSubStr.'</ul>';

		if(count($blocks) != 0 && $action == 'paste') {
			$exemptModules = array();
			// 配置ブロックあり - ペースト関数チェック
			foreach($blocks as $block) {
				if(!isset($block['Module']['dir_name'])) {
					// グループブロック
					continue;
				}
				$moduleId = $block['Block']['module_id'];
				$dirName = $block['Module']['dir_name'];
				// module_linksで移動先ルームに貼り付けることができるかどうか確認
				if(!isset($exemptModules[$moduleId]) && !$this->_controller->ModuleLink->isAddModule($movePage, $moduleId)) {
					// 操作対象外
					$exemptModules[$moduleId] = $this->_controller->Module->loadModuleName($dirName);
				}

				if(!isset($exemptModules[$moduleId]) && $action == 'paste') {
					// ショートカットと移動は関数がなくてもエラーとしない
					if(!$this->_controller->Module->isOperationAction($dirName, $action)) {
						// コピー対象外
						$exemptModules[$moduleId] = $this->_controller->Module->loadModuleName($dirName);
					}
				}
			}
			if(count($exemptModules)) {
				$echoStr .= '<div class="align-right"><a class="pages-menu-edit-confirm-textarea" href="#" onclick="$(\'#pages-menu-edit-confirm-textarea\').toggle();return false;">'.
					__d('page','Exempt from the Operation')
				.'</a></div><textarea id="pages-menu-edit-confirm-textarea" readonly="readonly">'.implode(',', $exemptModules).'</textarea>';
			}
		}

		return $echoStr;
	}

/**
 * TempDataのキー取得
 * @param   integer   copy_page_id	コピー元
 * @param   integer   page_id		コピー先
 * @return  void
 * @since   v 3.0.0.0
 */
	public function getOperationKey($copyPageId, $movePageId) {
		return 'page_menu.percent['.$this->_controller->Session->id().']['.$copyPageId.']['.$movePageId.']';
	}

/**
 * ページのコピー,移動処理
 * @param   string       $action paste or shortcut or move
 * @param   boolean      $isConfirm
 * @param   integer      $copyPageId		コピー元
 * @param   integer      $movePageId		コピー先
 * @param   string       $position inner top or bottom 挿入位置
 * @return  boolean false(バリデータ以外のエラーの場合、リダイレクトして終了) or 確認メッセージ or array ($copyPageIdArr, copy page lists, new page lists)
 * @since   v 3.0.0.0
 */
	public function operatePage($action, $isConfirm, $copyPageId, $movePageId, $position = 'bottom') {
		//// $user = $this->_controller->Auth->user();
		$insPages = array();
		$copyPages = array();
		//
		// データ取得
		//
		$results = $this->getOperationPage($copyPageId, $movePageId, $position);
		if($results === false) {
			$this->_controller->response->statusCode('404');
			$this->_controller->flash(__('Page not found.'), '');
			return false;
		}
		list($copyPage, $movePage, $copyParentPage, $moveParentPage, $moveRoomName, $copyPageIdArr,
				$copyRoomIdArr, $childCopyPages, $childMovePages, $lastCopyPage, $lastMovePage) = $results;
		$insertDisplaySequence = $lastMovePage['Page']['display_sequence'];
		$copyPages[] = $copyPage;
		if(count($childCopyPages) > 0) {
			$copyPages = array_merge ( $copyPages, $childCopyPages );
		}

		if($copyPage['Page']['root_id'] != $movePage['Page']['root_id'] || $copyPage['Page']['lang'] != $movePage['Page']['lang']) {
			$isDiffNode = true;
		} else {
			$isDiffNode = false;
		}
		if($copyPage['Page']['thread_num'] == 1 && $movePage['Page']['thread_num'] == 1 &&
				$copyPage['Page']['space_type'] == $movePage['Page']['space_type'] &&
				$copyPage['Page']['lang'] == $movePage['Page']['lang'] &&
				$position != 'inner') {
			$isSameTopNode = true;
		} else {
			$isSameTopNode = false;
		}

		// 権限チェック
		$bufCopyParentPage = ($copyPage['Page']['thread_num'] > 1) ? $copyParentPage : null;
		if(!$this->validatorPage($this->_controller->request, $copyPage, $bufCopyParentPage)) {
			return false;
		}

		// 表示順変更権限チェック
		if(!$this->validatorMovePage($copyPage, $movePage, $position, $copyParentPage)) {
			return false;
		}

		// ブロック情報取得
		$userId = $this->_controller->Auth->user('id');
		$blocks = $this->_controller->Block->findByPageIds($copyPageIdArr, $userId, '');

		if(!$isConfirm) {
			$confirm = $this->showConfirm($action, $position, $copyPage, $movePage, $moveRoomName, $copyParentPage, $moveParentPage, $copyRoomIdArr, $blocks);
			if($confirm != '') {
				echo $confirm;
				$this->_controller->render(false, 'ajax');
				return true;
			}
		}

		// コピー先権限チェック
		if(isset($movePage['Page'])) {
			$bufMoveParentPage = ($position != 'inner') ? $moveParentPage : null;
			if($movePage['Page']['thread_num'] <= 1) {
				if($position != 'inner' && $movePage['Page']['space_type'] != NC_SPACE_TYPE_GROUP) {
					// パブリック、マイポータル、マイページ直下の上下への操作はエラーとする
					$this->_controller->response->statusCode('403');
					$this->_controller->flash(__('Forbidden permission to access the page.'), '');
					return false;
				}
			} else if(!$this->checkAuth($movePage, $bufMoveParentPage)) {
				$this->_controller->response->statusCode('403');
				$this->_controller->flash(__('Forbidden permission to access the page.'), '');
				return false;
			}

			// 親ページをコピーし、それを子ページにペースト、ショートカット作成、移動を行うとエラーとする
			if(in_array($movePage['Page']['id'], $copyPageIdArr) && $movePage['Page']['id'] != $copyPageIdArr[0]) {
				$this->_controller->Page->validationErrors['page_menu_errors'][0] = __d('page', 'You can\'t operate to the lower page of the copy page.');
				return false;
			}
			if($action == 'move' && $movePage['Page']['id'] == $copyPageIdArr[0]) {
				// 移動元と移動先が同じ
				return false;
			}
		}

		//
		// ページデータ挿入
		//
		$insPage['Page'] = $copyPage['Page'];
		$insPage['Page']['display_reverse_permalink'] = null;

		$spaceType = $movePage['Page']['space_type'];
		if($position == 'inner') {
			$parentId = $movePage['Page']['id'];
			$threadNum = $movePage['Page']['thread_num'] + 1;
			$displaySequence = $insertDisplaySequence + 1;
			$roomId = $movePage['Page']['room_id'];
			$rootId = $movePage['Page']['root_id'];
			if($movePage['Page']['thread_num'] <= 1 && $spaceType != NC_SPACE_TYPE_PRIVATE) {
				$lang = Configure::read(NC_CONFIG_KEY.'.'.'language');
			} else {
				$lang = $movePage['Page']['lang'];
			}
			$chkDisplayPage = $movePage;
		} else if($isSameTopNode) {
			$parentId = $copyPage['Page']['parent_id'];
			$threadNum = $copyPage['Page']['thread_num'];
			$displaySequence = ($position == 'bottom') ? $movePage['Page']['display_sequence'] + 1 : $movePage['Page']['display_sequence'];
			$roomId = $copyPage['Page']['room_id'];
			$rootId = $copyPage['Page']['root_id'];
			$lang = $copyPage['Page']['lang'];
			$chkDisplayPage = $copyPage;
		} else if($position == 'bottom') {
			$parentId = $movePage['Page']['parent_id'];
			$threadNum = $movePage['Page']['thread_num'];
			$displaySequence = $insertDisplaySequence + 1;
			$roomId = $moveParentPage['Page']['room_id'];
			$rootId = $moveParentPage['Page']['root_id'];
			$lang = $movePage['Page']['lang'];
			$chkDisplayPage = $moveParentPage;
		} else {
			// top
			$parentId = $movePage['Page']['parent_id'];
			$threadNum = $movePage['Page']['thread_num'];
			$displaySequence = $movePage['Page']['display_sequence'];
			$roomId = $moveParentPage['Page']['room_id'];
			$rootId = $moveParentPage['Page']['root_id'];
			$lang = $movePage['Page']['lang'];
			$chkDisplayPage = $moveParentPage;
		}
		if($copyPage['Page']['id'] == $copyPage['Page']['room_id']) {
			$roomId = $copyPage['Page']['room_id'];
		}

		// 固定リンク、ページ名称設定
		$pageName = $copyPage['Page']['page_name'];
		$prePermalink = $this->_controller->Page->getMovePermalink($copyPage, $moveParentPage);
		if($action == 'paste' || $action == 'shortcut') {
			$id = 0;
		} else {
			$id = $copyPage['Page']['id'];
		}
		list($renameCount, $permalink) = $this->renamePermalink($id, $prePermalink, $movePage['Page']['space_type'], $lang);
		if($renameCount > 0 || $copyPage['Page']['permalink'] == '') {
			if($renameCount == 0 && $copyPage['Page']['permalink'] == '') {
				$renameCount = '';
			}
			$pageName = preg_replace('/^\[copy[0-9]+\](.*)/', "$1", $pageName);
			$pageName = __d('pages', '[copy%s]%s', $renameCount, $pageName) ;
		}

		// 登録処理
		$childsUpdateType = ($action == 'move') ? 'update_once' : 'update_all';
		$currentFieldList = array();
		if($pageName != $copyPage['Page']['page_name']) {
			$currentFieldList[] = 'page_name';
			$insPage['Page']['page_name'] = $pageName;
		}
		if($permalink != $copyPage['Page']['permalink']) {
			$currentFieldList[] = 'permalink';
			$insPage['Page']['permalink'] = $permalink;
			$childsUpdateType = 'update_all';
		}
		if($parentId != $copyPage['Page']['parent_id']) {
			$currentFieldList[] = 'parent_id';
			$insPage['Page']['parent_id'] = $parentId;
		}
		if($lang != $copyPage['Page']['lang']) {
			$currentFieldList[] = 'lang';
			$insPage['Page']['lang'] = $lang;
		}

		// 「下位にも適用」のFrom公開日付を１つ上の階層のものしかみていない。
		// このため、２つ以上、上位階層で「下位にも適用」でFrom公開日付を設定されていると
		// From公開日付が設定されないが、上位階層を表示すれば自動的に公開にはなるため、
		// 更新は行わない。
		if($moveParentPage['Page']['thread_num'] > 1 && $moveParentPage['Page']['display_flag'] == _OFF &&
				$movePage['Page']['display_flag'] == _ON) {
			$currentFieldList[] = 'display_flag';
			$insPage['Page']['display_flag'] = _OFF;
			$childsUpdateType = 'update_all';
		}

		if((!empty($moveParentPage['Page']['display_from_date']) && $moveParentPage['Page']['display_apply_subpage'] == _ON) &&
				!empty($movePage['Page']['display_from_date']) &&
				strtotime($movePage['Page']['display_from_date']) < strtotime($moveParentPage['Page']['display_from_date'])) {
			$currentFieldList[] = 'display_from_date';
			$insPage['Page']['display_from_date'] = $moveParentPage['Page']['display_from_date'];
			$childsUpdateType = 'update_all';
		}
		if(!empty($moveParentPage['Page']['display_to_date']) &&
				(empty($movePage['Page']['display_to_date']) ||
						strtotime($movePage['Page']['display_to_date']) > strtotime($moveParentPage['Page']['display_to_date']))) {
			$currentFieldList[] = 'display_to_date';
			$insPage['Page']['display_to_date'] =
			$childsUpdateType = 'update_all';
		}

		if($childsUpdateType == 'update_once') {
			$fields = array();
			if($displaySequence != $copyPage['Page']['display_sequence']) {
				$fields['Page.display_sequence'] = 'Page.display_sequence+('.($displaySequence - $copyPage['Page']['display_sequence']).')';
				$insPage['Page']['display_sequence'] = $displaySequence;
			}
			if($threadNum != $copyPage['Page']['thread_num']) {
				$fields['Page.thread_num'] = 'Page.thread_num+('.($threadNum - $copyPage['Page']['thread_num']).')';
				$insPage['Page']['thread_num'] = $threadNum;
			}
			if($rootId != $copyPage['Page']['root_id']) {
				$fields['Page.root_id'] = $rootId;
				$insPage['Page']['root_id'] = $rootId;
			}
			if($spaceType != $copyPage['Page']['space_type']) {
				$fields['Page.space_type'] = $spaceType;
				$insPage['Page']['space_type'] = $spaceType;
			}
			if($roomId != $copyPage['Page']['room_id']) {
				$fields['Page.room_id'] = $roomId;
				$insPage['Page']['room_id'] = $roomId;
			}

			list($insPage, $fieldChildList) = $this->setDisplay($insPage, $chkDisplayPage);
			foreach($fieldChildList as $fieldChild) {
				$fields['Page.'.$fieldChild] = $insPage['Page'][$fieldChild];
			}

			if($movePage['Page']['thread_num'] > 1 && $lang != $copyPage['Page']['lang']) {
				$fields['Page.lang'] = $lang;
			}
			$errorMes = "Failed to update the database, (%s).";
		} else {
			$sumDisplaySequence = 0;
			if($displaySequence != $copyPage['Page']['display_sequence']) {
				$currentFieldList[] = 'display_sequence';
				$insPage['Page']['display_sequence'] = $displaySequence;
				$sumDisplaySequence = $displaySequence - $copyPage['Page']['display_sequence'];
			}
			$sumThreadNum = 0;
			if($threadNum != $copyPage['Page']['thread_num']) {
				$currentFieldList[] = 'thread_num';
				$insPage['Page']['thread_num'] = $threadNum;
				$sumThreadNum = $threadNum - $copyPage['Page']['thread_num'];
			}
			if($rootId != $copyPage['Page']['root_id']) {
				$currentFieldList[] = 'root_id';
				$insPage['Page']['root_id'] = $rootId;
			}
			if($spaceType != $copyPage['Page']['space_type']) {
				$currentFieldList[] = 'space_type';
				$insPage['Page']['space_type'] = $spaceType;
			}
			if($roomId != $copyPage['Page']['room_id']) {
				$currentFieldList[] = 'room_id';
				$insPage['Page']['room_id'] = $roomId;
			}

			list($insPage, $currentFieldList) = $this->setDisplay($insPage, $chkDisplayPage, $currentFieldList);

			if($lang != $copyPage['Page']['lang']) {
				$currentFieldList[] = 'lang';
				$insPage['Page']['lang'] = $lang;
			}

			$errorMes = "Failed to register the database, (%s).";
		}

		$insPages[] = $insPage;

		// 子ページエラーチェック
		if($childsUpdateType == 'update_all' && count($childCopyPages) > 0) {
			$appendField = array(
				'display_sequence' => $sumDisplaySequence,
				'thread_num' => $sumThreadNum,
				'root_id' => $rootId,
				'space_type' => $spaceType,
				'room_id' => $roomId,
				'lang' => $lang
			);
			if($movePage['Page']['space_type'] == NC_SPACE_TYPE_GROUP && $movePage['Page']['thread_num'] == 1) {
				unset($appendField['lang']);
				$appendField['display_sequence'] = 0;
				$appendField['permalink'] = $insPage['Page']['permalink'];
			}
			$retChilds = $this->childsValidateErrors($action, $childCopyPages, $insPage, $appendField);
			if(!$retChilds) {
				// 子ページエラーメッセージ
				// 親と子とのエラーメッセージの差異はなし
				return false;
			}
		}

		// カレントページ更新
		$insPageIdArr = array();
		$insRoomIdArr = array();
		if($action == 'move') {
			$this->_controller->Page->autoConvert = false;
		}
		if(count($currentFieldList) > 0) {
			if($action == 'paste' || $action == 'shortcut') {
				unset($insPage['Page']['id']);	// pageをinsertするため
				$insPage['Page']['show_count'] = 0;
				$currentFieldList = null;
			}
			$this->_controller->Page->create();
			if(!$this->_controller->Page->save($insPage, true, $currentFieldList)) {
				throw new InternalErrorException(__($errorMes, 'pages'));
			}
			$insPage['Page']['id'] = $this->_controller->Page->id;
			$insPages[0] = $insPage;	// 再セット
			if($action == 'paste' || $action == 'shortcut') {
				$insPageIdArr[$this->_controller->Page->id] = $this->_controller->Page->id;
				if($copyPage['Page']['id'] == $copyPage['Page']['room_id']) {
					$insNewRoomId = $this->_controller->Page->id;
					$insRoomIdArr[$insNewRoomId] = $insNewRoomId;
				}
			}
		}

		// 子ページ更新
		if(isset($fields) && count($fields) > 0) {
			if($isSameTopNode) {
				$conditions = array(
					'Page.id' => $copyPage['Page']['id']
				);
			} else {
				$conditions = array(
					'Page.id' => $copyPageIdArr
				);
			}
			if(!$this->_controller->Page->updateAll($fields, $conditions)) {
				throw new InternalErrorException(__($errorMes, 'pages'));
			}
		} else if(isset($retChilds) && is_array($retChilds)) {
			$newChildPages = $this->childsUpdate($action, $retChilds[0], $retChilds[1], $copyPage['Page']['id'], $insPage['Page']['id']);
			if (!$newChildPages) {
				throw new InternalErrorException(__($errorMes, 'pages'));
			}

			$insPages = array_merge ( $insPages, $newChildPages );
			if($action == 'paste' || $action == 'shortcut') {
				$insParentIdArr = array();
				foreach($newChildPages as $index =>$new_child_page) {
					$insPageIdArr[$new_child_page['Page']['id']] = $new_child_page['Page']['id'];
					$insParentIdArr[$new_child_page['Page']['id']] = $new_child_page['Page']['parent_id'];
					if($childCopyPages[$index]['Page']['id'] == $childCopyPages[$index]['Page']['room_id']) {
						$insRoomIdArr[$new_child_page['Page']['id']] = $new_child_page['Page']['id'];
					}
				}
			}
		}

		/**
		 * 移動先、移動元を更新
		 * 移動するfrom-Toを計算し、一度のSQLでdisplay_sequenceの更新処理を行っている
		 */
		if($isSameTopNode) {
			// コミュニティの表示順変更
			$conditions = array(
				'Page.position_flag' => _ON,
				'Page.thread_num' => 1,
				'Page.space_type' => $copyPage['Page']['space_type'],
				'Page.lang' => ''
			);
			if($action == 'paste' || $action == 'shortcut') {
				// インクリメント
				$updDisplaySequence = 1;
				$operation = ($position == 'top') ? '>=' : '>';
				$conditions["Page.display_sequence ".$operation] = $movePage['Page']['display_sequence'];
				$conditions['not'] = array('Page.id' => $insPageIdArr);
			} else if($copyPage['Page']['display_sequence'] < $movePage['Page']['display_sequence']) {
				// 上から下へ デクリメント
				$updDisplaySequence = -1;
				$conditions["Page.display_sequence >"] = $lastCopyPage['Page']['display_sequence'];
				if($position == 'bottom') {
					$conditions["Page.display_sequence <="] = $insPage['Page']['display_sequence'];

					$next_conditions = array(
						'Page.position_flag' => _ON,
						'Page.thread_num' => 1,
						'Page.lang' => '',
						'Page.display_sequence' => intval($movePage['Page']['display_sequence']) + 1,
						'not' => array('Page.id' => $insPage['Page']['id'])
					);
					$next_page = $this->_controller->Page->find('first', array('fields' => array('Page.id'), 'conditions' => $next_conditions));
					if(isset($next_page['Page'])) {
						// 移動先の１つ下のページを取得し、そのページ以外
						$conditions['not'] = array('Page.id' => $next_page['Page']['id']);
					}
				} else {
					// top
					$conditions["Page.display_sequence <="] = $movePage['Page']['display_sequence'];
					$conditions['not'] = array('Page.id' => $movePage['Page']['id']);
				}
			} else {
				// 下から上へ インクリメント
				$updDisplaySequence = 1;
				$operation = ($position == 'top') ? '>=' : '>';
				$conditions["Page.display_sequence ".$operation] = $movePage['Page']['display_sequence'];
				$conditions["Page.display_sequence <"] = $copyPage['Page']['display_sequence'];
				$conditions['not'] = array('Page.id' => $copyPage['Page']['id']);
			}
			$fields = array('Page.display_sequence'=>'Page.display_sequence+('.$updDisplaySequence.')');
			if(!$this->_controller->Page->updateAll($fields, $conditions)) {
				throw new InternalErrorException(__('Failed to update the database, (%s).', 'pages'));
			}
		} else {
			$conditions = array(
				'Page.position_flag' => _ON,
				'Page.thread_num >' => 1,
				'Page.lang' => $movePage['Page']['lang'],
				'Page.root_id' => $movePage['Page']['root_id']
			);
			if($action == 'paste' || $action == 'shortcut') {
				$updDisplaySequence = count($childCopyPages) + 1;
				if($position == 'top') {
					$conditions["Page.display_sequence >="] = $movePage['Page']['display_sequence'];
				} else {
					$conditions["Page.display_sequence >"] = $insertDisplaySequence;
				}
				$conditions['not'] = array('Page.id' => $insPageIdArr);
			} else if($isDiffNode) {
				// 別ルート
				$updDisplaySequence = count($childCopyPages) + 1;
				$conditions['not'] = array('Page.id' => $copyPageIdArr);

				$preFields = array('Page.display_sequence'=>'Page.display_sequence+(-'.$updDisplaySequence.')');
				$preConditions = $conditions;
				$preConditions["Page.display_sequence >"] = $lastCopyPage['Page']['display_sequence'];
				$preConditions["Page.lang"] = $copyPage['Page']['lang'];
				$preConditions["Page.root_id"] = $copyPage['Page']['root_id'];
				if(!$this->_controller->Page->updateAll($preFields, $preConditions)) {
					throw new InternalErrorException(__('Failed to update the database, (%s).', 'pages'));
				}
				if($position == 'bottom' || $position == 'inner') {
					$conditions["Page.display_sequence >="] = $displaySequence;
				} else {
					// top
					$conditions["Page.display_sequence >="] = $movePage['Page']['display_sequence'];
				}
			} else if($copyPage['Page']['display_sequence'] < $movePage['Page']['display_sequence']) {
				// 上から下へ デクリメント
				$updDisplaySequence = -(count($childCopyPages) + 1);
				$conditions["Page.display_sequence >"] = $lastCopyPage['Page']['display_sequence'];

				if($position == 'bottom' || $position == 'inner') {
					$conditions["Page.display_sequence <="] = $insPage['Page']['display_sequence'] + count($childCopyPages);;

					$next_conditions = array(
						'Page.position_flag' => _ON,
						'Page.lang' => $movePage['Page']['lang'],
						'Page.root_id' => $movePage['Page']['root_id'],
						'Page.display_sequence >=' => intval($insertDisplaySequence) + 1,
						'Page.display_sequence <' => intval($insPage['Page']['display_sequence']) + count($childCopyPages) + 2,
						'not' => array('Page.id' => $copyPageIdArr)
					);
					$next_page = $this->_controller->Page->find('list', array('fields' => array('Page.id'), 'conditions' => $next_conditions));
					if(is_array($next_page) && count($next_page) > 0) {
						// 移動先の下のページを取得し、そのページ以外
						$conditions['not'] = array('Page.id' => $next_page);
					}
				} else {
					// top
					$conditions["Page.display_sequence <="] = $insPage['Page']['display_sequence'] + count($childCopyPages);
					$next_conditions = array(
						'Page.position_flag' => _ON,
						'Page.lang' => $movePage['Page']['lang'],
						'Page.root_id' => $movePage['Page']['root_id'],
						'Page.display_sequence >=' => $insPage['Page']['display_sequence'],
						'Page.display_sequence <' => intval($insPage['Page']['display_sequence']) + count($childCopyPages) + 1,
						'not' => array('Page.id' => $copyPageIdArr)
					);
					$next_page = $this->_controller->Page->find('list', array('fields' => array('Page.id'), 'conditions' => $next_conditions));
					if(is_array($next_page) && count($next_page) > 0) {
						// 移動先の下のページを取得し、そのページ以外
						$conditions['not'] = array('Page.id' => $next_page);
					}
				}
			} else {
				// 下から上へ インクリメント
				$updDisplaySequence = count($childCopyPages) + 1;
				if($position == 'top') {
					$conditions["Page.display_sequence >="] = $movePage['Page']['display_sequence'];
				} else {
					$conditions["Page.display_sequence >"] = $insertDisplaySequence;
				}
				$conditions["Page.display_sequence <"] = $copyPage['Page']['display_sequence'];
				$conditions['not'] = array('Page.id' => $copyPageIdArr);
			}
			$fields = array('Page.display_sequence'=>'Page.display_sequence+('.$updDisplaySequence.')');
			if(!$this->_controller->Page->updateAll($fields, $conditions)) {
				throw new InternalErrorException(__('Failed to update the database, (%s).', 'pages'));
			}
		}

		// 異なるルームへの操作で移動元にルームが存在していれば権限の割り当てを解除する
		if($action == 'move' && $copyParentPage['Page']['room_id'] != $moveParentPage['Page']['room_id'] && count($copyRoomIdArr) > 0) {
			foreach($copyRoomIdArr as $copy_room_id) {
				if(!$this->_controller->PageMenuUserLink->deallocation($copy_room_id, $copyPageIdArr, $moveParentPage)) {
					throw new InternalErrorException(__('Failed to delete the database, (%s).', 'page_user_links'));
				}
			}
		} else if(($action == 'shortcut' || $action == 'paste') && $copyParentPage['Page']['room_id'] == $moveParentPage['Page']['room_id'] &&
				 count($copyRoomIdArr) > 0) {
			// Page room_id更新
			foreach($insPageIdArr as $bufInsPageId) {
				if(isset($insRoomIdArr[$bufInsPageId])) {
					$updRoomId = $insRoomIdArr[$bufInsPageId];
				} else {
					$updRoomId = null;
					$bufParentId = $insParentIdArr[$bufInsPageId];
					while(1) {
						if(isset($insRoomIdArr[$bufParentId])) {
							$updRoomId = $insRoomIdArr[$bufParentId];
							break;
						}
						if(!isset($insParentIdArr[$bufParentId])) {
							break;
						}
						$bufParentId = $insParentIdArr[$bufParentId];
					}
					if(!isset($updRoomId)) {
						continue;
					}
				}
				$this->_controller->Page->id = $bufInsPageId;
				if(!$this->_controller->Page->saveField('room_id', $updRoomId)) {
					throw new InternalErrorException(__('Failed to update the database, (%s).', 'pages'));
				}
			}

			// 同じルーム内でのペースト、ショートカット作成は、権限を引き継ぐ。
			if(!$this->_controller->PageMenuUserLink->copyPageUserLink($insRoomIdArr, $copyRoomIdArr)) {
				throw new InternalErrorException(__('Failed to register the database, (%s).', 'page_user_links'));
			}

			// コミュニティ直下のショートカット作成、ペーストでコミュニティ関連を作成
			if($movePage['Page']['space_type'] == NC_SPACE_TYPE_GROUP && $movePage['Page']['thread_num'] == 1) {
				if(!$this->_controller->PageMenuCommunity->copyCommunity($insNewRoomId, $copyRoomIdArr[0], $renameCount)) {
					throw new InternalErrorException(__('Failed to register the database, (%s).', 'communities'));
				}
				// root_id更新
				$fields = array('Page.root_id'=>$insNewRoomId);
				$conditions = array(
					'Page.room_id' => $insNewRoomId
				);
				if(!$this->_controller->Page->updateAll($fields, $conditions)) {
					throw new InternalErrorException(__($errorMes, 'pages'));
				}
			}
		}

		return array($copyPageIdArr, $copyPages, $insPages);
	}

/**
 * ブロックのコピー,ショートカット、移動処理
 * @param   string       $action paste or shortcut
 * @param   string       TempDataテーブルハッシュキー $hashKey
 * @param   integer      $userId
 * @param   integer      $copyPageIdArr		コピー元ID配列
 * @param   Model Pages  $copyPages	コピー元
 * @param   Model Pages  $insPages		コピー先
 * @param   integer      $defaultShortcutType ペーストならnull ショートカット 0 権限付与つきショートカット 1
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function operateBlock($action, $hashKey, $userId, $copyPageIdArr, $copyPages, $insPages, $defaultShortcutType = null) {
		if($action == 'move' && $copyPages[0]['Page']['room_id'] == $insPages[0]['Page']['room_id']) {
			// 移動で同一ルーム内の移動であればblockテーブルは更新しない
			$this->_controller->TempData->destroy($hashKey);
			return true;
		}
		$blocks = $this->_controller->Block->findByPageIds($copyPageIdArr, $userId, "");
		$total = count($blocks);
		if($total > 0) {
			$percent = 0;
			//$current = 0;
			//$total_page = count($copyPageIdArr);
			$pagesIndexs = array();
			foreach($copyPages as $key => $copyPage) {
				$pagesIndexs[$copyPage['Page']['id']] = $key;
			}

			$count = 0;
			$prePageId = 0;
			$rootIdArr = array();
			$parentIdArr = array();
			$contentIdArr = array();
			foreach($blocks as $buf_block) {
				$block = array('Block' => $buf_block['Block']);
				$module = isset($buf_block['Module']) ? array('Module' => $buf_block['Module']) : null;
				$content = array('Content' => $buf_block['Content']);

				$currentPage = $copyPages[$pagesIndexs[$block['Block']['page_id']]];
				$insPage = $insPages[$pagesIndexs[$block['Block']['page_id']]];

				$count++;
				//if($block['Block']['page_id'] != $prePageId) {
				//	$prePageId = $block['Block']['page_id'];
				//	$current++;
				//}
				if($block['Block']['title'] == NC_DEFAULT_BLOCK_TITLE) {
					$title = $content['Content']['title'];
				} else {
					$title = $block['Block']['title'];
				}
				$title .= ' - ' . $currentPage['Page']['page_name'];
				$percent = floor((($count - 1) / $total)*100);
				$data = array(
					'percent' => $percent,
					'title' => $title,
					'total' => $total,
					'current' => $count
				);
				$this->_controller->TempData->write($hashKey, serialize($data));

				if($block['Block']['thread_num'] == 0) {
					$newRootId = null;
					$newParentId = 0;
				} else {
					$newRootId = $rootIdArr[$block['Block']['root_id']];
					$newParentId = $parentIdArr[$block['Block']['parent_id']];
				}

				if($action != 'move' && isset($contentIdArr[$content['Content']['id']])) {
					// 既にpasteしてあるコンテンツのショートカットならば、ショートカットとして貼り付ける。
					$shortcutType = _OFF;
					$content['Content']['id'] = $contentIdArr[$content['Content']['id']];
					if($content['Content']['shortcut_type'] == NC_SHORTCUT_TYPE_OFF) {
						$content['Content']['master_id'] = $content['Content']['id'];
					}
				} else {
					$shortcutType = $defaultShortcutType;
				}

				$ins_ret = $this->_controller->BlockOperation->addBlock($action, $currentPage, $module, $block, $content, $shortcutType, $insPage, $newRootId, $newParentId);

				if($ins_ret === false) {
					$this->_controller->TempData->destroy($hashKey);
					return false;
				}
				list($ret, $insBlock, $insContent) = $ins_ret;

				$rootIdArr[$block['Block']['id']] = $insBlock['Block']['root_id'];
				$parentIdArr[$block['Block']['id']] = $insBlock['Block']['id'];
				$contentIdArr[$content['Content']['id']] = $insContent['Content']['id'];
//sleep(4);
			}

			// TODO:uploadsテーブルの更新処理


			$this->_controller->TempData->destroy($hashKey);
		}
		return true;
	}

/**
 * PageモデルのvalidationErrorsからメッセージ生成
 * @param   void
 * @return  string エラーメッセージ
 * @since   v 3.0.0.0
 */
	public function getErrorStr() {
		$error = '';
		foreach($this->_controller->Page->validationErrors as $field => $errors) {
			if($field == 'permalink') {
				$error .= __('Permalink'). ':';
			} else if($field == 'page_name') {
				$error .= __('Page name'). ':';
			}
			$error .= $errors[0]."\n";	// 最初の１つめ
		}
		echo $error;
	}

/**
 * 公開日、非公開日等を親のPageをみてセットしなおす
 * @param   Model Page    $page カレントページ
 * @param   Model Page    $parentPage 親ページ
 * @param   array $fieldChildList
 * @return  string エラーメッセージ
 * @since   v 3.0.0.0
 */
	public function setDisplay($page, $parentPage, $fieldChildList = array()) {
		$displayFlag = $parentPage['Page']['display_flag'];
		$displayFromDate = $parentPage['Page']['display_from_date'];
		$displayApplySubpage = $parentPage['Page']['display_apply_subpage'];
		$displayToDate = $parentPage['Page']['display_to_date'];

		if($displayFlag == _OFF && $page['Page']['display_flag'] == _ON) {
			$fieldChildList[] = 'display_flag';
			$page['Page']['display_flag'] = $displayFlag;
		}
		if(!empty($displayFromDate) && $displayApplySubpage == _ON) {
			$fieldChildList[] = 'display_from_date';
			$page['Page']['display_from_date'] = $displayFromDate;
		}
		if(!empty($displayFromDate) && !empty($page['Page']['display_from_date']) &&
				strtotime($page['Page']['display_from_date']) < strtotime($displayFromDate)) {
			$fieldChildList[] = 'display_from_date';
			$page['Page']['display_from_date'] = $displayFromDate;
		}
		if(!empty($displayToDate) && (empty($page['Page']['display_to_date']) ||
				strtotime($page['Page']['display_to_date']) > strtotime($displayToDate))) {
			$fieldChildList[] = 'display_to_date';
			$page['Page']['display_to_date'] = $displayToDate;
		}
		return array($page, $fieldChildList);
	}

/**
 * 同じ階層に同名の固定リンクあればリネームして返す
 *
 * @param   integr      $id
 * @param   string      $permalink
 * @param   integr      $spaceType
 * @param   string      $lang
 * @return  array(integer count, string $permalink)
 * @since   v 3.0.0.0
 */
	protected function renamePermalink($id, $permalink, $spaceType, $lang) {
		$prePermalink = $permalink;
		$prePermalinkArr = explode('/', $prePermalink);
		$preCurrentPermalink = preg_replace('/^-copy[0-9]+-(.*)/', "$1", array_pop($prePermalinkArr));
		$preParentPermalink = implode($prePermalinkArr, '/');
		if($preParentPermalink != '') {
			$preParentPermalink .= '/';
		}
		$count = 0;
		while(1) {
			$chkConditions = array(
				'Page.position_flag' => _ON,
				'Page.lang' => $lang,
				'Page.space_type' => $spaceType,
				'Page.permalink' => $permalink,
				'Page.id !=' => $id
			);
			$chkPage = $this->_controller->Page->find('first', array('fields' => array('Page.id'), 'conditions' => $chkConditions));
			if(isset($chkPage['Page'])) {
				// 同名の固定リンクあり
				$count ++ ;
				$permalink = $preParentPermalink.__d('pages', '-copy%s-%s', $count, $preCurrentPermalink) ;


				//$permalink = $preParentPermalink.__d('pages', 'copy_%s_%s', $count, $preCurrentPermalink) ;
			} else {
				break;
			}
		}
		return array($count, $permalink);
	}
}