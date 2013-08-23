<?php
/**
 * Upload behavior
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Model.Behavior
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
/**
 * Upload behavior
 *
 * Enables users to easily add file uploading and necessary validation rules
 *
 * PHP versions 4 and 5
 *
 * Copyright 2010, Jose Diaz-Gonzalez
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2010, Jose Diaz-Gonzalez
 * @package       upload
 * @subpackage    upload.models.behaviors
 * @link          http://github.com/josegonzalez/upload
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
App::uses('Folder', 'Utility');
class UploadBehavior extends ModelBehavior {

	public $defaults = array(
		'rootDir'			=> null,
		'pathMethod'		=> 'flat',
		'path'				=> '{ROOT}{DS}',
		'fields'			=> array('dir' => 'dir', 'type' => 'type', 'size' => 'size'),
		'mimetypes'			=> array(),
		'extensions'		=> array(),
		'maxSize'			=> 2097152,
		'minSize'			=> 8,
		'maxHeight'			=> 0,
		'minHeight'			=> 0,
		'maxWidth'			=> 0,
		'minWidth'			=> 0,
		'thumbnails'		=> true,
		'thumbnailMethod'	=> 'imagick',
		'thumbnailName'		=> null,
		'thumbnailPath'		=> null,
		'thumbnailPrefixStyle'=> false,
		'thumbnailQuality'	=> 75,
		'thumbnailSizes'	=> array(),
		'thumbnailType'		=> false,
		'deleteOnUpdate'	=> true,
		'mediaThumbnailType'=> 'png',
		'saveDir'			=> true,
		'deleteFolderOnDelete' => true,
		'mode' 				=> NC_UPLOAD_FOLDER_MODE,
// Add Start R.Ohga
		'renameFlag'		=> true,		//ファイル名を[[アップロードID].[拡張子]]形式に変換するかどうか
		'filePath'			=> '',			//Upload.file_path登録用
		'modelName'			=> '',			//UploadLink.model_name登録用
		'fieldName'			=> '',			//UploadLink.field_name登録用
		'accessHierarchy'	=> 0,			//UploadLink.access_hierarchy登録用
		'downloadPassword'	=> '',			//UploadLink.download_password登録用
		'checkComponentAction'=> 'Download',//UploadLink.check_component_action登録用
// Add End R.Ohga
	);

// Add Start R.Ohga
// ビヘイビア全体を通して使用するオプションを保持
	protected $_behavierOptions = array(
		'fileType' => 'file',	//バリデータ判断用
// 		'userId' => 0,			//Upload.user_id登録用 // 初期値をログインユーザーのIDとするためここでは設定しない
		'plugin' => '',			//Upload.plugin, UploadLink.plugin登録用
		'contentId' => 0,		//UploadLink.content_id登録用
		'isWysiwyg' => false,	//WYSIWYGからの登録かどうかの判断用
	);
// Add End R.Ohga

	protected $_imageMimetypes = array(
		'image/bmp',
		'image/gif',
		'image/jpeg',
		'image/pjpeg',
		'image/png',
		'image/vnd.microsoft.icon',
		'image/x-icon',
	);

	protected $_mediaMimetypes = array(
		'application/pdf',
		'application/postscript',
	);

	protected $_pathMethods = array('flat', 'primaryKey', 'random', 'randomCombined');

	protected $_resizeMethods = array('imagick', 'php');

	private $__filesToRemove = array();

	private $__foldersToRemove = array();

	protected $_removingOnly = array();

/**
 * Runtime configuration for this behavior
 *
 * @var array
 **/
	public $runtime;

/**
 * Initiate Upload behavior
 *
 * @param object $model instance of model
 * @param array $config array of configuration settings.
 * @return void
 * @access public
 */
	public function setup(Model $model, $config = array()) {
		if (isset($this->settings[$model->alias])) return;
		$this->settings[$model->alias] = array();

		foreach ($config as $field => $options) {
			$this->_setupField($model, $field, $options);
		}
	}

/**
 * Setup a particular upload field
 *
 * @param AppModel $model Model instance
 * @param string $field Name of field being modified
 * @param array $options array of configuration settings for a field
 * @return void
 * @author Jose Diaz-Gonzalez
 */
	public function _setupField(Model $model, $field, $options) {
		if (is_int($field)) {
			$field = $options;
			$options = array();
		}

		$this->defaults['rootDir'] = NC_UPLOADS_DIR;
		if (!isset($this->settings[$model->alias][$field])) {
			$options = array_merge($this->defaults, (array) $options);

			// HACK: Remove me in next major version
			if (!empty($options['thumbsizes'])) {
				$options['thumbnailSizes'] = $options['thumbsizes'];
			}

			if (!empty($options['prefixStyle'])) {
				$options['thumbnailPrefixStyle'] = $options['prefixStyle'];
			}
			// ENDHACK

// Add Start R.Ohga

			if (empty($options['modelName'])) {
				$options['modelName'] = $model->name;
			}
			if (empty($options['fieldName'])) {
				$options['fieldName'] = $field;
			}

// Add End R.Ohga

			$options['fields'] += $this->defaults['fields'];
			if ($options['rootDir'] === null) {
				$options['rootDir'] = $this->defaults['rootDir'];
			}

			if ($options['thumbnailName'] === null) {
				if ($options['thumbnailPrefixStyle']) {
// Edit Start R.Ohga
					$options['thumbnailName'] = '{size}-{filename}';
//					$options['thumbnailName'] = '{size}_{filename}';
// Edit End R.Ohga
				} else {
// Edit Start R.Ohga
					$options['thumbnailName'] = '{filename}-{size}';
//					$options['thumbnailName'] = '{filename}_{size}';
// Edit End R.Ohga
				}
			}

			if ($options['thumbnailPath'] === null) {
				$options['thumbnailPath'] = Folder::slashTerm($this->_path($model, $field, array(
					'isThumbnail' => true,
					'path' => $options['path'],
					'rootDir' => $options['rootDir']
				)));
			} else {
				$options['thumbnailPath'] = Folder::slashTerm($this->_path($model, $field, array(
					'isThumbnail' => true,
					'path' => $options['thumbnailPath'],
					'rootDir' => $options['rootDir']
				)));
			}

			$options['path'] = Folder::slashTerm($this->_path($model, $field, array(
				'isThumbnail' => false,
				'path' => $options['path'],
				'rootDir' => $options['rootDir']
			)));

// Edit Start R.Ohga
			$options['thumbnailMethod'] = $this->_getEnableThumnailMethod($model, $options['thumbnailMethod']);
// 			if (!in_array($options['thumbnailMethod'], $this->_resizeMethods)) {
// 				$options['thumbnailMethod'] = 'imagick';
// 			}
// Edit End R.Ohga

			if (!in_array($options['pathMethod'], $this->_pathMethods)) {
// Edit Start R.Ohga
				$options['pathMethod'] = 'flat';
//				$options['pathMethod'] = 'primaryKey';
// Edit End R.Ohga
			}
			$options['pathMethod'] = '_getPath' . Inflector::camelize($options['pathMethod']);
			$options['thumbnailMethod'] = '_resize' . Inflector::camelize($options['thumbnailMethod']);
			$this->settings[$model->alias][$field] = $options;
		}
	}

// Add Start R.Ohga
/**
 * 利用可能な画像縮小メソッド取得
 *
 * @param AppModel $model Model instance
 * @param string $thumbnailMethod
 * @return string $thumbnailMethod
 */
	public function _getEnableThumnailMethod(Model $model, $thumbnailMethod) {
		if ($thumbnailMethod == 'php' && extension_loaded('gd')) {
			return 'php';
		}
		if (extension_loaded('imagick')) {
			return 'imagick';
		} elseif (extension_loaded('gd')) {
			return 'php';
		}
		return '';
	}
// Add End R.Ohga

/**
 * Convenience method for configuring UploadBehavior settings
 *
 * @param AppModel $model Model instance
 * @param string $field Name of field being modified
 * @param mixed $one A string or an array of data.
 * @param mixed $two Value in case $one is a string (which then works as the key).
 *   Unused if $one is an associative array, otherwise serves as the values to $one's keys.
 * @return void
 */
	public function uploadSettings(Model $model, $field, $one, $two = null) {
		if (empty($this->settings[$model->alias][$field])) {
			$this->_setupField($model, $field, array());
		}

		$data = array();

		if (is_array($one)) {
			if (is_array($two)) {
				$data = array_combine($one, $two);
			} else {
				$data = $one;
			}
		} else {
			$data = array($one => $two);
		}
		$this->settings[$model->alias][$field] = $data + $this->settings[$model->alias][$field];
	}

