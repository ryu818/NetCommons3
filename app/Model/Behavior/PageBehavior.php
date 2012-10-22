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
	function getPermalink(&$Model, $permalink, $space_type) {
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
}