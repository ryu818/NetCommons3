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
	public $uses = array('Upload', 'Upload.UploadSearch', 'Upload.UploadLibrary');

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
		} elseif ($this->action == 'delete') {
			$this->Security->validatePost = false;
			//$this->Security->csrfUseOnce = false;
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
		$popupType = $this->_initialize();
		$this->set('plugin', $plugin);
		$resolusion = isset($this->request->data['UploadLibrary']['resolusion']) ? $this->request->data['UploadLibrary']['resolusion'] : 'normal';
		if($this->request->is('post') && isset($this->request->data['UploadLibrary']['file_name'])) {
			// ファイルアップロード
			if(empty($plugin)) {
				$plugin = 'Upload';
			}
			$uploadSettings = array(
				'fileType' => $popupType,
				'plugin' => Inflector::camelize($plugin),
			);
			$thumbnailSizes = $this->Upload->getUploadMaxSizeByResolusion($resolusion);
			if (!empty($thumbnailSizes)) {
				$uploadSettings['thumbnailSizes'] = array('original' => $thumbnailSizes);
			}
			$this->UploadLibrary->uploadSettings('file_name', $uploadSettings);

			if($this->UploadLibrary->uploadFile($this->request->data)) {
				$fileName = $this->User->getUploadFileNames('file_name');
				$upload = $this->UploadLibrary->findById(substr($fileName, 0, strpos($fileName, '.')));
				$this->UploadSearch->convertUpload($upload, 'UploadLibrary');
				$this->set('upload', $upload);
			}
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
		$popupType = $this->_initialize();
		$this->set('plugin', $plugin);

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
		$data['UploadSearch']['user_type'] = $this->_getUserType($data['UploadSearch'], $isAdmin);
		$data['UploadSearch']['file_type'] = $this->_getFileType($data['UploadSearch']['file_type'], $popupType);

		if (!$this->request->is('post') && empty($data['UploadSearch']['plugin'])) {
			$data['UploadSearch']['plugin'] = Inflector::camelize($plugin);
		}
		$searchResult = $this->UploadSearch->search($data, $isAdmin);

		$this->set('is_admin', $isAdmin);
		$this->set('page', !empty($data['UploadSearch']['page']) ? intval($data['UploadSearch']['page']) : 1);
		$this->set('has_more', $searchResult[0]);

		if(!$isSearch && !isset($this->request->named['more'])) {
			$this->set('search_results', $searchResult[1]);
			$this->set('upload_search', $data);

			$uploadSearchPluginOptions = array();
			$uploadSearchCreatedOptions = array();

			$searchConditions = array('UploadSearch.user_id'=> $userId);
			$uploadSearchPluginOptions['myself'] = $this->UploadSearch->findPluginOptions($searchConditions);
			$uploadSearchCreatedOptions['myself'] = $this->UploadSearch->findCreatedOptions($searchConditions);
			if($isAdmin) {
				// 管理者の場合、すべてと退会ユーザーの絞り込みのための選択肢をあらかじめ取得
				$uploadSearchPluginOptions['all'] = $this->UploadSearch->findPluginOptions(array());
				$uploadSearchCreatedOptions['all'] = $this->UploadSearch->findCreatedOptions(array());

				$searchConditions = array('UploadSearch.user_id'=> '0');
				$uploadSearchPluginOptions['withdraw'] = $this->UploadSearch->findPluginOptions($searchConditions);
				$uploadSearchCreatedOptions['withdraw'] = $this->UploadSearch->findCreatedOptions($searchConditions);
			}
			$this->set('upload_search_plugin_options', $uploadSearchPluginOptions);
			$this->set('upload_search_created_options', $uploadSearchCreatedOptions);
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
		$this->_initialize();
		$loginUser = $this->Auth->user();
		$userId = $loginUser['id'];
		$isAdmin = ($this->Authority->getUserAuthorityId($loginUser['hierarchy']) == NC_AUTH_ADMIN_ID) ? true : false;
		$isSelfUploadFile = false;
		$isEdit = false;
		$this->set('plugin', $plugin);

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
		$this->_initialize($uploadId);
		if(!$this->_isEditValidate(intval($uploadId))) {
			return;
		}

		$upload = $prevUpload = $this->Upload->findById($uploadId);
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
			} else {
				// Saveエラー
				$upload['Upload']['file_name'] = $prevUpload['Upload']['file_name'];
				if (isset($this->Upload->validationErrors['file_name'])) {
					$this->Upload->validationErrors['basename'] = $this->Upload->validationErrors['file_name'];
					unset($this->Upload->validationErrors['file_name']);
				}
			}
		}
		$this->set('ref_url', isset($this->request->named['ref_url']) ? intval($this->request->named['ref_url']) : false);
		$this->UploadSearch->convertUpload($upload, 'Upload');
		// エラーがある場合はbasenameに入力した文字列を再セット
		if (!empty($this->Upload->validationErrors)) {
			$upload['Upload']['basename'] = $basename;
		}
		$this->set('upload', $upload);
		$this->set('success', $success);
	}

