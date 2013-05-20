<?php
/**
 * Page Behavior
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Model.Behavior
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class PageBehavior extends ModelBehavior {
/**
 * マイポータル、マイルーム名称をセット
 *
 * @param Model   $Model
 * @param array    $page
 * @param integer  $type 0:表示時 1:登録時
 * @return boolean
 * @since   v 3.0.0.0
 */
	public function setPageName(Model $Model, $page, $type = 0) {
		if(($page['thread_num'] == 0 || ($page['space_type'] != NC_SPACE_TYPE_GROUP && $page['thread_num'] == 1) ||
				($page['space_type'] != NC_SPACE_TYPE_PUBLIC && $page['thread_num'] == 2 && $page['display_sequence'] == 1))) {
			if($type == 0) {
				if($page['thread_num'] == 1 && ($page['space_type'] == NC_SPACE_TYPE_MYPORTAL || $page['space_type'] == NC_SPACE_TYPE_PRIVATE)) {

					App::uses('AuthComponent', 'Controller/Component');
					$user = AuthComponent::user();
					if($page['space_type'] == NC_SPACE_TYPE_MYPORTAL) {
						// TODO:config_langsに{X-HANDLE}のマイポータル {X-USERNAME}のマイポータル等のデータを持ち、変換する。
						if($user['myportal_page_id'] == $page['id']) {
							$page['page_name'] = __('Myportal of %s', $user['handle']);
						} else {
							App::uses('User', 'Model');
							$User = new User();
							$conditions = array('myportal_page_id' => $page['id']);
							$user = $User->find( 'first', array('conditions' => $conditions, 'recursive' => -1) );
							$page['page_name'] = __('Myportal of %s', $user['User']['handle']);
						}
					} else {
						// TODO:config_langsに{X-handle}のマイルーム {X-username}のマイルーム等のデータを持ち、変換する。
						if($user['private_page_id'] == $page['id']) {
							$page['page_name'] = __('Private room of %s', $user['handle']);
						} else {
							App::uses('User', 'Model');
							$User = new User();
							$conditions = array('User.private_page_id' => $page['id']);
							$user = $User->find( 'first', array('conditions' => $conditions, 'recursive' => -1) );
							$page['page_name'] = __('Private room of %s', $user['User']['handle']);
						}
					}
				} else {
					$page['page_name'] = __($page['page_name']);
				}
			} else {
				switch($page['space_type']) {
					case NC_SPACE_TYPE_PUBLIC:
						if($page['page_name'] == __('Public')) {
							$page['page_name'] = "Public room";
						}
						break;
					case NC_SPACE_TYPE_MYPORTAL:
						if($page['page_name'] == __('Myportal')) {
							$page['page_name'] = "Myportal";
						} else if($page['page_name'] == __('Myportal Top')) {
							$page['page_name'] = "Myportal Top";
						}
						break;
					case NC_SPACE_TYPE_PRIVATE:
						if($page['page_name'] == __('Private')) {
							$page['page_name'] = "Private room";
						} else if($page['page_name'] == __('Private Top')) {
							$page['page_name'] = "Private Top";
						}
						break;
					case NC_SPACE_TYPE_GROUP:
						if($page['page_name'] == __('Community')) {
							$page['page_name'] = "Community";
						}
						break;
				}
			}
		}
		return $page;
	}

