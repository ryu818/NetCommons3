<?php
/**
 * PageUserLinkモデル
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Model
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class PageUserLink extends AppModel
{
	public $actsAs = array('Page', 'Auth' => array('joins' => false, 'afterFind' => false));

/**
 * construct
 * @param integer|string|array $id Set this ID for this model on startup, can also be an array of options, see above.
 * @param string $table Name of database table to use.
 * @param string $ds DataSource connection name.
 * @return  void
 * @since   v 3.0.0.0
 */
	public function __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);
	;
	}

/**
 * 参加会員修正時のPageUserLink登録・更新処理
 * @param  Model Page  $page
 * @param  array       $postPageUserLinks 変更後Sessionデータ array(0 => array('user_id', 'authority_id'))
 * @param  array       $prePageUserLinks  変更前DBデータ array(0 => 'PageUserLink' => array('user_id', 'authority_id')))
 * @param  array       $preParentPageUserLinks 変更前親ルームDBデータ array(0 => 'PageUserLink' => array('user_id', 'authority_id'))) 子グループの場合
 * @param  integer     $participantType
 * 						0:参加者のみ表示　
 * 						1:すべての会員表示（変更不可)
 * 						2:すべての会員表示（PageUserLinkにない会員は、default値と不参加のみ変更可）
 * 						3:すべての会員表示（変更可）
 * @return string success error or, reload
 * @since  v 3.0.0.0
 */
	public function saveParticipant($page, $postPageUserLinks, $prePageUserLinks, $preParentPageUserLinks = null, $participantType = null) {
		$roomId = $page['Page']['id'];
		$deleteUserIdArr = array();
		$preUserIdArr = array();
		$postUserIdArr = array();
		$preParentUserIdArr = array();
		$bufPreParentUserIdArr = array();

		foreach($postPageUserLinks as $pageUserLink) {
			$postUserIdArr[$pageUserLink['user_id']] = $pageUserLink['authority_id'];
		}
		foreach($prePageUserLinks as $pageUserLink) {
			$preUserIdArr[$pageUserLink['PageUserLink']['user_id']] = $pageUserLink['PageUserLink']['authority_id'];
		}
		if(isset($preParentPageUserLinks)) {
			// 子グループ
			foreach($preParentPageUserLinks as $pageUserLink) {
				$bufPreParentUserIdArr[$pageUserLink['PageUserLink']['user_id']] = $pageUserLink['PageUserLink']['authority_id'];
			}
		}
		if(isset($preParentPageUserLinks) && isset($participantType) && $participantType == 0) {
			// 子グループ
			//foreach($preParentPageUserLinks as $pageUserLink) {
			//	$preParentUserIdArr[$pageUserLink['PageUserLink']['user_id']] = $pageUserLink['PageUserLink']['authority_id'];
			//}
			$preParentUserIdArr = $bufPreParentUserIdArr;
		} else if(isset($participantType) && $participantType == 0){
			$preParentUserIdArr = $preUserIdArr;
		} else if(isset($participantType) && $participantType != 0){
			$preParentUserIdArr = $postUserIdArr;
		} else {
			return 'error';
		}

		unset($page['Authority']);

		$preParentUserIdArr = $this->varidateParticipant($page, $preParentUserIdArr, $postUserIdArr);
		if($preParentUserIdArr === false) {
			// エラーを上部に表示
			return 'reload';
		}

		foreach($preParentUserIdArr as $userId => $parentAuthorityId) {
			$authorityId = isset($postUserIdArr[$userId]) ? intval($postUserIdArr[$userId]) : null;
			$preAuthorityId = isset($preUserIdArr[$userId]) ? intval($preUserIdArr[$userId]) : null;
			$page['PageUserLink']['user_id'] = $userId;
			$defaultAuthorityId = $this->getDefaultAuthorityId($page);
			if($page['Page']['id'] != $page['Page']['room_id']) {
				// サブグループの新規登録時は、以前のデータはないため、初期化。
				$preAuthorityId = null;
			}
			if(!isset($authorityId) || $preAuthorityId === $authorityId ||
				 ($authorityId == $defaultAuthorityId && $authorityId != NC_AUTH_OTHER_ID)) {
				// 既にあるデータか、default値といっしょなので、Insertしない。
				if($authorityId == $defaultAuthorityId && $authorityId != $preAuthorityId) {
					// delete
					$conditions = array(
						"PageUserLink.user_id" => $userId,
						"PageUserLink.room_id" => $roomId
					);
					if(!$this->deleteAll($conditions)) {
						return 'error';
					}
				}
				continue;
			}

			if($authorityId == NC_AUTH_OTHER_ID) {
				$deleteUserIdArr[] = $userId;
			}
			if($authorityId == NC_AUTH_OTHER_ID && $defaultAuthorityId == NC_AUTH_OTHER_ID) {
				// 会員のみ参加ルームで不参加にしたため、削除
				$conditions = array(
					"PageUserLink.user_id" => $userId,
					"PageUserLink.room_id" => $roomId
				);
				if(!$this->deleteAll($conditions)) {
					return 'error';
				}
			} else if(isset($preAuthorityId)) {
				// 更新
				$fields = array(
					'authority_id' => $authorityId
				);
				$conditions = array(
					'room_id' => $roomId,
					'user_id' => $userId
				);
				if(!$this->updateAll($fields, $conditions)) {
					return 'error';
				}
			} else {
				// 登録
				$this->create();
				$pageUserLink = array(
					'PageUserLink' => array(
						'room_id' => $roomId,
						'user_id' => $userId,
						'authority_id' => $authorityId
					)
				);
				if(!$this->save($pageUserLink)) {
					return 'error';
				}
			}
		}

		//
		// 公開ルームではないならば、子供のルームを求め不参加会員を削除する。
		// 		親ルームで参加していた会員を不参加に変更すると、親ルームには参加していないが、子ルームに参加している
		//		状態になってしまうため
		// 公開ルームならば、子供のルームも不参加として登録
		if(count($deleteUserIdArr) == 0) {
			return 'success';
		}
		$Page = ClassRegistry::init('Page');
		$roomIdListArr = array();
		$child_pages = $Page->findChilds('all', $page);
		if(count($child_pages) > 0) {
			foreach($child_pages as $child_page) {
				if($child_page['Page']['id'] == $child_page['Page']['room_id']) {
					$roomIdListArr[] = $child_page['Page']['id'];
				}
			}
		}
		if(count($roomIdListArr) == 0) {
			return 'success';
		}
		if($defaultAuthorityId == NC_AUTH_OTHER_ID) {
			// 非公開ルーム
			$conditions = array(
				"PageUserLink.user_id" => $deleteUserIdArr,
				"PageUserLink.room_id" => $roomIdListArr
			);
			$this->deleteAll($conditions);
		} else {
			// 公開ルーム
			foreach($roomIdListArr as $child_room_id) {
				foreach($deleteUserIdArr as $userId) {
					$params = array(
						'fields' => array(
								'PageUserLink.id'
						),
						'conditions' => array(
								'PageUserLink.room_id' => $child_room_id,
								'PageUserLink.user_id' => $userId
						),
					);

					$pageUserLink = $this->find('first', $params);
					if(!isset($pageUserLink['PageUserLink'])) {
						// 登録
						$this->create();
						$pageUserLink = array(
							'PageUserLink' => array(
								'room_id' => $child_room_id,
								'user_id' => $userId,
								'authority_id' => NC_AUTH_OTHER_ID
							)
						);
						if(!$this->save($pageUserLink)) {
							return 'error';
						}
					} else {
						// 更新
						$fields = array(
							'authority_id' => NC_AUTH_OTHER_ID
						);
						$conditions = array(
							'room_id' => $child_room_id,
							'user_id' => $userId
						);
						if(!$this->updateAll($fields, $conditions)) {
							return 'error';
						}
					}
				}
			}
		}

		return 'success';
	}

