<?php
/**
 * BlockOperationモデル
 *
 * <pre>
 *  ブロック移動、ペースト、ショートカット作成操作用モデル
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Plugin.Block.Model
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class BlockOperation extends AppModel {
	public $name = 'BlockOperation';
    public $useTable = 'blocks';
    public $alias = 'Block';

	public function findRowCount($page_id, $parent_id, $col_num)
	{
		$count_row_num = $this->find('count', array(
			'fields' => 'COUNT(*) as count',
			'recursive' => -1,
			'conditions' => array(
				'Block.page_id' => $page_id,
				'Block.parent_id' => $parent_id,
				'Block.col_num' => $col_num
			)
		));
		return $count_row_num;
	}

/**
 * 行前詰め処理
 *
 * @return boolean true or false
 * @since   v 3.0.0.0
 */
	public function decrementRowNum($block = null,$row_num = 1) {
	 	$row_num = -1*$row_num;
	 	return $this->_operationRowNum($block, $row_num);
	}
	public function incrementRowNum($block = null,$row_num = 1) {
	 	return $this->_operationRowNum($block, $row_num);
	}
	protected function _operationRowNum($block = null,$row_num = 1) {
		$fields = array('Block.row_num'=>'Block.row_num+('.$row_num.')');
		$conditions = array(
			"Block.page_id" => $block['Block']['page_id'],
			"Block.id !=" => $block['Block']['id'],
			"Block.parent_id" => $block['Block']['parent_id'],
			"Block.col_num" => $block['Block']['col_num'],
			"Block.row_num >" => $block['Block']['row_num']
		);
		$ret = $this->updateAll($fields, $conditions);
		return $ret;
	}
/**
 * 列前詰め処理
 *
 * @return boolean true or false
 * @since   v 3.0.0.0
 */
	public function decrementColNum($block = null,$col_num = 1) {
	 	$col_num = -1*$col_num;
	 	return $this->_operationColNum($block, $col_num);
	}
	public function incrementColNum($block = null,$col_num = 1) {
	 	return $this->_operationColNum($block, $col_num);
	}
	protected function _operationColNum($block = null,$col_num = 1) {
		$fields = array('Block.col_num'=>'Block.col_num+('.$col_num.')');
		$conditions = array(
			"Block.page_id" => $block['Block']['page_id'],
			"Block.id !=" => $block['Block']['id'],
			"Block.parent_id" => $block['Block']['parent_id'],
			"Block.col_num >=" => $block['Block']['col_num']
		);
		$ret = $this->updateAll($fields, $conditions);
		return $ret;
	}

	public function defaultBlock($ins_block) {
		// TODO:固定 test
		unset($ins_block['Block']['id']);
		$ins_block['Block']['title'] = '{X-CONTENT}';
		$ins_block['Block']['module_id'] = 0;
		$ins_block['Block']['content_id'] = 0;
		$ins_block['Block']['show_title'] = _ON;
		$ins_block['Block']['display_flag'] = NC_DISPLAY_FLAG_ON;
		$ins_block['Block']['display_from_date'] = null;
		$ins_block['Block']['display_to_date'] = null;
		$ins_block['Block']['controller_action'] = 'group';
		$ins_block['Block']['theme_name'] = 'NoneFrame';
		$ins_block['Block']['temp_name'] = '';
		$ins_block['Block']['left_margin'] = 8;
		$ins_block['Block']['right_margin'] = 8;
		$ins_block['Block']['top_margin'] = 8;
		$ins_block['Block']['bottom_margin'] = 8;
		$ins_block['Block']['min_width_size'] = 0;
		$ins_block['Block']['min_height_size'] = 0;
		$ins_block['Block']['lock_authority_id'] = 0;
		return $ins_block;
	}

