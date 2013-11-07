<?php
/**
 * AuthorityPageUserLinkモデル
 *
 * <pre>
 *  権限関連PageUserLink更新処理
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Plugin.Block.Model
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class AuthorityPageUserLink extends AppModel {
	public $useTable = 'page_user_links';
	public $alias = 'PageUserLink';

	public $actsAs = array('Auth' => array('joins' => false, 'afterFind' => false));

/**
 * 「参加者設定画面で使用する」をONからOFFにした場合、該当のPageUserLinkデータをデフォルトに戻す。
 *
 * @param   Model Authority    $preAuthority 変更前Authority
 * @param   Model Authority    $authority 変更後Authority
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function updateDisplayParticipantsEditingFromOnToOff($preAuthority, $authority) {
		if($preAuthority['Authority']['display_participants_editing'] != _OFF && $authority['Authority']['display_participants_editing'] == _OFF) {
			$Authority = ClassRegistry::init('Authority');
			$authorityId = $authority['Authority']['id'];

			$userAuthorityId = $Authority->getUserAuthorityId($authority['Authority']['hierarchy']);

			list($minHierarchy, $maxHierarchy) = $Authority->getHierarchyByUserAuthorityId($userAuthorityId);
			$params = array(
				'fields' => array('Authority.id'),
				'conditions' => array('Authority.hierarchy >=' => $minHierarchy, 'Authority.hierarchy <=' => $maxHierarchy)
			);
			$AuthorityIds = $Authority->find('list', $params);

			$params = array(
				'fields' => $this->getFieldsArray(),
				'conditions' => array('PageUserLink.authority_id' => $AuthorityIds),	// $authorityId
				'joins' => $this->getJoinsArray(),
			);
			$pageUserLinks = $this->find('all', $params);
			if(count($pageUserLinks) > 0) {
				foreach($pageUserLinks as $pageUserLink) {
					$defaultHierarchy = $this->getDefaultHierarchy($pageUserLink);
					$defaultAuthorityId = $Authority->getUserAuthorityId($defaultHierarchy);
					$conditions = array(
						$this->alias.'.id' => $pageUserLink[$this->alias]['id']
					);
					if($defaultAuthorityId == $userAuthorityId) {
						// デフォルト値と同じになるため削除
						if(!$this->deleteAll($conditions)) {
							return false;
						}
					} else if($pageUserLink['PageUserLink']['authority_id'] != $userAuthorityId) {
						// 更新
						$fields = array(
							'PageUserLink.authority_id' => $userAuthorityId
						);
						if(!$this->updateAll($fields, $conditions)) {
							return false;
						}
					}

				}
			}
		}
		return true;
	}

/**
 * ベース権限を現在のものより低い権限を選択した際に、編集した権限の会員(User.authority_id)を検索して、以下の条件でPageUserLink更新・削除。
 * 			1.編集後のベース権限より大きいPageUserLinkデータをパブリック、自分自身ではないマイポータル、マイルーム、「参加者のみ」コミュニティーから取得（ゲストならば ＋サブグループを含むコミュニティーすべて）
 * 			2.PageUserLinkデータから、デフォルト値と同じならば、データ削除。そうでないならば、そのベース権限で更新。
 *
 * @param   Model Authority    $preAuthority 変更前Authority
 * @param   Model Authority    $authority 変更後Authority
 * @param   string|array       $userIds 会員IDの配列OR会員ID	指定しないと、編集するAuthorityから取得
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function updateHierarchyLower($preAuthority, $authority, $userIds = null) {
		if($preAuthority['Authority']['hierarchy'] > $authority['Authority']['hierarchy']) {
			$Authority = ClassRegistry::init('Authority');
			$preUserAuthorityId = $Authority->getUserAuthorityId($preAuthority['Authority']['hierarchy']);
			//if($authority['Authority']['display_participants_editing']) {
			//	$userAuthorityId = $authority['Authority']['id'];
			//} else {
				$userAuthorityId = $Authority->getUserAuthorityId($authority['Authority']['hierarchy']);
			//}
			if($preUserAuthorityId != $userAuthorityId) {
				list($minHierarchy, $maxHierarchy) = $Authority->getHierarchyByUserAuthorityId($userAuthorityId);
				$authorityId = $authority['Authority']['id'];
				if(!isset($userIds)) {
					$User = ClassRegistry::init('User');
					$params = array(
						'fields' => array(
							'User.id'
						),
						'conditions' => array(
							'User.authority_id' => $authorityId
						),
					);
					$userIds = $User->find('list', $params);
				}

				$conditions = array(
					'PageUserLink.user_id' => $userIds,
					'PageAuthority.hierarchy >' => $maxHierarchy,
					'or' => array(
						'Page.space_type' => NC_SPACE_TYPE_PUBLIC,
						array(
							'Page.space_type' => array(NC_SPACE_TYPE_MYPORTAL, NC_SPACE_TYPE_PRIVATE),
							"`PageUserLink`.`room_id`!=`User`.`myportal_page_id`",
							"`PageUserLink`.`room_id`!=`User`.`private_page_id`",
						),
						array(
							'Page.space_type' => NC_SPACE_TYPE_GROUP,
							'Page.thread_num' => 1,
							'Community.participate_flag' => NC_PARTICIPATE_FLAG_ONLY_USER,
						)
					),
				);
				if($userAuthorityId == NC_AUTH_GUEST_ID) {
					// サブグループ以外（コミュニティー）
					$conditions['or']['Page.space_type'] = array(NC_SPACE_TYPE_PUBLIC, NC_SPACE_TYPE_GROUP);
					unset($conditions['or'][1]);
				}

				$params = array(
					'fields' => $this->getFieldsArray(),
					'conditions' => $conditions,
					'joins' => $this->getJoinsArray(),
				);
				$pageUserLinks = $this->find('all', $params);
				if(count($pageUserLinks) > 0) {
					foreach($pageUserLinks as $pageUserLink) {
						$defaultHierarchy = $this->getDefaultHierarchy($pageUserLink);
						$defaultAuthorityId = $Authority->getUserAuthorityId($defaultHierarchy);
						$conditions = array(
							$this->alias.'.id' => $pageUserLink[$this->alias]['id']
						);
						if($defaultAuthorityId == $userAuthorityId) {
							// デフォルト値と同じになるため削除
							if(!$this->deleteAll($conditions)) {
								return false;
							}
						} else if($pageUserLink['PageUserLink']['authority_id'] != $userAuthorityId) {
							// 更新
							$fields = array(
								'PageUserLink.authority_id' => $userAuthorityId
							);
							if(!$this->updateAll($fields, $conditions)) {
								return false;
							}
						}
					}
				}

			}
		}

		return true;
	}

/**
 * AuthorityPageUserLinkモデル共通Fields文
 * @param   void
 * @return  array   $fields
 * @since   v 3.0.0.0
 */
	protected function getFieldsArray() {
		return array(
			'PageUserLink.id',
			'PageUserLink.authority_id',
			'Page.space_type',
			'Page.thread_num',
			'Page.root_id',
			'Community.publication_range_flag',
			'Community.participate_force_all_users',
			'Authority.id',
			'Authority.display_participants_editing',
			'Authority.hierarchy',
		);
	}

/**
 * AuthorityPageUserLinkモデル共通JOIN文
 * @param   void
 * @return  array   $joins
 * @since   v 3.0.0.0
 */
	protected function getJoinsArray() {
		return array(
			array(
				"type" => "INNER",
				"table" => "pages",
				"alias" => "Page",
				"conditions" => "`PageUserLink`.`room_id`=`Page`.`id`"
			),
			array(
				"type" => "LEFT",
				"table" => "communities",
				"alias" => "Community",
				"conditions" => "`Page`.`root_id`=`Community`.`room_id`"
			),
			array(
				"type" => "INNER",
				"table" => "users",
				"alias" => "User",
				"conditions" => "`PageUserLink`.`user_id`=`User`.`id`"
			),
			array(
				"type" => "INNER",
				"table" => "authorities",
				"alias" => "Authority",
				"conditions" => "`User`.`authority_id`=`Authority`.`id`"
			),
			array(
				"type" => "INNER",
				"table" => "authorities",
				"alias" => "PageAuthority",
				"conditions" => "`PageUserLink`.`authority_id`=`PageAuthority`.`id`"
			),
		);
	}
}