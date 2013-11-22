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
 * 			「全会員を強制的に参加させる。」
 *
 * 1.公開コミュニティーは、参加コミュニティー一覧には含めない。
 * 2.公開コミュニティーでも、「参加する」クリックで、参加コミュニティー一覧に含める。
 * 3.メール送信では、公開コミュニティーであってもコミュニティー参加者のみにメールを送信する（ゲストには送信しない）。
 * 4.「全会員を強制的に参加させる。」コミュニティーでは、全会員にメール送信できる。
 * 5.コミュニティー作成時に公開範囲で公開を選択した場合、強制的にゲストとして全会員に閲覧を許す。
 * 6.管理者であれば（権限管理で設定可（「全会員を強制的に参加させる。」コミュニティー作成可以上の権限））、「全会員を強制的に参加させる。」のチェックボックスを表示する。
 *  コミュニティー一覧にも参加しているコミュニティーとして表示させる。
 * 7.権限管理に「コミュニティーで主担ならば、参加会員を自由に追加でき、参加方法で「参加者のみ」を選択可能とする。」
 * （以下、※１とする）があり、会員権限が主担以上で設定可能とする。
 * 8.ページメニュー、会員管理：パブリック、自分自身以外のマイポータル、「参加者のみ」のコミュニティーでは、自分の権限以上にはなれない。
 * 		(hierarchyの上下ではなく、ベースの会員権限が同じならば変更可能とする)
 * 		但し、パブリック以外のルーム下に、さらにルームを作成した場合は、自分以上にもなれる(ゲスト以外)。
 * 9.コミュニティー作成権限がない会員は、コミュニティーで主担にしてもコミュニティー修正、参加者修正できなくする。
 *  ->人的管理をしない。
 * 10.※１にチェックがついていない会員が「参加者のみ」のコミュニティーを編集する場合、「参加方法」の変更はできなくする。
 *  （参加者のみ->参加受付制への変更は許さない。）
 * 11.コミュニティーを退会・参加者修正する場合に「コミュニティー修正、参加者修正」できる人がいるかどうかのチェックとする。
 *  但し、「参加者のみ」コミュニティーの場合、「参加者のみ」のコミュニティーを作成できる主担がいるかどうかのチェック
 *  とする。
 * （参加者修正では、主担から別なルーム権限へ変更された会員がチェック対象）
 * 12.「参加会員のみ」に設定すると「全会員を強制的に参加させる（作成権限があれば）」のチェックボックスを有効にする。
 * 13.「参加者のみ」のコミュニティーは、退会機能は動作させない。
 * 14.ゲスト権限は、マイルーム、マイポータル以外では、主担になりえない。
 *
 * --------------------------------------------------------------------------------------------------------------------
 * ルーム管理的な利用方法の設定
 * 管理者：
 * 	すべてのコミュニティー作成・編集・表示順変更・削除。
 * 	（公開、及び、非公開＋すべてのコミュニティーの表示順変更・削除）
 * 	※１がチェックされている。
 * 主担
 * 	コミュニティー作成不可
 * 	※１がチェックされている。
 * 一般
 * 	コミュニティー作成不可
 *
 * ・コミュニティーは管理者が作る。
 * ・コミュニティーの内部の管理は管理者、主担が行う。
 * ・デフォルト：「参加者のみ」のコミュニティーが設定されている状態。-> 一般は、主担にはなりえない。
 *
 * --------------------------------------------------------------------------------------------------------------------
 *
 * 参加受付制の利用方法の設定
 *
 * 管理者：
 * 	すべてのコミュニティー作成・編集・表示順変更・削除。
 * 	（公開、及び、非公開＋すべてのコミュニティーの表示順変更・削除）
 * 	※１がチェックされている。
 * 主担
 * 	公開コミュニティー作成可能（公開、及び、非公開）
 * 	※１がチェックされている。
 * 一般
 * 	非公開のコミュニティー作成可能
 *
 * ・コミュニティーはみんな（一般が主）が作る。
 * ・コミュニティーの内部の管理はみんな（一般が主）が行う。
 * ・デフォルト：「参加受付制」のコミュニティーが設定されている状態。-> 一般は、主担になりえる。
 *
 * --------------------------------------------------------------------------------------------------------------------
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
		'Common',
	);
	public $validate = array();

