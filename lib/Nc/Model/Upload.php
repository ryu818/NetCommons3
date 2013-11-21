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
	);

/**
 * バリデート処理
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);

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
						'rule' => array('notEmptyExceptExtension'),
					),
					'maxLength'  => array(
						'rule' => array('maxLength', NC_VALIDATOR_TITLE_LEN),
						'message' => __('The input must be up to %s characters.', NC_VALIDATOR_TITLE_LEN)
					),
					'custom' => array(
						'rule' => array('custom', NC_UPLOAD_ALLOW_CHAR_FILE),
						'message' => __('It contains an invalid string.')
					),
				),
				'alt' => array(
					'maxLength'  => array(
						'rule' => array('maxLength', NC_VALIDATOR_TITLE_LEN),
						'message' => __('The input must be up to %s characters.', NC_VALIDATOR_TITLE_LEN)
					),
				),
				'caption' => array(
					'maxLength'  => array(
						'rule' => array('maxLength', NC_VALIDATOR_TITLE_LEN),
						'message' => __('The input must be up to %s characters.', NC_VALIDATOR_TITLE_LEN)
					),
				),
				'description' => array(
					'maxLength'  => array(
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
						'rule' => array('checkFileSizeLimit'),
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
 * アップロードファイルのサイズを取得
 * @param   mixed 解像度種別
 * 					normal|asis|large|middle|small|icon
 * @return  string thumbnailSizes文字列
 * @since   v 3.0.0.0
 */
	public function getUploadMaxSizeByResolusion($resolusion) {
		switch ($resolusion) {
			case 'asis':
				// 縮小しないため空文字を返す
				return '';
			case 'large':
				$maxWidth = NC_UPLOAD_RESOLUTION_IMAGE_LARGE_WIDTH;
				$maxHeight = NC_UPLOAD_RESOLUTION_IMAGE_LARGE_HEIGHT;
				break;
			case 'middle':
				$maxWidth = NC_UPLOAD_RESOLUTION_IMAGE_MIDDLE_WIDTH;
				$maxHeight = NC_UPLOAD_RESOLUTION_IMAGE_MIDDLE_HEIGHT;
				break;
			case 'small':
				$maxWidth = NC_UPLOAD_RESOLUTION_IMAGE_SMALL_WIDTH;
				$maxHeight = NC_UPLOAD_RESOLUTION_IMAGE_SMALL_HEIGHT;
				break;
			case 'icon':
				$maxWidth = NC_UPLOAD_RESOLUTION_IMAGE_ICON_SIZE;
				$maxHeight = NC_UPLOAD_RESOLUTION_IMAGE_ICON_SIZE;
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
		
		return '['.$maxWidth.'x'.$maxHeight.']';
	}

/**
 * ユーザーに紐づくアップロードファイルのサイズ総計取得
 * @param   array $userId
 * @return  integer アップロードファイルサイズ総計
 * @since   v 3.0.0.0
 */
	public function findFileSizeSumByUserId($userId) {
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
		return !empty($fileSizeResult[0][0]['Upload.file_size_sum']) ? $fileSizeResult[0][0]['Upload.file_size_sum'] : 0;
	}

/**
 * 権限テーブルの容量制限チェック
 *
 * @param array $check
 * @return boolean
 * @since   v 3.0.0.0
 */
	public function checkFileSizeLimit($check) {
		$user = Configure::read(NC_SYSTEM_KEY.'.user');
		$Authority = ClassRegistry::init('Authority');
		if($user['id'] == $this->data[$this->alias]['user_id']) {
			$authorityId = $user['authority_id'];
		} else {
			$User = ClassRegistry::init('User');
			if(empty($this->data[$this->alias]['user_id'])) {
				$authorityId = $user['authority_id'];
			} else {
				$user = $User->findById($this->data[$this->alias]['user_id']);
				if(isset($user['User']['authority_id'])) {
					$authorityId = $user['User']['authority_id'];
				} else {
					$authorityId = NC_AUTH_GUEST_ID;
				}
			}
		}
		$authority = $Authority->findById($authorityId);
		if($authority['Authority']['max_size'] == 0) {
			// 無制限
			return true;
		}
	
		$filesizeSum = $this->findFileSizeSumByUserId($this->data[$this->alias]['user_id']);
		$check = array_shift($check);
		if ($filesizeSum + $check > $authority['Authority']['max_size']) {
			App::uses('CakeNumber', 'Utility');
			$restSize = intval($authority['Authority']['max_size']) - intval($filesizeSum);
			if($restSize < 0) {
				$restSize = '-'. CakeNumber::toReadableSize(abs($restSize));
			} else {
				$restSize = CakeNumber::toReadableSize($restSize);
			}
			return __('Total file size exceeded the limit.Only %s left.', $restSize);
		}
		return true;
	}

/**
 * 拡張子以外が空かどうかの入力チェック
 * (.gifのみだとエラーにする)
 *
 * @param array $check
 * @return boolean
 * @since   v 3.0.0.0
 */
	public function notEmptyExceptExtension($check) {
		$check = array_shift($check);
		preg_match('/(.*)\.(.*)$/', $check, $match);
	
		if (empty($match[1])) {
			return __('Filename rejected.');
		}
		return true;
	}
}