/**
 * ファイル削除処理
 * @param   integer $uploadId
 * @return  void
 * @since   v 3.0.0.0
 */
	public function delete($uploadId) {

		$uploadIdArr = explode(',', $uploadId);
		if(!isset($uploadIdArr[0])) {
			throw new BadRequestException(__('Unauthorized request.<br />Please reload the page.'));
		}
		$this->_initialize($uploadIdArr[0]);
		$deleteUploads = array();
		foreach($uploadIdArr as $key => $bufUploadId) {
			if(empty($bufUploadId)) {
				unset($uploadIdArr[$key]);
				continue;
			}
			$deleteUploads[$bufUploadId] = $this->_isEditValidate($bufUploadId);
			if(!$deleteUploads[$bufUploadId]) {
				return;
			}
			if(!$deleteUploads[$bufUploadId]['Upload']['is_delete_from_library']) {
				throw new BadRequestException(__('Unauthorized request.<br />Please reload the page.'));
			}
		}


		$confirmed = isset($this->data['confirmed']) ? $this->data['confirmed'] : _OFF;

		$uploads = $this->UploadSearch->findIsUseUploads($uploadIdArr);
		$this->set('token', $this->params['_Token']['key']);
		if (count($uploads) > 0 && $confirmed != _ON) {
			// 確認ダイアログ表示
			$this->set('uploads', $uploads);
			$this->set('uploadId', $uploadId);
			$this->render('delete_confirm');
			return;
		} else {
			// 削除処理
			$deleteClasses = array();
			foreach($deleteUploads as $bufUploadId => $bufUpload) {
				$delUpload['UploadLibrary']['id'] = $bufUploadId;
				$delUpload['UploadLibrary']['file_name'] = $bufUpload['Upload']['file_name'];
				$pluginName = $bufUpload['Upload']['plugin'];
				$uploadModelName = $bufUpload['Upload']['upload_model_name'];

				// Upload.upload_model_nameからdeleteFileを呼ぶ。そうしなければサムネイル毎、削除してくれないものが発生するため。
				// モデルからさがし、なければ、プラグインモデルからさがす。
				if($uploadModelName != 'UploadLibrary' && !isset($deleteClasses[$uploadModelName])) {
					$deleteClass = ClassRegistry::init($uploadModelName);
					if(!isset($deleteClass->actsAs['Upload'])) {
						$deleteClass = ClassRegistry::init($pluginName.'.'.$uploadModelName);
						if(!isset($deleteClass->actsAs['Upload'])) {
							$uploadModelName = 'UploadLibrary';
						}
					}
					if($uploadModelName != 'UploadLibrary') {
						$deleteClasses[$uploadModelName] = $deleteClass;
					}
				}

				$this->UploadLibrary->uploadSettings('file_name', array(
					'plugin' => Inflector::camelize($bufUpload['Upload']['plugin']),
				));

				if($uploadModelName == 'UploadLibrary') {
					$this->UploadLibrary->uploadSettings('file_name', array(
						'thumbnailSizes' => array(),
					));
				} else {
					$fields = $deleteClasses[$uploadModelName]->actsAs['Upload'];
					$thumbnailSizes = array();
					foreach($fields as $value) {
						if(isset($value['thumbnailSizes'])) {
							$thumbnailSizes = array_merge($thumbnailSizes, $value['thumbnailSizes']);
						}
					}
					$this->UploadLibrary->uploadSettings('file_name', array(
						'thumbnailSizes' => $thumbnailSizes,
					));
				}
				if(!$this->UploadLibrary->deleteFile($bufUploadId)) {
					throw new InternalErrorException(__('Failed to update the database, (%s).', 'uploads'));
				}
			}
		}


		$this->viewClass = 'Json';
		if (Configure::read('debug') != 0) {
			// SQLデバッグを表示させる。
			$this->autoRender = false;
			echo $this->render('Elements/sql_dump');
			$this->autoRender = true;
		}

		$this->set('_serialize', array('token'));

	}

