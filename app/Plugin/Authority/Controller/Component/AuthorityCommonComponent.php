<?php
/**
 * AuthorityCommonComponentクラス
 *
 * <pre>
 * 権限管理共通コンポーネント
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Plugin.Blog.Component
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class AuthorityCommonComponent extends Component {
/**
 * _controller
 *
 * @var Controller
 */
	protected $_controller = null;

/**
 * startup
 *
 * @param Controller $controller
 */
	public function startup(Controller $controller) {
		$this->_controller = $controller;
	}

/**
 * 単位付きのサイズに変換し返す
 * @param float $size(size)
 * @param integer $precision(小数点いくつまで表示するか（default:小数点１位まで）)
 * @return string
 * @since   v 3.0.0.0
 */
	public function formatSize($size, $precision=1) {
		$UnitArray = array("", "K", "M", "G", "T");

		$Byte = 1024;
		foreach ($UnitArray as $val) {
			if ($size < $Byte) break;
			$size = $size / $Byte;
		}

		if ($size < 100 && $val != $UnitArray[0]) {
			return round($size, $precision). $val;
		} else {
			return round($size). $val;
		}
	}

/**
 * リクエスト情報セット処理
 * @param   integer $authorityId
 * @return  Model Authority $autty
 * @since   v 3.0.0.0
 */
	public function setInit($authorityId = null) {
		$authorityId = !isset($authorityId) ? 0: $authorityId;
		if (!isset($this->_controller->request->data['Authority']) || $this->_controller->request->data['Authority']['id'] != $authorityId) {
			$this->_controller->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'Authority.setInit.001');
			return;
		}
		$authority = array('Authority' => $this->_controller->request->data['Authority']);
		$this->setHierarchy($authority);

		$this->_controller->set('authority', $authority);
		return $authority;
	}

/**
 * hierarchyの値を再セット
 * @param   Model Authority
 * @return  void
 * @since   v 3.0.0.0
 */
	public function setHierarchy(&$authority) {
		if(isset($authority['Authority']['base_authority_id'])) {
			$hierarchyArr = $this->_controller->Authority->getHierarchyByUserAuthorityId($authority['Authority']['base_authority_id']);
			if(intval($authority['Authority']['hierarchy']) < $hierarchyArr[0] || intval($authority['Authority']['hierarchy']) >= $hierarchyArr[1]) {
				// ベースが変更されていれば、hierarchyをセットしなおす
				$hierarchy = $hierarchyArr[0];
				$interlevel = $hierarchyArr[1] - $hierarchyArr[0];
				if($interlevel > 0) {
					$hierarchy += floor($interlevel/2);
				}
				$authority['Authority']['hierarchy'] = $hierarchy;
			}
		} else {
			$hierarchyArr = $this->_controller->Authority->getHierarchyByUserAuthorityId($this->_controller->Authority->getUserAuthorityId($authority['Authority']['hierarchy']));
		}
		if(isset($authority['Authority']['add_hierarchy'])) {
			$authority['Authority']['hierarchy'] = $hierarchyArr[0] - 1 + intval($authority['Authority']['add_hierarchy']);
		}
		if(!isset($authority['Authority']['base_authority_id'])) {
			$authority['Authority']['base_authority_id'] = $this->_controller->Authority->getUserAuthorityId($authority['Authority']['hierarchy']);
		}
	}

/**
 * 言語切替
 * @param   string $renderElement
 * @return  string
 * @since   v 3.0.0.0
 */
	public function setLanguage($renderElement = null) {
		$activeLang = isset($this->_controller->request->named['language']) ? $this->_controller->request->named['language'] : null;
		if(isset($this->_controller->request->data['activeLang'])) {
			$activeLang = $this->_controller->request->data['activeLang'];
		}
		if($activeLang == '') {
			$activeLang = null;
		}

		$preLang = Configure::read(NC_CONFIG_KEY.'.'.'authority.preLanguage');
		if(isset($preLang)) {
			return $activeLang;
		}
		$languages = $this->_controller->Language->findSelectList();
		$this->_controller->set("language", $activeLang);
		$this->_controller->set("languages", $languages);
		if(isset($activeLang) && isset($languages[$activeLang])) {
			Configure::write(NC_CONFIG_KEY.'.'.'authority.preLanguage', Configure::read(NC_CONFIG_KEY.'.'.'language'));

			Configure::write(NC_CONFIG_KEY.'.'.'language', $activeLang);
			$this->_controller->Session->write(NC_CONFIG_KEY.'.language', $activeLang);
			if(!empty($renderElement)) {
				$this->_controller->render($renderElement);
			}
		}
		return $activeLang;
	}

