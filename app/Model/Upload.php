<?php
/**
 * Uploadモデル
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Model
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class Upload extends AppModel
{
	public $hasMany = array(
		'UploadLink'=>array('dependent'=> true)
	);

/**
 * Behavior name
 *
 * @var array
 */
	public $actsAs = array(
		'TimeZone',
		'Upload' => array('')
	);

/**
 * バリデート処理
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function __construct() {
		parent::__construct();

		$this->validate = array(
				'user_id' => array(
					'notEmpty'  => array(
						'rule' => array('notEmpty'),
						'message' => __('Please be sure to input.')
					),
					'numeric' => array(
						'rule' => array('numeric'),
						'required' => false,
						'message' => __('The input must be a number.')
					)
				),
				'file_name' => array(
					'notEmpty'  => array(
						'rule' => array('notEmpty'),
						'message' => __('Please be sure to input.')
					),
					'notEmptyExceptExtension'  => array(
						'rule' => array('_notEmptyExceptExtension'),
						'message' => __('Please be sure to input.')
					),
					'maxlength'  => array(
						'rule' => array('maxLength', NC_VALIDATOR_TITLE_LEN),
						'message' => __('The input must be up to %s characters.', NC_VALIDATOR_TITLE_LEN)
					),
					'extension'  => array(
						'rule' => array('extension'),
						'message' => __('Invalid extension.')
					),
					'invalidCharacter' => array(
						'rule' => array('_invalidCharacter', NC_UPLOAD_PROHIBITION_CHAR_FILE),
						'message' => __('It contains an invalid string.')
					),
				),
				'physical_file_name' => array(
					'notEmpty'  => array(
						'rule' => array('notEmpty'),
						'message' => __('Please be sure to input.')
					),
					'maxlength'  => array(
						'rule' => array('maxLength', NC_VALIDATOR_TITLE_LEN),
						'message' => __('The input must be up to %s characters.', NC_VALIDATOR_TITLE_LEN)
					)
				),
				'description' => array(
					'maxlength'  => array(
						'rule' => array('maxLength', NC_VALIDATOR_TEXTAREA_LEN),
						'message' => __('The input must be up to %s characters.', NC_VALIDATOR_TEXTAREA_LEN)
					)
				),
				'file_size' => array(
					'notEmpty'  => array(
						'rule' => array('notEmpty'),
						'message' => __('Please be sure to input.')
					),
					'numeric' => array(
						'rule' => array('numeric'),
						'message' => __('The input must be a number.')
					),
					'checkFilesizeLimit'  => array(
						'rule' => array('_checkFilesizeLimit'),
						'message' => __('Ecxeeded upload capacity.')
					)
				),
				'plugin' => array(
					'notEmpty'  => array(
						'rule' => array('notEmpty'),
						'message' => __('Please be sure to input.')
					),
				),
				'is_use' => array(
					'boolean'  => array(
						'rule' => array('boolean'),
						'message' => __('The input must be a boolean.')
					)
				),
				'is_wysiwyg' => array(
					'boolean'  => array(
						'rule' => array('boolean'),
						'message' => __('The input must be a boolean.')
					)
				),
				'download_count' => array(
					'numeric' => array(
						'rule' => array('numeric'),
						'message' => __('The input must be a number.')
					)
				),
				'year' => array(
					'numeric' => array(
						'rule' => array('numeric'),
						'message' => __('The input must be a number.')
					)
				),
				'month' => array(
					'numeric' => array(
						'rule' => array('numeric'),
						'message' => __('The input must be a number.')
					)
				),
				'day' => array(
					'numeric' => array(
						'rule' => array('numeric'),
						'message' => __('The input must be a number.')
					)
				),
		);
	}

/**
 * beforeSave
 * @param   array  $options
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function beforeSave($options=array()) {
		parent::beforeSave($options);
		$this->data['Upload']['year'] = $this->nowDate('Y');
		$this->data['Upload']['month'] = $this->nowDate('m');
		$this->data['Upload']['day'] = $this->nowDate('d');
		return true;
	}

/**
 * beforeDelete
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function beforeDelete($cascade = true) {
		App::uses('Folder', 'Utility');
		App::uses('File', 'Utility');

		$upload = $this->findById($this->id);

		// 物理ファイル削除処理
		$filePath = NC_UPLOADS_DIR.$upload['Upload']['plugin'].DS;
		if (empty($upload['Upload']['file_path'])) {
			$dir = new Folder($filePath);
			$files = $dir->find($upload['Upload']['id'].'[.-].*');
			foreach ($files as $filename) {
				$file = new File($dir->pwd().$filename);
				if (!$file->delete()) {
					return false;
				}
			}
		} else {
			$filePath .= $upload['Upload']['file_path'];
			$dir = new Folder($filePath);
			if (!$dir->delete()) {
				return false;
			}
		}

		return true;
	}

/**
 * ファイルアップロード処理
 * @param   array $uploadFile input type="file"の値
 * @param   array $options (
 * 				filePath				Upload.file_path
 * 				userId 					Upload.user_id
 * 				plugin 					Upload.plugin,UploadLink.plugin
 * 				contentId				UploadLink.content_id
 * 				resolusion 				画像の解像度 string or array(幅, 高さ)
 * 				fileType 				ファイルタイプ(image/file)
 * 				isWysiwyg				WYSIWYGからのアップロードかどうか
 * 			)
 * @return  mixed false|array $uploadData
 * @since   v 3.0.0.0
 */
	public function uploadFile($uploadFile, $options=array()) {

		if (empty($uploadFile) || !is_uploaded_file($uploadFile['tmp_name'])) {
			return false;
		}

		$isImage = false;
		if (preg_match('/^image\/(gif|jpe?g|png)/', $uploadFile['type'])) {
			$isImage = true;
		}
		$maxWidth = 0;
		$maxHeight = 0;
		if ($isImage) {
			list($maxWidth, $maxHeight) = $this->getUploadMaxsizeByResolusion($options['resolusion']);
		}

		$behavierOption = array();
		if (isset($options['fileType'])
			&& ($options['fileType'] == 'file' || $options['fileType'] == 'image')) {
			$behavierOption['fileType'] = $options['fileType'];
		}
		foreach (array('userId','plugin','contentId','isWysiwyg') as $key) {
			if (isset($options[$key])) {
				$behavierOption[$key] = $options[$key];
			}
		}
		$this->setBehavierOptions($behavierOption);

		$columnOptions = array(
			'maxWidth' => $maxWidth,
			'maxHeight' => $maxHeight,
			'thumbnails' => ($isImage) ? true : false,
		);
		if (isset($options['filePath'])) {
			$columnOptions['filePath'] = $options['filePath'];
		}
		$this->setColumnOptions('', $columnOptions);

		$uploadData = $this->saveUpload('', $uploadFile, false);
		if(!$uploadData) {
			return false;
		}

		return $uploadData;
	}

