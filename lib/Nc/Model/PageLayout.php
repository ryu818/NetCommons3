<?php
/**
 * PageLayoutモデル
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Model
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class PageLayout extends AppModel
{
/**
 * Behavior
 *
 * @var array
 */
	public $actsAs = array('PageStyle');

/**
 * バリデート処理
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);
		
		$defaultValidate = $this->constructDefault();

		$this->validate = array_merge($defaultValidate, array(
			'type' => array(
				'inList' => array(
					'rule' => array('inList', array(
						_OFF,
					), false),
					'allowEmpty' => false,
					'message' => __('It contains an invalid string.')
				),
			),
			'is_display_header' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'last' => true,
					'required' => true,
					'message' => __('The input must be a boolean.')
				)
			),
			'is_display_left' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'last' => true,
					'required' => true,
					'message' => __('The input must be a boolean.')
				)
			),
			'is_display_right' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'last' => true,
					'required' => true,
					'message' => __('The input must be a boolean.')
				)
			),
			'is_display_footer' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'last' => true,
					'required' => true,
					'message' => __('The input must be a boolean.')
				)
			),
		));
	}
}
