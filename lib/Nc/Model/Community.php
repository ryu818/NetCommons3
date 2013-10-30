<?php
/**
 * Communityモデル
 * <pre>
 * コミュニティーの仕様
 *  公開範囲
 * 		公開（すべてのログイン会員が閲覧可能）
 * 		非公開（コミュニティー参加者のみが閲覧可能）
 *
 * 参加方法
 * 		参加受付制(希望者は誰でも参加可能）
 * 		主担の承認が必要
 * 		招待制（コミュニティー参加者から招待を受けた会員のみ参加可能）
 * 		参加会員のみ
 *
 * ・公開コミュニティーは、参加コミュニティー一覧には含めない。
 * ・公開コミュニティーでも、「参加する」クリックで、参加コミュニティー一覧に含める。
 * ・メール送信では、公開コミュニティーであってもコミュニティー参加者のみにメールを送信する（ゲストには送信しない）。
 * ・「全会員を強制的に参加させる。」コミュニティーでは、全会員にメール送信できる。
 * ・コミュニティー作成時に公開範囲で公開を選択した場合、強制的にゲストとして全会員に閲覧を許す。
 * ・管理者であれば（権限管理で設定可（「全会員を強制的に参加させる。」コミュニティー作成可以上の権限））、「全会員を強制的に参加させる。」のチェックボックスを表示する。
 *  コミュニティー一覧にも表示させる。参加方法も「参加会員のみ」固定。
 * </pre>
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
			'participate_force_all_users' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'last' => true,
					'required' => true,
					'message' => __('The input must be a boolean.')
				),
				'isAllowCreatingCommunityIfOn'  => array(
					'rule' => array('_isAllowCreatingCommunityIfOn'),
					'last' => true,
					'message' => __('Authority Error!  You do not have the privilege to access this page.')
				),
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
 * もし、ONならばallow_creating_communityがNC_ALLOW_CREATING_COMMUNITY_FORCE_ALLかNC_ALLOW_CREATING_COMMUNITY_ADMINかどうかチェック
 * 但し、すでにONならばチェックしない。
 * @param   array    $check
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function _isAllowCreatingCommunityIfOn($check){
		$keys = array_keys($check);
		$key = $keys[0];
		$check = array_shift($check);

		if($check == _ON) {
			// TODO: ログインSession情報の$loginUser['allow_creating_community']でも同様の処理ができるが、考慮はしていない。
			if(!empty($this->data[$this->alias]['id'])) {
				$community = $this->find('first', array(
					'fields' => array($key),
					'conditions' => array('id' => $this->data[$this->alias]['id'])
				));
				if(isset($community[$this->alias]) && !empty($community[$this->alias][$key])) {
					return true;
				}
			}
			$Authority = ClassRegistry::init('Authority');
			$loginUser = Configure::read(NC_SYSTEM_KEY.'.user');
			$authority = $Authority->findById($loginUser['authority_id']);
			if(!isset($authority[$Authority->alias]) ||
				($authority[$Authority->alias]['allow_creating_community'] != NC_ALLOW_CREATING_COMMUNITY_FORCE_ALL &&
				$authority[$Authority->alias]['allow_creating_community'] != NC_ALLOW_CREATING_COMMUNITY_ADMIN)) {
				return false;
			}
		}
		return true;
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
		$data['Community']['participate_force_all_users'] = _OFF;
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