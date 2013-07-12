<?php
/**
 * BlockMoveComponentクラス
 *
 * <pre>
 * ブロック移動用コンポーネント
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       App.Plugin.Controller
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class BlockMoveComponent extends Component {
/**
 * _controller
 *
 * @var Controller
 */
	protected $_controller = null;

/**
 * startup
 *
 * @param Controller $controller
 */
	public function startup(Controller $controller) {
		$this->_controller = $controller;
	}

/**
 * 行列移動・追加バリデータ処理
 *
 * @param  CakeRequest $request
 * @param  Page Model $insert_page
 * @return boolean true or false
 * @since   v 3.0.0.0
 */
	public function validatorRequest($request) {
		if (!isset($request->data) || !isset($request->data['show_count']) || !isset($request->data['parent_id'])
			 || !isset($request->data['page_id'])  || !isset($request->data['col_num']) || !isset($request->data['row_num'])) {
			// Error
			return false;
		}
		return true;
	}

/**
 * コンテンツ情報バリデータ(移動、ペースト、ショートカット作成時)
 * <pre>
 * ショートカットブロックの移動、ペースト、ショートカット作成を許さない
 * （但し、ショートカット先コンテンツルームも主坦か、同じルーム内での操作であれば可能とする）
 * </pre>
 * @param  Content Model $content
 * @param  Page Model $pre_page
 * @return mixed boolean true or string Error Mes
 * @since   v 3.0.0.0
 */
	public function validatorRequestContent($content, $pre_page, $page) {
		if($pre_page['Page']['room_id'] == $page['Page']['room_id']) {
			// 同じルーム内
			return true;
		}
		$room_id = $content['Content']['room_id'];

		if($pre_page['Page']['room_id'] != $room_id || $content['Content']['shortcut_type'] != NC_SHORTCUT_TYPE_OFF) {
			// ショートカット
			$userId = $this->_controller->Auth->user('id');
			$masterContent = $this->_controller->Content->findAuthById($content['Content']['master_id'], $userId);
			if(!isset($masterContent['Content'])) {
				// error
				return __d('block','Because an origin of contents is deleted, You can\'t be operated.');
			}
			if($masterContent['PageAuthority']['hierarchy'] < NC_AUTH_MIN_CHIEF) {
				return __d('block','Because there is not the room editing authority of the origin of contents, You can\'t be operated.');
			}
		}
		return true;
	}

