<?php
/**
 * ConfigLangモデル
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Model
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
namespace App\Model;

use App\Model\AppModel;
use Cake\Event\Event;

class ConfigLang extends AppModel
{
/**
 * moduleId, configName, langデータからの登録処理
 *
 * @param  integer $moduleId
 * @param  string  $configName
 * @param  string  $lang
 * @param  string  $value
 * @return boolean
 * @since  v 3.0.0.0
 */
	public function saveKeys($moduleId, $configName, $lang, $value) {
		$insConfigLang = array('ConfigLang' => array(
			'module_id' => $moduleId,
			'config_name' => $configName,
			'lang' => $lang,
		));
		$configLang = $this->find('first', array('field' => array('id'), 'conditions' => $insConfigLang['ConfigLang'], 'recursive' => -1));

		if(isset($configLang['ConfigLang'])) {
			// update
			$insConfigLang['ConfigLang']['id'] = $configLang['ConfigLang']['id'];
		}
		$insConfigLang['ConfigLang']['value'] = $value;
		return $this->save($insConfigLang);
	}
}
