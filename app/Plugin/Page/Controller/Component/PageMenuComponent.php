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

		/**
		 * 管理者：コミュニティーの表示順変更、すべてのコミュニティーの削除。
		 * 主坦：コミュニティーの表示順変更(参加コミュニティのみ)。デフォルト参加ルームの作成。
		 * モデレータ：コミュニティーの作成、編集、削除。（公開コミュニティーの作成。モデレータのHierarchyを２つに分離する）
		 * 一般：主坦権限のルームへのページ追加、編集、削除
		 * ゲスト：ページメニューをみるだけ。
		 */
		$is_auth_ok = false;
		if(($request->params['action'] == 'chgsequence' || $request->params['action'] == 'delete') &&
				$page['Page']['thread_num'] == 1 && $admin_hierarchy >= NC_AUTH_MIN_ADMIN &&
				$page['Page']['space_type'] == NC_SPACE_TYPE_GROUP) {
			$is_auth_ok = true;
		}

		$chk_page = isset($parent_page) ? $parent_page : $page;
		if(!$is_auth_ok && !$this->_controller->CheckAuth->chkAuth($chk_page['Authority']['hierarchy'], NC_AUTH_CHIEF)) {
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
 * @param  boolean     $is_child
 *
 * @return boolean
 * @since   v 3.0.0.0
 */
	public function validatorPageDetail($request, $page = null, $parent_page = null, $admin_hierarchy = null, $is_child = false) {
		$lang = Configure::read(NC_CONFIG_KEY.'.'.'language');
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
				if($page['Page']['thread_num'] <= 1 && $page['Page']['space_type'] != NC_SPACE_TYPE_GROUP) {
					//コミュニティー以外のTop Nodeの編集は許さない
					$this->_controller->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'PageMenu.validatorPageDetail.004', '400');
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
					if($page['Page']['space_type'] != NC_SPACE_TYPE_GROUP || $admin_hierarchy < NC_AUTH_MIN_CHIEF) {
						//コミュニティーの表示順変更は$admin_hierarchy=NC_AUTH_MIN_CHIEF以上
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
		if($page['Page']['thread_num'] != 1 && !$this->_controller->CheckAuth->chkAuth($parent_page['Authority']['hierarchy'], NC_AUTH_CHIEF)) {
			$this->_controller->flash(__('Forbidden permission to access the page.'), null, 'PageMenu.validatorMovePage.001', '400');
			return;
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
			if($current_permalink_arr[$child_page['Page']['parent_id']] != ''
					&& !($child_page['Page']['thread_num'] == 2 && $child_page['Page']['display_sequence'] == 1)) {
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

/**
 * Insert Page defaults
 * @param   string     edit(編集) or inner or bottom(追加) $type
 * @param   array      $current_page
 * param   array       $current_page
 * @return  array      array($page, $parent_page)
 * @since   v 3.0.0.0
 */
	public function getDefaultPage($type, $current_page, $parent_page) {
		$lang = Configure::read(NC_CONFIG_KEY.'.'.'language');

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
 * 固定リンクか同じではないページ名称を取得
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
}