/**
 * 列移動・追加処理
 *
 * @param object  $block		移動block
 * @param integer $parent_id	移動先parent_id
 * @param integer $col_num		移動先列数
 * @param integer $row_num		移動先行数
 * @param object  $page			移動元page
 * @param object  $insert_page	移動先page
 *
 * @return boolean true or false
 * @since   v 3.0.0.0
 */
	public function InsertCell($block, $parent_id, $col_num, $row_num, $page, $insert_page=null)
	{
		$id = $block['Block']['id'];
		$page_id = $block['Block']['page_id'];
		$pre_parent_id = $block['Block']['parent_id'];
		$pre_col_num = $block['Block']['col_num'];
		$pre_row_num = $block['Block']['row_num'];
		$insert_room_id = isset($insert_page['Page']['room_id']) ? $insert_page['Page']['room_id'] : null;

		$pre_count_row_num = $this->_controller->BlockOperation->findRowCount($page_id, $pre_parent_id, $pre_col_num);

		//前詰め処理(移動元)
		$result = $this->_controller->BlockOperation->decrementRowNum($block);
		if(!$result) {
			return false;
		}
		if($pre_count_row_num == 1) {
			//移動前の列が１つしかなかったので
			//列--
			$result = $this->_controller->BlockOperation->decrementColNum($block);
			if(!$result) {
				return false;
			}
		}

		//UpdateCol
		if($parent_id == 0) {
			$root_id = $block['Block']['id'];
			$thread_num = 0;
		} else {
			$parent_block = $this->_controller->Block->findById($parent_id);
			if($parent_block['Block']['root_id'] != 0) {
				$root_id = $parent_block['Block']['root_id'];
			} else {
				$root_id = $parent_block['Block']['id'];
			}
			$thread_num = $parent_block['Block']['thread_num'] + 1;
		}

		$fields = array(
			'Block.parent_id'=>$parent_id,
    		'Block.root_id'=>$root_id,
    		'Block.col_num'=>$col_num,
    		'Block.row_num'=>$row_num,
			'Block.thread_num'=>$thread_num
    	);
		if($insert_page) {
			$fields['Block.page_id'] = $insert_page['Page']['id'];

			// 移動元のPage.room_id == Content.room_idならば、移動先のコンテンツとして更新
			if($page['Page']['room_id'] == $block['Content']['room_id']) {
				// Content更新
				$content_fields = array(
					'Content.shortcut_type'=> NC_SHORTCUT_TYPE_OFF,
		    		'Content.master_id'=>$block['Block']['content_id'],
		    		'Content.room_id'=>$insert_room_id
		    	);
				$content_conditions = array(
					"Content.id" => $block['Block']['content_id']
				);
				if(!$this->_controller->Content->updateAll($content_fields, $content_conditions)) {
					return false;
				}
			}
		}
		$conditions = array(
			"Block.id" => $id
		);
		$result = $this->_controller->Block->updateAll($fields, $conditions);
    	if(!$result) {
			return false;
		}

		//更新したブロックの子供のroot_id, page_id, Content.room_id更新処理
		//if($root_id==0)
		//	$root_id = $id;

		if($block['Block']['controller_action'] == "group") {
			$this->_updRootIdByParentId($block['Block']['id'], $root_id, $thread_num+1, $page, $insert_page);
		}

		//グループ化した空ブロック削除処理
		if($pre_count_row_num == 1) {
			$this->deleteGroupingBlock($pre_parent_id, $id);
		}

		//移動先より大きな列+1
		$buf_block = $block;
		$buf_block['Block']['parent_id'] = $parent_id;
		$buf_block['Block']['col_num'] = $col_num;
		if($insert_page) {
			$buf_block['Block']['page_id'] = $insert_page['Page']['id'];
		}

		$result = $this->_controller->BlockOperation->incrementColNum($buf_block);
		if(!$result) {
			return false;
		}
		return true;
	}

