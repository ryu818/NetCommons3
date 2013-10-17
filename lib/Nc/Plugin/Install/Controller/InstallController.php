<?php
/**
 * InstallControllerクラス
 *
 * <pre>
 * インストーラ
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       App.Controller
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
App::uses('Folder', 'Utility');

class InstallController extends Controller {
	/**
	 * コントローラ名
	 *
	 * @var string
	 */
	public $name = 'Install';

	/**
	 * モデル
	 *
	 * @var array
	 */
	public $uses = null;

	/**
	 * コンポーネント
	 *
	 * @var array
	 */
	public $components = array(
		'Session',
	);

/**
 * ヘルパー
 *
 * @var array
 */
	public $helpers = array(
		'Html',
	);

	/**
	 * Default値
	 *
	 * @var array
	 */
	public $defaultConfig = array(
		'site_name' => '',
		'name' => 'default',
		'datasource'=> 'Database/Mysql',
		'persistent'=> false,
		'host'=> 'localhost',
		'login'=> 'root',
		'password'=> '',
		'database'=> '',
		'schema'=> null,
		'prefix'=> 'nc3_',
		'encoding' => 'UTF8',
		'port' => null,
	);

/**
 * beforeFilter
 *
 * @return void
 * @since  v 3.0.0.0
 */
	public function beforeFilter() {
		parent::beforeFilter();
		//$this->disableCache();

		$this->layout = 'install';
	}

/**
 * インストールチェック
 *
 * @return void
 * @since  v 3.0.0.0
 */
	protected function _check() {
		if(!defined('NC_INSTALLED') && NC_INSTALLED == true) {
			$this->Session->setFlash(__d('install', 'Already Installed.'));
			$this->redirect('/');
		}

		$select_lang = isset($this->request->data['select_lang']) ? $this->request->data['select_lang'] : 'jpn';
		$chk_database = isset($this->request->data['chk_database']) ? $this->request->data['chk_database'] : _OFF;
		$this->set("select_lang" , $select_lang);
		Configure::write(NC_CONFIG_KEY.'.'.'language', $select_lang);

		$this->defaultConfig['site_name'] = __d('install', 'Undefined site name');

		$sess_config = $this->Session->read('Database.config');
		if(!isset($this->request->data['Database']) && isset($sess_config)) {
			$config = $this->Session->read('Database.config');
		} else {
			$config = $this->defaultConfig;
		}
		$this->set('config', $config);
		if (!isset($this->request->data['Database'])) {
			return $config;
		}

		//$site_name = '';
		$required = true;
		$title_str = '';
		foreach ($this->request->data['Database'] AS $key => $value) {
			if (isset($this->request->data['Database'][$key])) {
				//if($key == "site_name") {
				//$site_name = $config[$key];
				//unset($config[$key]);
				//} else
				if($key == "persistent") {
					$config[$key] = ($value == "true") ? true : false;
				} else if($key == "port") {
					$config[$key] = ($value == "") ? null : $value;
				} else {
					$config[$key] = $value;
				}
				if($value == '' && $key != "port" && $key != "password") {
					$required = false;
					if($title_str != '') {
						$title_str .= ',';
					}
					switch($key) {
						case 'site_name':
							$title_str .=  __d('install', 'Site Name');
							break;
						case 'datasource':
							$title_str .=  __d('install', 'Database');
							break;
						case 'host':
							$title_str .=  __d('install', 'Database Hostname');
							break;
						case 'login':
							$title_str .=  __d('install', 'Database Username');
							break;
						case 'password':
							$title_str .=  __d('install', 'Database Password');
							break;
						case 'database':
							$title_str .=  __d('install', 'Database Name');
							break;
						case 'prefix':
							$title_str .=  __d('install', 'Table Prefix');
							break;
					}
				}
			}
		}
		$this->set('config', $config);
		if($chk_database && $required == false) {
			$mes = __('Please input %s.', $title_str);
			$this->Session->setFlash($mes, 'default', array('class' => 'header-error'));
			return false;
		}
		return $config;
	}

