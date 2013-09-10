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

	public $components = array('Block.BlockMove', 'CheckAuth' => array('allowAuth' => NC_AUTH_CHIEF, 'chkPlugin' => false, 'checkOrder' => array("request", "url")));
	public $uses = array('Block.BlockOperation');

/**
 * 実行前処理
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function beforeFilter()
	{
		if(empty($this->request->params['requested']) && $this->action == 'add_block') {
			$this->CheckAuth->chkBlockId = false;
		} else if(($this->action == 'add_block' || $this->action == 'insert_row') && !empty($this->request->params['requested'])) {
			$this->CheckAuth->chkMovedPermanently = false;
		}
		parent::beforeFilter();
	}

/**
 * ブロック追加
 * ブロック操作 - ペースト、ショットカット作成処理
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function add_block() {
		$user_id = $this->Auth->user('id');
		$page = (!empty($this->request->params['requested'])) ? $this->nc_current_page : $this->nc_page;
		$page_id = $page['Page']['id'];
		$module_id = $this->request->data['module_id'];
		$show_count = $this->request->data['show_count'];
		$pre_page = $page;
		$copy_block_id = intval($this->Session->read('Blocks.'.'copy_block_id'));
		$copy_content_id = intval($this->Session->read('Blocks.'.'copy_content_id'));
		$shortcut_type = isset($this->request->data['shortcut_type']) ? $this->request->data['shortcut_type'] : null;

		if(!empty($this->request->params['requested']) && !empty($copy_block_id)) {
			$block = $this->Block->findById($copy_block_id);	// 再取得
			$pre_page = $this->Page->findAuthById(intval($block['Block']['page_id']), $user_id);
			if(!$pre_page || $pre_page['PageAuthority']['hierarchy'] < NC_AUTH_MIN_CHIEF) {
				$this->response->statusCode('403');
				$this->flash(__('Authority Error!  You do not have the privilege to access this page.'), '');
				return;
			}
			$content = array('Content' => $this->nc_block['Content']);
			$ret_validator = $this->BlockMove->validatorRequestContent($content, $pre_page, $page);
			if($ret_validator !== true) {
				throw new BadRequestException($ret_validator);
			}
		}

		if (!isset($this->request->data) || !isset($this->request->data['show_count'])
			|| !isset($this->request->data['module_id']) || !isset($this->request->data['page_id'])) {
			throw new BadRequestException(__('Unauthorized request.<br />Please reload the page.'));
		}

		if(!$page || $page['Page']['show_count'] != $show_count) {
			$this->response->statusCode('400');
			$this->flash(__d('block', 'Because of the possibility of inconsistency happening, update will not be executed. <br /> Please redraw and update again.'), '');
			return;
		}

		$module = $this->Module->findById($module_id);
		if(!$module) {
			throw new BadRequestException(__('Unauthorized request.<br />Please reload the page.'));
		}

		if(!empty($this->request->params['requested']) && !empty($copy_block_id)) {
			if(isset($shortcut_type)) {
				$title = 'Shortcut';
				$action = 'shortcut';
			} else {
				$title = 'Paste';
				$action = 'paste';
			}
			$ins_ret = $this->BlockOperation->addBlock($action, $pre_page, $module, $block, $content,
				$shortcut_type, $page);
		} else {
			$title = 'Add';
			$ins_ret = $this->BlockOperation->addBlock($this->action, $pre_page, $module);
		}
		if($ins_ret === false) {
			throw new InternalErrorException(__('Failed to execute the %s.', __($title)));
		}
		list($operation_ret, $ins_block, $ins_content) = $ins_ret;

		$last_id = $ins_block['Block']['id'];
		$ins_block['Block']['row_num'] = 0;
		$inc_ret = $this->BlockOperation->incrementRowNum($ins_block);
		if(!$inc_ret) {
			throw new InternalErrorException(__('Failed to update the database, (%s).', 'blocks'));
		}

		// 表示カウント++
		$this->Page->id = $page_id;
		if(!$this->Page->saveField('show_count', intval($show_count) + 1)) {
			throw new InternalErrorException(__('Failed to update the database, (%s).', 'pages'));
		}
		if($pre_page['Page']['id'] != $page['Page']['id']) {
			// 移動元表示カウント++(ブロック移動時)
			$this->Page->id = $pre_page['Page']['id'];
			if(!$this->Page->saveField('show_count', intval($pre_page['Page']['show_count']) + 1)) {
				throw new InternalErrorException(__('Failed to update the database, (%s).', 'pages'));
			}
		}

		if($operation_ret === false) {
			throw new InternalErrorException(__('Failed to execute the %s.', __($title)));
		}

		if(!empty($this->request->params['requested']) && !empty($copy_block_id)) {
			// ペースト OR ショートカット
			$this->autoRender = false;
			return $last_id;
		}

		if(isset($module['Module']['ini']['add_block_controller_action'])) {
			$params = $this->Common->explodeControllerAction($module['Module']['ini']['add_block_controller_action']);
		} else if(isset($module['Module']['ini']['edit_controller_action'])) {
			$params = $this->Common->explodeControllerAction($module['Module']['edit_controller_action']);
		} else {
			$params = $this->Common->explodeControllerAction($module['Module']['controller_action']);
		}

		$params['block_id'] = $last_id;
		$params['?'] = array('_nc_include_css' => 1);
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
			$this->response->statusCode('400');
			$this->flash(__d('block', 'Because of the possibility of inconsistency happening, update will not be executed. <br /> Please redraw and update again.'), '');
			return;
		}

		// ブロック削除処理
		if(!$this->Block->deleteBlock($block, $all_delete)) {
			throw new InternalErrorException(__('Failed to delete the database, (%s).', 'blocks'));
		}

		// 表示カウント++
		$this->Page->id = $page_id;
		if(!$this->Page->saveField('show_count', intval($show_count) + 1)) {
			throw new InternalErrorException(__('Failed to update the database, (%s).', 'pages'));
		}

		echo 'true';
		$this->render(false);
	}

/**
 * ブロック移動 - 行移動
 * ブロック操作 - 移動
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function insert_row() {
		$user_id = $this->Auth->user('id');
		$block = $this->nc_block;
		$page = (!empty($this->request->params['requested'])) ? $this->nc_current_page : $this->nc_page;
		$page_id = $page['Page']['id'];
		$show_count = $this->request->data['show_count'];
		$pre_page = $page;

		if(!empty($this->request->params['requested'])) {
			$pre_page = $this->Page->findAuthById(intval($block['Block']['page_id']), $user_id);
			if(!$pre_page || $pre_page['PageAuthority']['hierarchy'] < NC_AUTH_MIN_CHIEF) {
				$this->response->statusCode('403');
				$this->flash(__('Authority Error!  You do not have the privilege to access this page.'), '');
				return;
			}
			$content = array('Content' => $block['Content']);
			$ret_validator = $this->BlockMove->validatorRequestContent($content, $pre_page, $page);
			if($ret_validator !== true) {
				throw new BadRequestException($ret_validator);
			}
		}

		if(!$this->BlockMove->validatorRequest($this->request)) {
			throw new BadRequestException(__('Unauthorized request.<br />Please reload the page.'));
		}

		if(!$page || $page['Page']['show_count'] != $show_count) {
			$this->response->statusCode('400');
			$this->flash(__d('block', 'Because of the possibility of inconsistency happening, update will not be executed. <br /> Please redraw and update again.'), '');
			return;
		}

		$ret = $this->BlockMove->InsertRow($block, $this->request->data['parent_id'], $this->request->data['col_num'], $this->request->data['row_num'], $pre_page, $page);
		if(!$ret) {
			throw new InternalErrorException(__('Failed to update the database, (%s).', 'blocks'));
		}

		// 表示カウント++
		$this->Page->id = $page_id;
		if(!$this->Page->saveField('show_count', intval($show_count) + 1)) {
			throw new InternalErrorException(__('Failed to update the database, (%s).', 'pages'));
		}

		if($pre_page['Page']['id'] != $page['Page']['id']) {
			// 移動元表示カウント++(ブロック移動時)
			$this->Page->id = $pre_page['Page']['id'];
			if(!$this->Page->saveField('show_count', intval($pre_page['Page']['show_count']) + 1)) {
				throw new InternalErrorException(__('Failed to update the database, (%s).', 'pages'));
			}
		}
		if(!empty($this->request->params['requested'])) {
			if($pre_page['Page']['room_id'] != $page['Page']['room_id']) {
				$module = array('Module' => $this->nc_block['Module']);
				$ins_block = $this->Block->findById(intval($block['Block']['id']));
				$ins_content = $this->Content->findById(intval($block['Block']['content_id']));
				// ルームが異なればブロック移動アクションを呼ぶ
				/** args
				 * @param   Model Block   移動元ブロック
				 * @param   Model Block   移動先ブロック
				 * @param   Model Content 移動元コンテンツ
				 * @param   Model Content 移動先コンテンツ
				 * @param   Model Page    移動元ページ
				 * @param   Model Page    移動先ページ
				 */
				$args = array(
					array('Block' => $block['Block']),
					$ins_block,
					$content,
					$ins_content,
					$pre_page,
					$page
				);

				if(!$this->Module->operationAction($module['Module']['dir_name'], 'move', $args)) {
					throw new InternalErrorException(__('Failed to execute the %s.', __('Move')));
				}
			}
			$this->autoRender = false;
			return 'true';
		} else {
			echo 'true';
			$this->render(false);
		}
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

		if(!empty($this->request->params['requested']) && isset($this->request->data['page_id'])) {
			$insert_page = $this->Page->findAuthById(intval($this->request->data['page_id']), $user_id);
			if(!$insert_page || $insert_page['PageAuthority']['hierarchy'] < NC_AUTH_MIN_CHIEF) {
				$this->response->statusCode('403');
				$this->flash(__('Authority Error!  You do not have the privilege to access this page.'), '');
				return;
			}
		}

		if(!$this->BlockMove->validatorRequest($this->request)) {
			// Error
			throw new BadRequestException(__('Unauthorized request.<br />Please reload the page.'));
		}

		if(!$page || $page['Page']['show_count'] != $show_count) {
			$this->response->statusCode('400');
			$this->flash(__d('block', 'Because of the possibility of inconsistency happening, update will not be executed. <br /> Please redraw and update again.'), '');
			return;
		}

		// TODO: ShowCountのチェック(insert_page_id)

		$ret = $this->BlockMove->InsertCell($block,  $this->request->data['parent_id'], $this->request->data['col_num'], $this->request->data['row_num'], $page, $insert_page);
		if(!$ret) {
			throw new InternalErrorException(__('Failed to update the database, (%s).', 'blocks'));
		}

		// 表示カウント++
		$this->Page->id = $page_id;
		if(!$this->Page->saveField('show_count', intval($show_count) + 1)) {
			throw new InternalErrorException(__('Failed to update the database, (%s).', 'pages'));
		}

		echo 'true';
		$this->render(false);
	}