/**
 * ブロック追加,移動,ショートカット作成処理
 * @param  string        $action paste or shortcut
 * @param  Model Page    $pre_page
 * @param  Model Module  $module
 * @param  Model Block   $block(ペースト、ショートカット、移動作成用)
 * @param  Model Content $content(ペースト、ショートカット、移動作成用)
 * @param  boolean       $shortcut_flag(ショートカットならば_OFF, 権限が付与されたショートカットならば_ON)
 * @param  Model Page    $page(ペースト、ショートカット、移動、作成用):追加先ページ
 * @param  integer       $new_root_id ページのペースト、ショートカット作成、移動時 root_id
 * @param  integer       $new_parent_id ページのペースト、ショートカット作成、移動時 parent_id
 * @return false or array(, Model Block   $ins_block, Model Content   $ins_content)
 * @since  v 3.0.0.0
 */
	public function addBlock($action, $pre_page, $module, $block = null, $content = null, $shortcut_flag = null, $page = null, $new_root_id = null, $new_parent_id = null) {
		// TODO: そのmoduleが該当ルームに貼れるかどうかのチェックが必要。
		// グループ化ブロック（ショートカット）ならば、該当グループ内のmoduleのチェックが必要。
		// はりつけたあと、表示されませんで終わらす方法も？？？ -> グループ化ブロックはペースト不可

		App::uses('Content', 'Model');
		$Content = new Content();

		if(!isset($page)) {
			$page = $pre_page;
		}

		if(isset($shortcut_flag) && $shortcut_flag == _ON && $page['Page']['room_id'] == $content['Content']['room_id']) {
			// コンテンツのルームが同じならば、権限が付与されていないショートカットへ
			$shortcut_flag = _OFF;
		}

		$Content->create();
		if($action == 'paste' || $action == 'shortcut' || $action == 'move') {
			// ブロック操作
			$master_content = $content;
			if(!$content['Content']['is_master']) {
				$master_content = $Content->findById($content['Content']['master_id']);
			}
			if($action == 'shortcut' && $block['Block']['module_id'] == 0) {
				// グループブロックはペースト、移動のみ
				$action = 'paste';
				$shortcut_flag = null;
			}
			/** ペースト、ショートカットのペースト,ショートカットの作成
			 *  ・権限が付与されていないショートカットのペースト、ショートカット作成
			 * 		Block.content_id 新規に取得しないで、ショートカット元のcontent_idを付与
			 * 		Contentは追加しない。
			 *  ・ペースト、ショートカットの作成（表示中のルーム権限より閲覧・編集権限を付与する。）
			 * 		Contentは新規追加するが、ショートカット元のContentの中身(title,is_master, master_id,display_flag,is_approved,url)はコピー
			 * 			room_idはショートカット先のroom_id
			 */
			if(!$content['Content']['is_master'] &&
					$page['Page']['room_id'] == $master_content['Content']['room_id']) {
				// 権限が付与されているショートカットを元のルームに戻した。
				$ins_content = $master_content;
				$last_content_id = $master_content['Content']['id'];
				if($action == 'move') {
					// 前コンテンツ削除処理
					$result = $Content->delete($content['Content']['id']);
					// 同ページ内の他ブロックにより、既に削除されている可能性があるためエラーチェックしない。
					// if(!$result) {
					//	return false;
					//}
					$master_content_id = $last_content_id;
				}
				if($action == 'paste') {
					$action = 'shortcut';
				}
			} else if($block['Block']['module_id'] != 0 && ((!isset($shortcut_flag) && $pre_page['Page']['room_id'] != $content['Content']['room_id']) ||
				$shortcut_flag === _OFF)) {
				// 権限が付与されていないショートカットのペースト、ショートカット作成
				$ins_content = $content;
				$last_content_id = $content['Content']['id'];
				if($action == 'paste') {
					$action = 'shortcut';
				}
			} else {
				$ins_content = array(
					'Content' => array(
						'module_id' => $content['Content']['module_id'],
						'title' => $content['Content']['title'],
						'is_master' => ($shortcut_flag === _ON) ? _OFF : $content['Content']['is_master'],
						'room_id' => $page['Page']['room_id'],
						'display_flag' => $content['Content']['display_flag'],
						'is_approved' => $content['Content']['is_approved'],
						'url' => $content['Content']['url']
					)
				);
				if($block['Block']['module_id'] != 0 && !$content['Content']['is_master']) {
					// 権限が付与されたショートカットのペーストか、権限が付与されたショートカットの権限が付与されたショートカット
					$ins_content['Content']['master_id'] = $content['Content']['master_id'];
					if($action == 'paste') {
						$action = 'shortcut';
					}
				} else if($block['Block']['module_id'] != 0 && $shortcut_flag === _ON) {
					// 権限が付与されたショートカットの作成
					$ins_content['Content']['master_id'] = $content['Content']['id'];
					if($action == 'paste') {
						$action = 'shortcut';
					}
				}
				if($action == 'move') {
					$ins_content['Content']['id'] = $content['Content']['id'];
				}

				$ins_ret = $Content->save($ins_content);
				if(!$ins_ret) {
					return false;
				}
				$last_content_id = $Content->id;
			}
		} else {
			// ブロック追加時
			$ins_content = array(
				'Content' => array(
					'module_id' => $module['Module']['id'],
					'title' => $module['Module']['module_name'],
					'is_master' => _ON,
					'room_id' => $page['Page']['room_id'],
					'display_flag' => (isset($module['Module']['ini']['add_block_disable']) && $module['Module']['ini']['add_block_disable'] == _ON)
						? NC_DISPLAY_FLAG_DISABLE : NC_DISPLAY_FLAG_ON,
					'is_approved' => _ON,
					'url' => ''
				)
			);
			$Content->set($ins_content);
			$content_title = $buf_content_title = $module['Module']['module_name'];
			$count = 0;
			while(1) {
				if(!$Content->isUniqueWith(array(), array('title' => $content_title, 'room_id'))) {
					$count++;
					$content_title = $buf_content_title. '-' . $count;
				} else {
					break;
				}
			}
			$ins_content['Content']['title'] = $content_title;
			$ins_ret = $Content->save($ins_content);
			if(!$ins_ret) {
				return false;
			}
			$last_content_id = $Content->id;
		}
		$ins_content['Content']['id'] = $last_content_id;

		if($ins_content['Content']['is_master'] && !isset($ins_content['Content']['master_id'])) {
			if(!$Content->saveField('master_id', $last_content_id)) {
				return false;
			}
		}

		if($action == 'move') {
			$ins_block = $block;
			if(isset($master_content_id)) {
				$fields = array(
					'content_id' => $master_content_id
				);
				$conditions = array(
					'id' => $block['Block']['id']
				);
				$result = $this->updateAll($fields, $conditions);
				if(!$result) {
					return false;
				}
			}
		} else {
			$ins_block = array();
			$ins_block = $this->defaultBlock($ins_block);
			$ins_block['Block'] = array_merge($ins_block['Block'], array(
				'page_id' => $page['Page']['id'],
				'module_id' => isset($module['Module']['id']) ? $module['Module']['id'] : 0,
				'content_id' => $last_content_id,
				'controller_action' =>  isset($module['Module']['controller_action']) ? $module['Module']['controller_action'] : '',
				'theme_name' => '',
				'root_id' => 0,
				'parent_id' => 0,
				'thread_num' => 0,
				'col_num' => 1,
				'row_num' => 1
			));
			if($action == 'paste' || $action == 'shortcut') {
				/** ペースト OR ショートカット作成 OR 移動
				 * 	移動元のBlockの中身(title, show_title, display_flag, display_from_date,display_to_date, theme_name, temp_name, left_margin,
				 * 		right_margin, top_margin,bottom_margin,min_width_size,min_height_size, lock_authority_id)はコピー
				 */
				$ins_block['Block'] = array_merge($ins_block['Block'], array(
					'title' => $block['Block']['title'],
					'show_title' => $block['Block']['show_title'],
					'display_flag' => $block['Block']['display_flag'],
					'display_from_date' => $block['Block']['display_from_date'],
					'display_to_date' => $block['Block']['display_to_date'],
					'theme_name' => $block['Block']['theme_name'],
					'temp_name' => $block['Block']['temp_name'],
					'left_margin' => $block['Block']['left_margin'],
					'right_margin' => $block['Block']['right_margin'],
					'top_margin' => $block['Block']['top_margin'],
					'bottom_margin' => $block['Block']['bottom_margin'],
					'min_width_size' => $block['Block']['min_width_size'],
					'min_height_size' => $block['Block']['min_height_size'],
					'lock_authority_id' => $block['Block']['lock_authority_id'],
				));
			}

			if(isset($new_parent_id)) {
				// ページのペースト、ショートカット作成
				$ins_block['Block'] = array_merge($ins_block['Block'], array(
					'controller_action' => $block['Block']['controller_action'],
					'root_id' => isset($new_root_id) ? $new_root_id : 0,
					'parent_id' => $new_parent_id,
					'thread_num' => $block['Block']['thread_num'],
					'col_num' => $block['Block']['col_num'],
					'row_num' => $block['Block']['row_num']
				));
			}
			$this->create();
			$ins_ret = $this->save($ins_block);
			if(!$ins_ret) {
				return false;
			}
			$last_id = $this->id;

			if($ins_block['Block']['thread_num'] == 0) {
				//root_idを再セット
				if(!$this->saveField('root_id', $last_id)) {
					return false;
				}
				$ins_block['Block']['root_id'] = $last_id;
			}
			$ins_block['Block']['id'] = $last_id;
		}


		if($block['Block']['module_id'] != 0) {
			App::uses('Module', 'Model');
			$Module = new Module();

			if($action != 'add_block') {
				// ブロック移動(表示順変更)、ペースト、ショートカット作成
				/** args
				 * @param   Model Block   移動元ブロック
				 * @param   Model Block   移動先ブロック
				 * @param   Model Content 移動元コンテンツ
				 * @param   Model Content 移動先コンテンツ
				 * @param   Model Page    移動元ページ
				 * @param   Model Page    移動先ページ
				 */
				$args = array(
					$block,
					$ins_block,
					$content,
					$ins_content,
					$pre_page,
					$page
				);
			} else {
				// ブロック追加
				/** args
				 * @param   Model Block   追加ブロック
				 * @param   Model Content 追加コンテンツ
				 * @param   Model Page    追加先ページ
				 */
				$args = array(
					$ins_block,
					$ins_content,
					$page
				);
			}
			if($action != 'move' || $pre_page['Page']['room_id'] != $page['Page']['room_id']) {
				// 移動は異なるルームへの移動のみmoveアクションを呼ぶ
				// エラー時でも処理を続けるため、$ins_block、$ins_contentを返す。
				if(!$Module->operationAction($module['Module']['dir_name'], $action, $args)) {
					return array(false, $ins_block, $ins_content);
				}
			}
		}

		return array(true, $ins_block, $ins_content);
	}
}