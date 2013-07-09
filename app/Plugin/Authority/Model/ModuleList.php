<?php
/**
 * ModuleListモデル
 *
 * <pre>
 *  配置モジュール一覧用モデル
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Plugin.Block.Model
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class ModuleList extends AppModel {
	public $useTable = 'modules';
	public $alias = 'Module';

/**
 * 一般モジュールの全リスト取得
 * @param   integer    $spaceType
 * @param   integer    $authorityId
 * @return  array
 * @since   v 3.0.0.0
 */
	public function findGeneralModules() {
		App::uses('Module', 'Model');
		$Module = new Module();

		$params = array(
			'conditions' => array(
				'system_flag' => _OFF,
				'disposition_flag' => _ON
			),
			'order' => array(
				'display_sequence' => "ASC"
			)
		);

		$modules = $this->find('all', $params);
		foreach($modules as $key => $module) {
			$modules[$key]['Module']['module_name'] = $Module->loadModuleName($module['Module']['dir_name']);
		}

		return $modules;
	}
}