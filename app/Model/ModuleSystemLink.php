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
/**
 * 管理権限をプラグイン名称から求める
 * @param   string  $pluginName
 * @param   integer $authorityId
 * @return  integer $hierarchy エラーの場合、false
 */
	function findHierarchyByPluginName($pluginName, $authorityId) {
		$dirname = Inflector::camelize($pluginName);
		$nc_module = Configure::read(NC_SYSTEM_KEY.'.Modules.'.$dirname);
		if(!isset($nc_module['Module'])) {
			App::uses('Module', 'Model');
			$Module = new Module();
			$module = $Module->findByDirname($dirname);
			$moduleId = $module['Module']['id'];
		} else {
			$moduleId = $nc_module['Module']['id'];
		}
		return $this->findHierarchy($moduleId, $authorityId);
	}

/**
 * 管理権限を求める
 * @param   integer $moduleId
 * @param   integer $authorityId
 * @return  integer $hierarchy エラーの場合、false
 */
	function findHierarchy($moduleId, $authorityId) {
		$conditions = array(
			'ModuleSystemLink.authority_id' => $authorityId,
			'ModuleSystemLink.module_id' => $moduleId
		);

		$moduleSystemsLinkParams = array(
			'fields' => array(
				'ModuleSystemLink.hierarchy'
			),
			'conditions' => $conditions
		);
		$moduleSystemsLinks = $this->find('first', $moduleSystemsLinkParams);

		if(empty($moduleSystemsLinks['ModuleSystemLink'])) {
			return NC_AUTH_OTHER;
		}

		return intval($moduleSystemsLinks['ModuleSystemLink']['hierarchy']);
	}

	/**
	 * module_idよりModuleSystemLinkデータ削除
	 *
	 * @param  integer $moduleId
	 * @return boolean
	 * @since   v 3.0.0.0
	 */
	public function deleteByModuleId($moduleId) {
		$conditions = array(
			"ModuleSystemLink.module_id" => $moduleId
		);
		if(!$this->deleteAll($conditions)) {
			return false;
		}
		return true;
	}
}