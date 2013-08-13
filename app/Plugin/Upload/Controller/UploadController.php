<?php
/**
 * UploadControllerクラス
 *
 * <pre>
 * ファイルアップロード処理用コントローラ
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       App.Controller
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class UploadController extends UploadAppController
{
/**
 * Model name
 *
 * @var array
 */
	public $uses = array('Upload', 'Upload.UploadSearch');

/**
 * Component name
 *
 * @var array
 */
	public $components = array('Security', 'CheckAuth' => array('allowUserAuth' => NC_AUTH_GUEST, 'chkBlockId' => false, 'chkPlugin' => false));

/**
 * 実行前処理
 * <pre>Tokenチェック処理</pre>
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function beforeFilter() {
		parent::beforeFilter();
		if ($this->action == 'index') {
			$this->Security->csrfUseOnce = false;	// 複数ファイルアップロードする際、Tokenが同一のため
		} elseif ($this->action == 'library') {
			$this->Security->unlockedFields = array('UploadSearch.file_type', 'UploadSearch.page');
			if ($this->request->is('post') && isset($this->request->data['UploadSearch'])) {
				// 検索の場合:Token値を書き換える処理ははいっているが、連続で検索やページ移動した場合、機能しないため。
				$this->Security->csrfUseOnce = false;
			}
		}
	}

/**
 * アップロード
 * @param   string plugin名称
 * @return  void
 * @since   v 3.0.0.0
 */
	public function index($plugin = null) {
		$this->_initialize($plugin);
		if($this->request->is('post') && isset($this->request->data['Upload']['file'])) {
			// ファイルアップロード
			$user = Configure::read(NC_SYSTEM_KEY.'.user');
			$userId = null;
			if (!isset($userId)) {
				$userId = $user['id'];
			}
			$options = array(
				'fileType'=>$this->request->query['popup_type'],
				'userId'=>$userId,
				'plugin'=>Inflector::camelize($plugin),
				'resolusion'=>isset($this->request->data['Upload']['resolusion']) ? $this->request->data['Upload']['resolusion'] : ''
			);
			$upload = $this->Upload->uploadFile($this->request->data['Upload']['file'], $options);
			$this->UploadSearch->convertUpload($upload, 'Upload');

			$this->set('upload', $upload);
			$this->render('Elements/preview');
			return;
		}
		$this->_afterAction();
	}

/**
 * ライブラリから追加タブ表示処理
 * @param   string plugin名称
 * @return  void
 * @since   v 3.0.0.0
 */
	public function library($plugin = null) {
		$popupType = $this->_initialize($plugin);

		$loginUser = $this->Auth->user();
		$userId = $loginUser['id'];
		$isAdmin = ($this->Authority->getUserAuthorityId($loginUser['hierarchy']) == NC_AUTH_ADMIN_ID) ? true : false;
		$isSearch = false;

		$this->_setProgressbar($loginUser);

		$data = $this->UploadSearch->findDefault();
		if ($this->request->is('post') && isset($this->request->data['UploadSearch'])) {
			$isSearch = true;
			$data = array_merge($data, $this->request->data);
		}
		$data['UploadSearch']['user_type'] = $this->_getUserType($data['UploadSearch']['user_type'], $isAdmin);
		$data['UploadSearch']['file_type'] = $this->_getFileType($data['UploadSearch']['file_type'], $popupType);

		//$searchConditions = array();
		//if ($data['UploadSearch']['user_type'] == UPLOAD_SEARCH_CONDITION_USER_MYSELF) {
			$searchConditions = array('UploadSearch.user_id'=> $userId);
		//}

		$searchResult = $this->UploadSearch->search($data, $isAdmin);

		$this->set('is_admin', $isAdmin);
		$this->set('page', !empty($data['UploadSearch']['page']) ? intval($data['UploadSearch']['page']) : 1);
		$this->set('has_more', $searchResult[0]);

		// TODO:削除未実装

		if(!$isSearch && !isset($this->request->named['more'])) {
			$this->set('search_results', $searchResult[1]);
			$this->set('upload_search', $data);
			$this->set('upload_search_plugin_options', $this->UploadSearch->findPluginOptions($searchConditions));
			$this->set('upload_search_created_options', $this->UploadSearch->findCreatedOptions($searchConditions));
			if($isAdmin) {
				// すべての会員からの日付セレクトボックスを予め取得
				$this->set('upload_search_created_all_options', $this->UploadSearch->findCreatedOptions(array()));
			}
		} else {
			// 検索 More Data Json
			$results = array();
			if(count($searchResult[1]) > 0) {
				foreach($searchResult[1] as $uploadSearch) {
					$results[] = $uploadSearch['UploadSearch'];
				}
			}
			$this->set('search_results', $results);
			$this->set('token', $this->params['_Token']['key']);
			$this->viewClass = 'Json';
			if (Configure::read('debug') != 0) {
				// SQLデバッグを表示させる。
				$this->autoRender = false;
				echo $this->render('Elements/sql_dump');
				$this->autoRender = true;
			}

			$this->set('_serialize', array('page', 'has_more','search_results', 'token'));

			return;
		}
		$this->_afterAction();
	}

