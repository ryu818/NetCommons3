<?php
/**
 * Contentモデル
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Model
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class Content extends AppModel
{
	public $actsAs = array('Page', 'Validation', 'Auth' => array('joins' => false, 'afterFind' => false));

/**
 * バリデート処理
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function __construct() {
		parent::__construct();

		/*
		 * エラーメッセージ設定
		*/
		$this->validate = array(
			'module_id' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'required' => true,
					'allowEmpty' => false,
					'message' => __('The input must be a number.')
				)
			),
			'title' => array(
				'notEmpty'  => array(
					'rule' => array('notEmpty'),
					'last' => true,
					'required' => true,
					'message' => __('Please be sure to input.')
				),
				'maxlength'  => array(
					'rule' => array('maxLength', NC_VALIDATOR_BLOCK_TITLE_LEN),
					'last' => false ,
					'message' => __('The input must be up to %s characters.', NC_VALIDATOR_BLOCK_TITLE_LEN)
				)
			),
			'shortcut_type' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'required' => true,
					'message' => __('The input must be a number.')
				),
				'inList' => array(
					'rule' => array('inList', array(
						NC_SHORTCUT_TYPE_OFF,
						NC_SHORTCUT_TYPE_SHOW_ONLY,
						NC_SHORTCUT_TYPE_SHOW_AUTH,
					), false),
					'allowEmpty' => false,
					'message' => __('It contains an invalid string.')
				)
			),
			'master_id' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'required' => true,
					'allowEmpty' => false,
					'message' => __('The input must be a number.')
				)
			),
			'room_id' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'required' => true,
					'allowEmpty' => false,
					'message' => __('The input must be a number.')
				)
			),
			'display_flag' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'required' => true,
					'message' => __('The input must be a number.')
				),
				'inList' => array(
					'rule' => array('inList', array(
						NC_DISPLAY_FLAG_OFF,
						NC_DISPLAY_FLAG_ON,
						NC_DISPLAY_FLAG_DISABLE,
					), false),
					'allowEmpty' => false,
					'message' => __('It contains an invalid string.')
				)
			),
			'is_approved' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'last' => true,
					'required' => true,
					'message' => __('The input must be a boolean.')
				)
			),
		);
	}

/**
 * content_id,user_idから該当コンテンツを取得
 * @param   integer   $id
 * @param   integer   $userId
 * @return  Content   $content
 * @since   v 3.0.0.0
 */
	public function findAuthById($id, $userId) {
		$conditions['Content.id'] = $id;
		$params = array(
			'fields' => $this->_getFieldsArray(),
			'joins' => $this->_getJoinsArray($userId),
			'conditions' => $conditions
		);

		$ret = $this->afterFindDefault($this->find('first', $params));
		return $ret;
	}

/**
 * afterFind
 * @param   array   $val
 * @return  Model Content   $content
 * @since   v 3.0.0.0
 */
	public function afterFindDefault($val) {
		if(isset($val['Content']['id'])) {
			if(!isset($val['PageAuthority']['hierarchy'])) {
				$val['PageAuthority']['hierarchy'] = $this->getDefaultHierarchy($val);
			}
		}
		return $val;
	}

/**
 * Contentモデル共通Fields文
 * @param   void
 * @return  array   $fields
 * @since   v 3.0.0.0
 */
	protected function _getFieldsArray() {
		return array(
			'Content.*',
			'Page.id','Page.page_name','Page.thread_num','Page.room_id','Page.root_id','Page.space_type',
			'Module.id','Module.controller_action','Module.edit_controller_action','Module.style_controller_action','Module.dir_name','Module.content_has_one',
			'PageAuthority.id','PageAuthority.hierarchy'
		);
	}

/**
 * Contentモデル共通JOIN文
 * @param   integer $userId
 * @return  array   $joins
 * @since   v 3.0.0.0
 */
	protected function _getJoinsArray($userId) {
		$ret = array(
			array("type" => "LEFT",
				"table" => "page_user_links",
				"alias" => "PageUserLink",
				"conditions" => "`Content`.`room_id`=`PageUserLink`.`room_id`".
				" AND `PageUserLink`.`user_id` =".intval($userId)
			),
			array("type" => "LEFT",
				"table" => "authorities",
				"alias" => "PageAuthority",
				"conditions" => "`PageAuthority`.`id`=`PageUserLink`.`authority_id`"
			),
			array("type" => "LEFT",
				"table" => "pages",
				"alias" => "Page",
				"conditions" => "`Content`.`room_id`=`Page`.`id`"
			),
			array("type" => "LEFT",
				"table" => "modules",
				"alias" => "Module",
				"conditions" => "`Module`.`id`=`Content`.`module_id`"
			)
		);
		return $ret;
	}

