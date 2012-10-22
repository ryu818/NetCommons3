<?php
/**
 * Configモデル
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Model
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class Config extends AppModel
{
	public $name = 'Config';
	/*public $hasOne = array(
		'ConfigsLang' => array(
			'className' => 'ConfigsLang',
			'conditions' => array('ConfigsLang.lang'  => 'en'),
			'foreignKey' => 'config_id'
		)
	);*/

	public function afterFind($results, $primary = false) {
		$ret = array();
		//$single_flag = false;
		//if(isset($results['Config'])) {
		//	$single_flag = true;
		//	$results = array($results);
		//}
		if(isset($results[0]['Config']) && count($results[0]['Config']) == 2 &&
			 isset($results[0]['Config']['name']) && isset($results[0]['Config']['value'])) {
			// key = value
			foreach ($results as $key => $result) {
				if(isset($result['ConfigsLang']['value']) && !is_null($result['ConfigsLang']['value'])) {
					$result['Config']['value'] = $result['ConfigsLang']['value'];
				}
				if(isset($result['ConfigsLang']['id']) && !is_null($result['ConfigsLang']['id'])) {
					$result['Config']['configs_lang_id'] = $result['ConfigsLang']['id'];
				}
				$ret[$result['Config']['name']] = $result['Config']['value'];
			}
		} else if(isset($results[0]['Config']['module_id']) && $results[0]['Config']['module_id'] != 0) {
			foreach ($results as $key => $result) {
				if(isset($result['ConfigsLang']['value']) && !is_null($result['ConfigsLang']['value'])) {
					$result['Config']['value'] = $result['ConfigsLang']['value'];
				}
				if(isset($result['ConfigsLang']['id']) && !is_null($result['ConfigsLang']['id'])) {
					$result['Config']['configs_lang_id'] = $result['ConfigsLang']['id'];
				}
				$ret[$result['Config']['module_id']][$result['Config']['name']] = $result['Config'];
			}
		} else {
			foreach ($results as $key => $result) {
				if(isset($result['ConfigsLang']['value']) && !is_null($result['ConfigsLang']['value'])) {
					$result['Config']['value'] = $result['ConfigsLang']['value'];
				}
				if(isset($result['ConfigsLang']['id']) && !is_null($result['ConfigsLang']['id'])) {
					$result['Config']['configs_lang_id'] = $result['ConfigsLang']['id'];
				}
				$ret[$result['Config']['name']] = $result['Config'];
			}
		}
		//if($single_flag) {
			//$buf_ret = array($ret);
			//return $buf_ret;
		//}
		return $ret;
	}
}