/**
 * welcome & 言語設定
 *
 * @return void
 * @since  v 3.0.0.0
 */
	public function index() {
		$this->_check();
		$dir = new Folder(App::pluginPath('Install') . 'Locale');
		list($dirs, $files) = $dir->read();
		$this->set("lang_list" , $dirs);
	}

/**
 * introduction
 *
 * @return void
 * @since  v 3.0.0.0
 */
	public function introduction() {
		$this->_check();
	}

/**
 * アクセスチェック
 *
 * @return void
 * @since  v 3.0.0.0
 */
	public function check() {
		$this->_check();
	}

/**
 * database設定
 *
 * @return void
 * @since  v 3.0.0.0
 */
	public function database() {
		$config = $this->_check();
		$setting_db = isset($this->request->data['setting_db']) ? $this->request->data['setting_db'] : _OFF;

		if (!$setting_db || empty($this->request->data) || !$config) {
			return;
		}

		// 接続確認
		if(!$this->_connect($config)) {
			return;
		}

		$this->Session->write('Database.config', $config);
		$this->redirect(array('action' => 'dbconfirm'));
	}

/**
 * dbconfirm設定
 *
 * @return void
 * @since  v 3.0.0.0
 */
	public function dbconfirm() {
		$exists_database = true;
		$failure_database = false;
		$exists_table = false;
		$this->request->data['Database'] = $this->Session->read('Database.config');
		$config = $this->_check();

		$create_db = isset($this->request->data['create_db']) ? $this->request->data['create_db'] : _OFF;
		if($create_db) {
			// データベース作成を試みる
			if(!$this->_createDb($config)) {
				// DB作成失敗
				$failure_database = true;
			}
		}

		App::uses('ConnectionManager', 'Model');
		try {
			ConnectionManager::create('install', $config);
			$db = ConnectionManager::getDataSource('install');
			$connected = $db->isConnected();
		} catch (Exception $e) {
			$connected = false;
		}

		if (!$connected) {
			// データベースが存在しない
			$exists_database = false;
		} else {
			// テーブル存在チェック modules
			try {
				$result = @$db->query("SELECT * FROM ".$config['prefix']."modules");
				if($result !== false) {
					$exists_table = true;
				}
			} catch (Exception $e) {
				$exists_table = false;
			}
		}
		$this->set("failure_database", $failure_database);
		$this->set("exists_database", $exists_database);
		$this->set("exists_table", $exists_table);
	}

/**
 * DB接続
 * TODO:mysql以外、未テスト
 * @param  array $config
 * @return mixed $connection or false
 * @since  v 3.0.0.0
 */
	protected function _connect($config) {
		$connection = false;
		$datasource = $config['datasource'];
		switch($datasource) {
			case 'Database/Mysql':
				if(!function_exists('mysql_connect')) {
					$mes = __d('install', 'Could not connect to database.<br />Call to undefined function %s.', 'mysql_connect()');
					$this->Session->setFlash($mes, 'default', array('class' => 'header-error'));
					return false;
				} else {
					$connection = @mysql_connect($config['host'] . ':' . $config['port'], $config['login'], $config['password'], true);
				}
				break;
			/*case 'mysqli':
				if(!function_exists('mysqli_connect')) {
					$mes = __d('install', 'Could not connect to database.<br />Call to undefined function %s.', 'mysqli_connect()');
					$this->Session->setFlash($mes, 'default', array('class' => 'header-error'));
					return false;
				} else {
					//$connection = mysqli_connect($config['host'], $config['login'], $config['password'], $config['database'], $config['port'], $config['socket']);
					$connection = @mysqli_connect($config['host'], $config['login'], $config['password'], null, $config['port'], $config['socket']);
				}
				break;*/
			case 'Database/Sqlite':
				//if(!function_exists('sqlite_open')) {
				if(class_exists('PDO') && version_compare ( preg_replace('/[a-z-]/','', phpversion()),'5','>=')) {
					/* SQLite利用チェック */
					$mes = __d('install', 'Could not connect to database.<br />Call to undefined function %s.', 'sqlite_open()');
					$this->Session->setFlash($mes, 'default', array('class' => 'header-error'));
					return false;
				} else {
					$connection = @sqlite_open($config['database']);
				}
				break;
			case 'Database/Postgres':
				if(!function_exists('pg_connect')) {
					$mes = __d('install', 'Could not connect to database.<br />Call to undefined function %s.', 'pg_pconnect()');
					$this->Session->setFlash($mes, 'default', array('class' => 'header-error'));
					return false;
				} else {
					$conn  = "host='{$config['host']}' port='{$config['port']}' dbname='{$config['database']}' ";
					$conn .= "user='{$config['login']}' password='{$config['password']}'";
					$connection = @pg_pconnect($conn);
				}
				break;
			case 'Database/Sqlserver':
				if(!function_exists('mssql_connect')) {
					$mes = __d('install', 'Could not connect to database.<br />Call to undefined function %s.', 'mssql_connect()');
					$this->Session->setFlash($mes, 'default', array('class' => 'header-error'));
					return false;
				} else {
					$connection = @mssql_connect($config['host'] . $port, $config['login'], $config['password'], true);
				}
				break;
		}

		if (!$connection) {
			$this->Session->setFlash(__d('install', 'Could not connect to database.<br />Please check the database server and its configuration.'), 'default', array('class' => 'header-error'));
			return false;
		}
		return $connection;
	}