/**
 * ファイル編集・削除バリデート
 * @param   integer $uploadId
 * @return  boolean false| array $upload
 * @since   v 3.0.0.0
 */
	protected function _isEditValidate($uploadId) {
		$loginUser = $this->Auth->user();
		$userId = $loginUser['id'];
		$isAdmin = ($this->Authority->getUserAuthorityId($loginUser['hierarchy']) == NC_AUTH_ADMIN_ID) ? true : false;

		$upload = $this->Upload->findById(intval($uploadId));
		if(!isset($upload['Upload'])) {
			$this->response->statusCode('404');
			$this->flash(__d('upload', 'The file does not exist. It might be deleted.'), '');
			return false;
		}
		if(!isset($upload['Upload']['user_id']) || ($upload['Upload']['user_id'] != $userId && !$isAdmin)) {
			$this->response->statusCode('403');
			$this->flash(__('Authority Error!  You do not have the privilege to access this page.'), '');
			return false;
		}
		return $upload;
	}

/**
 * Action initialize処理
 * 	・topID再セット
 * @param   string $id
 * @return  string $popupType
 * @since   v 3.0.0.0
 */
	protected function _initialize($id = null) {
		$popupType = isset($this->request->query['popup_type']) ? $this->_getPopupType($this->request->query['popup_type']) : 'file';
		$isWysiwyg = isset($this->request->query['is_wysiwyg']) ? $this->request->query['is_wysiwyg'] : true;
		$multiple = isset($this->request->query['multiple']) ? $this->request->query['multiple'] : true;

		//$this->set('id', 'upload-'.h($this->request->query['id']));
		if(isset($id)) {
			$ref_url = '';
			if(isset($this->request->named['ref_url'])) {
				$ref_url .= '-ref-url';
			}
			$this->set('id', 'upload-'.$this->action.'-'.$id.$ref_url);
		} else {
			$id = h($this->request->query['id']);
			$this->set('dialog_id', $id);
			$this->set('id', 'upload-'.$this->action.'-'.$id);
		}
		$this->set('popup_type', $popupType);
		$this->set('is_wysiwyg', $isWysiwyg);
		$this->set('multiple', $multiple);
		return $popupType;
	}

/**
 * Action 後処理
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	protected function _afterAction() {
		if((isset($this->request->named['is_tab']) && $this->request->named['is_tab'] == _ON)
			|| isset($this->request->data['Upload']['file'])
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
		$fileSize = $this->Upload->findFileSizeSumByUserId($loginUser['id']);
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
		$module = $this->Module->findByDirName($plugin);
		if(!isset($module['Module'])) {
			throw new BadRequestException(__('Unauthorized request.<br />Please reload the page.'));
		}
		return true;
	}

/**
 * user_type取得
 * @param   array $uploadSearch
 * @param   boolean $isAdmin
 * @return  integer
 * @since   v 3.0.0.0
 */
	protected function _getUserType($uploadSearch, $isAdmin) {
		if(!$isAdmin
			|| !isset($uploadSearch['user_type'])
			|| !in_array($uploadSearch['user_type'], array(UPLOAD_SEARCH_CONDITION_USER_MYSELF, UPLOAD_SEARCH_CONDITION_USER_ALL, UPLOAD_SEARCH_CONDITION_USER_WITHDRAW))) {
			return UPLOAD_SEARCH_CONDITION_USER_MYSELF;
		}
		return $uploadSearch['user_type'];
	}

/**
 * popup_type取得
 * @param   string $popupType
 * @return  file or image
 * @since   v 3.0.0.0
 */
	protected function _getPopupType($popupType) {
		if($popupType != 'image' && $popupType != 'file') {
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
