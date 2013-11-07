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
 * @param  string        $action paste or shortcut or move
 * @param  Model Page    $prePage
 * @param  Model Module  $module
 * @param  Model Block   $block(ペースト、ショートカット、移動作成用)
 * @param  Model Content $content(ペースト、ショートカット、移動作成用)
 * @param  boolean       $isShortcut(ショートカットならば_OFF, 権限が付与されたショートカットならば_ON)
 * @param  Model Page    $page(ペースト、ショートカット、移動、作成用):追加先ページ
 * @param  integer       $newRootId ページのペースト、ショートカット作成、移動時 root_id
 * @param  integer       $newParentId ページのペースト、ショートカット作成、移動時 parent_id
 * @return false or array(boolean, Model Block   $ins_block, Model Content   $insContent)
 * @since  v 3.0.0.0
 */
	public function addBlock($action, $prePage, $module, $block = null, $content = null, $isShortcut = null, $page = null, $newRootId = null, $newParentId = null) {
		// TODO: そのmoduleが該当ルームに貼れるかどうかのチェックが必要。
		// グループ化ブロック（ショートカット）ならば、該当グループ内のmoduleのチェックが必要。
		// はりつけたあと、表示されませんで終わらす方法も？？？ -> グループ化ブロックはペースト不可

		$Content = ClassRegistry::init('Content');

		if(!isset($page)) {
			$page = $prePage;
		}

		if(isset($isShortcut) && $isShortcut == _ON && $page['Page']['room_id'] == $content['Content']['room_id']) {
			// コンテンツのルームが同じならば、権限が付与されていないショートカットへ
			$isShortcut = _OFF;
		}

		$Content->create();
		$lastContentId = 0;
		if(($action == 'move' || $action == 'shortcut') && empty($content['Content']['id'])) {
			// コンテンツなし
		} else if($action == 'paste' || $action == 'shortcut' || $action == 'move') {
			// ブロック操作
			$masterContent = $content;
			if($content['Content']['shortcut_type'] != NC_SHORTCUT_TYPE_OFF) {
				$masterContent = $Content->findById($content['Content']['master_id']);
			}
			if($action == 'shortcut' && $block['Block']['module_id'] == 0) {
				// グループブロックはペースト、移動のみ
				$action = 'paste';
				$isShortcut = null;
			}
			/** ペースト、ショートカットのペースト,ショートカットの作成
			 * 	・同ルーム内のショートカット
			 * 		Block.content_id 新規に取得しないで、ショートカット元のcontent_idを付与
			 * 		Contentは追加しない。
			 *  ・ペースト、別ルームへのショートカットの作成
			 * 		Contentは新規追加するが、ショートカット元のContentの中身(title,shortcut_type, master_id,display_flag,is_approved,url)はコピー
			 * 			room_idはショートカット先のroom_id
			 * 		・shortcut_type NC_SHORTCUT_TYPE_SHOW_ONLY 閲覧は許可する。
			 * 		・shortcut_type NC_SHORTCUT_TYPE_SHOW_AUTH 表示中のルーム権限より閲覧・編集権限を付与する。
			 *
			 */
			if($content['Content']['shortcut_type'] != NC_SHORTCUT_TYPE_OFF &&
					$page['Page']['room_id'] == $masterContent['Content']['room_id']) {
				// ショートカットを元のルームに戻した。
				$insContent = $masterContent;
				$lastContentId = $masterContent['Content']['id'];
				if($action == 'move') {
					// 前コンテンツ削除処理
					$result = $Content->delete($content['Content']['id']);
					// 同ページ内の他ブロックにより、既に削除されている可能性があるためエラーチェックしない。
					// if(!$result) {
					//	return false;
					//}
					$masterContentId = $lastContentId;
				}
				if($action == 'paste') {
					$action = 'shortcut';
				}
			} else if($action == 'shortcut' && $page['Page']['room_id'] == $masterContent['Content']['room_id']) {
				// 同じルーム内のショートカットを作成
				$insContent = $content;
				$lastContentId = $content['Content']['id'];
			/*} else if($block['Block']['module_id'] != 0 && $isShortcut === _OFF) {
				// 権限が付与されていないショートカットのペースト、ショートカット作成
				$shortcutFlag = NC_SHORTCUT_TYPE_SHOW_ONLY;
				$insContent = $content;
				$lastContentId = $content['Content']['id'];
				if($action == 'paste') {
					$action = 'shortcut';
				}*/
			} else {
				if($isShortcut === _ON) {
					$shortcutFlag = NC_SHORTCUT_TYPE_SHOW_AUTH;
				} else if($isShortcut === _OFF) {
					$shortcutFlag = NC_SHORTCUT_TYPE_SHOW_ONLY;
				} else {
					$shortcutFlag = $content['Content']['shortcut_type'];
				}
				$insContent = array(
					'Content' => array(
						'module_id' => $content['Content']['module_id'],
						'title' => $content['Content']['title'],
						'shortcut_type' => $shortcutFlag,
						'room_id' => ($shortcutFlag == NC_SHORTCUT_TYPE_OFF || $shortcutFlag == NC_SHORTCUT_TYPE_SHOW_AUTH) ? $page['Page']['room_id'] : $content['Content']['room_id'],
						'display_flag' => $content['Content']['display_flag'],
						'is_approved' => $content['Content']['is_approved'],
						'url' => $content['Content']['url']
					)
				);
				if($block['Block']['module_id'] != 0 && $content['Content']['shortcut_type'] != NC_SHORTCUT_TYPE_OFF) {
					// ショートカットのペーストか、ショートカットのショートカット
					$insContent['Content']['master_id'] = $content['Content']['master_id'];
					if($action == 'paste') {
						$action = 'shortcut';
					}
				} else if($block['Block']['module_id'] != 0 && $shortcutFlag != NC_SHORTCUT_TYPE_OFF) {
					// ショートカットの作成
					$insContent['Content']['master_id'] = $content['Content']['id'];
					if($action == 'paste') {
						$action = 'shortcut';
					}
				}
				if($action == 'move') {
					// もし、移動元ページに該当コンテンツがはってあれば、権限つきショートカットとして移動
					// はってなければ、コンテンツ毎移動。
					$otherBlock = $this->find('first', array(
						'fields' => array($this->alias.'.id'),
						'conditions' => array(
							$this->alias.'.content_id' => $content['Content']['id'],
							$this->alias.'.page_id !=' => $prePage['Page']['id']
						),
						'recursive' => -1,
					));
					if(!isset($otherBlock[$this->alias])) {
						$insContent['Content']['id'] = $content['Content']['id'];
					} else {
						$insContent['Content']['master_id'] = $content['Content']['id'];
						$insContent['Content']['shortcut_type'] = NC_SHORTCUT_TYPE_SHOW_AUTH;
					}
				}
				$Content->create();
				$ins_ret = $Content->save($insContent);
				if(!$ins_ret) {
					return false;
				}
				$lastContentId = $Content->id;
				if(isset($otherBlock[$this->alias])) {
					$masterContentId = $lastContentId;
				}
			}
		} else {
			// ブロック追加時
			$insContent = array(
				'Content' => array(
					'module_id' => $module['Module']['id'],
					'title' => $module['Module']['module_name'],
					'shortcut_type' => NC_SHORTCUT_TYPE_OFF,
					'room_id' => $page['Page']['room_id'],
					'display_flag' => (isset($module['Module']['ini']['add_block_disable']) && $module['Module']['ini']['add_block_disable'] == _ON)
						? NC_DISPLAY_FLAG_DISABLE : NC_DISPLAY_FLAG_ON,
					'is_approved' => _ON,
					'url' => ''
				)
			);
			$Content->set($insContent);
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
			$insContent['Content']['title'] = $content_title;
			$ins_ret = $Content->save($insContent);
			if(!$ins_ret) {
				return false;
			}
			$lastContentId = $Content->id;
		}
		$insContent['Content']['id'] = $lastContentId;

		if($lastContentId > 0 && $insContent['Content']['shortcut_type'] == NC_SHORTCUT_TYPE_OFF && !isset($insContent['Content']['master_id'])) {
			if(!$Content->saveField('master_id', $lastContentId)) {
				return false;
			}
		}

		if($action == 'move') {
			$ins_block = $block;
			if(isset($masterContentId)) {
				$fields = array(
					'content_id' => $masterContentId
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
				'content_id' => $lastContentId,
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

			if(isset($newParentId)) {
				// ページのペースト、ショートカット作成
				$ins_block['Block'] = array_merge($ins_block['Block'], array(
					'controller_action' => $block['Block']['controller_action'],
					'root_id' => isset($newRootId) ? $newRootId : 0,
					'parent_id' => $newParentId,
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
			$Module = ClassRegistry::init('Module');

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
					$insContent,
					$prePage,
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
					$insContent,
					$page
				);
			}
			if($action != 'move' || $prePage['Page']['room_id'] != $page['Page']['room_id']) {
				// 移動は異なるルームへの移動のみmoveアクションを呼ぶ
				// エラー時でも処理を続けるため、$ins_block、$insContentを返す。
				if(!$Module->operationAction($module['Module']['dir_name'], $action, $args)) {
					return array(false, $ins_block, $insContent);
				}
			}
		}

		return array(true, $ins_block, $insContent);
	}
}