<?php
/**
 * ModuleSystemLinkモデル
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Model
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class ModuleSystemLink extends AppModel
{
	public $name = 'ModuleSystemLink';

	/**
	 * 管理権限を求める
	 * @param   string $dirname
	 * @param   integer $authority_id
	 * @return  integer $hierarchy エラーの場合、false
	 */
	function findHierarchy($dirname, $authority_id) {
		$nc_module = Configure::read(NC_SYSTEM_KEY.'.Modules.'.$dirname);
		if(!isset($nc_module['Module'])) {
			App::uses('Module', 'Model');
			$Module = new Module();
			$module = $Module->findByDirname($dirname);
			$module_id = $module['Module']['id'];
		} else {
			$module_id = $nc_module['Module']['id'];
		}
		$conditions = array(
			'ModuleSystemLink.authority_id' => $authority_id,
			'ModuleSystemLink.module_id' => $module_id
		);

		$module_systems_link_params = array(
			'fields' => array(
				'ModuleSystemLink.hierarchy'
			),
			'conditions' => $conditions
		);
		$module_systems_links = $this->find('first', $module_systems_link_params);

		if(empty($module_systems_links['ModuleSystemLink'])) {
			return 0;
		}

		return intval($module_systems_links['ModuleSystemLink']['hierarchy']);
	}
}