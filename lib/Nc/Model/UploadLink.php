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
					'maxLength'  => array(
						'rule' => array('maxLength', NC_VALIDATOR_TITLE_LEN),
						'message' => __('The input must be up to %s characters.', NC_VALIDATOR_TITLE_LEN)
					)
				),
				'field_name' => array(
					'notEmpty'  => array(
						'rule' => array('notEmpty'),
						'message' => __('Please be sure to input.')
					),
					'maxLength'  => array(
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
					'maxLength'  => array(
						'rule' => array('maxLength', NC_VALIDATOR_TITLE_LEN),
						'message' => __('The input must be up to %s characters.', NC_VALIDATOR_TITLE_LEN)
					)
				),
				'check_component_action' => array(
					'notEmpty'  => array(
						'rule' => array('notEmpty'),
						'message' => __('Please be sure to input.')
					),
					'maxLength'  => array(
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
 * @param integer $newValue 更新後のアップロードID
 * @param integer $preValue 更新前のアップロードID
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
 * @param string $text
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
			'plugin' => 'Common',
			'contentId' => 0,
			'modelName' => 'Revision',
			'fieldName' => 'content',
			'uniqueId' => 0,
			'accessHierarchy' => 0,
			'downloadPassword' => '',
			'checkComponentAction' => 'Download',
		);
		$options = array_merge($default, $options);

		$uploadLinks = $this->find('list', array(
			'recursive' => -1,
			'fields' => array($this->alias.'.upload_id', $this->alias.'.check_component_action'),
			'conditions' => array(
				'plugin' => $options['plugin'],
				'content_id' => $options['contentId'],
				'model_name' => $options['modelName'],
				'field_name' => $options['fieldName'],
				'unique_id' => $options['uniqueId'],
			)
		));
		$dbUploadIdArr = array_keys($uploadLinks);

		$results = $this->getExtractedUploadId($text);
		$useUploadIdArr = array();
		$useUploadOwnerUserIdArr = array();
		if(count($results) > 0) {
			list($useUploadIdArr, $useUploadOwnerUserIdArr) = $results;
		}

		$newUploadIdArr = array_diff($useUploadIdArr, $dbUploadIdArr);
		if (!empty($newUploadIdArr)) {
			// 新しく追加したファイル
			foreach ($newUploadIdArr as $key => $uploadId) {
				$this->create();
				$data = array($this->alias => array(
					'upload_id' => $uploadId,
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
				$data[$this->alias]['check_component_action'] = $this->checkComponentAction($data[$this->alias]['check_component_action'], $useUploadOwnerUserIdArr[$key]);
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
				$this->alias.'.plugin' => $options['plugin'],
				$this->alias.'.content_id' => $options['contentId'],
				$this->alias.'.unique_id' => $options['uniqueId'],
				$this->alias.'.model_name' => $options['modelName'],
				$this->alias.'.field_name' => $options['fieldName'],
			);
			foreach ($dbUploadIdArr as $uploadId) {
				$fields = array(
					$this->alias.".access_hierarchy" =>$options['accessHierarchy'],
					$this->alias.".is_use" => _ON,
					$this->alias.".download_password" => '"' .$options['downloadPassword'] . '"',
					$this->alias.".check_component_action" => '"' .$options['checkComponentAction'] . '"',
				);
				$conditions[$this->alias.'.upload_id'] = $uploadId;

				if (isset($useUploadIdArr[$uploadId])) {
					// 使用中
					if($uploadLinks[$uploadId] == 'Download.checkAdmin') {
						$fields[$this->alias.'.check_component_action'] = '"' .$this->checkComponentAction($options['checkComponentAction'], $useUploadOwnerUserIdArr[$uploadId]) . '"';
					}
					if(!$this->updateAll($fields, $conditions)) {
						return false;
					}
					$uploadFields = array(
						"Upload.is_use" => _ON
					);
					$uploadConditions['Upload.id'] = $uploadId;
					if(!$Upload->updateAll($uploadFields, $uploadConditions)) {
						return false;
					}
				} else {
					// 未使用
					$fields[$this->alias.'.is_use'] = _OFF;
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
 * @return array (アップロードID配列, onwerUserIdの配列)
 */
	public function getExtractedUploadId($text) {
		if (empty($text)) {
			return array();
		}

		$baseUrl = Router::url('/', true);
		$searchKey = $baseUrl.'nc-downloads/';
		$count = substr_count($text, $searchKey);
		if (!$count) {
			return array();
		}

		$parts = explode($searchKey, $text);
		$matchUploadIdArr = array();

		$matchUploadFileNameArr = array();

		for ($i = 1; $i <= $count; $i++) {
			if(preg_match ( "/^([0-9]+)(_[a-zA-Z_-]+)\.(.+?)['\"]{1}/" , $parts[$i], $matches)) {
				$uploadId = $matches[1];
				$thumbnailPostFix = $matches[2];
				$extension = $matches[3];
			} else if(preg_match ( "/^([0-9]+)\.(.+?)['\"]{1}/" , $parts[$i], $matches)) {
				$uploadId = $matches[1];
				$thumbnailPostFix = '';
				$extension = $matches[2];
			} else {
				continue;
			}

			if(isset($matchUploadFileNameArr[$uploadId])) {
				$matchUploadFileNameArr[$uploadId] = array();
			}
			$matchUploadFileNameArr[$uploadId][] = $uploadId.$thumbnailPostFix.".".$extension;

			$matchUploadIdArr[$uploadId] = $uploadId;
		}
		// 存在しているものだけ返す
		$Upload = ClassRegistry::init('Upload');
		$uploads = $Upload->find('all', array('fields' => array('Upload.id', 'Upload.user_id', 'Upload.plugin', 'Upload.file_path'), 'conditions' => array($Upload->primaryKey => $matchUploadIdArr)));
		$matchUploadIdArr = array();
		$onwerUserIdArr = array();
		foreach($uploads as $upload) {
			$exists = false;
			$fileNames = $matchUploadFileNameArr[$upload['Upload']['id']];
			foreach($fileNames as $fileName) {
				if(file_exists(NC_UPLOADS_DIR.$upload['Upload']['plugin'].DS.$upload['Upload']['file_path'].$fileName)) {
					$exists = true;
					break;
				}
			}
			if($exists) {
				$matchUploadIdArr[$upload['Upload']['id']] = $upload['Upload']['id'];
				$onwerUserIdArr[$upload['Upload']['id']] = $upload['Upload']['user_id'];
			}
		}
		return array($matchUploadIdArr, $onwerUserIdArr);
	}

/**
 * ファイルを未使用に更新
 *
 * @param integer $uploadId
 * @return boolean true/false
 */
	public function updateUploadToUnuse($uploadId = 0) {
		if (empty($uploadId)) {
			return true;
		}

		$Upload = ClassRegistry::init('Upload');

		$conditions = array('conditions'=>array(
			$this->alias.'.upload_id' => $uploadId,
			$this->alias.'.is_use' => _ON,
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

/**
 * 自分自身OR管理者ならばuploadLinkに閲覧権限を追加。
 * 			それ以外、check_component_actionを「Download.checkAdmin」にし、自分自身OR管理者ならば閲覧を許す。
 * 			(一般１(一般)、一般２(主担)が同じコミュニティーに参加しており、一般１のコミュニティー内部に画像挿入（Upload.id:1)、
 *			その内容を一般２がコピーし、コミュニティー紹介に貼り付け。ログインしていなくても画像がみえてしまう等の現象を防ぐため。）
 * @param   string $checkComponentAction
 * @param   integer $ownerUserId
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function checkComponentAction($checkComponentAction, $ownerUserId) {
		$Authority = ClassRegistry::init('Authority');

		$loginUser = Configure::read(NC_SYSTEM_KEY.'.user');
		$loginUserId = $loginUser['id'];
		$isAdmin = ($Authority->getUserAuthorityId($loginUser['hierarchy']) == NC_AUTH_ADMIN_ID) ? true : false;
		if(!$isAdmin && $loginUserId != $ownerUserId) {
			return 'Download.checkAdmin';
		}
		return $checkComponentAction;
	}
}