<?php
/**
 * ModuleAdminモデル
 *
 * <pre>
 *  モジュール管理用モデル
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Plugin.Block.Model
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class ModuleAdmin extends AppModel {
	public $useTable = 'modules';
	public $alias = 'Module';
	public $actsAs = array('File');

/**
 * Schema class being used.
 *
 * @var CakeSchema
 */
	public $Schema;

/**
 * コンストラクター
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function __construct() {
		parent::__construct();
		App::uses('File', 'Core');
		App::uses('CakeSchema', 'Model');
		$this->Schema = new CakeSchema(compact('name', 'path', 'file', 'connection', 'plugin'));
	}

/**
 * 一般モジュール一覧取得
 *
 * @param   void
 * @return  Model Modules or false
 * @since   v 3.0.0.0
 */
	public function findList() {
		$order = array(
			'Module.display_sequence' => "ASC"
		);
		$params = array(
			'conditions' => array('Module.system_flag' => _OFF),
			'order' => $order
		);

		$general_modules = $this->find('all', $params);
		if(!$general_modules) {
			return $general_modules;
		}
		return $general_modules;
	}

/**
 * システムモジュール一覧取得
 *
 * @param   void
 * @return  Model Modules or false
 * @since   v 3.0.0.0
 */
	public function findSystemList() {
		$order = array(
			'Module.display_sequence' => "ASC"
		);
		$params = array(
			'conditions' => array('Module.system_flag' => _ON),
			'order' => $order
		);
		$system_modules = $this->find('all', $params);
		if(!$system_modules) {
			return $system_modules;
		}
		return $system_modules;
	}

/**
 * インストール可能モジュール一覧取得
 *
 * @param   Model Modules $general_modules
 * @param   Model Modules $system_modules
 * @return  array $not_install_modules
 * @since   v 3.0.0.0
 */
	public function findInstallList($general_modules, $system_modules) {
		App::uses('Module', 'Model');
		$Module = new Module();
		$locale = Configure::read(NC_SYSTEM_KEY.'.locale');

		$installed = array();
		foreach($system_modules as $system_module) {
			$installed[] = $system_module['Module']['dir_name'];
		}
		foreach($general_modules as $general_module) {
			$installed[] = $general_module['Module']['dir_name'];
		}

		$not_install_modules = array();

		$paths = App::path('Plugin');
		foreach ($paths as $path) {
			$plugin_paths = $this->getCurrentDir($path);
			if($plugin_paths === false || count($plugin_paths) == 0) {
				continue;
			}
			foreach( $plugin_paths as $plugin_name){
				if (in_array($plugin_name, $installed)) {
					continue;
				}
				$full_path = $path . $plugin_name;
				$install_inc_ini_path = $full_path. DS .NC_INSTALL_INC_FILE;
				if(!isset($not_install_modules[$plugin_name]) && @file_exists($install_inc_ini_path)) {
					$install_inc_ini = parse_ini_file($install_inc_ini_path);
					$not_install_modules[$plugin_name]['Module'] = $install_inc_ini;
					$not_install_modules[$plugin_name]['Module']['module_name'] = __('New module');

					if(@file_exists($full_path . DS . 'Locale'. DS . $locale. '/'. NC_MODINFO_FILENAME)) {
						$modinfo_ini = parse_ini_file($full_path . DS . 'Locale'. DS . $locale. DS. NC_MODINFO_FILENAME);
					}
					if(!empty($modinfo_ini["module_name"])) {
						$not_install_modules[$plugin_name]['Module']['module_name'] = $modinfo_ini["module_name"];
					}

					$not_install_modules[$plugin_name]['Module']['dir_name'] = $plugin_name;
					$not_install_modules[$plugin_name]['Module']['install_flag'] = _ON;
				}
			}
		}

		return $not_install_modules;
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
		App::uses('Module', 'Model');
		$Module = new Module();
		return $Module->afterFind($results, $primary);
	}

