<?php
/**
 * Pageモデル
 *
 * <pre>
 *  ページ一覧
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Model
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */

class Page extends AppModel
{
	public $name = 'Page';
	public $actsAs = array('Page');	// , 'Validation'

/**
 * ページリストからページ取得
 * @param   array    $page_id_arr
 * @param   integer  $user_id
 * @return  array    $pages
 * @since   v 3.0.0.0
 */
	public function findByIds($page_id_arr, $user_id) {
		$conditions = array();
		if(is_array($page_id_arr)) {
			foreach($page_id_arr as $page_id) {
				$conditions[0]['or'][]['Page.id'] = $page_id;
			}
		} else {
			$conditions['Page.id'] = $page_id_arr;
		}

		$params = array(
							'fields' => $this->_getFieldsArray(),
							'joins' => $this->_getJoinsArray($user_id),
							'conditions' => $conditions
							);

		if(is_array($page_id_arr)) {
			return $this->afterFindIds($this->find('all', $params));
		}
		$ret = $this->afterFindIds($this->find('first', $params));
		return $ret;
	}

/**
 * afterFind
 * @param   array   $results
 * @return  array   $pages
 * @since   v 3.0.0.0
 */
	public function afterFindIds($results) {
		$pages = array();
		$single_flag = false;
		if(isset($results['Page']['id'])) {
			$single_flag = true;
			$current_page_id = $results['Page']['id'];
			$results = array($results);
		}
		if(is_array($results)) {
			foreach ($results as $key => $val) {
				if(!isset($val['Authority']['hierarchy'])) {
					$val['Authority']['hierarchy'] = $this->getDefaultHierarchy($val['Page']);
				}
				$val['Page'] = $this->setPageName($val['Page']);
				$pages[$val['Page']['id']] = $val;
			}
		}
		if(count($pages) == 0)
			return false;

		if($single_flag) {
			return $pages[$current_page_id];
		}

		return $pages;
	}

