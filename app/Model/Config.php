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
	/*public $hasMany = array(
		'ConfigLang' => array(
			'className'  => 'ConfigLang',
			'conditions' => array('ConfigLang.lang'  => 'en'),
			'order'      => 'Recipe.created DESC'
		)
	);*/

/**
 * afterFind
 *
 * @param  array   $results
 * @param  boolean $primary
 * @return array $results
 * @since   v 3.0.0.0
 */
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
				if(isset($result['ConfigLang']['value']) && !is_null($result['ConfigLang']['value'])) {
					$result['Config']['value'] = $result['ConfigLang']['value'];
				}
				if(isset($result['ConfigLang']['id']) && !is_null($result['ConfigLang']['id'])) {
					$result['Config']['config_lang_id'] = $result['ConfigLang']['id'];
				}
				$ret[$result['Config']['name']] = $result['Config']['value'];
			}
		} else if(isset($results[0]['Config']['module_id']) && $results[0]['Config']['module_id'] != 0) {
			foreach ($results as $key => $result) {
				if(isset($result['ConfigLang']['value']) && !is_null($result['ConfigLang']['value'])) {
					$result['Config']['value'] = $result['ConfigLang']['value'];
				}
				if(isset($result['ConfigLang']['id']) && !is_null($result['ConfigLang']['id'])) {
					$result['Config']['config_lang_id'] = $result['ConfigLang']['id'];
				}
				$ret[$result['Config']['module_id']][$result['Config']['name']] = $result['Config'];
			}
		} else {
			foreach ($results as $key => $result) {
				if(isset($result['ConfigLang']['value']) && !is_null($result['ConfigLang']['value'])) {
					$result['Config']['value'] = $result['ConfigLang']['value'];
				}
				if(isset($result['ConfigLang']['id']) && !is_null($result['ConfigLang']['id'])) {
					$result['Config']['config_lang_id'] = $result['ConfigLang']['id'];
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

/**
 * Version情報取得
 *
 * @param  void
 * @return string
 * @since   v 3.0.0.0
 */
	public function getVersion() {
		$conditions = array(
			'name' => 'version',
			'cat_id' => NC_SYSTEM_CATID
		);
		$params = array(
			'fields' => array(
				'Config.name',
				'Config.value'
			),
			'conditions' => $conditions
		);
		$configs = $this->find('all', $params);
		if(isset($configs['version'])) {
			return $configs['version'];
		}
		return NC_VERSION;
	}

/**
 * Configモデル共通JOIN文
 * @param   string  $lang
 * @return  array   $joins
 * @since   v 3.0.0.0
 */
	public function getJoinsArray($lang) {
		return array(
			array(
				"type" => "LEFT",
				"table" => "config_langs",
				"alias" => "ConfigLang",
				"conditions" => array(
					"`ConfigLang`.`config_name`=`Config`.`name`",
					"`ConfigLang`.`lang`" => $lang
				)
			),
		);
	}
}