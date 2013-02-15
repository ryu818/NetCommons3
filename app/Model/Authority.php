<?php
/**
 * Authorityモデル
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Model
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class Authority extends AppModel
{
	public $name = 'Authority';
	public $validate = array();

    public function __construct() {
		parent::__construct();

		//エラーメッセージ取得
		$this->validate = array(
			'authority_name' => array(
				'notEmpty'  => array(
									'rule' => array('notEmpty'),
									'last' => true,
									'required' => true,
									'allowEmpty' => false,
									'message' => __('Please be sure to input.')
								),
				'maxlength'  => array(
									'rule' => array('maxLength', 30),
									'message' => __('The input must be up to %s characters.', 30)
								),
				'duplicationAuthorityName'  => array(
									'rule' => array('_duplicationAuthorityName'),
									'message' => __d('authorities', 'Authority with the same name')
								)
			),

			'system_flag' => array(
				'boolean'  => array(
									'rule' => array('boolean'),
									'last' => true,
									'required' => true,
									'allowEmpty' => false,
									'message' => __('The input must be a boolean.')
								)
			),

			'hierarchy' => array(
				'numeric' => array(
										'rule' => 'numeric',
										'required' => true,
										'message' => __('The input must be a number.')
									),
				'range' => array(
										'rule' => array('range', NC_AUTH_GUEST - 1, NC_AUTH_ADMIN + 1),
										'message' => __('The input must be a number bigger than %d and less than %d.', NC_AUTH_GUEST, NC_AUTH_ADMIN)
									)
			),

			'myportal_use_flag' => array(
				'boolean'  => array(
									'rule' => array('numeric'),
									'last' => true,
									'required' => true,
									'allowEmpty' => false,
									'message' => __('The input must be a number.')
								)
			),

			'private_use_flag' => array(
				'boolean'  => array(
									'rule' => array('boolean'),
									'last' => true,
									'required' => false,
									'allowEmpty' => true,
									'message' => __('The input must be a boolean.')
								)
			),

			'public_createroom_flag' => array(
				'boolean'  => array(
									'rule' => array('boolean'),
									'last' => true,
									'required' => false,
									'allowEmpty' => true,
									'message' => __('The input must be a boolean.')
								)
			),

			'group_createroom_flag' => array(
				'boolean'  => array(
									'rule' => array('boolean'),
									'last' => true,
									'required' => false,
									'allowEmpty' => true,
									'message' => __('The input must be a boolean.')
								)
			),

			'myportal_createroom_flag' => array(
				'boolean'  => array(
									'rule' => array('boolean'),
									'last' => true,
									'required' => false,
									'allowEmpty' => true,
									'message' => __('The input must be a boolean.')
								)
			),

			'private_createroom_flag' => array(
				'boolean'  => array(
									'rule' => array('boolean'),
									'last' => true,
									'required' => false,
									'allowEmpty' => true,
									'message' => __('The input must be a boolean.')
								)
			),

			'allow_htmltag_flag' => array(
				'boolean'  => array(
									'rule' => array('boolean'),
									'last' => true,
									'required' => true,
									'allowEmpty' => false,
									'message' => __('The input must be a boolean.')
								)
			),

			'allow_layout_flag' => array(
				'boolean'  => array(
									'rule' => array('boolean'),
									'last' => true,
									'required' => true,
									'allowEmpty' => false,
									'message' => __('The input must be a boolean.')
								)
			),

			'allow_attachment' => array(
				'numeric' => array(
										'rule' => 'numeric',
										'required' => true,
										'message' => __('The input must be a number.')
									),
				'range' => array(
										'rule' => array('range', -1, 3),
										'message' => __('The input must be a number bigger than %d and less than %d.', 0, 2)
									)
			),

			'allow_video' => array(
				'boolean'  => array(
									'rule' => array('boolean'),
									'last' => true,
									'required' => true,
									'allowEmpty' => false,
									'message' => __('The input must be a boolean.')
								)
			),

			'max_size' => array(
				'numeric' => array(
										'rule' => 'numeric',
										'required' => true,
										'message' => __('The input must be a number.')
									)
			),

			'change_leftcolumn_flag' => array(
				'boolean'  => array(
									'rule' => array('boolean'),
									'last' => true,
									'required' => false,
									'allowEmpty' => true,
									'message' => __('The input must be a boolean.')
								)
			),

			'change_rightcolumn_flag' => array(
				'boolean'  => array(
									'rule' => array('boolean'),
									'last' => true,
									'required' => false,
									'allowEmpty' => true,
									'message' => __('The input must be a boolean.')
								)
			),

			'change_headercolumn_flag' => array(
				'boolean'  => array(
									'rule' => array('boolean'),
									'last' => true,
									'required' => false,
									'allowEmpty' => true,
									'message' => __('The input must be a boolean.')
								)
			),

			'change_footercolumn_flag' => array(
				'boolean'  => array(
									'rule' => array('boolean'),
									'last' => true,
									'required' => false,
									'allowEmpty' => true,
									'message' => __('The input must be a boolean.')
								)
			)
	    );
	}

/**
 * 重複チェック
 *
 * @param   array    $data
 * @return  boolean
 */
	protected function _duplicationAuthorityName($data) {
		if(!empty($this->data['Authority']['id']))
			$data['id !='] = $this->data['Authority']['id'];

		$count = $this->find( 'count', array('conditions' => $data, 'recursive' => -1) );
		if($count != 0)
			return false;
		return true;
    }

	public function findAuthSelectHtml() {
		$conditions = array(
			'Authority.hierarchy >=' => NC_AUTH_MIN_GENERAL,
			'Authority.hierarchy <=' => NC_AUTH_CHIEF
		);
		$order = array(
			'Authority.hierarchy' => "ASC",
			'Authority.id' => "ASC"
		);
		$params = array(
						'fields' => array(
							'Authority.id',
							'Authority.authority_name',
							'Authority.hierarchy'
						),
						'conditions' => $conditions,
						'order' => $order,
						'callbacks' => 'after'
						);

		return $this->_afterFind($this->find('all', $params));
	}

	protected function _afterFind($results) {
		$rets = array();
		$select_chief_arr = array();
		$select_moderate_arr = array();
		$select_general_arr = array();

		foreach ($results as $key => $val) {
			$hierarchy = $val['Authority']['hierarchy'];
			if($hierarchy >= NC_AUTH_MIN_ADMIN) {
				continue;
			} else if($hierarchy >= NC_AUTH_MIN_CHIEF) {
				$select_chief_arr[$val['Authority']['id']] = $val['Authority'];
			} else if($hierarchy >= NC_AUTH_MIN_MODERATE) {
				$select_moderate_arr[$val['Authority']['id']] = $val['Authority'];
			} else if($hierarchy >= NC_AUTH_MIN_GENERAL) {
				$select_general_arr[$val['Authority']['id']] = $val['Authority'];
			} else {
				continue;
			}
		}
		$rets[NC_AUTH_CHIEF] = $select_chief_arr;
		$rets[NC_AUTH_MODERATE] = $select_moderate_arr;
		$rets[NC_AUTH_GENERAL] = $select_general_arr;
		$rets[NC_AUTH_GUEST][NC_AUTH_GUEST_ID]['id'] = NC_AUTH_GUEST_ID;
		$rets[NC_AUTH_GUEST][NC_AUTH_GUEST_ID]['authority_name'] = 'Guest';
		$rets[NC_AUTH_GUEST][NC_AUTH_GUEST_ID]['hierarchy'] = NC_AUTH_GUEST;
		$rets[NC_AUTH_OTHER][NC_AUTH_OTHER_ID]['id'] = NC_AUTH_OTHER_ID;
		$rets[NC_AUTH_OTHER][NC_AUTH_OTHER_ID]['authority_name'] = 'Non members';
		$rets[NC_AUTH_OTHER][NC_AUTH_OTHER_ID]['hierarchy'] = NC_AUTH_OTHER;
		return $rets;
	}

/**
 * 参加者のみ取得する必要があるかどうかの確認
 * @param  integer       $authority_id
 * @param  Model Page    $page
 * @return boolean
 * @since  v 3.0.0.0
 */
	public function isParticipantOnly($authority_id, $page) {
		if($page['Page']['thread_num'] > 1) {
			// 子ルームならば、参加会員のみ
			return true;
		}
		if($page['Page']['space_type'] == NC_SPACE_TYPE_PRIVATE || $page['Page']['space_type'] == NC_SPACE_TYPE_MYPORTAL) {
			return true;
		}
		$authority = $this->findById($authority_id);
		if(!isset($authority['Authority'])) {
			return true;
		}

		if($authority['Authority']['allow_new_participant']) {
			return false;
		}
		return true;
	}
}