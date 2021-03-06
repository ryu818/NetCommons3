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
	// public $belongsTo = array('Module');

	public $actsAs = array('Page', 'TimeZone', 'Validation', 'Auth' => array('joins' => false, 'afterFind' => false));

	// 公開日付をsaveする前に変換するかどうかのフラグ
	// public $autoConvert = true;

/**
 * construct
 * @param integer|string|array $id Set this ID for this model on startup, can also be an array of options, see above.
 * @param string $table Name of database table to use.
 * @param string $ds DataSource connection name.
 * @return  void
 * @since   v 3.0.0.0
 */
	public function __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);

		/*
		 * エラーメッセージ設定
		 */
		$this->validate = array(
			'page_id' => array(
				'numeric' => array(
								'rule' => array('numeric'),
								'allowEmpty' => false,
								'message' => __('The input must be a number.')
							)
			),
			'content_id' => array(
					'numeric' => array(
						'rule' => array('numeric'),
						'allowEmpty' => false,
						'message' => __('The input must be a number.')
					)
			),
			'module_id' => array(
					'numeric' => array(
						'rule' => array('numeric'),
						'allowEmpty' => false,
						'message' => __('The input must be a number.')
					)
			),
			'title' => array(
				'notEmpty'  => array(
									'rule' => array('notEmpty'),
									'last' => true,
									'required' => true,
									'allowEmpty' => false,
									'message' => __('Please be sure to input.')
								),
				'maxLength'  => array(
									'rule' => array('maxLength', NC_VALIDATOR_BLOCK_TITLE_LEN),
									'message' => __('The input must be up to %s characters.', NC_VALIDATOR_BLOCK_TITLE_LEN)
								)
			),
			'show_title' => array(
				'boolean'  => array(
									'rule' => array('boolean'),
									'last' => true,
									'required' => true,
									'message' => __('The input must be a boolean.')
								)
			),

			'display_flag' => array(
				'boolean'  => array(
									'rule' => array('boolean'),
									'last' => true,
									'required' => true,
									'message' => __('The input must be a boolean.')
								)
			),
			'display_from_date' => array(
				'datetime'  => array(
					'rule' => array('datetime'),
					'last' => true,
					'allowEmpty' => true,
					'message' => __('Unauthorized pattern for %s.', __('Date-time'))
				),
				'isFutureDateTime'  => array(
					'rule' => array('isFutureDateTime'),
					'last' => true,
					'allowEmpty' => true,
					'message' => __('%s in the past can not be input.', __('Date-time'))
				),
				'invalidDisplayFromDate'  => array(
					'rule' => array('invalidDisplayFromDate'),
					'last' => true,
					'allowEmpty' => true,
					'message' => __('Because the page is not a private, You can\'t set a publish date.')
				),
			),
			'display_to_date' => array(
				'datetime'  => array(
					'rule' => array('datetime'),
					'last' => true,
					'allowEmpty' => true,
					'message' => __('Unauthorized pattern for %s.', __('Date-time'))
				),
				'isFutureDateTime'  => array(
					'rule' => array('isFutureDateTime'),
					'last' => true,
					'allowEmpty' => true,
					'message' => __('%s in the past can not be input.', __('Date-time'))
				),
				'invalidDisplayToDate'  => array(
					'rule' => array('invalidDisplayToDate'),
					'last' => true,
					'allowEmpty' => true,
					'message' => __('Because the page is not published, You can\'t set a closed date.')
				),
				'invalidDisplayFromToDate'  => array(
					'rule' => array('invalidDisplayFromToDate'),
					'last' => true,
					'allowEmpty' => true,
					'message' => __('Please input in [publish date < closed date].')
				),
			),
			/* TODO:未作成
			 * 'temp_name' => array(

			),*/
			'theme_name' => array(
				'existsTheme'  => array(
									'rule' => array('existsTheme'),
									'last' => true,
									'allowEmpty' => true,
									'message' => __('Content not found.')
								)
			),
			'left_margin' => array(
				'range'  => array(
								'rule' => array('range', -1, 1000),
								'last' => true,
								'allowEmpty' => false,
								'message' => __('The input must be a number bigger than %d and less than %d.', 0, 999)
							)
			),
			'right_margin' => array(
				'range'  => array(
								'rule' => array('range', -1, 1000),
								'last' => true,
								//'required' => true,
								'allowEmpty' => false,
								'message' => __('The input must be a number bigger than %d and less than %d.', 0, 999)
							)
			),
			'top_margin' => array(
				'range'  => array(
								'rule' => array('range', -1, 1000),
								'last' => true,
								'allowEmpty' => false,
								'message' => __('The input must be a number bigger than %d and less than %d.', 0, 999)
							)
			),
			'bottom_margin' => array(
				'range'  => array(
								'rule' => array('range', -1, 1000),
								'last' => true,
								'allowEmpty' => false,
								'message' => __('The input must be a number bigger than %d and less than %d.', 0, 999)
							)
			),
			'min_width_size' => array(
				'range'  => array(
								'rule' => array('range', -2, 2001),
								'last' => true,
								'allowEmpty' => false,
								'message' => __('The input must be a number bigger than %d and less than %d.', 0, 2000)
							)
			),
			'min_height_size' => array(
				'range'  => array(
								'rule' => array('range', -2, 2001),
								'last' => true,
								'allowEmpty' => false,
								'message' => __('The input must be a number bigger than %d and less than %d.', 0, 2000)
							)
			),
			/* TODO:未作成
			 * 'lock_authority_id' => array(

			 ),*/
	    );
	}

