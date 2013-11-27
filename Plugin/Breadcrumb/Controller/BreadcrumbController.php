<?php
/**
 * BreadcrumbControllerクラス
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       App.Controller
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
App::uses('AppPluginController', 'Controller');
class BreadcrumbController extends AppPluginController {
/**
 * ぱんくずリスト表示
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function index() {
		$centerPage = Configure::read(NC_SYSTEM_KEY.'.'.'center_page');
		$pageId = isset($centerPage['Page']['id']) ? intval($centerPage['Page']['id']) : null;
		$page = $this->Page->findAuthById($pageId);
		$pagesList = $this->Page->findBreadcrumb($page);

		$this->set('pages_list', $pagesList);
	}
}