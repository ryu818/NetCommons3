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
	public $name = 'Content';

	public $actsAs = array('Page', 'Validation');

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
			'is_master' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'last' => true,
					'required' => true,
					'message' => __('The input must be a boolean.')
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
			'approved_flag' => array(
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
 * @param   integer   $content_id
 * @param   integer   $user_id
 * @return  Content   $content
 * @since   v 3.0.0.0
 */
	public function findAuthById($content_id, $user_id) {
		$conditions['Content.id'] = $content_id;
		$params = array(
			'fields' => $this->_getFieldsArray(),
			'joins' => $this->_getJoinsArray($user_id),
			'conditions' => $conditions
		);

		$ret = $this->afterFindDefault($this->find('first', $params), $user_id);
		return $ret;
	}

/**
 * afterFind
 * @param   array   $val
 * @param   integer  $user_id
 * @return  Model Content   $content
 * @since   v 3.0.0.0
 */
	public function afterFindDefault($val, $user_id) {
		if(!isset($val['Authority']['hierarchy'])) {
			$val['Authority']['hierarchy'] = $this->getDefaultHierarchy($val, $user_id);
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
			'Page.thread_num','Page.room_id','Page.root_id','Page.space_type',
			'Module.id','Module.controller_action','Module.edit_controller_action','Module.style_controller_action','Module.dir_name','Module.content_has_one',
			'Authority.id','Authority.hierarchy'
		);
	}

/**
 * Contentモデル共通JOIN文
 * @param   integer $user_id
 * @return  array   $joins
 * @since   v 3.0.0.0
 */
	protected function _getJoinsArray($user_id) {
		$ret = array(
			array("type" => "LEFT",
				"table" => "page_user_links",
				"alias" => "PageUserLink",
				"conditions" => "`Content`.`room_id`=`PageUserLink`.`room_id`".
				" AND `PageUserLink`.`user_id` =".intval($user_id)
			),
			array("type" => "LEFT",
				"table" => "authorities",
				"alias" => "Authority",
				"conditions" => "`Authority`.id``=`PageUserLink`.`authority_id`"
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
 * @param   integer $parent_room_id $all_delete NC_DELETE_MOVE_PARENTの場合の振り替え先room_id
 * @return	boolean true or false
 * @since   v 3.0.0.0
 */
	public function deleteContent($content, $all_delete = _OFF, $parent_room_id = null) {
		App::uses('Page', 'Model');
		App::uses('Upload', 'Model');
		$Page = new Page();
		$Upload = new Upload();

		$content_id = $content['Content']['id'];
		$module_id = $content['Content']['module_id'];
		$master_id = $content['Content']['master_id'];
		$room_id = $content['Content']['room_id'];
		if($module_id == 0 || !$content['Content']['is_master']) {
			// group化ブロック、または、権限を付与されたショートカット
			$ret = $this->delete($content_id);
			$Upload->deleteByContentId($content_id);
			return $ret;
		}
		if($all_delete == _OFF) {
			return true;
		}

		if($all_delete == NC_DELETE_MOVE_PARENT && !isset($parent_room_id)) {
			$page = $Page->findById($room_id);
			$parent_page = $Page->findById($page['Page']['parent_id']);
			$parent_room_id = isset($parent_page['Page']) ? $parent_page['Page']['room_id'] : 0;
		}
		if($parent_room_id == 0) {
			// 移動先のルームがないため完全に削除。
			$all_delete = _ON;
		}

		// -------------------------------------
		// --- 削除関数                      ---
		// -------------------------------------
		if($all_delete == _ON) {
			if(isset($content['Module'])) {
				$module['Module'] = $content['Module'];
			} else {
				App::uses('Module', 'Model');
				$Module = new Module();
				$module = $Module->findById($module_id);
			}
			$plugin = $module['Module']['dir_name'];
			App::uses($plugin.'OperationComponent', 'Plugin/'.$plugin.'/Controller/Component');
			$class_name = $plugin.'OperationComponent';
			if(class_exists($class_name) && method_exists($class_name,'delete')) {
				eval('$class = new '.$class_name.'();');
				$class->startup();

				// 削除アクション
				$ret = $class->delete($content);
				if(!$ret) {
					return false;
				}
			}
			$this->delete($content_id);
			$Upload->deleteByContentId($content_id);
		} else if($content['Content']['room_id'] != $parent_room_id) {
			// 親のルームの持ち物に変換し、親のルーム内の権限を付与したショートカットを解除
			if(!$this->cancelShortcutParentRoom($content_id, $parent_room_id)) {
				return false;
			}
		}

		return true;
	}

/**
 * 親のルームの持ち物に変換し、親ルームの該当コンテンツにおける権限を付与したショートカットを解除
 * ルーム削除（コンテンツ削除）時に親のルームの物に変換される場合、親ルームの削除コンテンツに対する権限を付与したショートカットを解除する必要があるため
 *
 * @param   integer $content_id
 * @param   integer $parent_room_id
 * @return	boolean true or false
 * @since   v 3.0.0.0
 */
	public function cancelShortcutParentRoom($content_id, $parent_room_id) {
		$fields = array('Content.room_id'=> $parent_room_id);
		$conditions = array(
			"Content.id" => $content_id
		);
		if(!$this->updateAll($fields, $conditions)) {
			return false;
		}

		// 親のルーム内のショートカットを解除
		$cancel_contents_list = $this->findCancelShortcut($content_id, $parent_room_id);
		if(count($cancel_contents_list) > 0) {
			$this->delete($cancel_contents_list);
			App::uses('Block', 'Model');
			$Block = new Block();
			$fields = array(
				'Block.content_id' => $content_id
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
 * @param   integer $content_id
 * @param   integer $parent_room_id
 * @return	boolean true or false
 * @since   v 3.0.0.0
 */
	protected function findCancelShortcut($content_id, $parent_room_id) {
		$params = array(
			'fields' => array('Content.id'),
			'conditions' => array(
				'Content.master_id' => $content_id,
				'Content.is_master' => _OFF,
				'Content.room_id' => $parent_room_id
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
 * @access public
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