<?php
/**
 * PolicyControllerクラス
 *
 * <pre>
 * 個人情報管理コントローラー
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       App.Controller
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
App::uses('AppPluginController', 'Controller');
class PolicyController extends AppPluginController {

/**
 * Component name
 *
 * @var array
 */
	public $components = array('Security', 'CheckAuth' => array('allowAuth' => NC_AUTH_GUEST));

/**
 * Model name
 *
 * @var array
 */
	public $uses = array('UserItem', 'UserItemAuthorityLink');

/**
 * 表示前処理
 *
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function beforeFilter() {
		parent::beforeFilter();
		include_once dirname(dirname(__FILE__)).'/Config/defines.inc.php';
		$this->Security->validatePost = false;
		$items = $this->UserItem->findList();
		$userAuthorityId = NC_AUTH_OTHER_ID;
		switch($this->action) {
			case 'index':
				$userAuthorityId = NC_AUTH_ADMIN_ID;
				break;
			case 'chief':
				$userAuthorityId = NC_AUTH_CHIEF_ID;
				break;
			case 'moderate':
				$userAuthorityId = NC_AUTH_MODERATE_ID;
				break;
			case 'general':
				$userAuthorityId = NC_AUTH_GENERAL_ID;
				break;
			case 'guest':
				$userAuthorityId = NC_AUTH_GUEST_ID;
				break;
		}
		$type = isset($this->request->data['type']) ? $this->request->data['type'] : 'submit';
		if($this->request->is('post') && $type == 'submit' && is_array($this->request->data['UserItemAuthorityLink'])) {
			// 登録処理
			foreach($this->request->data['UserItemAuthorityLink'] as $itemId => $UserItemAuthorityLinks) {
				$fields = array();
				foreach($UserItemAuthorityLinks as $key => $value) {
					if($key != 'edit_lower_hierarchy' && $key != 'show_lower_hierarchy') {
						continue;
					}
					$fields['UserItemAuthorityLink.'. $key] = $value;
				}
				$conditions = array(
					"UserItemAuthorityLink.user_item_id" => $itemId,
					"UserItemAuthorityLink.user_authority_id" => $userAuthorityId
				);
				if(!$this->UserItemAuthorityLink->updateAll($fields, $conditions)) {
					throw new InternalErrorException(__('Failed to update the database, (%s).', 'user_item_authority_links'));
				}
			}
			$this->Session->setFlash(__('Has been successfully updated.'));
		}
		$params = array(
			'conditions' => array('user_authority_id' => $userAuthorityId)
		);
		$UserItemAuthorityLinks = $this->UserItemAuthorityLink->findList('all', $params);

		if($this->action != 'index') {
			$this->set('id', $this->id.'_'.$this->action);
		}
		$this->set('items', $items);
		$this->set('user_authority_id', $userAuthorityId);
		$this->set('user_item_authority_links', $UserItemAuthorityLinks[$userAuthorityId]);



	}

/**
 * 一覧表示(管理者)
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function index() {
		if ($this->request->is('post')) {
			$this->render('Elements/list');
		}
	}

/**
 * 一覧表示(主担)
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function chief() {
		$this->render('Elements/list');
	}

/**
 * 一覧表示(モデレーター)
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function moderate() {
		$this->render('Elements/list');
	}

/**
 * 一覧表示(一般)
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function general() {
		$this->render('Elements/list');
	}

/**
 * 一覧表示(ゲスト)
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function guest() {
		$this->render('Elements/list');
	}
}