/**
 * モジュールインストール処理
 *
 * @param  string $dirName
 * @return array (successMes, errorMes)
 * @since   v 3.0.0.0
 */
	public function installModule($dirName) {
		App::uses('Module', 'Model');
		$Module = new Module();
		$successMes = array();
		$errorMes = array();
		if(empty($dirName)) {
			$errorMes[] = __d('module', 'Module %s:', h($dirName)).__('Unauthorized request.<br />Please reload the page.');
			return array($successMes, $errorMes);
		}

		$module = array();
		$module['Module']['dir_name'] = $dirName;
		$module = $this->afterFind($module);
		$module_name = $module['Module']['module_name'];
		$prefix = __d('module', 'Module %s:', h($module_name));

		// ----------------------------------------------
		//  チェック処理
		// ----------------------------------------------
		$result = $this->check($module);
		if($result !== true) {
			$errorMes[] = $result;
			return array($successMes, $errorMes);
		}

		// ------------------------------------------------------------
		// --- 同一のディレクトリ名で既に登録されていないかチェック ---
		// ------------------------------------------------------------
		if($Module->findByDirname($dirName) !== false) {
			$errorMes[] = $prefix.__d('module', 'This module is already installed.');
			return array($successMes, $errorMes);
		}

		$default_enable_flag = isset($module['Module']['ini']['default_enable_flag']) ? intval($module['Module']['ini']['default_enable_flag']) : _ON;
		$module['Module'] = array_merge($module['Module'], $module['Module']['ini']);
		unset($module['Module']['ini']);
		unset($module['Module']['module_name']);
		$displayseq_conditions = array(
			"Module.system_flag" => $module['Module']['system_flag']
		);
		$max_result = $this->find('first', array("fields" => "MAX(Module.display_sequence) as max_number", "conditions" => $displayseq_conditions));
		if(isset($max_result[0])) {
			$module['Module']['display_sequence'] = $max_result[0]['max_number'] + 1;
		} else {
			$module['Module']['display_sequence'] = 1;
		}

		// ----------------------------------------------
		// --- Module Insert                     	 ---
		// ----------------------------------------------
		$this->create();
		$this->set($module);
		if(!$this->save($module)) {
			if(count($this->validationErrors) == 0) {
				$errorMes[] = $prefix.__('Failed to register the database, (%s).','modules');
			} else {
				foreach($this->validationErrors as $field => $errors) {
					$errorMes[] = $prefix.$errors[0];	// 最初の１つ目
				}
			}
			return array($successMes, $errorMes);
		}
		$module_id = $this->id;

		// ----------------------------------------------
		// --- スキーマ実行(create)	 ---
		// ----------------------------------------------
		if(!$this->createSchema($dirName, $prefix, $errorMes)) {
			return array($successMes, $errorMes);
		}


		// -------------------------------------------------------------------------------------------
		// --- 権限モジュールリンクデータ登録, システム権限リンクデータ登録                       ---
		// -------------------------------------------------------------------------------------------
		if($module['Module']['system_flag']) {
			// module_system_links Insert
			if(!$this->saveModuleSystemLinkAdmin($module_id)) {
				$errorMes[] = $prefix.__('Failed to register the database, (%s).','module_system_links');
				return array($successMes, $errorMes);
			}
		} else {
			// module_links Insert(default_enable_flagを見て、insert)
			if(!$this->saveModuleLinkDefaultEnableFlag($module_id, $default_enable_flag)) {
				$errorMes[] = $prefix.__('Failed to register the database, (%s).','module_links');
				return array($successMes, $errorMes);
			}
		}

		$successMes[] = $prefix.__d('module', 'Install was successful.');
		return array($successMes, $errorMes);
	}

