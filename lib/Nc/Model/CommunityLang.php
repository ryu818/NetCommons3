<?php
/**
 * CommunityLangモデル
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Model
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class CommunityLang extends AppModel
{
	public $validate = array();

	/**
	 * バリデート処理
	 * @param   void
	 * @return  void
	 * @since   v 3.0.0.0
	*/
	public function __construct() {
		parent::__construct();

		//エラーメッセージ取得
		$this->validate = array(
			'community_name' => array(
				'notEmpty'  => array(
					'rule' => array('notEmpty'),
					'last' => true,
					'required' => true,
					'message' => __('Please be sure to input.')
				),
				'maxLength'  => array(
					'rule' => array('maxLength', 30),
					'last' => true ,
					'message' => __('The input must be up to %s characters.', 30)
				)
			),
			'summary' => array(
				'maxLength' => array(
					'rule' => array('maxLength', NC_VALIDATOR_TEXTAREA_LEN),
					'message' => __('The input must be up to %s characters.' , NC_VALIDATOR_TEXTAREA_LEN),
				)
			),
		);
	}

	public function getDefault($page_name = '', $room_id = 0) {
		$data = array();

		$data['CommunityLang']['lang'] = Configure::read(NC_CONFIG_KEY.'.'.'language');
		$data['CommunityLang']['room_id'] = $room_id;
		$data['CommunityLang']['community_name'] = $page_name;
		$data['CommunityLang']['summary'] = '';
		$data['CommunityLang']['description'] = '';
		return $data;
	}
}