/**
 * アップロードファイルのサイズを取得
 * @param   mixed 解像度種別 string or array(幅, 高さ)
 * @return  array (幅,高さ)
 * @since   v 3.0.0.0
 */
	public function getUploadMaxsizeByResolusion($resolusion) {
		$maxWidth = 0;
		$maxHeight = 0;

		if (is_array($resolusion)) {
			if (isset($resolusion[0]) && is_int($resolusion[0])) {
				$maxWidth = $resolusion[0];
			}
			if (isset($resolusion[1]) && is_int($resolusion[1])) {
				$maxHeight = $resolusion[1];
			}
			return array($maxWidth, $maxHeight);
		}

		switch ($resolusion) {
			case 'asis':
				break;
			case 'large':
				$maxWidth = UPLOAD_RESOLUTION_IMAGE_LARGE_WIDTH;
				$maxHeight = UPLOAD_RESOLUTION_IMAGE_LARGE_HEIGHT;
				break;
			case 'middle':
				$maxWidth = UPLOAD_RESOLUTION_IMAGE_MIDDLE_WIDTH;
				$maxHeight = UPLOAD_RESOLUTION_IMAGE_MIDDLE_HEIGHT;
				break;
			case 'small':
				$maxWidth = UPLOAD_RESOLUTION_IMAGE_SMALL_WIDTH;
				$maxHeight = UPLOAD_RESOLUTION_IMAGE_SMALL_HEIGHT;
				break;
			case 'icon':
				$maxWidth = UPLOAD_RESOLUTION_IMAGE_ICON_SIZE;
				$maxHeight = UPLOAD_RESOLUTION_IMAGE_ICON_SIZE;
				break;
			default:
				$Config = ClassRegistry::init('Config');
				$conditions = array(
					'Config.name' => array('upload_normal_width_size', 'upload_normal_height_size')
				);
				$params = array(
					'fields' => array(
						'Config.name',
						'Config.value'
					),
					'conditions' => $conditions,
				);
				$result = $Config->find('all', $params);
				$maxWidth = $result['upload_normal_width_size'];
				$maxHeight = $result['upload_normal_height_size'];
				break;
		}
		return array($maxWidth, $maxHeight);
	}

