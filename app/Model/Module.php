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
/**
 * バリデート処理
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function __construct() {
		parent::__construct();

		/*
		 * エラーメッセージ設定
		*/
		$this->validate = array(
			// dir_name
			// version

			'system_flag' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'allowEmpty' => true,
					'message' => __('The input must be a boolean.')
				)
			),
			'disposition_flag' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'allowEmpty' => true,
					'message' => __('The input must be a boolean.')
				)
			),
			// controller_action
			// edit_controller_action
			// style_controller_action


			'display_sequence' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'allowEmpty' => true,
					'message' => __('The input must be a number.')
				)
			),
			// module_icon
			// temp_name
			// content_has_one
			//

			'copy_operation' => array(
				'inList' => array(
					'rule' => array('inList', array(
						'enable',		// 使用可能だがデフォルト使用不可(システム管理より変更可)
						'enabled',		// 使用可能
						'disabled',		// 使用不可
					), false),
					'allowEmpty' => false,
					'message' => __('It contains an invalid string.')
				)
			),
			'shortcut_operation' => array(
				'inList' => array(
					'rule' => array('inList', array(
						'enable',		// 使用可能だがデフォルト使用不可(システム管理より変更可)
						'enabled',		// 使用可能
						'disabled',		// 使用不可
					), false),
					'allowEmpty' => false,
					'message' => __('It contains an invalid string.')
				)
			),
			'move_operation' => array(
				'inList' => array(
					'rule' => array('inList', array(
						'enable',		// 使用可能だがデフォルト使用不可(システム管理より変更可)
						'enabled',		// 使用可能
						'disabled',		// 使用不可
					), false),
					'allowEmpty' => false,
					'message' => __('It contains an invalid string.')
				)
			),
		);
	}

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
		$locale = Configure::read(NC_SYSTEM_KEY.'.locale');

		$single_flag = false;
		if(isset($results['Module']['dir_name'])) {
			$single_flag = true;
			$results = array($results);
		}

		foreach ($results as $key => $result) {
			if(!isset($result['Module']['dir_name'])) {
				continue;
			}
			$dir_name = $result['Module']['dir_name'];

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
				$result['Module']['ini']['temp_name'] = '';
			}
			// TODO:content_has_oneカラム自体使用するかどうか未検討
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
 * コントロールパネルの表示リスト一覧取得
 * @param  integer  $authority_id
 * @param  string   $joinType
 * @return Model Modules
 */
	public function findSystemModule($authority_id, $joinType = "INNER") {
		$conditions = array(
			'Module.system_flag' => _ON,
			'Module.disposition_flag' => _ON
		);
		$order = array(
			'Module.display_sequence' => "ASC"
		);
		$params = array(
			'fields' => array(
				'Module.*', 'ModuleSystemLink.hierarchy'
			),
			'joins' => array(
				array("type" => $joinType,
					"table" => "module_system_links",
					"alias" => "ModuleSystemLink",
					"conditions" => array(
						"`ModuleSystemLink`.`module_id`=`Module`.`id`",
						"`ModuleSystemLink`.`authority_id`" => intval($authority_id)
					)
				),
			),
			'conditions' => $conditions,
			'order' => $order
		);

		return $this->find('all', $params);
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
		$module_name = __('Untitled').'['.$dir_name.']';
		if(!isset($locale)) {
			$locale = Configure::read(NC_SYSTEM_KEY.'.locale');
		}

		if (!CakePlugin::loaded($dir_name)) {
			return $module_name;
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
		App::uses($dir_name.'OperationComponent', 'Plugin/'.$dir_name.'/Controller/Component');
		$class_name = $dir_name.'OperationComponent';
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
		$class_name = $dir_name.'OperationComponent';
		if(!class_exists($class_name)) {
			App::uses($dir_name.'OperationComponent', 'Plugin/'.$dir_name.'/Controller/Component');
			if(!class_exists($class_name)) {
				// isOperationActionで確認するため、ここではエラーにはしない。
				return true;
			}
		}
		if(!method_exists($class_name, $action)) {
			return true;
		}

		eval('$class = new '.$class_name.'(new ComponentCollection());');
		$class->startup();

		$ret = call_user_func_array(array($class, $action), $args);
		if(!$ret) {
			return false;
		}
		return true;
	}
}