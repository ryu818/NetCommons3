<?php
/**
 * GroupControllerクラス
 *
 * <pre>
 * グループ化ブロック
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       App.Plugin.Controller
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class GroupController extends GroupAppController {

	public function index() {
		$user_id = $this->Auth->user('id');
		$block = $this->nc_block;

		$this->set('parent_id', intval($block['Block']['id']));

		if(count($this->nc_blocks) == 0) {
			$buf_blocks = $this->Block->findByGroupId($block, $user_id);
			if(isset($buf_blocks[$block['Block']['page_id']])) {
				$blocks = $buf_blocks[$block['Block']['page_id']];
			}
		} else {
			$blocks = $this->nc_blocks;
		}

		if($block['Block']['controller_action'] != 'group') {
			// Error
			// TODO:test
			//throw new BadRequestException(__('Unauthorized request.<br />Please reload the page.'));
		}
		$this->set('blocks', $blocks);
	}
}