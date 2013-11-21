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
/**
 * construct
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);
	}

/**
 * Configリスト取得
 * @param   string  $type  list or all
 * @param   integer $moduleId
 * @param   integer $catId
 * @param   string  $lang
 * @param   boolean $afterFindOptions
 * @return  array
 * @since   v 3.0.0.0
 */
	public function findList($type, $moduleId, $catId, $lang = null, $afterFindOptions = false) {
		if(!isset($lang)) {
			$lang = Configure::read(NC_CONFIG_KEY.'.'.'language');
		}
		$conditions = array(
			$this->alias.'.module_id' => $moduleId,
			$this->alias.'.cat_id' => $catId
		);
		if($type == 'list') {
			$fields = array('Config.name', 'Config.value', 'ConfigLang.value');
		} else {
			$fields = array('Config.*', 'ConfigLang.value');
		}
		$results = $this->find('all', array(
			'fields' => $fields,
			'joins' => $this->getJoinsArray($lang, $moduleId),
			'conditions' => $conditions,
		));
		if($afterFindOptions) {
			$results = $this->afterFindOptions($results);
		}
		return $results;
	}

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
		return $ret;
	}

/**
 * afterFindAttributeOptions
 * Config.attribute, Config.options用AfterFind
 *
 * @param  array   $results
 * @return array $results
 * @since   v 3.0.0.0
 */
	public function afterFindOptions($results) {
		foreach($results as $key => $result) {
			if(isset($result['options']) && isset($result['domain'])) {
				if($result['name'] == 'default_TZ') {
					$UserItem = ClassRegistry::init('UserItem');
					$item = $UserItem->findList('first', array('UserItem.id' => NC_ITEM_ID_TIMEZONE_OFFSET));
					if(!isset($item['UserItemLang']['options'])) {
						$item['UserItemLang']['options'] = $item['UserItem']['default_options'];
					}
					$results[$key]['options'] = !empty($item['UserItemLang']['options']) ? unserialize($item['UserItemLang']['options']) : array();
				} else {
					$domain = $result['domain'];
					$results[$key]['options'] = $this->convertOptions($result['options'], $domain);
				}
			}
			if(isset($result['attribute'])) {
				$results[$key]['attribute'] = $this->convertAttribute($result['attribute']);
			}
		}
		return $results;
	}

/**
 * options配列変換処理
 *
 * @param  string $attribute
 * @param  string $domain
 * @return array
 * @since   v 3.0.0.0
 */
	public function convertOptions($options, $domain = '') {
		$retOptions = array();
		if(!empty($options)) {
			$bufOptions = unserialize($options);
			foreach($bufOptions as $key => $value) {
				if(defined($key)) {
					$key = constant($key);
				} else if($key != $value) {
					if(!isset($domain) || $domain == '') {
						$key = __($key);
					} else {
						$key = __d($domain, $key);
					}
				}
				if(!isset($domain) || $domain == '') {
					$value = __($value);
				} else {
					$value = __d($domain, $value);
				}
				$retOptions[$key] = $value;
			}
		}
		return $retOptions;
	}

/**
 * attribute配列変換処理
 *
 * @param  string $attribute
 * @return array
 * @since   v 3.0.0.0
 */
	public function convertAttribute($attribute) {
		$retAttribute = array();
		if(!empty($attribute)) {
			$attributeNames = array('class', 'cols', 'rows', 'style', 'size', 'div', 'maxlength');
			$regexp = "\s*=\s*([\"'])?([^ \"']*)";
			foreach($attributeNames as $attributeName) {
				if(preg_match("/".$attributeName.$regexp."/i" , $attribute, $matches)) {
					$retAttribute[$attributeName] = $matches[2];
				}
			}
		}
		return $retAttribute;
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
 * @param   integer $moduleId
 * @return  array   $joins
 * @since   v 3.0.0.0
 */
	public function getJoinsArray($lang, $moduleId = 0) {
		return array(
			array(
				"type" => "LEFT",
				"table" => "config_langs",
				"alias" => "ConfigLang",
				"conditions" => array(
					"`ConfigLang`.`config_name`=`Config`.`name`",
					"`ConfigLang`.`module_id`" => $moduleId,
					"`ConfigLang`.`lang`" => $lang,
				)
			),
		);
	}
}