// Add Start R.Ohga
/**
 * 各カラムに使用するオプションを設定
 * 	新しい関数が増え紛らわしくなるためにuploadSettings()の別名を定義
 *
 * @param AppModel $model Model instance
 * @param string $field Name of field being modified
 * @param mixed $one A string or an array of data.
 * @param mixed $two Value in case $one is a string (which then works as the key).
 *   Unused if $one is an associative array, otherwise serves as the values to $one's keys.
 * @return void
 */
	public function setColumnOptions(Model $model, $field, $one, $two = null) {
		$this->uploadSettings($model, $field, $one, $two = null);
	}

/**
 * ビヘイビア全体を通して使用するオプションを設定
 *
 * @param AppModel $model Model instance
 * @param array $options
 * @return void
 */
	public function setBehavierOptions(Model $model, $options) {
		if (!is_array($options)) {
			return;
		}

		$options = array_merge($this->_behavierOptions, $options);
		if (!isset($options['userId'])) {
			$user = Configure::read(NC_SYSTEM_KEY.'.user');
			$options['userId'] = $user['id'];
		}
		$this->_behavierOptions = $options;
	}

/**
 * ファイル名称が重複している場合にファイル名称に数字を追加
 *
 * @param AppModel $model Model instance
 * @param string $field
 * @param string $fileName
 * @return strng $fileName
 */
	public function _rename(Model $model, $field, $fileName) {
		$path = $this->settings[$model->alias][$field]['path'];
		$plugin = $this->_behavierOptions['plugin'];
		$srcFile = $path . $plugin . DS . $fileName;
		if (!file_exists($srcFile)) {
			return $fileName;
		}
		$pathinfo = $this->_pathinfo($srcFile);
		$i = 0;
		while (true) {
			$i++;
			$fileName = $pathinfo['filename'].'-'.$i.'.'.$pathinfo['extension'];
			$srcFile = $path . $plugin . DS . $fileName;
			if (!file_exists($srcFile)) {
				return $fileName;
			}
		}
	}
// Add End R.Ohga

/**
 * Before save method. Called before all saves
 *
 * Handles setup of file uploads
 *
 * @param AppModel $model Model instance
 * @return boolean
 */
	public function beforeSave(Model $model) {
// Add Start R.Ohga
// Uploadへの登録の場合は以下の処理を行わない
		if ($model->alias == 'Upload') {
			return true;
		}
// Add End R.Ohga

		$this->_removingOnly = array();
		foreach ($this->settings[$model->alias] as $field => $options) {
			if (!isset($model->data[$model->alias][$field])) continue;
			if (!is_array($model->data[$model->alias][$field])) continue;

			$this->runtime[$model->alias][$field] = $model->data[$model->alias][$field];

			$beforeUploadId = 0;
			$removing = !empty($model->data[$model->alias][$field]['remove']);
			if ($removing || ($this->settings[$model->alias][$field]['deleteOnUpdate']
			&& isset($model->data[$model->alias][$field]['name'])
			&& strlen($model->data[$model->alias][$field]['name']))) {
				// We're updating the file, remove old versions
				if (!empty($model->id)) {
					$data = $model->find('first', array(
						'conditions' => array("{$model->alias}.{$model->primaryKey}" => $model->id),
						'contain' => false,
						'recursive' => -1,
					));
					$this->_prepareFilesForDeletion($model, $field, $data, $options);
				}

				if ($removing) {
					$model->data[$model->alias] = array(
						$field => null,
						$options['fields']['type'] => null,
						$options['fields']['size'] => null,
						$options['fields']['dir'] => null,
					);

					$this->_removingOnly[$field] = true;
					continue;
				} else {
// Edit Start R.Ohga
// もともとのアップロードIDを取得
					$model->data[$model->alias][$field] = array(
						$field => $data[$model->alias][$field],
						$options['fields']['type'] => null,
						$options['fields']['size'] => null,
					);
					$beforeUploadId = $data[$model->alias][$field];
// 					$model->data[$model->alias][$field] = array(
// 						$field => null,
// 						$options['fields']['type'] => null,
// 						$options['fields']['size'] => null,
// 					);
// Edit End R.Ohga
				}
			} elseif (!isset($model->data[$model->alias][$field]['name'])
			|| !strlen($model->data[$model->alias][$field]['name'])) {
				// if field is empty, don't delete/nullify existing file
				unset($model->data[$model->alias][$field]);
				continue;
			}

// Edit Start R.Ohga
			// 当該カラムのファイル情報をUploadテーブルに登録
			$uploadField = $this->runtime[$model->alias][$field];
			$upload = $this->saveUpload($model, $field, $uploadField, true, $beforeUploadId);
			if ($upload === false) {
				return false;
			}

			// 登録したUpload.idを当該カラムに設定
			$model->data[$model->alias] = array_merge($model->data[$model->alias], array(
				$field => $upload['Upload']['id'],
				$options['fields']['type'] => $this->runtime[$model->alias][$field]['type'],
				$options['fields']['size'] => $this->runtime[$model->alias][$field]['size']
			));
// 			$model->data[$model->alias] = array_merge($model->data[$model->alias], array(
// 				$field => $this->runtime[$model->alias][$field]['name'],
// 				$options['fields']['type'] => $this->runtime[$model->alias][$field]['type'],
// 				$options['fields']['size'] => $this->runtime[$model->alias][$field]['size']
// 			));
// Edit End R.Ohga
		}
		return true;
	}

// Edit Start R.Ohga
// 物理ファイル名の更新・UploadLinkテーブルへの追加・ファイルパスの変更をするように修正
// 	public function afterSave(Model $model, $created) {
// 		$temp = array($model->alias => array());
// 		foreach ($this->settings[$model->alias] as $field => $options) {
// 			if (!in_array($field, array_keys($model->data[$model->alias]))) continue;
// 			if (empty($this->runtime[$model->alias][$field])) continue;
// 			if (isset($this->_removingOnly[$field])) continue;

// 			$tempPath = $this->_getPath($model, $field);

// 			$path = $this->settings[$model->alias][$field]['path'];
// 			$thumbnailPath = $this->settings[$model->alias][$field]['thumbnailPath'];

// 			if (!empty($tempPath)) {
// 				$path .= $tempPath . DS;
// 				$thumbnailPath .= $tempPath . DS;
// 			}
// 			$tmp = $this->runtime[$model->alias][$field]['tmp_name'];
// 			$filePath = $path . $model->data[$model->alias][$field];
// 			if (!$this->handleUploadedFile($model->alias, $field, $tmp, $filePath)) {
// 				CakeLog::error(sprintf('Model %s, Field %s: Unable to move the uploaded file to %s', $model->alias, $field, $filePath));
// 				$model->invalidate($field, sprintf('Unable to move the uploaded file to %s', $filePath));
// 				$db = $model->getDataSource();
// 				$db->rollback();
// 				throw new UploadException('Unable to upload file');
// 			}

// 			$this->_createThumbnails($model, $field, $path, $thumbnailPath);
// 			if ($model->hasField($options['fields']['dir'])) {
// 				if ($created && $options['pathMethod'] == '_getPathFlat') {
// 				} else if ($options['saveDir']) {
// 					$temp[$model->alias][$options['fields']['dir']] = "'{$tempPath}'";
// 				}
// 			}
// 		}

// 		if (!empty($temp[$model->alias])) {
// 			$model->updateAll($temp[$model->alias], array(
// 					$model->alias.'.'.$model->primaryKey => $model->id
// 			));
// 		}