/**
 * 行移動・追加処理
 *
 * @param object  $block		移動block
 * @param integer $parent_id	移動先parent_id
 * @param integer $col_num		移動先列数
 * @param integer $row_num		移動先行数
 * @param object  $page			移動元page
 * @param object  $insert_page	移動先page
 *
 * @return boolean true or false
 * @since   v 3.0.0.0
 */
	public function InsertRow($block, $parent_id, $col_num, $row_num, $page, $insert_page)
	{
		$id = $block['Block']['id'];
		$pre_page_id = $block['Block']['page_id'];
		$pre_parent_id = $block['Block']['parent_id'];
		$pre_col_num = $block['Block']['col_num'];
		$pre_row_num = $block['Block']['row_num'];
		$insert_room_id = isset($insert_page['Page']['room_id']) ? $insert_page['Page']['room_id'] : null;

		$pre_count_row_num = $this->_controller->BlockOperation->findRowCount($pre_page_id, $pre_parent_id, $pre_col_num);

		//前詰め処理(移動元)
		$result = $this->_controller->BlockOperation->decrementRowNum($block);
		if(!$result) {
			return false;
		}
		if($pre_count_row_num == 1) {
			//移動前の列が１つしかなかったので
			//列--
			$result = $this->_controller->BlockOperation->decrementColNum($block);
			if(!$result) {
				return false;
			}
		}

		//UpdateRow
		if($parent_id == 0) {
			$root_id = $id;
			$thread_num = 0;
		} else {
			$parent_block = $this->_controller->Block->findById($parent_id);
			if($parent_block['Block']['root_id'] != 0) {
				$root_id = $parent_block['Block']['root_id'];
			} else {
				$root_id = $parent_block['Block']['id'];
			}
			$thread_num = $parent_block['Block']['thread_num'] + 1;
		}

		$fields = array(
			'Block.parent_id'=>$parent_id,
    		'Block.root_id'=>$root_id,
    		'Block.col_num'=>$col_num,
    		'Block.row_num'=>$row_num,
			'Block.thread_num'=>$thread_num
    	);
		if($insert_page['Page']['id'] != $page['Page']['id']) {
			// ブロック移動時
			$fields['Block.page_id'] = $insert_page['Page']['id'];

			/*
			 * ほかのルームでショートカットを作成し、それを元のルームに戻したら(移動)、ショートカットの
			 * コンテンツを削除する。ブロックもショートカットを解除する。
			 */
			if($block['Content']['shortcut_type'] != NC_SHORTCUT_TYPE_OFF) {	// 権限を付与したショートカット
				$content = $this->_controller->Content->findById($block['Content']['master_id']);
				if($content['Content']['room_id'] == $insert_room_id) {
					// 元のルームに戻した(権限を付与してあるショートカット)
					// ショートカット削除処理
					$result = $this->_controller->Content->delete($block['Content']['id']);
					if(!$result) {
						return false;
					}

					$fields['Block.content_id'] = $block['Content']['master_id'];
				}
			}

			// 移動元のPage.room_id == Content.room_idならば、移動先のコンテンツとして更新
			if(!isset($fields['Block.content_id']) && $page['Page']['room_id'] == $block['Content']['room_id']) {
				// Content更新
				$content_fields = array(
		    		'Content.room_id'=> $insert_room_id
		    	);
		    	if($block['Content']['master_id'] == $block['Block']['content_id']) {
					$content_fields['Content.master_id'] = $block['Block']['content_id'];
				}

				$content_conditions = array(
					'Content.id' => $block['Block']['content_id']
				);
				if(!$this->_controller->Content->updateAll($content_fields, $content_conditions)) {
					return false;
				}
			}
		}
		$conditions = array(
			"Block.id" => $id
		);
		$result = $this->_controller->Block->updateAll($fields, $conditions);
		if(!$result) {
			return false;
		}

		//更新したブロックの子供のroot_id更新処理
		if($block['Block']['controller_action'] == "group")
			$this->_updRootIdByParentId($id, $root_id, $thread_num+1, $page, $insert_page);


		//グループ化した空ブロック削除処理
		if($pre_count_row_num == 1) {
			$this->deleteGroupingBlock($pre_parent_id, $id);
		}

		//前詰め処理（移動先)
		$buf_block = $block;
		$buf_block['Block']['parent_id'] = $parent_id;
		$buf_block['Block']['col_num'] = $col_num;
		$buf_block['Block']['row_num'] = $row_num - 1;
		if($insert_page['Page']['id'] != $page['Page']['id']) {
			$buf_block['Block']['page_id'] = $insert_page['Page']['id'];
		}
		$result = $this->_controller->BlockOperation->incrementRowNum($buf_block);

		if(!$result) {
			return false;
		}

		return true;
	}

