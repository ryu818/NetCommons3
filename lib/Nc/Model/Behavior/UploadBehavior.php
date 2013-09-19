<?php
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
App::uses('UploadException', 'Upload.Lib/Error/Exception');
App::uses('HttpSocket', 'Network/Http');
class UploadBehavior extends ModelBehavior {

	public $defaults = array(
		'rootDir' => null,
// Edit Start Ryuji.M
		'pathMethod' => 'flat',					// useUploadModel=falseの場合は、flatのままだと、uploadモデルのファイル名と同名のファイルがつきかねないので、他を用いること。
		//'pathMethod' => 'primaryKey',
		'path' => '{ROOT}',
		//'path' => '{ROOT}webroot{DS}files{DS}{model}{DS}{field}{DS}',
		'fields' => array('dir' => 'file_path', 'type' => 'file_type', 'size' => 'file_size'),
		// 'fields' => array('dir' => 'dir', 'type' => 'type', 'size' => 'size'),
// Edit End Ryuji.M
		
		'mimetypes' => array(),
		'extensions' => array(),
		'maxSize' => 2097152,
		'minSize' => 8,
		'maxHeight' => 0,
		'minHeight' => 0,
		'maxWidth' => 0,
		'minWidth' => 0,
		'thumbnails' => true,
		'thumbnailMethod' => 'imagick',
		'thumbnailName' => null,
		'thumbnailPath' => null,
// Edit Start Ryuji.M
		//'thumbnailPrefixStyle' => true,
		'thumbnailPrefixStyle' => false,		// この初期値を変更してしまうとダウンロードできなくなるため変更不可
// Edit End Ryuji.M
		'thumbnailQuality' => 75,
		'thumbnailSizes' => array(),
		'thumbnailType' => false,
		'deleteOnUpdate' => false,
		'mediaThumbnailType' => 'png',
		'saveDir' => true,
		'deleteFolderOnDelete' => false,
// Edit Start Ryuji.M
		'mode' => NC_UPLOAD_FOLDER_MODE,
		//'mode' => 0777,
		'fileType' => 'file',
		'useUploadModel'	=> true,		// Uploadモデルを使用するかどうか（ファイル名を[[アップロードID].[拡張子]]形式に変換するかどうか）
		'useUploadLinkModel'=> true,		// UploadLinkモデルを使用するかどうか
		'plugin' => null,
		'contentId' => 0,
		'uniqueIdName' => null,
		'modelName' => null,
		'fieldName' => null,
		'accessHierarchy'	=> 0,			// UploadLink.access_hierarchy登録用
		'downloadPassword'	=> '',			// UploadLink.download_password登録用
		'checkComponentAction'=> 'Download',// UploadLink.check_component_action登録用
		'isDeleteFromLibrary' => true,		// ライブラリー一覧から削除させるかどうか。
		'isWysiwyg' => null,				// WYSIWYGからの登録かどうかの判断用 nullの場合、カラムがtextならばtrue、それ以外はfalseとする。
// Edit End Ryuji.M
	);

	protected $_imageMimetypes = array(
		'image/bmp',
		'image/gif',
		'image/jpeg',
		'image/pjpeg',
		'image/png',
// Add Start R.Ohga
// IE6-8では.pngの画像をアップロードするとimage/x-pngとなるため
		'image/x-png',
// Add End R.Ohga
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
	
// Add Start Ryuji.M

/**
 * アップロードファイルリスト
 * 	$uploadFileNames[$field] = newFileName
 *
 * @var array
 */
	public $uploadFileNames = array();

	protected $uploadUserId = null;
	
	private $__extensionType = null;
// Add End Ryuji.M

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
// Add Start Ryuji.M
		$user = Configure::read(NC_SYSTEM_KEY.'.user');
		$this->setUploadUserId($model, $user['id']);
// Add End Ryuji.M
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
// Edit Start Ryuji.M
		$this->defaults['rootDir'] = NC_UPLOADS_DIR;
		// fieldがrevision_group_idならば、強制的にisWysiwyg=trueとする。
		$this->defaults['isWysiwyg'] = ($field == 'revision_group_id') ? true : false;
		if ($this->defaults['isWysiwyg']) {
			// wysiwygのデフォルト値を設定
			$this->defaults['useUploadModel'] = false;
			$this->defaults['modelName'] = 'Revision';
			$this->defaults['fieldName'] = 'content';
			$this->defaults['uniqueIdName'] = 'revision_group_id';
		}
		
		//$this->defaults['rootDir'] = ROOT . DS . APP_DIR . DS;
// Edit End Ryuji.M
		if (!isset($this->settings[$model->alias][$field])) {
			$options = array_merge($this->defaults, (array) $options);

// Edit Start Ryuji.M
			$options['thumbnailMethod'] = $this->_getEnableThumnailMethod($model, $options['thumbnailMethod']);
			$this->_setFileTypeSetting($model, $field, $options);
			$plugin = isset($options['plugin']) ? $options['plugin'] : Configure::read(NC_SYSTEM_KEY.'.plugin');
			if(empty($this->settings[$model->alias][$field]['plugin']) && isset($plugin)) {
				$options['plugin'] = $plugin;
			}
			$contentId = !empty($options['contentId']) ? $options['contentId'] : Configure::read(NC_SYSTEM_KEY.'.content_id');
			if(empty($this->settings[$model->alias][$field]['contentId']) && isset($contentId)) {
				$options['contentId'] = $contentId;
			}
			// HACK: Remove me in next major version
// 			if (!empty($options['thumbsizes'])) {
// 				$options['thumbnailSizes'] = $options['thumbsizes'];
// 			}

// 			if (!empty($options['prefixStyle'])) {
// 				$options['thumbnailPrefixStyle'] = $options['prefixStyle'];
// 			}
			// ENDHACK
// Edit End Ryuji.M

			$options['fields'] += $this->defaults['fields'];
			if ($options['rootDir'] === null) {
				$options['rootDir'] = $this->defaults['rootDir'];
			}
// Add Start Ryuji.M
			$tempPath = $options['path'];
// Add End Ryuji.M
			if ($options['thumbnailName'] === null) {
				if ($options['thumbnailPrefixStyle']) {
					$options['thumbnailName'] = '{size}_{filename}';
				} else {
					$options['thumbnailName'] = '{filename}_{size}';
				}
			}

			if ($options['thumbnailPath'] === null) {
				$options['thumbnailPath'] = Folder::slashTerm($this->_path($model, $field, array(
					'isThumbnail' => true,
					'path' => $options['path'],
					'rootDir' => $options['rootDir'].$options['plugin']. DS,
// Edit Start Ryuji.M
					//'plugin' => $options['plugin'],
					'contentId' => $options['contentId'],
// Edit End Ryuji.M
				)));
			} else {
				$options['thumbnailPath'] = Folder::slashTerm($this->_path($model, $field, array(
					'isThumbnail' => true,
					'path' => $options['thumbnailPath'],
					'rootDir' => $options['rootDir'].$options['plugin']. DS,
// Edit Start Ryuji.M
					//'plugin' => $options['plugin'],
					'contentId' => $options['contentId'],
// Edit End Ryuji.M
				)));
			}

			$options['path'] = Folder::slashTerm($this->_path($model, $field, array(
				'isThumbnail' => false,
				'path' => $options['path'],
				'rootDir' => $options['rootDir'].$options['plugin']. DS,
// Edit Start Ryuji.M
				//'plugin' => $options['plugin'],
				'contentId' => $options['contentId'],
// Edit End Ryuji.M
			)));
// Edit Start Ryuji.M
			$options['appendPath'] = Folder::slashTerm($this->_path($model, $field, array(
				'isThumbnail' => false,
				'path' => $tempPath,
				'rootDir' => '',
				//'plugin' => $options['plugin'],
				'contentId' => $options['contentId'],
			)));
			if (!in_array($options['thumbnailMethod'], $this->_resizeMethods)) {
				$options['thumbnailMethod'] = 'imagick';
			}
			$options['thumbnailSizes']['library'] = NC_UPLOAD_LIBRARY_THUMBNAIL_WIDTH_RESIZE_MODE;	// 削除時では、beforeSavが呼ばれないため、ここで初期値設定
// Edit End Ryuji.M
			if (!in_array($options['pathMethod'], $this->_pathMethods)) {
				$options['pathMethod'] = 'primaryKey';
			}
			$options['pathMethod'] = '_getPath' . Inflector::camelize($options['pathMethod']);
			$options['thumbnailMethod'] = '_resize' . Inflector::camelize($options['thumbnailMethod']);
			$this->settings[$model->alias][$field] = $options;
		}
	}

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
// Edit Start Ryuji.M
		//if (empty($this->settings[$model->alias][$field])) {
		//	$this->_setupField($model, $field, array());
		//}

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

