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
			$db = ConnectionManager::getDataSource($this->ModuleAdmin->useDbConfig);
			$tables = $db->listSources();
			$dirName = isset($this->request->data['dir_name']) ? $this->request->data['dir_name'] : '';
			$type = isset($this->request->data['type']) ? $this->request->data['type'] : '';
			switch($type) {
				case 'install':
					$result = $this->ModuleAdmin->installModule($dirName);
					break;
				case 'update':
					$result = $this->ModuleAdmin->updateModule($tables, $dirName);
					break;
				case 'update-all':
					$result = $this->ModuleAdmin->updateAllModule($tables);
					break;
				case 'uninstall':
					$result = $this->ModuleAdmin->uninstallModule($dirName);
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
		$generalModules = $this->ModuleAdmin->findList();
		if($generalModules === false) {
			throw new InternalErrorException(__('Failed to obtain the database, (%s).', 'modules'));
		}
		$systemModules = $this->ModuleAdmin->findSystemList();
		if($systemModules === false) {
			throw new InternalErrorException(__('Failed to obtain the database, (%s).', 'modules'));
		}
		$notInstallModules = $this->ModuleAdmin->findInstallableList($generalModules, $systemModules);

		$this->set('version', $this->Config->getVersion());
		$this->set('general_modules', $generalModules);
		$this->set('system_modules', $systemModules);
		$this->set('not_install_modules', $notInstallModules);
		$this->set('active_tab', 0);	// TODO:固定
	}
}