/**
 * 会員ID、ページ情報からHierarchyを返す
 * @param  Model        $Model
 * @param  boolean|integer $isLogin(userId)
 * @param  Model Page   $page
 * @return integer hierarchy
 * @since  v 3.0.0.0
 */
	public function getDefaultHierarchy($Model, $page, $isLogin = false) {
		if(isset($page['Authority']['hierarchy'])) {
			return $page['Authority']['hierarchy'];
		}
		if(!isset($page['Community'])  && $page['Page']['space_type'] == NC_SPACE_TYPE_GROUP) {
			App::uses('Community', 'Model');
			$Community = new Community();
			$params = array(
				'fields' => array(
					'Community.publication_range_flag'
				),
				'conditions' => array('room_id' => $page['Page']['root_id']),
			);
			$current_community = $Community->find('first', $params);
			$page['Community'] = $current_community['Community'];
		}

		$hierarchy = NC_AUTH_OTHER;
		if($page['Page']['space_type'] == NC_SPACE_TYPE_PUBLIC) {
			$hierarchy = Configure::read(NC_CONFIG_KEY.'.default_entry_public_hierarchy');
		} else if($page['Page']['space_type'] == NC_SPACE_TYPE_MYPORTAL) {
			$hierarchy = Configure::read(NC_CONFIG_KEY.'.default_entry_myportal_hierarchy');
		} else if($page['Page']['space_type'] == NC_SPACE_TYPE_PRIVATE) {
			$hierarchy = Configure::read(NC_CONFIG_KEY.'.default_entry_private_hierarchy');
		} else if($page['Page']['root_id'] == 0) {
			$hierarchy = NC_AUTH_OTHER_ID;
		} else {
			if($page['Community']['publication_range_flag'] == NC_PUBLICATION_RANGE_FLAG_ONLY_USER ||
				((!isset($isLogin) || intval($isLogin) == 0) && $page['Community']['publication_range_flag'] == NC_PUBLICATION_RANGE_FLAG_LOGIN_USER)) {
				$hierarchy = NC_AUTH_OTHER_ID;
			} else {
				$hierarchy = Configure::read(NC_CONFIG_KEY.'.default_entry_group_hierarchy');
			}
		}

		return $hierarchy;
	}

/**
 * ページにおけるデフォルトの権限を取得
 * @param  Model           $Model
 * @param  boolean|integer $isLogin(userId)
 * @param  Model Page      $page
 * @return integer authority_id
 * @since  v 3.0.0.0
 */
	public function getDefaultAuthorityId($Model, $page, $isLogin = false) {
		if(!isset($page['Community'])  && $page['Page']['space_type'] == NC_SPACE_TYPE_GROUP) {
			App::uses('Community', 'Model');
			$Community = new Community();
			$params = array(
				'fields' => array(
					'Community.publication_range_flag'
				),
				'conditions' => array('room_id' => $page['Page']['root_id']),
			);
			$current_community = $Community->find('first', $params);
			$page['Community'] = $current_community['Community'];
		}
		$authority_id = NC_AUTH_OTHER_ID;
		if($page['Page']['space_type'] == NC_SPACE_TYPE_PUBLIC) {
			$authority_id = Configure::read(NC_CONFIG_KEY.'.default_entry_public_authority_id');
		} else if($page['Page']['space_type'] == NC_SPACE_TYPE_MYPORTAL) {
			$authority_id = Configure::read(NC_CONFIG_KEY.'.default_entry_myportal_authority_id');
		} else if($page['Page']['space_type'] == NC_SPACE_TYPE_PRIVATE) {
			$authority_id = Configure::read(NC_CONFIG_KEY.'.default_entry_private_authority_id');
		} else if($page['Page']['root_id'] == 0) {
			$authority_id = NC_AUTH_OTHER_ID;
		} else {
			if($page['Community']['publication_range_flag'] == NC_PUBLICATION_RANGE_FLAG_ONLY_USER ||
					((!isset($isLogin) || intval($isLogin) == 0) && $page['Community']['publication_range_flag'] == NC_PUBLICATION_RANGE_FLAG_LOGIN_USER)) {
				$authority_id = NC_AUTH_OTHER_ID;
			} else {
				$authority_id = Configure::read(NC_CONFIG_KEY.'.default_entry_group_authority_id');
			}
		}

		/*if($isLogin != _OFF && $authority_id != NC_AUTH_OTHER_ID && $page['Page']['root_id'] != $page['Page']['room_id']) {
			// 子グループ
			// 親ルームが存在するならば、親ルームの参加者権限を取得
			App::uses('Page', 'Model');
			$Page = new Page();
			$parent_page = $Page->findById($page['Page']['parent_id']);
			$parent_room_id = $parent_page['Page']['room_id'];

			App::uses('PageUserLink', 'Model');
			$PageUserLink = new PageUserLink();
			$conditions = array(
				'PageUserLink.room_id' => $parent_room_id,
				'PageUserLink.user_id' => $isLogin
			);
			$params = array(
				'fields' => 'PageUserLink.authority_id',
				'conditions' => $conditions
			);
			$page_user_link = $PageUserLink->find('first', $params);
			$authority_id = $page_user_link['PageUserLink']['authority_id'];
		}*/
		return $authority_id;
	}