/**
 * モジュールアップデート処理
 *
 * @param  array    $tables
 * @param  string   $dirName
 * @return array (successMes, error_mes)
 * @since   v 3.0.0.0
 */
	public function updateModule($tables, $dirName) {
		App::uses('Sanitize', 'Utility');
		App::uses('ModuleSystemLink', 'Model');
		App::uses('ModuleLink', 'Model');
		$ModuleLink = new ModuleLink();
		$ModuleSystemLink = new ModuleSystemLink();

		$successMes = array();
		$errorMes = array();
		$module = $this->_getModule($dirName, $errorMes);
		if($module === false) {
			return array($successMes, $errorMes);
		}
		$module_id = $module['Module']['id'];
		$plugin_name = Inflector::camelize($module['Module']['dir_name']);
		$default_enable_flag = isset($module['Module']['ini']['default_enable_flag']) ? intval($module['Module']['ini']['default_enable_flag']) : _OFF;

		// ----------------------------------------------
		//  チェック処理
		// ----------------------------------------------
		$result = $this->check($module);
		if($result !== true) {
			$errorMes[] = $result;
			return array($successMes, $errorMes);
		}

		// ----------------------------------------------
		// --- アップデートアクション実行(update)	 ---
		// ----------------------------------------------
		$module_name = $module['Module']['module_name'];
		$prefix = __d('module', 'Module %s:', h($module_name));

		if($module['Module']['ini']['controller_action'] != $module['Module']['controller_action']) {
			/**
			 * Block Update
			 */
			App::uses('Block', 'Model');
			$Block = new Block();
			$fields = array('Block.controller_action' => "'" . Sanitize::escape($module['Module']['ini']['controller_action']) . "'");
			$conditions = array(
				"Block.module_id" => $module_id
			);
			if(!$Block->updateAll($fields, $conditions)) {
				$errorMes[] = $prefix.__('Failed to update the database, (%s).','blocks');
				return array($successMes, $errorMes);
			}
		}

		if($module['Module']['ini']['disposition_flag'] != $module['Module']['disposition_flag'] &&
				$module['Module']['ini']['disposition_flag'] == _OFF) {
			if($module['Module']['system_flag'] == _ON) {
				/**
				 * モジュールリンクデータ削除(module_system_links)
				 *    配置不可能モジュールの場合
				 */
				if(!$ModuleSystemLink->deleteByModuleId($module_id)) {
					$errorMes[] = $prefix.__('Failed to delete the database, (%s).','module_system_links');
					return array($successMes, $errorMes);
				}
			} else {
				/**
				 * モジュールリンクデータ削除(module_links)
				 *    配置不可能モジュールの場合
				 */
				if(!$ModuleLink->deleteByModuleId($module_id)) {
					$errorMes[] = $prefix.__('Failed to delete the database, (%s).','module_links');
					return array($successMes, $errorMes);
				}
			}
		}

		if($module['Module']['ini']['system_flag'] != $module['Module']['system_flag'] &&
			$module['Module']['ini']['disposition_flag'] == _ON) {
			if($module['Module']['system_flag'] == _ON) {
				// module_links Delete
				if(!$ModuleLink->deleteByModuleId($module_id)) {
					$errorMes[] = $prefix.__('Failed to delete the database, (%s).','module_links');
					return array($successMes, $errorMes);
				}
				// module_system_links Insert
				if(!$this->saveModuleSystemLinkAdmin($module_id)) {
					$errorMes[] = $prefix.__('Failed to register the database, (%s).','module_system_links');
					return array($successMes, $errorMes);
				}
			} else {
				// module_system_links Delete
				if(!$ModuleSystemLink->deleteByModuleId($module_id)) {
					$errorMes[] = $prefix.__('Failed to delete the database, (%s).','module_system_links');
					return array($successMes, $errorMes);
				}
				// module_links Insert(default_enable_flagを見て、insert)
				if(!$this->saveModuleLinkDefaultEnableFlag($module_id, $default_enable_flag)) {
					$errorMes[] = $prefix.__('Failed to register the database, (%s).','module_links');
					return array($successMes, $errorMes);
				}
			}
		}

		$module['Module'] = array_merge($module['Module'], $module['Module']['ini']);
		unset($module['Module']['ini']);
		unset($module['Module']['module_name']);

		// ----------------------------------------------
		// --- Module Update                     	 ---
		// ----------------------------------------------
		$this->create();
		$this->set($module);
		$fieldList = array(
			'dir_name',
			'version',
			'system_flag',
			'disposition_flag',
			'controller_action',
			'edit_controller_action',
			'style_controller_action',
			'module_icon',
			'temp_name',
			'content_has_one',
		);
		if(!$this->save($module, true, $fieldList)) {
			if(count($this->validationErrors) == 0) {
				$errorMes[] = $prefix.__('Failed to update the database, (%s).','modules');
			} else {
				foreach($this->validationErrors as $field => $errors) {
					$errorMes[] = $prefix.$errors[0];	// 最初の１つ目
				}
			}
			return array($successMes, $errorMes);
		}

		// ----------------------------------------------
		// --- Assetの削除処理                      ---
		// ----------------------------------------------
		if(!$this->_deleteAsset($plugin_name, $errorMes)) {
			return array($successMes, $errorMes);
		}

		// ----------------------------------------------
		// --- スキーマ実行(update)	 ---
		// ----------------------------------------------
		if(!$this->updateSchema($dirName, $prefix, $errorMes, $tables)) {
			return array($successMes, $errorMes);
		}

		$successMes[] = $prefix.__d('module', 'Update was successful.');
		return array($successMes, $errorMes);
	}

/**
 * モジュール一括アップデート処理
 *
 * @param  array    $tables
 * @return array (successMes, error_mes)
 * @since   v 3.0.0.0
 */
	public function updateAllModule($tables) {
		$successMes = array();
		$errorMes = array();
		$prefix = '';

		// ----------------------------------------------
		// --- Assetの全削除処理                      ---
		// ----------------------------------------------
		App::uses('Asset', 'Model');
		$Asset = new Asset();
		$Asset->gc(null, true, true);

		// ----------------------------------------------
		// --- スキーマ実行(update)	 ---
		// ----------------------------------------------
		if(!$this->updateSchema(null, $prefix, $errorMes, $tables)) {
			return array($successMes, $errorMes);
		}

		$params = array(
			'fields' => array(
				'Module.dir_name'
			),
			'order' => array(
				'Module.system_flag',
				'Module.display_sequence'
			)
		);
		$modules = $this->find('list', $params);
		if(count($modules) > 0) {
			foreach($modules as $dirName) {
				list($buf_successMes, $buf_error_mes) = $this->updateModule($tables, $dirName);
				$successMes = array_merge($successMes, $buf_successMes);
				$errorMes = array_merge($errorMes, $buf_error_mes);
			}
		}

		if(count($errorMes) > 0) {
			return array($successMes, $errorMes);
		}

		// ----------------------------------------------
		// --- バージョンアップデート                 ---
		// ----------------------------------------------
		App::uses('Config', 'Model');
		$Config = new Config();
		$fields = array(
			'Config.value'=> '"'.NC_VERSION.'"'
		);
		if(!$Config->updateAll($fields, array('Config.name' => 'version', 'Config.cat_id' => NC_SYSTEM_CATID))) {
			$errorMes[] = $prefix.__('Failed to update the database, (%s).','configs');
			return array($successMes, $errorMes);
		}

		$successMes[] = __d('module', 'All update was successful.');
		return array($successMes, $errorMes);
	}