	/**
	 * ページメニューのリストを取得
	 *
	 * @param string    $type all or count or list
	 * @param integer   $login_user_id ログイン会員ID
	 * @param integer   $space_type
	 * @param array     $current_user
	 * @param array     $params
	 * @param function  $fetchcallback callback関数 default メニュー形式
	 *                                     $pages[space_type][root_sequence][thread_num][parent_id][display_sequence] = Page
	 * @param array     $fetch_params callback関数 parameter
	 * @param integer   $admin_hierarchy
	 * @return array
	 * @since   v 3.0.0.0
	 */
	public function findMenu($type, $login_user_id, $space_type = NC_SPACE_TYPE_PUBLIC, $current_user = null, $params = null, $fetchcallback = null, $fetch_params = null, $admin_hierarchy = null) {
		//$lang = Configure::read(NC_CONFIG_KEY.'.'.'language');
		$space_type_flag = true;
		if(is_array($space_type)) {
			foreach($space_type as $buf_space_type) {
				if($buf_space_type != NC_SPACE_TYPE_PUBLIC && $buf_space_type != NC_SPACE_TYPE_GROUP) {
					$space_type_flag = false;
					break;
				}
			}
		}
		if($space_type == NC_SPACE_TYPE_PUBLIC || $space_type == NC_SPACE_TYPE_GROUP || (is_array($space_type) && $space_type_flag)) {
			$conditions = array(
					'Page.space_type' => $space_type,
					'Page.position_flag' => _ON,
					'Page.root_sequence !=' => 0
					//'Page.lang' => array('', $lang)
			);
		} else {
			$conditions = array(
					//'Page.space_type' => array(NC_SPACE_TYPE_MYPORTAL, NC_SPACE_TYPE_PRIVATE),
					'Page.position_flag' => _ON,
					'Page.display_flag !=' => NC_DISPLAY_FLAG_DISABLE,
					'Page.root_sequence !=' => 0
					//'Page.lang' => array('', $lang)
			);
			if($login_user_id != $current_user['User']['id'] || $space_type == NC_SPACE_TYPE_MYPORTAL) {
				// マイルームを表示しない。
				$conditions['Page.room_id'] = $current_user['User']['myportal_page_id'];
			} else if($space_type == NC_SPACE_TYPE_PRIVATE) {
				// マイルームのみ
				$conditions['Page.room_id'] = $current_user['User']['private_page_id'];
			} else {
				$conditions['Page.room_id'] = array($current_user['User']['myportal_page_id'], $current_user['User']['private_page_id']);
			}
		}
		if(!isset($params['conditions'])) {
			$params['conditions'] = $conditions;
		} else {
			$params['conditions'] = array_merge($conditions, $params['conditions']);
		}
	
		if($type != 'count' && !isset($params['order'])) {
			$params['order'] = array(
					'Page.space_type' => "ASC",
					'Page.root_sequence' => "ASC",
					'Page.thread_num' => "ASC",
					'Page.display_sequence' => "ASC"
			);
		}
	
		if($type == 'count') {
			unset($params['fields']);
		} else if(empty($login_user_id)) {
			if(!isset($params['fields'])) {
				$params['fields'] = array('Page.*');
			}
		} else {
			if(!isset($params['fields'])) {
				$params['fields'] = $this->_getFieldsArray();
			}
			if(!isset($params['joins'])) {
				$join_type = ($admin_hierarchy < NC_AUTH_MIN_ADMIN && ($space_type == NC_SPACE_TYPE_PUBLIC || $space_type == NC_SPACE_TYPE_GROUP)) ? 'INNER' : 'LEFT';
				$params['joins'] = $this->_getJoinsArray($login_user_id, $join_type);
			}
		}
	
		/*if((isset($params['limit']) || isset($params['page'])) && $space_type == NC_SPACE_TYPE_GROUP &&
				!isset($params['conditions']['Page.display_sequence'])) {
			// ページメニュー：グループルーム編集モード
			$top_params = $params;
			$top_params['fields'] = array('Page.root_sequence');
			$top_params['conditions']['Page.display_sequence'] = 0;
			$top_results = $this->find('list', $top_params);
			if(count($top_results) == 0) {
				return $top_results;
			}
			$params['conditions']['Page.root_sequence'] = $top_results;
			//$params['conditions']['Page.room_id'] = $top_results;
			unset($params['limit']);
			unset($params['page']);
		}*/
	
		if($fetchcallback === "" || ($fetchcallback === null && $type == 'count')) {
			$results = $this->find($type, $params);
		} else if(!is_null($fetchcallback)) {
			$results = call_user_func_array($fetchcallback, array($this->find($type, $params), $fetch_params));
		} else {
			$results = $this->afterFindMenu($this->find($type, $params), $fetch_params);
		}
		return $results;
	}

/**
 * Pageモデル共通Fields文
 * @param   void
 * @return  array   $fields
 * @since   v 3.0.0.0
 */
	protected function _getFieldsArray() {
		return array(
			'Page.*',
			'Authority.myportal_use_flag, Authority.private_use_flag, Authority.hierarchy'
		);
	}

/**
 * Pageモデル共通JOIN文
 * @param   integer $user_id
 * @return  array   $joins
 * @since   v 3.0.0.0
 */
	protected function _getJoinsArray($user_id) {
		$ret = array(
			array(
					"type" => "LEFT",
					"table" => "page_user_links",
					"alias" => "PageUserLink",
					"conditions" => "`Page`.`room_id`=`PageUserLink`.`room_id`".
						" AND `PageUserLink`.`user_id` =".intval($user_id)
			),
			array(
					"type" => "LEFT",
					"table" => "authorities",
					"alias" => "Authority",
					"conditions" => "`Authority`.id``=`PageUserLink`.`authority_id`"
			)
		);
		return $ret;
	}
}