/**
 * ユーザに紐づくアップロードファイルのサイズ総計取得
 * @param   array $userId
 * @return  int アップロードファイルサイズ総計
 * @since   v 3.0.0.0
 */
	public function findFilesizeSumByUserId($userId) {
		if (empty($userId)) {
			return false;
		}

		$params = array(
			'fields' => array('SUM(Upload.file_size) AS "Upload.file_size_sum"'),
			'conditions' => array('Upload.user_id'=>$userId)
		);
		$fileSizeResult = $this->find('all', $params);
		if (empty($fileSizeResult)) {
			return false;
		}
		return $fileSizeResult[0][0]['Upload.file_size_sum'];
	}

/**
 * PHPエラーチェック処理
 * @param  int $error PHPエラーコード
 * 	UPLOAD_ERR_OK			:0; エラーはなく、ファイルアップロードは成功しています。
 * 	UPLOAD_ERR_INI_SIZE		:1; アップロードされたファイルは、php.ini の upload_max_filesize ディレクティブの値を超えています。
 * 	UPLOAD_ERR_FORM_SIZE	:2; アップロードされたファイルは、HTML フォームで指定された MAX_FILE_SIZE を超えています。
 * 	UPLOAD_ERR_PARTIAL		:3; アップロードされたファイルは一部のみしかアップロードされていません。
 * 	UPLOAD_ERR_NO_FILE		:4; ファイルはアップロードされませんでした。
 * 	UPLOAD_ERR_NO_TMP_DIR	:6; テンポラリフォルダがありません。
 * 	UPLOAD_ERR_CANT_WRITE	:7; ディスクへの書き込みに失敗しました。
 * 	UPLOAD_ERR_EXTENSION	:8; PHP の拡張モジュールがファイルのアップロードを中止しました。
 * @return  array $errorMes
 * @since   v 3.0.0.0
 */
	public function checkPHPError($error) {
		$errorMes = array();
		switch ($error) {
			case UPLOAD_ERR_OK:
				break;
			case UPLOAD_ERR_INI_SIZE:
				$errorMes['file_name'][] = __('The uploaded file is too big! It exceeds the upload_max_filesize defined in php.ini.');
				break;
			case UPLOAD_ERR_FORM_SIZE:
				$errorMes['file_name'][] = __('The uploaded file is too big! It exceeds the MAX_FILE_SIZE defined in HTML form.');
				break;
			case UPLOAD_ERR_PARTIAL:
				$errorMes['file_name'][] = __('Only partially uploaded.');
				break;
			case UPLOAD_ERR_NO_FILE:
				$errorMes['file_name'][] = __('No file was uploaded.');
				break;
			case UPLOAD_ERR_NO_TMP_DIR:
				$errorMes['file_name'][] = __('There is no temporary folder.');
				break;
			case UPLOAD_ERR_CANT_WRITE:
				$errorMes['file_name'][] = __('Failed to write to disk.');
				break;
			case UPLOAD_ERR_EXTENSION:
				$errorMes['file_name'][] = __('PHP Extension is aborted file uploads.');
				break;
			default:
				break;
		}
		return $errorMes;
	}
}