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
 * @param   int $fileName (uploadId)_($sizeType).(extension)
 * @return  void
 * @since   v 3.0.0.0
 */
	public function index($fileName) {
		$this->autoRender = false;
		
		if(!preg_match("/^([0-9]+)(_|-){0,1}(.*)\.(.*)/", $fileName, $matches)) {
			throw new NotFoundException(__('File not found.'));
		}
		$uploadId = intval($matches[1]);
		//$sizeType = $matches[3];
		//$extension = $matches[4];

		$upload = $this->Upload->findById($uploadId);
		if (empty($upload)) {
			throw new NotFoundException(__('File not found.'));
		}

		$loginUser = $this->Auth->user();
		$loginUserId = isset($loginUser['id']) ? intval($loginUser['id']) : 0;
		$isAdmin = ($this->Authority->getUserAuthorityId($loginUser['hierarchy']) == NC_AUTH_ADMIN_ID) ? true : false;

		$downloadPassword = isset($this->request->query['password']) ? $this->request->query['password'] : '';

		$hasDownloadAuth = false;
		if ($isAdmin || $loginUserId == $upload['Upload']['user_id']) {
			$hasDownloadAuth = true;
		} else if (!empty($upload['UploadLink'])) {
			foreach ($upload['UploadLink'] as $uploadLink) {
				if (!$this->checkComponent($uploadLink, $upload['Upload']['user_id'], $downloadPassword)) {
					continue;
				}
				$hasDownloadAuth = true;
				break;
			}
		}

		clearstatcache();

		if ($hasDownloadAuth) {
			$this->Download->flush($uploadId, $fileName);
		} else {
			throw new ForbiddenException(__('Forbidden permission to access the page.'));
		}
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
		if(!empty($uploadLink['check_component_action'])) {
			$checkComponentActionArr = explode(',', $uploadLink['check_component_action']);
		} else {
			$checkComponentActionArr = array();
		}

		foreach ($checkComponentActionArr as $checkComponentAction) {

			// 同一の条件の場合はチェックしない
			$hash = md5($checkComponentAction.'_'.$uploadLink['content_id'].'_'.$uploadLink['unique_id'].'_'.$uploadLink['model_name'].'_'.$uploadLink['field_name'].
				'_'.$uploadLink['access_hierarchy'].'_'.$uploadLink['download_password']);
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

/**
 * Tex数式画像出力処理
 * @return  void
 * @since   v 3.0.0.0
 */
	public function tex() {
		$this->autoRender = false;

		$tex = $this->request->query['tex'];
		$size = $this->request->query['size'];

		$mimetexPath = VENDORS . 'mimetex/';

		if (substr(PHP_OS, 0, 3) == 'WIN') {
			$mimetex = $mimetexPath . 'mimetex.exe';
		} else {
			$mimetex = $mimetexPath . 'mimetex.cgi';
		}

		switch ($size) {
			case 'Large':
			case 'large':
			case 'small':
				$size = '\\'.$size;
				break;
			case 'n':
				$size = '\\normalsize';
				break;
			default:
				$size = '\\Large';
		}

		if (function_exists('is_executable') && !is_executable($mimetex)) {
			return;
		} elseif (!file_exists($mimetex)) {
			return;
		}

		$this->response->header('Cache-Control', 'no-store, no-cache, must-revalidate');
		$this->response->header('Pragma', 'no-cache');
		$this->response->header('Content-Type', 'image/gif');
		$this->response->header('Content-Disposition', 'attachment; filename=\"'.md5($tex).'.gif\"');
		$this->response->header('Content-Transfer-Encoding', 'Binary');

		$tex = rawurldecode(str_replace('%_', '%', $tex));

		passthru($mimetex.' -d '. escapeshellarg($size.' '.$tex));

		return;
	}
}