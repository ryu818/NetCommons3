<?php
/**
 * AuthorityControllerクラス
 *
 * <pre>
 * 権限管理コントローラー
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       App.Controller
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class AuthorityController extends AuthorityAppController {
/**
 * Model name
 *
 * @var array
 */
	public $uses = array('AuthorityLang', 'PageUserLink', 'Authority.ModuleList', 'Authority.ModuleLinkList');

/**
 * Component name
 *
 * @var array
 */
	public $components = array('Security', 'CheckAuth' => array('allowAuth' => NC_AUTH_GUEST), 'Authority.AuthorityCommon');


/**
 * 表示前処理
 * <pre>
 * Tokenチェック処理
 * </pre>
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function beforeFilter() {
		parent::beforeFilter();

		$this->Security->validatePost = false;
	}

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
		$preLang = Configure::read(NC_CONFIG_KEY.'.'.'authority.preLanguage');
		if(isset($preLang)) {
			Configure::write(NC_CONFIG_KEY.'.'.'language', $preLang);
			$this->Session->write(NC_CONFIG_KEY.'.language', $preLang);
		}
	}

/**
 * 権限一覧
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function index() {
		$onRegist = isset($this->request->named['on_regist']) ? intval($this->request->named['on_regist']) : _OFF;

		// 言語切替
		$this->AuthorityCommon->setLanguage();
		$this->set('authorities', $this->Authority->findList('all'));
		$this->set('on_regist', $onRegist);
	}

/**
 * 権限追加・編集
 *
 * @param   integer $authorityId
 * @return  void
 * @since   v 3.0.0.0
 */
	public function edit($authorityId = null) {

		// 言語切替
		$this->AuthorityCommon->setLanguage();

		if($this->request->is('post')) {
			if (!isset($this->request->data['Authority'])) {
				throw new BadRequestException(__('Unauthorized request.<br />Please reload the page.'));
			}
			$authority = array('Authority' => $this->request->data['Authority']);
		} else if(!empty($authorityId)) {
			// 編集
			$authority = $this->Authority->findList('first',array('conditions' => array('Authority.id' => intval($authorityId))));
			if(!isset($authority['Authority'])) {
				$this->response->statusCode('400');
				$this->flash(__('Unauthorized request.<br />Please reload the page.'), '');
				return;
			}
			if(isset($authority['AuthorityLang']['name'])) {
				$authority['Authority']['default_name'] = $authority['AuthorityLang']['name'];
			} else {
				$authority['Authority']['default_name'] = $authority['Authority']['default_name'];
			}
		} else {
			$authority = $this->Authority->findDefault();
		}
		$this->AuthorityCommon->setHierarchy($authority);

		$onNext = isset($this->request->data['on_next']) ? intval($this->request->data['on_next']) : _OFF;
		if($this->request->is('post') && $onNext) {
			if(isset($this->request->data['Authority']['base_authority_id'])) {
				$ret = $this->Authority->getHierarchyByUserAuthorityId($this->request->data['Authority']['base_authority_id']);
				if(!isset($authority['Authority']['allow_creating_community']) ||
					(intval($this->request->data['Authority']['hierarchy']) < $ret[0] || intval($this->request->data['Authority']['hierarchy']) > $ret[1])) {
					$authority = $this->Authority->findDefault($this->request->data['Authority']['base_authority_id']);
				}

			}
			$authority['Authority']['default_name'] = $this->request->data['Authority']['default_name'];
			$this->Authority->set($authority);
			if($this->Authority->validates(array('fieldList' => array('default_name', 'hierarchy')))) {
				// TODO:同じControllerにsubmitし、権限名称のエラーチェックを行う。
				// その際、redirectしてしまうとPOSTの値を保持できないため、set_levelをrenderすることで実装。
				// requestActionでも同様の処理は可能。
				// 但し、pjaxで権限管理のような画面があった場合、urlがかわらない不具合が起こる可能性あり。
				$this->set_level($authorityId);
				$this->set('action', 'set_level');
				$this->set('authority', $authority);
				$this->render('set_level');
				//////$this->redirect(array('action' => 'set_level'));
				//echo $this->requestAction(array('action' => 'set_level', $authorityId), array('data' => $this->request->data, 'return'));
				//$this->render(false);
				return;
			} else if(!isset($authority['Authority']['base_authority_id'])) {
				$authority['Authority']['base_authority_id'] = $this->request->data['Authority']['base_authority_id'];
			}
		}
		$this->set('authority', $authority);
	}