		if(!isset($data['path'])) {
			$data['path'] = $this->defaults['path'];
		}
		if (!isset($data['thumbnailPath'])) {
			$data['thumbnailPath'] = $this->defaults['thumbnailPath'];
		}
		$data = $data + $this->settings[$model->alias][$field];
		unset($this->settings[$model->alias][$field]);
		$this->_setupField($model, $field, $data);
		// $this->settings[$model->alias][$field] = $data + $this->settings[$model->alias][$field];
// Edit End Ryuji.M
	}

/**
 * Before save method. Called before all saves
 *
 * Handles setup of file uploads
 *
 * @param AppModel $model Model instance
 * @return boolean
 */
	public function beforeSave(Model $model) {
		$this->_removingOnly = array();
		foreach ($this->settings[$model->alias] as $field => $options) {
			if (!isset($model->data[$model->alias][$field])) continue;
			if (!is_array($model->data[$model->alias][$field])) continue;

			$this->runtime[$model->alias][$field] = $model->data[$model->alias][$field];

// Add Start Ryuji.M
			// ライブラリーのサムネイル追加処理(設定しないでも追加する)
			$tmp = isset($this->runtime[$model->alias][$field]['tmp_name']) ? $this->runtime[$model->alias][$field]['tmp_name'] : null;
			if(!empty($tmp) && $this->settings[$model->alias][$field]['fileType'] == 'image') {
				list($width, $height) = getimagesize($tmp);
				if ($width > $height) {
					$libraryGeometry = NC_UPLOAD_LIBRARY_THUMBNAIL_HEIGHT_RESIZE_MODE;
				} else {
					$libraryGeometry = NC_UPLOAD_LIBRARY_THUMBNAIL_WIDTH_RESIZE_MODE;
				}
			} else {
				$libraryGeometry = NC_UPLOAD_LIBRARY_THUMBNAIL_WIDTH_RESIZE_MODE;
			}
			$this->settings[$model->alias][$field]['thumbnailSizes'] = $options['thumbnailSizes'] = array_merge((array) $this->settings[$model->alias][$field]['thumbnailSizes'], 
					array('library' => $libraryGeometry));
// Add End Ryuji.M

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
// Edit Start Ryuji.M
// field以外のカラムにデータが入っていても、fieldのみのupdateになってしまうため
// その他のデータとマージするように修正。
					$model->data[$model->alias] = array_merge($model->data[$model->alias], array(
						$field => null,
						$options['fields']['type'] => null,
						$options['fields']['size'] => null,
						$options['fields']['dir'] => null,
					));
// 					$model->data[$model->alias] = array(
// 						$field => null,
// 						$options['fields']['type'] => null,
// 						$options['fields']['size'] => null,
// 						$options['fields']['dir'] => null,
// 					);
// Edit End Ryuji.M
					$this->_removingOnly[$field] = true;
					continue;
				} else {
					$model->data[$model->alias][$field] = array(
						$field => null,
						$options['fields']['type'] => null,
						$options['fields']['size'] => null,
					);
				}
			} elseif (!isset($model->data[$model->alias][$field]['name'])
			|| !strlen($model->data[$model->alias][$field]['name'])) {
				// if field is empty, don't delete/nullify existing file
				unset($model->data[$model->alias][$field]);
				continue;
			}

// Edit Start Ryuji.M
			if(!is_array($this->runtime[$model->alias][$field])) {
				$newFileName = $this->runtime[$model->alias][$field]['Id'];
				// uploadに存在するかどうかのチェック
				$Upload = ClassRegistry::init('Upload');
				$upload = $Upload->findById(substr($newFileName, 0, strpos($newFileName, '.')));
				if(!isset($upload['Upload'])) {
					$model->invalidate($field, __('File not found.'));
					return false;
				}
			} else if ($this->settings[$model->alias][$field]['useUploadModel']) {
				// 当該カラムのファイル情報をUploadテーブルに登録
				$beforeUploadId = null;
				$beforeFileName = isset($data[$model->alias][$field]) ? $data[$model->alias][$field] : null;
				if(isset($beforeFileName)) {
					$beforeUploadId = substr($beforeFileName, 0, strpos($beforeFileName, '.'));
				}
				$newFileName = $this->saveUpload($model, $field, $this->runtime[$model->alias][$field], $this->settings[$model->alias][$field], $beforeUploadId);
				if ($newFileName === false) {
					return false;
				}
			}

			$model->data[$model->alias] = array_merge($model->data[$model->alias], array(
				//$field => $this->runtime[$model->alias][$field]['name'],
				$field => isset($newFileName) ? $newFileName : $this->runtime[$model->alias][$field]['name'],
				$options['fields']['type'] => $this->runtime[$model->alias][$field]['type'],
				$options['fields']['size'] => $this->runtime[$model->alias][$field]['size']
			));
			$this->uploadFileNames[$field] = $model->data[$model->alias][$field];
