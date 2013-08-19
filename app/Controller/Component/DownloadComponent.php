<?php
/**
 * DownloadComponentクラス
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
class DownloadComponent extends Component {
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
		$Page = ClassRegistry::init('Page');

		$loginUser = $this->_controller->Auth->user();
		$loginUserId = isset($loginUser['id']) ? intval($loginUser['id']) : 0;
		$loginUserHierarchy = isset($loginUser['hierarchy']) ? intval($loginUser['hierarchy']) : 0;

		// 自分自身がアップロードしたファイルは常に許可
		if ($loginUserId == $fileOwnerId) {
			return true;
		}

		$addParams = array(
			'conditions' => array(
				'Content.id' => $uploadLink['content_id']
			),
			'joins' => array(
				array(
					'type'=>'INNER',
					'table'=>'contents',
					'alias'=>'Content',
					'conditions'=>'`Page`.`room_id`=`Content`.`room_id`'
				)
			)
		);
		$rooms = $Page->findViewableRoom('first', $loginUserId, $addParams);
		if (empty($rooms)) {
			return false;
		}

		if ($uploadLink['access_hierarchy'] > $loginUserHierarchy) {
			return false;
		}

		if (!empty($uploadLink['download_password'])
			&& $uploadLink['download_password'] != $downloadPassword) {
			return false;
		}

		return true;
	}

/**
 * ファイル内容出力処理
 * @param   int $uploadId
 * @param   string $sizeType
 * @return  void
 * @since   v 3.0.0.0
 */
	public function flush($uploadId, $sizeType='') {

		$upload = $this->_controller->Upload->findById($uploadId);

		$fileName = $upload['Upload']['file_name'];
		$filePath = $upload['Upload']['file_path'];
		$mimetype = $upload['Upload']['mimetype'];
		$plugin = $upload['Upload']['plugin'].DS;
		if (empty($sizeType)) {
			$physicalFileName = $upload['Upload']['physical_file_name'];
		} else {
			$physicalFileName = $upload['Upload']['id'].'-'.$sizeType.'.'.$upload['Upload']['extension'];
		}
		$physicalFilePath = NC_UPLOADS_DIR.$plugin.$filePath.$physicalFileName;

		if (file_exists($physicalFilePath)) {
			$isUseCache = $this->isUseCache($upload['Upload']['id']);
			$this->_headerOutput($fileName, $physicalFilePath, $mimetype, $isUseCache);
			if ($this->_controller->response->statusCode() == '200') {
				$resource = fopen($physicalFilePath, 'rb');
				while (!feof($resource)) {
					echo fread($resource, 1 * (1024 * 1024));
					ob_flush();
					flush();
				}
				fclose($resource);
			}
		} else {
			$this->_controller->response->header('HTTP/1.0 404 not found');
		}
		$this->_controller->_stop();
	}

/**
 * キャッシュを使用するかどうかを取得する
 * 		(パブリックルーム または マイポータルで使用されているファイルはキャッシュを使用する)
 * @param   array $uploadLinks
 * @param   string $sizeType
 * @return  void
 * @since   v 3.0.0.0
 */
	public function isUseCache($uploadId) {
		$Page = ClassRegistry::init('Page');

		$isUseCache = false;
		$params = array(
			'conditions' => array(
				'UploadLink.upload_id' => $uploadId,
			),
			'joins' => array(
				array(
					'type'=>'INNER',
					'table'=>'contents',
					'alias'=>'Content',
					'conditions'=>array(
						"`Page`.`room_id`=`Content`.`room_id`",
						"`Page`.`id`=`Page`.`room_id`",
						'Page.space_type' =>array(NC_SPACE_TYPE_PUBLIC, NC_SPACE_TYPE_MYPORTAL),
					),
				),
				array(
					'type'=>'INNER',
					'table'=>'upload_links',
					'alias'=>'UploadLink',
					'conditions'=>array(
						"`Content`.`id`=`UploadLink`.`content_id`",
					),
				)
			)
		);
		$rooms = $Page->find('all', $params);
		if (!empty($rooms)) {
			$isUseCache = true;
		}

		return $isUseCache;
	}

/**
 * ヘッダー設定処理
 * @param   string $filename
 * @param   string $pathname
 * @param   string $mimetype
 * @param   boolean $isUseCache
 * @return  void
 * @since   v 3.0.0.0
 */
	function _headerOutput($filename, $pathname, $mimetype, $isUseCache=false) {

		$statusCode = '200';
		$userAgent = $_SERVER['HTTP_USER_AGENT'];
		$encoding = Configure::read('App.encoding');

		$stats = stat($pathname);
		$etag = sprintf('"%x-%x-%x"', $stats['ino'], $stats['size'], $stats['mtime']);
		$this->_controller->response->header('Etag', $etag);

		if($isUseCache == true) {
			// パブリック・マイポータルのファイルならばキャッシュ取得
			$now = time();
			$offset = 60 * 60 * 24 * 7; // 1Week
			$this->_controller->response->cache($now, $now + $offset);
			if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && stripcslashes($_SERVER['HTTP_IF_NONE_MATCH']) == $etag) {
				$statusCode = '304';
				$this->_controller->response->header('HTTP/1.1 304 Not Modified');
			}
		} else if (isset($_SERVER['HTTPS']) && preg_match('/MSIE [6-8]{1}\./', $userAgent)) {
			// IE6-8 + サイト全体SSLの場合、ダウンロードが正常に行われない。
			// ダウンロードさせるためには、以下コメントをはずす必要があるが、
			// アップロードした画像ファイル等をローカルキャッシュにとられてしまう弊害がある。
			$now = time();
			$offset = 60 * 60 * 24 * 7; // 1Week
			$this->_controller->response->cache($now, $now + $offset);
		} else {
			$this->_controller->response->disableCache();
		}

		if (stristr($userAgent, 'MSIE')) {
			// IEの場合-URLエンコード
			$filename = urlencode($filename);
		} elseif (!stristr($userAgent, 'Safari')) {
			// Safari以外の場合-Base64エンコード
			$filename = '=?'.$encoding.'?B?'.base64_encode($filename).'?=';
		}

		$this->_controller->response->file($pathname, array('name'=>$filename));
		//W3Cによるとcharsetを明示的に指定することが重要なため定義する(http://www.w3.org/International/O-HTTP-charset)
		$this->_controller->response->type($mimetype.'; charset='.$encoding);
		$this->_controller->response->statusCode($statusCode);

		$this->_controller->response->send();

		return;
	}

}