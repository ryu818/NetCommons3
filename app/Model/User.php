<?php
/**
 * Userモデル
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Model
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class User extends AppModel
{
	public $name = 'User';

	public $belongsTo = 'Authority';

/**
 * Behavior name
 *
 * @var array
 */
	public $actsAs = array('Timezone');

	public $validate = array();

	public $email_tags = array();

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
		 * エラーメッセージ設定
		 */
	    //App::import('Model','Item');
	    App::uses('Item', 'Model');
		$this->Item = new Item();

	    $params = array(
				'order' => array(
					'Item.col_num' => 'ASC',
					'Item.row_num' => 'ASC'
				),
				'recursive' => -1
		);
		$items = $this->Item->find('all', $params);
		/*
		 * emailカラムセット
		 */
		$this->email_tags = array();
		foreach($items as $item) {
			if($item['Item']['type'] == NC_ITEM_TYPE_EMAIL || $item['Item']['type'] == NC_ITEM_TYPE_MOBILE_EMAIL) {
				$this->email_tags[$item['Item']['id']] = $item['Item']['tag_name'];
			}
		}

		foreach($items as $item) {
			if($item['Item']['tag_name'] == "") {
				continue;
			}

			// 必須チェック
			if($item['Item']['require_flag'] == _ON) {
				if($item['Item']['tag_name'] != 'password' || empty($this->data[$this->alias]['id'])) {
					// パスワードで編集ならば必須としない
					$this->validate[$item['Item']['tag_name']]['notEmpty'] = array(
						'rule' => array('notEmpty'),
						'last' => true,
						//'required' => true,
						'allowEmpty' => false,
						'message' => __('Please input %s.', __d('user_items', $item['Item']['item_name'])),
					);
				}
			}

			// 重複チェック
			if($item['Item']['duplicate_flag'] == _ON) {
				if($item['Item']['type'] == NC_ITEM_TYPE_EMAIL || $item['Item']['type'] == NC_ITEM_TYPE_MOBILE_EMAIL) {
					$this->validate[$item['Item']['tag_name']]['_duplicateEmail'] = array(
						'rule' => array('_duplicateEmail'),
						//'last' => false,
						'allowEmpty' => true,
						'message' => __('The contents of %s is already in use.', __d('user_items', $item['Item']['item_name'])),
					);
				} else {
					$this->validate[$item['Item']['tag_name']]['_duplicate'] = array(
						'rule' => array('_duplicate'),
						//'last' => false,
						'allowEmpty' => true,
						'message' => __('The contents of %s is already in use.', __d('user_items', $item['Item']['item_name'])),
					);
				}
			}

			// minlengthチェック
			if($item['Item']['minlength'] != '0') {
				$this->validate[$item['Item']['tag_name']]['minlength'] = array(
					'rule' => array('minLength', $item['Item']['minlength']),
					//'last' => false,
					'allowEmpty' => true,
					'message' => __('The input must be at least %s characters.', $item['Item']['minlength']),
				);
			}

			// maxlengthチェック
			if($item['Item']['maxlength'] != '0') {
				$this->validate[$item['Item']['tag_name']]['maxlength'] = array(
					'rule' => array('maxLength', $item['Item']['maxlength']),
					//'last' => true,
					//'required' => true,
					'allowEmpty' => true,
					'message' => __('The input must be up to %s characters.', $item['Item']['maxlength']),
				);
			}

			// emailチェック
			if($item['Item']['type'] == NC_ITEM_TYPE_EMAIL || $item['Item']['type'] == NC_ITEM_TYPE_MOBILE_EMAIL) {
				$this->validate[$item['Item']['tag_name']]['email'] = array(
					'rule' => array('email'),
					//'last' => true,
					//'required' => true,
					'allowEmpty' => true,
					'message' => __('Unauthorized pattern for %s.', __d('user_items', $item['Item']['item_name'])),
				);
			}

			// 正規表現チェック
			if($item['Item']['regexp'] != '') {
				$this->validate[$item['Item']['tag_name']]['email'] = array(
					'rule' => array('custom', $item['Item']['regexp']),
					//'last' => true,
					//'required' => true,
					'allowEmpty' => true,
					'message' => __('Unauthorized pattern for %s.', __d('user_items', $item['Item']['item_name'])),
				);
			}

			// authority_idチェック
			/*if($item['Item']['tag_name'] == 'authority_id') {
				$this->validate[$item['Item']['tag_name']]['chkAuthorityId'] = array(
					'rule' => array('_chkAuthorityId'),
					//'last' => true,
					//'required' => true,
					'allowEmpty' => false,
					'message' => __('Unauthorized request.<br />Please reload the page.')
				);
			}*/

			// permalinkチェック
			if($item['Item']['tag_name'] == 'permalink') {
				$this->validate[$item['Item']['tag_name']]['invalidPermalink'] = array(
					'rule' => array('_invalidPermalink'),
					'last' => true,
					'message' => __('It contains an invalid string.')
				);
			}
		}

		return parent::beforeValidate($options);
	}