/**
 * 会員参加バリデート
 * ・パブリックで不参加にはできない。
 * ・会員が存在しない。
 * ・権限が存在しないか、リストに表示されない権限
 * ・コミュニティーを退会・参加者修正する場合は「コミュニティー修正、参加者修正」できる人がいるかどうかのチェックとする。
 *  但し、「参加者のみ」コミュニティーの場合、「参加者のみ」のコミュニティーを作成できる主担がいるかどうかのチェック
 *  チェックし、一人も該当会員がいない場合、falseを返す。
 * @param  Model Page  $page
 * @param  array       $preParentUserIdArr array (user_id => authority_id)	現在の参加者情報
 * @param  array       $postUserIdArr  array (user_id => authority_id)		更新する参加者情報
 * @return boolean false|array       $preParentUserIdArr array (user_id => authority_id)	現在の参加者情報
 * @since  v 3.0.0.0
 */
	public function varidateParticipant($page, $preParentUserIdArr, $postUserIdArr) {
		$User = ClassRegistry::init('User');
		$Authority = ClassRegistry::init('Authority');
		$isCommunityEdit = ($page['Page']['space_type'] == NC_SPACE_TYPE_GROUP) ? false : true;

		$authorities = $Authority->find('list', array(
			'fields' => array(
				'Authority.id',
				'Authority.hierarchy'
			),
			'conditions' => array('display_participants_editing' => _ON)
		));

		foreach($preParentUserIdArr as $userId => $parentAuthorityId) {
			$authorityId = isset($postUserIdArr[$userId]) ? intval($postUserIdArr[$userId]) : null;

			if($page['Page']['space_type'] == NC_SPACE_TYPE_PUBLIC && $authorityId == NC_AUTH_OTHER_ID) {
				// パブリックで不参加にはできない。
				unset($preParentUserIdArr[$userId]);
				continue;
			}

			$activeUser = $User->findById($userId);
			if(!isset($activeUser['User'])) {
				// 会員が存在しない。
				unset($preParentUserIdArr[$userId]);
				continue;
			}
			if(!isset($authorities[$authorityId]) && $authorityId != NC_AUTH_OTHER_ID) {
				// 権限が存在しないか、リストに表示されない権限
				unset($preParentUserIdArr[$userId]);
				continue;
			}

			$minHierarchy = $Authority->getMinHierarchy(isset($authorities[$authorityId]) ? $authorities[$authorityId] : NC_AUTH_OTHER);

			if($page['Page']['space_type'] != NC_SPACE_TYPE_GROUP || $activeUser['Authority']['hierarchy'] == NC_AUTH_GUEST ||
				($page['Page']['thread_num'] == 1 && $page['Community']['participate_flag'] == NC_PARTICIPATE_FLAG_ONLY_USER)) {
				// パブリック、自分自身以外のマイポータル、「参加者のみ」のコミュニティーでは、自分の権限以上にはなれない。
				// 但し、パブリック以外のルーム下に、さらにルームを作成した場合は、自分以上にもなれる。
				if($Authority->getMinHierarchy($activeUser['Authority']['hierarchy']) < $minHierarchy) {
					unset($preParentUserIdArr[$userId]);
					continue;
				}
			}
			if(!$isCommunityEdit && $minHierarchy >= NC_AUTH_MIN_CHIEF) {
				// コミュニティーを退会・参加者修正する場合は「コミュニティー修正、参加者修正」できる人がいるかどうかのチェックとする。
				// 但し、「参加者のみ」コミュニティーの場合、「参加者のみ」のコミュニティーを作成できる主担がいるかどうかのチェック
				if($activeUser['Authority']['allow_creating_community'] != NC_ALLOW_CREATING_COMMUNITY_OFF) {
					if($page['Community']['participate_flag'] == NC_PARTICIPATE_FLAG_ONLY_USER) {
						if($activeUser['Authority']['allow_new_participant']) {
							$isCommunityEdit = true;
						}
					} else {
						$isCommunityEdit = true;
					}
				}
			}
		}
		if(!$isCommunityEdit) {
			if($page['Community']['participate_flag'] == NC_PARTICIPATE_FLAG_ONLY_USER) {
				$this->invalidate('authority_id', __d('page', 'Chief who "can add a participation member freely" has to set the one, if community of "only participating users". Please try again.'));
			} else {
				$this->invalidate('authority_id', __d('page', 'Chief who can edit the community has to set the one.Please try again.'));
			}
			return false;
		}

		return $preParentUserIdArr;
	}