// 		if (empty($this->__filesToRemove[$model->alias])) return true;
// 		foreach ($this->__filesToRemove[$model->alias] as $file) {
// 			$result[] = $this->unlink($file);
// 		}
// 		return $result;
// 	}

	public function afterSave(Model $model, $created) {
		$Upload = ClassRegistry::init('Upload');
		$UploadLink = ClassRegistry::init('UploadLink');

		$temp = array($model->alias => array());
		foreach ($this->settings[$model->alias] as $field => $options) {
			if (empty($this->runtime[$model->alias][$field]['tmp_name'])) continue;
			if (isset($this->_removingOnly[$field])) continue;

			$tempPath = $this->_getPath($model, $field);

			$path = $this->settings[$model->alias][$field]['path'];
			$thumbnailPath = $this->settings[$model->alias][$field]['thumbnailPath'];

			if (!empty($this->_behavierOptions['plugin'])) {
				$plugin = $this->_behavierOptions['plugin'];
				$path .= $plugin . DS;
				$thumbnailPath .= $plugin . DS;
			}

			if (!empty($tempPath)) {
				$path .= $tempPath . DS;
				$thumbnailPath .= $tempPath . DS;
			}

			$tmp = $this->runtime[$model->alias][$field]['tmp_name'];
			$name = $this->runtime[$model->alias][$field]['name'];
			$pathInfo = $this->_pathinfo($path.$name);
			if ($model->alias == 'Upload') {
				$uploadId = $model->id;
			} else {
				$uploadId = $model->data[$model->alias][$field];
			}

			if ($this->settings[$model->alias][$field]['renameFlag']) {
				$physicalFileName = $uploadId . '.' . $pathInfo['extension'];
			} elseif ($this->settings[$model->alias][$field]['pathMethod'] == '_getPathFlat') {
				$physicalFileName = $this->_rename($model, $field, $pathInfo['basename']);
			} else {
				$physicalFileName = $pathInfo['basename'];
			}

			$fields = array(
				'Upload.physical_file_name' => "'".$physicalFileName."'"
			);
			if(!$Upload->updateAll($fields, array('Upload.id' => $uploadId))) {
				return false;
			}

			// UploadLinkテーブル更新
			$fields = array(
				'UploadLink.unique_id' => $model->id,
			);
			if(!$UploadLink->updateAll($fields, array('UploadLink.upload_id' => $uploadId))) {
				return false;
			}

			$physicalFilePath = $path . $physicalFileName;
			if (!$this->handleUploadedFile($model->alias, $field, $tmp, $physicalFilePath)) {
				CakeLog::error(sprintf('Model %s, Field %s: Unable to move the uploaded file to %s', $model->alias, $field, $physicalFilePath));
				$model->invalidate($field, sprintf('Unable to move the uploaded file to %s', $physicalFilePath));
				$db = $model->getDataSource();
				$db->rollback();
				return false;
			}

			if ($this->settings[$model->alias][$field]['thumbnails']) {
				// リサイズ・サムネイル画像生成処理
				$maxWidth = $this->settings[$model->alias][$field]['maxWidth'];
				$maxHeight = $this->settings[$model->alias][$field]['maxHeight'];

				list($width, $height) = getimagesize($physicalFilePath);
				if ($width > $height) {
					$libraryGeometry = NC_UPLOAD_LIBRARY_GEOMETRY_FIT_HEIGHT;
				} else {
					$libraryGeometry = NC_UPLOAD_LIBRARY_GEOMETRY_FIT_WIDTH;
				}

				$thumbnailSizes = array('library' => $libraryGeometry);
				if ($maxWidth != 0 || $maxHeight != 0) {
					$thumbnailSizes[] = '['.$maxWidth.'x'.$maxHeight.']';//リサイズ
				}
				$this->settings[$model->alias][$field]['thumbnailSizes'] = array_merge(
					$this->settings[$model->alias][$field]['thumbnailSizes'], $thumbnailSizes
				);

				$this->_createThumbnails($model, $field, $physicalFilePath, $thumbnailPath);
				if ($model->hasField($options['fields']['dir'])) {
					if ($created && $options['pathMethod'] == '_getPathFlat') {
					} else if ($options['saveDir']) {
						$temp[$model->alias][$options['fields']['dir']] = "'{$tempPath}'";
					}
				}
			}
		}

		if (!empty($temp[$model->alias])) {
			$model->updateAll($temp[$model->alias], array(
				$model->alias.'.'.$model->primaryKey => $model->id
			));
		}

		if (empty($this->__filesToRemove[$model->alias])) return true;
		foreach ($this->__filesToRemove[$model->alias] as $file) {
			$result[] = $this->unlink($file);
		}
		return $result;
	}
// Edit End R.Ohga

// Add Start R.Ohga
/**
 * アップロード関連のテーブルへの登録更新
 *
 * @param AppModel $model Model instance
 * @param string $field
 * @param array $uploadFile
 * @param int $beforeUploadId もともと使用されていたアップロードID
 * @return mixed success:登録内容 error:false
 */
	public function saveUpload(Model $model, $field, $uploadFile, $doSaveUploadLink, $beforeUploadId=0) {

		if ($model->alias == 'Upload') {
			$Upload = $model;
		} else {
			$Upload = ClassRegistry::init('Upload');
		}

		// Upload登録
		$pathinfo = $this->_pathinfo($uploadFile['name']);
		$filePath = '';
		if (!empty($this->settings[$model->alias][$field]['filePath'])) {
			$filePath .= $this->settings[$model->alias][$field]['filePath'].DS;
		}

		// validate設定
		$Upload->validate = $Upload->setValidate($this->_behavierOptions['fileType']);
		$this->runtime[$model->alias][$field] = $uploadFile;

		if (empty($beforeUploadId)) {
			// 追加
			$data = array(
				'Upload' => array(
					'user_id' => $this->_behavierOptions['userId'],
					'file_name' => $uploadFile['name'],
					'file_size' => $uploadFile['size'],
					'file_path' => $filePath,
					'mimetype' => $uploadFile['type'],
					'extension' => $pathinfo['extension'],
					'plugin' => $this->_behavierOptions['plugin'],
					'is_wysiwyg' => ($this->_behavierOptions['isWysiwyg'] ? _ON : _OFF),
				)
			);
			$uploadResult = $Upload->save($data);
		} else {
			// 更新
			$data = array(
				'Upload.user_id' => $this->_behavierOptions['userId'],
				'Upload.file_name' => "'".$uploadFile['name']."'",
				'Upload.file_size' => $uploadFile['size'],
				'Upload.file_path' => "'".$filePath."'",
				'Upload.mimetype' => "'".$uploadFile['type']."'",
				'Upload.extension' => "'".$pathinfo['extension']."'",
				'Upload.plugin' => $this->_behavierOptions['plugin'],
			);
			$conditions = array('Upload.id' => $beforeUploadId);
			if(!$Upload->updateAll($data, $conditions)) {
				return false;
			}
			$model->data[$model->alias][$field] = $uploadFile;
			return array('Upload'=>$Upload->findById($beforeUploadId));
		}

		if(!is_array($uploadResult)) {
			return array('Upload'=>array(
				'file_name' => $uploadFile['name'],
				'error' => $Upload->validationErrors
			));
		}

		if ($doSaveUploadLink) {
			$options = $this->settings[$model->alias][$field];

			$data = array(
				'upload_id'=>$Upload->id,
				'plugin'=>$this->_behavierOptions['plugin'],
				'content_id'=>$this->_behavierOptions['contentId'],
				'model_name'=>$options['modelName'],
				'field_name'=>$options['fieldName'],
				'access_hierarchy'=>$options['accessHierarchy'],
				'is_use'=>_ON,
				'download_password'=>$options['downloadPassword'],
				'check_component_action'=>$options['checkComponentAction'],
			);
			$Upload->UploadLink->save($data);
		}

		return $Upload->findById($Upload->id);
	}

// Add End R.Ohga

	public function handleUploadedFile($modelAlias, $field, $tmp, $filePath) {
		if (is_uploaded_file($tmp)) {
			return move_uploaded_file($tmp, $filePath);
		} else {
			return rename($tmp, $filePath);
		}
	}

	public function unlink($file) {
		return @unlink($file);
	}

	public function deleteFolder(Model $model, $path) {
		if (!isset($this->__foldersToRemove[$model->alias])) {
			return false;
		}

		$folders = $this->__foldersToRemove[$model->alias];
		foreach ($folders as $folder) {
			$dir = $path . $folder;
			$it = new RecursiveDirectoryIterator($dir);
			$files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
			foreach ($files as $file) {
				if ($file->getFilename() === '.' || $file->getFilename() === '..') {
					continue;
				}

				if ($file->isDir()) {
					@rmdir($file->getRealPath());
				} else {
					@unlink($file->getRealPath());
				}
			}
			@rmdir($dir);
		}

		return true;
	}

	public function beforeDelete(Model $model, $cascade = true) {
		$data = $model->find('first', array(
			'conditions' => array("{$model->alias}.{$model->primaryKey}" => $model->id),
			'contain' => false,
			'recursive' => -1,
		));

		foreach ($this->settings[$model->alias] as $field => $options) {
			$this->_prepareFilesForDeletion($model, $field, $data, $options);
		}
		return true;
	}

	public function afterDelete(Model $model) {
		$result = array();
		if (!empty($this->__filesToRemove[$model->alias])) {
			foreach ($this->__filesToRemove[$model->alias] as $file) {
				$result[] = $this->unlink($file);
			}
		}

		foreach ($this->settings[$model->alias] as $field => $options) {
			if ($options['deleteFolderOnDelete'] == true) {
				$this->deleteFolder($model, $options['path']);
				return true;
			}
		}
		return $result;
	}