/**
 * 各スペースタイプ毎のprefixを付加
 *
 * @param  Model     $Model
 * @param  string    $permalink
 * @param  integer   $space_type
 * @return string    $ret_permalink
 * @since   v 3.0.0.0
 */
	public function getPermalink(Model $Model, $permalink, $space_type) {
		if($space_type == NC_SPACE_TYPE_PUBLIC) {
			$ret_permalink = NC_SPACE_PUBLIC_PREFIX;
		} else if($space_type == NC_SPACE_TYPE_MYPORTAL) {
			$ret_permalink = NC_SPACE_MYPORTAL_PREFIX;
		} else if($space_type == NC_SPACE_TYPE_PRIVATE) {
			$ret_permalink = NC_SPACE_PRIVATE_PREFIX;
		} else {
			$ret_permalink = NC_SPACE_GROUP_PREFIX;
		}
		if($ret_permalink != '')
			$ret_permalink .= '/';
		if($permalink != '')
			$ret_permalink .= $permalink.'/';

		return $ret_permalink;
	}

/**
 * ページメニューの階層構造を表示するafterコールバック
 *
 * @param  Model     $Model
 * @param  array     $results
 * @param  boolean   $isLogin
 * @param  array     $fetch_params
 * @return array     $pages['space_type']['thread_num']['parent_id']['display_sequence']
 * @since   v 3.0.0.0
 */
	public function afterFindMenu(Model $Model, $results, $isLogin = false, $fetch_params = null) {
		$pages = array();
		$lang = Configure::read(NC_CONFIG_KEY.'.'.'language');
		$active_page_id = isset($fetch_params['active_page_id']) ? $fetch_params['active_page_id'] : null;

		if(!empty($active_page_id)) {
			$buf_pages = array();
			foreach ($results as $key => $val) {
				$buf_pages[$val[$Model->alias]['id']] = $val[$Model->alias]['parent_id'];
			}
			$active_id_arr = array($active_page_id => true);
			$buf_page_id = $active_page_id;
			while(1) {
				if(!empty($buf_pages[$buf_page_id])) {
					$active_id_arr[$buf_pages[$buf_page_id]] = true;
					$buf_page_id = $buf_pages[$buf_page_id];
				} else {
					break;
				}
			}
		}

		$active_lang = null;
		$active_room_id = null;
		//$parent_id_arr = array();
		$permalink_arr = array();
		$node_top_arr = array();
		$parent_display_arr = array();
		foreach ($results as $key => $val) {
			if(($val[$Model->alias]['thread_num'] == 1 || $val[$Model->alias]['display_sequence'] == 1) && $val[$Model->alias]['lang'] == $lang) {
				// NodeTop
				$node_top_arr[$val[$Model->alias]['space_type']] = true;
			}
			if($val[$Model->alias]['lang'] != '' && $val[$Model->alias]['lang'] != $lang && isset($node_top_arr[$val[$Model->alias]['space_type']])) {
				// 現在の言語でTopNodeがある場合、他の言語は表示しない。
				continue;
			}

			if(isset($permalink_arr[$val[$Model->alias]['space_type']][$val[$Model->alias]['permalink']])) {
				continue;
			} else if($val[$Model->alias]['lang'] == $lang) {
				$permalink_arr[$val[$Model->alias]['space_type']][$val[$Model->alias]['permalink']] = $val[$Model->alias]['permalink'];
			} else if($val[$Model->alias]['lang'] != '' &&
					$val[$Model->alias]['display_sequence'] != 0 && ($active_lang === '' || $active_lang == 'en')) {
				continue;
			}

			$pre_display_flag = $val[$Model->alias]['display_flag'];


			$val[$Model->alias] = $this->updDisplayFlag($Model, $val[$Model->alias]);

			if($pre_display_flag != $val[$Model->alias]['display_flag'] &&
					($val[$Model->alias]['display_flag'] == _OFF ||
						($val[$Model->alias]['display_flag'] == _ON && $val[$Model->alias]['display_apply_subpage'] == _ON))) {
				// 親が非公開ならば、子供が公開になっていても非公開として表示。
				// 公開日付Toを設定直後に親が非公開、子供が公開で表示されてしまうため
				// 親が公開で、「下位ページにも適用」のチェックボックスがONの場合も同様。
				$parent_display_arr[$val[$Model->alias]['id']] = $val[$Model->alias]['display_flag'];
			}
			if(isset($parent_display_arr[$val[$Model->alias]['parent_id']])) {
				$val[$Model->alias]['display_flag'] = $parent_display_arr[$val[$Model->alias]['parent_id']];
			}

			//if($val[$Model->alias]['thread_num'] >= 2 && !isset($parent_id_arr[$val[$Model->alias]['parent_id']])) {
			//	// 親がなし
			//	continue;
			//}

			if($val[$Model->alias]['display_sequence'] != 0 && $active_room_id != $val[$Model->alias]['room_id']) {
				$active_lang = $val[$Model->alias]['lang'];
				$active_room_id = $val[$Model->alias]['room_id'];
			}

			if(!empty($active_page_id)) {
				if(!empty($active_id_arr[$val[$Model->alias]['id']])) {
					$val[$Model->alias]['active'] = true;
				} else {
					$val[$Model->alias]['active'] = false;
				}

				if(isset($active_id_arr[$val[$Model->alias]['parent_id']]) || $val[$Model->alias]['thread_num'] <= 1) {	// || $val[$Model->alias]['thread_num'] <= 2
					$val[$Model->alias]['show'] = true;
				} else {
					$val[$Model->alias]['show'] = false;
				}
			}
			//$val[$Model->alias]['hierarchy'] = isset($val['Authority']['hierarchy']) ? $val['Authority']['hierarchy'] : NC_AUTH_OTHER;
			if(!isset($val['Authority']['hierarchy'])) {
				$val['Authority']['hierarchy'] = $this->getDefaultHierarchy($Model, $val, $isLogin);
			}

			if(isset($val['CommunityLang']['community_name'])) {
				$val[$Model->alias]['page_name'] = $val['CommunityLang']['community_name'];
			}

			$val[$Model->alias]['visibility_flag'] = empty($val['Menu']['visibility_flag']) ? _ON : $val['Menu']['visibility_flag'];
			$val[$Model->alias]['permalink'] = $this->getPermalink($Model, $val[$Model->alias]['permalink'], $val[$Model->alias]['space_type']);
			$val[$Model->alias] = $this->setPageName($Model, $val[$Model->alias]);
			$pages[$val[$Model->alias]['space_type']][$val[$Model->alias]['thread_num']][$val[$Model->alias]['parent_id']][$val[$Model->alias]['display_sequence']] = $val;
			//$parent_id_arr[$val[$Model->alias]['id']] = $val[$Model->alias]['id'];
		}
		return $pages;
	}