/**
 * 権限詳細画面　変更不可情報セット処理
 * @param   integer baseAuthorityId
 * @return  Model Authority
 * @since   v 3.0.0.0
 */
	public function getDisabled($baseAuthorityId = NC_AUTH_GENERAL_ID) {

		$index = $baseAuthorityId - 1;

		$disabledCreatingCommunityArr = explode('|', AUTHORITY_ALLOW_CREATING_COMMUNITY_DISABLED);
		$disabledNewParticipantArr = explode('|', AUTHORITY_ALLOW_NEW_PARTICIPANT_DISABLED);
		$disabledMyportalUseFlagArr = explode('|', AUTHORITY_MYPORTAL_USE_FLAG_DISABLED);
		$disabledAllowMyportalViewingHierarchyArr = explode('|', AUTHORITY_MYPORTAL_VIEWING_HIERARCHY_DISABLED);
		$disabledPrivateUseFlagArr = explode('|', AUTHORITY_PRIVATE_USE_FLAG_DISABLED);
		$disabledPublicCreateroomFlagArr = explode('|', AUTHORITY_PUBLIC_CREATEROOM_FLAG_DISABLED);
		$disabledGroupCreateroomFlagArr = explode('|', AUTHORITY_GROUP_CREATEROOM_FLAG_DISABLED);
		$disabledMyportalCreateroomFlagArr = explode('|', AUTHORITY_MYPORTAL_CREATEROOM_FLAG_DISABLED);
		$disabledPrivateCreateroomFlagArr = explode('|', AUTHORITY_PRIVATE_CREATEROOM_FLAG_DISABLED);
		$disabledAllowHtmltagFlagArr = explode('|', AUTHORITY_ALLOW_HTMLTAG_FLAG_DISABLED);
		$disabledAllowLayoutFlagArr = explode('|', AUTHORITY_ALLOW_LAYOUT_FLAG_DISABLED);
		$disabledAllowAttachmentArr = explode('|', AUTHORITY_ALLOW_ATTACHMENT_DISABLED);
		$disabledAllowVideoArr = explode('|', AUTHORITY_ALLOW_VIDEO_DISABLED);
		$disabledChangeLeftcolumnFlagArr = explode('|', AUTHORITY_CHANGE_LEFTCOLUMN_FLAG_DISABLED);
		$disabledChangeRightcolumnFlagArr = explode('|', AUTHORITY_CHANGE_RIGHTCOLUMN_FLAG_DISABLED);
		$disabledChangeHeadercolumnFlagArr = explode('|', AUTHORITY_CHANGE_HEADERCOLUMN_FLAG_DISABLED);
		$disabledChangeFootercolumnFlagArr = explode('|', AUTHORITY_CHANGE_FOOTERCOLUMN_FLAG_DISABLED);

		$ret = array(
			'Authority' => array(
				'allow_creating_community' => $disabledCreatingCommunityArr[$index],
				'allow_new_participant' => $disabledNewParticipantArr[$index],
				'myportal_use_flag' => $disabledMyportalUseFlagArr[$index],
				'allow_myportal_viewing_hierarchy' => $disabledAllowMyportalViewingHierarchyArr[$index],
				'private_use_flag' => $disabledPrivateUseFlagArr[$index],
				'public_createroom_flag' => $disabledPublicCreateroomFlagArr[$index],
				'group_createroom_flag' => $disabledGroupCreateroomFlagArr[$index],
				'myportal_createroom_flag' => $disabledMyportalCreateroomFlagArr[$index],
				'private_createroom_flag' => $disabledPrivateCreateroomFlagArr[$index],
				'allow_htmltag_flag' => $disabledAllowHtmltagFlagArr[$index],
				'allow_layout_flag' => $disabledAllowLayoutFlagArr[$index],
				'allow_attachment' => $disabledAllowAttachmentArr[$index],
				'allow_video' => $disabledAllowVideoArr[$index],
				'change_leftcolumn_flag' => $disabledChangeLeftcolumnFlagArr[$index],
				'change_rightcolumn_flag' => $disabledChangeRightcolumnFlagArr[$index],
				'change_headercolumn_flag' => $disabledChangeHeadercolumnFlagArr[$index],
				'change_footercolumn_flag' => $disabledChangeFootercolumnFlagArr[$index]
			)
		);

		return $ret;
	}

/**
 * 一般モジュールの配列取得-リクエストセット
 * @param   integer    $spaceType
 * @param   integer    $authorityId
 * @return  array
 * @since   v 3.0.0.0
 */
	public function findModuleLists($spaceType, $authorityId) {
		if($spaceType == NC_SPACE_TYPE_MYPORTAL && isset($this->_controller->request->data['MyportalModuleLink'])) {
			$moduleLinks = $this->_controller->request->data['MyportalModuleLink'];
		} else if($spaceType == NC_SPACE_TYPE_PRIVATE && isset($this->_controller->request->data['PrivateModuleLink'])) {
			$moduleLinks = $this->_controller->request->data['PrivateModuleLink'];
		}
		if(isset($moduleLinks)) {
			$enrollModules = array();
			foreach($moduleLinks as $moduleId => $moduleLink) {
				$enrollModules[] = $moduleId;
			}
		} else if($spaceType == NC_SPACE_TYPE_MYPORTAL && isset($this->_controller->request->data['MyportalModuleLink'])) {
			$enrollModules = $this->_controller->request->data['MyportalModuleLink'];
		} else if($spaceType == NC_SPACE_TYPE_PRIVATE && isset($this->_controller->request->data['PrivateModuleLink'])) {
			$enrollModules = $this->_controller->request->data['PrivateModuleLink'];
		} else if($authorityId > 0) {
			$enrollModules = $this->_controller->ModuleLinkList->findModulelinks($spaceType, $authorityId);
		} else {
			// 新規権限ならばパブリックからデフォルトモジュールを取得
			$enrollModules = $this->_controller->ModuleLinkList->findModulelinks(NC_SPACE_TYPE_PUBLIC, 0);
		}
		return $enrollModules;
	}

