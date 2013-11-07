<?php
/**
 * UserItemLinkモデル
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Model
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class UserItemLink extends AppModel
{
	public $validate = array();

	public $emailTags = array();

/**
 * バリデート前処理
 *
 * <pre>
 * validatesメソッドを実行すると自動で処理される
 * </pre>
 *
 * @param   array  オプション
 * @return  void
 * @since   v 3.0.0.0
 */
	public function beforeValidate($options = array())
	{
		/*
		 * エラーメッセージ設定(初期化)
		 */
		$this->validate = array(
			'user_id' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'required' => true,
					'allowEmpty' => false,
					'message' => __('The input must be a number.')
				)
			),
			// lang
			'user_item_id' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'required' => true,
					'allowEmpty' => false,
					'message' => __('The input must be a number.')
				)
			),
			'public_flag' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'allowEmpty' => true,
					'message' => __('The input must be a boolean.')
				)
			),
			'email_reception_flag' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'allowEmpty' => true,
					'message' => __('The input must be a boolean.')
				)
			),
			// content
		);

		/*
		 * エラーメッセージ設定
		 */
		if(!isset($this->data['UserItemLink']['user_item_id'])) {
			return true;
		}
		if(!isset($options['items'])) {
			$UserItem = ClassRegistry::init('UserItem');
			$items = $UserItem->findList();
		} else {
			$items = $options['items'];
		}
		$item = array();
		foreach($items as $bufItem) {
			if(count($this->emailTags) == 0) {
				/*
		 		 * emailカラムセット
		 		 */
				if($bufItem['UserItem']['tag_name'] == "" && ($bufItem['UserItem']['type'] == 'email' || $bufItem['UserItem']['type'] == 'mobile_email')) {
					$this->emailTags[] = $bufItem['UserItem']['id'];
				}
			}
			if($this->data['UserItemLink']['user_item_id'] == $bufItem['UserItem']['id']) {
				$item = $bufItem;
			}
		}

		if(!isset($item['UserItemLang']['name'])) {
			$name = $item['UserItem']['default_name'];
		} else {
			$name = $item['UserItemLang']['name'];
		}

		// 必須チェック
		if($item['UserItem']['required'] == _ON) {
			$this->validate['content']['notEmpty'] = array(
				'rule' => array('notEmpty'),
				'last' => true,
				//'required' => true,
				'allowEmpty' => false,
				'message' => __('Please input %s.', $name),
			);
		}

		// 重複チェック
		if($item['UserItem']['allow_duplicate'] == _OFF) {
			if($item['UserItem']['type'] == 'email' || $item['UserItem']['type'] == 'mobile_email') {
				$this->validate['content']['duplicateEmail'] = array(
					'rule' => array('duplicateEmail'),
					//'last' => false,
					'allowEmpty' => true,
					'message' => __('The contents of %s is already in use.', $name),
				);
			} else {
				$this->validate['content']['duplicate'] = array(
					'rule' => array('duplicate'),
					//'last' => false,
					'allowEmpty' => true,
					'message' => __('The contents of %s is already in use.', $name),
				);
			}
		}

		// minlengthチェック
		if($item['UserItem']['minlength'] != '0') {
			$this->validate['content']['minlength'] = array(
				'rule' => array('minLength', $item['UserItem']['minlength']),
				//'last' => false,
				'allowEmpty' => true,
				'message' => __('The input must be at least %s characters.', $item['UserItem']['minlength']),
			);
		}

		// maxlengthチェック
		if($item['UserItem']['maxlength'] != '0') {
			$this->validate['content']['maxLength'] = array(
				'rule' => array('maxLength', $item['UserItem']['maxlength']),
				//'last' => true,
				//'required' => true,
				'allowEmpty' => true,
				'message' => __('The input must be up to %s characters.', $item['UserItem']['maxlength']),
			);
		}

		// emailチェック
		if($item['UserItem']['type'] == 'email' || $item['UserItem']['type'] == 'mobile_email') {
			$this->validate['content']['email'] = array(
				'rule' => array('email'),
				//'last' => true,
				//'required' => true,
				'allowEmpty' => true,
				'message' => __('Unauthorized pattern for %s.',$name),
			);
		}

		// 正規表現チェック
		if($item['UserItem']['regexp'] != '') {
			$this->validate['content']['custom'] = array(
				'rule' => array('custom', $item['UserItem']['regexp']),
				//'last' => true,
				//'required' => true,
				'allowEmpty' => true,
				'message' => __('Unauthorized pattern for %s.', $name),
			);
		}

		return parent::beforeValidate($options);
	}

/**
 * 重複チェック
 *
 * @param   array    $data
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function duplicate($data) {
		if(!empty($this->data[$this->alias]['user_item_id']) && !empty($this->data[$this->alias]['user_id'])) {
			$data['user_item_id !='] = $this->data[$this->alias]['user_item_id'];
			$data['user_id !='] = $this->data[$this->alias]['user_id'];
		}

		$count = $this->find( 'count', array('conditions' => $data, 'recursive' => -1) );
		if($count != 0)
			return false;
		return true;
	}

/**
 * 重複チェック(Email)
 *
 * @param   array    $check
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function duplicateEmail($data) {
		$values = array_values($data);

		$conditions = array(
			'user_item_id' => $this->emailTags,
			'content' => $values[0],
		);
		if(!empty($this->data[$this->alias]['user_id'])) {
			$conditions['user_id !='] = $this->data[$this->alias]['id'];
		}
		$count = $this->find( 'count', array('conditions' => $conditions, 'recursive' => -1) );
		if($count != 0)
			return false;

		// Userテーブルのチェック
		$conditions['or'] = array(
			array('User.email' => $values[0]),
			array('User.mobile_email' => $values[0])
		);
		if(!empty($this->data[$this->alias]['user_id'])) {
			$conditions['id !='] = $this->data[$this->alias]['user_id'];
		}

		$count = $this->find( 'count', array('conditions' => $conditions, 'recursive' => -1) );
		if($count != 0)
			return false;

		return true;
	}

/**
 * $userIdに対応したデータ取得
 *
 * @param  $userId
 * @return Model UserItemLink
 * @since   v 3.0.0.0
 */
	public function findUser($userId = null) {
		if(!isset($userId)) {
			$loginUser = Configure::read(NC_SYSTEM_KEY.'.user');
			$userId = $loginUser['id'];
		}
		$lang = Configure::read(NC_CONFIG_KEY.'.'.'language');
		$conditions = array(
			'user_id' => 'version',
			'lanf' => array('', $lang)
		);
		$params = array(
			'conditions' => $conditions
		);
		return $this->find('all', $params);
	}

/**
 * langがはいっているデータならば、public_flag, email_reception_flag同期化
 * @param   boolean $created
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function afterSave($created) {
		if($this->data[$this->alias]['lang'] != '') {
			$fields = array(
				$this->alias.'.public_flag' => isset($this->data[$this->alias]['public_flag']) ? $this->data[$this->alias]['public_flag'] : _ON,
				$this->alias.'.email_reception_flag' => isset($this->data[$this->alias]['email_reception_flag']) ? $this->data[$this->alias]['email_reception_flag'] : _ON,
			);
			$conditions = array(
				$this->alias.".user_id" => $this->data[$this->alias]['user_id'],
				$this->alias.".user_item_id" => $this->data[$this->alias]['user_item_id']
			);
			$this->updateAll($fields, $conditions);
		}
	}
}