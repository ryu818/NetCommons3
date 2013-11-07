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
	public $actsAs = array(
		'Upload' => array(
			'revision_group_id' => array(
				'checkComponentAction'=>'Page.CommunityDownload.checkRevision',
			),
		),
	);

	public $belongsTo = array(
		'Revision'      => array(
			'foreignKey'    => '',
			'type' => 'LEFT',
			'fields' => array('id', 'group_id', 'content',
				'revision_name', 'is_approved_pointer', 'created', 'created_user_id', 'created_user_name'),
			'conditions' => array(
				'CommunityLang.revision_group_id = Revision.group_id',
				'Revision.pointer' => _ON,
				'Revision.revision_name !=' => 'auto-draft',
				'Revision.is_approved_pointer' => _ON
			),
		),
	);

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
			'revision_group_id' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'allowEmpty' => false,
					'message' => __('The input must be a number.')
				)
			),
		);
	}

	public function getDefault($page_name = '', $room_id = 0) {
		return array(
			'CommunityLang' => array(
				'lang' => Configure::read(NC_CONFIG_KEY.'.'.'language'),
				'room_id' => $room_id,
				'community_name' => $page_name,
				'summary' => '',
				'revision_group_id' => 0,
			),
			'Revision' => array(
				'content' => '',
				'revision_name' => 'publish',
			)
		);
	}
}