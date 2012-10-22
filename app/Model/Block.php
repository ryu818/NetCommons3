<?php
/**
 * Blockモデル
 *
 * <pre>
 *  ブロック一覧
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Model
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */

class Block extends AppModel
{
	public $name = 'Block';
	// public $belongsTo = array('Module');

/**
 * block_id,user_idから該当ブロックを取得
 * @param   integer $block_id
 * @param   integer $user_id
 * @param   boolean $afterFind_flag	コールバックを呼ぶかどうか
 * @return  array   $block
 * @since   v 3.0.0.0
 */
	public function findByUserId($block_id, $user_id, $afterFind_flag = true) {
		$conditions['Block.id'] = $block_id;
		$params = array(
			'fields' => $this->_getFieldsArray(),
			'joins' => $this->_getJoinsArray($user_id),
			'conditions' => $conditions
		);

		if(!$afterFind_flag)
			return $this->find('first', $params);
		return $this->afterFindDefault($this->find('first', $params));
	}

	public function findUsers($type, $conditions, $user_id) {
		$params = array(
						'fields' => $this->_getFieldsArray(),
						'joins' => $this->_getJoinsArray($user_id),
						'conditions' => $conditions
						);

		return $this->find($type, $params);
	}
/**
 * afterFind
 * @param   array   $val
 * @param   boolean $check_auth	権限チェックをするかどうか
 * @return  array   $block
 * @since   v 3.0.0.0
 */
	public function afterFindDefault($val, $check_auth = true) {
		$d = gmdate("Y-m-d H:i:s");
		// 公開日付・非公開日時
		if(!empty($val['Block']['display_from_date']) && strtotime($val['Block']['display_from_date']) <= strtotime($d)) {
			$val['Block']['display_flag'] = ($val['Block']['display_flag']) ? NC_DISPLAY_FLAG_OFF : NC_DISPLAY_FLAG_ON;
			$val['Block']['display_from_date'] = $val['Block']['display_to_date'];
			$val['Block']['display_to_date'] = null;
			$this->create();
			$this->id = $val['Block']['id'];
			$this->save($val, true, array('display_flag', 'display_from_date', 'display_to_date'));
		}

		if($check_auth && isset($val['Content']['accept_flag']) &&
			(($val['Content']['accept_flag'] != NC_ACCEPT_FLAG_ON || $val['Block']['display_flag'] == _OFF) && $val['Authority']['hierarchy'] < NC_AUTH_MIN_CHIEF)) {
			// 非公開
			return;
		}

		// class name
		if($val['Block']['controller_action'] == "group"){
			$val['Block']['class_name'] = "nc_group";
		} else{
			$val['Block']['class_name'] = "nc_block";
		}

		if(!isset($val['Block']['theme_name'])) {
			return $val;
		}

		if($val['Block']['theme_name'] == '') {
			// TODO:test
			//$page_style_arr = Configure::read(NC_SYSTEM_KEY.'.page_style_arr');
			//$theme_name = $page_style_arr[$val['Block']['page_id']];
			$theme_name = 'Default.gray';
			$val['Block']['theme_name'] = $theme_name;
		}

		// array_merge();
		$val['Block']['room_id'] = $val['Content']['room_id'];
		$val['Block']['is_master'] = $val['Content']['is_master'];
		$val['Block']['master_id'] = $val['Content']['master_id'];
		$val['Block']['accept_flag'] = isset($val['Content']['accept_flag']) ? $val['Content']['accept_flag'] : NC_ACCEPT_FLAG_ON;
		//$val['Block']['url'] = $val['Content']['url'];
		$val['Block']['dir_name'] = $val['Module']['dir_name'];
		$val['Block']['edit_controller_action'] = $val['Module']['edit_controller_action'];

		$val['Block']['hierarchy'] = $val['Authority']['hierarchy'];
		if($val['Block']['title'] != '') {
			if($val['Block']['title'] == "{X-CONTENT}") {
				$val['Block']['title'] = $val['Content']['title'];
				$val['Block']['block_only'] = _ON;
			}
		}

		if(!isset($val['Authority']['hierarchy'])) {
			// configの値から初期権限を付与する
			$id = Configure::read(NC_AUTH_KEY.'.'.'id');
			if(!isset($id)) {
				if($val['Page']['space_type'] == NC_SPACE_TYPE_PUBLIC) {
					$val['Block']['hierarchy'] = NC_AUTH_GUEST;
				} else {
					$val['Block']['hierarchy'] = NC_AUTH_OTHER;
				}
			} else if($val['Page']['space_type'] == NC_SPACE_TYPE_PUBLIC) {
				$val['Block']['hierarchy'] = Configure::read(NC_CONFIG_KEY.'.default_entry_public_hierarchy');
			} else if($val['Page']['space_type'] == NC_SPACE_TYPE_MYPORTAL) {
				$val['Block']['hierarchy'] = Configure::read(NC_CONFIG_KEY.'.default_entry_myportal_hierarchy');
			} else if($val['Page']['space_type'] == NC_SPACE_TYPE_PRIVATE) {
				$val['Block']['hierarchy'] = Configure::read(NC_CONFIG_KEY.'.default_entry_private_hierarchy');
			} else {
				$val['Block']['hierarchy'] = Configure::read(NC_CONFIG_KEY.'.default_entry_group_hierarchy');
			}
		} else {
			$val['Block']['hierarchy'] = $val['Authority']['hierarchy'];
		}
		unset($val['Page']);
		unset($val['Authority']);
		//unset($val['Content']);
		//unset($val['Module']);
		return $val;
	}

/**
 * ページリストからブロック取得
 * @param   array    $page_id_arr
 * @param   integer  $user_id
 * @param   function $fetchcallback
 * @param   array    $fetch_params
 * @return  array    $blocks
 * @since   v 3.0.0.0
 */
	public function findByPageIds($page_id_arr, $user_id, $fetchcallback = null, $fetch_params = null) {
		$conditions = array();
		if(is_array($page_id_arr)) {
			foreach($page_id_arr as $page_id) {
				$conditions[0]['or'][]['Block.page_id'] = $page_id;
			}
		} else {
			$conditions['Block.page_id'] = $page_id_arr;
		}
		$params = array(
			'fields' => $this->_getFieldsArray(),
			'joins' => $this->_getJoinsArray($user_id),
			'conditions' => $conditions,
			'order' => $this->_getOrderArray(),
		);

		//if($display_flag == NC_DISPLAY_FLAG_ON) {
		//	$params['joins'][0]['type'] = "LEFT";
		//}

		if($fetchcallback === "") {
			$results = $this->find('all', $params);
		} else if(!is_null($fetchcallback)) {
			$results = call_user_func_array($fetchcallback, array($this->find('all', $params), $fetch_params));
		} else {
			$results = $this->_afterFind($this->find('all', $params), $fetch_params);
		}
		return $results;
	}

/**
 * root_idからblock配列を取得する
 * @param  integer root_id
 * @return array block
 */
	function findByRootId($root_id)
	{
		$conditions = array(
			"root_id" => $root_id
		);
		$params = array(
			'fields' => array(
								'Block.*'
			),
			'conditions' => $conditions,
			'order' => 'thread_num'
		);

		return $this->find('all', $params);
	}
/**
 * グループ化したブロックからブロック取得
 * @param   array    $block
 * @param   integer  $user_id
 * @return  array    $blocks
 * @since   v 3.0.0.0
 */
	function findByGroupId($block, $user_id) {
		$conditions = array(
			'Block.root_id' => $block['Block']['root_id'],
			'Block.thread_num >=' => $block['Block']['thread_num']
		);
		$params = array(
			'fields' => $this->_getFieldsArray(),
			'joins' => $this->_getJoinsArray($user_id),
			'conditions' => $conditions,
			'order' => $this->_getOrderArray(),
		);
		return $this->_afterFind($this->find('all', $params), array('group_block_id' => $block['Block']['id']));
	}
/**
 * afterFind
 * @param   array   $results
 * @param   array   $fetch_params
 * @return  array   $blocks
 * @since   v 3.0.0.0
 */
	protected function _afterFind($results, $fetch_params = array()) {
		$blocks = array();
		$group_block_id = null;
		if(isset($fetch_params['group_block_id'])) {
			$group_block_id = $fetch_params['group_block_id'];
			$parent_id_arr = array($fetch_params['group_block_id']);
		}

		foreach ($results as $key => $val) {
			// 公開日付・非公開日付
			$val = $this->afterFindDefault($val);
			if(empty($val))
				continue;
			if($group_block_id && ($val['Block']['id'] == $group_block_id ||
				in_array($val['Block']['parent_id'], $parent_id_arr))) {
				$parent_id_arr[] = $val['Block']['id'];
			} else if($group_block_id) {
				continue;
			}
			$blocks[$val['Block']['page_id']][$val['Block']['parent_id']][$val['Block']['col_num']][$val['Block']['row_num']] = $val;
		}

		if(count($blocks) == 0)
			return false;

		return $blocks;
	}

/**
 * Blockモデル共通Fields文
 * @param   void
 * @return  array   $fields
 * @since   v 3.0.0.0
 */
	protected function _getFieldsArray() {
		return array(
			'Block.*',
			'Page.space_type',
			'Content.id','Content.module_id','Content.title','Content.is_master','Content.master_id','Content.room_id','Content.accept_flag','Content.url',
			'Module.id','Module.edit_controller_action','Module.dir_name','Module.content_has_one',
			'Authority.id','Authority.hierarchy'
		);
	}

/**
 * Blockモデル共通JOIN文
 * @param   integer $user_id
 * @return  array   $joins
 * @since   v 3.0.0.0
 */
	protected function _getJoinsArray($user_id) {
		$ret = array(
			array("type" => "LEFT",
				"table" => "contents",
				"alias" => "Content",
				"conditions" => "`Block`.`content_id`=`Content`.`id`"
			),
			array("type" => "LEFT",
				"table" => "pages_users_links",
				"alias" => "PagesUsersLink",
				"conditions" => "`Content`.`room_id`=`PagesUsersLink`.`room_id`".
				" AND `PagesUsersLink`.`user_id` =".intval($user_id)
			),
			array("type" => "LEFT",
				"table" => "authorities",
				"alias" => "Authority",
				"conditions" => "`Authority`.id``=`PagesUsersLink`.`authority_id`"
			),
			array("type" => "LEFT",
				"table" => "pages",
				"alias" => "Page",
				"conditions" => "`Content`.`room_id`=`Page`.`id`"
			),
			array("type" => "LEFT",
				"table" => "modules",
				"alias" => "Module",
				"conditions" => "`Module`.`id`=`Block`.`module_id`"
			)
		);
		return $ret;
	}

/**
 * Blockモデル共通Order文
 * @param   void
 * @return  array   $fields
 * @since   v 3.0.0.0
 */
	protected function _getOrderArray() {
		return array(
			'Block.thread_num' => "ASC",
			'Block.col_num' => "ASC",
			'Block.row_num' => "ASC"
		);
	}

/**
 * ブロック削除処理
 * 再帰的に処理
 *
 * @param object  $block
 * @param boolean $all_delete コンテンツもすべて削除するかどうか
 * @return	boolean true or false
 * @access	public
 **/
	public function deleteBlock($block, $all_delete = _OFF)
	{
		App::uses('Content', 'Model');
		$Content = new Content();

		$controller_action = $block['Block']['controller_action'];
		$page_id = $block['Block']['page_id'];
		$module_id = $block['Block']['module_id'];
		$block_id = $block['Block']['id'];
		$content_id = $block['Block']['content_id'];
		if(isset($block['Content'])) {
			$content = $block;
		} else {
			$content = $Content->findById($content_id);
		}

		if($controller_action == "group") {
			// --------------------------------------
			// --- 子供に位置するモジュール削除   ---
			// --------------------------------------
			//子供取得
			$child_blocks = $this->find('all', array(
				'recursive' => -1,
				'conditions' => array(
					'Block.parent_id' => $block['Block']['id']
				)
			));
			foreach($child_blocks as $child_block) {
				//再帰処理
				$ret = $this->deleteBlock($child_block);
				if(!$ret) {
					return false;
				}
			}
		} else {
			// -------------------------------------
			// --- 削除関数                      ---
			// -------------------------------------
			App::uses('Page', 'Model');
			$Page = new Page();
			App::uses('Module', 'Model');
			$Module = new Module();
			$module = $Module->findById($module_id);
			$page = $Page->findById($page_id);
			if(!empty($module['Module'])) {
				// ルームが異なるコンテンツは削除アクションを行わない。
				$plugin = $module['Module']['dir_name'];
				App::uses($plugin.'OperationsComponent', 'Plugin/'.$plugin.'/Controller/Component');
				$class_name = $plugin.'OperationsComponent';
				if(class_exists($class_name)) {
					eval('$class = new '.$class_name.'();');
					$class->startup();
					if(method_exists($class_name,'delete_block')) {
						// ブロック削除アクション
						$ret = $class->delete_block($block);
						if(!$ret) {
							return false;
						}
					}

					if($all_delete &&
						$page['Page']['room_id'] == $content['Content']['room_id'] && method_exists($class_name, 'delete')) {
						// 削除アクション
						$ret = $class->delete($content);
						if(!$ret) {
							return false;
						}
					}
				}

				if(!$content['Content']['is_master'] ||
					($all_delete && $page['Page']['room_id'] == $content['Content']['room_id'])) {

					// 権限が付与されたショートカットか、ショートカットではない場合
					if(!$Content->delete($block['Block']['content_id'])) {
						return false;
					}
				}
			}
		}

		// --------------------------------------
		// --- ブロック削除処理     	      ---
		// --------------------------------------
		if(!$this->delete($block_id)) {
			return false;
		}
		// --------------------------------------------------------------------------------------
		// --- contents 削除処理     	                                                      ---
		// --- group化ブロック、または、権限が付与されたショートカット              	      ---
		// --------------------------------------------------------------------------------------
		if(isset($content['Content']) && ($content['Content']['module_id'] == 0 || !$content['Content']['is_master'])) {
			if(!$Content->delete($block['Block']['content_id'])) {
				return false;
			}
		}
		return true;
	}
}