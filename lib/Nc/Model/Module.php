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
 * construct
 * @param integer|string|array $id Set this ID for this model on startup, can also be an array of options, see above.
 * @param string $table Name of database table to use.
 * @param string $ds DataSource connection name.
 * @return  void
 * @since   v 3.0.0.0
 */
	public function __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);

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
 * afterFind
 *
 * @param  array   $results
 * @param  boolean $primary
 * @param  string  $alias
 * @return array $results
 * @since   v 3.0.0.0
 */
	public function afterFind($results, $primary = false, $alias=null) {
		if(isset($results[0][0])) {
			// max等
			return $results;
		}

		$ret = array();
		$locale = Configure::read(NC_SYSTEM_KEY.'.locale');
		if(empty($alias)) {
			$alias = $this->alias;
		}

		$single_flag = false;
		if(isset($results[$alias]['dir_name'])) {
			$single_flag = true;
			$results = array($results);
		}

		foreach ($results as $key => $result) {
			if(!isset($result[$alias]['dir_name'])) {
				continue;
			}
			$dir_name = $result[$alias]['dir_name'];

			$result[$alias]['ini'] = $this->loadInstallIni($dir_name);

			//
 	        // default値
 	        //
			if(!isset($result[$alias]['ini']['system_flag'])) {
				$result[$alias]['ini']['system_flag'] = _OFF;
			}
			if(!isset($result[$alias]['ini']['disposition_flag'])) {
 	        	$result[$alias]['ini']['disposition_flag'] = _ON;
 	        }
			if(!isset($result[$alias]['ini']['module_icon'])) {
 	        	$result[$alias]['ini']['module_icon'] = '';
 	        }
			if(!isset($result[$alias]['ini']['temp_name'])) {
				$result[$alias]['ini']['temp_name'] = '';
			}
			// TODO:content_has_oneカラム自体使用するかどうか未検討
			if(!isset($result[$alias]['ini']['content_has_one'])) {
				$result[$alias]['ini']['content_has_one'] = _OFF;
			}

			$result[$alias]['module_name'] = $this->loadModuleName($dir_name, $locale);

			if(empty($result[$alias]['temp_name'])) {
	       		$result[$alias]['temp_name'] = $result[$alias]['ini']['temp_name'];
	       	}

			//if(isset($result['ModuleLink']['hierarchy'])) {
	       	//	$result[$alias]['hierarchy'] = $result['ModuleLink']['hierarchy'];
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

		$class = new $class_name(new ComponentCollection());
		$class->startup();

		$ret = call_user_func_array(array($class, $action), $args);
		if(!$ret) {
			return false;
		}
		return true;
	}
}