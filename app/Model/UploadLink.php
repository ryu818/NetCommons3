<?php
/**
 * UploadLinkモデル
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Model
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class UploadLink extends AppModel
{
	public $name = 'UploadLink';

	public $belongsTo = array('Upload');

/**
 * バリデート処理
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function __construct() {
		parent::__construct();

		$this->validate = array(
				'upload_id' => array(
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
				'plugin' => array(
					'notEmpty'  => array(
						'rule' => array('notEmpty'),
						'message' => __('Please be sure to input.')
					),
				),
				'content_id' => array(
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
				'unique_id' => array(
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
				'model_name' => array(
					'notEmpty'  => array(
						'rule' => array('notEmpty'),
						'message' => __('Please be sure to input.')
					),
					'maxlength'  => array(
						'rule' => array('maxLength', NC_VALIDATOR_TITLE_LEN),
						'message' => __('The input must be up to %s characters.', NC_VALIDATOR_TITLE_LEN)
					)
				),
				'field_name' => array(
					'notEmpty'  => array(
						'rule' => array('notEmpty'),
						'message' => __('Please be sure to input.')
					),
					'maxlength'  => array(
						'rule' => array('maxLength', NC_VALIDATOR_TITLE_LEN),
						'message' => __('The input must be up to %s characters.', NC_VALIDATOR_TITLE_LEN)
					)
				),
				'access_hierarchy' => array(
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
				'is_use' => array(
					'boolean'  => array(
						'rule' => array('boolean'),
						'message' => __('The input must be a boolean.')
					)
				),
				'download_password' => array(
					'maxlength'  => array(
						'rule' => array('maxLength', NC_VALIDATOR_TITLE_LEN),
						'message' => __('The input must be up to %s characters.', NC_VALIDATOR_TITLE_LEN)
					)
				),
				'check_component_action' => array(
					'notEmpty'  => array(
						'rule' => array('notEmpty'),
						'message' => __('Please be sure to input.')
					),
					'maxlength'  => array(
						'rule' => array('maxLength', NC_VALIDATOR_TITLE_LEN),
						'message' => __('The input must be up to %s characters.', NC_VALIDATOR_TITLE_LEN)
					)
				),
		);
	}
/**
 * beforeDelete
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function beforeDelete($cascade = true) {
		$this->data = $this->findById($this->id);
	}
/**
 * afterDelete
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function afterDelete() {
		// 未使用になったUploadテーブルのレコードのis_useを_OFFにする
		$this->updateUploadToUnuse($this->data['UploadLink']['upload_id']);
	}

/**
 * ファイル項目の登録更新に伴うアップロード情報の更新
 *
 * @param int $newValue 更新後のアップロードID
 * @param int $preValue 更新前のアップロードID
 * @param array $options (
 * 					plugin 					UploadLink.plugin
 * 					contentId				UploadLink.content_id
 * 					uniqueId				UploadLink.unique_id
 * 					modelName				UploadLink.model_name
 * 					fieldName				UploadLink.field_name
 * 					accessHierarchy			UploadLink.access_hierarchy
 * 					downloadPassword		UploadLink.download_password
 * 					checkComponentAction	UploadLink.check_component_action
 * 				)
 * @return boolean success:true error:false
 */
	public function updateUploadInfo($newValue, $preValue, $options) {

		if($preValue == $newValue) {
			return true;
		}

		$default = array(
			'plugin'=>'',
			'contentId'=>0,
			'uniqueId'=>0,
			'modelName'=>'',
			'fieldName'=>'',
			'accessHierarchy'=>0,
			'downloadPassword'=>'',
			'checkComponentAction'=>'Download',
		);
		$options = array_merge($default, $options);

		if (!empty($newValue)) {
			$Upload = ClassRegistry::init('Upload');

			$fields = array('is_use' => _ON);
			$conditions = array("Upload.id" => $newValue);
			if(!$Upload->updateAll($fields, $conditions)) {
				return false;
			}
			$data = array('UploadLink'=> array(
				'upload_id' => $newValue,
				'plugin' => $options['plugin'],
				'content_id' => $options['contentId'],
				'unique_id' => $options['uniqueId'],
				'model_name' => $options['modelName'],
				'field_name' => $options['fieldName'],
				'access_hierarchy' => $options['accessHierarchy'],
				'is_use' => _ON,
				'download_password' => $options['downloadPassword'],
				'check_component_action' => $options['checkComponentAction'],
			));
			if(!$this->save($data)) {
				return false;
			}
		}
		$fields = array(
			'Upload.is_use' => _OFF,
			'UploadLink.is_use' => _OFF
		);
		$conditions = array("UploadLink.upload_id" => $preValue);
		if(!$this->updateAll($fields, $conditions)) {
			return false;
		}
		return true;
	}