/**
 * レベル設定
 * @param   integer $authorityId
 * @return  void
 * @since   v 3.0.0.0
 */
	public function set_level($authorityId = null) {
		// 言語切替
		$this->AuthorityCommon->setLanguage();
		$this->AuthorityCommon->setInit($authorityId);
	}

/**
 * 詳細設定
 * @param   integer $authorityId
 * @return  void
 * @since   v 3.0.0.0
 */
	public function detail($authorityId = null) {
		// 言語切替
		$this->AuthorityCommon->setLanguage();
		$authority = $this->AuthorityCommon->setInit($authorityId);
		$this->set('authorityDisabled', $this->AuthorityCommon->getDisabled($authority['Authority']['id'], $authority['Authority']['base_authority_id']));

		$this->AuthorityCommon->setMaxSizeOptions();
	}

/**
 * 詳細設定（その2）
 * @param   integer $authorityId
 * @return  void
 * @since   v 3.0.0.0
 */
	public function detail2($authorityId = null) {
		// 言語切替
		$this->AuthorityCommon->setLanguage();
		$authority = $this->AuthorityCommon->setInit($authorityId);
		$this->set('authorityDisabled', $this->AuthorityCommon->getDisabled($authority['Authority']['id'], $authority['Authority']['base_authority_id']));

		$this->_setDetail($authorityId, $authority);
	}

/**
 * 詳細情報セット
 * @param   integer $authorityId
 * @param   Model Authority $authority
 * @return  array
 * @since   v 3.0.0.0
 */
	protected function _setDetail($authorityId, $authority) {

		list($systemModules, $siteModules) = $this->AuthorityCommon->findSystemModuleLists($authorityId);

		$user = isset($systemModules['User']) ? $systemModules['User'] : $siteModules['User'];
		$systemModulesOptions = $this->AuthorityCommon->getSysModulesArray($authorityId, $authority['Authority']['base_authority_id']);

		if(!isset($this->request->data['ModuleSystemLink']) && isset($systemModulesOptions['checked']) && $authorityId > 0) {
			foreach($systemModulesOptions['checked'] as $key => $dirName) {
				if($dirName != 'All' && !isset($systemModules[$dirName]['ModuleSystemLink']['hierarchy']) &&
					!isset($siteModules[$dirName]['ModuleSystemLink']['hierarchy'])) {
					unset($systemModulesOptions['checked'][$key]);
				}
			}
		}

		$this->set('user', $user);
		$this->set('system_modules', $systemModules);
		$this->set('site_modules', $siteModules);
		$this->set('system_modules_options', $systemModulesOptions);
		return array($user, $systemModules, $siteModules, $systemModulesOptions);
	}

/**
 * 配置可能なモジュール
 * @param   integer $authorityId
 * @return  void
 * @since   v 3.0.0.0
 */
	public function usable_module($authorityId = null) {
		// 言語切替
		$this->AuthorityCommon->setLanguage();
		$this->AuthorityCommon->setInit($authorityId);

		$this->_setUsableModule($authorityId);
	}

/**
 * 配置可能なモジュール情報セット
 * @param   integer $authorityId
 * @return  void
 * @since   v 3.0.0.0
 */
	protected function _setUsableModule($authorityId) {
		$this->set('modules', $this->ModuleList->findGeneralModules());
		$this->set('myportal_enroll_modules', $this->AuthorityCommon->findModuleLists(NC_SPACE_TYPE_MYPORTAL, $authorityId));
		$this->set('private_enroll_modules', $this->AuthorityCommon->findModuleLists(NC_SPACE_TYPE_PRIVATE, $authorityId));
	}

