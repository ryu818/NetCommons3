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

	public $actsAs = array('Page');

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
			'Module.id','Module.controller_action','Module.edit_controller_action','Module.dir_name','Module.content_has_one',
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
}