/**
 * beforeSave
 * @param   array  $options
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function beforeSave($options = array()) {
		//if(!$this->autoConvert) {
		//	return true;
		//}
		if (!empty($this->data['Block']['display_from_date']) ) {
			$this->data['Block']['display_from_date'] = $this->dateUtc($this->data['Block']['display_from_date']);
		}
		if (!empty($this->data['Block']['display_to_date']) ) {
			$this->data['Block']['display_to_date'] = $this->dateUtc($this->data['Block']['display_to_date']);
		}
		return true;
	}

/**
 * block_id,user_idから該当ブロックを取得
 * @param   integer $blockId
 * @param   integer $userId
 * @param   boolean $afterFindFlag	コールバックを呼ぶかどうか
 * @return  Block   $block
 * @since   v 3.0.0.0
 */
	public function findAuthById($blockId, $userId, $afterFindFlag = true) {
		$conditions['Block.id'] = $blockId;
		$params = array(
			'fields' => $this->_getFieldsArray(),
			'joins' => $this->_getJoinsArray($userId),
			'conditions' => $conditions
		);

		if(!$afterFindFlag)
			return $this->find('first', $params);
		return $this->afterFindDefault($this->find('first', $params), null, 'Page', true);
	}

	public function findUsers($type, $conditions, $userId) {
		$params = array(
						'fields' => $this->_getFieldsArray(),
						'joins' => $this->_getJoinsArray($userId),
						'conditions' => $conditions
						);

		return $this->find($type, $params);
	}

/**
 * afterFind
 * @param   array   $val
 * @param   boolean $checkAuth	権限チェックをするかどうか
 * @return  array   $block
 * @since   v 3.0.0.0
 */
	public function afterFindDefault($val, $checkAuth = true) {
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

		if($checkAuth && isset($val['Content']['display_flag']) &&
			(($val['Content']['display_flag'] != NC_DISPLAY_FLAG_ON || $val['Block']['display_flag'] == NC_DISPLAY_FLAG_OFF) && $val['ContentAuthority']['hierarchy'] < NC_AUTH_MIN_CHIEF)) {
			// 非公開
			return;
		}
		if(!isset($val['Block'])) {
			return $val;
		}

		// class name
		if($val['Block']['controller_action'] == "group"){
			$val['Block']['class_name'] = "nc-group";
		} else if(isset($val['Block'])) {
			$val['Block']['class_name'] = "nc-block";
		}

		// array_merge();
		if(isset($val['Content']['id'])) {
			$val['Block']['room_id'] = $val['Content']['room_id'];
			$val['Block']['shortcut_type'] = $val['Content']['shortcut_type'];
			$val['Block']['master_id'] = $val['Content']['master_id'];
		} else {
			$val['Block']['room_id'] = $val['Page']['room_id'];
			$val['Block']['shortcut_type'] = NC_SHORTCUT_TYPE_OFF;
			$val['Block']['master_id'] = 0;
		}

		//$val['Block']['url'] = $val['Content']['url'];
		$val['Block']['dir_name'] = $val['Module']['dir_name'];
		$val['Block']['edit_controller_action'] = $val['Module']['edit_controller_action'];
		$val['Block']['style_controller_action'] = $val['Module']['style_controller_action'];
		$val['Block']['display_content_title'] = _OFF;

		if($val['Block']['title'] != '') {
			if($val['Block']['title'] == "{X-CONTENT}") {
				$val['Block']['title'] = $val['Content']['title'];
				$val['Block']['display_content_title'] = _ON;
			}
		}

		$val = $this->setDefaultAuthority($val);

		$val['Block']['hierarchy'] = $val['ContentAuthority']['hierarchy'];
		$val['Block']['page_hierarchy'] = $val['PageAuthority']['hierarchy'];

		//if(!isset($val['Content']['id'])) {
		//	// TODO:必要かどうか検討すること 後に削除
		//	$val['Block']['hierarchy'] = null;
		//}
		unset($val['Page']);
		unset($val['ContentAuthority']);
		unset($val['BlockPage']);
		unset($val['PageAuthority']);
		//unset($val['Content']);
		//unset($val['Module']);

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

		return $val;
	}