/**
 * Verify that the uploaded file has been moved to the
 * destination successfully. This rule is special that it
 * is invalidated in afterSave(). Therefore it is possible
 * for save() to return true and this rule to fail.
 *
 * @param Object $model
 * @return boolean Always true
 * @access public
 */
	public function moveUploadedFile(Model $model) {
		return true;
	}
/**
 * Check that the file does not exceed the max
 * file size specified by PHP
 *
 * @param Object $model
 * @param mixed $check Value to check
 * @return boolean Success
 * @access public
 */
	public function isUnderPhpSizeLimit(Model $model, $check) {
		$field = $this->_getField($check);

		if (!empty($check[$field]['remove'])) {
			return true;
		}

		return $check[$field]['error'] !== UPLOAD_ERR_INI_SIZE;
	}

/**
 * Check that the file does not exceed the max
 * file size specified in the HTML Form
 *
 * @param Object $model
 * @param mixed $check Value to check
 * @return boolean Success
 * @access public
 */
	public function isUnderFormSizeLimit(Model $model, $check) {
		$field = $this->_getField($check);

		if (!empty($check[$field]['remove'])) {
			return true;
		}

		return $check[$field]['error'] !== UPLOAD_ERR_FORM_SIZE;
	}

/**
 * Check that the file was completely uploaded
 *
 * @param Object $model
 * @param mixed $check Value to check
 * @return boolean Success
 * @access public
 */
	public function isCompletedUpload(Model $model, $check) {
		$field = $this->_getField($check);

		if (!empty($check[$field]['remove'])) {
			return true;
		}

		return $check[$field]['error'] !== UPLOAD_ERR_PARTIAL;
	}

/**
 * Check that a file was uploaded
 *
 * @param Object $model
 * @param mixed $check Value to check
 * @return boolean Success
 * @access public
 */
	public function isFileUpload(Model $model, $check) {
		$field = $this->_getField($check);

		if (!empty($check[$field]['remove'])) {
			return true;
		}

		return $check[$field]['error'] !== UPLOAD_ERR_NO_FILE;
	}

/**
 * Check that the PHP temporary directory is missing
 *
 * @param Object $model
 * @param mixed $check Value to check
 * @return boolean Success
 * @access public
 */
	public function tempDirExists(Model $model, $check, $requireUpload = true) {
		$field = $this->_getField($check);

		if (!empty($check[$field]['remove'])) {
			return true;
		}

		// Allow circumvention of this rule if uploads is not required
		if (!$requireUpload && $check[$field]['error'] === UPLOAD_ERR_NO_FILE) {
			return true;
		}

		return $check[$field]['error'] !== UPLOAD_ERR_NO_TMP_DIR;
	}

/**
 * Check that the file was successfully written to the server
 *
 * @param Object $model
 * @param mixed $check Value to check
 * @return boolean Success
 * @access public
 */
	public function isSuccessfulWrite(Model $model, $check, $requireUpload = true) {
		$field = $this->_getField($check);

		if (!empty($check[$field]['remove'])) {
			return true;
		}

		// Allow circumvention of this rule if uploads is not required
		if (!$requireUpload && $check[$field]['error'] === UPLOAD_ERR_NO_FILE) {
			return true;
		}

		return $check[$field]['error'] !== UPLOAD_ERR_CANT_WRITE;
	}

/**
 * Check that a PHP extension did not cause an error
 *
 * @param Object $model
 * @param mixed $check Value to check
 * @return boolean Success
 * @access public
 */
	public function noPhpExtensionErrors(Model $model, $check, $requireUpload = true) {
		$field = $this->_getField($check);

		if (!empty($check[$field]['remove'])) {
			return true;
		}

		// Allow circumvention of this rule if uploads is not required
		if (!$requireUpload && $check[$field]['error'] === UPLOAD_ERR_NO_FILE) {
			return true;
		}

		return $check[$field]['error'] !== UPLOAD_ERR_EXTENSION;
	}

/**
 * Check that the file is of a valid mimetype
 *
 * @param Object $model
 * @param mixed $check Value to check
 * @param array $mimetypes file mimetypes to allow
 * @return boolean Success
 * @access public
 */
	public function isValidMimeType(Model $model, $check, $mimetypes = array(), $requireUpload = true) {
		$field = $this->_getField($check);

		if (!empty($check[$field]['remove'])) {
			return true;
		}

		// Allow circumvention of this rule if uploads is not required
		if (!$requireUpload && $check[$field]['error'] === UPLOAD_ERR_NO_FILE) {
			return true;
		}

		// Non-file uploads also mean the mimetype is invalid
		if (!isset($check[$field]['type']) || !strlen($check[$field]['type'])) {
			return false;
		}

		// Sometimes the user passes in a string instead of an array
		if (is_string($mimetypes)) {
			$mimetypes = array($mimetypes);
		}

		foreach ($mimetypes as $key => $value) {
			if (!is_int($key)) {
				$mimetypes = $this->settings[$model->alias][$field]['mimetypes'];
				break;
			}
		}

		if (empty($mimetypes)) $mimetypes = $this->settings[$model->alias][$field]['mimetypes'];

		return in_array($check[$field]['type'], $mimetypes);
	}

/**
 * Check that the upload directory is writable
 *
 *
 * @param Object $model
 * @param mixed $check Value to check
 * @param string $path Full upload path
 * @return boolean Success
 * @access public
 */
	public function isWritable(Model $model, $check, $requireUpload = true) {
		$field = $this->_getField($check);

		if (!empty($check[$field]['remove'])) {
			return true;
		}

		// Allow circumvention of this rule if uploads is not required
		if (!$requireUpload && $check[$field]['error'] === UPLOAD_ERR_NO_FILE) {
			return true;
		}

		return is_writable($this->settings[$model->alias][$field]['path']);
	}

/**
 * Check that the upload directory exists
 *
 * @param Object $model
 * @param mixed $check Value to check
 * @param string $path Full upload path
 * @return boolean Success
 * @access public
 */
	public function isValidDir(Model $model, $check, $requireUpload = true) {
		$field = $this->_getField($check);

		if (!empty($check[$field]['remove'])) {
			return true;
		}

		// Allow circumvention of this rule if uploads is not required
		if (!$requireUpload && $check[$field]['error'] === UPLOAD_ERR_NO_FILE) {
			return true;
		}

		return is_dir($this->settings[$model->alias][$field]['path']);
	}

/**
 * Check that the file is below the maximum file upload size
 *
 * @param Object $model
 * @param mixed $check Value to check
 * @param int $size Maximum file size
 * @return boolean Success
 * @access public
 */
	public function isBelowMaxSize(Model $model, $check, $size = null, $requireUpload = true) {
		$field = $this->_getField($check);

		if (!empty($check[$field]['remove'])) {
			return true;
		}

		// Allow circumvention of this rule if uploads is not required
		if (!$requireUpload && $check[$field]['error'] === UPLOAD_ERR_NO_FILE) {
			return true;
		}

		// Non-file uploads also mean the size is too small
		if (!isset($check[$field]['size']) || !strlen($check[$field]['size'])) {
			return false;
		}

		if (!$size) $size = $this->settings[$model->alias][$field]['maxSize'];

		return $check[$field]['size'] <= $size;
	}

/**
 * Check that the file is above the minimum file upload size
 *
 * @param Object $model
 * @param mixed $check Value to check
 * @param int $size Minimum file size
 * @return boolean Success
 * @access public
 */
	public function isAboveMinSize(Model $model, $check, $size = null, $requireUpload = true) {
		$field = $this->_getField($check);

		if (!empty($check[$field]['remove'])) {
			return true;
		}

		// Allow circumvention of this rule if uploads is not required
		if (!$requireUpload && $check[$field]['error'] === UPLOAD_ERR_NO_FILE) {
			return true;
		}

		// Non-file uploads also mean the size is too small
		if (!isset($check[$field]['size']) || !strlen($check[$field]['size'])) {
			return false;
		}

		if (!$size) $size = $this->settings[$model->alias][$field]['minSize'];

		return $check[$field]['size'] >= $size;
	}

