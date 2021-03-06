<?php
/**
 * UserItemモデル
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Model
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class UserItem extends AppModel
{
	public $order = array("UserItem.list_num" => "ASC", "UserItem.col_num" => "ASC", "UserItem.row_num" => "ASC");

/**
 * construct
 * @param integer|string|array $id Set this ID for this model on startup, can also be an array of options, see above.
 * @param string $table Name of database table to use.
 * @param string $ds DataSource connection name.
 * @return  void
 * @since   v 3.0.0.0
 */
	public function __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);

		/*
		 * エラーメッセージ設定
		 */
		$this->validate = array(
			'default_name' => array(
				'notEmpty'  => array(
					'rule' => array('notEmpty'),
					'last' => true,
					'required' => true,
					'allowEmpty' => false,
					'message' => __('Please be sure to input.')
				),
				'maxLength'  => array(
					'rule' => array('maxLength', 30),
					'message' => __('The input must be up to %s characters.', 30)
				),
			),
			// default_description
			// default_options
			'type' => array(
				'inList' => array(
					'rule' => array('inList', array(
						'text',
						'password',
						'email',
						'mobile_email',
						'select',
						'file',
						'label',
						'radio',
						'textarea',
						'checkbox',
						//'wysiwyg',
					), false),
					'allowEmpty' => false,
					'message' => __('It contains an invalid string.')
				)
			),
			'tag_name' => array(
				'inList' => array(
					'rule' => array('inList', array(
						'login_id',
						'password',
						'handle',
						'email',
						'mobile_email',
						'timezone_offset',
						'lang',
						'authority_id',
						'is_active',
						'permalink',
						'avatar',
						'created',
						'created_user_name',
						'modified',
						'modified_user_name',
						'password_regist',
						'last_login',
						'previous_login',
					), false),
					'allowEmpty' => true,
					'message' => __('It contains an invalid string.')
				)
			),
			'is_system' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'allowEmpty' => true,
					'message' => __('The input must be a boolean.')
				)
			),
			'allow_self_edit' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'allowEmpty' => true,
					'message' => __('The input must be a boolean.')
				)
			),
			'required' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'allowEmpty' => true,
					'message' => __('The input must be a boolean.')
				)
			),
			'allow_duplicate' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'allowEmpty' => true,
					'message' => __('The input must be a boolean.')
				)
			),
			'minlength' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'allowEmpty' => true,
					'message' => __('The input must be a number.')
				)
			),
			'minlength' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'allowEmpty' => true,
					'message' => __('The input must be a number.')
				)
			),
			'maxLength' => array(
				'maxLength'  => array(
					'rule' => array('maxLength', NC_VALIDATOR_VARCHAR_LEN),
					'message' => __('The input must be up to %s characters.', NC_VALIDATOR_VARCHAR_LEN)
				)
			),
			'display_flag' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'message' => __('The input must be a boolean.')
				)
			),
			'allow_public_flag' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'message' => __('The input must be a boolean.')
				)
			),
			'allow_email_reception_flag' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'message' => __('The input must be a boolean.')
				)
			),
			'list_num' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'allowEmpty' => false,
					'message' => __('The input must be a number.')
				),
				'range'  => array(
					'rule' => array('range', -1, 10000),
					'last' => true,
					'allowEmpty' => false,
					'message' => __('The input must be a number bigger than %d and less than %d.', 0, 9999)
				)
			),
			'col_num' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'allowEmpty' => false,
					'message' => __('The input must be a number.')
				),
				'range'  => array(
					'rule' => array('range', -1, NC_USER_MAX_COL_NUM + 1),
					'last' => true,
					'allowEmpty' => false,
					'message' => __('The input must be a number bigger than %d and less than %d.', 0, NC_USER_MAX_COL_NUM)
				)
			),
			'row_num' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'allowEmpty' => false,
					'message' => __('The input must be a number.')
				),
				'range'  => array(
					'rule' => array('range', -1, 10000),
					'last' => true,
					'allowEmpty' => false,
					'message' => __('The input must be a number bigger than %d and less than %d.', 0, 9999)
				)
			),
			//attribute
			//default_selected
			'display_title' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'message' => __('The input must be a boolean.')
				)
			),
			'is_lang' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'message' => __('The input must be a boolean.')
				)
			),
			// 自動登録時の入力項目
			'autoregist_use' => array(
				'inList' => array(
					'rule' => array('inList', array(
						'required',		// 必須
						'optional',		// 任意
						'hide',			// 非表示
						'disabled',		// 使用不可
					), false),
					'allowEmpty' => false,
					'message' => __('It contains an invalid string.')
				)
			),
			// 自動登録時の入力項目のメール送信有無
			'autoregist_sendmail' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'message' => __('The input must be a boolean.')
				)
			),
		);
	}

/**
 * 項目設定　追加・編集時初期値
 * @param   void
 * @return  Model UserItem UserItemLang
 * @since   v 3.0.0.0
 */
	public function findDefault() {
		$ret = array(
			'UserItem' => array(
				'id' => 0,
				'default_name' => '',
				'default_description' => '',
				'default_options' => '',
				'type' => 'text',
				'tag_name' => '',
				'is_system' => _OFF,
				'allow_self_edit' => _ON,
				'required' => _OFF,
				'allow_duplicate' => _ON,
				'minlength' => '',
				'maxlength' => '',
				'regexp' => '',
				'display_flag' => NC_DISPLAY_FLAG_ON,
				'allow_public_flag' => _OFF,
				'allow_email_reception_flag' => _OFF,
				//'list_num' => 0,
				//'col_num' => 0,
				//'row_num' => 0,
				'attribute' => '',
				'default_selected' => '',
				'display_title' => _ON,
				'is_lang' => _OFF,
				'autoregist_use' => 'hide',
				'autoregist_sendmail' => _ON,
			),
			'UserItemLang' => array(
				'name' => '',
				'description' => '',
				'options' => '',
				'lang' => '',
			)
		);

		return $ret;
	}

/**
 * 一覧取得
 * @param   string    $type first or all or list
 * @param   array     $addConditions
 * @param   array     $fields
 * @return Model Modules
 */
	public function findList($type = 'all', $addConditions = array(), $fields = array()) {
		$lang = Configure::read(NC_CONFIG_KEY.'.'.'language');
		$conditions = array();
		if(count($addConditions) > 0) {
			$conditions = array_merge($conditions, $addConditions);
		}
		if(count($fields) == 0) {
			$fields = array(
				'UserItem.*',
				'UserItemLang.name', 'UserItemLang.description', 'UserItemLang.options'
			);
		}

		$params = array(
			'fields' => $fields,
			'joins' => array(
				array("type" => "INNER",
					"table" => "user_item_langs",
					"alias" => "UserItemLang",
					"conditions" => array(
						"`UserItemLang`.`user_item_id`=`UserItem`.`id`",
						"`UserItemLang`.`lang`" => $lang
					)
				),
			),
			'conditions' => $conditions,
		);

		return $this->find($type, $params);
	}
}