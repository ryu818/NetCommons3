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
/**
 * Behavior name
 *
 * @var array
 */
	public $actsAs = array(
		'Upload' => array(
			'photo' => array(
				'fileType' => 'image',
				'checkComponentAction'=>'Page.CommunityDownload',
				//'deleteOnUpdate' => true,
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
			'is_upload' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'last' => true,
					'required' => true,
					'message' => __('The input must be a boolean.')
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
			'participate_as_general' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'last' => true,
					'required' => true,
					'message' => __('The input must be a boolean.')
				)
			),
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
			'invite_hierarchy' => array(
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
			'is_participate_notice' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'last' => true,
					'required' => true,
					'message' => __('The input must be a boolean.')
				)
			),
			'participate_notice_hierarchy' => array(
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
			'is_resign_notice' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'last' => true,
					'required' => true,
					'message' => __('The input must be a boolean.')
				)
			),
			'resign_notice_hierarchy' => array(
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
		);
	}

/**
 * コミュニティーデフォルト値取得
 * @param   void
 * @return  Model Community
 * @since   v 3.0.0.0
 */
	public function getDefault() {
		$data = array();

		$data['Community']['photo'] = 'community.gif';	// TODO:test
		$data['Community']['is_upload'] = _OFF;
		// TODO:Configにコミュニティーのデフォルトの公開範囲の仕方を変更できるフラグから設定する。
		$data['Community']['publication_range_flag'] = NC_PUBLICATION_RANGE_FLAG_ONLY_USER;
		$data['Community']['participate_as_general'] = _OFF;
		// TODO:Configにコミュニティーのデフォルトの公開された場合の権限を設定する。ゲストOr一般,モデレータ
		$data['Community']['publication_authority'] = NC_AUTH_GUEST_ID;
		// TODO:Configにコミュニティーのデフォルトの参加受付の仕方を変更できるフラグから設定する。
		$data['Community']['participate_flag'] = NC_PARTICIPATE_FLAG_ONLY_USER;
		$data['Community']['invite_hierarchy'] = NC_AUTH_MIN_CHIEF;
		$data['Community']['is_participate_notice'] = _ON;
		$data['Community']['participate_notice_hierarchy'] = NC_AUTH_MIN_CHIEF;
		$data['Community']['is_resign_notice'] = _ON;
		$data['Community']['resign_notice_hierarchy'] = NC_AUTH_MIN_CHIEF;

		return $data;
	}

/**
 * コミュニティー情報取得
 * @param   integer        $room_id
 * @return  mixed false or array    array(Model community Model Communitylang , Model CommunitiesTag['tag_values'])
 * @since   v 3.0.0.0
 */
	public function getCommunityData($room_id) {
		$CommunityLang = ClassRegistry::init('CommunityLang');
		$CommunityTag = ClassRegistry::init('CommunityTag');

		$lang = Configure::read(NC_CONFIG_KEY.'.'.'language');

		$conditions = array(
			'Community.room_id' => $room_id
		);
		$community = $this->find('first', array(
			'recursive' => -1,
			'conditions' => $conditions
		));
		if(!isset($community['Community'])) {
			return false;
		}

		$conditions = array(
			'CommunityLang.room_id' =>  $room_id,
			'CommunityLang.lang' => $lang
		);
		$community_lang = $CommunityLang->find('first', array(
			'conditions' => $conditions
		));
		if(!isset($community_lang['CommunityLang'])) {
			$community_lang = $CommunityLang->getDefault('', $room_id);
		}

		$communities_tag['CommunityTag']['tag_value'] = $CommunityTag->findCommaDelimitedTags($room_id, $lang);

		return array($community, $community_lang, $communities_tag);
	}
}