/**
 * モジュールアンインストール処理
 *
 * @param  string   $dirName
 * @return array (successMes, error_mes)
 * @since   v 3.0.0.0
 */
	public function uninstallModule($dirName) {
		App::uses('Sanitize', 'Utility');
		App::uses('Module', 'Model');
		App::uses('Content', 'Model');
		App::uses('ModuleSystemLink', 'Model');
		App::uses('ModuleLink', 'Model');
		App::uses('Upload', 'Model');
		$Content = new Content();
		$Module = new Module();
		$ModuleLink = new ModuleLink();
		$ModuleSystemLink = new ModuleSystemLink();
		$Upload = new Upload();

		$successMes = array();
		$errorMes = array();
		$module = $this->_getModule($dirName, $errorMes);
		if($module === false) {
			return array($successMes, $errorMes);
		}
		$module_id = $module['Module']['id'];
		$plugin_name = Inflector::camelize($module['Module']['dir_name']);
		$module_name = $module['Module']['module_name'];
		$prefix = __d('module', 'Module %s:', h($module_name));

		// ------------------------------------------------------
		// --- ブロックテーブルのデータ削除                  ---
		// ------------------------------------------------------
		// TODO: col_num,row_num,グループ化ブロック等を整列しなおす必要があるため、現状、コメント
		// deleteBlockにその処理もいれて、ブロックの削除もそちらを呼ぶだけにするほうが望ましい。
		/*
		App::uses('Block', 'Model');
		$Block = new Block();
		$conditions = array(
			'module_id' => $module_id
		);
		$order = array(
			'Block.thread_num' => "ASC",
			'Block.col_num' => "ASC",
			'Block.row_num' => "ASC"
		);
		$params = array(
			'fields' => array(
				'Block.*'
			),
			'conditions' => $conditions,
			'order' => $order
		);
		$blocks = $Block->find('all', $params);
		if(isset($blocks[0])) {
			foreach($blocks as $block) {
				if(!$Block->deleteBlock($block)) {
					$errorMes[] = $prefix.__d('module', 'Failed to %s.',t __d('module', 'Delete block')
					return array($successMes, $errorMes);
				}
			}
		}*/

		// ------------------------------------------------------
		// --- コンテンツテーブルのデータ削除                  ---
		// ------------------------------------------------------
		$conditions = array(
			'module_id' => $module_id
		);
		$params = array(
			'fields' => array(
				'Content.*'
			),
			'conditions' => $conditions
		);
		$contents = $Content->find('all', $params);
		if(isset($contents)) {
			foreach($contents as $content) {
				if(!$Content->deleteContent($content, _ON)) {
					$errorMes[] = $prefix.__d('module', 'Failed to %s.', __d('module', 'Delete content'));
					return array($successMes, $errorMes);
				}
			}
		}

		// ------------------------------------------------------
		// --- アップロードテーブルのデータ削除＆ファイル削除 ---
		// ------------------------------------------------------
		if(is_dir(NC_UPLOADS_DIR.$dirName) && !$this->File->delDir(NC_UPLOADS_DIR.$dirName)) {
			// upload file
			$errorMes[] = $prefix.__d('module', 'Failed to %s.', __d('module', 'Delete upload file'));
			return array($successMes, $errorMes);
		}
		$condition = array('Upload.module_id' => $module_id);
		if(!$Upload->deleteAll($condition)) {
			$errorMes[] = $prefix.__('Failed to delete the database, (%s).','uploads');
			return array($successMes, $errorMes);
		}

		// ------------------------------------------------------
		// --- システム権限モジュールリンクデータ削除         ---
		// --- 権限モジュールリンクデータ削除                 ---
		// ------------------------------------------------------
		if($module['Module']['system_flag']) {
			// module_system_links Delete
			if(!$ModuleSystemLink->deleteByModuleId($module_id)) {
				$errorMes[] = $prefix.__('Failed to delete the database, (%s).','module_system_links');
				return array($successMes, $errorMes);
			}
		} else {
			// module_links Delete
			if(!$ModuleLink->deleteByModuleId($module_id)) {
				$errorMes[] = $prefix.__('Failed to delete the database, (%s).','module_links');
				return array($successMes, $errorMes);
			}
		}

		// ----------------------------------------------
		// --- Assetの削除処理                      ---
		// ----------------------------------------------
		if(!$this->_deleteAsset($plugin_name, $errorMes)) {
			return array($successMes, $errorMes);
		}

		// ------------------------------------------------------
		// --- モジュールテーブルのデータ削除                ---
		// ------------------------------------------------------
		if(!$Module->delete($module_id)) {
			$errorMes[] = $prefix.__('Failed to delete the database, (%s).','modules');
			return array($successMes, $errorMes);
		}
		$displayseq_fields = array('Module.display_sequence'=>'Module.display_sequence-1');
		$displayseq_conditions = array(
			"Module.system_flag" => $module['Module']['system_flag'],
			"Module.display_sequence >=" => $module['Module']['display_sequence']
		);
		if(!$Module->updateAll($displayseq_fields, $displayseq_conditions)) {
			$errorMes[] = $prefix.__('Failed to update the database, (%s).','modules');
			return array($successMes, $errorMes);
		}

		// ----------------------------------------------
		// --- スキーマ実行(update)	 ---
		// ----------------------------------------------
		if(!$this->dropSchema($dirName, $prefix, $errorMes)) {
			return array($successMes, $errorMes);
		}

		$successMes[] = $prefix.__d('module', 'Uninstall was successful.');
		return array($successMes, $errorMes);
	}

