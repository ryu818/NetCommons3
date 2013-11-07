<?php
/**
 * ContentAppControllerクラス
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       App.Controller
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class ContentAppController extends AppController {
/**
 * beforeRender
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function beforeRender() {
		$this->set('id', $this->id);
		$this->set('block_id', $this->block_id);
		$this->set('module_id', $this->module_id);
	}
}