/**
 * コンテンツ削除処理
 *
 * @param   Model Content  $content
 * @param   boolean $mode _OFF:ショートカットのみ削除。_ON. 強制的に削除 NC_DELETE_MOVE_PARENT.自動で親のルームがあれば、そちらのものとする。ショートカットは削除。
 * @param   integer $parentRoomId $allDelete NC_DELETE_MOVE_PARENTの場合の振り替え先room_id
 * @return	boolean true or false
 * @since   v 3.0.0.0
 */
	public function deleteContent($content, $allDelete = _OFF, $parentRoomId = null) {
		$Page = ClassRegistry::init('Page');
		$UploadLink = ClassRegistry::init('UploadLink');
		$Revision = ClassRegistry::init('Revision');

		$id = $content['Content']['id'];
		$module_id = $content['Content']['module_id'];
		$master_id = $content['Content']['master_id'];
		$room_id = $content['Content']['room_id'];
		if($module_id == 0 || $content['Content']['shortcut_type'] != NC_SHORTCUT_TYPE_OFF) {
			// group化ブロック、または、ショートカット
			$ret = $this->delete($id);
			return $ret;
		}
		if($allDelete == _OFF) {
			return true;
		}

		if($allDelete == NC_DELETE_MOVE_PARENT && !isset($parentRoomId)) {
			$page = $Page->findById($room_id);
			$parent_page = $Page->findById($page['Page']['parent_id']);
			$parentRoomId = isset($parent_page['Page']) ? $parent_page['Page']['room_id'] : 0;
		}
		if($parentRoomId == 0) {
			// 移動先のルームがないため完全に削除。
			$allDelete = _ON;
		}

		// -------------------------------------
		// --- 削除関数                      ---
		// -------------------------------------
		if($allDelete == _ON) {
			if(isset($content['Module'])) {
				$module['Module'] = $content['Module'];
			} else {
				$Module = ClassRegistry::init('Module');
				$module = $Module->findById($module_id);
			}
			$plugin = $module['Module']['dir_name'];
			App::uses($plugin.'OperationComponent', 'Plugin/'.$plugin.'/Controller/Component');
			$class_name = $plugin.'OperationComponent';
			if(class_exists($class_name) && method_exists($class_name,'delete')) {
				eval('$class = new '.$class_name.'(new ComponentCollection());');
				$class->startup();

				// 削除アクション
				$ret = $class->delete($content);
				if(!$ret) {
					return false;
				}
			}
			$this->delete($id);
			$conditions = array(
				"Revision.content_id" => $id
			);
			if(!$Revision->deleteAll($conditions)) {
				return false;
			}

			$conditions = array(
				"UploadLink.content_id" => $id
			);
			if(!$UploadLink->deleteAll($conditions, true, true)) {
				return false;
			}
		} else if($content['Content']['room_id'] != $parentRoomId) {
			// 親のルームの持ち物に変換し、親のルーム内の権限を付与したショートカットを解除
			if(!$this->cancelShortcutParentRoom($id, $parentRoomId)) {
				return false;
			}
		}

		return true;
	}

/**
 * 親のルームの持ち物に変換し、親ルームの該当コンテンツにおける権限を付与したショートカットを解除
 * ルーム削除（コンテンツ削除）時に親のルームの物に変換される場合、親ルームの削除コンテンツに対する権限を付与したショートカットを解除する必要があるため
 *
 * @param   integer $id
 * @param   integer $parentRoomId
 * @return	boolean true or false
 * @since   v 3.0.0.0
 */
	public function cancelShortcutParentRoom($id, $parentRoomId) {
		$fields = array('Content.room_id'=> $parentRoomId);
		$conditions = array(
			"Content.id" => $id
		);
		if(!$this->updateAll($fields, $conditions)) {
			return false;
		}

		// 親のルーム内のショートカットを解除
		$cancel_contents_list = $this->findCancelShortcut($id, $parentRoomId);
		if(count($cancel_contents_list) > 0) {
			$this->delete($cancel_contents_list);
			$Block = ClassRegistry::init('Block');
			$fields = array(
				'Block.content_id' => $id
			);
			$conditions = array(
				'Block.content_id' => $cancel_contents_list
			);
			if(!$Block->updateAll($fields, $conditions)) {
				return false;
			}
		}
		return true;
	}

/**
 * 解除対象のコンテンツ一覧を取得する
 * ルーム削除（コンテンツ削除）時に親のルームの物に変換される場合、親ルームの削除コンテンツに対する権限を付与したショートカットを解除する必要があるため
 *
 * @param   integer $id
 * @param   integer $parentRoomId
 * @return	boolean true or false
 * @since   v 3.0.0.0
 */
	protected function findCancelShortcut($id, $parentRoomId) {
		$params = array(
			'fields' => array('Content.id'),
			'conditions' => array(
				'Content.master_id' => $id,
				'Content.shortcut_type !=' => NC_SHORTCUT_TYPE_OFF,
				'Content.room_id' => $parentRoomId
			)
		);

		return $this->find('list', $params);
	}

/**
 * Removes record for given ID. If no ID is given, the current ID is used. Returns true on success.
 * master_idも等しいものを削除。
 *
 * @param mixed $id ID of record to delete
 * @param boolean $cascade Set to true to delete records that depend on this record
 * @return boolean True on success
 * @since   v 3.0.0.0
 */
	public function delete($id = null, $cascade = true) {
		$ret = parent::delete($id, $cascade);
		if($ret === false) {
			return $ret;
		}
		$conditions = array(
			"Content.master_id" => $id
		);
		$this->deleteAll($conditions);
		return $ret;
	}
}