/**
 * モジュールデータ取得
 * @param string $dirName
 * @param array  $successMes
 * @param array  $errorMes
 * @return mixed Model Module $module or boolean false
 * @since   v 3.0.0.0
 */
	private function _getModule($dirName, &$errorMes) {
		App::uses('Module', 'Model');
		$Module = new Module();

		if(empty($dirName)) {
			$errorMes[] = __('Unauthorized request.<br />Please reload the page.');
			return false;
		}
		$module = $Module->findByDirname($dirName);
		if(!$module) {
			$errorMes[] = __('Unauthorized request.<br />Please reload the page.');
			return false;
		}
		return $module;
	}

/**
 * Assetの削除処理
 * @param string $plugin_name
 * @param array  $successMes
 * @param array  $errorMes
 * @return mixed Model Module $module or boolean false
 * @since   v 3.0.0.0
 */
	private function _deleteAsset($plugin_name, &$errorMes) {
		App::uses('Asset', 'Model');
		$Asset = new Asset();
		$conditions = array(
			"Asset.plugin" => $plugin_name
		);
		if(!$Asset->deleteAll($conditions)) {
			$errorMes[] = $prefix.__('Failed to delete the database, (%s).','asset');
			return false;
		}
		return true;
	}

/**
 * モジュールデータチェック処理
 * @param  array   $module
 * @return mixed boolean true or string error
 * @since   v 3.0.0.0
 */
	protected function check($module) {
		$module_name = $module['Module']['module_name'];
		$module_icon = !isset($module['Module']['ini']['module_icon']) ? '' : $module['Module']['ini']['module_icon'];
		$temp_name = !isset($module['Module']['ini']['temp_name']) ? '' : $module['Module']['ini']['temp_name'];
		$prefix = __d('module', 'Module %s:', h($module_name));

		$plugin_path = App::pluginPath($module['Module']['dir_name']);
		$install_ini_path =  $plugin_path . NC_INSTALL_INC_FILE;
		$module_icon_path = $plugin_path . 'webroot' . DS . 'img' . DS;
		if($temp_name != '') {
			$module_temp_name_path = $plugin_path . 'View' . DS . 'Themed' . DS . $temp_name . DS;
		}

		if(!isset($module['Module']['ini'])) {
			return $prefix.__d('module', '[%s] does not found.', h($install_ini_path));
		}

		if(!isset($module['Module']['ini']['version'])) {
			return $prefix.__d('module', '[%s] does not found in the [%s].', 'version', h($install_ini_path));
		}

		if(!isset($module['Module']['ini']['controller_action'])) {
			return $prefix.__d('module', '[controller_action] has not been described in [%s].', h($install_ini_path));
		}

		if($module_icon != '' && !file_exists($module_icon_path.$module_icon)) {
			return $prefix.__d('module', '[%s] does not found in the [%s].', h($module_icon), h($module_icon_path));
		}

		// Module.temp_name
		if($temp_name != '' && !file_exists($module_temp_name_path)) {
			return $prefix.__d('module', '[%s] does not found.', h($module_temp_name_path));
		}

		$result = $this->fileExistsController($plugin_path, $module['Module']['dir_name'], $module['Module']['ini']['controller_action']);
		if($result !== true) {
			return $prefix.$result;
		}

		// edit_controller_actionチェック
		if(isset($module['Module']['ini']['edit_controller_action'])) {
			$result = $this->fileExistsController($plugin_path, $module['Module']['dir_name'], $module['Module']['ini']['edit_controller_action'], true);
			if($result !== true) {
				return $prefix.$result;
			}
		}

		// style_controller_actionチェック
		if(isset($module['Module']['ini']['style_controller_action'])) {
			$result = $this->fileExistsController($plugin_path, $module['Module']['dir_name'], $module['Module']['ini']['style_controller_action'], true);
			if($result !== true) {
				return $prefix.$result;
			}
		}

		return true;
	}