/**
 * ページリストからブロック取得
 * @param   array    $page_id_arr
 * @param   integer  $userId
 * @param   function $fetchcallback
 * @param   array    $fetchParams
 * @return  array    $blocks
 * @since   v 3.0.0.0
 */
	public function findByPageIds($page_id_arr, $userId, $fetchcallback = null, $fetchParams = null) {
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
			'joins' => $this->_getJoinsArray($userId),
			'conditions' => $conditions,
			'order' => $this->_getOrderArray(),
		);

		//if($display_flag == NC_DISPLAY_FLAG_ON) {
		//	$params['joins'][0]['type'] = "LEFT";
		//}

		if($fetchcallback === "") {
			$results = $this->find('all', $params);
		} else if(!is_null($fetchcallback)) {
			$results = call_user_func_array($fetchcallback, array($this->find('all', $params), $fetchParams));
		} else {
			$results = $this->_afterFind($this->find('all', $params), $fetchParams);
		}
		return $results;
	}

/**
 * root_idからblock配列を取得する
 * @param  integer root_id
 * @return array block
 * @since   v 3.0.0.0
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
 * @param   integer  $userId
 * @return  array    $blocks
 * @since   v 3.0.0.0
 */
	function findByGroupId($block, $userId) {
		$conditions = array(
			'Block.root_id' => $block['Block']['root_id'],
			'Block.thread_num >=' => $block['Block']['thread_num']
		);
		$params = array(
			'fields' => $this->_getFieldsArray(),
			'joins' => $this->_getJoinsArray($userId),
			'conditions' => $conditions,
			'order' => $this->_getOrderArray(),
		);
		return $this->_afterFind($this->find('all', $params), $userId, array('group_block_id' => $block['Block']['id']));
	}
/**
 * afterFind
 * @param   array   $results
 * @param   array   $fetchParams
 * @return  array   $blocks
 * @since   v 3.0.0.0
 */
	protected function _afterFind($results, $userId, $fetchParams = array()) {
		$blocks = array();
		$group_block_id = null;
		if(isset($fetchParams['group_block_id'])) {
			$group_block_id = $fetchParams['group_block_id'];
			$parent_id_arr = array($fetchParams['group_block_id']);
		}

		foreach ($results as $key => $val) {
			// 公開日付・非公開日付
			$val = $this->afterFindDefault($val, $userId);
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
			'Page.thread_num','Page.room_id','Page.root_id','Page.space_type',
			'BlockPage.thread_num','BlockPage.room_id','BlockPage.root_id','BlockPage.space_type',
			'Content.id','Content.module_id','Content.title','Content.shortcut_type','Content.master_id','Content.room_id','Content.display_flag','Content.is_approved','Content.url',
			'Module.id','Module.controller_action','Module.edit_controller_action','Module.style_controller_action','Module.dir_name','Module.content_has_one',
			'Module.copy_operation','Module.shortcut_operation','Module.move_operation',
			'ContentAuthority.id','ContentAuthority.hierarchy','PageAuthority.id','PageAuthority.hierarchy',

		);
	}

