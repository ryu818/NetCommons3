<?php
/**
 * SystemControllerクラス
 *
 * <pre>
 * システム管理コントローラー
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       App.Controller
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class SystemController extends SystemAppController {
/**
 * Model name
 *
 * @var array
 */
	public $uses = array('ConfigLang', 'System.ConfigRegist', 'UserItem');

/**
 * Component name
 *
 * @var array
 */
	public $components = array('Security', 'System.SystemCommon', 'CheckAuth' => array('allowAuth' => NC_AUTH_GUEST));

/**
 * 表示後処理
 * <pre>
 * 	セッションにセットしてあった言語を元に戻す。
 * </pre>
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function afterFilter()
	{
		parent::afterFilter();
		$preLang = Configure::read(NC_CONFIG_KEY.'.'.'system.preLanguage');
		if(isset($preLang)) {
			Configure::write(NC_CONFIG_KEY.'.'.'language', $preLang);
			$this->Session->write(NC_CONFIG_KEY.'.language', $preLang);
		}
	}

/**
 * 一般設定
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function index() {
		$this->_actionCommon(array(NC_SYSTEM_CATID, NC_LOGIN_CATID));
		// 標準の開始ページ
		$communities = $this->community_list();
		$isCommunity = (is_array($communities) && count($communities) > 0) ? true : false;
		$this->set('pages', $this->ConfigRegist->findDefaultStartPage($isCommunity));

		if($this->request->is('post') || isset($this->request->named['language'])) {
			$this->render('Elements/general');
		}
	}

/**
 * 標準の開始ページ - コミュニティー一覧取得
 * @param   void
 * @return  Model Pages
 * @since   v 3.0.0.0
 */
	public function community_list() {
		$page = !empty($this->request->named['page']) ? intval($this->request->named['page']) : 1;
		$limit = !empty($this->request->named['limit']) ? intval($this->request->named['limit']) : SYSTEM_COMMUNITY_LIMIT_DEFAULT;

		$config = $this->Config->find('all', array(
			'fields' => 'Config.name, Config.value',
			'conditions' => array(
				'Config.module_id' => 0,
				'Config.name' => 'first_startpage_id'
			),
		));
		$pageId = intval($config['first_startpage_id']);
		if (isset($this->request->data['ConfigRegist']) && $this->request->data['ConfigRegist']['first_startpage_id'] == -3) {
			$pageId = intval($this->request->data['ConfigRegist']['first_startcommunity_id']);
		}
		if($pageId > 0) {
			$firstPage = $this->Page->findById($pageId);
			if(isset($firstPage['Page'])) {
				if(empty($this->request->named['page']) && $firstPage['Page']['display_sequence'] > 0) {
					$page = ceil($firstPage['Page']['display_sequence']/$limit);
				}
				$this->set('first_startpage', $firstPage);
				if($firstPage['Page']['space_type'] == NC_SPACE_TYPE_GROUP) {
					$this->set('isCommunity', true);
				}
			}
		}

		list($hasNext, $communities) = $this->ConfigRegist->findDefaultStartPageGroup($limit, $page);
		$this->set('hasNext', $hasNext);
		$this->set('page', $page);
		$this->set('communities', $communities);
		$this->set('first_startcommunity_id', $pageId);

		if($this->action == 'community_list') {
			$id = $this->id.'-index';
			$this->set('id', $id);
			$this->render('Elements/community_list');
		}
		return $communities;
	}

/**
 * ログインとログアウト
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function login_logout() {
		$this->_actionCommon(array(NC_SYSTEM_CATID, NC_LOGIN_CATID, NC_SERVER_CATID));
	}

/**
 * サイトの閉鎖、クローズドサイト
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function closed() {
		$this->_actionCommon(array(NC_SYSTEM_CATID, NC_LOGIN_CATID));
	}

/**
 * サーバー設定
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function server() {
		$this->_actionCommon(NC_SERVER_CATID);
	}

/**
 * メール設定
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function mail() {
		$this->_actionCommon(NC_MAIL_CATID);
	}

/**
 * メタ情報
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function meta() {
		$this->_actionCommon(NC_META_CATID);
	}

/**
 * 表示設定
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function display() {
		$this->_actionCommon(NC_STYLE_CATID);
	}

/**
 * モジュール設定
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function module() {
		$isError = false;
		if($this->request->is('post') && isset($this->request->data['Module'])) {
			$operationModules = $this->request->data['Module'];
			foreach ($operationModules as $moduleId => $checkMap) {
				$data = array('id' => $moduleId);
				foreach ($checkMap as $key => $check) {
					$key = str_replace('_modules', '', $key);
					$data[$key] = ($check == _ON) ? 'enabled' : 'enable';
				}
				if(!$this->Module->save($data)) {
					$isError = true;
				}
			}
		}

		if ($isError) {
			$this->ConfigRegist->invalidate('modules_operation', __('Failed to update the database, (%s).', 'Module'));
		}

		$this->_actionCommon(NC_MODULE_CATID, !$isError);

		$params = array(
			'conditions' => array(
				'Module.system_flag' => _OFF,
				'Module.disposition_flag' => _ON
			),
			'order' => array('Module.display_sequence' => 'ASC')
		);

		$this->set('modules_operation', $this->Module->find('all', $params));
	}

/**
 * 入会退会設定
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function membership() {
		$this->_actionCommon(NC_MEMBERSHIP_CATID);
	}

/**
 * 自動登録設定
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function autoregist() {

		$isError = false;
		if($this->request->is('post') && isset($this->request->data['UserItem'])) {
			$dataItems = $this->request->data['UserItem'];
			foreach ($dataItems as $itemId => $dataItem) {
				$dataItem['id'] = $itemId;
				if(!$this->UserItem->save($dataItem)) {
					$isError = true;
				}
			}
		}

		if ($isError) {
			$this->ConfigRegist->invalidate('autoregist_use_items', __('Failed to update the database, (%s).', 'user_items'));
		}
		$this->_actionCommon(NC_MEMBERSHIP_CATID, !$isError);

		$this->set('autoregist_author', Configure::read(NC_CONFIG_KEY.'.'.'languages'));
		$conditions = array('autoregist_use != ' => 'disabled');
		$this->set('autoregist_use_items', $this->UserItem->findList('all', $conditions));

	}

/**
 * コミュニティー設定
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function community() {
		$this->_actionCommon(NC_COMMUNITY_CATID);
	}


/**
 * 開発者向け
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function developper() {
		$this->_actionCommon(NC_DEVELOPMENT_CATID);
	}

/**
 * Action共通
 * @param   string|array $catId
 * @return  void
 * @since   v 3.0.0.0
 */
	protected function _actionCommon($catId, $doSave=true) {
		$requestConfigs = isset($this->request->data['ConfigRegist']) ? $this->request->data['ConfigRegist'] : array();
		$id = $this->id.'-'.$this->action;

		// 言語切替
		$this->SystemCommon->setLanguage();

		$configs = $this->Config->findList('all', 0, $catId, null, true);
		$this->ConfigRegist->convertConfig($id, $configs, $requestConfigs);
		if($this->request->is('post') && $doSave) {
			// 登録処理
			if($this->ConfigRegist->saveValues($configs, $requestConfigs)) {
				$this->Session->setFlash(__('Has been successfully registered.'));
			}
		}
		$this->set('dialog_id', $this->id);
		$this->set('id', $id);
		$this->set('configs', $configs);
	}
}