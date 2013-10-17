<?php
/**
 * SetConfigComponentクラス
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
class SetConfigComponent extends Component {
/**
 * Controller
 *
 * @var     object
 */
	protected $_controller = null;

/**
 * Other components utilized by Component
 *
 * @var     array
 */
	public $components = array('Auth', 'Session', 'Common');		// , 'Init', 'Auth'

/**
 * Constructor
 *
 * @param ComponentCollection $collection A ComponentCollection this component can use to lazy load its components
 * @param array $settings Array of configuration settings.
 */
	public function __construct(ComponentCollection $collection, $settings = array()) {
		$this->_controller = $collection->getController();
		parent::__construct($collection, $settings);
	}

/**
 * Config値セット
 *
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function set() {
		$controller = $this->_controller;

		/*
		 * 同じ処理を何度も実行させないため
		 */
		$sitename = Configure::read(NC_CONFIG_KEY.'.'.'sitename');
		if(isset($sitename)) {
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
			}
		}
		$sessLang = $this->Session->read(NC_CONFIG_KEY.'.'.'language');
		if(empty($lang)) {
			$lang = $sessLang;
			if(empty($lang)) {
				//システム管理のシステム標準使用言語の「自動」で選択している場合、自動で判断する。
				if(empty($config_language)) {
					$lang = $this->_getAcceptLang($controller);
				} else {
					$lang = $config_language;
				}
			}
		}
		if(!empty($lang) && $lang != $sessLang) {
			$this->Session->write(NC_CONFIG_KEY.'.'.'language', $lang);
		}

		// ******************************************************************************************
		// Config
		// ******************************************************************************************
		$configs = $controller->Config->findList('list', 0, array(NC_SYSTEM_CATID, NC_LOGIN_CATID, NC_SERVER_CATID, NC_STYLE_CATID, NC_DEVELOPMENT_CATID), $lang);
		$configs['language'] = $lang;

		// ******************************************************************************************
		// タイムゾーン
		// ******************************************************************************************
		$user = $this->Auth->user();//認証済みユーザーを取得
		if(isset($user['id'])) {
			$configs['timezone_offset'] = $user['timezone_offset'];
		} else {
			$configs['timezone_offset'] = $configs['default_TZ'];
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
		// メンテナンスモード
		// ******************************************************************************************
		if($configs['is_maintenance'] == _ON) {
			if($user['id'] != 0  && $user['hierarchy'] < NC_AUTH_MIN_ADMIN) {
				// 強制ログアウト
				$this->Auth->logout();
				$user['id'] = 0;
			}
			if($user['id'] == 0 && !($controller->params['controller'] == 'users' && $controller->params['action'] == 'login')) {
				$controller->set('page_title', $configs['sitename']);
				$controller->set('message', $configs['maintenance_text']);
				echo $controller->render(false, 'maintenance');
				exit;
			}
		}
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