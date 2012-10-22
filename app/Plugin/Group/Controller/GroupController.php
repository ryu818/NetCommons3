<?php
class GroupController extends GroupAppController {
	public function index() {
		$user_id = $this->Auth->user('id');
		$block = $this->request->params['block'];

		$this->set('parent_id', intval($this->request->params['block']['Block']['id']));

		if(!isset($this->request->params['blocks'])) {
			$buf_blocks = $this->Block->findByGroupId($this->request->params['block'], $user_id);
			if(isset($buf_blocks[$this->request->params['block']['Block']['page_id']])) {
				$blocks = $buf_blocks[$this->request->params['block']['Block']['page_id']];
			}
		} else {
			$blocks = $this->request->params['blocks'];
		}

		if($block['Block']['controller_action'] != 'group') {
			// Error
			//$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'group.001', '400');
			//return;
		}

		$this->set('blocks', $blocks);
	}
}