<?php
/**
 * AnnouncementEditモデル
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Model
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class AnnouncementEdit extends AppModel
{
	/**
	 * バリデート処理
	 * @param   void
	 * @return  void
	 * @since   v 3.0.0.0
	 */
	public function __construct() {
		parent::__construct();

		/*
		 * エラーメッセージ設定
		*/
		$this->validate = array(
			'content_id' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'required' => true,
					'allowEmpty' => false,
					'message' => __('The input must be a number.')
				)
			),
			'post_hierarchy' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'required' => true,
					'message' => __('The input must be a number.')
				),
				'inList' => array(
					'rule' => array('inList', array(
						NC_AUTH_MIN_GENERAL,
						NC_AUTH_MIN_MODERATE,
						NC_AUTH_MIN_CHIEF,
					), false),
					'allowEmpty' => false,
					'message' => __('It contains an invalid string.')
				)
			),
			'approved_flag' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'last' => true,
					'required' => true,
					'message' => __('The input must be a boolean.')
				)
			),
			'approved_pre_change_flag' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'last' => true,
					'required' => true,
					'message' => __('The input must be a boolean.')
				)
			),
			'approved_mail_flag' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'last' => true,
					'required' => true,
					'message' => __('The input must be a boolean.')
				)
			),
			'approved_mail_subject' => array(
				'maxLength'  => array(
					'rule' => array('maxLength', NC_VALIDATOR_TITLE_LEN),
					'required' => true,
					//'allowEmpty' => true,
					'message' => __('The input must be up to %s characters.', NC_VALIDATOR_TITLE_LEN)
				),
				'_notEmptyCondition'  => array(
					'rule' => array('_notEmptyCondition', array('approved_flag', 'approved_mail_flag')),
					'required' => true,
					//'allowEmpty' => true,
					'message' => __('Please be sure to input.')
				),
			),
			'approved_mail_body' => array(
				'_notEmptyCondition'  => array(
					'rule' => array('_notEmptyCondition', array('approved_flag', 'approved_mail_flag')),
					'required' => true,
					//'allowEmpty' => true,
					'message' => __('Please be sure to input.')
				),
			),
		);
	}

/**
 * メールSubject、メールBody等条件付き必須チェック
 * @param   array    $check
 * @param   array    $columns	$columnsのカラムの値がすべてOnならば必須
 * @return  boolean
 * @since   v 3.0.0.0
 * TODO:共通化するべき
 */
	public function _notEmptyCondition($check, $columns){
		$notEmpty = true;
		foreach($columns as $column) {
			$value = intval($this->data[$this->alias][$column]);
			if($value) {
				$notEmpty = true;
			} else {
				$notEmpty = false;
				break;
			}
		}

		if($notEmpty) {
			$check_arr = array_values($check);
			return Validation::notEmpty($check_arr[0]);
		}
		return true;
	}

/**
 * ブロック追加時初期値
 * @param   integer $contentId
 * @return  Model AnnouncementEdit
 * @since   v 3.0.0.0
 */
	public function findDefault($contentId) {
		$ret = array(
			'AnnouncementEdit' => array(
				'content_id' => $contentId,
				'post_hierarchy' => NC_AUTH_MIN_CHIEF,
				'approved_flag' => _OFF,
				'approved_pre_change_flag' => _ON,
				'approved_mail_flag' => _OFF,
				'approved_mail_subject' => __("[{X-SITE_NAME}] [{X-CONTENT_NAME}] Post Approval completion notice"),
				'approved_mail_body' => __("Your article posted to [{X-SITE_NAME}] [{X-CONTENT_NAME}] was approved.\n\n\n{X-BODY}\n\nClick the link below to check the article.\n{X-URL}"),
				'approved_pre_change_flag' => _ON,
			),
		);

		return $ret;
	}
}