/**
 * Check that the file has a valid extension
 *
 * @param Object $model
 * @param mixed $check Value to check
 * @param array $extensions file extenstions to allow
 * @return boolean Success
 * @access public
 */
	public function isValidExtension(Model $model, $check, $extensions = array(), $requireUpload = true) {
		$field = $this->_getField($check);

		if (!empty($check[$field]['remove'])) {
			return true;
		}

		// Allow circumvention of this rule if uploads is not required
		if (!$requireUpload && $check[$field]['error'] === UPLOAD_ERR_NO_FILE) {
			return true;
		}

		// Non-file uploads also mean the extension is invalid
		if (!isset($check[$field]['name']) || !strlen($check[$field]['name'])) {
			return false;
		}

		// Sometimes the user passes in a string instead of an array
		if (is_string($extensions)) {
			$extensions = array($extensions);
		}

		// Sometimes a user does not specify any extensions in the validation rule
		foreach ($extensions as $key => $value) {
			if (!is_int($key)) {
				$extensions = $this->settings[$model->alias][$field]['extensions'];
				break;
			}
		}

		if (empty($extensions)) $extensions = $this->settings[$model->alias][$field]['extensions'];
		$pathInfo = $this->_pathinfo($check[$field]['name']);

		$extensions = array_map('strtolower', $extensions);
		return in_array(strtolower($pathInfo['extension']), $extensions);
	}

/**
 * Check that the file is above the minimum height requirement
 *
 * @param Object $model
 * @param mixed $check Value to check
 * @param int $height Height of Image
 * @return boolean Success
 * @access public
 */
	public function isAboveMinHeight(Model $model, $check, $height = null, $requireUpload = true) {
		$field = $this->_getField($check);

		if (!empty($check[$field]['remove'])) {
			return true;
		}

		// Allow circumvention of this rule if uploads is not required
		if (!$requireUpload && $check[$field]['error'] === UPLOAD_ERR_NO_FILE) {
			return true;
		}

		// Non-file uploads also mean the height is too big
		if (!isset($check[$field]['tmp_name']) || !strlen($check[$field]['tmp_name'])) {
			return false;
		}

		if (!$height) $height = $this->settings[$model->alias][$field]['minHeight'];

		list($imgWidth, $imgHeight) = getimagesize($check[$field]['tmp_name']);
		return $height > 0 && $imgHeight >= $height;
	}

/**
 * Check that the file is below the maximum height requirement
 *
 * @param Object $model
 * @param mixed $check Value to check
 * @param int $height Height of Image
 * @return boolean Success
 * @access public
 */
	public function isBelowMaxHeight(Model $model, $check, $height = null, $requireUpload = true) {
		$field = $this->_getField($check);

		if (!empty($check[$field]['remove'])) {
			return true;
		}

		// Allow circumvention of this rule if uploads is not required
		if (!$requireUpload && $check[$field]['error'] === UPLOAD_ERR_NO_FILE) {
			return true;
		}

		// Non-file uploads also mean the height is too big
		if (!isset($check[$field]['tmp_name']) || !strlen($check[$field]['tmp_name'])) {
			return false;
		}

		if (!$height) $height = $this->settings[$model->alias][$field]['maxHeight'];

		list($imgWidth, $imgHeight) = getimagesize($check[$field]['tmp_name']);
		return $height > 0 && $imgHeight <= $height;
	}

/**
 * Check that the file is above the minimum width requirement
 *
 * @param Object $model
 * @param mixed $check Value to check
 * @param int $width Width of Image
 * @return boolean Success
 * @access public
 */
	public function isAboveMinWidth(Model $model, $check, $width = null, $requireUpload = true) {
		$field = $this->_getField($check);

		if (!empty($check[$field]['remove'])) {
			return true;
		}

		// Allow circumvention of this rule if uploads is not required
		if (!$requireUpload && $check[$field]['error'] === UPLOAD_ERR_NO_FILE) {
			return true;
		}

		// Non-file uploads also mean the height is too big
		if (!isset($check[$field]['tmp_name']) || !strlen($check[$field]['tmp_name'])) {
			return false;
		}

		if (!$width) $width = $this->settings[$model->alias][$field]['minWidth'];

		list($imgWidth, $imgHeight) = getimagesize($check[$field]['tmp_name']);
		return $width > 0 && $imgWidth >= $width;
	}

