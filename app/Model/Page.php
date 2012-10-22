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
					"table" => "pages_users_links",
					"alias" => "PagesUsersLink",
					"conditions" => "`Page`.`room_id`=`PagesUsersLink`.`room_id`".
						" AND `PagesUsersLink`.`user_id` =".intval($user_id)
			),
			array(
					"type" => "LEFT",
					"table" => "authorities",
					"alias" => "Authority",
					"conditions" => "`Authority`.id``=`PagesUsersLink`.`authority_id`"
			)
		);
		return $ret;
	}
}