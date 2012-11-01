<?php
class GroupController extends GroupAppController {

	public $nc_block = array();
	public $nc_blocks = array();

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
			//$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'group.001', '400');
			//return;
		}
		$this->set('blocks', $blocks);
	}
}