/**
 * Check that the file is below the maximum width requirement
 *
 * @param Object $model
 * @param mixed $check Value to check
 * @param int $width Width of Image
 * @return boolean Success
 * @access public
 */
	public function isBelowMaxWidth(Model $model, $check, $width = null, $requireUpload = true) {
		$field = $this->_getField($check);

		if (!empty($check[$field]['remove'])) {
			return true;
		}

		// Allow circumvention of this rule if uploads is not required
		if (!$requireUpload && $check[$field]['error'] === UPLOAD_ERR_NO_FILE) {
			return true;
		}

		// Non-file uploads also mean the height is too big
		if (!isset($check[$field]['tmp_name']) || !strlen($check[$field]['tmp_name'])) {
			return false;
		}

		if (!$width) $width = $this->settings[$model->alias][$field]['maxWidth'];

		list($imgWidth, $imgHeight) = getimagesize($check[$field]['tmp_name']);
		return $width > 0 && $imgWidth <= $width;
	}

	public function _resizeImagick(Model $model, $field, $path, $size, $geometry, $thumbnailPath) {
// Edit Start R.Ohga
		$srcFile  = $path;
// 		$srcFile  = $path . $model->data[$model->alias][$field];
// Edit End R.Ohga
		$pathInfo = $this->_pathinfo($srcFile);
		$thumbnailType = $imageFormat = $this->settings[$model->alias][$field]['thumbnailType'];

		$isMedia = $this->_isMedia($model, $this->runtime[$model->alias][$field]['type']);
		$image    = new imagick();

		if ($isMedia) {
			$image->setResolution(300, 300);
			$srcFile = $srcFile.'[0]';
		}

		$image->readImage($srcFile);
		$height   = $image->getImageHeight();
		$width    = $image->getImageWidth();

		if (preg_match('/^\\[[\\d]+x[\\d]+\\]$/', $geometry)) {
			// resize with banding
			list($destW, $destH) = explode('x', substr($geometry, 1, strlen($geometry)-2));
// Edit Start R.Ohga
// 余白ができてしまうため修正
			if ($width > $height) {
				$destH = 0;
			} else {
				$destW = 0;
			}
// Edit Start R.Ohga
// 小さい画像を拡大してしまうため修正
			if ($destH > $height) {$destH = $height;}
			if ($destW > $width) {$destW = $width;}
// Edit End R.Ohga
			$image->resizeImage($destW, $destH, imagick::FILTER_MITCHELL, 1);
// 			$image->thumbnailImage($destW, $destH, true);
// 			$imageGeometry = $image->getImageGeometry();
// 			$x = ($destW - $imageGeometry['width']) / 2;
// 			$y = ($destH - $imageGeometry['height']) / 2;
// 			$image->setGravity(Imagick::GRAVITY_CENTER);
// 			$image->extentImage($destW, $destH, $x, $y);
// Edit End R.Ohga
		} elseif (preg_match('/^[\\d]+x[\\d]+$/', $geometry)) {
			// cropped resize (best fit)
			list($destW, $destH) = explode('x', $geometry);
// Edit Start R.Ohga
			if ($destH > $height) {$destH = $height;}
			if ($destW > $width) {$destW = $width;}
// Edit End R.Ohga
			$image->cropThumbnailImage($destW, $destH);
		} elseif (preg_match('/^[\\d]+w$/', $geometry)) {
			// calculate heigh according to aspect ratio
// Edit Start R.Ohga
			$destW = (int)$geometry-1;
			if ($destW < $width) {
				$image->thumbnailImage($destW, 0);
			}
// 			$image->thumbnailImage((int)$geometry-1, 0);
// Edit End R.Ohga
		} elseif (preg_match('/^[\\d]+h$/', $geometry)) {
			// calculate width according to aspect ratio
// Edit Start R.Ohga
			$destH = (int)$geometry-1;
			if ($destH < $height) {
				$image->thumbnailImage(0, $destH);
			}
// 			$image->thumbnailImage(0, (int)$geometry-1);
// Edit End R.Ohga
		} elseif (preg_match('/^[\\d]+l$/', $geometry)) {
			// calculate shortest side according to aspect ratio
			$destW = 0;
			$destH = 0;
			$destW = ($width > $height) ? (int)$geometry-1 : 0;
			$destH = ($width > $height) ? 0 : (int)$geometry-1;
// Edit Start R.Ohga
			if ($destH > $height) {$destH = $height;}
			if ($destW > $width) {$destW = $width;}
// Edit End R.Ohga

			$imagickVersion = phpversion('imagick');
			$image->thumbnailImage($destW, $destH, !($imagickVersion[0] == 3));
		}

		if ($isMedia) {
			$thumbnailType = $imageFormat = $this->settings[$model->alias][$field]['mediaThumbnailType'];
		}

		if (!$thumbnailType || !is_string($thumbnailType)) {
			try {
				$thumbnailType = $imageFormat = $image->getImageFormat();
				// Fix file casing
				while (true) {
					$ext = false;
					$pieces = explode('.', $srcFile);
					if (count($pieces) > 1) {
						$ext = end($pieces);
					}

					if (!$ext || !strlen($ext)) {
						break;
					}

					$low = array(
						'ext' => strtolower($ext),
						'thumbnailType' => strtolower($thumbnailType),
					);

					if ($low['ext'] == 'jpg' && $low['thumbnailType'] == 'jpeg') {
						$thumbnailType = $ext;
						break;
					}

					if ($low['ext'] == $low['thumbnailType']) {
						$thumbnailType = $ext;
					}

					break;
				}
			} catch (Exception $e) {$this->log($e->getMessage(), 'upload');
				$thumbnailType = $imageFormat = 'png';
			}
		}

// Edit Start R.Ohga
// $sizeが空の場合はファイル名に$sizeを使用しないように修正
		$thumbnailName = $this->settings[$model->alias][$field]['thumbnailName'];
		if (empty($size)) {
			$thumbnailName = preg_replace('/-?\{size\}-?/', '',$thumbnailName);
		}
		$fileName = str_replace(
			array('{size}', '{filename}', '{primaryKey}'),
			array($size, $pathInfo['filename'], $model->id),
			$thumbnailName
		);
// 		$fileName = str_replace(
// 				array('{size}', '{filename}', '{primaryKey}'),
// 				array($size, $pathInfo['filename'], $model->id),
// 				$this->settings[$model->alias][$field]['thumbnailName']
// 		);
// Edit End R.Ohga

		$destFile = "{$thumbnailPath}{$fileName}.{$thumbnailType}";

		$image->setImageCompressionQuality($this->settings[$model->alias][$field]['thumbnailQuality']);
		$image->setImageFormat($imageFormat);
		if (!$image->writeImage($destFile)) {
			return false;
		}

		$image->clear();
		$image->destroy();
		return true;
	}

	public function _resizePhp(Model $model, $field, $path, $size, $geometry, $thumbnailPath) {
// Edit Start R.Ohga
		$srcFile  = $path;
// 		$srcFile  = $path . $model->data[$model->alias][$field];
// Edit End R.Ohga
		$pathInfo = $this->_pathinfo($srcFile);
		$thumbnailType = $this->settings[$model->alias][$field]['thumbnailType'];

		if (!$thumbnailType || !is_string($thumbnailType)) {
			$thumbnailType = $pathInfo['extension'];
		}

		if (!$thumbnailType) {
			$thumbnailType = 'png';
		}

// Edit Start R.Ohga
// $sizeが空の場合はファイル名に$sizeを使用しないように修正
		$thumbnailName = $this->settings[$model->alias][$field]['thumbnailName'];
		if (empty($size)) {
			$thumbnailName = preg_replace('/-?\{size\}-?/', '',$thumbnailName);
		}
		$fileName = str_replace(
			array('{size}', '{filename}', '{primaryKey}'),
			array($size, $pathInfo['filename'], $model->id),
			$thumbnailName
		);
// 		$fileName = str_replace(
// 				array('{size}', '{filename}', '{primaryKey}'),
// 				array($size, $pathInfo['filename'], $model->id),
// 				$this->settings[$model->alias][$field]['thumbnailName']
// 		);
// Edit End R.Ohga

		$destFile = "{$thumbnailPath}{$fileName}.{$thumbnailType}";

		copy($srcFile, $destFile);
		$src = null;
		$createHandler = null;
		$outputHandler = null;
		switch (strtolower($pathInfo['extension'])) {
			case 'gif':
				$createHandler = 'imagecreatefromgif';
				break;
			case 'jpg':
			case 'jpeg':
				$createHandler = 'imagecreatefromjpeg';
				break;
			case 'png':
				$createHandler = 'imagecreatefrompng';
				break;
			default:
				return false;
		}

		$supportsThumbnailQuality = false;
		$adjustedThumbnailQuality = $this->settings[$model->alias][$field]['thumbnailQuality'];
		switch (strtolower($thumbnailType)) {
			case 'gif':
				$outputHandler = 'imagegif';
				break;
			case 'jpg':
			case 'jpeg':
				$outputHandler = 'imagejpeg';
				$supportsThumbnailQuality = true;
				break;
			case 'png':
				$outputHandler = 'imagepng';
				$supportsThumbnailQuality = true;
				// convert 0 (lowest) - 100 (highest) thumbnailQuality, to 0 (highest) - 9 (lowest) quality (see http://php.net/manual/en/function.imagepng.php)
				$adjustedThumbnailQuality = intval((100 - $this->settings[$model->alias][$field]['thumbnailQuality'])/100*9);
				break;
			default:
				return false;
		}

		if ($src = $createHandler($destFile)) {
			$srcW = imagesx($src);
			$srcH = imagesy($src);

			// determine destination dimensions and resize mode from provided geometry
			if (preg_match('/^\\[[\\d]+x[\\d]+\\]$/', $geometry)) {
				// resize with banding
				list($destW, $destH) = explode('x', substr($geometry, 1, strlen($geometry)-2));
// Edit Start R.Ohga
// 小さい画像を拡大してしまうため修正
				if ($destH > $srcH) {$destH = $srcH;}
				if ($destW > $srcW) {$destW = $srcW;}
// Edit End R.Ohga
				$resizeMode = 'band';
			} elseif (preg_match('/^[\\d]+x[\\d]+$/', $geometry)) {
				// cropped resize (best fit)
				list($destW, $destH) = explode('x', $geometry);
// Edit Start R.Ohga
				if ($destH > $srcH) {$destH = $srcH;}
				if ($destW > $srcW) {$destW = $srcW;}
// Edit End R.Ohga
				$resizeMode = 'best';
			} elseif (preg_match('/^[\\d]+w$/', $geometry)) {
				// calculate heigh according to aspect ratio
				$destW = (int)$geometry-1;
// Edit Start R.Ohga
				if ($destW > $srcW) {$destW = $srcW;}
// Edit End R.Ohga
				$resizeMode = false;
			} elseif (preg_match('/^[\\d]+h$/', $geometry)) {
				// calculate width according to aspect ratio
				$destH = (int)$geometry-1;
// Edit Start R.Ohga
				if ($destH > $srcH) {$destH = $srcH;}
// Edit End R.Ohga
				$resizeMode = false;
			} elseif (preg_match('/^[\\d]+l$/', $geometry)) {
				// calculate shortest side according to aspect ratio
				if ($srcW > $srcH) $destW = (int)$geometry-1;
				else $destH = (int)$geometry-1;
// Edit Start R.Ohga
				if ($destH > $srcH) {$destH = $srcH;}
				if ($destW > $srcW) {$destW = $srcW;}
// Edit End R.Ohga
				$resizeMode = false;
			}
			if (!isset($destW)) $destW = ($destH/$srcH) * $srcW;
			if (!isset($destH)) $destH = ($destW/$srcW) * $srcH;

			// determine resize dimensions from appropriate resize mode and ratio
			if ($resizeMode == 'best') {
				// "best fit" mode
				if ($srcW > $srcH) {
					if ($srcH/$destH > $srcW/$destW) $ratio = $destW/$srcW;
					else $ratio = $destH/$srcH;
				} else {
					if ($srcH/$destH < $srcW/$destW) $ratio = $destH/$srcH;
					else $ratio = $destW/$srcW;
				}
				$resizeW = $srcW*$ratio;
				$resizeH = $srcH*$ratio;
			} else if ($resizeMode == 'band') {
				// "banding" mode
				if ($srcW > $srcH) $ratio = $destW/$srcW;
				else $ratio = $destH/$srcH;
				$resizeW = $srcW*$ratio;
				$resizeH = $srcH*$ratio;
			} else {
				// no resize ratio
				$resizeW = $destW;
				$resizeH = $destH;
			}

// Edit Start R.Ohga
// 余白ができてしまうため修正
			$img = imagecreatetruecolor($resizeW, $resizeH);
// 			$img = imagecreatetruecolor($destW, $destH);
// Edit End R.Ohga
			imagealphablending($img, false);
			imagesavealpha($img, true);
			imagefill($img, 0, 0, imagecolorallocate($img, 255, 255, 255));
// Edit Start R.Ohga
			imagecopyresampled($img, $src, 0, 0, 0, 0, $resizeW, $resizeH, $srcW, $srcH);
// 			imagecopyresampled($img, $src, ($destW-$resizeW)/2, ($destH-$resizeH)/2, 0, 0, $resizeW, $resizeH, $srcW, $srcH);
// Edit End R.Ohga

			if ($supportsThumbnailQuality) {
				$outputHandler($img, $destFile, $adjustedThumbnailQuality);
			} else {
				$outputHandler($img, $destFile);
			}

			return true;
		}
		return false;
	}

	public function _getPath(Model $model, $field) {
		$path = $this->settings[$model->alias][$field]['path'];
		$pathMethod = $this->settings[$model->alias][$field]['pathMethod'];

		if (method_exists($this, $pathMethod)) {
			return $this->$pathMethod($model, $field, $path);
		}

		return $this->_getPathPrimaryKey($model, $field, $path);
	}

	public function _getPathFlat(Model $model, $field, $path) {
// Add Start R.Ohga
// プラグイン名をファイルパスに追加
		$plugin = '';
		if (!empty($this->_behavierOptions['plugin'])) {
			$plugin = $this->_behavierOptions['plugin'] . DS;
		}
// Add End R.Ohga
		$destDir = $path . $plugin;
		$this->_mkPath($model, $field, $destDir);
		return '';
	}

	public function _getPathPrimaryKey(Model $model, $field, $path) {
// Add Start R.Ohga
// プラグイン名をファイルパスに追加
		$plugin = '';
		if (!empty($this->_behavierOptions['plugin'])) {
			$plugin = $this->_behavierOptions['plugin'] . DS;
		}
// Add End R.Ohga
		$destDir = $path . $plugin . $model->id . DS;
		$this->_mkPath($model, $field, $destDir);
		return $model->id;
	}

	public function _getPathRandom(Model $model, $field, $path) {
		$endPath = null;
		$decrement = 0;
		$string = crc32($field . microtime());

// Add Start R.Ohga
// プラグイン名をファイルパスに追加
		$plugin = '';
		if (!empty($this->_behavierOptions['plugin'])) {
			$plugin = $this->_behavierOptions['plugin'] . DS;
		}
// Add End R.Ohga
		for ($i = 0; $i < 3; $i++) {
			$decrement = $decrement - 2;
			$endPath .= sprintf("%02d" . DS, substr('000000' . $string, $decrement, 2));
		}

// Add Start R.Ohga
		$destDir = $path . $plugin . $endPath;
// 		$destDir = $path . $endPath;
// Add End R.Ohga
		$this->_mkPath($model, $field, $destDir);

		return substr($endPath, 0, -1);
	}

	public function _getPathRandomCombined(Model $model, $field, $path) {
		$endPath = null;
		$decrement = 0;
		$string = crc32($field . microtime() . $model->id);

// Add Start R.Ohga
// プラグイン名をファイルパスに追加
		$plugin = '';
		if (!empty($this->_behavierOptions['plugin'])) {
			$plugin = $this->_behavierOptions['plugin'] . DS;
		}
// Add End R.Ohga
		for ($i = 0; $i < 3; $i++) {
			$decrement = $decrement - 2;
			$endPath .= sprintf("%02d" . DS, substr('000000' . $string, $decrement, 2));
		}

// Add Start R.Ohga
		$destDir = $path . $plugin . $endPath;
// 		$destDir = $path . $endPath;
// Add End R.Ohga
		$this->_mkPath($model, $field, $destDir);

		return substr($endPath, 0, -1);
	}

	/**
	 * Download remote file into PHP's TMP dir
	 */
	public function _grab(Model $model, $field, $uri) {
		$socket = new HttpSocket();
		$file = $socket->get($uri, array(), array('redirect' => true));
		$headers = $socket->response['header'];
		$file_name = basename($socket->request['uri']['path']);
		$tmp_file = sys_get_temp_dir() . '/' . $file_name;

		if ($socket->response['status']['code'] != 200) {
			return false;
		}

		if (isset($model->data[$model->alias]['file_name_override'])) {
			$file_name = $model->data[$model->alias]['file_name_override'] . '.' . pathinfo($socket->request['uri']['path'], PATHINFO_EXTENSION);
		}

		$model->data[$model->alias][$field] = array(
			'name' => $file_name,
			'type' => $headers['Content-Type'],
			'tmp_name' => $tmp_file,
			'error' => 1,
			'size' => (isset($headers['content-length']) ? $headers['Content-Length'] : 0),
		);

		$file = file_put_contents($tmp_file, $socket->response['body']);
		if (!$file) {
			return false;
		}

		$model->data[$model->alias][$field]['error'] = 0;
		return true;
	}

	public function _mkPath(Model $model, $field, $destDir) {
		if (!file_exists($destDir)) {
			@mkdir($destDir, $this->settings[$model->alias][$field]['mode'], true);
			@chmod($destDir, $this->settings[$model->alias][$field]['mode']);
		}
		return true;
	}

