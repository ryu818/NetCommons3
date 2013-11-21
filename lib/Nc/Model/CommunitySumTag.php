<?php
/**
 * CommunitySumTagモデル
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Model
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class CommunitySumTag extends AppModel
{
	public $actsAs = array(
		'Common',
	);

	public $validate = array();

	/**
	 * バリデート処理
	 * @param   void
	 * @return  void
	 * @since   v 3.0.0.0
	*/
	public function __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);

		//エラーメッセージ取得
		$this->validate = array(
			'tag_value' => array(
				'notEmpty'  => array(
					'rule' => array('notEmpty'),
					'last' => true,
					'required' => true,
					'message' => __('Please be sure to input.')
				),
				'maxLength'  => array(
					'rule' => array('maxLength', NC_VALIDATOR_TAG_NAME_LEN),
					'last' => true ,
					'message' => __('The input must be up to %s characters.', NC_VALIDATOR_TAG_NAME_LEN)
				)
			),
			// lang
			'used_number' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'required' => true,
					'message' => __('The input must be a number.')
				),
			),
		);
	}
}