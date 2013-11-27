<?php
/**
 * PageDownloadComponentクラス
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       App.Controllers.Components
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class PageDownloadComponent extends Component {
/**
 * Controller
 *
 * @var     object
 */
	protected $_controller = null;

/**
 * 初期化処理
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function startup(Controller $controller){
		$this->_controller = $controller;
	}

/**
 * ダウンロード権限チェック処理
 * @param   array $uploadLink
 * @param   int $fileOwnerId
 * @param   string $downloadPassword
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function check($uploadLink, $fileOwnerId, $downloadPassword=null) {
		$loginUser = $this->_controller->Auth->user();
		$loginUserId = isset($loginUser['id']) ? intval($loginUser['id']) : 0;
		$id = $uploadLink['unique_id'];

		$PageStyle = ClassRegistry::init('PageStyle');
		$pageStyle = $PageStyle->findById($id);

		if(!empty($pageStyle[$PageStyle->alias]['page_id'])) {
			$Page = ClassRegistry::init('Page');
			$page = $Page->findAuthById(intval($pageStyle[$PageStyle->alias]['page_id']), $loginUserId);
			if(!$page || $page['PageAuthority']['hierarchy'] < NC_AUTH_MIN_CHIEF) {
				return false;
			}
		} else {
			return true;
		}
		return true;
	}

}