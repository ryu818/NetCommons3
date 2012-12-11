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
 * @return mixed $admin_hierarchy or false + flash
 * @since   v 3.0.0.0
 */
	public function validatorPage($request, $page = null, $parent_page = null) {
		$login_user = $this->_controller->Auth->user();
		$user_id = $login_user['id'];

		$admin_hierarchy = $this->_controller->ModuleSystemLink->findHierarchy(Inflector::camelize($request->params['plugin']), $login_user['authority_id']);
		if($admin_hierarchy <= NC_AUTH_GENERAL) {
			$this->_controller->flash(__('Forbidden permission to access the page.'), null, 'PageMenu.validatorPage.001', '403');
			return false;
		}

		if(is_null($page)) {
			return $admin_hierarchy;
		}
		if(!isset($page['Page'])) {
			$this->_controller->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenu.validatorPage.002', '400');
			return false;
		}

		$is_auth_ok = false;
		if($request->params['action'] == 'chgsequence' && $page['Page']['thread_num'] == 1 && $admin_hierarchy >= NC_AUTH_MIN_ADMIN &&
				$page['Page']['space_type'] == NC_SPACE_TYPE_GROUP) {
			$is_auth_ok = true;
		}

		$chk_page = isset($parent_page) ? $parent_page : $page;
		if(!$is_auth_ok && !$this->_controller->CheckAuth->chkAuth($chk_page['Authority']['hierarchy'], NC_AUTH_CHIEF)) {
			//if($user_id == 0) {
			//	$this->Session->setFlash(__('Forbidden permission to access the page.'), 'default', array(), 'auth');
			//}
			$this->_controller->flash(__('Forbidden permission to access the page.'), null, 'PageMenu.validatorPage.003', '403');
			return false;
		}

		if($page['Page']['position_flag'] != _ON) {
			$this->_controller->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenu.validatorPage.004', '400');
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
 * @return boolean
 * @since   v 3.0.0.0
 */
	public function validatorPageDetail($request, $page = null, $parent_page = null, $admin_hierarchy = null) {
		if($request->params['action'] != 'detail') {
			if(!$request->is('post')) {
				$this->_controller->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenu.validatorPageDetail.001', '400');
				return false;
			}
		}
		switch($request->params['action']) {
			case 'add':
				if($page['Page']['thread_num'] == 0) {
					$this->_controller->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenu.validatorPageDetail.002', '400');
					return false;
				}
				break;
			case 'display':
				// 親がOFFならば変更を許さない。
				$parent_page = $this->_controller->Page->findById($page['Page']['parent_id']);
				if(!isset($parent_page['Page']) || $parent_page['Page']['display_flag'] != NC_DISPLAY_FLAG_ON) {
					$this->_controller->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenu.validatorPageDetail.003', '400');
					return false;
				}
				if($page['Page']['thread_num'] <= 1 && $page['Page'] != NC_SPACE_TYPE_GROUP) {
					//コミュニティ以外のTop Nodeの編集は許さない
					$this->_controller->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenu.validatorPageDetail.004', '400');
					return false;
				}
				break;
			case 'delete':
				// 各スペースタイプのトップページは最低１つ以上ないと削除不可。
				if ($page['Page']['display_sequence'] == 1) {
					// 該当ルームのTopページの数を取得
					$conditions = array(
							'Page.room_id' => $page['Page']['room_id'],
							'Page.display_sequence' => $page['Page']['display_sequence'],
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
					//コミュニティのTop Node以外の編集は許さない
					$this->_controller->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenu.validatorPageDetail.005', '400');
					return false;
				}
				/*if($page['Page']['space_type'] != NC_SPACE_TYPE_PUBLIC && $page['Page']['thread_num'] == 2 && $page['Page']['display_sequence'] == 1) {
					//パブリック以外の各ノードのTopページの編集は許さない
					$this->_controller->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenu.validatorPageDetail.006', '400');
					return false;
				}*/
				break;
			case 'chgsequence':
				// ページロックしてあれば、削除不可。
				if($page['Page']['lock_authority_id'] > 0) {
					$page['Page'] = $this->_controller->Page->setPageName($page['Page']);
					echo __d('page', 'Because the [%s] page is locked, I can\'t be deleted. Please run after unlock the page.', $page['Page']['page_name']);
					$this->_controller->render(false, 'ajax');
					return false;
				}
				if($page['Page']['thread_num'] == 1) {
					if($page['Page']['space_type'] != NC_SPACE_TYPE_GROUP || $admin_hierarchy < NC_AUTH_MIN_ADMIN) {
						//コミュニティの表示順変更は$admin_hierarchy=NC_AUTH_MIN_ADMINのみ
						$this->_controller->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenu.validatorPageDetail.007', '400');
					}
				} else if($page['Page']['thread_num'] <= 1) {
					//Top Nodeの編集は許さない
					$this->_controller->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenu.validatorPageDetail.008', '400');
					return false;
				}
				break;
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
 * @return boolean
 * @since   v 3.0.0.0
 */
	public function validatorMovePage($request, $page, $move_page, $position) {
		$user_id = $this->_controller->Auth->user('id');
		if($position != 'inner') {
			$parent_page = $this->_controller->Page->findAuthById($move_page['Page']['parent_id'], $user_id);
		} else {
			$parent_page = $move_page;
		}

		// 移動先権限チェック
		if(!$this->_controller->CheckAuth->chkAuth($parent_page['Authority']['hierarchy'], NC_AUTH_CHIEF)) {
			$this->_controller->flash(__('Forbidden permission to access the page.'), null, 'PageMenu.validatorMovePage.001', '400');
			return;
		}

		if(($page['Page']['space_type'] != NC_SPACE_TYPE_GROUP && $page['Page']['thread_num'] == 1)
				|| $page['Page']['thread_num'] == 0 || $move_page['Page']['thread_num'] == 0 ||
				($page['Page']['space_type'] != NC_SPACE_TYPE_GROUP && $move_page['Page']['thread_num'] == 1 && $position != 'inner')) {
			// 移動元がコミュニティ以外のノード,移動先が Top Node
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
			// コミュニティTopノードへの移動チェック
			$this->_controller->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenu.validatorMovePage.005', '400');
			return false;
		}
		return $parent_page;
	}

/**
 *  ページ表示順変更、編集時、子供編集処理
 *
 *  <pre>
 *  現在のpage下のページすべてのpermalink更新処理
 *  From公開日付は、「下位にも適用」の場合、親の日付で更新
 *  To公開日付は、子供がセットしていないか、親よりも古い日付ならば、親の日付で更新
 *  </pre>
 *
 * @param  Page Models  $child_pages
 * @param  Page Model   $current_page
 * @param  Page Model   $parent_page セットされていれば、$current_pageも更新対象とする。
 * @return boolean
 * @since   v 3.0.0.0
 */
	public function childsUpdate($child_pages, $current_page, $parent_page = null) {
		$current_permalink_arr = array();
		if(isset($parent_page['Page'])) {
			// 表示順変更の場合、「下位にも適用」のFrom公開日付を１つ上の階層のものしかみていない。
			// このため、２つ以上、上位階層で「下位にも適用」でFrom公開日付を設定されていると
			// From公開日付が設定されないが、上位階層を表示すれば自動的に公開にはなるため、
			// 更新は行わない。
			$display_flag = $parent_page['Page']['display_flag'];
			$display_from_date = $parent_page['Page']['display_from_date'];
			$display_to_date = $parent_page['Page']['display_to_date'];
			$display_apply_subpage = $parent_page['Page']['display_apply_subpage'];
			$current_permalink_arr[$parent_page['Page']['id']] = $parent_page['Page']['permalink'];
			array_unshift($child_pages, $current_page);
		} else {
			$display_flag = $current_page['Page']['display_flag'];
			$display_from_date = $current_page['Page']['display_from_date'];
			$display_to_date = $current_page['Page']['display_to_date'];
			$display_apply_subpage = $current_page['Page']['display_apply_subpage'];
			$current_permalink_arr[$current_page['Page']['id']] = $current_page['Page']['permalink'];
		}

		foreach($child_pages as $key => $child_page) {
			$permalink_arr = explode('/', $child_page['Page']['permalink']);
			if($current_permalink_arr[$child_page['Page']['parent_id']] != '') {
				$upd_permalink = $current_permalink_arr[$child_page['Page']['parent_id']] . '/' . $permalink_arr[count($permalink_arr)-1];
			} else {
				$upd_permalink = $permalink_arr[count($permalink_arr)-1];
			}

			$this->_controller->Page->create();
			$fieldChildList = array('permalink');

			$child_page['Page']['permalink'] = $upd_permalink;
			$current_permalink_arr[$child_page['Page']['id']] = $upd_permalink;

			if($display_flag == _OFF && $child_page['Page']['display_flag'] == _ON) {
				$child_page['Page']['display_flag'] = _OFF;
				$fieldChildList['display_flag'] = true;
			}

			if(!empty($display_from_date) && $display_apply_subpage == _ON) {
				$child_page['Page']['display_from_date'] = $display_from_date;
				$fieldChildList['display_from_date'] = true;
			}
			if(!empty($display_from_date) && !empty($child_page['Page']['display_from_date']) &&
					strtotime($child_page['Page']['display_from_date']) < strtotime($display_from_date)) {
				$child_page['Page']['display_from_date'] = $display_from_date;
				$fieldChildList['display_from_date'] = true;
			}
			if(!empty($display_to_date)) {
				if(empty($child_page['Page']['display_to_date']) || strtotime($child_page['Page']['display_to_date']) > strtotime($display_to_date)) {
					$child_page['Page']['display_to_date'] = $display_to_date;
					$fieldChildList['display_to_date'] = true;
				}
			}
			//$this->_controller->Page->set($child_page);
			if(!$this->_controller->Page->save($child_page, false, $fieldChildList)) {	//親でUpdate処理実行後に更新するためバリデータを実行しない。
				return false;
			}
		}
		return true;
	}
}