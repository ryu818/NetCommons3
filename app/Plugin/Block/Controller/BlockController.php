<?php
/**
 * BlockControllerクラス
 *
 * <pre>
 * ブロック操作（ブロック追加、削除、ブロックテーマ、ブロック移動、グループ化）用コントローラ
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       App.Plugin.Controller
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class BlockController extends BlockAppController {

	public $components = array('Block.BlockMove', 'CheckAuth' => array('allowAuth' => NC_AUTH_CHIEF));
	public $uses = array('Block.BlockOperation');
	
	public $nc_block = array();
	public $nc_page = array();

/**
 * ブロック追加
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	function add_block() {
		$user_id = $this->Auth->user('id');
		$page_id = $this->request->data['page_id'];
		$module_id = $this->request->data['module_id'];
		$show_count = $this->request->data['show_count'];
		$page = $this->Page->findByIds(intval($page_id), $user_id);
		
		if(!$page || $page['Authority']['hierarchy'] < NC_AUTH_MIN_CHIEF) {
			$this->flash(__('Authority Error!  You do not have the privilege to access this page.'), null, 'add_block.001', '403');
			return;
		}
		
		if($page['Page']['show_count'] != $show_count) {
			$this->flash(__d('block', 'Because of the possibility of inconsistency happening, update will not be executed. <br /> Please redraw and update again.'), null, 'add_block.002', '400');
			return;
		}
		
		$module = $this->Module->findById($module_id);
		if(!$module) {
			$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'add_block.003', '400');
			return;
		}
	
		// TODO: そのmoduleが該当ルームに貼れるかどうかのチェックが必要。
		// グループ化ブロック（ショートカット）ならば、該当グループ内のmoduleのチェックが必要。
		// はりつけたあと、表示されませんで終わらす方法も？？？
		
		$ins_content['Content'] = array(
				'module_id' => $module['Module']['id'],
				'title' => $module['Module']['module_name'],
				'is_master' => _ON,
				'room_id' => $page['Page']['room_id'],
				'accept_flag' => NC_ACCEPT_FLAG_ON,
				'url' => ''
		);
		$ins_ret = $this->Content->save($ins_content);
		if(!$ins_ret) {
			$this->flash(__('Failed to insert the database, (%s).', 'contents'), null, 'add_block.004', '400');
			return;
		}
		$last_content_id = $this->Content->id;
		
		if(!isset($ins_content['Content']['master_id'])) {
			if(!$this->Content->saveField('master_id', $last_content_id)) {
				$this->flash(__('Failed to update the database, (%s).', 'contents'), null, 'add_block.005', '400');
				return;
			}
		}
		
		$ins_block = array();
		$ins_block = $this->BlockOperation->defaultBlock($ins_block);
		$ins_block['Block'] = array_merge($ins_block['Block'], array(
				'page_id' => $page['Page']['id'],
				'module_id' => $module['Module']['id'],
				'content_id' => $last_content_id,
				'controller_action' => $module['Module']['controller_action'],
				'theme_name' => '',
				'root_id' => 0,
				'parent_id' => 0,
				'thread_num' => 0,
				'col_num' => 1,
				'row_num' => 1
		));
		
		$ins_ret = $this->Block->save($ins_block);
		if(!$ins_ret) {
			$this->flash(__('Failed to insert the database, (%s).', 'blocks'), null, 'add_block.006', '400');
			return;
		}
		
		//root_idを再セット
		$last_id = $this->Block->id;
		if(!$this->Block->saveField('root_id', $last_id)) {
			$this->flash(__('Failed to update the database, (%s).', 'blocks'), null, 'add_block.007', '400');
			return;
		}
		
		$ins_ret['Block']['id'] = $this->Block->id;
		$ins_ret['Block']['root_id'] = $this->Block->id;
		$ins_ret['Block']['row_num'] = 0;
		$inc_ret = $this->BlockOperation->incrementRowNum($ins_ret);
		if(!$inc_ret) {
			$this->flash(__('Failed to update the database, (%s).', 'blocks'), null, 'add_block.008', '400');
			return;
		}
		
		// 表示カウント++
		$this->Page->id = $page_id;
		if(!$this->Page->saveField('show_count', intval($show_count) + 1)) {
			$this->flash(__('Failed to update the database, (%s).', 'pages'), null, 'add_block.009', '400');
			return;
		}

		$params = array('block_id' => $last_id);
		$controller_arr = explode('_', $module['Module']['edit_controller_action'], 2);
		$params['plugin'] = $params['controller'] = $controller_arr[0];
		if(isset($controller_arr[1])) {
			$params['action'] = $controller_arr[1];
		}
		$this->redirect($params);
	}

/**
 * ブロック削除
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function del_block() {
		$block = $this->nc_block;
		$page = $this->nc_page;
		$block_id = $block['Block']['id'];
		$page_id = $page['Page']['id'];
		$show_count = $this->request->data['show_count'];
		$all_delete = $this->request->data['all_delete'];

		if(!$page || $page['Page']['show_count'] != $show_count) {
			$this->flash(__d('block', 'Because of the possibility of inconsistency happening, update will not be executed. <br /> Please redraw and update again.'), null, 'del_block.001', '400');
			return;
		}

		// --------------------------------------
		// --- 前詰め処理(移動元)		      ---
		// --------------------------------------
		$dec_ret = $this->BlockOperation->decrementRowNum($block);
		if(!$dec_ret) {
			$this->flash(__('Failed to update the database, (%s).', 'blocks'), null, 'del_block.002', '400');
			return;
		}

		$count_row_num = $this->BlockOperation->findRowCount($block['Block']['page_id'], $block['Block']['parent_id'], $block['Block']['col_num']);
		if($count_row_num == 1) {
			//移動前の列が１つしかなかったので
			//列--
			$dec_ret = $this->BlockOperation->decrementColNum($block);
			if(!$dec_ret) {
				$this->flash(__('Failed to update the database, (%s).', 'blocks'), null, 'del_block.003', '400');
				return;
			}
		}

        // --------------------------------------
		// --- ブロック削除処理     	      ---
		// --------------------------------------
		if(!$this->Block->deleteBlock($block, $all_delete)) {
			$this->flash(__('Failed to delete the database, (%s).', 'blocks'), null, 'del_block.004', '400');
			return;
		}

		//グループ化した空ブロック削除処理
		if($count_row_num == 1) {
			if(!$this->BlockMove->delGroupingBlock($block['Block']['parent_id'])) {
				$this->flash(__('Failed to delete the database, (%s).', 'blocks'), null, 'del_block.005', '400');
				return;
			}
		}

		// 表示カウント++
		$this->Page->id = $page_id;
		if(!$this->Page->saveField('show_count', intval($show_count) + 1)) {
			$this->flash(__('Failed to update the database, (%s).', 'pages'), null, 'del_block.006', '400');
			return;
		}

		$this->render("/Commons/true");
		return;
	}

/**
 * ブロック移動 - 行移動
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function insert_row() {
		$user_id = $this->Auth->user('id');
		$insert_page = null;
		$block = $this->nc_block;
		$page = $this->nc_page;
		$page_id = $page['Page']['id'];
		$show_count = $this->request->data['show_count'];

		if(isset($this->request->data['insert_page_id'])) {
			$insert_page = $this->Page->findByIds(intval($request->data['insert_page_id']), $user_id);
			if(!$insert_page || $insert_page['Authority']['hierarchy'] < NC_AUTH_MIN_CHIEF) {
				$this->flash(__('Authority Error!  You do not have the privilege to access this page.'), null, 'insert_row.001', '403');
				return;
			}
		}

		if(!$this->BlockMove->validatorRequest($this->request, $insert_page)) {
			// Error
			$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'insert_row.002', '400');
			return;
		}

		if(!$page || $page['Page']['show_count'] != $show_count) {
			$this->flash(__d('block', 'Because of the possibility of inconsistency happening, update will not be executed. <br /> Please redraw and update again.'), null, 'insert_row.003', '400');
			return;
		}

		// TODO: ShowCountのチェック(insert_page_id)

		

		$ret = $this->BlockMove->InsertRow($block, $this->request->data['parent_id'], $this->request->data['col_num'], $this->request->data['row_num'], $page, $insert_page);
		if(!$ret) {
			$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'insert_row.005', '400');
			return;
		}

		// 表示カウント++
		$this->Page->id = $page_id;
		if(!$this->Page->saveField('show_count', intval($show_count) + 1)) {
			$this->flash(__('Failed to update the database, (%s).', 'pages'), null, 'insert_row.006', '400');
			return;
		}

		$this->render("/Commons/true");
	}

/**
 * ブロック移動 - 列追加
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function insert_cell() {
		$user_id = $this->Auth->user('id');
		$insert_page = null;
		$block = $this->nc_block;
		$page = $this->nc_page;
		$page_id = $page['Page']['id'];
		$show_count = $this->request->data['show_count'];

		if(isset($this->request->data['insert_page_id'])) {
			$insert_page = $this->Page->findByIds(intval($request->data['insert_page_id']), $user_id);
			if(!$insert_page || $insert_page['Authority']['hierarchy'] < NC_AUTH_MIN_CHIEF) {
				$this->flash(__('Authority Error!  You do not have the privilege to access this page.'), null, 'insert_cell.001', '403');
				return;
			}
		}

		if(!$this->BlockMove->validatorRequest($this->request, $insert_page)) {
			// Error
			$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'insert_cell.002', '400');
			return;
		}

		if(!$page || $page['Page']['show_count'] != $show_count) {
			$this->flash(__d('block', 'Because of the possibility of inconsistency happening, update will not be executed. <br /> Please redraw and update again.'), null, 'insert_cell.003', '400');
			return;
		}

		// TODO: ShowCountのチェック(insert_page_id)

		$ret = $this->BlockMove->InsertCell($block,  $this->request->data['parent_id'], $this->request->data['col_num'], $this->request->data['row_num'], $page, $insert_page);
		if(!$ret) {
			$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'insert_cell.005', '400');
			return;
		}

		// 表示カウント++
		$this->Page->id = $page_id;
		if(!$this->Page->saveField('show_count', intval($show_count) + 1)) {
			$this->flash(__('Failed to update the database, (%s).', 'pages'), null, 'insert_cell.006', '400');
			return;
		}

		$this->render("/Commons/true");
	}

/**
 * ブロックグループ化
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function add_group() {

		if(!is_array($this->request->data['groups']) || count($this->request->data['groups']) == 0) {
			// Error
			$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'add_group.001', '400');
			return;
		}

		$block = $this->nc_block;
		$page = $this->nc_page;
		$page_id = $page['Page']['id'];
		$show_count = $this->request->data['show_count'];

		if(!$page || $page['Page']['show_count'] != $show_count) {
			$this->flash(__d('block', 'Because of the possibility of inconsistency happening, update will not be executed. <br /> Please redraw and update again.'), null, 'add_group.002', '400');
			return;
		}

		$block_arr = $this->request->data['groups'];
// TODO: Validatorとして切り出すほうがよい
		$max_col_num = 1;
		$upd_block_id_arr = array();
		$ret = array();
		$ret_pos = array();
		foreach($block_arr as $block_id) {
			$block_id = intval($block_id);
			if($block_id == 0) {
				continue;
			}

			$group_block = $this->Block->findById($block_id);
			$ret_pos[$group_block['Block']['col_num']][$group_block['Block']['row_num']] = $group_block;
			$max_thread_num = $this->BlockMove->maxThreadNum($group_block);
			if($max_thread_num >= 5) {
				$this->flash(__d('block', 'More than this, can not be grouped complex.'), null, 'add_group.003');
				return;
			}
			if($group_block['Block']['page_id'] != $page_id ||
				(!empty($pre_block) && ($group_block['Block']['page_id'] != $pre_block['Block']['page_id'] ||
					 $group_block['Block']['parent_id'] != $pre_block['Block']['parent_id'] ||
					 in_array($block_id, $upd_block_id_arr)))) {
				// グループ化する基点とpage_id,parent_id相違
				$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'add_group.004', '400');
				return;
			}
			$upd_block_id_arr[] = $block_id;
			$pre_block = $group_block;
		}
// Validator End

		// 左上の基点を中心に並び替え
		ksort($ret_pos);
		foreach($ret_pos as $k => $v) {
			ksort($ret_pos[$k]);
		}
		foreach($ret_pos as $k => $v) {
			foreach($v as $k_sub => $v_sub) {
				$ret[] = $v_sub;
			}
		}

		$pos = array();
		$upd_blocks = array();

		// update
		foreach($ret as $key => $group_block) {

			//if(empty($group_block)) {
			//	continue;
			//}
			$block_id = intval($group_block['Block']['id']);
			if($key == 0) {
				// グループ化する基点
				/*
				 * Content Insert
				 */
				$ins_content['Content'] = array(
					'module_id' => 0,
					'is_master' => _ON,
					'title' => __d('block', 'New group'),
					'room_id' => $page['Page']['room_id'],
					'accept_flag' => NC_ACCEPT_FLAG_ON,
					'url' => ''
				);
				$this->Content->create();
				$ins_ret = $this->Content->save($ins_content);
				if(!$ins_ret) {
					$this->flash(__('Failed to register the database, (%s).', 'contents'), null, 'add_group.005', '400');
					return;
				}
				$last_content_id = $this->Content->id;
				if(!$this->Content->saveField('master_id', $last_content_id)) {
					$this->flash(__('Failed to update the database, (%s).', 'contents'), null, 'add_group.006', '400');
					return;
				}

				/*
				 * Block Insert
				 */
				$ins_block['Block'] = $group_block['Block'];
				$ins_block = $this->BlockOperation->defaultBlock($ins_block);
				$ins_block['Block']['content_id'] = $this->Content->id;
				//$ins_block['Block']['title'] = __d('block', 'New group');

				$ins_ret = $this->Block->save($ins_block);
				if(!$ins_ret) {
					$this->flash(__('Failed to register the database, (%s).', 'blocks'), null, 'add_group.007', '400');
					return;
				}
				$last_id = $this->Block->id;
				$ins_ret['Block']['id'] = $last_id;
				//$ins_ret['Block']['parent_id'] = $last_id;
				if($ins_ret['Block']['root_id'] == $block_id) {
					$ins_ret['Block']['root_id'] = $last_id;
				}

				$ins_ret = $this->Block->save($ins_ret);
				if(!$ins_ret) {
					$this->flash(__('Failed to update the database, (%s).', 'blocks'), null, 'add_group.008', '400');
					return;
				}

				$upd_blocks[$key]['Block'] = $group_block['Block'];
				$upd_blocks[$key]['Block']['col_num'] = 1;
				$upd_blocks[$key]['Block']['row_num'] = 1;
				$pos[0][0] = _ON;
			} else {
				$upd_blocks[$key]['Block'] = $group_block['Block'];

				$col_num = count($pos);
				if($group_block['Block']['col_num'] - ($ins_block['Block']['col_num'] - 1) > $max_col_num) {
					$pos[++$col_num - 1][0] = _ON;
					$max_col_num = $group_block['Block']['col_num'];
				} else {	//if($group_block['Block']['col_num'] > $col_num) {
					$pos[$col_num - 1][] = _ON;
				}
				$upd_blocks[$key]['Block']['col_num'] = $col_num;
				$upd_blocks[$key]['Block']['row_num'] = count($pos[$col_num - 1]);

				//前詰め処理(移動元)
				$dec_ret = $this->BlockOperation->decrementRowNum($group_block);
				if(!$dec_ret) {
					$this->flash(__('Failed to update the database, (%s).', 'blocks'), null, 'add_group.009', '400');
					return;
				}
				//$dec_row_num--;

				$count_row_num = $this->Block->find('count', array(
					'recursive' => -1,
					'conditions' => array(
						"page_id" => $group_block['Block']['page_id'],
						"parent_id" => $group_block['Block']['parent_id'],
						"col_num" => $group_block['Block']['col_num']
					)
				));

				if($count_row_num == 1) {
					//移動前の列が１つしかなかったので
					//列--
					$dec_ret = $this->BlockOperation->decrementColNum($group_block);
					if(!$dec_ret) {
						$this->flash(__('Failed to update the database, (%s).', 'blocks'), null, 'add_group.010', '400');
						return;
					}
				}

			}
			$root_id = $upd_blocks[$key]['Block']['root_id'];
			$upd_blocks[$key]['Block']['thread_num'] = ++$upd_blocks[$key]['Block']['thread_num'];
			$upd_blocks[$key]['Block']['parent_id'] = $last_id;
			$upd_blocks[$key]['Block']['root_id'] = $ins_ret['Block']['root_id'];
			//$this->Block->create($upd_blocks[$key - 1]);
			$upd_ret = $this->Block->save($upd_blocks[$key]);
			if(!$upd_ret) {
				$this->flash(__('Failed to update the database, (%s).', 'blocks'), null, 'add_group.011', '400');
				return;
			}
			//グループ化しているブロックならば,そのグループの子供を求める
	    	if($upd_blocks[$key]['Block']['controller_action'] == "group") {
	    		$block_children =& $this->Block->findByRootId($root_id);
	    		$parent_id_arr = array($upd_blocks[$key]['Block']['id']);
	    		foreach ($block_children as $block_child) {
	    			if(in_array($block_child['Block']['parent_id'], $parent_id_arr)) {
	    				$parent_id_arr[] = $block_child['Block']['id'];
	    			} else {
	    				continue;
	    			}

		    		$block_child['Block']['root_id'] = $ins_ret['Block']['root_id'];
		    		$block_child['Block']['thread_num'] = intval($block_child['Block']['thread_num']) + 1;
		    		$save_ret = $this->Block->save($block_child);
		    		if(!$save_ret) {
						$this->flash(__('Failed to update the database, (%s).', 'blocks'), null, 'add_group.012', '400');
						return;
					}
	    		}
	    	}
		}

		// 表示カウント++
		$this->Page->id = $page_id;
		if(!$this->Page->saveField('show_count', intval($show_count) + 1)) {
			$this->flash(__('Failed to update the database, (%s).', 'pages'), null, 'add_group.013', '400');
			return;
		}
		
		$params = array('block_id' => $last_id, 'plugin' => 'group', 'controller' => 'group', 'action' => 'index');
		echo ($this->requestAction($params, array('return')));
		$this->render("/Commons/empty");
	}
