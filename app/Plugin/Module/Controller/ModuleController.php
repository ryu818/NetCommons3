<?php
/**
 * ModuleControllerクラス
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       App.Controller
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
App::uses('AppPluginController', 'Controller');
class ModuleController extends AppPluginController {
/**
 * Model name
 *
 * @var array
 */
	public $uses = array('Module.ModuleAdmin');

/**
 * モジュール管理
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function index() {
		if($this->request->is('post')) {
			// 登録処理
			$dir_name = isset($this->request->data['dir_name']) ? $this->request->data['dir_name'] : '';
			$type = isset($this->request->data['type']) ? $this->request->data['type'] : '';
			switch($type) {
				case 'install':
					$result = $this->ModuleAdmin->installModule($dir_name);
					break;
				case 'update':
					$result = $this->ModuleAdmin->updateModule($dir_name);
					break;
				case 'update-all':
					$result = $this->ModuleAdmin->updateAllModule();
					break;
				case 'uninstall':
					$result = $this->ModuleAdmin->uninstallModule($dir_name);
					break;
				case 'chgsequence':
					break;
				default:
					$result = array(array(), array());
					break;;
			}
			list($success_mes, $error_mes) = $result;
			$this->set('success_mes', $success_mes);
			$this->set('error_mes', $error_mes);
		}
		$general_modules = $this->ModuleAdmin->findList();
		if($general_modules === false) {
			$this->flash(__('Unauthorized request.<br />Please reload the page.', true), null, 'Module.index.001');
			return;
		}
		$system_modules = $this->ModuleAdmin->findSystemList();
		if($system_modules === false) {
			$this->flash(__('Unauthorized request.<br />Please reload the page.', true), null, 'Module.index.002');
			return;
		}
		$not_install_modules = $this->ModuleAdmin->findInstallList($general_modules, $system_modules);

		$this->set('version', $this->Config->getVersion());
		$this->set('general_modules', $general_modules);
		$this->set('system_modules', $system_modules);
		$this->set('not_install_modules', $not_install_modules);
		$this->set('active_tab', 0);	// TODO:固定
	}
}