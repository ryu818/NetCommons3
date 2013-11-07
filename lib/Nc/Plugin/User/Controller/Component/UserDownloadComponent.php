<?php
/**
 * UserDownloadComponentクラス
 *
 * <pre>
 * 初期処理
 * ・DEBUG情報のセット
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       App.Controllers.Components
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class UserDownloadComponent extends Component {
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
		$User = ClassRegistry::init('User');
		$UserItem = ClassRegistry::init('UserItem');
		$UserItemAuthorityLink = ClassRegistry::init('UserItemAuthorityLink');

		$loginUser = $this->_controller->Auth->user();
		$loginUserId = isset($loginUser['id']) ? intval($loginUser['id']) : 0;
		if ($loginUserId == $fileOwnerId) {
			return true;
		}

		$fileOwner = $User->findById($fileOwnerId);
		$avatar = $UserItem->findByTagName('avatar');

		$publicFlags = $UserItemAuthorityLink->findIsPublicForLoginUser($fileOwner);
		if (!$publicFlags[$avatar['UserItem']['id']]) {
			return false;
		}

		return true;
	}

}