/**
 * ページメニューの階層構造を表示するafterコールバック
 *
 * @param  Model     $Model
 * @param  array     $results
 * @param  array     $fetch_params
 * @return array     $pages['space_type']['thread_num']['parent_id']['display_sequence']
 * @since   v 3.0.0.0
 */
	public function updDisplayFlag(Model $Model, $val) {
		$d = gmdate("Y-m-d H:i:s");

		// 公開日時
		if(!empty($val['display_from_date']) && $val['display_flag'] != NC_DISPLAY_FLAG_DISABLE &&
				strtotime($val['display_from_date']) <= strtotime($d)) {

			$page_id_arr = array($val['id']);

			if($val['display_apply_subpage'] == _ON) {
				$current_page['Page'] = $val;
				$child_pages = $Model->findChilds('list', $current_page);
				if(count($child_pages) > 0) {
					foreach($child_pages as $page_id => $v) {
						$page_id_arr[] = $page_id;
					}
				}
			}

			$val['display_flag'] = NC_DISPLAY_FLAG_ON;

			$fields = array(
				'display_flag' => $val['display_flag'],
				'display_from_date' => null
			);
			$conditions = array(
				'id' => $page_id_arr
			);
			$result = $Model->updateAll($fields, $conditions);
		}

		if(!empty($val['display_to_date']) && $val['display_flag'] != NC_DISPLAY_FLAG_DISABLE &&
				strtotime($val['display_to_date']) <= strtotime($d)) {
			// 現在のページ以下のページを取得
			if(!isset($child_pages)) {
				$current_page['Page'] = $val;
				$child_pages = $Model->findChilds('list', $current_page);
			}
			$page_id_arr = array($val['id']);
			if(count($child_pages) > 0) {
				foreach($child_pages as $page_id => $v) {
					$page_id_arr[] = $page_id;
				}
			}

			$val['display_flag'] = NC_DISPLAY_FLAG_OFF;

			$fields = array(
				'display_flag' => $val['display_flag'],
				'display_to_date' => null
			);
			$conditions = array(
				'id' => $page_id_arr
			);
			$result = $Model->updateAll($fields, $conditions);
		}

		return $val;
	}
}