/**
 * Returns a path based on settings configuration
 *
 * @return string
 **/
	public function _path(Model $model, $fieldName, $options = array()) {
		$defaults = array(
			'isThumbnail' => true,
			'path' => '{ROOT}{DS}',
			'rootDir' => $this->defaults['rootDir'],
		);

		$options = array_merge($defaults, $options);

		foreach ($options as $key => $value) {
			if ($value === null) {
				$options[$key] = $defaults[$key];
			}
		}

		if (!$options['isThumbnail']) {
			$options['path'] = str_replace(array('{size}', '{geometry}'), '', $options['path']);
		}

		$replacements = array(
			'{ROOT}'	=> $options['rootDir'],
			'{primaryKey}'	=> $model->id,
			'{model}'	=> Inflector::underscore($model->alias),
			'{field}'	=> $fieldName,
			'{time}'	=> time(),
			'{microtime}'	=> microtime(),
			'{DS}'		=> DIRECTORY_SEPARATOR,
			'//'		=> DIRECTORY_SEPARATOR,
			'/'			=> DIRECTORY_SEPARATOR,
			'\\'		=> DIRECTORY_SEPARATOR,
		);

		$newPath = Folder::slashTerm(str_replace(
			array_keys($replacements),
			array_values($replacements),
			$options['path']
		));

		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
			if (!preg_match('/^([a-zA-Z]:\\\|\\\\)/', $newPath)) {
				$newPath = $options['rootDir'] . $newPath;
			}
		} elseif ($newPath[0] !== DIRECTORY_SEPARATOR) {
			$newPath = $options['rootDir'] . $newPath;
		}

		$pastPath = $newPath;
		while (true) {
			$pastPath = $newPath;
			$newPath = str_replace(array(
				'//',
				'\\',
				DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR
			), DIRECTORY_SEPARATOR, $newPath);
			if ($pastPath == $newPath) {
				break;
			}
		}

		return $newPath;
	}

	public function _pathThumbnail(Model $model, $field, $params = array()) {
		return str_replace(
			array('{size}', '{geometry}'),
			array($params['size'], $params['geometry']),
			$params['thumbnailPath']
		);
	}

	public function _createThumbnails(Model $model, $field, $path, $thumbnailPath) {
		$isImage = $this->_isImage($model, $this->runtime[$model->alias][$field]['type']);
		$isMedia = $this->_isMedia($model, $this->runtime[$model->alias][$field]['type']);
		$createThumbnails = $this->settings[$model->alias][$field]['thumbnails'];
		$hasThumbnails = !empty($this->settings[$model->alias][$field]['thumbnailSizes']);

		if (($isImage || $isMedia) && $createThumbnails && $hasThumbnails) {
			$method = $this->settings[$model->alias][$field]['thumbnailMethod'];

			foreach ($this->settings[$model->alias][$field]['thumbnailSizes'] as $size => $geometry) {
				$thumbnailPathSized = $this->_pathThumbnail($model, $field, compact(
					'geometry', 'size', 'thumbnailPath'
				));
				$this->_mkPath($model, $field, $thumbnailPathSized);

				$valid = false;
				if (method_exists($model, $method)) {
					$valid = $model->$method($model, $field, $path, $size, $geometry, $thumbnailPathSized);
				} elseif (method_exists($this, $method)) {
					$valid = $this->$method($model, $field, $path, $size, $geometry, $thumbnailPathSized);
				} else {
					CakeLog::error(sprintf('Model %s, Field %s: Invalid thumbnailMethod %s', $model->alias, $field, $filePath));
					$db = $model->getDataSource();
					$db->rollback();
					throw new Exception("Invalid thumbnailMethod %s", $method);
				}

				if (!$valid) {
					$model->invalidate($field, 'resizeFail');
				}
			}
		}
	}

	public function _isImage(Model $model, $mimetype) {
		return in_array($mimetype, $this->_imageMimetypes);
	}

	public function _isURI($url_str) {
		return (filter_var($url_str, FILTER_VALIDATE_URL) ? true : false);
	}

	public function _isMedia(Model $model, $mimetype) {
		return in_array($mimetype, $this->_mediaMimetypes);
	}

	public function _getMimeType($filePath) {
		if (class_exists('finfo')) {
			$finfo = new finfo(defined('FILEINFO_MIME_TYPE') ? FILEINFO_MIME_TYPE : FILEINFO_MIME);
			return $finfo->file($filePath);
		}

		if (function_exists('exif_imagetype') && function_exists('image_type_to_mime_type')) {
			$mimetype = image_type_to_mime_type(exif_imagetype($filePath));
			if ($mimetype !== false) {
				return $mimetype;
			}
		}

		if (function_exists('mime_content_type')) {
			return mime_content_type($filePath);
		}

		return 'application/octet-stream';
	}