/**
 * URLから参照タブ表示処理
 * @param   string plugin名称
 * @return  void
 * @since   v 3.0.0.0
 */
	public function ref_url($plugin = null) {
		$this->_initialize($plugin);
		$loginUser = $this->Auth->user();
		$userId = $loginUser['id'];
		$isAdmin = ($this->Authority->getUserAuthorityId($loginUser['hierarchy']) == NC_AUTH_ADMIN_ID) ? true : false;
		$isSelfUploadFile = false;
		$isEdit = false;

		$src = isset($this->request->query['src']) ? $this->request->query['src'] : null;
		if(isset($src)) {
			$uploadId = $this->_isSelfFile($src);
			if($uploadId !== true && $uploadId > 0) {
				$upload = $this->Upload->findById($uploadId);
				if(isset($upload['Upload'])) {
					if($upload['Upload']['user_id'] == $userId || $isAdmin) {
						$isEdit = true;
					}
					$this->UploadSearch->convertUpload($upload, 'Upload');
					$this->set('upload', $upload);
					$isSelfUploadFile = true;
				}

			}
		}
		$this->set('is_self_upload_file', $isSelfUploadFile);
		$this->set('is_edit', $isEdit);
		$this->set('src', $src);
		$this->_afterAction();
	}

/**
 * ファイル編集表示処理
 * @param   integer $uploadId
 * @return  void
 * @since   v 3.0.0.0
 */
	public function edit($uploadId) {
		$this->_initialize(null, $uploadId);

		$loginUser = $this->Auth->user();
		$userId = $loginUser['id'];
		$isAdmin = ($this->Authority->getUserAuthorityId($loginUser['hierarchy']) == NC_AUTH_ADMIN_ID) ? true : false;
		$isEdit = false;

		$upload = $this->Upload->findById(intval($uploadId));
		if(!isset($upload['Upload'])) {
			$this->flash(__d('upload', 'The file does not exist. It might be deleted.'), null, 'Upload.edit.001', '404');
			return;

		}
		if($upload['Upload']['user_id'] != $userId && !$isAdmin) {
			$this->flash(__('Authority Error!  You do not have the privilege to access this page.'), null, 'Upload.edit.002', '403');
			return;
		}

		$success = false;
		if($this->request->is('post') && isset($this->request->data['Upload'])) {
			// 登録処理
			$basename = isset($this->request->data['Upload']['basename']) ? $this->request->data['Upload']['basename'] : '';
			$upload['Upload']['file_name'] = $basename.'.'.$upload['Upload']['extension'];
			$upload['Upload'] = array_merge($upload['Upload'], $this->request->data['Upload']);
			$this->Upload->id = $uploadId;
			if ($this->Upload->save($upload)) {
				$this->Session->setFlash(__('Has been successfully updated.'));
				$success = true;
			}
		}
		$this->set('ref_url', isset($this->request->named['ref_url']) ? intval($this->request->named['ref_url']) : false);
		$this->UploadSearch->convertUpload($upload, 'Upload');
		$this->set('upload', $upload);
		$this->set('success', $success);
	}