/**
 * ブロックグループ化解除
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function cancel_group() {
		if(!is_array($this->request->data['cancel_groups']) || count($this->request->data['cancel_groups']) == 0) {
			// Error
			$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'cancel_group.001', '400');
			return;
		}

		$block = $this->nc_block;
		$page = $this->nc_page;
		$page_id = $page['Page']['id'];
		$show_count = $this->request->data['show_count'];

		if(!$page || $page['Page']['show_count'] != $show_count) {
			$this->flash(__d('block', 'Because of the possibility of inconsistency happening, update will not be executed. <br /> Please redraw and update again.'), null, 'add_group.002', '400');
			return;
		}

		$block_arr = $this->request->data['cancel_groups'];

// TODO: Validatorとして切り出すほうがよい
		$ret = array();
		$upd_block_id_arr = array();
		foreach($block_arr as $block_id) {
			$block_id = intval($block_id);
			if($block_id == 0) {
				continue;
			}

			$group_block = $this->Block->findById($block_id);

			if($group_block['Block']['controller_action'] != 'group' || $group_block['Block']['page_id'] != $page_id ||
				(!empty($pre_block) && ($group_block['Block']['page_id'] != $pre_block['Block']['page_id'] ||
					 $group_block['Block']['parent_id'] != $pre_block['Block']['parent_id'] ||
					 in_array($block_id, $upd_block_id_arr)))) {
				// グループ化する基点とpage_id,parent_id相違
				$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'cancel_group.003', '400');
				return;
			}
			$upd_block_id_arr[] = $block_id;
			$pre_block = $group_block;
			$ret[$block_id] = $group_block;
		}

// Validator End
		foreach($ret as $block_id => $group_block) {
			//グルーピングブロック削除
    		$this->Block->delete($group_block['Block']['id']);
    		$this->Content->delete($group_block['Block']['content_id']);

    		$params = array(
				'conditions' => array('Block.parent_id =' => $block_id),
				'fields' => array('Block.*'),
				'recursive' =>  -1
			);
			$blocks = $this->Block->find("all", $params);

			$row_count = -1;
	    	$col_count = 0;
	    	if(!empty($blocks)) {
		    	foreach($blocks as $sub_block) {
		    		if($sub_block['Block']['col_num'] == 1) {
		    			$row_count++;
		    		} else if($col_count < $sub_block['Block']['col_num'] - 1) {
		    			$col_count = $sub_block['Block']['col_num'] - 1;
		    		}
		    	}
	    	}

    		//親移動
	    	if($row_count != 0) {
			$inc_ret = $this->BlockOperation->incrementRowNum($group_block, $row_count);
				if(!$inc_ret) {
					$this->flash(__('Failed to update the database, (%s).', 'blocks'), null, 'cancel_group.004', '400');
					return;
				}
	    	}
			if($col_count != 0) {
				$buf_group_block = $group_block;
				$buf_group_block['Block']['col_num']++;
				$inc_ret = $this->BlockOperation->incrementColNum($buf_group_block, $col_count);
				if(!$inc_ret) {
					$this->flash(__('Failed to update the database, (%s).', 'blocks'), null, 'cancel_group.005', '400');
					return;
				}
			}

			//
	    	// グルーピング解除処理
	    	//
	    	foreach($blocks as $sub_block) {
	    		if($group_block['Block']['id'] != $group_block['Block']['root_id'])
	    			$root_id = $block['Block']['root_id'];
	    		else
	    			$root_id = $sub_block['Block']['id'];

			    $sub_block['Block']['parent_id'] = $group_block['Block']['parent_id'];
			    $pre_root_id = $sub_block['Block']['root_id'];
			    $sub_block['Block']['root_id'] = $root_id;
			    $sub_block['Block']['thread_num'] = $group_block['Block']['thread_num'];

	    		if($sub_block['Block']['col_num'] == 1) {
	    			$sub_block['Block']['col_num'] = intval($group_block['Block']['col_num']);
			    	$sub_block['Block']['row_num'] = intval($sub_block['Block']['row_num']) + intval($group_block['Block']['row_num']) - 1;
	    		} else {
	    			$sub_block['Block']['col_num'] = intval($sub_block['Block']['col_num']) + intval($group_block['Block']['col_num']) - 1;
	    		}

	    		//$this->Block->create();
	    		$save_ret = $this->Block->save($sub_block);
	    		if(!$save_ret) {
					$this->flash(__('Failed to update the database, (%s).', 'blocks'), null, 'cancel_group.006', '400');
					return;
				}
				//グループ化しているブロックならば,そのグループの子供を求める
				if($sub_block['Block']['controller_action'] == "group") {
		    		$block_children =& $this->Block->findByRootId($pre_root_id);
		    		$parent_id_arr = array($sub_block['Block']['id']);
		    		foreach ($block_children as $block_child) {
		    			if(in_array($block_child['Block']['parent_id'], $parent_id_arr)) {
		    				$parent_id_arr[] = $block_child['Block']['id'];
		    			} else {
		    				continue;
		    			}

			    		$block_child['Block']['root_id'] = $root_id;
			    		$block_child['Block']['thread_num'] = intval($block_child['Block']['thread_num']) - 1;
			    		//$this->Block->create();
			    		$save_ret = $this->Block->save($block_child);
			    		if(!$save_ret) {
							$this->flash(__('Failed to update the database, (%s).', 'blocks'), null, 'cancel_group.007', '400');
							return;
						}
		    		}
		    	}
	    	}
		}

		// 表示カウント++
		$this->Page->id = $page_id;
		if(!$this->Page->saveField('show_count', intval($show_count) + 1)) {
			$this->flash(__('Failed to update the database, (%s).', 'pages'), null, 'cancel_group.008', '400');
			return;
		}

		$this->render("/Commons/true");
	}
}