/**
 * parent_idからroot_id, page_id, Content.room_id更新処理
 *
 * @param integer $parent_id	移動先parent_id
 * @param integer $root_id     移動元root_id
 * @param integer $thread_num  移動元thread_num
 * @param object  $page			移動元page
 * @param object  $insert_page	移動先page
 *
 * @return boolean true or false
 * @since   v 3.0.0.0
 */
	protected function _updRootIdByParentId($parent_id, $root_id, $thread_num=0, $page, $insert_page)
	{
		$user_id = $this->_controller->Auth->user('id');

		$insert_room_id = isset($insert_page['Page']['room_id']) ? $insert_page['Page']['room_id'] : null;

		$conditions = array('Block.parent_id =' => $parent_id);
		$blocks = $this->_controller->Block->findUsers("all", $conditions, $user_id);
		foreach($blocks as $block) {
    		if($block['Block']['controller_action'] == "group"){
    			$this->_updRootIdByParentId($block['Block']['id'],$root_id,$thread_num+1, $page, $insert_page);
    		}
			if($insert_page) {
				// 移動元のPage.room_id == Content.room_idならば、移動先のコンテンツとして更新
				if($page['Page']['room_id'] == $block['Content']['room_id']) {
					// Content更新
					$content_fields = array(
						'Content.shortcut_type'=>NC_SHORTCUT_TYPE_OFF,
			    		'Content.master_id'=>$block['Block']['content_id'],
			    		'Content.room_id'=>$insert_room_id
			    	);
					$content_conditions = array(
						"Content.id" => $block['Block']['content_id']
					);
					if(!$this->_controller->Content->updateAll($content_fields, $content_conditions)) {
						return false;
					}
				}
			}
    	}

    	$fields = array(
    		'Block.root_id'=>$root_id,
    		'Block.thread_num'=>$thread_num
    	);
		if($insert_page) {
			$fields['Block.page_id'] = $insert_page['Page']['id'];
		}
		$conditions = array(
			"Block.parent_id" => $parent_id
		);
		$result = $this->_controller->Block->updateAll($fields, $conditions);
		if(!$result) {
			return false;
		}
		return true;
	}

/**
 * グループ化した空ブロック削除処理
 *
 * @param integer $parent_id
 * @param integer $block_id	操作対象block_id
 *
 * @return boolean true or false
 * @since   v 3.0.0.0
 */
	public function deleteGroupingBlock($parent_id, $block_id = null)
	{
		$block = $this->_controller->Block->findById($parent_id);
		if(!empty($block)) {

			$block_count = $this->_controller->Block->find('count', array(
				'fields' => 'COUNT(*) as count',
				'recursive' => -1,
				'conditions' => array(
					'Block.parent_id' => $parent_id
				)
			));

			if($block_count == 0) {
			    //削除処理
				$this->_controller->Content->delete($block['Block']['content_id']);
			    $this->_controller->Block->delete($parent_id);
			    if($block_id) {
			    	// 操作対象block_idがあれば、更新対象にしない
			    	$block['Block']['id'] = $block_id;
			    }
			    //前詰め処理(移動元)
			    $result = $this->_controller->BlockOperation->decrementRowNum($block);
				if(!$result) {
					return false;
				}
				$count_row_num = $this->_controller->BlockOperation->findRowCount($block['Block']['page_id'], $block['Block']['parent_id'], $block['Block']['col_num']);
				if($count_row_num == 0) {
					//削除列が１つもなくなったので
					//列--
					$result = $this->_controller->BlockOperation->decrementColNum($block);
					if(!$result) {
						return false;
					}
				}
			    //再帰処理
			    if($block['Block']['parent_id'] != 0) {
				    $result = $this->deleteGroupingBlock($block['Block']['parent_id'], $block_id);
				    if(!$result) {
						return false;
					}
			    }
			}
		}
		return true;
	}

/**
 * グループブロック(ブロック)内の最大の深さを取得
 * @param   array $block
 * @return  void
 * @since   v 3.0.0.0
 */
	public function maxThreadNum($block) {
		$max_thread_num = $block['Block']['thread_num'];

		if($block['Block']['controller_action'] == 'group') {
			$params = array(
				'conditions' => array(
					'Block.parent_id' => $block['Block']['id']
				),
				'fields' => array('Block.id', 'Block.controller_action','Block.thread_num'),
				'recursive' =>  -1
			);
			$child_blocks = $this->_controller->Block->find('all', $params);
			foreach($child_blocks as $child_block) {
				$max_child_thread_num = $this->maxThreadNum($child_block);
				if($max_child_thread_num > $max_thread_num) {
					$max_thread_num = $max_child_thread_num;
				}
			}
		}
		return $max_thread_num;
	}
}