/**
 * 登録確認
 * @param   integer $authorityId
 * @return  void
 * @since   v 3.0.0.0
 */
	public function confirm($authorityId = null) {
		$onRegist = isset($this->request->data['on_regist']) ? intval($this->request->data['on_regist']) : _OFF;

		// 言語切替
		$this->AuthorityCommon->setLanguage();
		$authority = $this->AuthorityCommon->setInit($authorityId);
		list($user, $systemModules, $siteModules, $systemModulesOptions) = $this->_setDetail($authorityId, $authority);
		$this->AuthorityCommon->setMaxSizeOptions();

		$this->_setUsableModule($authorityId);

		if($this->request->is('post') && $onRegist) {

			// 登録処理
			$lang = Configure::read(NC_CONFIG_KEY.'.'.'language');
			$created = empty($authorityId) ? true : false;
			if(!$created) {
				$preAuthority = $this->Authority->findById($authorityId);
			}
			// 新規追加か、langがenならば、default_nameを更新。
			$authorityName = $authority['Authority']['default_name'];
			if(!empty($authority['Authority']['id']) && $lang != 'en') {
				$authority['Authority']['default_name'] = $preAuthority['Authority']['default_name'];
			}
			unset($authority['Authority']['base_authority_id']);


			$this->Authority->set($authority);
			if(!$this->Authority->save($authority)) {
				throw new InternalErrorException(__('Failed to register the database, (%s).', 'authorities'));
			}
			$authorityId = $this->Authority->id;

			$params = array(
				'fields' => array(
					'AuthorityLang.id'
				),
				'conditions' => array(
					'AuthorityLang.authority_id' => $authorityId,
					'AuthorityLang.lang' => $lang
				)
			);
			$authorityLang = $this->AuthorityLang->find('first', $params);
			$authorityLang['AuthorityLang']['authority_id'] = $authorityId;
			$authorityLang['AuthorityLang']['lang'] = $lang;
			$authorityLang['AuthorityLang']['name'] = $authorityName;

			if(!$this->AuthorityLang->save($authorityLang)) {
				throw new InternalErrorException(__('Failed to register the database, (%s).', 'authority_langs'));
			}

			/**
			 * 管理系
			 */
			// すべて削除後にInsert
			$moduleSystemLinkModuleIds = $this->ModuleSystemLink->find('list', array(
				'fields' => array('ModuleSystemLink.module_id', 'ModuleSystemLink.id'),
				'conditions' => array('ModuleSystemLink.authority_id' => $authorityId)
			));
			$conditions = array(
				'ModuleSystemLink.authority_id' => $authorityId
			);
			$this->ModuleSystemLink->create();
			if(!$this->ModuleSystemLink->deleteAll($conditions)) {
				throw new InternalErrorException(__('Failed to delete the database, (%s).', 'module_links'));
			}
			$modules = array();
			foreach($siteModules as $dirName => $module) {
				$modules[$dirName] = $module;
			}
			foreach($systemModules as $dirName => $module) {
				$modules[$dirName] = $module;
			}
			$modules = array_merge($systemModules, $siteModules);
			foreach($modules as $dirName => $module) {
				if(count($systemModulesOptions['checked']) > 0 && $systemModulesOptions['checked'][0] == 'All' || in_array($dirName, $systemModulesOptions['checked'])) {
					$checked = true;
				} else {
					$checked = false;
				}
				if(!$checked) {
					continue;
				}
				$moduleId = $module['Module']['id'];

				if($dirName == 'User') {
					if($user['ModuleSystemLink']['hierarchy'] >= NC_AUTH_MIN_CHIEF) {
						$hierarchy = NC_AUTH_CHIEF;
					} else {
						$hierarchy = NC_AUTH_GENERAL;
					}
				} else {
					$hierarchy = NC_AUTH_CHIEF;
				}
				$dataModuleLink['ModuleSystemLink'] = array(
					'id' => isset($moduleSystemLinkModuleIds[$moduleId]) ? $moduleSystemLinkModuleIds[$moduleId] : null,
					'authority_id' => $authorityId,
					'module_id' => $moduleId,
					'hierarchy' => $hierarchy,
				);
				$this->ModuleSystemLink->create();
				if(!$this->ModuleSystemLink->save($dataModuleLink)) {
					throw new InternalErrorException(__('Failed to register the database, (%s).', 'module_links'));
				}
			}

			/**
			 * マイポータル
			 */
			if(isset($this->request->data['MyportalModuleLink'])) {
				// すべて削除後にInsert
				$conditions = array(
					'ModuleLink.room_id' => 0,
					'ModuleLink.space_type' => NC_SPACE_TYPE_MYPORTAL,
					'ModuleLink.authority_id' => $authorityId
				);
				$moduleLinkModuleIds = $this->ModuleLink->find('list', array(
					'fields' => array('ModuleLink.module_id', 'ModuleLink.id'),
					'conditions' => $conditions
				));
				$this->ModuleLink->create();
				if(!$this->ModuleLink->deleteAll($conditions)) {
					throw new InternalErrorException(__('Failed to delete the database, (%s).', 'module_links'));
				}
				foreach($this->request->data['MyportalModuleLink'] as $moduleId => $moduleLink) {
					$dataModuleLink['ModuleLink'] = array(
						'id' => isset($moduleLinkModuleIds[$moduleId]) ? $moduleLinkModuleIds[$moduleId] : null,
						'space_type' => NC_SPACE_TYPE_MYPORTAL,
						'authority_id' => $authorityId,
						'room_id' => 0,
						'module_id' => $moduleId
					);
					$this->ModuleLink->create();
					if(!$this->ModuleLink->save($dataModuleLink)) {
						throw new InternalErrorException(__('Failed to register the database, (%s).', 'module_links'));
					}
				}
			}

			if(isset($this->request->data['PrivateModuleLink'])) {
				// すべて削除後にInsert
				$conditions = array(
					'ModuleLink.room_id' => 0,
					'ModuleLink.space_type' => NC_SPACE_TYPE_PRIVATE,
					'ModuleLink.authority_id' => $authorityId
				);
				$moduleLinkModuleIds = $this->ModuleLink->find('list', array(
					'fields' => array('ModuleLink.module_id', 'ModuleLink.id'),
					'conditions' => $conditions
				));
				$this->ModuleLink->create();
				if(!$this->ModuleLink->deleteAll($conditions)) {
					throw new InternalErrorException(__('Failed to delete the database, (%s).', 'module_links'));
				}
				foreach($this->request->data['PrivateModuleLink'] as $moduleId => $moduleLink) {
					$dataModuleLink['ModuleLink'] = array(
						'id' => isset($moduleLinkModuleIds[$moduleId]) ? $moduleLinkModuleIds[$moduleId] : null,
						'space_type' => NC_SPACE_TYPE_PRIVATE,
						'authority_id' => $authorityId,
						'room_id' => 0,
						'module_id' => $moduleId
					);
					$this->ModuleLink->create();
					if(!$this->ModuleLink->save($dataModuleLink)) {
						throw new InternalErrorException(__('Failed to register the database, (%s).', 'module_links'));
					}
				}

			}
			if (!$created) {
				if($preAuthority['Authority']['display_participants_editing'] != _OFF && $authority['Authority']['display_participants_editing'] == _OFF) {
					// page_user_linksで該当データがあれば、authority_id更新
					// デフォルトの参加権限であっても、PageUserLinkに追加されてしまう。
					$userAuthorityId = $this->Authority->getUserAuthorityId($authority['Authority']['hierarchy']);
					$pageUserLinks = $this->PageUserLink->findAllByAuthorityId($authorityId);
					if(count($pageUserLinks) > 0) {
						$fields = array(
							'PageUserLink.authority_id' => $userAuthorityId
						);
						$conditions = array(
							"PageUserLink.authority_id" => $authorityId
						);
						if(!$this->PageUserLink->updateAll($fields, $conditions)) {
							throw new InternalErrorException(__('Failed to update the database, (%s).', 'page_user_links'));
						}
					}
				}
			}


			/**
			 * 「マイポータル、プライベートルームを使用する」がNC_DISPLAY_FLAG_ONからNC_DISPLAY_FLAG_OFFに変更されたら、マイポータル、プライベートルームのdisplay_flag=NC_DISPLAY_FLAG_DISABLEを立てる
			 * 「マイポータル、プライベートルームを使用する」がNC_DISPLAY_FLAG_OFFからNC_DISPLAY_FLAG_ONに変更されたら、マイポータル、プライベートルームにdisplay_flag=_ONを立てる
			 */
			if (!$created) {
				$privateDisplayFlag = null;
				$myportalDisplayFlag = null;
				if($preAuthority['Authority']['private_use_flag'] != _OFF && $authority['Authority']['private_use_flag'] == _OFF) {
					$privateDisplayFlag = NC_DISPLAY_FLAG_DISABLE;
				} else if($preAuthority['Authority']['private_use_flag'] == _OFF && $authority['Authority']['private_use_flag'] != _OFF) {
					$privateDisplayFlag = NC_DISPLAY_FLAG_ON;
				}
				if($preAuthority['Authority']['myportal_use_flag'] != _OFF && $authority['Authority']['myportal_use_flag'] == _OFF) {
					$myportalDisplayFlag = NC_DISPLAY_FLAG_DISABLE;
				} else if($preAuthority['Authority']['myportal_use_flag'] == _OFF && $authority['Authority']['myportal_use_flag'] != _OFF) {
					$myportalDisplayFlag = NC_DISPLAY_FLAG_ON;
				}
				if($privateDisplayFlag !== null) {
					$pageIds = $this->User->find('list', array(
						'fields' => array('private_page_id'),
						'conditions' => array('authority_id' => $authorityId)
					));

					if(count($pageIds) > 0) {
						$fields = array('Page.display_flag'=> $privateDisplayFlag);

						$conditions = array(
							"Page.root_id" => $pageIds
						);
						if(!$this->Page->updateAll($fields, $conditions)) {
							throw new InternalErrorException(__('Failed to update the database, (%s).', 'pages'));
						}
					}
				}
				// マイポータルのサブルームを作成できるため、更新するroom_idが子供のroom_idも更新する。->root_idより更新
				if($myportalDisplayFlag !== null) {
					$pageIds = $this->User->find('list', array(
						'fields' => array('myportal_page_id'),
						'conditions' => array('authority_id' => $authorityId)
					));
					if(count($pageIds) > 0) {
						$fields = array('Page.display_flag'=> $myportalDisplayFlag);

						$conditions = array(
							"Page.root_id" => $pageIds
						);
						if(!$this->Page->updateAll($fields, $conditions)) {
							throw new InternalErrorException(__('Failed to update the database, (%s).', 'pages'));
						}
					}
				}
			}

			if(empty($authorityId)) {
				$this->Session->setFlash(__('Has been successfully registered.'));
			} else {
				$this->Session->setFlash(__('Has been successfully updated.'));
			}
			$this->redirect(array('action' => 'index', 'on_regist' => $onRegist));
		}

		$this->set('user_authority_name', $this->Authority->getUserAuthorityName($authority['Authority']['hierarchy']));
		$this->set('myportal_viewing_user_authority_name', $this->Authority->getUserAuthorityName($authority['Authority']['allow_myportal_viewing_hierarchy']));
	}