/**
 * DB作成
 * TODO:現状、MYSQL以外、作成失敗
 * @return boolean
 * @since  v 3.0.0.0
 */
	protected function _createDb($config) {
		$connection = $this->_connect($config);
		if(!$connection) {
			return;
		}
		$datasource = $config['datasource'];
		$dbname = $config['database'];
		$append_str = "";
		$char_set = "utf8";
		if(strstr($datasource, "Database/Mysql")) {
			$server_info = $this->_query("select version()", $connection);
			$version = 0;
			while ($row = mysql_fetch_assoc($server_info)) {
				foreach($row as $v) {
					$version = $v;
					break;
				}
				break;
			}
			if(floatval($version) >= 4.01) {
				$append_str = " DEFAULT CHARACTER SET ".$char_set;
				//if($char_set == "utf8") {
				$append_str .= " COLLATE utf8_general_ci";
				//}
			}
		}

		switch($datasource) {
			case 'Database/Mysql':
			//case 'Database/Mysqli':
				$result = $this->_query("CREATE DATABASE ". $dbname . "".$append_str, $connection);
				break;
			default:
				// TODO:現状、MYSQL以外、作成失敗
				return false;
		}

		if ($result === false) {
			return false;
		}

		return true;
	}

	protected function _query($str, $connection = null) {
		if(!function_exists('mysqli_query')) {
			$result = mysqli_query($str, $connection);
		} else {
			$result = mysql_query($str, $connection);
		}
		return $result;
	}

