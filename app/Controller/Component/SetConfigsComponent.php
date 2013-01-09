<?php
/**
 * SetConfigsComponentクラス
 *
 * <pre>
 * Configテーブルのデータのセット処理
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       App.Controllers.Components
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class SetConfigsComponent extends Component {
/**
 * Other components utilized by Component
 *
 * @var     array
 */
	public $components = array('Auth', 'Session', 'Common');		// , 'Init', 'Auth'

/**
  * Start SetConfigsComponent for use in the controller
 *
 * @param Controller $controller
 * @return  void
 * @since   v 3.0.0.0
 */
	public function startup(Controller $controller) {
		$user = $this->Auth->user();//認証済みユーザーを取得
		$user_id = isset($user['id']) ? intval($user['id']) : 0;

		/*
		 * 同じ処理を何度も実行させないため
		 */
		$lang = Configure::read(NC_CONFIG_KEY.'.'.'sitename');
		if(isset($lang)) {
			return;
		}

		// ******************************************************************************************
		// 言語セット
		// ******************************************************************************************
		$config_language = Configure::read(NC_CONFIG_KEY.'.'.'config_language');
		$lang = isset($controller->request->query['lang']) ? $controller->request->query['lang'] : '';
		if($lang == '') {
			$lang = isset($controller->request->data['lang']) ? $controller->request->data['lang'] : '';
		}
		if(!empty($lang)) {
			$languages = $controller->Language->find('first', array('language' => $lang));
			if (!isset($languages['Language'])) {
				$lang = null;
			}else {
				$this->Session->write(NC_CONFIG_KEY.'.'.'language', $lang);
			}
		}
		if(empty($lang)) {
			$lang = $this->Session->read(NC_CONFIG_KEY.'.'.'language');
			if(empty($lang)) {
				//システム管理のシステム標準使用言語の「自動」で選択している場合、自動で判断する。
				if(empty($config_language)) {
					$lang = $this->_getAcceptLang($controller);
				} else {
					$lang = $config_language;
				}
				$this->Session->write(NC_CONFIG_KEY.'.'.'language', $lang);
			}
		}

		// ******************************************************************************************
		// Config
		// ******************************************************************************************
		$conditions = array(
			'module_id' => 0,
			'cat_id' => array(NC_SYSTEM_CATID, NC_LOGIN_CATID, NC_SERVER_CATID, NC_DEVELOPMENT_CATID)
		);
		$params = array(
			'fields' => array(
								'Config.name',
								'Config.value',
								'ConfigLang.value'
			),
			'conditions' => $conditions,
			'joins' => array(
				array(
						"type" => "LEFT",
						"table" => "config_langs",
						"alias" => "ConfigLang",
						"conditions" => array(
							"`ConfigLang`.`config_id`=`Config`.`id`",
							"`ConfigLang`.`lang`" => $lang
						)
				),
			)
		);
		$configs = $controller->Config->find('all', $params);
		$configs['language'] = $lang;

		// ******************************************************************************************
		// 自動ログイン
		// ******************************************************************************************
		if(!$user && $this->Common->autoLogin($configs)) {
			$user = $this->Auth->user();//認証済みユーザーを取得
			$user_id = isset($user['id']) ? intval($user['id']) : 0;
		}
		// ******************************************************************************************
		// タイムゾーン
		// ******************************************************************************************
		if($user_id == 0) {
			$configs['timezone_offset'] = $configs['default_TZ'];
		} else {
			$configs['timezone_offset'] = $user['timezone_offset'];
		}

		// ******************************************************************************************
		// default参加のhierarchyセット
		// ******************************************************************************************
		$conditions = array(
			'id' => array(
				$configs['default_entry_public_authority_id'],
				$configs['default_entry_myportal_authority_id'],
				$configs['default_entry_private_authority_id'],
				$configs['default_entry_group_authority_id']
			)
		);
		$params = array(
			'fields' => array(
								'Authority.id',
								'Authority.hierarchy'
			),
			'conditions' => $conditions
		);
		$authorities = $controller->Authority->find('all', $params);
		foreach($authorities as $authority) {
			if($authority['Authority']['id'] == $configs['default_entry_public_authority_id'])
				$configs['default_entry_public_hierarchy'] = $authority['Authority']['hierarchy'];
			if($authority['Authority']['id'] == $configs['default_entry_myportal_authority_id'])
				$configs['default_entry_myportal_hierarchy'] = $authority['Authority']['hierarchy'];
			if($authority['Authority']['id'] == $configs['default_entry_private_authority_id'])
				$configs['default_entry_private_hierarchy'] = $authority['Authority']['hierarchy'];
			if($authority['Authority']['id'] == $configs['default_entry_group_authority_id'])
				$configs['default_entry_group_hierarchy'] = $authority['Authority']['hierarchy'];
		}

		// ******************************************************************************************
		// ini_set
		// ******************************************************************************************
		ini_set('session.gc_maxlifetime', $configs['gc_maxlifetime'] * 60);
		// TODO:Session.timeoutで行うべき？
		////Configure::write("Session.timeout", $configs['gc_maxlifetime'] * 60);
		ini_set('memory_limit', $configs['memory_limit']);

		////$controller->set('nc_temp_name', $configs['temp_name']);

		Configure::write(NC_CONFIG_KEY, $configs);
		Configure::write('debug', $configs['debug']);

		//App::import('I18n', 'L10n');
		App::uses('L10n', 'I18n');
		$L10n = new L10n();
		$catalog = $L10n->catalog($configs['language']);
		Configure::write(NC_SYSTEM_KEY.'.locale', $catalog['locale']);

		// ******************************************************************************************
		// サイトClose
		// ******************************************************************************************
		/*if($configs['closesite'] == _ON) {
			if($user_id != 0  && $user['hierarchy'] < NC_AUTH_MIN_ADMIN) {
				// 強制ログアウト
				$this->Auth->logout();
				$user_id = 0;
			}
			if($user_id == 0 && !($controller->params['controller'] == 'users' && $controller->params['action'] == 'login')) {
				$controller->set('page_title', $configs['sitename']);
				$controller->set('message', $configs['closesite_text']);
				echo $controller->render(false, "closesite");
				exit;
			}
		}*/
	}

/**
 * ブラウザの指定言語を取得
 * @param  Controller $controller
 * @return string $used_language
 */
	protected function _getAcceptLang(Controller $controller)
	{
		$used_language = null;
		$lang_arr = array();
		$maximal_num = 0;
		$languages = $controller->Language->find('all');
		foreach($languages as $language) {
			$lang_arr[$language['Language']['language']] = $language['Language']['language'];
		}
		if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
			foreach (explode(",", $_SERVER["HTTP_ACCEPT_LANGUAGE"]) as $value) {
				$pri = explode(";", trim($value));
				$num = (isset($pri[1])) ? (float) preg_replace("/^q=/", "", $pri[1]) : 1;
				if($num > $maximal_num) {
					if(array_key_exists($pri[0], $lang_arr)) {
						$maximal_num = $num;
						$used_language = $lang_arr[$pri[0]];
					}else if(strpos($pri[0], '-') != false) {
						$pri_key = substr($pri[0], 0, strpos($pri[0], '-'));
						if(array_key_exists($pri_key , $lang_arr)) {
							$maximal_num = $num;
							$used_language = $lang_arr[$pri_key];
						}
					}
				}
			}
		}
		if(!$used_language) {
			$used_language = $languages[0]['language'];
		}
		return $used_language ;
	}
}