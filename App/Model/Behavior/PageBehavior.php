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
	public function setPageName(Model $Model, $page, $type = 0, $alias = 'Page') {
		if(($page[$alias]['thread_num'] == 0 || $page[$alias]['thread_num'] == 1 ||
				($page[$alias]['space_type'] != NC_SPACE_TYPE_PUBLIC && $page[$alias]['thread_num'] == 2 && $page[$alias]['display_sequence'] == 1))) {
			if($type == 0) {
				if($page[$alias]['thread_num'] == 1 && ($page[$alias]['space_type'] == NC_SPACE_TYPE_MYPORTAL || $page[$alias]['space_type'] == NC_SPACE_TYPE_PRIVATE)) {

					App::uses('AuthComponent', 'Controller/Component');
					$user = AuthComponent::user();
					if($page[$alias]['space_type'] == NC_SPACE_TYPE_MYPORTAL) {
						// TODO:config_langsに{X-HANDLE}のマイポータル {X-USERNAME}のマイポータル等のデータを持ち、変換する。
						if($user['myportal_page_id'] == $page[$alias]['id']) {
							$page[$alias]['page_name'] = __('Myportal of %s', $user['handle']);
						} else {
							$User = ClassRegistry::init('User');
							$conditions = array('myportal_page_id' => $page[$alias]['id']);
							$user = $User->find( 'first', array('conditions' => $conditions, 'recursive' => -1) );
							$page[$alias]['page_name'] = __('Myportal of %s', $user['User']['handle']);
						}
					} else {
						// TODO:config_langsに{X-handle}のマイルーム {X-username}のマイルーム等のデータを持ち、変換する。
						if($user['private_page_id'] == $page[$alias]['id']) {
							$page[$alias]['page_name'] = __('Private room of %s', $user['handle']);
						} else {
							$User = ClassRegistry::init('User');
							$conditions = array('User.private_page_id' => $page[$alias]['id']);
							$user = $User->find( 'first', array('conditions' => $conditions, 'recursive' => -1) );
							$page[$alias]['page_name'] = __('Private room of %s', $user['User']['handle']);
						}
					}
				} else if($page[$alias]['thread_num'] == 1 && $page[$alias]['space_type'] == NC_SPACE_TYPE_GROUP) {
					if(!isset($page['CommunityLang']['community_name'])) {
						$lang = Configure::read(NC_CONFIG_KEY.'.'.'language');
						$CommunityLang = ClassRegistry::init('CommunityLang');
						$page['CommunityLang'] = $CommunityLang->find('first', array('conditions' => array('room_id' => $page[$alias]['id'], 'lang' => $lang)));
					}
					if(isset($page['CommunityLang']['community_name'])) {
						$page[$alias]['page_name'] = $page['CommunityLang']['community_name'];
					}
				} else {
					$page[$alias]['page_name'] = __($page[$alias]['page_name']);
				}
			} else if($page[$alias]['thread_num'] <= 1) {
				switch($page[$alias]['space_type']) {
					case NC_SPACE_TYPE_PUBLIC:
						if($page[$alias]['page_name'] == __('Public')) {
							$page[$alias]['page_name'] = "Public room";
						}
						break;
					case NC_SPACE_TYPE_MYPORTAL:
						if($page[$alias]['page_name'] == __('Myportal')) {
							$page[$alias]['page_name'] = "Myportal";
						} else if($page['page_name'] == __('Myportal Top')) {
							$page[$alias]['page_name'] = "Myportal Top";
						}
						break;
					case NC_SPACE_TYPE_PRIVATE:
						if($page[$alias]['page_name'] == __('Private')) {
							$page[$alias]['page_name'] = "Private room";
						} else if($page[$alias]['page_name'] == __('Private Top')) {
							$page[$alias]['page_name'] = "Private Top";
						}
						break;
					case NC_SPACE_TYPE_GROUP:
						if($page[$alias]['page_name'] == __('Community')) {
							$page[$alias]['page_name'] = "Community";
						} else if($page[$alias]['page_name'] == __('Community Top')) {
							$page[$alias]['page_name'] = "Community Top";
						}
						break;
				}
			}
		}
		return $page;
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
 * @return array     $pages['space_type']['thread_num']['parent_id']['display_sequence']
 * @since   v 3.0.0.0
 */
	public function updateDisplayFlag(Model $Model, $val) {
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