/**
 * テーブル作成
 * install.inc.php書き込み
 *
 * @return void
 * @since  v 3.0.0.0
 */
	public function data() {
		ini_set("memory_limit", "32M");

		$exists_database = true;
		$config = $this->_check();

		ClassRegistry::init('ConnectionManager');
		try {
			ConnectionManager::create('install', $config);
			$db = ConnectionManager::getDataSource('install');
			$connected = $db->isConnected();
		} catch (Exception $e) {
			$connected = false;
		}
		if (!$connected) {
			// データベースが存在しない
			$exists_database = false;
			$this->set("exists_database", $exists_database);
			return;
		}
		$this->set("exists_database", $exists_database);

		/*
		 * テーブル作成
		*/
		App::uses('Folder', 'Utility');
		App::uses('File', 'Utility');

		$tables = array();
		$not_tables = array();
		$CakeSchema = ClassRegistry::init('CakeSchema');
		$options = array(array('name'=>'app'));
		$schema = $CakeSchema->load($options);

		foreach($schema->tables as $table => $fields) {
			$create = $db->createSchema($schema, $table);
			if($db->execute($create)) {
				$tables[] = $table;
			} else {
				$not_tables[] = $table;
			}
		}

		$xmlFilePath = App::pluginPath('Install') . DS . 'Config' . DS . 'Data' . DS . 'default' . DS;	// TODO:default固定
		$xmlFile = $xmlFilePath . 'data.xml';
		$xmlArray = Xml::toArray(Xml::build(file_get_contents($xmlFile)));
		if (empty($xmlArray)) {
			$this->set('failure_datas', true);
			return;
		}

		$tableDatas = $xmlArray['database']['table'];

		$columnNameArr = array();
		foreach ($tableDatas as $tableData) {
			if ($tableData['@name'] != 'modules') {
				continue;
			}

			$columnDatas = $tableData['column'];
			foreach ($columnDatas as $columnData) {
				if($columnData['@name'] != 'dir_name') {
					continue;
				}

				if(!isset($columnData['@']) || $columnData['@'] === 'NULL') {
					continue;
				}
				$columnNameArr[$columnData['@']] = true;
			}
		}

		// page_styleコピー
		$pageStyleFolder = new Folder($xmlFilePath . 'theme' .DS .  'page_styles', false, 0755);
		$pageStyleFolder->copy(WWW_ROOT . 'theme' .DS .  'page_styles');

		/*
		 * Plugin テーブル作成
		 */
		$CakeSchema = ClassRegistry::init('CakeSchema');
		$paths = App::path('Plugin');
		$pluginArr = array();
		foreach($paths as $path) {
			$dir = new Folder($path);
			list($dirs, $files) = $dir->read();
			foreach($dirs as $dir) {
				$schemaPlugin = $CakeSchema->load(array('name' => $dir, 'plugin' => $dir));
				if($schemaPlugin === false) {
					continue;
				}
				if(!isset($columnNameArr[$dir])) {
					continue;
				}
				if(in_array($dir, $pluginArr)) {
					continue;
				}

				foreach($schemaPlugin->tables as $table => $fields) {
					if(in_array($table, $tables)) {
						// 既にcreate済
						continue;
					}
					$create = $db->createSchema($schemaPlugin, $table);
					if($db->execute($create)) {
						$tables[] = $table;
					} else {
						$not_tables[] = $table;
					}
				}
				$pluginArr[] = $dir;
			}
		}


		$this->set("tables", $tables);
		$this->set("not_tables", $not_tables);

		$err_mes = array();
		foreach ($tableDatas as $tableData) {
			$tableName = $db->name($config['prefix'].$tableData['@name']);
			$columnDatas = $tableData['column'];
			if(!isset($columnDatas[0])) {
				$columnDatas = array($columnDatas);
			}

			$columns = array();
			$datas = array();
			foreach ($columnDatas as $columnData) {
				$columnName = $columnData['@name'];

				$data = '';
				if(isset($columnData['@'])) {
					if($columnData['@'] === 'NULL') {
						continue;
					}
					$data = $columnData['@'];
				}

				$columnType = null;
// 				if (is_object($model)) {
// 					$columnType = $model->getColumnType($column_name);
// 				}
				$columns[] = $db->name($columnName);
				$datas[] = $db->value(str_replace('{NC_WEBROOT}', $this->webroot, $data), $columnType);
			}

			$query = array(
				'table' => $tableName,
				'fields' => implode(', ', $columns),
				'datas' => implode(', ', $datas)
			);
			extract($query);

			$sql = "INSERT INTO {$table} ({$fields}) VALUES ({$datas})";
			if(!$db->execute($sql)) {
				$this->set('failure_datas', true);
				$this->set('failure_sql', $sql);
				return;
			}
		}

		// install.inc.php書き込み
		copy(APP . 'Config' . DS . 'install.inc.php.default', APP . 'Config' . DS . NC_INSTALL_INC_FILE);
		$file = new File(APP . 'Config' . DS . NC_INSTALL_INC_FILE, true);
		$content = $file->read();
		if (!class_exists('Security')) {
			require LIBS . 'security.php';
		}
		$config['salt'] = Security::generateAuthKey();
		$config['cipherSeed'] = mt_rand() . mt_rand();

		foreach ($config AS $configKey => $configValue) {
			if($configKey == "persistent") {
				$configValue = ($configValue == true) ? 'true' : 'false';
			}
			$content = str_replace('{default_' . $configKey . '}', $configValue, $content);
		}

		$this->set("failure_install_ini", false);
		if(!$file->write($content) ) {
			$this->set("failure_install_ini", true);
			return;
		}
	}

