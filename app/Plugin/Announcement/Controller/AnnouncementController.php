<?php
class AnnouncementController extends AnnouncementAppController {
	public function index() {
		// TODO: $this->request->params['content']['master_id']として取得するほうがわかりやすい。
		$master_id = $this->request->params['block']['Content']['master_id'];
		$ret = $this->Htmlarea->findByMasterId($master_id);
		if(!isset($ret['Htmlarea'])) {
			if($this->hierarchy >= NC_AUTH_MIN_CHIEF) {
				// 指定したコンテンツは、存在しません。
				$this->set('content', __('Content not found.'));
			}
			return;
		}
		$this->set('content', $ret['Htmlarea']['content']);
	}

	public function edit() {
		$this->index();
	}
}