<?php
/**
 * PageMenuComponentクラス
 *
 * <pre>
 * ページ操作用コンポーネント
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       App.Plugin.Controller
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
 * @param  Page Model  $parent_page
 * @return mixed $admin_hierarchy or false + flashメッセージ
 * @since   v 3.0.0.0
 */
	public function validatorPage($request, $page = null, $parent_page = null) {
		$login_user = $this->_controller->Auth->user();
		$user_id = $login_user['id'];

		if($request->params['action'] != 'detail' && $request->params['action'] != 'participant'  && $request->params['action'] != 'participant_cancel') {
			if(!$request->is('post')) {
				$this->_controller->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenu.validatorPage.001', '400');
				return false;
			}
		}
		if($request->params['action'] == 'detail' || $request->params['action'] == 'delete' ||
				$request->params['action'] == 'participant' || $request->params['action'] == 'participant_detail' ||
				$request->params['action'] == 'participant_cancel' ||
				$request->params['action'] == 'copy' || $request->params['action'] == 'paste' ||
				$request->params['action'] == 'move' || $request->params['action'] == 'shortcut') {
			if(!$request->is('ajax')) {
				$this->_controller->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenu.validatorPage.002', '400');
				return false;
			}
		}

		$admin_hierarchy = $this->_controller->ModuleSystemLink->findHierarchy(Inflector::camelize($request->params['plugin']), $login_user['authority_id']);
		if($admin_hierarchy <= NC_AUTH_GENERAL) {
			$this->_controller->flash(__('Forbidden permission to access the page.'), null, 'PageMenu.validatorPage.003', '403');
			return false;
		}

		if(is_null($page)) {
			return $admin_hierarchy;
		}
		if(!isset($page['Page'])) {
			$this->_controller->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenu.validatorPage.004', '400');
			return false;
		}

		/**
		 * 管理者：コミュニティーの表示順変更、コミュニティ内において、自分が主坦でなくても追加・編集・削除・参加者修正、モジュール選択を許す。
		 * 主坦：コミュニティーの表示順変更(参加コミュニティのみ) TODO:未テスト。デフォルト参加ルームの作成。
		 * モデレータ：コミュニティーの作成、編集、削除。（公開コミュニティーの作成。モデレータのHierarchyを２つに分離する）
		 * 一般：主坦権限のルームへのページ追加、編集、削除
		 * ゲスト：ページメニューをみるだけ。
		 */
		$is_auth_ok = false;
		if($admin_hierarchy >= NC_AUTH_MIN_ADMIN &&
				$page['Page']['space_type'] == NC_SPACE_TYPE_GROUP) {
			$is_auth_ok = true;
		}

		$chk_page = isset($parent_page) ? $parent_page : $page;
		if(!$is_auth_ok && !$this->_controller->CheckAuth->chkAuth($chk_page['Authority']['hierarchy'], NC_AUTH_CHIEF)) {
			$this->_controller->flash(__('Forbidden permission to access the page.'), null, 'PageMenu.validatorPage.005', '403');
			return false;
		}

		if($page['Page']['position_flag'] != _ON) {
			$this->_controller->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenu.validatorPage.006', '400');
			return false;
		}

		if(!$this->validatorPageDetail($request, $page, $parent_page, $admin_hierarchy)) {
			return false;
		}

		return $admin_hierarchy;
	}

