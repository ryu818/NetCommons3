<?php
/**
 * BlockListモデル
 *
 * <pre>
 *  配置ブロック一覧用モデル
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Plugin.Block.Model
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class BlockList extends AppModel {
	public $name = 'BlockList';
    public $useTable = 'blocks';
    public $alias = 'Block';

	public $actsAs = array('Page');

/**
 * コンテンツ一覧表示
 * @param   integer $userId ログインuserId
 * @param   integer $masterId
 * @param   integer $blockId
 * @param   string  $sortname
 * @param   string  $sortorder
 * @param   integer $page
 * @param   integer $limit
 * @since   v 3.0.0.0
 */
	public function findBlocks($userId, $masterId, $blockId, $sortname = 'title', $sortorder = 'asc', $page = 1, $limit= 20) {
		// TODO:英語と日本語のブロックが混じって表示されても違和感がないかどうか。また問題なく動作するか確認する。
		App::uses('Content', 'Model');
		$Content = new Content();
		$conditions = array(
			'Content.master_id' => intval($masterId)
		);
		$params = array(
			'fields' => array(
				'Content.id'
			),
			'conditions' => $conditions,
			'recursive' => -1
		);
		$contents = $Content->find('list', $params);

		$blocks = array();
		$conditions = array(
			'Block.content_id' => $contents
		);

		$total = $this->find('count', array(
			'fields' => 'COUNT(*) as count',
			'conditions' => $conditions,
			'recursive' => -1
		));
		if($total != 0) {
			$fields = array(
				'Block.*',
				'Page.permalink',
				'Page.id','Page.page_name','Page.space_type','Page.thread_num','Page.display_sequence','Page.room_id',
				'RoomPage.id','RoomPage.page_name','RoomPage.space_type','RoomPage.thread_num','RoomPage.display_sequence',
				'Authority.hierarchy',
				'Content.id','Content.title','Content.room_id','Content.is_master'
			);
			if($blockId > 0) {
				$order = array(
					'ActiveBlock.id' => "DESC",
					'Block.'.$sortname => $sortorder,
					'Block.id' => $sortorder
				);
			} else {
				$order = array(
					'Block.'.$sortname => $sortorder,
					'Block.id' => $sortorder
				);
			}
			$params = array(
				'fields' => $fields,
				'conditions' => $conditions,
				'joins' => array(
					array("type" => "LEFT",
						"table" => "contents",
						"alias" => "Content",
						"conditions" => "`Block`.`content_id`=`Content`.`id`"
					),
					array("type" => "LEFT",
						"table" => "pages",
						"alias" => "Page",
						"conditions" => "`Block`.`page_id`=`Page`.`id`"
					),
					array("type" => "LEFT",
						"table" => "pages",
						"alias" => "RoomPage",
						"conditions" => "`RoomPage`.id``=`Content`.`room_id`"
					),
					array("type" => "LEFT",
						"table" => "page_user_links",
						"alias" => "PagesUsersLink",
						"conditions" => "`Page`.`room_id`=`PagesUsersLink`.`room_id`".
						" AND `PagesUsersLink`.`user_id` =".intval($userId)
					),
					array("type" => "LEFT",
						"table" => "authorities",
						"alias" => "Authority",
						"conditions" => "`Authority`.id``=`PagesUsersLink`.`authority_id`"
					),
				),
				'limit' => $limit,
				'page' => $page,
				'order' => $order
			);
			if($blockId > 0) {
				$params['joins'][] = array("type" => "LEFT",
					"table" => "blocks",
					"alias" => "ActiveBlock",
					"conditions" => "`Block`.`id`=`ActiveBlock`.`id`".
					" AND `ActiveBlock`.`id` =".$blockId
				);
			}
			$blocks = $this->find('all', $params);
			for($i =0; $i < count($blocks); $i++) {
				$blocks[$i]['Page'] = $this->setPageName($blocks[$i]['Page']);
				$blocks[$i]['RoomPage'] = $this->setPageName($blocks[$i]['RoomPage']);
				$blocks[$i]['Page']['permalink'] = $this->getPermalink($blocks[$i]['Page']['permalink'], $blocks[$i]['Page']['space_type']);
				if(!isset($blocks[$i]['Authority']['hierarchy'])) {
					$blocks[$i]['Authority']['hierarchy'] = $this->getDefaultHierarchy($blocks[$i], $userId);
				}
			}
		}

		return array($total, $blocks);
	}
}