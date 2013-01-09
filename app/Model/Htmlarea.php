<?php
/**
 * Htmlareaモデル
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Model
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class Htmlarea extends AppModel
{
	public $name = 'Htmlarea';

	public $validate = array();

	public function __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);
		$this->validate = array(
			'content' => array(
				'notEmpty'  => array(
					'rule' => array('notEmpty'),
					'last' => true,
					'message' => __('Please be sure to input.',true)
				),
				'maxLength' => array(
					'rule' => array('maxLength', NC_VALIDATOR_WYSIWYG_LEN),
					'message' => __('The input must be up to %s characters.' , NC_VALIDATOR_WYSIWYG_LEN),
				)
			)
		);
	}

	public function beforeValidate($options = array()) {
		if(preg_match('/^\s*<div><\/div>\s*$/iu', $this->data['Htmlarea']['content']) || preg_match('/^\s*<br\s*\/?>\s*$/iu', $this->data['Htmlarea']['content'])) {
			$this->data['Htmlarea']['content'] = "";
		}
		//$this->data['Htmlarea']['content'] = $this->cleanHTML($this->data['Htmlarea']['content']);
		return true;
	}
}