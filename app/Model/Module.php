<?php
/**
 * Moduleモデル
 *
 * <pre>
 *  モジュールのプラグイン一覧
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Model
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */

class Module extends AppModel
{
	public $name = 'Module';

/**
 * Moduleの値取得
 * @param  string  $dir_name
 * @return array $module
 */
	public function findByDirname($dir_name) {
		$module_params = array(
			//'fields' => array('Module.*'),
			'conditions' => array('Module.dir_name' => $dir_name)
		);
		$module = $this->find('first', $module_params);
		if(empty($module['Module'])) {
			return false;
		}
		return $module;
	}

	public function afterFind($results, $dir_name = '') {
		$ret = array();
		$locale = Configure::read(NC_SYSTEM_KEY.'.locale');

		$single_flag = false;
		if(isset($results['Module']['dir_name'])) {
			$single_flag = true;
			$results = array($results);
		}

		foreach ($results as $key => $result) {
			if(!isset($result['Module']['id'])) {
				continue;
			}
			if(isset($result['Module']['dir_name'])) {
				$dir_name = $result['Module']['dir_name'];
			} else {
				$result['Module']['dir_name'] = $dir_name;
			}

			$result['Module']['ini'] = $this->loadInstallIni($dir_name);

			//
 	        // default値
 	        //
			if(!isset($result['Module']['ini']['system_flag'])) {
				$result['Module']['ini']['system_flag'] = _OFF;
			}
			if(!isset($result['Module']['ini']['disposition_flag'])) {
 	        	$result['Module']['ini']['disposition_flag'] = _ON;
 	        }
			if(!isset($result['Module']['ini']['module_icon'])) {
 	        	$result['Module']['ini']['module_icon'] = '';
 	        }
			if(!isset($result['Module']['ini']['temp_name'])) {
				$result['Module']['ini']['temp_name'] = 'Default';
			}
			if(!isset($result['Module']['ini']['content_has_one'])) {
				$result['Module']['ini']['content_has_one'] = _OFF;
			}

			$result['Module']['module_name'] = $this->loadModuleName($dir_name, $locale);

			if(empty($result['Module']['temp_name'])) {
	       		$result['Module']['temp_name'] = $result['Module']['ini']['temp_name'];
	       	}

			//if(isset($result['ModuleLink']['hierarchy'])) {
	       	//	$result['Module']['hierarchy'] = $result['ModuleLink']['hierarchy'];
	       	//}

			$ret[] = $result;
		}
		if($single_flag) {
			return $ret[0];
		}
		return $ret;
	}

/**
 * Moduleのintall.iniの値取得
 * @param  string  $dir_name
 * @return array install.ini配列
 */
	public static function loadInstallIni($dir_name) {
		$install_inc_ini = array();
		$file_path = App::pluginPath($dir_name) . NC_INSTALL_INC_FILE;
		if (file_exists($file_path)) {
			$install_inc_ini = parse_ini_file($file_path);
		}
		return $install_inc_ini;
	}

/**
 * Module名称取得
 * @param  string  $dir_name
 * @param  string  $locale
 * @return string  $module_name
 */
	public static function loadModuleName($dir_name, $locale = null) {
		$module_name = __('New module', true);
		if(!isset($locale)) {
			$locale = Configure::read(NC_SYSTEM_KEY.'.locale');
		}
		$file_path = App::pluginPath($dir_name) . 'Locale'. '/' . $locale. '/'. NC_MODINFO_FILENAME;
		if (file_exists($file_path)) {
 	        $modinfo_ini = parse_ini_file($file_path);
 	        if(!empty($modinfo_ini["module_name"])) {
 	        	$module_name = $modinfo_ini["module_name"];
 	        }
       	}
       	return $module_name;
	}

/**
 * 操作関数が存在するかどうか
 * @param  string  $dir_name
 * @param  string  $action
 * @return boolean
 */
	public function isOperationAction($dir_name, $action) {
		App::uses($dir_name.'OperationsComponent', 'Plugin/'.$dir_name.'/Controller/Component');
		$class_name = $dir_name.'OperationsComponent';
		if(!class_exists($class_name) || !method_exists($class_name, $action)) {
			// ショートカットと移動は関数がなくてもエラーとしない
			return false;
		}
		return true;
	}

/**
 * ブロック操作関数を実行
 * @param  string      $dir_name
 * @param  string      $action
 * @param array       $args
 * @return boolean
 * @since   v 3.0.0.0
 */
	public function operationAction($dir_name, $action, $args) {
		$class_name = $dir_name.'OperationsComponent';
		if(!class_exists($class_name)) {
			App::uses($dir_name.'OperationsComponent', 'Plugin/'.$dir_name.'/Controller/Component');
			if(!class_exists($class_name)) {
				return false;
			}
		}
		if(!method_exists($class_name, $action)) {
			return true;
		}

		eval('$class = new '.$class_name.'();');
		$class->startup();

		$ret = call_user_func_array(array($class, $action), $args);
		if(!$ret) {
			return false;
		}
		return true;
	}
}