/**
 * コントローラファイルの存在チェック
 *
 * @param string $plugin_path
 * @param string $dirName
 * @param string $controller_action
 * @param boolean $is_edit
 * @return boolean true or error message
 */
	protected function fileExistsController($plugin_path, $dirName, $controller_action, $is_edit = false) {
		$controller_action_arr = explode('/', $controller_action);
		$plugin = Inflector::camelize($controller_action_arr[0]);
		if($plugin != $dirName ||
				!preg_match("/^[0-9a-zA-Z_\/]+$/", $controller_action)) {
			return __('Unauthorized pattern for %s.', 'controller_action');
		}

		$app_module_controller_path = $plugin_path . 'Controller'. DS . $plugin.'AppController.php';
		if(file_exists($app_module_controller_path)) {
			include_once $app_module_controller_path;
		}

		$controller = Inflector::camelize($controller_action_arr[0]);
		$action = isset($controller_action_arr[1]) ? $controller_action_arr[1] : '';
		$sub_action = isset($controller_action_arr[2]) ? $controller_action_arr[2] : '';

		$is_file_exists = false;
		$is_class_exists = false;
		$is_method_exists = false;
		$class_name = $controller.'Controller';
		$module_controller_path = $plugin_path . 'Controller'. DS . $class_name.'.php';
		if(file_exists($module_controller_path)) {
			$is_file_exists = true;
			include_once $module_controller_path;
			if(class_exists($class_name)) {
				$is_class_exists = true;
				if($action != '') {
					if(method_exists($class_name, $action)) {
						$is_method_exists = true;
					}
				} else if(method_exists($class_name, 'index')) {
					$is_method_exists = true;
				}
			}
		}
		if(!$is_method_exists && $action != '') {
			$is_file_exists = false;
			$is_class_exists = false;
			$is_method_exists = false;
			$sub_class_name = Inflector::camelize($controller_action_arr[0]).Inflector::camelize(Inflector::pluralize($action)).'Controller';
			$edit_module_controller_path = $plugin_path . 'Controller'. DS . $sub_class_name.'.php';
			if(file_exists($edit_module_controller_path)) {
				$is_file_exists = true;
				include_once $edit_module_controller_path;
				if(class_exists($sub_class_name)) {
					$is_class_exists = true;
					if($sub_action != '') {
						if(method_exists($sub_class_name, $sub_action)) {
							$is_method_exists = true;
						}
					} else if(method_exists($sub_class_name, 'index')) {
						$is_method_exists = true;
					}
				}
			}
		}
		if($is_edit) {
			$class_name = $sub_class_name;
			$action = $sub_action;
			$module_controller_path = $edit_module_controller_path;
		}
		if($is_file_exists && !$is_class_exists) {
			return __d('module', '[%s] does not found in the [%s].', h($class_name), h($module_controller_path));
		} else if($is_class_exists && !$is_method_exists) {
			$action = ($action == '') ? 'index' : $action;
			return __d('module', '[%s] does not found in the [%s].', h($class_name).'::'.$action, h($module_controller_path));
		} else if(!$is_file_exists) {
			return __d('module', '[%s] does not found.', h($module_controller_path));
		}
		return true;
	}

/**
 * 権限モジュールリンクデータ登録
 * ・default_enable_flag=_ONならば、space_type=1,2,3,4それぞれのレコードを追加
 * ・default_enable_flag=_OFFならば、authority_id=管理者のレコードインサート
 *
 * @param integer $module_id
 * @return Model Authority
 */
	public function saveModuleLinkDefaultEnableFlag($module_id, $default_enable_flag = null) {
		App::uses('ModuleLink', 'Model');
		$ModuleLink = new ModuleLink();

		$module_link = array(
			'ModuleLink' => array(
				'space_type' => NC_SPACE_TYPE_PUBLIC,
				'authority_id' => 0,
				'room_id' => 0,
				'module_id' => $module_id
			)
		);
		if(isset($default_enable_flag) && $default_enable_flag == _ON) {
			// パブリック直下、マイポータル、マイルームのすべての権限、コミュニティ直下に追加
			$authority_ids = $this->findAuthorityIdList();
			$space_type_ids = array(NC_SPACE_TYPE_PUBLIC, NC_SPACE_TYPE_MYPORTAL, NC_SPACE_TYPE_PRIVATE, NC_SPACE_TYPE_GROUP);
		} else {
			// 管理者のみ追加
			$authority_ids = $this->findAuthorityIdList(array('Authority.hierarchy >=' => NC_AUTH_MIN_ADMIN));
			$space_type_ids = array(NC_SPACE_TYPE_MYPORTAL, NC_SPACE_TYPE_PRIVATE);
		}

		foreach($space_type_ids as $space_type) {
			$module_link['ModuleLink']['space_type'] = $space_type;
			$module_link['ModuleLink']['authority_id'] = 0;
			if($space_type == NC_SPACE_TYPE_MYPORTAL || $space_type == NC_SPACE_TYPE_PRIVATE) {
				foreach($authority_ids as $authority_id) {
					$module_link['ModuleLink']['authority_id'] = $authority_id;
					$ModuleLink->create();
					$ModuleLink->set($module_link);
					if(!$ModuleLink->save($module_link)) {
						return false;
					}
				}
			} else {
				$ModuleLink->create();
				$ModuleLink->set($module_link);
				// TODO: space_type, authority_id,room_id, module_idでUniqueKeyをはったほうがよい
				if(!$ModuleLink->save($module_link)) {
					return false;
				}
			}
		}
		return true;
	}