/**
 * construct
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);

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
			'publication_range_flag' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'required' => true,
					'message' => __('The input must be a number.')
				),
				'range' => array(
					'rule' => array('range', NC_PUBLICATION_RANGE_FLAG_ONLY_USER - 1, NC_PUBLICATION_RANGE_FLAG_ALL + 1),
					'message' => __('The input must be a number bigger than %d and less than %d.', NC_PUBLICATION_RANGE_FLAG_ONLY_USER, NC_PUBLICATION_RANGE_FLAG_ALL)
				),
				'isAllowCreatingCommunityIfOn'  => array(
					'rule' => array('_isAllowCreatingCommunityIfOn'),
					'last' => true,
					'message' => __('Authority Error!  You do not have the privilege to access this page.')
				),
			),
			'participate_force_all_users' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'last' => true,
					'required' => true,
					'message' => __('The input must be a boolean.')
				),
				'checkParticipateForceAllUsers'  => array(
					'rule' => array('_checkParticipateForceAllUsers'),
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
				),
				'isAllowParticipateFlagIfOnlyParticipant'  => array(
					'rule' => array('_isAllowParticipateFlagIfOnlyParticipant'),
					'last' => true,
					'message' => __('Authority Error!  You do not have the privilege to access this page.')
				),
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
 * 公開コミュニティーが作成できるかどうか。権限管理でのコミュニティーの作成権限からチェックを行う。
 * <pre>
 * もし、ONならばAuthority.allow_creating_communityがNC_ALLOW_CREATING_COMMUNITY_FORCE_ALLかNC_ALLOW_CREATING_COMMUNITY_ADMIN,
 * NC_ALLOW_CREATING_COMMUNITY_ALL_USERかどうかチェック
 * 但し、すでにONならばチェックしない。
 * </pre>
 *
 * @param   array    $check
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function _isAllowCreatingCommunityIfOn($check){
		$keys = array_keys($check);
		$key = $keys[0];
		$check = array_shift($check);

		if($check != _OFF) {
			// TODO: ログインSession情報の$loginUser['allow_creating_community']でも同様の処理ができるが、再取得。
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
			$checkArr = array(NC_ALLOW_CREATING_COMMUNITY_ALL_USER, NC_ALLOW_CREATING_COMMUNITY_FORCE_ALL, NC_ALLOW_CREATING_COMMUNITY_ADMIN);
			if(!isset($authority[$Authority->alias]) || !in_array($authority[$Authority->alias]['allow_creating_community'], $checkArr)) {
				return false;
			}
		}
		return true;
	}

/**
 * 参加方法の「参加者のみ」に変更できるかどうかのチェック　権限管理の「コミュニティーで主担ならば、参加会員を自由に追加でき、参加方法で「参加者のみ」を選択可能とする。」
 * がONならば、変更可能。
 * <pre>
 * もし、NC_PARTICIPATE_FLAG_ONLY_USERならばAuthority.allow_new_participantがONかどうかチェック
 * 但し、すでにONならばチェックしない。
 * また、すでにNC_PARTICIPATE_FLAG_ONLY_USERに設定されており、Authority.allow_new_participantがONではないとエラー。
 * </pre>
 * @param   array    $check
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function _isAllowParticipateFlagIfOnlyParticipant($check){
		$keys = array_keys($check);
		$key = $keys[0];
		$check = array_shift($check);
		$Authority = ClassRegistry::init('Authority');
		$loginUser = Configure::read(NC_SYSTEM_KEY.'.user');
		$authority = $Authority->findById($loginUser['authority_id']);
		$isAllowNewParticipant = true;
		if(!isset($authority[$Authority->alias]) || empty($authority[$Authority->alias]['allow_new_participant'])) {
			$isAllowNewParticipant = false;
		}

		if($check == NC_PARTICIPATE_FLAG_ONLY_USER) {
			// TODO: ログインSession情報の$loginUser['allow_new_participant']でも同様の処理ができるが、再取得。
			if(!empty($this->data[$this->alias]['id'])) {
				$community = $this->find('first', array(
					'fields' => array($key),
					'conditions' => array('id' => $this->data[$this->alias]['id'])
				));
				if(isset($community[$this->alias]) && $community[$this->alias][$key] == NC_PARTICIPATE_FLAG_ONLY_USER) {
					return true;
				}
			}
			if(!$isAllowNewParticipant) {
				return false;
			}
		} else if(!$isAllowNewParticipant && !empty($this->data[$this->alias]['id'])) {
			// すでにNC_PARTICIPATE_FLAG_ONLY_USERに設定されており、Authority.allow_new_participantがONではないとエラー。
			$community = $this->find('first', array(
				'fields' => array($key),
				'conditions' => array('id' => $this->data[$this->alias]['id'])
			));
			if(isset($community[$this->alias]) && $community[$this->alias][$key] == NC_PARTICIPATE_FLAG_ONLY_USER) {
				return false;
			}
		}
		return true;
	}

	/**
	 * 「全会員を強制的に参加させる。」権限チェック
	 * 参加方法が「参加会員のみ」で、かつ、権限管理の「コミュニティーで主担ならば、参加会員を自由に追加でき、参加方法で「参加者のみ」がONならばOK
	 * 但し、すでにONならばチェックしない。
	 *
	 * @param   array    $check
	 * @return  boolean
	 * @since   v 3.0.0.0
	 */
	public function _checkParticipateForceAllUsers($check){
		$keys = array_keys($check);
		$key = $keys[0];
		$check = array_shift($check);
		if(!empty($check)) {
			$participateFlag = isset($this->data[$this->alias]['participate_flag']) ? $this->data[$this->alias]['participate_flag'] : null;
			if(!empty($this->data[$this->alias]['id'])) {

				$community = $this->find('first', array(
					'fields' => array($key, 'participate_flag'),
					'conditions' => array('id' => $this->data[$this->alias]['id'])
				));
				if(!isset($participateFlag)) {
					$participateFlag = $community[$this->alias]['participate_flag'];
				}
			}
			if($participateFlag != NC_PARTICIPATE_FLAG_ONLY_USER) {
				return false;
			}
			if(isset($community[$this->alias]) && !empty($community[$this->alias][$key])) {
				// すでにONならばチェックしない。
				return true;
			}
			$Authority = ClassRegistry::init('Authority');
			$loginUser = Configure::read(NC_SYSTEM_KEY.'.user');
			$authority = $Authority->findById($loginUser['authority_id']);
			if(!isset($authority[$Authority->alias]) || empty($authority[$Authority->alias]['allow_new_participant'])) {
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
		$Config = ClassRegistry::init('Config');
		$configs = $Config->findList('list', 0, NC_COMMUNITY_CATID);
		$loginUser = Configure::read(NC_SYSTEM_KEY.'.user');

		$data['Community']['photo'] = 'community.gif';	// TODO:test
		$data['Community']['is_upload'] = _OFF;
		if(!in_array($loginUser['allow_creating_community'], array(NC_ALLOW_CREATING_COMMUNITY_ALL_USER, NC_ALLOW_CREATING_COMMUNITY_FORCE_ALL, NC_ALLOW_CREATING_COMMUNITY_ADMIN))) {
			$data['Community']['publication_range_flag'] = NC_PUBLICATION_RANGE_FLAG_ONLY_USER;
		} else {
			$data['Community']['publication_range_flag'] = $configs['community_default_publication_range'];
		}
		if($configs['community_default_participate_flag'] == NC_PARTICIPATE_FLAG_ONLY_USER && $loginUser['allow_new_participant'] != _ON) {
			$data['Community']['participate_flag'] =NC_PARTICIPATE_FLAG_FREE;
		} else {
			$data['Community']['participate_flag'] = $configs['community_default_participate_flag'];
		}
		$data['Community']['participate_force_all_users'] = _OFF;
		$data['Community']['invite_hierarchy'] = NC_AUTH_MIN_CHIEF;
		$data['Community']['is_participate_notice'] = $configs['community_participate_sendmail'];
		$data['Community']['participate_notice_hierarchy'] = NC_AUTH_MIN_CHIEF;
		$data['Community']['is_resign_notice'] = $configs['community_withdraw_sendmail'];
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

/**
 * コミュニティー検索条件取得
 * @param   array paginate配列
 * @param   object $request
 * @return  void
 * @since   v 3.0.0.0
 */
	public function getSearchParams(&$paginate, $request) {
		if(!isset($request->data['CommunitySearch']['text']) && isset($request->named['search_communities_text'])) {
			$request->data['CommunitySearch']['text'] = $request->named['search_communities_text'];
		}
		if(!isset($request->data['CommunitySearch']['disclosed_communities']) && isset($request->named['disclosed_communities'])) {
			$request->data['CommunitySearch']['disclosed_communities'] = $request->named['disclosed_communities'];
		}

		$requestData = $request->data;
		if(isset($requestData['CommunitySearch']['text']) && $requestData['CommunitySearch']['text'] != '') {
			// 現在表示中の言語、page_name内から検索している。
			// researchmapでは、他の言語のものも検索対象にしているがここでは対処しない。
			$requestData['CommunitySearch']['text'] = $this->escapeLikeString(trim(mb_convert_kana( $requestData['CommunitySearch']['text'], "s")));
			$paginate['conditions']['or'] = array(
				'Page.page_name LIKE' => '%' . $requestData['CommunitySearch']['text'] . '%',
				'CommunityLang.community_name LIKE' => '%' . $requestData['CommunitySearch']['text'] . '%',
				'CommunityLang.summary LIKE' => '%' . $requestData['CommunitySearch']['text'] . '%',
				'Revision.content LIKE' => '%' . $requestData['CommunitySearch']['text'] . '%',
			);
			$paginate['joins'] = array(
				"type" => "LEFT",
				"table" => "revisions",
				"alias" => "Revision",
				"conditions" => array(
					"`Revision`.`group_id`=`CommunityLang`.`revision_group_id`",
					"Revision.pointer" => _ON,
				)
			);
		}
		if(!empty($requestData['CommunitySearch']['disclosed_communities'])) {
			$paginate['conditions']['Community.publication_range_flag'] = array(NC_PUBLICATION_RANGE_FLAG_LOGIN_USER, NC_PUBLICATION_RANGE_FLAG_ALL);
		}
	}
}