/**
 * ページ追加・削除、編集、表示順変更等バリデータ処理(詳細)
 *
 * @param  CakeRequest $request
 * @param  Page Model  $page
 * @param  Page Model  $parent_page
 * @param  integer     $admin_hierarchy
 * @param  boolean     $is_child
 *
 * @return boolean
 * @since   v 3.0.0.0
 */
	public function validatorPageDetail($request, $page = null, $parent_page = null, $admin_hierarchy = null, $is_child = false) {
		$lang = Configure::read(NC_CONFIG_KEY.'.'.'language');

		switch($request->params['action']) {
			case 'add':
				if($page['Page']['thread_num'] == 0) {
					$this->_controller->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenu.validatorPageDetail.001', '400');
					return false;
				}
				break;
			case 'display':
				// 親がOFFならば変更を許さない。
				$parent_page = $this->_controller->Page->findById($page['Page']['parent_id']);
				if(!isset($parent_page['Page']) || $parent_page['Page']['display_flag'] != NC_DISPLAY_FLAG_ON) {
					$this->_controller->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenu.validatorPageDetail.002', '400');
					return false;
				}
				if($page['Page']['thread_num'] <= 1 && $page['Page']['space_type'] != NC_SPACE_TYPE_GROUP) {
					//コミュニティー以外のTop Nodeの編集は許さない
					$this->_controller->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenu.validatorPageDetail.003', '400');
					return false;
				}
				break;
			case 'delete':
				// 各スペースタイプのトップページは最低１つ以上ないと削除不可。
				if (!$is_child && $page['Page']['thread_num'] == 2 && $page['Page']['display_sequence'] == 1) {
					// 該当ルームのTopページの数を取得
					$conditions = array(
						'Page.room_id' => $page['Page']['room_id'],
						'Page.display_sequence' => $page['Page']['display_sequence'],
						'Page.thread_num' => $page['Page']['thread_num'],
						'Page.position_flag' => $page['Page']['position_flag']
					);
					$count = $this->_controller->Page->find('count', array('conditions' => $conditions));

					if($count == 1) {
						echo __d('page', 'Top of each page is required at least one page. <br />You can\'t delete.');
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
					if($count >= 1) {
						echo __d('page', 'If you want to delete the top page, please run after deleting all the pages of this room.<br />You can\'t delete.');
						$this->_controller->render(false, 'ajax');
						return false;
					}
				}
				// break;
			case 'edit':
				if($page['Page']['thread_num'] == 0 || ($page['Page']['thread_num'] == 1 && $page['Page']['space_type'] != NC_SPACE_TYPE_GROUP)) {
					//コミュニティーのTop Node以外の編集は許さない
					$this->_controller->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenu.validatorPageDetail.004', '400');
					return false;
				}
				/*if($page['Page']['space_type'] != NC_SPACE_TYPE_PUBLIC && $page['Page']['thread_num'] == 2 && $page['Page']['display_sequence'] == 1) {
					//パブリック以外の各ノードのTopページの編集は許さない
					$this->_controller->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenu.validatorPageDetail.006', '400');
					return false;
				}*/
				break;
			case 'chgsequence':
			case 'move':
				// ページロックしてあれば、移動不可。
				if($page['Page']['lock_authority_id'] > 0) {
					$page['Page'] = $this->_controller->Page->setPageName($page['Page']);
					echo __d('page', 'Because the [%s] page is locked, I can\'t be deleted. Please run after unlock the page.', $page['Page']['page_name']);
					$this->_controller->render(false, 'ajax');
					return false;
				}
				if($page['Page']['thread_num'] == 1) {
					if($page['Page']['space_type'] != NC_SPACE_TYPE_GROUP || $admin_hierarchy < NC_AUTH_MIN_CHIEF) {
						//コミュニティーの表示順変更は$admin_hierarchy=NC_AUTH_MIN_CHIEF以上
						$this->_controller->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenu.validatorPageDetail.005', '400');
					}
				} else if($page['Page']['thread_num'] <= 1) {
					//Top Nodeの編集は許さない
					$this->_controller->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenu.validatorPageDetail.006', '400');
					return false;
				}
				break;
			case "participant":
				if($page['Page']['space_type'] == NC_SPACE_TYPE_PRIVATE) {
					// プライベートスペースは権限を設定不可
					$this->_controller->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenu.validatorPageDetail.007', '400');
					return false;
				}
				if($page['Page']['thread_num'] == 0) {
					$this->_controller->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenu.validatorPageDetail.008', '400');
					return false;
				}
		}
		return true;
	}

/**
 *  表示順変更 移動元-移動先権限チェック
 *
 * @param  CakeRequest $request
 * @param  Page Model  $page
 * @param  Page Model  $move_page
 * @param  string      $position inner or bottom or top
 * @param  Page Model  $parent_page
 * @return boolean
 * @since   v 3.0.0.0
 */
	public function validatorMovePage($request, $page, $move_page, $position, $parent_page) {
		$user_id = $this->_controller->Auth->user('id');

		// 移動先権限チェック
		if($page['Page']['thread_num'] != 1 && !$this->_controller->CheckAuth->chkAuth($parent_page['Authority']['hierarchy'], NC_AUTH_CHIEF)) {
			$this->_controller->flash(__('Forbidden permission to access the page.'), null, 'PageMenu.validatorMovePage.001', '400');
			return false;
		}

		if(($page['Page']['space_type'] != NC_SPACE_TYPE_GROUP && $page['Page']['thread_num'] == 1)
				|| $page['Page']['thread_num'] == 0 || $move_page['Page']['thread_num'] == 0 ||
				($page['Page']['space_type'] != NC_SPACE_TYPE_GROUP && $move_page['Page']['thread_num'] == 1 && $position != 'inner')) {
			// 移動元がコミュニティー以外のノード,移動先が Top Node
			$this->_controller->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenu.validatorMovePage.002', '400');
			return false;
		}

		if($position != 'inner' && $position != 'top' && $position != 'bottom') {
			$this->_controller->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenu.validatorMovePage.003', '400');
			return false;
		}

		if($move_page['Page']['thread_num'] == 2 &&
				$move_page['Page']['display_sequence'] == 1 && ($position == 'inner' || $position == 'top')) {
			// 各スペースタイプのトップページの中か上に移動しようとした。
			$this->_controller->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenu.validatorMovePage.004', '400');
			return false;
		}

		if($position != 'inner' && (($page['Page']['thread_num'] == 1 && $move_page['Page']['thread_num'] != 1) ||
				($move_page['Page']['thread_num'] == 1 && $page['Page']['thread_num'] != 1))) {
			// コミュニティーTopノードへの移動チェック
			$this->_controller->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenu.validatorMovePage.005', '400');
			return false;
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
	 * @param  Page Models  $child_pages
	 * @param  Page Model   $ins_page 親ページ
	 * @param  array        $appendField 更新カラム情報($action != 'edit'のみ)
	 * @return 成功 array($ret_pages, $ret_fieldChildList) 失敗 false エラーメッセージ データなし true
	 * @since   v 3.0.0.0
	 */
	public function childsValidateErrors($action, $child_pages, $ins_page, $appendField = null) {

		// 表示順変更の場合、「下位にも適用」のFrom公開日付を１つ上の階層のものしかみていない。
		// このため、２つ以上、上位階層で「下位にも適用」でFrom公開日付を設定されていると
		// From公開日付が設定されないが、上位階層を表示すれば自動的に公開にはなるため、
		// 更新は行わない。
		$ret_pages = array();
		$ret_fieldChildList = array();
		$display_flag = $ins_page['Page']['display_flag'];
		$display_from_date = $ins_page['Page']['display_from_date'];
		$display_to_date = $ins_page['Page']['display_to_date'];
		$display_apply_subpage = $ins_page['Page']['display_apply_subpage'];

		$current_permalink_arr = array(
			$ins_page['Page']['id'] => $ins_page['Page']['permalink']
		);
		foreach($child_pages as $child_page) {
			$fieldChildList = array('permalink');
			if($action != 'edit' && isset($appendField)) {
				$fieldChildList = array_merge($fieldChildList, array_keys($appendField));
			}
			if(count($fieldChildList) > 1) {
				$buf_appendField = $appendField;
				$child_page['Page']['display_sequence'] = $child_page['Page']['display_sequence'] + $buf_appendField['display_sequence'];
				$child_page['Page']['thread_num'] = $child_page['Page']['thread_num'] + $buf_appendField['thread_num'];
				unset($buf_appendField['display_sequence']);
				unset($buf_appendField['thread_num']);
				$child_page['Page'] = array_merge($child_page['Page'], $buf_appendField);
				unset($buf_appendField);
			}
			$permalink_arr = explode('/', $child_page['Page']['permalink']);
			if($current_permalink_arr[$child_page['Page']['parent_id']] != ''
					&& !($child_page['Page']['thread_num'] == 2 && $child_page['Page']['display_sequence'] == 1)) {
				$upd_permalink = $current_permalink_arr[$child_page['Page']['parent_id']] . '/' . $permalink_arr[count($permalink_arr)-1];
			} else {
				$upd_permalink = $permalink_arr[count($permalink_arr)-1];
			}
			$child_page['Page']['permalink'] = $upd_permalink;
			$current_permalink_arr[$child_page['Page']['id']] = $upd_permalink;

			if($display_flag == _OFF && $child_page['Page']['display_flag'] == _ON) {
				$fieldChildList[] = 'display_flag';
				$child_page['Page']['display_flag'] = $display_flag;
			}
			if(!empty($display_from_date) && $display_apply_subpage == _ON) {
				$fieldChildList[] = 'display_from_date';
				$child_page['Page']['display_from_date'] = $display_from_date;
			}
			if(!empty($display_from_date) && !empty($child_page['Page']['display_from_date']) &&
					strtotime($child_page['Page']['display_from_date']) < strtotime($display_from_date)) {
				$fieldChildList[] = 'display_from_date';
				$child_page['Page']['display_from_date'] = $display_from_date;
			}
			if(!empty($display_to_date) && (empty($child_page['Page']['display_to_date']) ||
					strtotime($child_page['Page']['display_to_date']) > strtotime($display_to_date))) {
				$fieldChildList[] = 'display_to_date';
				$child_page['Page']['display_to_date'] = $display_to_date;
			}

			$this->_controller->Page->create();
			//if($action == 'paste' || $action == 'shortcut') {
			//	unset($child_page['Page']['id']);	// pageをinsertするため
			//	//$ret = $this->_controller->Page->save($child_page);
			//} //else {
				//$ret = $this->_controller->Page->save($child_page, false, $fieldChildList);	//親でUpdate処理実行後に更新するためバリデータを実行しない。
			//}
			$this->_controller->Page->set($child_page);
			$ret = $this->_controller->Page->validates(array('fieldList' => $fieldChildList));
			//$all_errors = $this->_controller->validateErrors($this->_controller->Page);
			//if(is_array($all_errors)) {
			if(!$ret) {
				return false;
			}
			$ret_pages[] = $child_page;
			$ret_fieldChildList[] = $fieldChildList;
		}
		if(count($ret_pages) == 0) {
			return true;
		}
		return array($ret_pages, $ret_fieldChildList);
	}

/**
 *  子ページ更新処理
 *  ページ表示順変更、編集時、ページペースト、ページ移動、ページショートカットの作成、子供編集処理
 *
 * @param  string       $action edit or move or shortcut or paste
 * @param  Page Models  $child_pages
 * @param  array        $fieldChildsList
 * @param  integer      $old_parent_id
 * @param  integer      $new_parent_id
 * @return boolean false or array new page_id array
 * @since   v 3.0.0.0
 */
	public function childsUpdate($action, $child_pages, $fieldChildsList = null, $old_parent_id = null, $new_parent_id = null) {
		$page_id_arr = array();
		if($old_parent_id != $new_parent_id) {
			$parent_id_arr = array(
				$old_parent_id => $new_parent_id
			);
		}
		foreach($child_pages as $i => $child_page) {
			$fieldChildList = $fieldChildsList[$i];
			$this->_controller->Page->create();
			if(isset($parent_id_arr)) {
				if(isset($parent_id_arr[$child_page['Page']['parent_id']])) {
					$child_page['Page']['parent_id'] = $parent_id_arr[$child_page['Page']['parent_id']];
					if(!empty($fieldChildList)) {
						$fieldChildList[] = 'parent_id';
					}
				}
				/*if($child_page['Page']['parent_id'] == $old_parent_id) {
					$child_page['Page']['parent_id'] = $new_parent_id;
					if(!empty($fieldChildList)) {
						$fieldChildList[] = 'parent_id';
					}
				} */
			}
			$old_id = $child_page['Page']['id'];
			if($action == 'paste' || $action == 'shortcut') {
				unset($child_page['Page']['id']);	// pageをinsertするため
			}
			if(empty($child_page['Page']['id']) || empty($fieldChildList)) {
				$ret = $this->_controller->Page->save($child_page, false);
			} else {
				$ret = $this->_controller->Page->save($child_page, false, $fieldChildList);
			}
			$new_id = $this->_controller->Page->id;
			if(!$ret) {
				return false;
			}
			if(isset($parent_id_arr)) {
				$parent_id_arr[$old_id] = $new_id;
			}
			$page_id_arr[] = $new_id;
		}
		return $page_id_arr;
	}

/**
 * Insert Page defaults
 * @param   string     edit(編集) or inner or bottom(追加) $type
 * @param   array      $current_page
 * param   array       $current_page
 * @return  array      array($page, $parent_page)
 * @since   v 3.0.0.0
 */
	public function getDefaultPage($type, $current_page, $parent_page = null) {
		$lang = Configure::read(NC_CONFIG_KEY.'.'.'language');
		if(!isset($parent_page)) {
			$parent_page = $current_page;
		}

		$ins_page = $current_page;
		if($type == 'inner') {
			unset($ins_page['Page']['id']);
			$ins_page['Page']['parent_id'] = $current_page['Page']['id'];
			$ins_page['Page']['thread_num'] = $current_page['Page']['thread_num'] + 1;

		} else if($type == 'bottom') {
			unset($ins_page['Page']['id']);

			// hierarchy
			$ins_page['Authority']['hierarchy'] =$parent_page['Authority']['hierarchy'];
		}
		if($current_page['Page']['thread_num'] == 1) {
			$display_sequence = 1;
		} else {
			$display_sequence = $current_page['Page']['display_sequence'] + 1;
		}
		$conditions = array(
			'Page.position_flag' => _ON,
			'Page.space_type' => $current_page['Page']['space_type'],
			'Page.lang' => array('', $lang)
		);
		if($current_page['Page']['root_id'] != 0) {
			$conditions['Page.root_id'] = $current_page['Page']['root_id'];
		}
		$display_sequence_results = $this->_controller->Page->find('all', array(
			'fields' => 'Page.id, Page.parent_id, Page.display_sequence',
			'recursive' => -1,
			'conditions' => $conditions,
			'order' => 'Page.display_sequence'
		));

		$display_sequence_pages = array();
		$parent_id_arr[] = $current_page['Page']['id'];
		foreach ($display_sequence_results as $key => $val) {
			if(in_array($val['Page']['parent_id'], $parent_id_arr)) {
				$display_sequence_pages[$val['Page']['id']] = $val;
				$parent_id_arr[] = $val['Page']['id'];
				$display_sequence = $val['Page']['display_sequence'] + 1;
			}
		}

		$space_type = $ins_page['Page']['space_type'];
		$thread_num = $ins_page['Page']['thread_num'];

		$count_fields = 'MAX(Page.display_sequence) as max_number';
		$count_conditions = array(
			'Page.root_id' => $parent_page['Page']['root_id'],
			'Page.thread_num >' => 1,
			'Page.lang' => array('', $lang)
		);
		$result = $this->_controller->Page->find('first', array(
			'fields' => $count_fields,
			'recursive' => -1,
			'conditions' => $count_conditions
		));
		if(isset($result[0]['max_number'])) {
			$count = intval($result[0]['max_number']) + 1;
		} else {
			$count = 1;
		}

		if($display_sequence == 1) {
			// 各トップページ
			$permalink = '';
			if($parent_page['Page']['permalink'] != '') {
				$permalink = $parent_page['Page']['permalink'].$permalink;
			}
			if($space_type == NC_SPACE_TYPE_MYPORTAL) {
				$page_name = __("Myportal Top");
			} else if($space_type == NC_SPACE_TYPE_PRIVATE) {
				$page_name = __("Private Top");
			} else if($space_type == NC_SPACE_TYPE_GROUP) {
				$page_name = __("Community Top");
			} else {
				$page_name = __d('page', "New page");
			}
		} else {
			$page_name = __d('page', "New page");
			list($page_name, $permalink) = $this->_getPageName($page_name, $count, $parent_page);
		}

		$ins_page['Page']['display_sequence'] = $display_sequence;
		$ins_page['Page']['page_name'] = $page_name;
		$ins_page['Page']['permalink'] = $permalink;
		$ins_page['Page']['show_count'] = 0;

		$ins_page['Page']['display_flag'] = $parent_page['Page']['display_flag'];
		if(!empty($parent_page['Page']['display_from_date']) && $parent_page['Page']['display_apply_subpage'] == _ON) {
			$ins_page['Page']['display_from_date'] = $parent_page['Page']['display_from_date'];
		} else {
			$ins_page['Page']['display_from_date'] = null;
		}
		if(!empty($parent_page['Page']['display_to_date'])) {
			$ins_page['Page']['display_to_date'] = $parent_page['Page']['display_to_date'];
		} else {
			$ins_page['Page']['display_to_date'] = null;
		}

		if($space_type == NC_SPACE_TYPE_PRIVATE || ($space_type == NC_SPACE_TYPE_GROUP && $thread_num == 1)) {
			$ins_page['Page']['lang'] = '';
		} else {
			$ins_page['Page']['lang'] = $lang;
		}

		return $ins_page;
	}

/**
 * Insert Community Page defaults
 * @param   integer    $current_page_id
 * @param   integer    $all_community_cnt
 * @return  Model Page
 * @since   v 3.0.0.0
 */
	public function getDefaultCommunityPage($current_page_id, $all_community_cnt) {
		// $current_page_idがコミュニティーではなければ、一番後ろに追加。
		$current_page = $this->_controller->Page->findById($current_page_id);
		if(isset($current_page['Page']) && $current_page['Page']['space_type'] == NC_SPACE_TYPE_GROUP) {
			if($current_page['Page']['thread_num'] != 1) {
				$current_page = $this->_controller->Page->findById($current_page['Page']['root_id']);
			}
			$display_sequence = $current_page['Page']['display_sequence'] + 1;
		} else {
			$display_sequence = $all_community_cnt + 1;
		}
		$page_name = __d('page', "New community");
		list($page_name, $permalink) = $this->_getPageName($page_name, $all_community_cnt + 1);

		// TODO:page_inf_id, page_style_id, page_column_idは0固定
		$ins_page = array('Page' =>array(
			'root_id' => 0,
			'parent_id' => NC_TOP_GROUP_ID,
			'thread_num' => 1,
			'display_sequence' => $display_sequence,
			'page_name' => $page_name,
			'permalink' => $permalink,
			'position_flag' => _ON,
			'lang' => '',
			'page_inf_id' => 0,
			'page_style_id' => 0,
			'page_column_id' => 0,
			'space_type' => NC_SPACE_TYPE_GROUP,
			'show_count' => 0,
			'display_flag' => _ON,
			'display_to_date' => null,
			'display_apply_subpage' => _ON,
			'display_reverse_permalink' => null,
			'lock_authority_id' => 0
		));

		return $ins_page;
	}

/**
 * 固定リンクが同じではないページ名称,固定リンクを取得
 * @param   string        $page_name
 * @param   integer       $count カウント数初期値
 * @param   Model Page    $parent_page 親ページ
 * @return  array    array($page_name, $permalink)
 * @since   v 3.0.0.0
 */
	protected function _getPageName($page_name, $count, $parent_page = null) {
		$lang = Configure::read(NC_CONFIG_KEY.'.'.'language');
		$permalink = preg_replace(NC_PERMALINK_PROHIBITION, NC_PERMALINK_PROHIBITION_REPLACE, $page_name);
		if($parent_page['Page']['permalink'] != '') {
			$permalink = $parent_page['Page']['permalink']. '/'. $permalink;
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
		return array($page_name. '-' . $count, $permalink. '-' . $count);
	}

/**
 * コミュニティー情報取得
 * @param   integer        $room_id
 * @return  mixed false or array    array(Model community Model Communitylang , Model CommunitiesTag['tag_values'])
 * @since   v 3.0.0.0
 */
	public function getCommunityData($room_id) {
		$lang = Configure::read(NC_CONFIG_KEY.'.'.'language');

		$conditions = array(
			'Community.room_id' => $room_id
		);
		$community = $this->_controller->Community->find('first', array(
			'recursive' => -1,
			'conditions' => $conditions
		));
		if(!isset($community['Community'])) {
			return false;
		}

		$conditions = array(
			'CommunityLang.room_id' =>  $room_id,
			'CommunityLang.lang' => $lang
		);
		$community_lang = $this->_controller->CommunityLang->find('first', array(
			'recursive' => -1,
			'conditions' => $conditions
		));
		if(!isset($community_lang['CommunityLang'])) {
			$community_lang = $this->_controller->CommunityLang->getDefault('', $room_id);
		}

		$params = array(
			'fields' => array('CommunityTag.tag_value'),
			'conditions' => array(
				'CommunityTag.room_id' => $room_id
			),
			'joins' => array(
				array(
					'type' => "INNER",
					'table' => "tags",
					'alias' => "Tag",
					'conditions' => array(
						"`Tag`.`id`=`CommunityTag`.`tag_id`",
						'Tag.lang' => $lang
					)
				)
			),
			'order' => array('CommunityTag.display_sequence' => 'ASC')
		);

		$ret_communities_tags = $this->_controller->CommunityTag->find('list', $params);
		if(!isset($communities_tags['CommunityTag'])) {
			$communities_tag['CommunityTag']['tag_values'] = '';
		} else {
			if(count($ret_communities_tags) > 0) {
				$tags_str = '';
				foreach($ret_communities_tags as $ret_communities_tag) {
					if($tags_str != '') {
						$tags_str .= ',';
					}
					$tags_str .= $ret_communities_tag;

				}
			}
			$communities_tag['CommunityTag']['tag_values'] = $tags_str;
		}

		return array($community, $community_lang, $communities_tag);
	}

	/**
	 * コミュニティーPhotoサンプル画像名称取得
	 * @return  array
	 * @since   v 3.0.0.0
	 */
	public function getCommunityPhoto() {
		$photo_samples = array();
		$plugin_path = App::pluginPath('Page');
		$sample_path = $plugin_path . 'webroot' . DS .'img'. DS .'community'. DS;
		if(is_dir($sample_path)) {
			$dirArray = glob( $sample_path . DS . "*" );
			if(is_array($dirArray) && count($dirArray) > 0) {
				foreach( $dirArray as $child_path){
					if(!is_dir( $child_path )) {
						$file_name = basename($child_path);
						$photo_samples[$file_name] = $file_name;
					}
				}
			}
		}
		return $photo_samples;
	}

/**
 * PageUserLinksテーブルのデータをSessionにセット
 *
 * @param   CakeRequest $request
 * @param   integer     $page_id
 * @param   integer     $admin_hierarchy
 * @return  array $page_user_links
 * @since   v 3.0.0.0
 */
	public function participantSession($request, $page_id, $admin_hierarchy) {
		$login_user = $this->_controller->Auth->user();
		$user_id = $login_user['id'];

		$buf_page_user_links = $this->_controller->Session->read(NC_SYSTEM_KEY.'.page_menu.PageUserLink['.$page_id.']');
		$write_flag = false;

		if(!empty($request->data['PageUserLink'])) {
			// 権限項目
			$buf_page_user_links_params['PageUserLink'] = $request->data['PageUserLink'];
			if($admin_hierarchy < NC_AUTH_MIN_ADMIN) {
				// ページメニューが管理者権限でないならば、ログイン会員は必ず主坦として参加
				$buf_page_user_links_params['PageUserLink'][$user_id] = array(
					'id' => 0,
					'room_id' => 0,
					'user_id' => $user_id,
					'authority_id' => NC_AUTH_CHIEF_ID
				);
			}
			if(!empty($buf_page_user_links)) {

				//$buf_page_user_links['PageUserLink'] = array_merge($buf_page_user_links['PageUserLink'], $buf_page_user_links_params['PageUserLink']);
				foreach($buf_page_user_links_params['PageUserLink'] as $key => $buf_data) {
					$buf_page_user_links['PageUserLink'][$key] = $buf_data;
				}
			} else {
				$buf_page_user_links = $buf_page_user_links_params;
			}
			$write_flag = true;
		}
		if($write_flag) {
			$this->_controller->Session->write(NC_SYSTEM_KEY.'.page_menu.PageUserLink['.$page_id.']', $buf_page_user_links);
		}

		return $this->_controller->Session->read(NC_SYSTEM_KEY.'.page_menu.PageUserLink['.$page_id.']');
	}

/**
 * 移動、ペースト、ショートカット作成時　Pageデータ取得
 *
 * @param   integer  $pre_page_id 移動元（コピー元、ショートカット元）PageId
 * @param   integer  $move_page_id 移動先（コピー先、ショートカット先）PageId
 * @param   string   $position 移動位置 default:inner   inner or top or bottom
 * @return  array    array(Model Page 移動元Page, Model Page 移動先Page, Model Page 移動元親Page, string 移動先ルーム名称,
 * 							array 移動元ページID配列, array 移動元ルームのページID配列, Model Pages 移動元子供Pages,
 * 							Model Pages 移動先子供Pages,
 * 							Model Page 移動元のもっとも多いdisplay_sequenceのPage, Model Page 移動先のもっとも多いdisplay_sequenceのPage)
 * @since   v 3.0.0.0
 */
	public function getOperationPage($pre_page_id, $move_page_id, $position = 'bottom') {
		$user_id = $this->_controller->Auth->user('id');
		$pre_page = $this->_controller->Page->findAuthById($pre_page_id, $user_id);
		$move_page = $this->_controller->Page->findAuthById($move_page_id, $user_id);
		if(!isset($pre_page['Page']) || !isset($move_page['Page'])) {
			return false;
		}

		if($position != 'inner') {
			$parent_move_page = $this->_controller->Page->findAuthById($move_page['Page']['parent_id'], $user_id);
		} else {
			$parent_move_page = $move_page;
		}

		if($move_page['Page']['id'] == $move_page['Page']['room_id']) {
			$move_room_name = $move_page['Page']['page_name'];
		} else if($move_page['Page']['room_id'] == $parent_move_page['Page']['id']) {
			$move_room_name = $parent_move_page['Page']['page_name'];
		} else {
			$parent_room_page = $this->_controller->Page->findAuthById($move_page['Page']['room_id'], $user_id);
			$move_room_name = $parent_room_page['Page']['page_name'];
		}
		$pre_page_id_arr = array($pre_page['Page']['id']);
		$pre_room_id_arr = array();
		if($pre_page['Page']['id'] == $pre_page['Page']['room_id']) {
			$pre_room_id_arr = array($pre_page['Page']['id']);
		}
		// 移動元ページ以下のページ取得
		$child_pre_pages = $this->_controller->Page->findChilds('all', $pre_page, $user_id);
		$last_pre_page = $pre_page;
		foreach($child_pre_pages as $child_page) {
			$pre_page_id_arr[] = $child_page['Page']['id'];
			if($child_page['Page']['id'] == $child_page['Page']['room_id']) {
				$pre_room_id_arr = array($child_page['Page']['id']);
			}
			// ノード中のもっとも多いdisplay_sequenceのPageを取得
			if($pre_page['Page']['thread_num'] != 1 && $last_pre_page['Page']['display_sequence'] < $child_page['Page']['display_sequence']) {
				$last_pre_page = $child_page;
			}
		}

		$child_move_pages = $this->_controller->Page->findChilds('all', $move_page, $user_id);
		$last_move_page = $move_page;
		if($move_page['Page']['thread_num'] != 1 || $position == 'inner') {
			foreach($child_move_pages as $child_page) {
				if(in_array($child_page['Page']['id'], $pre_page_id_arr)) {
					continue;
				}
				// ノード中のもっとも多いdisplay_sequenceのPageを取得
				if($pre_page['Page']['thread_num'] != 1 && $last_move_page['Page']['display_sequence'] < $child_page['Page']['display_sequence']) {
					$last_move_page = $child_page;
				}
			}
		}
		return array($pre_page, $move_page, $parent_move_page, $move_room_name, $pre_page_id_arr, $pre_room_id_arr, $child_pre_pages, $child_move_pages, $last_pre_page, $last_move_page);
	}

/**
 * 確認メッセージ表示
 * @param  string $action Action名
 * @param  string $position
 * @param  Model Page  $pre_page 移動元（コピー元、ショートカット元）Page
 * @param  Model Page  $move_page 移動先（コピー先、ショートカット先）Page
 * @param  string 移動先ルーム名称
 * @param  array 移動元ルームのページID配列
 * @param  Model Blocks $blocks 移動元blocks
 * @return string dialog html string
 * @since   v 3.0.0.0
 */
	public function showConfirm($action, $position, $pre_page, $move_page, $move_room_name, $pre_room_id_arr, $blocks) {
		$pre_room_id = $pre_page['Page']['room_id'];
		$move_room_id = $move_page['Page']['room_id'];

		$echo_str = '<div class="pages-menu-edit-confirm-desc">';
		switch($this->_controller->action) {
			case 'chgsequence':
			case 'move':
				if($position != 'inner' && $move_page['Page']['thread_num'] == 1) {
					// コミュニティ
					return '';	// 確認メッセージなし
				} else if(count($pre_room_id_arr) > 0) {
					// ルーム
					$echo_str .= __d('page', 'You are about to move to [%s]. <br />When you move the contents of the [%s], it becomes contents of [%s].Are you sure?',
							$move_room_name, $pre_page['Page']['page_name'], $move_room_name);
				} else if(count($blocks) > 0) {
					// ブロックあり
					if($pre_room_id != $move_room_id) {
						$echo_str .= __d('page', 'You are about to move to [%s]. <br />When you move, block placement will be moved as a shortcut. Are you sure?', $move_room_name);
					} else {
						$echo_str .= __d('page','You are about to move to [%s]. Are you sure?', $move_room_name);
					}
				} else {
					return '';	// 確認メッセージなし
				}
				break;
			case 'paste':
				$echo_str .= __d('page','You create a copy to [%s]. The copying of contents may take time to some extent. Are you sure?', $move_room_name);
				break;
			case 'shortcut':
				$echo_str .= __d('page','You create a shortcut to [%s]. Are you sure?', $move_room_name);
				break;
		}

		$echo_str .= '</div>';

		if($this->_controller->action == 'shortcut' && $pre_room_id != $move_room_id) {
			// 移動先が移動元と異なれば、チェックボックス表示
			$echo_str .= '<label class="pages-menu-edit-confirm-shortcut" for="pages-menu-edit-confirm-shortcut">'.
					'<input id="pages-menu-edit-confirm-shortcut" type="checkbox" name="shortcut_flag" value="'._ON.'" />&nbsp;'.
					__('Allow the room authority to view and edit.').
					'</label>';
		}

		// 注釈
		$echo_sub_str = '';
		if(count($pre_room_id_arr) > 0) {
			// ルーム、または、子グループあり
			$echo_sub_str .= '<li>'.__d('page', 'The assignment of the rights of the room is released.').'</li>';
		}
		if($this->_controller->action != 'move' && $this->_controller->action != 'chgsequence') {
			$echo_sub_str .= '<li>'.__d('page', 'Block shortcut is copied as is.').'</li>';
			$echo_sub_str .= '<li>'.__d('page', 'Only a block located on the page is copied.').'</li>';
		}
		$echo_sub_str .= '<li>'.__d('page', 'It is added under the page that you selected.').'</li>';
		$echo_str .= '<div class="align-right"><a class="pages-menu-edit-confirm-note" href="#" onclick="$(\'#pages-menu-edit-confirm-ul\').toggle();return false;">'.__('Note')
				.'</a></div><ul id="pages-menu-edit-confirm-ul">'.$echo_sub_str.'</ul>';

		if(count($blocks) != 0 && $action == 'paste') {
			$exempt_modules = array();
			// 配置ブロックあり - ペースト関数チェック
			foreach($blocks as $block) {
				if(!isset($block['Module']['dir_name'])) {
					// グループブロック
					continue;
				}
				$module_id = $block['Block']['module_id'];
				$dir_name = $block['Module']['dir_name'];
				// module_linksで移動先ルームに貼り付けることができるかどうか確認
				if(!isset($exempt_modules[$module_id]) && !$this->_controller->ModuleLink->isAddModule($move_page, $module_id)) {
					// 操作対象外
					$exempt_modules[$module_id] = $this->_controller->Module->loadModuleName($dir_name);
				}

				if(!isset($exempt_modules[$module_id]) && $action == 'paste') {
					// ショートカットと移動は関数がなくてもエラーとしない
					if(!$this->_controller->Module->isOperationAction($dir_name, $action)) {
						// コピー対象外
						$exempt_modules[$module_id] = $this->_controller->Module->loadModuleName($dir_name);
					}
				}
			}
			if(count($exempt_modules)) {
				$echo_str .= '<div class="align-right"><a class="pages-menu-edit-confirm-textarea" href="#" onclick="$(\'#pages-menu-edit-confirm-textarea\').toggle();return false;">'.
					__d('page','Exempt from the Operation')
				.'</a></div><textarea id="pages-menu-edit-confirm-textarea" readonly="readonly">'.implode(',', $exempt_modules).'</textarea>';
			}
		}

		return $echo_str;
	}

/**
 * TempDataのキー取得
 * @param   integer   copy_page_id	コピー元
 * @param   integer   page_id		コピー先
 * @return  void
 * @since   v 3.0.0.0
 */
	public function getOperationKey($copy_page_id, $move_page_id) {
		return 'page_menu.percent['.$this->_controller->Session->id().']['.$copy_page_id.']['.$move_page_id.']';
	}

/**
 * ページのコピー,移動処理
 * @param   string       $action paste or shortcut or move
 * @param   boolean      $is_confirm
 * @param   integer      $copy_page_id		コピー元
 * @param   integer      $move_page_id		コピー先
 * @param   string       $position inner top or bottom 挿入位置
 * @return  boolean false(バリデータ以外のエラーの場合、リダイレクトして終了) or 確認メッセージ or array ($copy_page_id_arr, copy page lists, new page lists)
 * @since   v 3.0.0.0
 */
	public function operatePage($action, $is_confirm, $copy_page_id, $move_page_id, $position = 'bottom') {
		$ins_pages = array();
		$copy_pages = array();
		//
		// データ取得
		//
		$results = $this->getOperationPage($copy_page_id, $move_page_id, $position);
		if($results === false) {
			$this->_controller->flash(__('Failed to obtain the database, (%s).', 'pages'), null, 'PageMenu/operatePage.001', '500');
			return false;
		}
		list($copy_page, $move_page, $move_parent_page, $move_room_name, $copy_page_id_arr,
				$copy_room_id_arr, $child_copy_pages, $child_move_pages, $last_copy_page, $last_move_page) = $results;
		$insert_display_sequence = $last_move_page['Page']['display_sequence'];
		$copy_pages[] = $copy_page;
		if(count($child_copy_pages) > 0) {
			$copy_pages = array_merge ( $copy_pages, $child_copy_pages );
		}

		if($copy_page['Page']['root_id'] != $move_page['Page']['root_id'] || $copy_page['Page']['lang'] != $move_page['Page']['lang']) {
			$is_diff_node = true;
		} else {
			$is_diff_node = false;
		}
		if($copy_page['Page']['thread_num'] == 1 && $move_page['Page']['thread_num'] == 1 &&
				$copy_page['Page']['space_type'] == $move_page['Page']['space_type'] &&
				$copy_page['Page']['lang'] == $move_page['Page']['lang'] &&
				$position != 'inner') {
			$is_same_top_node = true;
		} else {
			$is_same_top_node = false;
		}


		// 権限チェック
		$admin_hierarchy = $this->validatorPage($this->_controller->request, $copy_page);
		if(!$admin_hierarchy) {
			$this->_controller->flash(__('Unauthorized request.<br />Please reload the page.', 'pages'), null, 'PageMenu/operatePage.002', '500');
			return false;
		}

		// ブロック情報取得
		$user_id = $this->_controller->Auth->user('id');
		$blocks = $this->_controller->Block->findByPageIds($copy_page_id_arr, $user_id, '');

		if(!$is_confirm) {
			$confirm = $this->showConfirm($this->_controller->action, $position, $copy_page, $move_page, $move_room_name, $copy_room_id_arr, $blocks);
			if($confirm != '') {
				echo $confirm;
				$this->_controller->render(false, 'ajax');
				return true;
			}
		}

		//
		// ページデータ挿入
		//
		$ins_page['Page'] = $copy_page['Page'];
		$ins_page['Page']['display_reverse_permalink'] = null;

		$space_type = $move_page['Page']['space_type'];
		$lang = $move_page['Page']['lang'];
		if($position == 'inner') {
			$parent_id = $move_page['Page']['id'];
			$thread_num = $move_page['Page']['thread_num'] + 1;
			$display_sequence = $insert_display_sequence + 1;
			$room_id = $move_page['Page']['room_id'];
			$root_id = $move_page['Page']['root_id'];
		//} else if($copy_page['Page']['thread_num'] <= 1) {
		//	// Top Node top or bottom
		//	$parent_id = $move_page['Page']['parent_id'];
		//	$thread_num = $move_page['Page']['thread_num'];
		//	if ($move_page['Page']['space_type'] == NC_SPACE_TYPE_GROUP) {
		//		$display_sequence = ($position == 'bottom') ? $move_page['Page']['display_sequence'] + 1 : $move_page['Page']['display_sequence'];
		//	} else {
		//		$display_sequence = 0;
		//	}
		//	$room_id = $copy_page['Page']['room_id'];
		//  $root_id = $move_page['Page']['root_id'];
		} else if($is_same_top_node) {
			$parent_id = $copy_page['Page']['parent_id'];
			$thread_num = $copy_page['Page']['thread_num'];
			$display_sequence = ($position == 'bottom') ? $move_page['Page']['display_sequence'] + 1 : $move_page['Page']['display_sequence'];
			$room_id = $copy_page['Page']['room_id'];
			$root_id = $copy_page['Page']['root_id'];
		} else if($position == 'bottom') {
			$parent_id = $move_page['Page']['parent_id'];
			$thread_num = $move_page['Page']['thread_num'];
			$display_sequence = $insert_display_sequence + 1;
			$room_id = $move_parent_page['Page']['room_id'];
			$root_id = $move_parent_page['Page']['root_id'];
		} else {
			// top
			$parent_id = $move_page['Page']['parent_id'];
			$thread_num = $move_page['Page']['thread_num'];
			$display_sequence = $move_page['Page']['display_sequence'];
			$room_id = $move_parent_page['Page']['room_id'];
			$root_id = $move_parent_page['Page']['root_id'];
		}

		// 固定リンク、ページ名称設定
		$page_name = $copy_page['Page']['page_name'];
		$pre_permalink = $this->_controller->Page->getMovePermalink($copy_page, $move_parent_page);
		if($action == 'paste' || $action == 'shortcut') {
			$id = 0;
		} else {
			$id = $copy_page['Page']['id'];
		}
		list($count, $permalink) = $this->renamePermalink($id, $pre_permalink, $move_page['Page']['space_type'], $move_page['Page']['lang']);

		if($count > 0) {
			//$page_name = __d('pages', 'copy %s %s', $count, $copy_page['Page']['page_name']) ;
			$page_name = preg_replace('/^\[copy[0-9]+\](.*)/', "$1", $page_name);
			$page_name = __d('pages', '[copy%s]%s', $count, $page_name) ;
		}

		// 登録処理
		$childs_update_type = ($action == 'move' || $action == 'chgsequence') ? 'update_once' : 'update_all';
		$currentFieldList = array();
		if($page_name != $copy_page['Page']['page_name']) {
			$currentFieldList[] = 'page_name';
			$ins_page['Page']['page_name'] = $page_name;
		}
		if($permalink != $copy_page['Page']['permalink']) {
			$currentFieldList[] = 'permalink';
			$ins_page['Page']['permalink'] = $permalink;
			$childs_update_type = 'update_all';
		}
		if($parent_id != $copy_page['Page']['parent_id']) {
			$currentFieldList[] = 'parent_id';
			$ins_page['Page']['parent_id'] = $parent_id;
		}
		if($lang != $copy_page['Page']['lang']) {
			$currentFieldList[] = 'lang';
			$ins_page['Page']['lang'] = $lang;
		}

		// 「下位にも適用」のFrom公開日付を１つ上の階層のものしかみていない。
		// このため、２つ以上、上位階層で「下位にも適用」でFrom公開日付を設定されていると
		// From公開日付が設定されないが、上位階層を表示すれば自動的に公開にはなるため、
		// 更新は行わない。
		if($move_parent_page['Page']['thread_num'] > 1 && $move_parent_page['Page']['display_flag'] == _OFF &&
				$move_page['Page']['display_flag'] == _ON) {
			$currentFieldList[] = 'display_flag';
			$ins_page['Page']['display_flag'] = _OFF;
			$childs_update_type = 'update_all';
		}

		if((!empty($move_parent_page['Page']['display_from_date']) && $move_parent_page['Page']['display_apply_subpage'] == _ON) &&
				!empty($move_page['Page']['display_from_date']) &&
				strtotime($move_page['Page']['display_from_date']) < strtotime($move_parent_page['Page']['display_from_date'])) {
			$currentFieldList[] = 'display_from_date';
			$ins_page['Page']['display_from_date'] = $move_parent_page['Page']['display_from_date'];
			$childs_update_type = 'update_all';
		}
		if(!empty($move_parent_page['Page']['display_to_date']) &&
				(empty($move_page['Page']['display_to_date']) ||
						strtotime($move_page['Page']['display_to_date']) > strtotime($move_parent_page['Page']['display_to_date']))) {
			$currentFieldList[] = 'display_to_date';
			$ins_page['Page']['display_to_date'] =
			$childs_update_type = 'update_all';
		}

		if($childs_update_type == 'update_once') {
			$fields = array();
			if($display_sequence != $copy_page['Page']['display_sequence']) {
				$fields['Page.display_sequence'] = 'Page.display_sequence+('.($display_sequence - $copy_page['Page']['display_sequence']).')';
				$ins_page['Page']['display_sequence'] = $display_sequence;
			}
			if($thread_num != $copy_page['Page']['thread_num']) {
				$fields['Page.thread_num'] = 'Page.thread_num+('.($thread_num - $copy_page['Page']['thread_num']).')';
				$ins_page['Page']['thread_num'] = $thread_num;
			}
			if($root_id != $copy_page['Page']['root_id']) {
				$fields['Page.root_id'] = $root_id;
				$ins_page['Page']['room_id'] = $root_id;
			}
			if($space_type != $copy_page['Page']['space_type']) {
				$fields['Page.space_type'] = $pace_type;
				$ins_page['Page']['space_type'] = $space_type;
			}
			if($room_id != $copy_page['Page']['room_id']) {
				$fields['Page.room_id'] = $room_id;
				$ins_page['Page']['room_id'] = $room_id;
			}
			if($lang != $copy_page['Page']['lang']) {
				$fields['Page.lang'] = $lang;
			}
			$error_mes = "Failed to update the database, (%s).";
		} else {
			$sum_display_sequence = 0;
			if($display_sequence != $copy_page['Page']['display_sequence']) {
				$currentFieldList[] = 'display_sequence';
				$ins_page['Page']['display_sequence'] = $display_sequence;
				$sum_display_sequence = $display_sequence - $copy_page['Page']['display_sequence'];
			}
			$sum_thread_num = 0;
			if($thread_num != $copy_page['Page']['thread_num']) {
				$currentFieldList[] = 'thread_num';
				$ins_page['Page']['thread_num'] = $thread_num;
				$sum_thread_num = $thread_num - $copy_page['Page']['thread_num'];
			}
			if($root_id != $copy_page['Page']['root_id']) {
				$currentFieldList[] = 'root_id';
				$ins_page['Page']['root_id'] = $root_id;
			}
			if($space_type != $copy_page['Page']['space_type']) {
				$currentFieldList[] = 'space_type';
				$ins_page['Page']['space_type'] = $space_type;
			}
			if($room_id != $copy_page['Page']['room_id']) {
				$currentFieldList[] = 'room_id';
				$ins_page['Page']['room_id'] = $room_id;
			}
			$error_mes = "Failed to register the database, (%s).";
		}

		$ins_pages[] = $ins_page;

		// 子ページエラーチェック
		if($childs_update_type == 'update_all' && count($child_copy_pages) > 0) {
			$appendField = array(
				'display_sequence' => $sum_display_sequence,
				'thread_num' => $sum_thread_num,
				'root_id' => $root_id,
				'space_type' => $space_type,
				'room_id' => $room_id,
				'lang' => $lang
			);
			$ret_childs = $this->childsValidateErrors($action, $child_copy_pages, $ins_page, $appendField);
			if(!$ret_childs) {
				// 子ページエラーメッセージ
				// 親と子とのエラーメッセージの差異はなし
				return false;
			}
		}

		// カレントページ更新
		$ins_page_id_arr = array();
		if(count($currentFieldList) > 0) {
			if($action == 'paste' || $action == 'shortcut') {
				unset($ins_page['Page']['id']);	// pageをinsertするため
				$ins_page['Page']['show_count'] = 0;
				$currentFieldList = null;
			}
			$this->_controller->Page->create();
			if(!$this->_controller->Page->save($ins_page, true, $currentFieldList)) {
				//$this->_controller->flash(__($error_mes, 'pages'), null, 'PageMenu/operatePage.003', '500');
				return false;
			}
			$ins_page['Page']['id'] = $this->_controller->Page->id;
			$ins_pages[0] = $ins_page;	// 再セット
			if($action == 'paste' || $action == 'shortcut') {
				$ins_page_id_arr[] = $this->_controller->Page->id;
			}
		}

		// 子ページ更新
		if(isset($fields) && count($fields) > 0) {
			if($is_same_top_node) {
				$conditions = array(
						'Page.id' => $copy_page['Page']['id']
				);
			} else {
				$conditions = array(
						'Page.id' => $copy_page_id_arr
				);
			}
			$ret = $this->_controller->Page->updateAll($fields, $conditions);
			if(!$ret) {
				$this->_controller->flash(__($error_mes, 'pages'), null, 'PageMenu/operatePage.003', '500');
				return false;
			}
		} else if(isset($ret_childs) && is_array($ret_childs)) {
			$ret_childsUpdate = $this->childsUpdate($action, $ret_childs[0], $ret_childs[1], $copy_page['Page']['id'], $ins_page['Page']['id']);
			if (!$ret_childsUpdate) {
				$this->_controller->flash(__($error_mes, 'pages'), null, 'PageMenu/operatePage.004', '500');
				return false;
			}
			$ins_pages = array_merge ( $ins_pages, $ret_childs[0] );
			if($action == 'paste' || $action == 'shortcut') {
				foreach($ret_childsUpdate as $new_page_id) {
					$ins_page_id_arr[] = $new_page_id;
				}
			}
		}

		/**
		 * 移動先、移動元を更新
		 * 移動するfrom-Toを計算し、一度のSQLでdisplay_sequenceの更新処理を行っている
		 */
		if($is_same_top_node) {
			// コミュニティの表示順変更
			$conditions = array(
				'Page.position_flag' => _ON,
				'Page.thread_num' => 1,
				'Page.space_type' => $copy_page['Page']['space_type'],
				'Page.lang' => ''
			);
			if($action == 'paste' || $action == 'shortcut') {
				// インクリメント
				$upd_display_sequence = 1;
				$operation = ($position == 'top') ? '>=' : '>';
				$conditions["Page.display_sequence ".$operation] = $move_page['Page']['display_sequence'];
				$conditions['not'] = array('Page.id' => $ins_page_id_arr);
			} else if($copy_page['Page']['display_sequence'] < $move_page['Page']['display_sequence']) {
				// 上から下へ デクリメント
				$upd_display_sequence = -1;
				$conditions["Page.display_sequence >"] = $last_copy_page['Page']['display_sequence'];
				if($position == 'bottom') {
					$conditions["Page.display_sequence <="] = $ins_page['Page']['display_sequence'];

					$next_conditions = array(
						'Page.position_flag' => _ON,
						'Page.thread_num' => 1,
						'Page.lang' => '',
						'Page.display_sequence' => intval($move_page['Page']['display_sequence']) + 1,
						'not' => array('Page.id' => $ins_page['Page']['id'])
					);
					$next_page = $this->_controller->Page->find('first', array('fields' => array('Page.id'), 'conditions' => $next_conditions));
					if(isset($next_page['Page'])) {
						// 移動先の１つ下のページを取得し、そのページ以外
						$conditions['not'] = array('Page.id' => $next_page['Page']['id']);
					}
				} else {
					// top
					$conditions["Page.display_sequence <="] = $move_page['Page']['display_sequence'];
					$conditions['not'] = array('Page.id' => $move_page['Page']['id']);
				}
			} else {
				// 下から上へ インクリメント
				$upd_display_sequence = 1;
				$operation = ($position == 'top') ? '>=' : '>';
				$conditions["Page.display_sequence ".$operation] = $move_page['Page']['display_sequence'];
				$conditions["Page.display_sequence <"] = $copy_page['Page']['display_sequence'];
				$conditions['not'] = array('Page.id' => $copy_page['Page']['id']);
			}
			$fields = array('Page.display_sequence'=>'Page.display_sequence+('.$upd_display_sequence.')');
			if(!$this->_controller->Page->updateAll($fields, $conditions)) {
				$this->_controller->flash(__('Failed to update the database, (%s).', 'pages'), null, 'PageMenu/operatePage.005', '500');
				return false;
			}
		} else {
			$conditions = array(
				'Page.position_flag' => _ON,
				'Page.thread_num >' => 1,
				'Page.lang' => $move_page['Page']['lang'],
				'Page.root_id' => $move_page['Page']['root_id']
			);
			if($action == 'paste' || $action == 'shortcut') {
				$upd_display_sequence = count($child_copy_pages) + 1;
				if($position == 'top') {
					$conditions["Page.display_sequence >="] = $move_page['Page']['display_sequence'];
				} else {
					$conditions["Page.display_sequence >"] = $insert_display_sequence;
				}
				$conditions['not'] = array('Page.id' => $ins_page_id_arr);
			} else if($is_diff_node) {
				// 別ルート
				$upd_display_sequence = count($child_copy_pages) + 1;
				$conditions['not'] = array('Page.id' => $copy_page_id_arr);

				$pre_fields = array('Page.display_sequence'=>'Page.display_sequence+(-'.$upd_display_sequence.')');
				$pre_conditions = $conditions;
				$pre_conditions["Page.display_sequence >"] = $last_copy_page['Page']['display_sequence'];
				$pre_conditions["Page.lang"] = $copy_page['Page']['lang'];
				$pre_conditions["Page.root_id"] = $copy_page['Page']['root_id'];
				if(!$this->_controller->Page->updateAll($pre_fields, $pre_conditions)) {
					$this->_controller->flash(__('Failed to update the database, (%s).', 'pages'), null, 'PageMenu/operatePage.006', '500');
					return false;
				}
				if($position == 'bottom' || $position == 'inner') {
					$conditions["Page.display_sequence >="] = $display_sequence;
				} else {
					// top
					$conditions["Page.display_sequence >="] = $move_page['Page']['display_sequence'];
				}
			} else if($copy_page['Page']['display_sequence'] < $move_page['Page']['display_sequence']) {
				// 上から下へ デクリメント
				$upd_display_sequence = -(count($child_copy_pages) + 1);
				$conditions["Page.display_sequence >"] = $last_copy_page['Page']['display_sequence'];

				if($position == 'bottom' || $position == 'inner') {
					$conditions["Page.display_sequence <="] = $ins_page['Page']['display_sequence'] + count($child_copy_pages);;

					$next_conditions = array(
						'Page.position_flag' => _ON,
						'Page.lang' => $move_page['Page']['lang'],
						'Page.root_id' => $move_page['Page']['root_id'],
						'Page.display_sequence >=' => intval($insert_display_sequence) + 1,
						'Page.display_sequence <' => intval($ins_page['Page']['display_sequence']) + count($child_copy_pages) + 2,
						'not' => array('Page.id' => $copy_page_id_arr)
					);
					$next_page = $this->_controller->Page->find('list', array('fields' => array('Page.id'), 'conditions' => $next_conditions));
					if(is_array($next_page) && count($next_page) > 0) {
						// 移動先の下のページを取得し、そのページ以外
						$conditions['not'] = array('Page.id' => $next_page);
					}
				} else {
					// top
					$conditions["Page.display_sequence <="] = $ins_page['Page']['display_sequence'] + count($child_copy_pages);
					$next_conditions = array(
						'Page.position_flag' => _ON,
						'Page.lang' => $move_page['Page']['lang'],
						'Page.root_id' => $move_page['Page']['root_id'],
						'Page.display_sequence >=' => $ins_page['Page']['display_sequence'],
						'Page.display_sequence <' => intval($ins_page['Page']['display_sequence']) + count($child_copy_pages) + 1,
						'not' => array('Page.id' => $copy_page_id_arr)
					);
					$next_page = $this->_controller->Page->find('list', array('fields' => array('Page.id'), 'conditions' => $next_conditions));
					if(is_array($next_page) && count($next_page) > 0) {
						// 移動先の下のページを取得し、そのページ以外
						$conditions['not'] = array('Page.id' => $next_page);
					}
				}
			} else {
				// 下から上へ インクリメント
				$upd_display_sequence = count($child_copy_pages) + 1;
				if($position == 'top') {
					$conditions["Page.display_sequence >="] = $move_page['Page']['display_sequence'];
				} else {
					$conditions["Page.display_sequence >"] = $insert_display_sequence;
				}
				$conditions["Page.display_sequence <"] = $copy_page['Page']['display_sequence'];
				$conditions['not'] = array('Page.id' => $copy_page_id_arr);
			}
			$fields = array('Page.display_sequence'=>'Page.display_sequence+('.$upd_display_sequence.')');
			if(!$this->_controller->Page->updateAll($fields, $conditions)) {
				$this->_controller->flash(__('Failed to update the database, (%s).', 'pages'), null, 'PageMenu/chgsequence.007', '500');
				return false;
			}
		}

		//
		// 権限の解除
		//
		// TOOD:未作成






		return array($copy_page_id_arr, $copy_pages, $ins_pages);
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
 * 同じ階層に同名の固定リンクあればリネームして返す
 *
 * @param   integr      $id
 * @param   string      $permalink
 * @param   integr      $space_type
 * @param   string      $lang
 * @return  array(integer count ,string      $permalink)
 * @since   v 3.0.0.0
 */
	protected function renamePermalink($id, $permalink, $space_type, $lang) {
		$pre_permalink = $permalink;
		$pre_permalink_arr = explode('/', $pre_permalink);
		$pre_current_permalink = preg_replace('/^-copy[0-9]+-(.*)/', "$1", array_pop($pre_permalink_arr));
		$pre_parent_permalink = implode($pre_permalink_arr, '/');
		if($pre_parent_permalink != '') {
			$pre_parent_permalink .= '/';
		}
		$count = 0;
		while(1) {

			$chk_conditions = array(
				'Page.position_flag' => _ON,
				'Page.lang' => $lang,
				'Page.space_type' => $space_type,
				'Page.permalink' => $permalink,
				'Page.id !=' => $id
			);
			$chk_page = $this->_controller->Page->find('first', array('fields' => array('Page.id'), 'conditions' => $chk_conditions));
			if(isset($chk_page['Page'])) {
				// 同名の固定リンクあり
				$count ++ ;
				$permalink = $pre_parent_permalink.__d('pages', '-copy%s-%s', $count, $pre_current_permalink) ;


				//$permalink = $pre_parent_permalink.__d('pages', 'copy_%s_%s', $count, $pre_current_permalink) ;
			} else {
				break;
			}
		}
		return array($count, $permalink);
	}
}