/**
 * システム権限モジュールリンクデータ登録

 *
 * @param integer $module_id
 * @param integer $hierarchy
 * @return Model Authority
 */
	public function saveModuleSystemLinkAdmin($module_id, $hierarchy = NC_AUTH_ADMIN) {
		App::uses('ModuleSystemLink', 'Model');
		$ModuleSystemLink = new ModuleSystemLink();
		$authority_ids = $this->findAuthorityIdList(array('Authority.hierarchy >=' => NC_AUTH_MIN_ADMIN));

		$module_system_link = array(
			'ModuleSystemLink' => array(
				'module_id' => $module_id,
				'hierarchy' => $hierarchy
			)
		);

		foreach($authority_ids as $authority_id) {
			$module_system_link['ModuleSystemLink']['authority_id'] = $authority_id;
			$ModuleSystemLink->create();
			$ModuleSystemLink->set($module_system_link);
			if(!$ModuleSystemLink->save($module_system_link)) {
				return false;
			}
		}

		return true;
	}
/**
 * 権限IDのリストを取得
 * @param array $conditions
 * @return Model Authority
 */
	public function findAuthorityIdList($conditions = array()) {
		App::uses('Authority', 'Model');
		$Authority = new Authority();
		//$conditions = array('Authority.hierarchy >=' => NC_AUTH_MIN_ADMIN);
		$params = array(
			'fields' => array(
				'Authority.id'
			),
			'conditions' => $conditions
		);
		return $Authority->find('list', $params);
	}

/**
 * Run database create commands.  Alias for run create.
 * <pre>
 * SchemaShellのcreateメソッドのモジュール管理バージョン(Create)
 * </pre>
 * @param  string $dirName
 * @param string $prefix
 * @param array $errorMes
 * @return boolean
 */
	public function createSchema($dirName, $prefix, &$errorMes) {
		$Schema = $this->_loadSchema($dirName);
		return $this->_createSchema($Schema, $prefix, $errorMes);
	}

/**
 * Run database drop commands.  Alias for run drop.
 * <pre>
 * SchemaShellのcreateメソッドのモジュール管理バージョン(Drop)
 * </pre>
 * @param  string $dirName
 * @param string $prefix
 * @param array $errorMes
 * @return boolean
 */
	public function dropSchema($dirName, $prefix, &$errorMes) {
		$Schema = $this->_loadSchema($dirName);
		return $this->_dropSchema($Schema, $prefix, $errorMes);
	}

/**
 * Run database create commands.  Alias for run create.
 * <pre>
 * SchemaShellのupdateメソッドのモジュール管理バージョン
 * </pre>
 * @param  array  $tables
 * @param  string $dirName
 * @param string $prefix
 * @param array $errorMes
 * @return boolean
 */
	public function updateSchema($dirName, $prefix, &$errorMes, $tables) {
		$Schema = $this->_loadSchema($dirName);
		return $this->_updateSchema($Schema, $prefix, $errorMes, null, $tables);
	}

/**
 * Prepares the Schema objects for database operations.
 * <pre>
 * SchemaShellの_loadSchemaメソッドのモジュール管理バージョン
 * </pre>
 * @param  string $dirName
 * @return boolean
 */
	protected function _loadSchema($dirName = null) {
		$name = $plugin = $dirName;
		if(isset($name)) {
			$options = array('name' => $name, 'plugin' => $plugin);
		} else {
			$options = array('name' => 'App', 'file' => 'schema.php', 'path' => APP . 'Config' . DS. 'Schema');
		}
		$Schema = $this->Schema->load($options);
		if (!$Schema) {
			return false;
		}
		return $Schema;
	}
