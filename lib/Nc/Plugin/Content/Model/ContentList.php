<?php
/**
 * ContentListモデル
 *
 * <pre>
 *  コンテンツ一覧用モデル
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Plugin.Block.Model
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class ContentList extends AppModel {
	public $useTable = 'contents';
	public $alias = 'Content';

	public $actsAs = array('Page');

/**
 * コンテンツ一覧表示
 * @param   integer $userId ログインuserId
 * @param   integer $roomId
 * @param   integer $page
 * @param   integer $limit
 * @param   integer $order
 * @param   integer $contentId アクティブなcontentId
 * @param   integer $moduleId
 * @param   integer $isApproved
 * @return  array (integer $total, Model Contents)
 * @since   v 3.0.0.0
 */
	public function findContents($userId, $roomId, $page = 1, $limit= 20, $order = null, $contentId = null, $moduleId = null, $isApproved = null) {
		$conditions = array();
		if(isset($moduleId) && $moduleId != 0) {
			$conditions['Content.module_id'] = $moduleId;
		}
		if(isset($roomId)) {
			$conditions['Content.room_id'] = $roomId;
		}
		if(isset($isApproved)) {
			$conditions['Content.is_approved'] = $isApproved;
		}
		$conditions['Content.shortcut_type'] = array(NC_SHORTCUT_TYPE_OFF, NC_SHORTCUT_TYPE_SHOW_AUTH);
		$conditions['Content.display_flag'] = array(NC_DISPLAY_FLAG_ON, NC_DISPLAY_FLAG_OFF);

		$join = array(
			array("type" => "INNER",
				"table" => "page_user_links",
				"alias" => "PagesUsersLink",
				"conditions" => "`Content`.`room_id`=`PagesUsersLink`.`room_id`".
					" AND `PagesUsersLink`.`user_id` =".intval($userId)
			),
			array("type" => "INNER",
				"table" => "authorities",
				"alias" => "ContentAuthority",
				"conditions" => "`ContentAuthority`.`id`=`PagesUsersLink`.`authority_id`"
			),
			array("type" => "INNER",
				"table" => "modules",
				"alias" => "Module",
				"conditions" => "`Module`.`id`=`Content`.`module_id`"
			),
			array("type" => "LEFT",
				"table" => "pages",
				"alias" => "Page",
				"conditions" => "`Page`.`id`=`Content`.`room_id`"
			),
			array("type" => "LEFT",
				"table" => "contents",
				"alias" => "ActiveContent",
				"conditions" => "`Content`.`id`=`ActiveContent`.`id`".
				" AND `ActiveContent`.`id` =".intval($contentId)
			),
		);
		$total = $this->find('count', array(
			'fields' => 'COUNT(*) as count',
			'joins' => $join,
			'conditions' => $conditions,
			'recursive' => -1
		));
		if($total == 0) {
			return array(0, array() );
		}
		if(empty($order)) {
			$order = array(
				'ActiveContent.id' => "DESC",
				'Content.title' => "ASC",
				'Content.id' => "ASC"
			);
		}
		$join[] = array("type" => "LEFT",
			"table" => "blocks",
			"alias" => "Block",
			"conditions" => "`Block`.`content_id`=`Content`.`id`"
		);

		$params = array(
			'fields' => array(
				'Content.id','Content.module_id','Content.title','Content.shortcut_type','Content.master_id',
					'Content.room_id','Content.display_flag','Content.is_approved',
				'PagesUsersLink.authority_id',
				'Page.id, Page.page_name, Page.thread_num, Page.space_type',
				'Page.display_sequence',
				'ContentAuthority.hierarchy',
				'ActiveContent.id',
				'Module.dir_name','Module.controller_action',
				'Block.id',
			),
			'joins' => $join,
			'conditions' => $conditions,
			'limit' => $limit,
			'page' => $page,
			'recursive' => -1,
			'order' => $order,
			'group' => 'Content.id'
		);


		$rets = $this->find('all', $params);
		if(count($rets) > 0) {
			for($i =0; $i < count($rets); $i++) {
				if($rets[$i]['Content']['shortcut_type'] != NC_SHORTCUT_TYPE_OFF) {
					// コンテンツ元取得
					$Content = ClassRegistry::init('Content');
					$parentContent = $Content->findAuthById($rets[$i]['Content']['master_id'], $userId);
					if(isset($parentContent['Page']['page_name'])) {
						$parentContent = $this->setPageName($parentContent);
						$rets[$i]['Page']['page_name'] = $parentContent['Page']['page_name'];
					}
				}
				//$rets[$i] = $this->setPageName($rets[$i]);
			}
		}

		return array($total, $rets);
	}
}