/**
 * システムコントロールモジュールの選択・サイト運営モジュールの選択取得-リクエストセット
 * @param   integer    $authorityId
 * @return  array
 * @since   v 3.0.0.0
 */
	public function findSystemModuleLists($authorityId) {
		$modules = $this->_controller->Module->findSystemModule($authorityId, "LEFT");
		$systemModules = array();
		$siteModules = array();
		$systemModulesDir = explode("|", AUTHORITY_SYSTEM_CONTROL_MODULES);
		foreach($modules as $module) {
			if(isset($this->_controller->request->data['ModuleSystemLink'][$module['Module']['id']]['hierarchy'])) {
				$module['ModuleSystemLink']['hierarchy'] = $this->_controller->request->data['ModuleSystemLink'][$module['Module']['id']]['hierarchy'];
			}
			if(in_array($module['Module']['dir_name'], $systemModulesDir)) {
				$systemModules[$module['Module']['dir_name']] = $module;
			} else {
				$siteModules[$module['Module']['dir_name']] = $module;
			}
		}
		return array($systemModules, $siteModules);
	}

/**
 * システムモジュールのデフォルト値、変更可能かどうかの配列取得
 * @param      $rid
 * @param  integer    $baseAuthorityId
 * @return array
 */
	public function getSysModulesArray($id, $baseAuthorityId) {
		$rets = array();
		switch($baseAuthorityId) {
			case NC_AUTH_ADMIN_ID:
				if($id == AUTHORITY_SYSTEM_ADMIN_ID) {
					$defDefaultName = AUTHORITY_SYSTEM_MODULES_SYSADMIN_DEFAULT;
					$defEnabledName = AUTHORITY_SYSTEM_MODULES_SYSADMIN_ENABLED;
				} else {
					$defDefaultName = AUTHORITY_SYSTEM_MODULES_ADMIN_DEFAULT;
					$defEnabledName = AUTHORITY_SYSTEM_MODULES_ADMIN_ENABLED;
				}
				break;
			case NC_AUTH_CHIEF_ID:
				$defDefaultName = AUTHORITY_SYSTEM_MODULES_CHIEF_DEFAULT;
				$defEnabledName = AUTHORITY_SYSTEM_MODULES_CHIEF_ENABLED;
				break;
			case NC_AUTH_MODERATE_ID:
				$defDefaultName = AUTHORITY_SYSTEM_MODULES_MODERATE_DEFAULT;
				$defEnabledName = AUTHORITY_SYSTEM_MODULES_MODERATE_ENABLED;
				break;
			case NC_AUTH_GENERAL_ID:
				$defDefaultName = AUTHORITY_SYSTEM_MODULES_GENERAL_DEFAULT;
				$defEnabledName = AUTHORITY_SYSTEM_MODULES_GENERAL_ENABLED;
				break;
			default:
				$defDefaultName = AUTHORITY_SYSTEM_MODULES_GUEST_DEFAULT;
				$defEnabledName = AUTHORITY_SYSTEM_MODULES_GUEST_ENABLED;
				break;
		}

		$rets['enabled'] = explode("|", $defEnabledName);
		$isRequestCheck = false;
		if(isset($this->_controller->request->data['ModuleSystemLink'])) {
			$rets['checked'] = array();
			foreach($this->_controller->request->data['ModuleSystemLink'] as $moduleSystemLink) {
				if(isset($moduleSystemLink['dir_name'])) {
					$isRequestCheck = true;
					$rets['checked'][] = $moduleSystemLink['dir_name'];
				}
			}
			$bufChecked = explode("|", $defDefaultName);
			foreach($bufChecked as $dirName) {
				if($dirName == '' || $dirName == 'All') {
					continue;
				}
				if(!in_array($dirName, $rets['enabled'])) {
					// 使用不可で、デフォルトがチェック
					$rets['checked'][] = $dirName;
				}
			}
		}
		if(!$isRequestCheck) {
			$rets['checked'] = explode("|", $defDefaultName);
		}
		return $rets;
	}

	public function setMaxSizeOptions() {
		$maxSizeOptions = array();
		$maxSizeOptionsArr = explode("|", AUTHORITY_MAX_SIZE_LIST);
		foreach($maxSizeOptionsArr as $list) {
			$maxSizeOptions[$list] = $this->formatSize($list);
		}
		$maxSizeOptions['0'] = __d('authority', 'Unlimited');
		$this->_controller->set('max_size_options', $maxSizeOptions);
	}
}