/**
 * Create database from Schema object
 * Should be called via the run method
 * <pre>
 * SchemaShellの_createメソッドのモジュール管理バージョン(Create table)
 * </pre>
 * @param CakeSchema $Schema
 * @param string $prefix
 * @param array $errorMes
 * @param string $table
 * @param array $tables
 * @return void
 */
	protected function _createSchema($Schema, $prefix, &$errorMes, $table = null, $tables = array()) {
		$db = ConnectionManager::getDataSource($this->useDbConfig);

		$create = array();

		if (!$table) {
			if(isset($Schema->tables)) {
				foreach ($Schema->tables as $table => $fields) {
					if (count($tables) == 0 || !in_array($this->tablePrefix.$table, $tables)) {
						$create[$table] = $db->createSchema($Schema, $table);
					}
				}
			}
		} elseif (isset($Schema->tables[$table]) && (count($tables) == 0 || !in_array($this->tablePrefix.$table, $tables))) {
			$create[$table] = $db->createSchema($Schema, $table);
		}
		if (empty($create)) {
			if(!isset($Schema->tables)) {
				// テーブルが存在しないならば、常にtrue
				return true;
			} else {
				// テーブルがあるが、既に存在している場合、false
				return false;
			}
		}

		return $this->_runSchema($create, 'create', $Schema, $prefix, $errorMes);
	}

/**
 * Create database from Schema object
 * Should be called via the run method
 * <pre>
 * SchemaShellの_createメソッドのモジュール管理バージョン(Drop)
 * </pre>
 * @param CakeSchema $Schema
 * @param string $prefix
 * @param array $errorMes
 * @param string $table
 * @return void
 */
	protected function _dropSchema($Schema, $prefix, &$errorMes, $table = null) {
		$db = ConnectionManager::getDataSource($this->useDbConfig);

		$drop = array();

		if (!$table) {
			if(isset($Schema->tables)) {
				foreach ($Schema->tables as $table => $fields) {
					$drop[$table] = $db->dropSchema($Schema, $table);
				}
			}
		} elseif (isset($Schema->tables[$table])) {
			$drop[$table] = $db->dropSchema($Schema, $table);
		}
		if (empty($drop)) {
			return true;
		}

		return $this->_runSchema($drop, 'drop', $Schema, $prefix, $errorMes);
	}

/**
 * Update database with Schema object
 * Should be called via the run method
 *
 * <pre>
 * SchemaShellの_updateメソッドのモジュール管理バージョン
 * </pre>
 *
 * @param CakeSchema $Schema
 * @param string $prefix
 * @param array $errorMes
 * @param string $table
 * @param array $tables
 * @return boolean
 */
	protected function _updateSchema(&$Schema, $prefix, &$errorMes, $table = null, $tables = array()) {
		$db = ConnectionManager::getDataSource($this->useDbConfig);

		//$this->out(__d('cake_console', 'Comparing Database to Schema...'));
		$options = array();
		//if (isset($this->params['force'])) {
		//	$options['models'] = false;
		//}
		$options['models'] = false;
		$Old = $this->Schema->read($options);
		$compare = $this->Schema->compare($Old, $Schema);

		$contents = array();

		if (empty($table)) {
			foreach ($compare as $table => $changes) {
				if(!$this->_createSchema($Schema, $prefix, $errorMes, $table, $tables)) {
					$contents[$table] = $db->alterSchema(array($table => $changes), $table);
				}
			}
		} elseif (isset($compare[$table])) {
			if(!$this->_createSchema($Schema, $prefix, $errorMes, $table, $tables)) {
				$contents[$table] = $db->alterSchema(array($table => $compare[$table]), $table);
			}
		}

		if (empty($contents)) {
			return true;
		}

		return $this->_runSchema($contents, 'update', $Schema, $prefix, $errorMes);
	}
/**
 * Runs sql from _create() or _update()
 *
 * <pre>
 * SchemaShellの_runメソッドのモジュール管理バージョン
 * </pre>
 *
 * @param array $contents
 * @param string $event     'create' or 'update' or 'drop'
 * @param CakeSchema $Schema
 * @param string $prefix
 * @param array $errorMes
 * @return boolean
 */
	protected function _runSchema($contents, $event, &$Schema, $prefix, &$errorMes) {
		if (empty($contents)) {
			return true;
		}
		// Configure::write('debug', 2);
		$db = ConnectionManager::getDataSource($this->useDbConfig);
		switch ($event) {
			case 'create':
				$title = __d('module', 'Install Action');
				break;
			case 'update':
				$title = __d('module', 'Update Action');
				break;
			case 'drop':
				$title = __d('module', 'Uninstall Action');
				break;
		}

		$is_error = false;
		foreach ($contents as $table => $sql) {
			if (empty($sql)) {
				// sql空
				//$this->out(__d('cake_console', '%s is up to date.', $table));
			} else {
				if (!$Schema->before(array($event => $table))) {
					$is_error = true;
					$errorMes[] = $prefix.__d('module', 'Failed to %s.', $title);
					continue;
				}
				$error = null;
				try {
					$db->execute($sql);
				} catch (PDOException $e) {
					$error = $table . ': ' . $e->getMessage();
				}

				$Schema->after(array($event => $table, 'errors' => $error));

				if (!empty($error)) {
					$is_error = true;
					$errorMes[] = $prefix.$error;
				} //else {
				//	$this->out(__d('cake_console', '%s updated.', $table));
				//}
			}
		}
		if($is_error) {
			return false;
		}

		return true;
	}
}