/**
 * 権限削除
 * @param   integer $authorityId
 * @return  void
 * @since   v 3.0.0.0
 */
	public function delete($authorityId = null) {
		if(!$this->request->is('post') || empty($authorityId)) {
			throw new BadRequestException(__('Unauthorized request.<br />Please reload the page.'));
		}

		$authority = $this->Authority->findById($authorityId);
		if(!isset($authority['Authority']) || $authority['Authority']['system_flag'] == _ON) {
			$this->response->statusCode('400');
			$this->flash(__('Unauthorized request.<br />Please reload the page.'), '');
			return;
		}
		$userAuthorityId = $this->Authority->getUserAuthorityId($authority['Authority']['hierarchy']);

		$user = $this->User->findByAuthorityId($authorityId);
		if(isset($user['User'])) {
			$this->Session->setFlash(__d('authority', 'Fail to delete the selected module. <br />Please confirm whether the authority is used.'));
			$this->redirect(array('action' => 'index'));
			return;
		}

		// autoregist_authorが削除された権限になった場合、autoregist_author更新
		$config = $this->Config->findAllByName('autoregist_author');
		if($authorityId == $config['autoregist_author']['value']) {
			$fields = array(
				'Config.value' => $userAuthorityId
			);
			$conditions = array(
				"Config.id" => $config['autoregist_author']['id']
			);
			if(!$this->Config->updateAll($fields, $conditions)) {
				throw new InternalErrorException(__('Failed to update the database, (%s).', 'configs'));
			}
		}
		// page_user_linksで該当データがあれば、authority_id更新
		// デフォルトの参加権限であっても、PageUserLinkに追加されてしまう。
		$pageUserLinks = $this->PageUserLink->findAllByAuthorityId($authorityId);
		if(count($pageUserLinks) > 0) {
			$fields = array(
				'PageUserLink.authority_id' => $userAuthorityId
			);
			$conditions = array(
				"PageUserLink.authority_id" => $authorityId
			);
			if(!$this->PageUserLink->updateAll($fields, $conditions)) {
				throw new InternalErrorException(__('Failed to update the database, (%s).', 'page_user_links'));
			}
		}

		$conditions = array(
			'ModuleSystemLink.authority_id' => $authorityId
		);
		if(!$this->ModuleSystemLink->deleteAll($conditions)) {
			throw new InternalErrorException(__('Failed to delete the database, (%s).', 'module_system_links'));
		}

		$conditions = array(
			'ModuleLink.authority_id' => $authorityId
		);
		if(!$this->ModuleLink->deleteAll($conditions)) {
			throw new InternalErrorException(__('Failed to delete the database, (%s).', 'module_links'));
		}

		$conditions = array(
			'AuthorityLang.authority_id' => $authorityId
		);
		if(!$this->AuthorityLang->deleteAll($conditions)) {
			throw new InternalErrorException(__('Failed to delete the database, (%s).', 'authority_langs'));
		}

		if(!$this->Authority->delete($authorityId)) {
			throw new InternalErrorException(__('Failed to delete the database, (%s).', 'authorities'));
		}
		$this->Session->setFlash(__('Has been successfully deleted.'));
		$this->redirect(array('action' => 'index'));
	}
}