/**
 * Action initialize処理
 * 	・topID再セット
 *  ・plugin名セット
 * @param   string $plugin
 * @param   string $id
 * @return  string $popupType
 * @since   v 3.0.0.0
 */
	protected function _initialize($plugin = null, $id = null) {

		$popupType = isset($this->request->query['popup_type']) ? $this->_getPopupType($this->request->query['popup_type']) : 'file';

		//$this->set('id', 'upload-'.h($this->request->query['id']));
		if(isset($id)) {
			$ref_url = '';
			if(isset($this->request->named['ref_url'])) {
				$ref_url .= '-ref-url';
			}
			$this->set('id', 'upload-'.$this->action.'-'.$id.$ref_url);
		} else {
			$this->set('dialog_id', h($this->request->query['id']));
			$this->set('id', 'upload-'.$this->action.'-'.h($this->request->query['id']));
		}
		$this->set('plugin', $plugin);
		$this->set('popup_type', $popupType);
		return $popupType;
	}

/**
 * Action 後処理
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	protected function _afterAction() {
		if((isset($this->request->named['is_tab']) && $this->request->named['is_tab'] == _ON) || isset($this->request->data['Upload']['file'])
		|| $this->request->is('post')) {
			$this->render('Elements/'.$this->action);
		} else {
			$this->render('index');
		}
	}

/**
 * 自サイトのアップロード画像かどうか
 * @param   string $src
 * @return  mixed false|true|integer upload_id
 * @since   v 3.0.0.0
 */
	protected function _isSelfFile($src) {
		if(preg_match('/^'.preg_quote(Router::url('/', true), '/').'/', $src) || preg_match('/^'.preg_quote(Router::url('/'), '/').'/', $src)) {
			if(preg_match('/\/nc-downloads\/([0-9]+)\/*$/', $src, $matches)) {
				return intval($matches[1]);
			}
			return true;
		}
		return false;
	}

/**
 * ファイルサイズのカレント値/合計値のプログレスバー情報セット
 * @param   array $loginUser
 * @return  void
 * @since   v 3.0.0.0
 */
	protected function _setProgressbar($loginUser) {
		App::uses('CakeNumber', 'Utility');
		$fileSize = $this->Upload->findFilesizeSumByUserId($loginUser['id']);
		$authority = $this->Authority->findById($loginUser['authority_id']);
		$fileSizeRate = round($fileSize / $authority['Authority']['max_size'], 2) * 100;
		$this->set('file_max_size', CakeNumber::toReadableSize($authority['Authority']['max_size']));
		$this->set('file_size', CakeNumber::toReadableSize($fileSize));
		$this->set('file_size_rate', $fileSizeRate);
	}

/**
 * pluginチェック
 * @param   string plugin名称
 * @return  boolean false|string plugin名称(camelize) + flashメッセージ
 * TODO:Modelのバリデートで行うかも
 * @since   v 3.0.0.0
 */
	protected function _validatePlugin($plugin = null) {
		if(!isset($plugin)) {
			return true;
		}
		$plugin = Inflector::camelize($plugin);
		$module = $this->Module->findByDirname($plugin);
		if(!isset($module['Module'])) {
			$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'Upload.validatePlugin.001', '500');
			return false;
		}
		return true;
	}

/**
 * user_type取得
 * @param   integer $userType
 * @param   boolean $isAdmin
 * @return  integer
 * @since   v 3.0.0.0
 */
	protected function _getUserType($userType, $isAdmin) {
		if(!$isAdmin || !in_array($userType, array(UPLOAD_SEARCH_CONDITION_USER_MYSELF, UPLOAD_SEARCH_CONDITION_USER_ALL, UPLOAD_SEARCH_CONDITION_USER_WITHDRAW))) {
			return UPLOAD_SEARCH_CONDITION_USER_MYSELF;
		}
		return $userType;
	}

/**
 * popup_type取得
 * @param   string $popupType
 * @return  file or image or library
 * @since   v 3.0.0.0
 */
	protected function _getPopupType($popupType) {
		// TODO:$popupType = 'library'未実装 WYSIWYG以外からライブラリー一覧を表示させるため
		if($popupType != 'library' &&  $popupType != 'image' && $popupType != 'file') {
			return 'file';
		}
		return $popupType;
	}

/**
 * file_type取得
 * @param   string $fileType
 * @param   string $popupType
 * @return  string $fileType
 * @since   v 3.0.0.0
 */
	protected function _getFileType($fileType, $popupType) {
		if($popupType == 'image') {
			$values = array('image', 'image-unused');
		} else {
			$values = array('', 'image', 'audio', 'video', 'other', 'file-unused');
		}
		if(!in_array($fileType, $values)) {
			return ($popupType == 'image') ? 'image' : '';
		}

		return $fileType;
	}
}
