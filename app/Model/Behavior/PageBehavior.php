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
 * @param integer  $type
 * @return boolean
 * @since   v 3.0.0.0
 */
	public function setPageName(Model $Model, $page, $type = 0) {
		if($type == 0) {
			if(($page['page_name'] == "Public room" || $page['page_name'] == "Myportal"
					|| $page['page_name'] == "Private room" || $page['page_name'] == "Community") &&
				$page['thread_num'] <= 2 && $page['display_sequence'] <= 1) {
				if(($page['thread_num'] == 1 || $page['thread_num'] == 2) && $page['space_type'] == NC_SPACE_TYPE_MYPORTAL) {
					$page['page_name'] = Configure::read(NC_AUTH_KEY.'.'.'myportal_name');
				} else if(($page['thread_num'] == 1 || $page['thread_num'] == 2) && $page['space_type'] == NC_SPACE_TYPE_PRIVATE) {
					$page['page_name'] = Configure::read(NC_AUTH_KEY.'.'.'private_name');
				} else {
					$page['page_name'] = __($page['page_name'], true);
				}
			}
		} else {
			if($page['thread_num'] <= 2 && $page['display_sequence'] <= 1) {
				if($page['page_name'] == __('Public', true) &&
					$page['space_type'] == NC_SPACE_TYPE_PUBLIC) {
					$page['page_name'] = "Public room";
				} else if($page['page_name'] == __('Myportal', true) &&
					$page['space_type'] == NC_SPACE_TYPE_MYPORTAL) {
					$page['page_name'] = "Myportal";
				} else if($page['page_name'] == __('Private room', true) &&
					$page['space_type'] == NC_SPACE_TYPE_PRIVATE) {
					$page['page_name'] = "Private room";
				} else if($page['page_name'] == __('Community', true) &&
					$page['space_type'] == NC_SPACE_TYPE_GROUP) {
					$page['page_name'] = "Community";
				}
				if($page['space_type'] == NC_SPACE_TYPE_MYPORTAL) {
					$page_name = Configure::read(NC_AUTH_KEY.'.'.'myportal_name');
					$def_page_name = "Myportal";
				} else if($page['space_type'] == NC_SPACE_TYPE_PRIVATE) {
					$page_name = Configure::read(NC_AUTH_KEY.'.'.'private_name');
					$def_page_name = "Private room";
				}
				if(isset($page_name) && $page['page_name'] == $page_name) {
					$page['page_name'] = $def_page_name;
				}
			}
		}
		return $page;
	}

/**
 * HierarchyのDefault値取得
 *
 * @param Model    $Model
 * @param array    $page
 * @return integer  $hierarchy
 * @since   v 3.0.0.0
 */
	public function getDefaultHierarchy(Model $Model, $page) {
		$id = Configure::read(NC_AUTH_KEY.'.'.'id');

		// configの値から初期権限を付与する
		if(!isset($id)) {
			if($page['space_type'] == NC_SPACE_TYPE_PUBLIC || $page['space_type'] == NC_SPACE_TYPE_MYPORTAL) {
				$hierarchy = NC_AUTH_GUEST;
			} else {
				$hierarchy = NC_AUTH_OTHER;
			}
		} else if($page['space_type'] == NC_SPACE_TYPE_PUBLIC) {
			$hierarchy = Configure::read(NC_CONFIG_KEY.'.default_entry_public_hierarchy');
		} else if($page['space_type'] == NC_SPACE_TYPE_MYPORTAL) {
			$hierarchy = Configure::read(NC_CONFIG_KEY.'.default_entry_myportal_hierarchy');
		} else if($page['space_type'] == NC_SPACE_TYPE_PRIVATE && $page['default_entry_flag'] == _ON) {
			$hierarchy = Configure::read(NC_CONFIG_KEY.'.default_entry_private_hierarchy');
		} else if($page['space_type'] == NC_SPACE_TYPE_GROUP && $page['default_entry_flag'] == _ON) {
			$hierarchy = Configure::read(NC_CONFIG_KEY.'.default_entry_group_hierarchy');
		} else {
			$hierarchy = NC_AUTH_OTHER;
		}
		return $hierarchy;
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
 * @param  array     $fetch_params
 * @return array     $pages['space_type'][$val['root_sequence']['thread_num']['parent_id']['display_sequence']
 * @since   v 3.0.0.0
 */
	public function afterFindMenu(Model $Model, $results, $fetch_params = null) {
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
		foreach ($results as $key => $val) {
			if(($val[$Model->alias]['root_sequence'] == 1 || $val[$Model->alias]['display_sequence'] == 1) && $val[$Model->alias]['lang'] == $lang) {
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
	
			$val[$Model->alias] = $this->updDisplayFlag($Model, $val[$Model->alias]);
	
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
				$val[$Model->alias]['hierarchy'] = $this->getDefaultHierarchy($Model, $val[$Model->alias]);
			} else {
				$val[$Model->alias]['hierarchy'] = $val['Authority']['hierarchy'];
			}
	
			$val[$Model->alias]['visibility_flag'] = empty($val['Menu']['visibility_flag']) ? _ON : $val['Menu']['visibility_flag'];
	
			$val[$Model->alias]['permalink'] = $this->getPermalink($Model, $val[$Model->alias]['permalink'], $val[$Model->alias]['space_type']);
			$val[$Model->alias] = $this->setPageName($Model, $val[$Model->alias]);
			$pages[$val[$Model->alias]['space_type']][$val[$Model->alias]['root_sequence']][$val[$Model->alias]['thread_num']][$val[$Model->alias]['parent_id']][$val[$Model->alias]['display_sequence']] = $val[$Model->alias];
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
 * @return array     $pages['space_type'][$val['root_sequence']['thread_num']['parent_id']['display_sequence']
 * @since   v 3.0.0.0
 */
	public function updDisplayFlag(Model $Model, $val) {
		$d = gmdate("Y-m-d H:i:s");
	
		// 公開日付・非公開日時
		if(!empty($val['display_date']) && $val['display_flag'] != NC_DISPLAY_FLAG_DISABLE &&
				strtotime($val['display_date']) <= strtotime($d)) {
			$val['display_flag'] = ($val['display_flag']) ? NC_DISPLAY_FLAG_OFF : NC_DISPLAY_FLAG_ON;
			$data[$Model->alias] = array(
					'id' => $val['id'],
					'display_flag' => $val['display_flag'],
					'display_date' => null
			);
			$Model->create();
			$ret = $Model->save($data, true, array('display_flag', 'display_date'));
		}
	
		return $val;
	}
}