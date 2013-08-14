<?php
/**
 * NcDownloadsControllerクラス
 *
 * <pre>
 * 共通ダウンロード用コントローラ
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       App.Controller
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */

App::uses('AppController', 'Controller');

/**
 * Static content controller
 *
 * Override this controller by placing a copy in controllers directory of an application
 *
 * @package       app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers/pages-controller.html
 */
class NcDownloadsController extends AppController {

/**
 * Model name
 *
 * @var array
 */
	public $uses = array('Upload', 'Authority');

/**
 * Component name
 *
 * @var array
 */
	public $components = array('Download', 'CheckAuth' => array('chkPlugin' => false, 'chkBlockId' => false));

/**
 * 確認済のチェックコンポーネント情報を以下の形式で保持する
 * 		成功の場合:ハッシュ値⇒true
 * 		失敗の場合:ハッシュ値⇒false
 *			ハッシュ値はUploadLink.check_component_actionの各アクション、
 *						UploadLink.content_id
 *						UploadLink.unique_id
 *						UploadLink.access_hierarchy
 *						UploadLink.download_password
 *							から生成
 * @var array
 */
	protected $_checkedActions = array();

/**
 * 共通ダウンロード処理
 * @param   int $uploadId
 * @param   string $sizeType
 * 				$sizeTypeが渡された場合、[$uploadId]-[$sizeType].拡張子のファイルをダウンロードする
 * 				$sizeTypeが渡されない場合、[$uploadId].拡張子のファイルをダウンロードする
 * @return  void
 * @since   v 3.0.0.0
 */
	public function index($uploadId, $sizeType='') {
		$this->autoRender = false;

		$upload = $this->Upload->findById($uploadId);
		if (empty($upload)) {
			exit;
		}

		$loginUser = $this->Auth->user();
		$loginUserId = isset($loginUser['id']) ? intval($loginUser['id']) : 0;
		$isAdmin = ($this->Authority->getUserAuthorityId($loginUser['hierarchy']) == NC_AUTH_ADMIN_ID) ? true : false;

		$downloadPassword = isset($this->request->query['download_password']) ? $this->request->query['download_password'] : '';

		$hasDownloadAuth = false;
		if (!empty($upload['UploadLink'])) {
			foreach ($upload['UploadLink'] as $uploadLink) {
				if (!$this->checkComponent($uploadLink, $upload['Upload']['user_id'], $downloadPassword)) {
					continue;
				}
				$hasDownloadAuth = true;
				break;
			}
		} elseif ($isAdmin || $loginUserId == $upload['Upload']['user_id']) {
			$hasDownloadAuth = true;
		}

		clearstatcache();

		// 管理者で閲覧権限がない場合は別の画像を表示する
		if ($isAdmin && !$hasDownloadAuth) {
			$this->response->file(App::pluginPath('Upload').'webroot/img/image.png');
		} elseif ($hasDownloadAuth) {
			$this->Download->flush($uploadId, $sizeType);
		}

		return;
	}

/**
 * コンポーネントチェック処理
 * @param   string $checkComponentAction
 * 				Download または 配列をシリアライズされた値
 * @param   int $fileOwnerId ファイル所有者のユーザーID
 * @param   string $downloadPassword ダウンロードパスワード
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function checkComponent($uploadLink, $fileOwnerId, $downloadPassword) {
		if (preg_match('/^a:/',$uploadLink['check_component_action'])) {
			$checkComponentActionArr = unserialize($uploadLink['check_component_action']);
		} else {
			$checkComponentActionArr = array($uploadLink['check_component_action']);
		}

		foreach ($checkComponentActionArr as $checkComponentAction) {

			// 同一の条件の場合はチェックしない
			$hash = md5($checkComponentAction.$uploadLink['content_id'].$uploadLink['unique_id'].$uploadLink['access_hierarchy'].$uploadLink['download_password']);
			if (isset($this->_checkedActions[$hash])) {
				if ($this->_checkedActions[$hash] === true) {
					continue;
				}
				return false;
			}

			$parts = explode('.', $checkComponentAction);
			$partsCount = count($parts);
			switch ($partsCount) {
				case 1:
					$component = $parts[0];
					$action = 'check';
					break;
				case 2:
					$component = $parts[0].'.'.$parts[1];
					$action = 'check';
					break;
				case 3:
					$component = $parts[0].'.'.$parts[1];
					$action = $parts[2];
					break;
				default:
					// 不正な形式のためエラー
					return false;
			}

			$loadedComponent = $this->Components->load($component);
			$loadedComponent->startup($this);
			if (!$loadedComponent->$action($uploadLink, $fileOwnerId, $downloadPassword)) {
				$this->_checkedActions[$hash] = false;
				return false;
			}
			$this->_checkedActions[$hash] = true;
		}
		return true;
	}
}
