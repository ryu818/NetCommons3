<?php
/**
 * Communityモデル
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Model
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class Community extends AppModel
{
	public $name = 'Community';
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
			'upload_id' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'allowEmpty' => true,
					'message' => __('The input must be a number.')
				)
			),
				// TODO:publication_range_flag admin_hierarchyのチェックが必要
			'publication_range_flag' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'required' => true,
					'message' => __('The input must be a number.')
				),
				'range' => array(
					'rule' => array('range', NC_PUBLICATION_RANGE_FLAG_ONLY_USER - 1, NC_PUBLICATION_RANGE_FLAG_ALL + 1),
					'message' => __('The input must be a number bigger than %d and less than %d.', NC_PUBLICATION_RANGE_FLAG_ONLY_USER, NC_PUBLICATION_RANGE_FLAG_ALL)
				)
			),
			// TODO:participate_flag admin_hierarchyのチェックが必要
			'participate_flag' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'required' => true,
					'message' => __('The input must be a number.')
				),
				'range' => array(
					'rule' => array('range', NC_PARTICIPATE_FLAG_ONLY_USER - 1, NC_PARTICIPATE_FLAG_FREE + 1),
					'message' => __('The input must be a number bigger than %d and less than %d.', NC_PARTICIPATE_FLAG_ONLY_USER, NC_PARTICIPATE_FLAG_FREE)
				)
			),
			'invite_authority' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'required' => true,
					'message' => __('The input must be a number.')
				),
				'range' => array(
					'rule' => array('range', NC_AUTH_CHIEF_ID - 1, NC_AUTH_GENERAL_ID + 1),
					'message' => __('The input must be a number bigger than %d and less than %d.', NC_AUTH_CHIEF_ID, NC_AUTH_GENERAL_ID)
				)
			),
			'participate_notice_flag' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'last' => true,
					'required' => true,
					'allowEmpty' => false,
					'message' => __('The input must be a boolean.')
				)
			),
			'participate_notice_authority' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'required' => true,
					'message' => __('The input must be a number.')
				),
				'range' => array(
					'rule' => array('range', NC_AUTH_CHIEF_ID - 1, NC_AUTH_GENERAL_ID + 1),
					'message' => __('The input must be a number bigger than %d and less than %d.', NC_AUTH_CHIEF_ID, NC_AUTH_GENERAL_ID)
				)
			),
			'resign_notice_flag' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'last' => true,
					'required' => true,
					'allowEmpty' => false,
					'message' => __('The input must be a boolean.')
				)
			),
			'resign_notice_authority' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'required' => true,
					'message' => __('The input must be a number.')
				),
				'range' => array(
					'rule' => array('range', NC_AUTH_CHIEF_ID - 1, NC_AUTH_GENERAL_ID + 1),
					'message' => __('The input must be a number bigger than %d and less than %d.', NC_AUTH_CHIEF_ID, NC_AUTH_GENERAL_ID)
				)
			),
		);
	}

	public function getDefault() {
		$data = array();

		$data['Community']['photo'] = 'community.gif';	// TODO:test
		$data['Community']['upload_id'] = 0;
		// TODO:Configにコミュニティーのデフォルトの公開範囲の仕方を変更できるフラグから設定する。
		$data['Community']['publication_range_flag'] = NC_PUBLICATION_RANGE_FLAG_ONLY_USER;
		// TODO:Configにコミュニティーのデフォルトの公開された場合の権限を設定する。ゲストOr一般,モデレータ
		$data['Community']['publication_authority'] = NC_AUTH_GUEST_ID;
		// TODO:Configにコミュニティーのデフォルトの参加受付の仕方を変更できるフラグから設定する。
		$data['Community']['participate_flag'] = NC_PARTICIPATE_FLAG_ONLY_USER;
		$data['Community']['invite_authority'] = NC_AUTH_CHIEF_ID;
		$data['Community']['participate_notice_flag'] = _ON;
		$data['Community']['participate_notice_authority'] = NC_AUTH_CHIEF_ID;
		$data['Community']['resign_notice_flag'] = _ON;
		$data['Community']['resign_notice_authority'] = NC_AUTH_CHIEF_ID;

		return $data;
	}
}