// Edit End Ryuji.M
		}
		return true;
	}

	/**
	 * Transform Model.field value like as PHP upload array (name, tmp_name)
	 * for UploadBehavior plugin processing.
	 */
	function beforeValidate(Model $model) {
		foreach ($this->settings[$model->alias] as $field => $options) {
// Add Start Ryuji.M
			// Model[$field][file]があれば、そちらをfileエレメントとしてバリデート(プレビュー後に登録を実装するため)
			if(isset($model->data[$model->alias][$field], $model->data[$model->alias][$field]['file']) && is_array($model->data[$model->alias][$field]['file'])) {
				$model->data[$model->alias][$field] = $model->data[$model->alias][$field]['file'];
			}
// Add End Ryuji.M
			if (!empty($model->data[$model->alias][$field])
				AND $this->_isURI($model->data[$model->alias][$field])) {
				$uri = $model->data[$model->alias][$field];
				if (!$this->_grab($model, $field, $uri)) {
					$model->invalidate($field, __d('upload', 'File was not downloaded.'));
					return false;
				}
			}
		}
		return true;
	}

	public function afterSave(Model $model, $created) {
		$temp = array($model->alias => array());
// Edit Start Ryuji.M
// 削除して登録することでdeleteOnUpdateがtrueの場合にアップロードした画像が削除されないように修正。
		$result = true;
		if (!empty($this->__filesToRemove[$model->alias])) {
			$result = array();
			foreach ($this->__filesToRemove[$model->alias] as $file) {
				$result[] = $this->unlink($file);
			}
		}
// Edit End Ryuji.M
		foreach ($this->settings[$model->alias] as $field => $options) {
// Edit Start Ryuji.M
			/////if (!in_array($field, array_keys($model->data[$model->alias]))) continue;
			/////if (empty($this->runtime[$model->alias][$field])) continue;
			if (isset($this->_removingOnly[$field])) continue;
			
			// モデルUserならば、id情報でUpload.user_id更新
			if ($this->settings[$model->alias][$field]['useUploadModel'] && !empty($model->id) && $model->alias == 'User' && $created) {
				$Upload = ClassRegistry::init('Upload');
				$data = array(
					'Upload.user_id' => "'".$model->id. "'",
				);
				$haystack = $model->data[$model->alias][$field];
				$conditions = array('Upload.id' => substr($haystack, 0, strpos($haystack, '.')));
				if(!$Upload->updateAll($data, $conditions)) {
					return false;
				}
			}
	
			if ($this->settings[$model->alias][$field]['useUploadLinkModel'] && !empty($model->id)) {
				if (!$this->saveUploadLink($model, $field)) {
					return false;
				}
			}
			if (!in_array($field, array_keys($model->data[$model->alias]))) continue;
			if (empty($this->runtime[$model->alias][$field])) continue;
// Edit End Ryuji.M

			$tempPath = $this->_getPath($model, $field);

			$path = $this->settings[$model->alias][$field]['path'];
			$thumbnailPath = $this->settings[$model->alias][$field]['thumbnailPath'];

			if (!empty($tempPath)) {
				$path .= $tempPath . DS;
				$thumbnailPath .= $tempPath . DS;
			}
// Add Start Ryuji.M
// pathの最後にDSを付与
			if (!empty($tempPath)) {
				$tempPath = !empty($this->settings[$model->alias][$field]['appendPath']) ? $this->settings[$model->alias][$field]['appendPath'] . $tempPath .DS : $tempPath. DS;
			} else if(!empty($this->settings[$model->alias][$field]['appendPath'])) {
				$tempPath = $this->settings[$model->alias][$field]['appendPath'];
			}
			if($tempPath == DS) {
				$tempPath = '';
			}
// Add End Ryuji.M
			$tmp = $this->runtime[$model->alias][$field]['tmp_name'];
			$filePath = $path . $model->data[$model->alias][$field];
			if (!$this->handleUploadedFile($model->alias, $field, $tmp, $filePath)) {
				$model->invalidate($field, __('Unable to move the uploaded file to %s', $filePath));
				return false;
				//CakeLog::error(sprintf('Model %s, Field %s: Unable to move the uploaded file to %s', $model->alias, $field, $filePath));
				//$model->invalidate($field, sprintf('Unable to move the uploaded file to %s', $filePath));
				//$db = $model->getDataSource();
				//$db->rollback();
				//throw new UploadException('Unable to upload file');
			}

			$this->_createThumbnails($model, $field, $path, $thumbnailPath);
			if ($model->hasField($options['fields']['dir'])) {
				if ($created && $options['pathMethod'] == '_getPathFlat') {
				} else if ($options['saveDir']) {
					$temp[$model->alias][$options['fields']['dir']] = "'{$tempPath}'";
				}
			}
			
// Add Start Ryuji.M
			if ($this->settings[$model->alias][$field]['useUploadModel'] &&
				(!empty($tempPath) || (isset($this->settings[$model->alias][$field]['thumbnailSizes']['original']) && $this->runtime[$model->alias][$field]['size'] != filesize($filePath)))) {
				// Upload update
				if(empty($Upload)) {
					$Upload = ClassRegistry::init('Upload');
				}
				$data = array(
					'Upload.file_path' => "'".$tempPath. "'",
					'Upload.file_size' => "'".filesize($filePath). "'",
				);
				$haystack = $model->data[$model->alias][$field];
				$conditions = array('Upload.id' => substr($haystack, 0, strpos($haystack, '.')));
				if(!$Upload->updateAll($data, $conditions)) {
					return false;
				}
			}
// Add End Ryuji.M
		}

		if (!empty($temp[$model->alias]) && $model->id) {
			$model->updateAll($temp[$model->alias], array(
				$model->alias.'.'.$model->primaryKey => $model->id
			));
		}

		if (empty($this->__filesToRemove[$model->alias])) return true;
// Edit End Ryuji.M 上部へ移動
// 		foreach ($this->__filesToRemove[$model->alias] as $file) {
// 			$result[] = $this->unlink($file);
// 		}
// Edit End Ryuji.M
		return $result;
	}

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
// Add End Ryuji.M
			if($model->useTable == 'uploads') {
				$data[$model->alias][$field] = $model->id . '.' . $data[$model->alias]['extension'];
			}