/**
 * 権限IDチェック
 * set(User)時にAuthorityのデータもセットしておく
 *
 * @param   array    $data
 * @return  boolean
 * @since   v 3.0.0.0
 */
/* TODO:使用方法を再検討
	protected function _chkAuthorityId($data) {
		if(!isset($this->data['Authority']))
			return false;
		return true;
    }
*/

/**
 * 重複チェック
 *
 * @param   array    $data
 * @return  boolean
 * @since   v 3.0.0.0
 */
	protected function _duplicate($data) {
		if(!empty($this->data[$this->alias]['id']))
			$data['id !='] = $this->data[$this->alias]['id'];

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
	protected function _duplicateEmail($data) {
		$values = array_values($data);

		$conditions['or'] = array(
									array('User.email' => $values[0]),
									array('User.mobile_email' => $values[0])
							);
		if(!empty($this->data[$this->alias]['id']))
			$conditions['id !='] = $this->data[$this->alias]['id'];

		$count = $this->find( 'count', array('conditions' => $conditions, 'recursive' => -1) );
		if($count != 0)
			return false;

		// $this->email_tagsからusers_items_linkテーブルのチェックを行う必要がある
		// TODO:未作成
		return true;
    }

/**
 * リンク識別子に不正な文字列がないかのバリデータ
 *
 * @param  array     $data
 * @return boolean
 * @since   v 3.0.0.0
 */
	protected function _invalidPermalink($data) {
		if($data['permalink'] == '') {
			return true;
		}
		$permalink = $data['permalink'];
		if(preg_match(NC_PERMALINK_PROHIBITION, $permalink)) {
			return false;
		}

		$mypotal_prefix = NC_SPACE_MYPORTAL_PREFIX;
		if($mypotal_prefix != '') {
			$chk_permalink = $mypotal_prefix.'/'.$permalink;
		} else {
			$chk_permalink = $permalink;
		}
		if(preg_match(NC_PERMALINK_PROHIBITION_DIR_PATTERN, $chk_permalink.'/')) {
			return false;
		}

		$private_prefix = NC_SPACE_PRIVATE_PREFIX;
		if($private_prefix != '') {
			$chk_permalink = $private_prefix.'/'.$permalink;
		} else {
			$chk_permalink = $permalink;
		}
		if(preg_match(NC_PERMALINK_PROHIBITION_DIR_PATTERN, $chk_permalink.'/')) {
			return false;
		}

		return true;
    }

/**
 * パスワードhash化
 * @param   array $options
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function beforeSave($options = array()) {
        $this->data[$this->alias]['password'] = AuthComponent::password($this->data[$this->alias]['password']);
        return true;
    }
/**
 * 最終ログイン日時更新
 * @param   array $user
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function updLastLogin($user) {
		$this->create();
		$this->id = $user[$this->alias]['id'];
		$user[$this->alias]['previous_login'] = $user[$this->alias]['last_login'];
		$user[$this->alias]['last_login'] = $this->date();
		$this->set($user);
		$fields = array('last_login', 'previous_login');

		$ret = $this->save($user, true, $fields);
		if(!$ret) {
			return false;
		}
		return true;
    }
}