/**
 * WYSIWYG記事の登録更新に伴うアップロード情報の更新
 *
 * @param string $text(ひとつの記事で複数のWYSIWYGがある場合、連結して引数に渡す)
 * @param array $options (
 * 					plugin 					UploadLink.plugin
 * 					contentId				UploadLink.content_id
 * 					uniqueId				UploadLink.unique_id
 * 					modelName				UploadLink.model_name
 * 					fieldName				UploadLink.field_name
 * 					accessHierarchy			UploadLink.access_hierarchy
 * 					downloadPassword		UploadLink.download_password
 * 					checkComponentAction	UploadLink.check_component_action
 * 				)
 * @return boolean success:true error:false
 */
	public function updateUploadInfoForWysiwyg($text, $options=array()) {
		$Upload = ClassRegistry::init('Upload');

		$default = array(
			'plugin'=>'',
			'contentId'=>0,
			'uniqueId'=>0,
			'accessHierarchy'=>0,
			'downloadPassword'=>'',
			'checkComponentAction'=>'Download',
		);
		$options = array_merge($default, $options);

		$dbUploadIdArr = $this->find('list', array(
			'recursive' => 0,
			'fields' => array('UploadLink.upload_id'),
			'conditions' => array(
				'content_id'=>$options['contentId'],
				'unique_id'=>$options['uniqueId'],
				'model_name'=>'Revision',
				'field_name'=>'content',
			)
		));
		$useUploadIdArr = $this->getExtractedUploadId($text);

		$newUploadIdArr = array_diff($useUploadIdArr, $dbUploadIdArr);
		if (!empty($newUploadIdArr)) {
			foreach ($newUploadIdArr as $uploadId) {
				$this->create();
				$data = array(
					'upload_id' => $uploadId,
					'plugin' => $options['plugin'],
					'content_id' => $options['contentId'],
					'unique_id' => $options['uniqueId'],
					'model_name' => 'Revision',
					'field_name' => 'content',
					'access_hierarchy' => isset($options['accessHierarchy']) ? $options['accessHierarchy'] : 0,
					'is_use' => _ON,
					'download_password' => isset($options['downloadPassword']) ? $options['downloadPassword'] : '',
					'check_component_action' => isset($options['checkComponentAction']) ? $options['checkComponentAction'] : 'Download',
				);
				if(!$this->save($data)) {
					return false;
				}
				$fields = array('Upload.is_use' => _ON);
				$conditions = array('Upload.id'=>$uploadId);
				if(!$Upload->updateAll($fields, $conditions)) {
					return false;
				}
			}
		}

		if (!empty($dbUploadIdArr)) {
			$conditions = array(
				'UploadLink.content_id' => $options['contentId'],
				'UploadLink.unique_id' => $options['uniqueId'],
				'UploadLink.model_name' => 'Revision',
				'UploadLink.field_name' => 'content',
			);
			foreach ($dbUploadIdArr as $uploadId) {
				$conditions['UploadLink.upload_id'] = $uploadId;
				if (isset($useUploadIdArr[$uploadId])) {
					// 使用中
					$fields = array(
						"Upload.is_use" => _ON,
						"UploadLink.is_use" => _ON
					);
					if(!$this->updateAll($fields, $conditions)) {
						return false;
					}
				} else {
					// 未使用
					$fields = array('UploadLink.is_use' => _OFF);
					if(!$this->updateAll($fields, $conditions)) {
						return false;
					}

					if (!$this->updateUploadToUnuse($uploadId)) {
						return false;
					}
				}
			}
		}
		return true;
	}

/**
 * 文字列から抽出したアップロードIDの配列を取得
 *
 * @param string $text
 * @return array アップロードID配列
 */
	public function getExtractedUploadId($text) {
		if (empty($text)) {
			return array();
		}

		$searchKey = 'nc-downloads/';
		$count = substr_count($text, $searchKey);
		if (!$count) {
			return array();
		}

		$parts = explode($searchKey, $text);

		$matchUploadIdArr = array();
		for ($i = 1; $i <= $count; $i++) {
			if(!preg_match("/^([0-9]+)/", $parts[$i], $matches)) {
				continue;
			}
			if(!isset($matches[1])) {
				continue;
			}
			$id = $matches[1];
			$matchUploadIdArr[$id] = $id;
		}

		return $matchUploadIdArr;
	}

/**
 * ファイルを未使用に更新
 *
 * @param int $uploadId
 * @return boolean true/false
 */
	public function updateUploadToUnuse($uploadId=0) {
		if (empty($uploadId)) {
			return true;
		}

		$Upload = ClassRegistry::init('Upload');

		$conditions = array('conditions'=>array(
			'UploadLink.upload_id' => $uploadId,
			'UploadLink.is_use' => _ON,
		));
		$UploadLinkCount = $this->find('count', $conditions);
		if($UploadLinkCount > 0) {
			return true;
		}

		$fields = array('Upload.is_use' => _OFF);
		if(!$Upload->updateAll($fields, array('Upload.id' => $uploadId))) {
			return false;
		}
		return true;
	}
}