// Edit Start R.Ohga
// 削除したアップロードファイルに関連するファイルが削除されるように修正（サムネイル等）
// 	public function _prepareFilesForDeletion(Model $model, $field, $data, $options) {
// 		if (!strlen($data[$model->alias][$field])) return $this->__filesToRemove;

// 		$dir = $data[$model->alias][$options['fields']['dir']];
// 		$filePathDir = $this->settings[$model->alias][$field]['path'] . $dir . DS;
// 		$filePath = $filePathDir.$data[$model->alias][$field];
// 		$pathInfo = $this->_pathinfo($filePath);

// 		if (!isset($this->__filesToRemove[$model->alias])) {
// 			$this->__filesToRemove[$model->alias] = array();
// 		}

// 		$this->__filesToRemove[$model->alias][] = $filePath;
// 		$this->__foldersToRemove[$model->alias][] = $dir;

// 		$createThumbnails = $options['thumbnails'];
// 		$hasThumbnails = !empty($options['thumbnailSizes']);

// 		if (!$createThumbnails || !$hasThumbnails) {
// 			return $this->__filesToRemove;
// 		}

// 		$DS = DIRECTORY_SEPARATOR;
// 		$mimeType = $this->_getMimeType($filePath);
// 		$isMedia = $this->_isMedia($model, $mimeType);
// 		$isImagickResize = $options['thumbnailMethod'] == 'imagick';
// 		$thumbnailType = $options['thumbnailType'];

// 		if ($isImagickResize) {
// 			if ($isMedia) {
// 				$thumbnailType = $options['mediaThumbnailType'];
// 			}

// 			if (!$thumbnailType || !is_string($thumbnailType)) {
// 				try {
// 					$srcFile = $filePath;
// 					$image    = new imagick();
// 					if ($isMedia) {
// 						$image->setResolution(300, 300);
// 						$srcFile = $srcFile.'[0]';
// 					}

// 					$image->readImage($srcFile);
// 					$thumbnailType = $image->getImageFormat();
// 				} catch (Exception $e) {
// 					$thumbnailType = 'png';
// 				}
// 			}
// 		} else {
// 			if (!$thumbnailType || !is_string($thumbnailType)) {
// 				$thumbnailType = $pathInfo['extension'];
// 			}

// 			if (!$thumbnailType) {
// 				$thumbnailType = 'png';
// 			}
// 		}

// 		foreach ($options['thumbnailSizes'] as $size => $geometry) {
// 			$fileName = str_replace(
// 				array('{size}', '{filename}', '{primaryKey}', '{time}', '{microtime}'),
// 				array($size, $pathInfo['filename'], $model->id, time(), microtime()),
// 				$options['thumbnailName']
// 			);

// 			$thumbnailPath = $options['thumbnailPath'];
// 			$thumbnailPath = $this->_pathThumbnail($model, $field, compact(
// 				'geometry', 'size', 'thumbnailPath'
// 			));

// 			$thumbnailFilePath = "{$thumbnailPath}{$dir}{$DS}{$fileName}.{$thumbnailType}";
// 			$this->__filesToRemove[$model->alias][] = $thumbnailFilePath;
// 		}
// 		return $this->__filesToRemove;
// 	}
	public function _prepareFilesForDeletion(Model $model, $field, $data, $options) {
		$Upload = ClassRegistry::init('Upload');
		if ($model->alias == 'Upload') {
			$uploadData = $Upload->findById($model->id);
		} else {
			$uploadId = $data[$model->alias][$field];
			$uploadData = $Upload->findById($uploadId);
		}

		$dir = $uploadData['Upload']['file_path'];
		$filePathDir = $this->settings[$model->alias][$field]['path'] . $dir;
		$filePath = $filePathDir.$uploadData['Upload']['physical_file_name'];
		$pathInfo = $this->_pathinfo($filePath);

		if (!isset($this->__filesToRemove[$model->alias])) {
			$this->__filesToRemove[$model->alias] = array();
		}

		$this->__filesToRemove[$model->alias][] = $filePath;
		if ($this->settings[$model->alias][$field]['pathMethod'] == '_getPathRandom'
				|| $this->settings[$model->alias][$field]['pathMethod'] == '_getPathRandomCombined') {
			$filePathArr = explode('/', $dir);
			$dir = $filePathArr[0] . DS . $filePathArr[1] . DS;
		}

		$dirArray = glob($filePathDir.$uploadData['Upload']['id'].'-*.'.$uploadData['Upload']['extension']);
		foreach ($dirArray as $moreImagePath) {
			$this->__filesToRemove[$model->alias][] = $moreImagePath;
		}

		return $this->__filesToRemove;
	}
// Edit End R.Ohga

	public function _getField($check) {
		$field_keys = array_keys($check);
		return array_pop($field_keys);
	}

	public function _pathinfo($filename) {
		$pathInfo = pathinfo($filename);

		if (!isset($pathInfo['extension']) || !strlen($pathInfo['extension'])) {
			$pathInfo['extension'] = '';
		}

		// PHP < 5.2.0 doesn't include 'filename' key in pathinfo. Let's try to fix this.
		if (empty($pathInfo['filename'])) {
			$pathInfo['filename'] = basename($pathInfo['basename'], '.' . $pathInfo['extension']);
		}
		return $pathInfo;
	}

// Add Start R.Ohga
/**
 * バリデート情報設定
 *
 * @param array $fileType ファイルタイプ
 * @return void
 */
	public function setValidate($fileType) {
		$validate = array();
		if ($fileType == 'image') {
			$allowExtension = array('gif', 'jpeg', 'png', 'jpg');
			$filiSize = NC_UPLOAD_MAX_SIZE_IMAGE;
		} else {
			$Config = ClassRegistry::init('Config');
			$conditions = array(
				'Config.name' => 'allow_extension'
			);
			$params = array(
				'fields' => array(
					'Config.name',
					'Config.value'
				),
				'conditions' => $conditions,
			);
			$allowExtension = $Config->find('all', $params);
			$allowExtension = explode(',', $allowExtension['allow_extension']);
			$filiSize = NC_UPLOAD_MAX_SIZE_ATTACHMENT;
		}

		$validate['file_name']['extension'] = array(
			'rule' => array('extension', $allowExtension),
			'message' => __('Invalid extension.')
		);
		$validate['file_size']['filesize'] = array(
			'rule' => array('comparison', '<=', $filiSize),
			'message' => __('File size too large. Max %u byte.', $filiSize)
		);
		return $validate;
	}

/**
 * 権限テーブルの容量制限チェック
 *
 * @param array $check
 * @return boolean
 * @since   v 3.0.0.0
 */
	protected function _checkFilesizeLimit($check) {
		$Authority = ClassRegistry::init('Authority');
		$user = Configure::read(NC_SYSTEM_KEY.'.user');
		$userId = isset($userId) ? $user['id'] : 0;
		$filesizeSum = $this->findFilesizeSumByUserId($userId);

		$authority = $Authority->findById($user['authority_id']);
		$check = array_shift($check);
		if ($filesizeSum + $check > $authority['Authority']['max_size']) {
			return false;
		}
		return true;
	}

/**
 * 不正文字使用チェック
 *
 * @param int $check
 * @param string $regex
 * @return boolean
 * @since   v 3.0.0.0
 */
	protected function _invalidCharacter($check, $regex) {
		$check = array_shift($check);
		if (!is_string($regex) || preg_match($regex, $check)) {
			return false;
		}
		return true;
	}

/**
 * 拡張子以外の入力チェック
 *
 * @param array $check
 * @return boolean
 * @since   v 3.0.0.0
 */
	protected function _notEmptyExceptExtension($check) {
		$check = array_shift($check);
		preg_match('/(.*)\.(.*)$/', $check, $match);

		if (empty($match[1])) {
			return false;
		}
		return true;
	}
// Add End R.Ohga

}