/**
 * ブロックグループ化
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function add_group() {

		if(!is_array($this->request->data['groups']) || count($this->request->data['groups']) == 0) {
			throw new BadRequestException(__('Unauthorized request.<br />Please reload the page.'));
		}

		$block = $this->nc_block;
		$page = $this->nc_page;
		$page_id = $page['Page']['id'];
		$show_count = $this->request->data['show_count'];

		if(!$page || $page['Page']['show_count'] != $show_count) {
			$this->response->statusCode('400');
			$this->flash(__d('block', 'Because of the possibility of inconsistency happening, update will not be executed. <br /> Please redraw and update again.'), '');
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
				$this->response->statusCode('400');
				$this->flash(__d('block', 'More than this, can not be grouped complex.'), '');
				return;
			}
			if($group_block['Block']['page_id'] != $page_id
				|| (!empty($pre_block)
					&& ($group_block['Block']['page_id'] != $pre_block['Block']['page_id']
						|| $group_block['Block']['parent_id'] != $pre_block['Block']['parent_id']
						|| in_array($block_id, $upd_block_id_arr)))
				) {
				// グループ化する基点とpage_id,parent_id相違
				throw new BadRequestException(__('Unauthorized request.<br />Please reload the page.'));
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
					'shortcut_type' => NC_SHORTCUT_TYPE_OFF,
					'title' => __d('block', 'New group'),
					'room_id' => $page['Page']['room_id'],
					'display_flag' => NC_DISPLAY_FLAG_ON,
					'is_approved' => NC_DISPLAY_FLAG_ON,
					'url' => ''
				);
				$this->Content->create();
				$ins_ret = $this->Content->save($ins_content);
				if(!$ins_ret) {
					throw new InternalErrorException(__('Failed to register the database, (%s).', 'contents'));
				}
				$last_content_id = $this->Content->id;
				if(!$this->Content->saveField('master_id', $last_content_id)) {
					throw new InternalErrorException(__('Failed to update the database, (%s).', 'contents'));
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
					throw new InternalErrorException(__('Failed to register the database, (%s).', 'blocks'));
				}
				$last_id = $this->Block->id;
				$ins_ret['Block']['id'] = $last_id;
				//$ins_ret['Block']['parent_id'] = $last_id;
				if($ins_ret['Block']['root_id'] == $block_id) {
					$ins_ret['Block']['root_id'] = $last_id;
				}

				$ins_ret = $this->Block->save($ins_ret);
				if(!$ins_ret) {
					throw new InternalErrorException(__('Failed to update the database, (%s).', 'blocks'));
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
					throw new InternalErrorException(__('Failed to update the database, (%s).', 'blocks'));
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
						throw new InternalErrorException(__('Failed to update the database, (%s).', 'blocks'));
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
				throw new InternalErrorException(__('Failed to update the database, (%s).', 'blocks'));
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
						throw new InternalErrorException(__('Failed to update the database, (%s).', 'blocks'));
					}
				}
			}
		}

		// 表示カウント++
		$this->Page->id = $page_id;
		if(!$this->Page->saveField('show_count', intval($show_count) + 1)) {
			throw new InternalErrorException(__('Failed to update the database, (%s).', 'pages'));
		}

		$params = array('block_id' => $last_id, 'plugin' => 'group', 'controller' => 'group', 'action' => 'index');
		echo ($this->requestAction($params, array('return')));
		$this->render(false, 'ajax');
	}
/**
 * ブロックグループ化解除
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function cancel_group() {
		if(!is_array($this->request->data['cancel_groups']) || count($this->request->data['cancel_groups']) == 0) {
			throw new BadRequestException(__('Unauthorized request.<br />Please reload the page.'));
		}

		$block = $this->nc_block;
		$page = $this->nc_page;
		$page_id = $page['Page']['id'];
		$show_count = $this->request->data['show_count'];

		if(!$page || $page['Page']['show_count'] != $show_count) {
			$this->response->statusCode('400');
			$this->flash(__d('block', 'Because of the possibility of inconsistency happening, update will not be executed. <br /> Please redraw and update again.'), '');
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

			if($group_block['Block']['controller_action'] != 'group'
				|| $group_block['Block']['page_id'] != $page_id
				|| (!empty($pre_block)
						&& ($group_block['Block']['page_id'] != $pre_block['Block']['page_id']
							|| $group_block['Block']['parent_id'] != $pre_block['Block']['parent_id']
							|| in_array($block_id, $upd_block_id_arr)))
				) {
				// グループ化する基点とpage_id,parent_id相違
				throw new BadRequestException(__('Unauthorized request.<br />Please reload the page.'));
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
					throw new InternalErrorException(__('Failed to update the database, (%s).', 'blocks'));
				}
			}
			if($col_count != 0) {
				$buf_group_block = $group_block;
				$buf_group_block['Block']['col_num']++;
				$inc_ret = $this->BlockOperation->incrementColNum($buf_group_block, $col_count);
				if(!$inc_ret) {
					throw new InternalErrorException(__('Failed to update the database, (%s).', 'blocks'));
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
					throw new InternalErrorException(__('Failed to update the database, (%s).', 'blocks'));
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
							throw new InternalErrorException(__('Failed to update the database, (%s).', 'blocks'));
						}
					}
				}
			}
		}

		// 表示カウント++
		$this->Page->id = $page_id;
		if(!$this->Page->saveField('show_count', intval($show_count) + 1)) {
			throw new InternalErrorException(__('Failed to update the database, (%s).', 'pages'));
		}

		echo 'true';
		$this->render(false);
	}
}