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
class ModuleLinkList extends AppModel {
	public $useTable = 'module_links';
	public $alias = 'ModuleLink';

/**
 * 配置可能一般モジュールの取得
 * @param   integer    $spaceType
 * @param   integer    $authorityId
 * @return  array
 * @since   v 3.0.0.0
 */
	public function findModulelinks($spaceType, $authorityId = 0) {
		$conditions = array(
			'ModuleLink.space_type' => $spaceType,
			'ModuleLink.authority_id' => $authorityId
		);
		$order = array('Module.display_sequence' => 'ASC');
		$params = array(
			'fields' => array(
				'ModuleLink.module_id',
				//'ModuleLink.authority_id',
				//'ModuleLink.space_type',
				//'Module.dir_name'
			),
			'joins' => array(
				array("type" => "INNER",
					"table" => "modules",
					"alias" => "Module",
					"conditions" => array(
						"`ModuleLink`.`module_id`=`Module`.`id`",
						'Module.disposition_flag' => _ON
					)
				)
			),
			'conditions' => $conditions,
			'order' => $order
		);
		return $this->find('list', $params);
	}
}