/**
 * Blockモデル共通JOIN文
 * @param   integer $userId
 * @return  array   $joins
 * @since   v 3.0.0.0
 */
	protected function _getJoinsArray($userId) {
		$ret = array(
			array("type" => "LEFT",
				"table" => "contents",
				"alias" => "Content",
				"conditions" => "`Block`.`content_id`=`Content`.`id`"
			),
			array("type" => "LEFT",
				"table" => "page_user_links",
				"alias" => "PageUserLink",
				"conditions" => "`Content`.`room_id`=`PageUserLink`.`room_id`".
				" AND `PageUserLink`.`user_id` =".intval($userId)
			),
			array("type" => "LEFT",
				"table" => "authorities",
				"alias" => "ContentAuthority",
				"conditions" => "`ContentAuthority`.`id`=`PageUserLink`.`authority_id`"
			),
			array("type" => "LEFT",
				"table" => "pages",
				"alias" => "Page",
				"conditions" => "`Content`.`room_id`=`Page`.`id`"
			),
			array("type" => "LEFT",
				"table" => "pages",
				"alias" => "BlockPage",
				"conditions" => "`Block`.`page_id`=`BlockPage`.`id`"
			),
			array("type" => "LEFT",
				"table" => "page_user_links",
				"alias" => "BlockPageUserLink",
				"conditions" => "`BlockPage`.`room_id`=`BlockPageUserLink`.`room_id`".
				" AND `BlockPageUserLink`.`user_id` =".intval($userId)
			),
			array("type" => "LEFT",
				"table" => "authorities",
				"alias" => "PageAuthority",
				"conditions" => "`PageAuthority`.`id`=`BlockPageUserLink`.`authority_id`"
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
			'Block.page_id' => "ASC",
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
 * @param integer $allDelete コンテンツもすべて削除するかどうか（NC_DELETE_MOVE_PARENTの場合、コンテンツを親のコンテンツへ）
 * @param integer $parentRoomId $allDelete NC_DELETE_MOVE_PARENTの場合の振り替え先room_id
 * @param boolean $isDeletePage ページ削除から呼ばれたかどうか
 * @return	boolean true or false
 * @since   v 3.0.0.0
 **/
	public function deleteBlock($block, $allDelete = _OFF, $parentRoomId = null, $isDeletePage = false)
	{
		$Content = ClassRegistry::init('Content');

		$controller_action = $block['Block']['controller_action'];
		$page_id = $block['Block']['page_id'];
		$module_id = $block['Block']['module_id'];
		$block_id = $block['Block']['id'];
		$content_id = $block['Block']['content_id'];
		if (isset($block['Content'])) {
			$content['Content'] = $block['Content'];
		} else {
			$content = $Content->findById($content_id);
		}

		if ($controller_action == 'group') {
			// --------------------------------------
			// --- 子供に位置するブロック削除     ---
			// --------------------------------------
			// ページから削除する場合は、再帰的に削除する必要なし。
			if (!$isDeletePage) {
				$child_blocks = $this->find('all', array(
					'recursive' => -1,
					'conditions' => array(
						'Block.parent_id' => $block['Block']['id']
					)
				));
				foreach($child_blocks as $child_block) {
					//再帰処理
					if(!$this->deleteBlock($child_block, $allDelete, $parentRoomId, $isDeletePage)) {
						return false;
					}
				}
			}
		} else {
			// -------------------------------------
			// --- 削除関数                      ---
			// -------------------------------------
			$Page = ClassRegistry::init('Page');
			$Module = ClassRegistry::init('Module');
			$BlockOperation = ClassRegistry::init('Block.BlockOperation');

			if (!$isDeletePage) {
				// 行の前詰め処理
				if(!$BlockOperation->decrementRowNum($block)) {
					return false;
				}
				$count_row_num = $BlockOperation->findRowCount($block['Block']['page_id'], $block['Block']['parent_id'], $block['Block']['col_num']);
				if($count_row_num == 1) {
					// 削除する列にブロックが１つしかないので 列の前詰め処理
					if(!$BlockOperation->decrementColNum($block)) {
						return false;
					}
				}
			}

			$module = $Module->findById($module_id);
			$page = $Page->findById($page_id);
			if (!empty($module['Module'])) {
				// ルームが異なるコンテンツは削除アクションを行わない。
				$plugin = $module['Module']['dir_name'];
				App::uses($plugin.'OperationComponent', 'Plugin/'.$plugin.'/Controller/Component');
				$class_name = $plugin.'OperationComponent';
				if(class_exists($class_name) && method_exists($class_name,'delete_block')) {
					$class = new $class_name(new ComponentCollection());
					$class->startup();
					// ブロック削除アクション
					if (!$class->delete_block($block, $content, $page)) {
						return false;
					}
				} else {
					// 自動でBlockIDをキーに削除処理を行う。
					$CakeSchema = ClassRegistry::init('CakeSchema');
					$options = array('name' => $plugin, 'plugin' => $plugin);
					$schema = $CakeSchema->load($options);
					if($schema) {
						foreach($schema->tables as $table => $fields) {
							if(isset($fields['block_id'])) {
								$model = ClassRegistry::init($schema->name. '.'. Inflector::classify($table));
								$conditions = array(
									$model->alias.".block_id" => $block_id
								);
								if(!$model->deleteAll($conditions)) {
									return false;
								}
							}
						}
					}
				}
				if (isset($content['Content'])) {
					if ($content['Content']['shortcut_type'] == NC_SHORTCUT_TYPE_OFF
						&& $allDelete == _ON && $page['Page']['room_id'] == $content['Content']['room_id']) {
						if(!$Content->deleteContent($content, $allDelete, $parentRoomId)) {
							return false;
						}
					} else if($content['Content']['display_flag'] == NC_DISPLAY_FLAG_DISABLE
						|| $content['Content']['shortcut_type'] != NC_SHORTCUT_TYPE_OFF) {
						// ショートカットではない場合
						// コンテンツがなくてもエラーとしない(コンテンツ一覧からコンテンツを削除後にブロック削除を行うとエラーとなるため)
						$Content->delete($block['Block']['content_id']);
					} elseif (isset($parentRoomId) && $allDelete == NC_DELETE_MOVE_PARENT
							&& $content['Content']['room_id'] != $parentRoomId) {
						// 親のルームの持ち物に変換し、親のルーム内のショートカットを解除
						if (!$Content->cancelShortcutParentRoom($content_id, $parentRoomId)) {
							return false;
						}
					}
				}
			}
		}

		// --------------------------------------
		// --- ブロック削除処理               ---
		// --------------------------------------
		$tmp_block = $this->findById($block_id);
		if (!empty($tmp_block) && !$this->delete($block_id)) {
			return false;
		}

		if ($controller_action == 'group') {
			// グループ化したブロックのcontents 削除処理
			// コンテンツがなくてもエラーとしない
			$Content->delete($block['Block']['content_id']);
		}
		if (!$isDeletePage && isset($count_row_num) && $count_row_num == 1) {
			//グループ化した空ブロック削除処理
			$this->deleteGroupingEmptyBlock($block['Block']['parent_id'], $block_id);
		}

		return true;
	}

/**
 * グループ化した空ブロック削除処理
 *
 * @param integer $parent_id グループのblock_id
 * @param integer $block_id	操作対象block_id
 * @return boolean true or false
 * @since   v 3.0.0.0
 */
	public function deleteGroupingEmptyBlock($parent_id, $block_id = null)
	{
		$Content = ClassRegistry::init('Content');
		$BlockOperation = ClassRegistry::init('Block.BlockOperation');

		$block = $this->findById($parent_id);
		if(empty($block)) {
			return true;
		}

		$block_count = $this->find('count', array(
			'fields' => 'COUNT(*) as count',
			'recursive' => -1,
			'conditions' => array(
				'Block.parent_id' => $parent_id
			)
		));
		if ($block_count != 0) {
			return true;
		}

		//削除処理
		$Content->delete($block['Block']['content_id']);
		$this->delete($parent_id);
		if($block_id) {
			// 操作対象block_idがあれば、更新対象にしない
			$block['Block']['id'] = $block_id;
		}
		// 行の前詰め処理
		if(!$BlockOperation->decrementRowNum($block)) {
			return false;
		}
		$count_row_num = $BlockOperation->findRowCount($block['Block']['page_id'], $block['Block']['parent_id'], $block['Block']['col_num']);
		if($count_row_num == 0) {
			//削除列が１つもなくなったので、列の前詰め処理
			$result = $BlockOperation->decrementColNum($block);
			if(!$result) {
				return false;
			}
		}

		//再帰処理
		if($block['Block']['parent_id'] != 0) {
			if(!$this->deleteGroupingEmptyBlock($block['Block']['parent_id'], $block_id)) {
				return false;
			}
		}

		return true;
	}
}