/**
 * コミュニティー参加中かどうか
 *  強制参加のコミュニティー以外で使用可能
 * @param  integer $roomId
 * @param  integer $userId
 * @return boolean false|integer authority_id
 * @since  v 3.0.0.0
 */
	public function isParticipate($roomId, $userId) {
		// 既に参加中かどうかチェック
		$params = array(
			'fields' => array(
				'PageUserLink.authority_id'
			),
			'conditions' => array(
				'PageUserLink.room_id' => $roomId,
				'PageUserLink.user_id' => $userId,
			)
		);
		$pageUserLink = $this->find('first', $params);
		if(!$pageUserLink) {
			// 参加していない
			return false;
		}
		return intval($pageUserLink[$this->alias]['authority_id']);
	}

/**
 * コミュニティー退会可能かどうか
 *  「参加者のみ」のコミュニティー以外で使用可能
 *  人的管理ができるルームの主担が一人もいなくならないようにチェック。
 * @param  integer $roomId
 * @param  integer $userId
 * @return boolean
 * @since  v 3.0.0.0
 */
	public function isResign($roomId, $userId) {
		$Authority = ClassRegistry::init('Authority');
		$authorityIds = $Authority->find('list', array(
			'fields' => array(
				'Authority.id'
			),
			'conditions' => array(
				'Authority.hierarchy >=' => NC_AUTH_MIN_CHIEF,
				'Authority.allow_creating_community !=' => NC_ALLOW_CREATING_COMMUNITY_OFF,
				//'Authority.display_participants_editing' => _ON
			)
		));

		$params = array(
			'fields' => array(
				'PageUserLink.id'
			),
			'joins' => array(
				array(
					"type" => "INNER",
					"table" => "communities",
					"alias" => "Community",
					"conditions" => "`PageUserLink`.`room_id`=`Community`.`room_id`"
				),
			),
			'conditions' => array(
				'PageUserLink.room_id' => $roomId,
				'PageUserLink.user_id !=' => $userId,
				'PageUserLink.authority_id' => $authorityIds,
			)
		);
		$pageUserLink = $this->find('first', $params);
		if(!$pageUserLink) {
			// コミュニティー編集できる会員がいなくなるためエラー
			return false;
		}
		return true;
	}

/**
 * 主担の会員のみ取得
 * @param  integer $roomId
 * @return boolean false|array userIds
 * @since  v 3.0.0.0
 */
	public function findChiefByRoomId($roomId) {
		// 既に参加中かどうかチェック
		$params = array(
			'fields' => array(
				'PageUserLink.user_id'
			),
			'conditions' => array(
				'PageUserLink.room_id' => $roomId,
				'Authority.hierarchy >=' => NC_AUTH_MIN_CHIEF,
			),
			'joins' => array(
				array(
					"type" => "INNER",
					"table" => "authorities",
					"alias" => "Authority",
					"conditions" => "`Authority`.`id`=`PageUserLink`.`authority_id`"
				),
			),
		);
		$pageUserLinks = $this->find('list', $params);
		if(!$pageUserLinks) {
			// 主担なし
			return false;
		}
		return $pageUserLinks;
	}
}