/**
 * サイト管理者についての設定
 *
 * @return void
 * @since  v 3.0.0.0
 */
	public function admin_setting() {
		App::uses('Sanitize', 'Utility');
		//App::uses('AppModel', 'Model');
		$User = ClassRegistry::init('User');
		$user['User'] = empty($this->request->data['User']) ? null : $this->request->data['User'];

		$config = $this->_check();

		if(isset($user['User'])) {
			$user['User']['id'] = NC_SYSTEM_USER_ID;
			$this->set('user', $user);
			$User->set($user);
			//$User->id = NC_SYSTEM_USER_ID;
			$error = $this->validateErrors($User);

			if(!isset($error['password']) && $user['User']['password'] != $user['User']['confirm_password']) {
				$User->invalidate('password', __d('install', '%s and %s does not match. Please re-enter.',
						__d('install', 'Admin Password'),
						__d('install', 'Confirm Password')));
				return;
			}
			unset($user['User']['confirm_password']);
			if (!empty($error)) {
				return;
			}

			// 更新処理
			//if (!class_exists('Security')) {
			//	require LIBS . 'security.php';
			//}
			//$user['User']['password'] = Security::hash($user['User']['password'], null, true);
			App::uses('AuthComponent', 'Controller/Component');
			$user['User']['created'] = $User->nowDate();
			$user['User']['created_user_id'] = $user['User']['id'];
			$user['User']['created_user_name'] = $user['User']['handle'];
			$user['User']['modified'] = $User->nowDate();
			$user['User']['modified_user_id'] = $user['User']['id'];
			$user['User']['modified_user_name'] = $user['User']['handle'];
			if(!$User->save($user, false, array('login_id', 'handle', 'password', 'created', 'created_user_id',
				'created_user_name', 'modified', 'modified_user_id', 'modified_user_name'))) {
				$mes = __('Failed to update the database, (%s).', 'users');
				$this->Session->setFlash($mes, 'default', array('class' => 'header-error'));
				return;
			}

			return $this->redirect(array('action' => 'finish'));
		}
		// ----------------------------------------------
		// --- サイト名の更新処理                    ---
		// ----------------------------------------------
		$Config = ClassRegistry::init('Config');
		$ConfigLang = ClassRegistry::init('ConfigLang');

		$fields = array('Config.value' => "'" . Sanitize::escape($config['site_name']) . "'");
		$conditions = array('Config.id' => 1);
		$retConfig = $Config->updateAll($fields, $conditions);

		$fields = array('ConfigLang.value' => "'" . Sanitize::escape($config['site_name']) . "'");
		$conditions = array('ConfigLang.config_name' => 'sitename');
		$retConfigLang = $ConfigLang->updateAll($fields, $conditions);

		// バックグランド登録処理
		$Background = ClassRegistry::init('Background');
		$Background->updateAllInit();

		$this->set('user', $user);
	}

/**
 * インストール完了
 *
 * @return void
 * @since  v 3.0.0.0
 */
	public function finish() {
		$config = $this->_check();

		$install_ini_path = APP . 'Config' . DS . NC_INSTALL_INC_FILE;

		// install.inc.php書き込み
		// define("NC_INSTALLED", false);をdefine("NC_INSTALLED", true);に更新
		App::uses('File', 'Utility');
		$file = new File($install_ini_path, true);
		$content = $file->read();
		$content = str_replace('define("NC_INSTALLED", false);', 'define("NC_INSTALLED", true);', $content);

		if(!$file->write($content)) {
			$mes = __d('install', 'Could not write install.inc.php file.');
			$this->Session->setFlash($mes, 'default', array('class' => 'header-error'));
			return;
		}

		// TODO:後にコメントをはずす
		//@chmod($install_ini_path, 0444);
	}
}