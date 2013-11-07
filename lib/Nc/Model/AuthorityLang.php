<?php
/**
 * AuthorityLangモデル
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Model
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class AuthorityLang extends AppModel
{
	public $validate = array();

	public function __construct() {
		parent::__construct();

		//エラーメッセージ取得
		$this->validate = array(
			'name' => array(
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
				'duplicationAuthorityName'  => array(
					'rule' => array('_duplicationAuthorityName'),
					'message' => __d('authority', 'Authority with the same name')
				)
			),
			// lang
		);
	}

/**
 * 重複チェック
 *
 * @param   array    $data
 * @return  boolean
 */
	public function _duplicationAuthorityName($data) {
		if(!empty($this->data['AuthorityLang']['id']))
			$data['id !='] = $this->data['AuthorityLang']['id'];

		$lang = Configure::read(NC_CONFIG_KEY.'.'.'language');
		$data['lang'] = $lang;

		$count = $this->find( 'count', array('conditions' => $data, 'recursive' => -1) );
		if($count != 0)
			return false;
		return true;
	}
}