// Add End Ryuji.M
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
		if (!$requireUpload && (!isset($check[$field]['error']) || $check[$field]['error'] === UPLOAD_ERR_NO_FILE)) {
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
		if (!$requireUpload && (!isset($check[$field]['error']) || $check[$field]['error'] === UPLOAD_ERR_NO_FILE)) {
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
		if (!$requireUpload && (!isset($check[$field]['error']) || $check[$field]['error'] === UPLOAD_ERR_NO_FILE)) {
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
		if (!$requireUpload && (!isset($check[$field]['error']) || $check[$field]['error'] === UPLOAD_ERR_NO_FILE)) {
			return true;
		}

// Edit Start Ryuji.M
		$ret = true;
		if (!isset($check[$field]['type']) || !strlen($check[$field]['type'])) {
			$ret = false;
		} else {
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
		if(!$ret) {
			return __('Invalid MimeType.');
		}
		return $ret;
		
// 		// Non-file uploads also mean the mimetype is invalid
// 		if (!isset($check[$field]['type']) || !strlen($check[$field]['type'])) {
// 			return false;
// 		}

// 		// Sometimes the user passes in a string instead of an array
// 		if (is_string($mimetypes)) {
// 			$mimetypes = array($mimetypes);
// 		}

// 		foreach ($mimetypes as $key => $value) {
// 			if (!is_int($key)) {
// 				$mimetypes = $this->settings[$model->alias][$field]['mimetypes'];
// 				break;
// 			}
// 		}

// 		if (empty($mimetypes)) $mimetypes = $this->settings[$model->alias][$field]['mimetypes'];

// 		return in_array($check[$field]['type'], $mimetypes);
// Edit End Ryuji.M
		
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
		if (!$requireUpload && (!isset($check[$field]['error']) || $check[$field]['error'] === UPLOAD_ERR_NO_FILE)) {
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
		if (!$requireUpload && (!isset($check[$field]['error']) || $check[$field]['error'] === UPLOAD_ERR_NO_FILE)) {
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
		if (!$requireUpload && (!isset($check[$field]['error']) || $check[$field]['error'] === UPLOAD_ERR_NO_FILE)) {
			return true;
		}
// Edit Start Ryuji.M
		$ret = true;
		if (!$size) $size = $this->settings[$model->alias][$field]['maxSize'];
		if (!isset($check[$field]['size']) || !strlen($check[$field]['size'])) {
			$ret = false;
		} else {
			$ret = $check[$field]['size'] <= $size;
		}
		if(!$ret) {
			App::uses('CakeNumber', 'Utility');
			return __('File size too large. Max %s.', CakeNumber::toReadableSize($size));
		}
		return $ret;
		
		//if (!isset($check[$field]['size']) || !strlen($check[$field]['size'])) {
		//	return false;
		//}
		//if (!$size) $size = $this->settings[$model->alias][$field]['maxSize'];
		//return $check[$field]['size'] <= $size;
// Edit End Ryuji.M
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
		if (!$requireUpload && (!isset($check[$field]['error']) || $check[$field]['error'] === UPLOAD_ERR_NO_FILE)) {
			return true;
		}

		// Non-file uploads also mean the size is too small
// Edit Start Ryuji.M
		$ret = true;
		if (!$size) $size = $this->settings[$model->alias][$field]['minSize'];
		if (!isset($check[$field]['size']) || !strlen($check[$field]['size'])) {
			$ret = false;
		} else {
			$ret = $check[$field]['size'] >= $size;
		}
		if(!$ret) {
			App::uses('CakeNumber', 'Utility');
			return __('File size too large. Min %s.', CakeNumber::toReadableSize($size));
		}
		return $ret;
		
//		if (!isset($check[$field]['size']) || !strlen($check[$field]['size'])) {
// 			return false;
// 		}

// 		if (!$size) $size = $this->settings[$model->alias][$field]['minSize'];
// 		return $check[$field]['size'] >= $size;
// Edit End Ryuji.M
		



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
		if (!$requireUpload && (!isset($check[$field]['error']) || $check[$field]['error'] === UPLOAD_ERR_NO_FILE)) {
			return true;
		}
// Edit Start Ryuji.M
		// Non-file uploads also mean the extension is invalid
		$ret = true;
		if (!isset($check[$field]['name']) || !strlen($check[$field]['name'])) {
			$ret = false;
		} else {
			// Sometimes the user passes in a string instead of an array
			if(is_null($extensions)) {
				$extensions = array();
			}
			else if (is_string($extensions)) {
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
// tar.gz対応
			if(preg_match('/\.gz$/', $pathInfo['extension'])) {
				$pathInfo['extension'] = 'gz';
			}
			
			$extensions = array_map('strtolower', $extensions);
			$ret = in_array(strtolower($pathInfo['extension']), $extensions);
		}
		if(!$ret) {
			switch($this->__extensionType) {
				case 'imagick':
					$mes = __('Only BMP,GIF,JPEG,PNG files are allowed as image files.');
					break;
				case 'php':
					$mes = __('Only GIF,JPEG,PNG files are allowed as image files.');
					break;
				case 'compression':
					$mes = __('Only ZIP,TAR,TGZ,GZ files are allowed as files.');
					break;
				default:
					$mes = __('Invalid extension.');
			}
			return $mes;
		}
		return $ret;
// 		if (!isset($check[$field]['name']) || !strlen($check[$field]['name'])) {
// 			return false;
// 		}

// 		// Sometimes the user passes in a string instead of an array
// 		if (is_string($extensions)) {
// 			$extensions = array($extensions);
// 		}

// 		// Sometimes a user does not specify any extensions in the validation rule
// 		foreach ($extensions as $key => $value) {
// 			if (!is_int($key)) {
// 				$extensions = $this->settings[$model->alias][$field]['extensions'];
// 				break;
// 			}
// 		}

// 		if (empty($extensions)) $extensions = $this->settings[$model->alias][$field]['extensions'];
// 		$pathInfo = $this->_pathinfo($check[$field]['name']);
// 		$extensions = array_map('strtolower', $extensions);
// 		return in_array(strtolower($pathInfo['extension']), $extensions);
// Edit End Ryuji.M
		
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
		if (!$requireUpload && (!isset($check[$field]['error']) || $check[$field]['error'] === UPLOAD_ERR_NO_FILE)) {
			return true;
		}
// Edit Start Ryuji.M
		// Non-file uploads also mean the height is too big
		$ret = true;
		if (!$height) $height = $this->settings[$model->alias][$field]['minHeight'];
		if (!isset($check[$field]['tmp_name']) || !strlen($check[$field]['tmp_name'])) {
			$ret = false;
		} else {
			list($imgWidth, $imgHeight) = getimagesize($check[$field]['tmp_name']);
			$ret = $height > 0 && $imgHeight >= $height;
		}
		if(!$ret) {
			return __('File height must be larger than %u.', $height);
		}
		return $ret;
// 		if (!isset($check[$field]['tmp_name']) || !strlen($check[$field]['tmp_name'])) {
// 			return false;
// 		}

// 		if (!$height) $height = $this->settings[$model->alias][$field]['minHeight'];

// 		list($imgWidth, $imgHeight) = getimagesize($check[$field]['tmp_name']);
// 		return $height > 0 && $imgHeight >= $height;
// Edit End Ryuji.M
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
		if (!$requireUpload && (!isset($check[$field]['error']) || $check[$field]['error'] === UPLOAD_ERR_NO_FILE)) {
			return true;
		}
// Edit Start Ryuji.M
		// Non-file uploads also mean the height is too big
		$ret = true;
		if (!$height) $height = $this->settings[$model->alias][$field]['maxHeight'];
		if (!isset($check[$field]['tmp_name']) || !strlen($check[$field]['tmp_name'])) {
			$ret = false;
		} else {
			list($imgWidth, $imgHeight) = getimagesize($check[$field]['tmp_name']);
			$ret = $height > 0 && $imgHeight <= $height;
		}
		if(!$ret) {
			return __('File height must be smaller than %u.', $height);
		}
		return $ret;
// 		// Non-file uploads also mean the height is too big
// 		if (!isset($check[$field]['tmp_name']) || !strlen($check[$field]['tmp_name'])) {
// 			return false;
// 		}

// 		if (!$height) $height = $this->settings[$model->alias][$field]['maxHeight'];

// 		list($imgWidth, $imgHeight) = getimagesize($check[$field]['tmp_name']);
// 		return $height > 0 && $imgHeight <= $height;
// Edit End Ryuji.M
		
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
		if (!$requireUpload && (!isset($check[$field]['error']) || $check[$field]['error'] === UPLOAD_ERR_NO_FILE)) {
			return true;
		}
// Edit Start Ryuji.M
		// Non-file uploads also mean the height is too big
		$ret = true;
		if (!$width) $width = $this->settings[$model->alias][$field]['minWidth'];
		if (!isset($check[$field]['tmp_name']) || !strlen($check[$field]['tmp_name'])) {
			$ret = false;
		} else {
			list($imgWidth, $imgHeight) = getimagesize($check[$field]['tmp_name']);
			$ret = $width > 0 && $imgWidth >= $width;
		}
		if(!$ret) {
			return __('File width must be larger than %u.', $width);
		}
		return $ret;
// 		if (!isset($check[$field]['tmp_name']) || !strlen($check[$field]['tmp_name'])) {
// 			return false;
// 		}

// 		if (!$width) $width = $this->settings[$model->alias][$field]['minWidth'];

// 		list($imgWidth, $imgHeight) = getimagesize($check[$field]['tmp_name']);
// 		return $width > 0 && $imgWidth >= $width;
// Edit End Ryuji.M
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
		if (!$requireUpload && (!isset($check[$field]['error']) || $check[$field]['error'] === UPLOAD_ERR_NO_FILE)) {
			return true;
		}
// Edit Start Ryuji.M
		// Non-file uploads also mean the height is too big
		$ret = true;
		if (!$width) $width = $this->settings[$model->alias][$field]['maxWidth'];
		if (!isset($check[$field]['tmp_name']) || !strlen($check[$field]['tmp_name'])) {
			$ret = false;
		} else {
			list($imgWidth, $imgHeight) = getimagesize($check[$field]['tmp_name']);
			$ret = $width > 0 && $imgWidth <= $width;
		}
		if(!$ret) {
			return __('File height must be smaller than %u.', $width);
		}
		return $ret;
// 		if (!isset($check[$field]['tmp_name']) || !strlen($check[$field]['tmp_name'])) {
// 			return false;
// 		}

// 		if (!$width) $width = $this->settings[$model->alias][$field]['maxWidth'];

// 		list($imgWidth, $imgHeight) = getimagesize($check[$field]['tmp_name']);
// 		return $width > 0 && $imgWidth <= $width;
// Edit End Ryuji.M
	}

	public function _resizeImagick(Model $model, $field, $path, $size, $geometry, $thumbnailPath) {
		$srcFile  = $path . $model->data[$model->alias][$field];
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
// 小さい画像を拡大してしまうため修正
			if ($destH > $height) {$destH = $height;}
			if ($destW > $width) {$destW = $width;}
// Edit End R.Ohga
// Edit Start R.Ohga
// 余白ができてしまうため修正
			$tempDestH = $destH;
			$tempDestW = $destW;
			if ($width > $height) {
				$tempDestH = 0;
			} else {
				$tempDestW = 0;
			}
			$image->resizeImage($tempDestW, $tempDestH, imagick::FILTER_MITCHELL, 1);
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
// Edit Start Ryuji.M
		if($size == 'original' && ((empty($destH) && empty($destH)) || ($destH == $height && $destW == $width))) {
			// 大きさの変更なし
			return true;
		}
// Edit End Ryuji.M
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
		if ($size == 'original') {
			$thumbnailName = preg_replace('/[_-]?\{size\}[_-]?/', '',$thumbnailName);
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
		$srcFile  = $path . $model->data[$model->alias][$field];
		$pathInfo = $this->_pathinfo($srcFile);
		$thumbnailType = $this->settings[$model->alias][$field]['thumbnailType'];

		if (!$thumbnailType || !is_string($thumbnailType)) {
			$thumbnailType = $pathInfo['extension'];
		}

		if (!$thumbnailType) {
			$thumbnailType = 'png';
		}

// Edit Start R.Ohga
// $sizeが「original」の場合はファイル名に$sizeを使用しないように修正(元ファイルの自動リサイズ)
		$thumbnailName = $this->settings[$model->alias][$field]['thumbnailName'];
		if ($size == 'original') {
			$thumbnailName = preg_replace('/[_-]?\{size\}[_-]?/', '',$thumbnailName);
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
// Edit Start Ryuji.M

			if($size == 'original' && $destH == $height && $destW == $width) {
				// 大きさの変更なし
				return true;
			}
// Edit End Ryuji.M

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
		$destDir = $path;
		$this->_mkPath($model, $field, $destDir);
		return '';
	}

	public function _getPathPrimaryKey(Model $model, $field, $path) {
		$destDir = $path . $model->id . DIRECTORY_SEPARATOR;
		$this->_mkPath($model, $field, $destDir);
		return $model->id;
	}

	public function _getPathRandom(Model $model, $field, $path) {
		$endPath = null;
		$decrement = 0;
		$string = crc32($field . microtime());

		for ($i = 0; $i < 3; $i++) {
			$decrement = $decrement - 2;
			$endPath .= sprintf("%02d" . DIRECTORY_SEPARATOR, substr('000000' . $string, $decrement, 2));
		}

		$destDir = $path . $endPath;
		$this->_mkPath($model, $field, $destDir);

		return substr($endPath, 0, -1);
	}

	public function _getPathRandomCombined(Model $model, $field, $path) {
		$endPath = null;
		$decrement = 0;
		$string = crc32($field . microtime() . $model->id);

		for ($i = 0; $i < 3; $i++) {
			$decrement = $decrement - 2;
			$endPath .= sprintf("%02d" . DIRECTORY_SEPARATOR, substr('000000' . $string, $decrement, 2));
		}

		$destDir = $path . $endPath;
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
			'path' => '{ROOT}webroot{DS}files{DS}{model}{DS}{field}{DS}',
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
// Add Start Ryuji.M
			//'{plugin}'	=> $options['plugin'],
			'{contentId}'	=> $options['contentId'],
// Add End Ryuji.M
		);
// Edit Start Ryuji.M
		$newPath = str_replace(
			array_keys($replacements),
			array_values($replacements),
			$options['path']
		);
		if($newPath != '') {
			$newPath = Folder::slashTerm($newPath);
		} else {
			$newPath = DS;
		}
		//$newPath = Folder::slashTerm(str_replace(
		//	array_keys($replacements),
		//	array_values($replacements),
		//	$options['path']
		//));
// Edit End Ryuji.M

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
// Edit Start Ryuji.M
					$model->invalidate($field, 'Failed to resize file.');
					//$model->invalidate($field, 'resizeFail');
// Edit End Ryuji.M
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

	public function _prepareFilesForDeletion(Model $model, $field, $data, $options) {
		if (!strlen($data[$model->alias][$field])) return $this->__filesToRemove;

		if (!empty($options['fields']['dir']) && isset($data[$model->alias][$options['fields']['dir']])) {
			$dir = $data[$model->alias][$options['fields']['dir']];
		} else {
			if (in_array($options['pathMethod'], array('_getPathFlat', '_getPathPrimaryKey'))) {
				$model->id = $data[$model->alias][$model->primaryKey];
				$dir = call_user_func(array($this, '_getPath'), $model, $field);
			} else {
				CakeLog::error(sprintf('Cannot get directory to %s.%s: %s pathMethod is not supported.', $model->alias, $field, $options['pathMethod']));
			}
		}
		$filePathDir = $this->settings[$model->alias][$field]['path'] . (empty($dir) ? '' : $dir. DS);
		$filePath = $filePathDir.$data[$model->alias][$field];

		$pathInfo = $this->_pathinfo($filePath);

		if (!isset($this->__filesToRemove[$model->alias])) {
			$this->__filesToRemove[$model->alias] = array();
		}

		$this->__filesToRemove[$model->alias][] = $filePath;
		$this->__foldersToRemove[$model->alias][] = $dir;

		$createThumbnails = $options['thumbnails'];
		$hasThumbnails = !empty($options['thumbnailSizes']);

		if (!$createThumbnails || !$hasThumbnails) {
			return $this->__filesToRemove;
		}
		
// Add Start Ryuji.M
		if ($this->settings[$model->alias][$field]['useUploadModel']) {
			// Uploadテーブル削除
			$Upload = ClassRegistry::init('Upload');
			$fileName = $data[$model->alias][$field];
			if(!empty($fileName)) {
				$uploadId = substr($fileName, 0, strpos($fileName, '.'));
				if(!$Upload->delete($uploadId)){
					return false;
				}
			}
		}
		if ($this->settings[$model->alias][$field]['useUploadLinkModel']) {
			// UploadLink削除
			$UploadLink = ClassRegistry::init('UploadLink');
			if(isset($options['uniqueIdName'])) {
				if(isset($model->data[$model->alias][$options['uniqueIdName']])) {
					$uniqueId = $model->data[$model->alias][$options['uniqueIdName']];
				} else if(isset($model->data[$options['modelName']][$options['uniqueIdName']])) {
					$uniqueId = $model->data[$options['modelName']][$options['uniqueIdName']];
				} else {
					$uniqueId = $data[$model->alias][$options['uniqueIdName']];
				}
			} else {
				$uniqueId = $model->id;
			}
			$modelName = isset($options['modelName']) ? $options['modelName'] : $model->alias;
			$fieldName = isset($options['fieldName']) ? $options['fieldName'] : $field;
			
			
			$delConditions = array(
				'unique_id'=> $uniqueId,
				'model_name'=> $modelName,
				'field_name'=> $fieldName
			);
			if(!$UploadLink->deleteAll($delConditions, true, true)){
				return false;
			}
		}
		if($options['isWysiwyg']) {
			return $this->__filesToRemove;
		}
// Add End Ryuji.M

		$DS = empty($dir) ? '' : DIRECTORY_SEPARATOR;
		$mimeType = $this->_getMimeType($filePath);
		$isMedia = $this->_isMedia($model, $mimeType);
		$isImagickResize = $options['thumbnailMethod'] == 'imagick';
		$thumbnailType = $options['thumbnailType'];

		if ($isImagickResize) {
			if ($isMedia) {
				$thumbnailType = $options['mediaThumbnailType'];
			}

			if (!$thumbnailType || !is_string($thumbnailType)) {
				try {
					$srcFile = $filePath;
					$image    = new imagick();
					if ($isMedia) {
						$image->setResolution(300, 300);
						$srcFile = $srcFile.'[0]';
					}

					$image->readImage($srcFile);
					$thumbnailType = $image->getImageFormat();
				} catch (Exception $e) {
					$thumbnailType = 'png';
				}
			}
		} else {
			if (!$thumbnailType || !is_string($thumbnailType)) {
				$thumbnailType = $pathInfo['extension'];
			}

			if (!$thumbnailType) {
				$thumbnailType = 'png';
			}
		}

		foreach ($options['thumbnailSizes'] as $size => $geometry) {
// Add Start Ryuji.M
			if($size == 'original') {
				continue;
			}
// Add End Ryuji.M
			$fileName = str_replace(
				array('{size}', '{filename}', '{primaryKey}', '{time}', '{microtime}'),
				array($size, $pathInfo['filename'], $model->id, time(), microtime()),
				$options['thumbnailName']
			);

			$thumbnailPath = $options['thumbnailPath'];
			$thumbnailPath = $this->_pathThumbnail($model, $field, compact(
				'geometry', 'size', 'thumbnailPath'
			));

			$thumbnailFilePath = "{$thumbnailPath}{$dir}{$DS}{$fileName}.{$thumbnailType}";
			$this->__filesToRemove[$model->alias][] = $thumbnailFilePath;
		}
		return $this->__filesToRemove;
	}

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
// Add Start Ryuji.M
// tar.gz対応
		if($pathInfo['extension'] == 'gz') {
			$fileNameArr = explode('.', $pathInfo['filename']);
			foreach($fileNameArr as $key => $fileName) {
				if($key == 0) {
					$pathInfo['filename'] = $fileName;
				} else if($key == count($fileNameArr) - 1) {
					$pathInfo['extension'] = $fileName . '.' . $pathInfo['extension'];
				} else {
					$pathInfo['filename'] .= '.' . $fileName;
				}
			}
		}
// Add End Ryuji.M
		return $pathInfo;
	}

// Add Start Ryuji.M
/**
 * アップロードテーブルのuser_idセット
 *
 * @param AppModel $model Model instance
 * @param integer $userId
 * @return void
 */
	public function setUploadUserId(Model $model, $userId) {
		$this->uploadUserId = $userId;
	}

/**
 * ファイルアップロード処理(Upload, UploadLinkテーブルのみ登録用)
 * @param AppModel $model Model instance
 * @param array $data
 * @return boolean
 */
	public function uploadFile(Model $model, $data = null) {
		$model->set($data);
	
		if(empty($data[$model->alias][$model->primaryKey])) {
			foreach ($this->settings[$model->alias] as $field => $options) {
				if(!$model->validates(array('fieldList' => array($field)))) {
					return false;
				}
			}
			if ($this->beforeSave($model)) {
				$this->afterSave($model, true);
			}

			if(count($model->validationErrors) > 0) {
				return false;
			}
		} else {
			$model->save();
		}
		return true;
	}
/**
 * ファイル削除処理(Upload, UploadLinkテーブルのみ削除用)
 * @param AppModel $model Model instance
 * @param integer $id
 * @param boolean $cascade
 * @return boolean
 */
	public function deleteFile(Model $model, $id, $cascade = true) {
		$model->id = $id;
		if ($this->beforeDelete($model, $cascade)) {
			$this->afterDelete($model);
		}
		return true;
	}

/**
 * アップロードテーブルへの登録更新
 *
 * @param AppModel $model Model instance
 * @param string $field
 * @param array $uploadFile
 * @param array $options
 * @param integer $beforeUploadId
 * @return mixed success:uploadId.(extension) error:false
 */
	public function saveUpload(Model $model, $field, $uploadFile, $options, $beforeUploadId = null) {
	
		$Upload = ClassRegistry::init('Upload');
		// Upload登録
		$pathinfo = $this->_pathinfo($uploadFile['name']);

		$data = array(
			'Upload' => array(
				'id' => $beforeUploadId,
				'user_id' => $this->uploadUserId,
				'file_name' => $uploadFile['name'],
				'file_size' => $uploadFile['size'],
				'file_path' => '',
				'mimetype' => $uploadFile['type'],
				'extension' => $pathinfo['extension'],
				'plugin' => $options['plugin'],
				'upload_model_name' => $model->name,
				'is_delete_from_library' => ($options['isDeleteFromLibrary'] ? _ON : _OFF),
				'is_wysiwyg' => ($options['isWysiwyg'] ? _ON : _OFF),
			)
		);
		if (!empty($model->id)) {
			$data['Upload']['is_use'] = _ON;
		}
		
		// PHPエラーチェック処理
		if(!$this->_checkPHPError($model, $field, $uploadFile)) {
			return false;
		}

		if(!$Upload->save($data)) {
			if ($model->alias != 'Upload') {
				foreach($Upload->validationErrors as $uploadField => $errors) {
					foreach($errors as $error) {
						$model->invalidate($field, $error);
					}
				}
			}
			return false;
		}

		return $Upload->id. '.'. $pathinfo['extension'];
	}
	
/**
 * アップロードリンクテーブルへの登録更新
 *
 * @param AppModel $model Model instance
 * @param string $field
 * @param array $options
 * @return 
 */
	public function saveUploadLink(Model $model, $field, $options = null) {
		if(!is_array($options)) {
			$options = $this->settings[$model->alias][$field];
		} else {
			$options = array_merge($this->settings[$model->alias][$field], $options);
		}
		
		//if (!isset($model->data[$model->alias][$field])) {
		if (isset($model->data[$model->alias][$field]) && !is_string($model->data[$model->alias][$field])) {
			// データなし
			return true;
		}
		$UploadLink = ClassRegistry::init('UploadLink');
		$isWysiwyg = false;
		if($options['isWysiwyg'] === null) {
			// fieldがrevision_group_idならば、強制的にisWysiwyg=trueとする。
			$isWysiwyg = ($field == 'revision_group_id') ? true : false;
			// $options['isWysiwyg'] = ($model->getColumnType($field) == 'text') ? true : false;
		} else {
			$isWysiwyg = $options['isWysiwyg'];
		}
		if(isset($options['uniqueIdName'])) {
			if(isset($model->data[$model->alias][$options['uniqueIdName']])) {
				$uniqueId = $model->data[$model->alias][$options['uniqueIdName']];
			} else if(isset($model->data[$options['modelName']][$options['uniqueIdName']])) {
				$uniqueId = $model->data[$options['modelName']][$options['uniqueIdName']];
			} else {
				$uniqueId = intval($options['uniqueIdName']);
			}
		} else {
			$uniqueId = $model->id;
		}
		$modelName = isset($options['modelName']) ? $options['modelName'] : $model->alias;
		$fieldName = isset($options['fieldName']) ? $options['fieldName'] : $field;
		
		if($isWysiwyg) {
			$text = isset($options['wysiwygText']) ? $options['wysiwygText'] : (isset($model->data[$modelName][$fieldName]) ? $model->data[$modelName][$fieldName] : null);
			if(!isset($text)) {
				return true;
			}
			$options = array(
				'plugin' => $options['plugin'],
				'contentId' => $options['contentId'],
				'uniqueId' => $uniqueId,
				'modelName' => $modelName,
				'fieldName' => $fieldName,
			);
			return $UploadLink->updateUploadInfoForWysiwyg($text, $options);
		} else {
			$fileName = isset($model->data[$model->alias][$field]) ? $model->data[$model->alias][$field] : null;
			if(!isset($fileName)) {
				return true;
			}
			$uploadId = substr($fileName, 0, strpos($fileName, '.'));
			if(intval($uploadId) <= 0) {
				return true;
			}
			
			$uploadLink = $UploadLink->find('first', array(
				'recursive' => -1,
				'fields' => array('UploadLink.id', 'UploadLink.is_use'),
				'conditions' => array(
					'plugin' => $options['plugin'],
					//'content_id'=>$options['contentId'],	// TODO:必要かも
					'unique_id' => $uniqueId,
					'model_name' => $modelName,
					'field_name' => $fieldName,
				)
			));
			
			if(is_string($options['checkComponentAction'])) {
				$checkComponentAction = $options['checkComponentAction'];
			} else {
				$checkComponentAction = implode(',', $options['checkComponentAction']);
			}

			$data = array('UploadLink' => array(
				'upload_id' => $uploadId,
				'plugin' => $options['plugin'],
				'content_id' => $options['contentId'],
				'model_name' => $modelName,
				'field_name' => $fieldName,
				'access_hierarchy' =>$options['accessHierarchy'],
				'is_use' => _ON,
				'unique_id' => $uniqueId,
				'download_password' => $options['downloadPassword'],
				'check_component_action' => $checkComponentAction,
			));
			if(isset($uploadLink['UploadLink'])) {
				$data['UploadLink']['id'] = $uploadLink['UploadLink']['id'];
			}
			if($UploadLink->save($data)) {
				if ((!isset($uploadLink['UploadLink']) || !$uploadLink['UploadLink']['is_use'])  &&
					$this->settings[$model->alias][$field]['useUploadModel']) {
					$Upload = ClassRegistry::init('Upload');
					
					$data = array(
						'Upload.is_use' => _ON,
					);
					$haystack = $model->data[$model->alias][$field];
					$conditions = array('Upload.id' => $uploadId);
					if(!$Upload->updateAll($data, $conditions)) {
						return false;
					}
				}
			}
			return true;
		}
	}

/**
 * PHPエラーチェック処理
 * 	UPLOAD_ERR_OK			:0; エラーはなく、ファイルアップロードは成功しています。
 * 	UPLOAD_ERR_INI_SIZE		:1; アップロードされたファイルは、php.ini の upload_max_filesize ディレクティブの値を超えています。
 * 	UPLOAD_ERR_FORM_SIZE	:2; アップロードされたファイルは、HTML フォームで指定された MAX_FILE_SIZE を超えています。
 * 	UPLOAD_ERR_PARTIAL		:3; アップロードされたファイルは一部のみしかアップロードされていません。
 * 	UPLOAD_ERR_NO_FILE		:4; ファイルはアップロードされませんでした。
 * 	UPLOAD_ERR_NO_TMP_DIR	:6; テンポラリフォルダがありません。
 * 	UPLOAD_ERR_CANT_WRITE	:7; ディスクへの書き込みに失敗しました。
 * 	UPLOAD_ERR_EXTENSION	:8; PHP の拡張モジュールがファイルのアップロードを中止しました。
 * @param AppModel $model Model instance
 * @param string $field
 * @param array $uploadFile
 * @return  boolean
 * @since   v 3.0.0.0
 */
	protected function _checkPHPError(Model $model, $field, $uploadFile) {
		$check = array($field, $uploadFile);
		if(!$this->isUnderPhpSizeLimit($model, $check)) {
			$model->invalidate($field, __('The uploaded file is too big! It exceeds the upload_max_filesize defined in php.ini.'));
			return false;
		} else if(!$this->isUnderFormSizeLimit($model, $check)) {
			$model->invalidate($field, __('The uploaded file is too big! It exceeds the MAX_FILE_SIZE defined in HTML form.'));
			return false;
		} else if(!$this->isCompletedUpload($model, $check)) {
			$model->invalidate($field, __('Only partially uploaded.'));
			return false;
		} else if(!$this->isFileUpload($model, $check)) {
			$model->invalidate($field, __('No file was uploaded.'));
			return false;
		} else if(!$this->tempDirExists($model, $check)) {
			$model->invalidate($field, __('There is no temporary folder.'));
			return false;
		} else if(!$this->isSuccessfulWrite($model, $check)) {
			$model->invalidate($field, __('Failed to write to disk.'));
			return false;
		} else if(!$this->noPhpExtensionErrors($model, $check)) {
			$model->invalidate($field, __('PHP Extension is aborted file uploads.'));
			return false;
		}
		
		return true;
	}
	
/**
 * FileTypeでのextensions,maxSizeセット処理
 * @param AppModel $model Model instance
 * @param string $field Name of field being modified
 * @param array $options
 * @return  void
 * @since   v 3.0.0.0
 */
	protected function _setFileTypeSetting(Model $model, $field, &$options) {
		if(empty($this->settings[$model->alias][$field]['extensions']) || isset($this->__extensionType)) {
			if ($options['fileType'] == 'image') {
				if($options['thumbnailMethod'] == 'imagick') {
					$this->__extensionType = 'imagick';
					$options['extensions'] = explode(',', NC_UPLOAD_IMAGEFILE_EXTENSION);
				} else {
					$this->__extensionType = 'php';
					$options['extensions'] = explode(',', NC_UPLOAD_IMAGEFILE_PHP_EXTENSION);
				}
			} else if($options['fileType'] == 'compression') {
				$this->__extensionType = 'compression';
				$options['extensions'] = explode(',', NC_UPLOAD_COMPRESSIONFILE_EXTENSION);
			} else if($options['fileType'] == 'file') {
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
				$options['extensions'] = explode(',', $allowExtension['allow_extension']);
				$this->__extensionType = 'file';
			} else {
				$options['extensions'] = explode(',', $options['fileType']);
			}
		}
		if(!isset($this->settings[$model->alias][$field]['fileType']) || 
			$this->settings[$model->alias][$field]['fileType'] != $options['fileType']) {
			$options['maxSize'] = ($options['fileType'] == 'image') ? NC_UPLOAD_MAX_SIZE_IMAGE : NC_UPLOAD_MAX_SIZE_ATTACHMENT;
		}
	}

/**
 * アップロードファイル名取得
 * @param AppModel $model Model instance
 * @param string $field Name of field being modified
 * @return  string 
 * @since   v 3.0.0.0
 */
	public function getUploadFileNames(Model $model, $field) {
		return $this->uploadFileNames[$field];
	}

// Add End Ryuji.M
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
}