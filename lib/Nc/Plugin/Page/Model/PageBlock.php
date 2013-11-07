<?php
/**
 * PageBlockモデル
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Plugin.Block.Model
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class PageBlock extends AppModel {
	public $useTable = 'blocks';
	public $alias = 'Block';

/**
 * 権限の割り当てで、子ルームを割り当てると、そこにはってあったブロックの変更処理
 * 		子ルーム以外にははっていなければ、子ルームの持ち物に変更
 * 		子ルーム以外のどこかにはってあれば(ショートカットとしてはってあるもの以外)、ショートカットに変更（権限も付与した状態）
 * 			 - 解除も同様に親ルームの持ち物に戻す場合は、ショートカット解除を行う必要あり。
 *
 * @param array   $page_id_arr：権限を付与したページIDリスト
 * @param integer $parent_room_id:親ルーム
 *
 * @return  boolean
 * @since  v 3.0.0.0
 */
	public function addAuthBlock($page_id_arr, $parent_room_id) {
		$Content = ClassRegistry::init('Content');

		$addauth_room_id = $page_id_arr[0];

		$conditions = array(
			'Block.page_id' => $page_id_arr
		);
		$params = array(
			'fields' => array('Block.*', 'Content.*'),
			'conditions' => $conditions,
				'recursive' => -1,
			'joins' => array(
				array(
					'type' => "LEFT",
					'table' => "contents",
					'alias' => "Content",
					'conditions' => "`Block`.`content_id`=`Content`.`id`"
				)
				/*array(
				 'type' => "LEFT",
						'table' => "modules",
						'alias' => "Module",
						'conditions' => "`Block`.`module_id`=`Module`.`id`"
				)*/
			)
		);

		$blocks = $this->find('all', $params);
		if(count($blocks) > 0) {
			foreach($blocks as $block) {
				if($block['Block']['controller_action'] == 'group' || $block['Content']['shortcut_type'] != NC_SHORTCUT_TYPE_OFF) {
					// グループブロックかショートカット
					$Content->id = $block['Block']['content_id'];
					if(!$Content->saveField('room_id', $addauth_room_id)) {
						return false;
					}
					continue;
				} else if($block['Content']['room_id'] != $parent_room_id) {
					// 親ルームのコンテンツではない(どこかのルームのショートカットか、子ルームが既に存在している)
					continue;
				}

				$conditions = array(
					'Block.content_id' => $block['Block']['content_id']
				);
				if(count($page_id_arr) == 1) {
					$conditions['Block.page_id !='] = $page_id_arr[0];
				} else {
					$conditions['Block.page_id NOT'] = $page_id_arr;
				}
				$params = array(
					'fields' => array('Block.*'),
					'conditions' => $conditions,
					'recursive' => -1
				);

				$other_block = $this->find('first', $params);

				if(!isset($other_block['Block'])) {
					// 子ルーム以外にははっていなければ、子ルームの持ち物に変更
					$Content->id = $block['Block']['content_id'];
					if(!$Content->saveField('room_id', $addauth_room_id)) {
						return false;
					}
				} else {
					// 子ルーム以外のどこかにはってあれば(ショートカットとしてはってあるもの以外)、ショートカットに変更（権限も付与した状態）

					$ins_content = array(
						'module_id' => $block['Content']['module_id'],
						'title' => $block['Content']['title'],
						'shortcut_type' => NC_SHORTCUT_TYPE_SHOW_AUTH,
						'master_id' => $block['Content']['id'],
						'room_id' => $addauth_room_id,
						'display_flag' => $block['Content']['display_flag'],
						'is_approved' => $block['Content']['is_approved'],
						'url' => $block['Content']['url']
					);
					if(!$Content->save($ins_content)) {
						return false;
					}

					$this->id = $block['Block']['id'];
					if(!$this->saveField('content_id', $Content->id)) {
						return false;
					}
				}
			}
		}


		// TODO:uploadsテーブルの更新処理

		return true;
	}

/**
 * 権限解除で、
 * 		親ルームのショートカットならば、ショートカットを解除
 * 		それ以外のショートカットではないブロックならば、親ルームの持ち物に戻す
 *
 * @param array   $page_id_arr：権限を付与したページIDリスト
 * @param integer $parent_room_id:親ルーム
 *
 * @return  boolean
 * @since  v 3.0.0.0
 */
	public function deallocationBlock($page_id_arr, $parent_room_id) {
		$Content = ClassRegistry::init('Content');

		$deallocation_room_id = $page_id_arr[0];
		$conditions = array(
			'Block.page_id' => $page_id_arr
		);
		$params = array(
			'fields' => array('Block.*', 'Content.*'),
			'conditions' => $conditions,
			'recursive' => -1,
			'joins' => array(
				array(
					'type' => "LEFT",
					'table' => "contents",
					'alias' => "Content",
					'conditions' => "`Block`.`content_id`=`Content`.`id`"
				)
			)
		);

		$blocks = $this->find('all', $params);
		if(count($blocks) > 0) {
			foreach($blocks as $block) {
				if($block['Block']['controller_action'] == 'group' ||
						($block['Content']['room_id'] != $parent_room_id && $block['Content']['room_id'] != $deallocation_room_id)) {
					// グループブロックか親ルームのコンテンツではない(どこかのルームのショートカットか、子ルームが既に存在している)
					continue;
				}

				// 親ルームのショートカットならば、ショートカットを解除
				if($block['Content']['shortcut_type'] != NC_SHORTCUT_TYPE_OFF) {
					$master_content = $Content->findById($block['Content']['master_id']);
					if(isset($master_content['Content']) && $master_content['Content']['room_id'] == $parent_room_id) {
						// 親ルームのショートカット
						// 同ページ内の他ブロックにより、既に削除されている可能性があるためエラーチェックしない。
						$Content->delete($block['Content']['id']);
						$this->id = $block['Block']['id'];
						if(!$this->saveField('content_id', $block['Content']['master_id'])) {
							return false;
						}
					}
				}
			}
		}

		// それ以外のショートカットではないブロックならば、親ルームの持ち物に戻す
		$fields = array(
			'Content.room_id' => $parent_room_id
		);

		$conditions = array(
			'Content.room_id' => $deallocation_room_id
		);
		if(!$Content->updateAll($fields, $conditions)) {
			return false;
		}

		// TODO:uploadsテーブルの更新処理

		return true;
	}
}