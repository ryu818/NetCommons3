<?php
/**
 * Page Test Case
 *
 */
class NcPageTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'NcPage',
		'NcAuthority',
		'NcPageTree',
		'NcPageUserLink',
		'NcUser',
		'NcCommunity',
		'NcCommunityLang',
		'NcSession',
		'NcConfig',
		'NcRevision',
	);


/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->Page = ClassRegistry::init('Page');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Page);
		parent::tearDown();
	}

/**
 * testFindDefault method
 *
 * @return void
 */
	public function testGetDefaultData() {

		//パブリックスペースのページを作るとき用の基本データ
		$result = array (
			'Page' =>
			array (
				'root_id' => 0,
				'parent_id' => 1,
				'thread_num' => 1,
				'display_sequence' => 0,
				'position_flag' => 1,
				'lang' => '',
				'is_page_meta_node' => 0,
				'is_page_style_node' => 0,
				'is_page_layout_node' => 0,
				'is_page_theme_node' => 0,
				'is_page_column_node' => 0,
				'space_type' => 1,
				'show_count' => 0,
				'display_flag' => 1,
				'display_apply_subpage' => 1,
				'is_approved' => 1,
				'lock_authority_id' => 0,
				'page_name' => 'Public room',
			),
		);

		$ck = $this->Page->getDefaultData(NC_SPACE_TYPE_PUBLIC);
		$this->assertEqual($result , $ck);

		//マイポータルのページを作るとき用の基本データ
		$result = array (
			'Page' =>
			array (
				'root_id' => 0,
				'parent_id' => 2,
				'thread_num' => 1,
				'display_sequence' => 0,
				'position_flag' => 1,
				'lang' => '',
				'is_page_meta_node' => 0,
				'is_page_style_node' => 0,
				'is_page_layout_node' => 0,
				'is_page_theme_node' => 0,
				'is_page_column_node' => 0,
				'space_type' => 2,
				'show_count' => 0,
				'display_flag' => 1,
				'display_apply_subpage' => 1,
				'is_approved' => 1,
				'lock_authority_id' => 0,
				'page_name' => 'Myportal',
			)
		);
		$ck = $this->Page->getDefaultData(NC_SPACE_TYPE_MYPORTAL);
		$this->assertEqual($result , $ck);

		//プライベートスペース（マイルーム）
		$result = array (
			'Page' =>
			array (
				'root_id' => 0,
				'parent_id' => 3,
				'thread_num' => 1,
				'display_sequence' => 0,
				'position_flag' => 1,
				'lang' => '',
				'is_page_meta_node' => 0,
				'is_page_style_node' => 0,
				'is_page_layout_node' => 0,
				'is_page_theme_node' => 0,
				'is_page_column_node' => 0,
				'space_type' => 3,
				'show_count' => 0,
				'display_flag' => 1,
				'display_apply_subpage' => 1,
				'is_approved' => 1,
				'lock_authority_id' => 0,
				'page_name' => 'Private room',
			),
		);
		$ck = $this->Page->getDefaultData(NC_SPACE_TYPE_PRIVATE );
		$this->assertEqual($result , $ck);

		//コミュニティー
		$result = array (
			'Page' =>
			array (
				'root_id' => 0,
				'parent_id' => 4,
				'thread_num' => 1,
				'display_sequence' => 0,
				'position_flag' => 1,
				'lang' => '',
				'is_page_meta_node' => 0,
				'is_page_style_node' => 0,
				'is_page_layout_node' => 0,
				'is_page_theme_node' => 0,
				'is_page_column_node' => 0,
				'space_type' => 4,
				'show_count' => 0,
				'display_flag' => 1,
				'display_apply_subpage' => 1,
				'is_approved' => 1,
				'lock_authority_id' => 0,
				'page_name' => 'Community',
			),
		);
		$ck = $this->Page->getDefaultData(NC_SPACE_TYPE_GROUP);
		$this->assertEqual($result , $ck);

	}

/**
 * testFindAuthById method
 *
 * @return void
 */
	public function testFindAuthById() {

		//Page.idを指定しなかった場合
		//falseよりもnullが適切な気もする。
		$pageIdArr = array();
		$ck = $this->Page->findAuthById($pageIdArr);
		$this->assertEqual($ck , false);

		$pageIdArr = array(1);
		$ck = $this->Page->findAuthById($pageIdArr);
		$ans = array (
			1 =>
			array (
				'Page' =>
				array (
					'id' => '1',
					'root_id' => '0',
					'parent_id' => '0',
					'thread_num' => '0',
					'display_sequence' => '1',
					'page_name' => 'Public room',
					'permalink' => '',
					'position_flag' => '1',
					'lang' => '',
					'is_page_meta_node' => '0',
					'is_page_style_node' => '0',
					'is_page_layout_node' => '0',
					'is_page_theme_node' => '0',
					'is_page_column_node' => '0',
					'room_id' => '0',
					'space_type' => '1',
					'show_count' => '0',
					'display_flag' => '1',
					'display_from_date' => NULL,
					'display_to_date' => NULL,
					'display_apply_subpage' => '1',
					'display_reverse_permalink' => NULL,
					'is_approved' => '1',
					'lock_authority_id' => '0',
					'created' => NULL,
					'created_user_id' => '0',
					'created_user_name' => '',
					'modified' => NULL,
					'modified_user_id' => '0',
					'modified_user_name' => '',
				),
				'Community' =>
				array (
					'publication_range_flag' => NULL,
					'participate_force_all_users' => NULL,
					'participate_flag' => NULL,
					'is_upload' => NULL,
					'photo' => NULL,
				),
				'CommunityLang' =>
				array (
					'community_name' => NULL,
					'summary' => NULL,
				),
				'PageAuthority' =>
				array (
					'id' => NULL,
					'hierarchy' => NULL,
				),
			),
		);
		$this->assertEqual($ck , $ans);

		//存在しないページ
		$pageIdArr = array(99999999999);
		$ck = $this->Page->findAuthById($pageIdArr);
		$this->assertEqual($ck , false);


		//$userIdを指定し存在するページを指定した
		$pageIdArr = array(16);
		$userId = 1;
		$ck = $this->Page->findAuthById($pageIdArr , $userId);
		$ans = array (
			16 =>
			array (
				'Page' =>
				array (
					'id' => '16',
					'root_id' => '16',
					'parent_id' => '4',
					'thread_num' => '1',
					'display_sequence' => '1',
					'page_name' => 'UnitTest-A',
					'permalink' => 'community-1',
					'position_flag' => '1',
					'lang' => '',
					'is_page_meta_node' => '0',
					'is_page_style_node' => '0',
					'is_page_layout_node' => '0',
					'is_page_theme_node' => '0',
					'is_page_column_node' => '0',
					'room_id' => '16',
					'space_type' => '4',
					'show_count' => '0',
					'display_flag' => '1',
					'display_from_date' => NULL,
					'display_to_date' => NULL,
					'display_apply_subpage' => '1',
					'display_reverse_permalink' => NULL,
					'is_approved' => '1',
					'lock_authority_id' => '0',
					'created' => '2013-06-24 06:59:37',
					'created_user_id' => '1',
					'created_user_name' => 'admin',
					'modified' => '2013-06-24 06:59:37',
					'modified_user_id' => '1',
					'modified_user_name' => 'admin',
				),
				'PageUserLink' =>
				array (
					'authority_id' => '2',
				),
				'PageAuthority' =>
				array (
					'id' => '2',
					'myportal_use_flag' => '0',
					'private_use_flag' => '1',
					'hierarchy' => '350',
				),
				'Community' =>
				array (
					'publication_range_flag' => '11',
					'participate_force_all_users' => true,
					'participate_flag' => '1',
					'is_upload' => true,
					'photo' => 'study.gif',
				),
				'CommunityLang' =>
				array (
					'community_name' => 'UnitTest-A',
					'summary' => 'UnitTest-A UnitTest-A UnitTest-A',
				),
				'Authority' =>
				array (
					'id' => '1',
					'hierarchy' => '500',
					'allow_creating_community' => '4',
					'allow_new_participant' => true,
					'myportal_use_flag' => '1',
					'allow_myportal_viewing_hierarchy' => '1',
					'private_use_flag' => '1',
					'display_participants_editing' => true,
				),
			),
		);
		$this->assertEqual($ck , $ans);

		//存在しない$userIdを指定し、存在するページを指定した
		//TODO : 存在しないUser.idを指定した場合の処理が未考慮。Noticeエラーが発生してしまう。調査し考慮した実相に変更する。
		//$pageIdArr = array(16);
		//$userId = 99999999999;
		//$ck = $this->Page->findAuthById($pageIdArr , $userId);

		$pageIdArr = array(16);
		$userId = 1;
		$spaceType = NC_SPACE_TYPE_PUBLIC;
		//$spaceType = NC_SPACE_TYPE_GROUP;
		$ck = $this->Page->findAuthById($pageIdArr , $userId , $spaceType);
		$ans = array();
		$ans[16] = array (
			'Page' =>
			array (
				'id' => '16',
				'root_id' => '16',
				'parent_id' => '4',
				'thread_num' => '1',
				'display_sequence' => '1',
				'page_name' => 'Community-1',
				'permalink' => 'community-1',
				'position_flag' => '1',
				'lang' => '',
				'is_page_meta_node' => '0',
				'is_page_style_node' => '0',
				'is_page_layout_node' => '0',
				'is_page_theme_node' => '0',
				'is_page_column_node' => '0',
				'room_id' => '16',
				'space_type' => '4',
				'show_count' => '0',
				'display_flag' => '1',
				'display_from_date' => NULL,
				'display_to_date' => NULL,
				'display_apply_subpage' => '1',
				'display_reverse_permalink' => NULL,
				'is_approved' => '1',
				'lock_authority_id' => '0',
				'created' => '2013-06-24 06:59:37',
				'created_user_id' => '1',
				'created_user_name' => 'admin',
				'modified' => '2013-06-24 06:59:37',
				'modified_user_id' => '1',
				'modified_user_name' => 'admin',
			),
			'PageUserLink' =>
			array (
				'authority_id' => '2',
			),
			'PageAuthority' =>
			array (
				'id' => '2',
				'myportal_use_flag' => '0',
				'private_use_flag' => '1',
				'hierarchy' => '350',
			),
			'CommunityLang' =>
			array (
				'CommunityLang' =>
				array (
					'id' => '2',
					'room_id' => '16',
					'lang' => 'eng',
					'community_name' => 'UnitTest-A',
					'summary' => 'UnitTest-A UnitTest-A UnitTest-A',
					'revision_group_id' => '0',
					'created' => '2013-11-20 05:41:20',
					'created_user_id' => '1',
					'created_user_name' => 'admin',
					'modified' => '2013-11-20 05:41:20',
					'modified_user_id' => '1',
					'modified_user_name' => 'admin',
				),
				'Revision' =>
				array (
					'id' => NULL,
					'group_id' => NULL,
					'content' => NULL,
					'revision_name' => NULL,
					'is_approved_pointer' => NULL,
					'created' => NULL,
					'created_user_id' => NULL,
					'created_user_name' => NULL,
				),
			),
			'Authority' =>
			array (
				'id' => '1',
				'hierarchy' => '500',
				'allow_creating_community' => '4',
				'allow_new_participant' => true,
				'myportal_use_flag' => '1',
				'allow_myportal_viewing_hierarchy' => '1',
				'private_use_flag' => '1',
				'display_participants_editing' => true,
			),
		);
		$this->assertEqual($ck , $ans);

		//$pageIdArrがarrayではなかった場合 $type:NC_SPACE_TYPE_PUBLIC
		$pageIdArr = 16;
		$userId = 1;
		$spaceType = NC_SPACE_TYPE_PUBLIC;
		$ck = $this->Page->findAuthById($pageIdArr , $userId , $spaceType);
		$ans = array();
		$ans = array (
			'Page' =>
			array (
				'id' => '16',
				'root_id' => '16',
				'parent_id' => '4',
				'thread_num' => '1',
				'display_sequence' => '1',
				'page_name' => 'Community-1',
				'permalink' => 'community-1',
				'position_flag' => '1',
				'lang' => '',
				'is_page_meta_node' => '0',
				'is_page_style_node' => '0',
				'is_page_layout_node' => '0',
				'is_page_theme_node' => '0',
				'is_page_column_node' => '0',
				'room_id' => '16',
				'space_type' => '4',
				'show_count' => '0',
				'display_flag' => '1',
				'display_from_date' => NULL,
				'display_to_date' => NULL,
				'display_apply_subpage' => '1',
				'display_reverse_permalink' => NULL,
				'is_approved' => '1',
				'lock_authority_id' => '0',
				'created' => '2013-06-24 06:59:37',
				'created_user_id' => '1',
				'created_user_name' => 'admin',
				'modified' => '2013-06-24 06:59:37',
				'modified_user_id' => '1',
				'modified_user_name' => 'admin',
			),
			'PageUserLink' =>
			array (
				'authority_id' => '2',
			),
			'PageAuthority' =>
			array (
				'id' => '2',
				'myportal_use_flag' => '0',
				'private_use_flag' => '1',
				'hierarchy' => '350',
			),
			'CommunityLang' =>
			array (
				'CommunityLang' =>
				array (
					'id' => '2',
					'room_id' => '16',
					'lang' => 'eng',
					'community_name' => 'UnitTest-A',
					'summary' => 'UnitTest-A UnitTest-A UnitTest-A',
					'revision_group_id' => '0',
					'created' => '2013-11-20 05:41:20',
					'created_user_id' => '1',
					'created_user_name' => 'admin',
					'modified' => '2013-11-20 05:41:20',
					'modified_user_id' => '1',
					'modified_user_name' => 'admin',
				),
				'Revision' =>
				array (
					'id' => NULL,
					'group_id' => NULL,
					'content' => NULL,
					'revision_name' => NULL,
					'is_approved_pointer' => NULL,
					'created' => NULL,
					'created_user_id' => NULL,
					'created_user_name' => NULL,
				),
			),
			'Authority' =>
			array (
				'id' => '1',
				'hierarchy' => '500',
				'allow_creating_community' => '4',
				'allow_new_participant' => true,
				'myportal_use_flag' => '1',
				'allow_myportal_viewing_hierarchy' => '1',
				'private_use_flag' => '1',
				'display_participants_editing' => true,
			),
		);
		$this->assertEqual($ck , $ans);

		//$pageIdArrがarrayではなかった場合 $type :NC_SPACE_TYPE_MYPORTAL
		$pageIdArr = 16;
		$userId = 1;
		$spaceType = NC_SPACE_TYPE_MYPORTAL;
		$ck = $this->Page->findAuthById($pageIdArr , $userId , $spaceType);
		$ans = array();
		$ans = array (
			'Page' =>
			array (
				'id' => '16',
				'root_id' => '16',
				'parent_id' => '4',
				'thread_num' => '1',
				'display_sequence' => '1',
				'page_name' => 'Community-1',
				'permalink' => 'community-1',
				'position_flag' => '1',
				'lang' => '',
				'is_page_meta_node' => '0',
				'is_page_style_node' => '0',
				'is_page_layout_node' => '0',
				'is_page_theme_node' => '0',
				'is_page_column_node' => '0',
				'room_id' => '16',
				'space_type' => '4',
				'show_count' => '0',
				'display_flag' => '1',
				'display_from_date' => NULL,
				'display_to_date' => NULL,
				'display_apply_subpage' => '1',
				'display_reverse_permalink' => NULL,
				'is_approved' => '1',
				'lock_authority_id' => '0',
				'created' => '2013-06-24 06:59:37',
				'created_user_id' => '1',
				'created_user_name' => 'admin',
				'modified' => '2013-06-24 06:59:37',
				'modified_user_id' => '1',
				'modified_user_name' => 'admin',
			),
			'PageUserLink' =>
			array (
				'authority_id' => '2',
			),
			'PageAuthority' =>
			array (
				'id' => '2',
				'myportal_use_flag' => '0',
				'private_use_flag' => '1',
				'hierarchy' => '350',
			),
			'CommunityLang' =>
			array (
				'CommunityLang' =>
				array (
					'id' => '2',
					'room_id' => '16',
					'lang' => 'eng',
					'community_name' => 'UnitTest-A',
					'summary' => 'UnitTest-A UnitTest-A UnitTest-A',
					'revision_group_id' => '0',
					'created' => '2013-11-20 05:41:20',
					'created_user_id' => '1',
					'created_user_name' => 'admin',
					'modified' => '2013-11-20 05:41:20',
					'modified_user_id' => '1',
					'modified_user_name' => 'admin',
				),
				'Revision' =>
				array (
					'id' => NULL,
					'group_id' => NULL,
					'content' => NULL,
					'revision_name' => NULL,
					'is_approved_pointer' => NULL,
					'created' => NULL,
					'created_user_id' => NULL,
					'created_user_name' => NULL,
				),
			),
			'Authority' =>
			array (
				'id' => '1',
				'hierarchy' => '500',
				'allow_creating_community' => '4',
				'allow_new_participant' => true,
				'myportal_use_flag' => '1',
				'allow_myportal_viewing_hierarchy' => '1',
				'private_use_flag' => '1',
				'display_participants_editing' => true,
			),
		);
		$this->assertEqual($ck , $ans);

		//$pageIdArrがarrayではなかった場合 $type :NC_SPACE_TYPE_PRIVATE
		$pageIdArr = 16;
		$userId = 1;
		$spaceType = NC_SPACE_TYPE_PRIVATE;
		$ck = $this->Page->findAuthById($pageIdArr , $userId , $spaceType);
		$ans = array();
		$ans = array (
			'Page' =>
			array (
				'id' => '16',
				'root_id' => '16',
				'parent_id' => '4',
				'thread_num' => '1',
				'display_sequence' => '1',
				'page_name' => 'Community-1',
				'permalink' => 'community-1',
				'position_flag' => '1',
				'lang' => '',
				'is_page_meta_node' => '0',
				'is_page_style_node' => '0',
				'is_page_layout_node' => '0',
				'is_page_theme_node' => '0',
				'is_page_column_node' => '0',
				'room_id' => '16',
				'space_type' => '4',
				'show_count' => '0',
				'display_flag' => '1',
				'display_from_date' => NULL,
				'display_to_date' => NULL,
				'display_apply_subpage' => '1',
				'display_reverse_permalink' => NULL,
				'is_approved' => '1',
				'lock_authority_id' => '0',
				'created' => '2013-06-24 06:59:37',
				'created_user_id' => '1',
				'created_user_name' => 'admin',
				'modified' => '2013-06-24 06:59:37',
				'modified_user_id' => '1',
				'modified_user_name' => 'admin',
			),
			'PageUserLink' =>
			array (
				'authority_id' => '2',
			),
			'PageAuthority' =>
			array (
				'id' => '2',
				'myportal_use_flag' => '0',
				'private_use_flag' => '1',
				'hierarchy' => '350',
			),
			'CommunityLang' =>
			array (
				'CommunityLang' =>
				array (
					'id' => '2',
					'room_id' => '16',
					'lang' => 'eng',
					'community_name' => 'UnitTest-A',
					'summary' => 'UnitTest-A UnitTest-A UnitTest-A',
					'revision_group_id' => '0',
					'created' => '2013-11-20 05:41:20',
					'created_user_id' => '1',
					'created_user_name' => 'admin',
					'modified' => '2013-11-20 05:41:20',
					'modified_user_id' => '1',
					'modified_user_name' => 'admin',
				),
				'Revision' =>
				array (
					'id' => NULL,
					'group_id' => NULL,
					'content' => NULL,
					'revision_name' => NULL,
					'is_approved_pointer' => NULL,
					'created' => NULL,
					'created_user_id' => NULL,
					'created_user_name' => NULL,
				),
			),
			'Authority' =>
			array (
				'id' => '1',
				'hierarchy' => '500',
				'allow_creating_community' => '4',
				'allow_new_participant' => true,
				'myportal_use_flag' => '1',
				'allow_myportal_viewing_hierarchy' => '1',
				'private_use_flag' => '1',
				'display_participants_editing' => true,
			),
		);
		$this->assertEqual($ck , $ans);

		//$pageIdArrがarrayではなかった場合 $type :NC_SPACE_TYPE_GROUP コミュニティの情報が充実する。
		//$space_typeの差では、NC_SPACE_TYPE_GROUPとそれ以外でデータの内容が異なる。
		//同じPageなのに配列の構造は異なる状態になっている。
		$pageIdArr = 16;
		$userId = 1;
		$spaceType = NC_SPACE_TYPE_GROUP;
		$ck = $this->Page->findAuthById($pageIdArr , $userId , $spaceType);
		$ans = array();
		$ans = array (
			'Page' =>
			array (
				'id' => '16',
				'root_id' => '16',
				'parent_id' => '4',
				'thread_num' => '1',
				'display_sequence' => '1',
				'page_name' => 'UnitTest-A',
				'permalink' => 'community-1',
				'position_flag' => '1',
				'lang' => '',
				'is_page_meta_node' => '0',
				'is_page_style_node' => '0',
				'is_page_layout_node' => '0',
				'is_page_theme_node' => '0',
				'is_page_column_node' => '0',
				'room_id' => '16',
				'space_type' => '4',
				'show_count' => '0',
				'display_flag' => '1',
				'display_from_date' => NULL,
				'display_to_date' => NULL,
				'display_apply_subpage' => '1',
				'display_reverse_permalink' => NULL,
				'is_approved' => '1',
				'lock_authority_id' => '0',
				'created' => '2013-06-24 06:59:37',
				'created_user_id' => '1',
				'created_user_name' => 'admin',
				'modified' => '2013-06-24 06:59:37',
				'modified_user_id' => '1',
				'modified_user_name' => 'admin',
			),
			'PageUserLink' =>
			array (
				'authority_id' => '2',
			),
			'PageAuthority' =>
			array (
				'id' => '2',
				'myportal_use_flag' => '0',
				'private_use_flag' => '1',
				'hierarchy' => '350',
			),
			'Community' =>
			array (
				'publication_range_flag' => '11',
				'participate_force_all_users' => true,
				'participate_flag' => '1',
				'is_upload' => true,
				'photo' => 'study.gif',
			),
			'CommunityLang' =>
			array (
				'community_name' => 'UnitTest-A',
				'summary' => 'UnitTest-A UnitTest-A UnitTest-A',
			),
			'Authority' =>
			array (
				'id' => '1',
				'hierarchy' => '500',
				'allow_creating_community' => '4',
				'allow_new_participant' => true,
				'myportal_use_flag' => '1',
				'allow_myportal_viewing_hierarchy' => '1',
				'private_use_flag' => '1',
				'display_participants_editing' => true,
			),
		);
		$this->assertEqual($ck , $ans);
	}

/**
 * testFindBreadcrumb method
 *
 * @return void
 */
	public function testFindBreadcrumb() {

		//パンくずリスト用の配列をつくる
		$page = $this->Page->find('first' , array('conditions'=>array('Page.id'=>1)));
		$ck = $this->Page->findBreadcrumb($page);
		$ans = array (
			0 =>
			array (
				'Page' =>
				array (
					'id' => '1',
					'root_id' => '0',
					'parent_id' => '0',
					'thread_num' => '0',
					'display_sequence' => '1',
					'page_name' => 'Public room',
					'permalink' => '',
					'position_flag' => '1',
					'lang' => '',
					'is_page_meta_node' => '0',
					'is_page_style_node' => '0',
					'is_page_layout_node' => '0',
					'is_page_theme_node' => '0',
					'is_page_column_node' => '0',
					'room_id' => '0',
					'space_type' => '1',
					'show_count' => '0',
					'display_flag' => '1',
					'display_from_date' => NULL,
					'display_to_date' => NULL,
					'display_apply_subpage' => '1',
					'display_reverse_permalink' => NULL,
					'is_approved' => '1',
					'lock_authority_id' => '0',
					'created' => NULL,
					'created_user_id' => '0',
					'created_user_name' => '',
					'modified' => NULL,
					'modified_user_id' => '0',
					'modified_user_name' => '',
				),
				'PageAuthority' =>
				array (
					'hierarchy' => NULL,
				),
			),
		);
		$this->assertEqual($ck , $ans);

		//User.idはnull
		$page = $this->Page->find('first' , array('conditions'=>array('Page.id'=>1)));
		$ck = $this->Page->findBreadcrumb($page , null);
		$ans = array (
			0 =>
			array (
				'Page' =>
				array (
					'id' => '1',
					'root_id' => '0',
					'parent_id' => '0',
					'thread_num' => '0',
					'display_sequence' => '1',
					'page_name' => 'Public room',
					'permalink' => '',
					'position_flag' => '1',
					'lang' => '',
					'is_page_meta_node' => '0',
					'is_page_style_node' => '0',
					'is_page_layout_node' => '0',
					'is_page_theme_node' => '0',
					'is_page_column_node' => '0',
					'room_id' => '0',
					'space_type' => '1',
					'show_count' => '0',
					'display_flag' => '1',
					'display_from_date' => NULL,
					'display_to_date' => NULL,
					'display_apply_subpage' => '1',
					'display_reverse_permalink' => NULL,
					'is_approved' => '1',
					'lock_authority_id' => '0',
					'created' => NULL,
					'created_user_id' => '0',
					'created_user_name' => '',
					'modified' => NULL,
					'modified_user_id' => '0',
					'modified_user_name' => '',
				),
				'PageAuthority' =>
				array (
					'hierarchy' => NULL,
				),
			),
		);
		$this->assertEqual($ck , $ans);

		//コミュニティだった場合
		$page = $this->Page->find('first' , array('conditions'=>array('Page.id'=>16)));
		$ck = $this->Page->findBreadcrumb($page);
		$ans = array (
			0 =>
			array (
				'Page' =>
				array (
					'id' => '16',
					'root_id' => '16',
					'parent_id' => '4',
					'thread_num' => '1',
					'display_sequence' => '1',
					'page_name' => 'Community-1',
					'permalink' => 'community/community-1/',
					'position_flag' => '1',
					'lang' => '',
					'is_page_meta_node' => '0',
					'is_page_style_node' => '0',
					'is_page_layout_node' => '0',
					'is_page_theme_node' => '0',
					'is_page_column_node' => '0',
					'room_id' => '16',
					'space_type' => '4',
					'show_count' => '0',
					'display_flag' => '1',
					'display_from_date' => NULL,
					'display_to_date' => NULL,
					'display_apply_subpage' => '1',
					'display_reverse_permalink' => NULL,
					'is_approved' => '1',
					'lock_authority_id' => '0',
					'created' => '2013-06-24 06:59:37',
					'created_user_id' => '1',
					'created_user_name' => 'admin',
					'modified' => '2013-06-24 06:59:37',
					'modified_user_id' => '1',
					'modified_user_name' => 'admin',
				),
				'PageAuthority' =>
				array (
					'hierarchy' => NULL,
				),
				'CommunityLang' =>
				array (
					'CommunityLang' =>
					array (
						'id' => '2',
						'room_id' => '16',
						'lang' => 'eng',
						'community_name' => 'UnitTest-A',
						'summary' => 'UnitTest-A UnitTest-A UnitTest-A',
						'revision_group_id' => '0',
						'created' => '2013-11-20 05:41:20',
						'created_user_id' => '1',
						'created_user_name' => 'admin',
						'modified' => '2013-11-20 05:41:20',
						'modified_user_id' => '1',
						'modified_user_name' => 'admin',
					),
					'Revision' =>
					array (
						'id' => NULL,
						'group_id' => NULL,
						'content' => NULL,
						'revision_name' => NULL,
						'is_approved_pointer' => NULL,
						'created' => NULL,
						'created_user_id' => NULL,
						'created_user_name' => NULL,
					),
				),
			),
		);
		$this->assertEqual($ck , $ans);

		//コミュニティだった場合
		$page   = $this->Page->find('first' , array('conditions'=>array('Page.id'=>16)));
		$userId = 2;
		$ck = $this->Page->findBreadcrumb($page , $userId);
		$ans = array (
			0 =>
			array (
				'Page' =>
				array (
					'id' => '16',
					'root_id' => '16',
					'parent_id' => '4',
					'thread_num' => '1',
					'display_sequence' => '1',
					'page_name' => 'Community-1',
					'permalink' => 'community/community-1/',
					'position_flag' => '1',
					'lang' => '',
					'is_page_meta_node' => '0',
					'is_page_style_node' => '0',
					'is_page_layout_node' => '0',
					'is_page_theme_node' => '0',
					'is_page_column_node' => '0',
					'room_id' => '16',
					'space_type' => '4',
					'show_count' => '0',
					'display_flag' => '1',
					'display_from_date' => NULL,
					'display_to_date' => NULL,
					'display_apply_subpage' => '1',
					'display_reverse_permalink' => NULL,
					'is_approved' => '1',
					'lock_authority_id' => '0',
					'created' => '2013-06-24 06:59:37',
					'created_user_id' => '1',
					'created_user_name' => 'admin',
					'modified' => '2013-06-24 06:59:37',
					'modified_user_id' => '1',
					'modified_user_name' => 'admin',
				),
				'PageAuthority' =>
				array (
					'hierarchy' => NULL,
				),
				'CommunityLang' =>
				array (
					'CommunityLang' =>
					array (
						'id' => '2',
						'room_id' => '16',
						'lang' => 'eng',
						'community_name' => 'UnitTest-A',
						'summary' => 'UnitTest-A UnitTest-A UnitTest-A',
						'revision_group_id' => '0',
						'created' => '2013-11-20 05:41:20',
						'created_user_id' => '1',
						'created_user_name' => 'admin',
						'modified' => '2013-11-20 05:41:20',
						'modified_user_id' => '1',
						'modified_user_name' => 'admin',
					),
					'Revision' =>
					array (
						'id' => NULL,
						'group_id' => NULL,
						'content' => NULL,
						'revision_name' => NULL,
						'is_approved_pointer' => NULL,
						'created' => NULL,
						'created_user_id' => NULL,
						'created_user_name' => NULL,
					),
				),
			),
		);
		$this->assertEqual($ck , $ans);

		$page   = $this->Page->find('first' , array('conditions'=>array('Page.id'=>11)));
		$userId = 2;
		$ck = $this->Page->findBreadcrumb($page , $userId);
		$ans = array (
			0 =>
			array (
				'Page' =>
				array (
					'id' => '11',
					'root_id' => '11',
					'parent_id' => '3',
					'thread_num' => '1',
					'display_sequence' => '0',
					'page_name' => 'Private room of admin',
					'permalink' => 'private/Admin/',
					'position_flag' => '1',
					'lang' => '',
					'is_page_meta_node' => '0',
					'is_page_style_node' => '0',
					'is_page_layout_node' => '0',
					'is_page_theme_node' => '0',
					'is_page_column_node' => '0',
					'room_id' => '11',
					'space_type' => '3',
					'show_count' => '0',
					'display_flag' => '1',
					'display_from_date' => NULL,
					'display_to_date' => NULL,
					'display_apply_subpage' => '1',
					'display_reverse_permalink' => NULL,
					'is_approved' => '1',
					'lock_authority_id' => '0',
					'created' => NULL,
					'created_user_id' => '0',
					'created_user_name' => '',
					'modified' => NULL,
					'modified_user_id' => '1',
					'modified_user_name' => '',
				),
				'PageAuthority' =>
				array (
					'hierarchy' => NULL,
				),
			),
		);
		$this->assertEqual($ck , $ans);

		//存在しないuserId
		$page   = $this->Page->find('first' , array('conditions'=>array('Page.id'=>11)));
		$userId = 999999999999;
		$ck = $this->Page->findBreadcrumb($page , $userId);
		$ans = array (
			0 =>
			array (
				'Page' =>
				array (
					'id' => '11',
					'root_id' => '11',
					'parent_id' => '3',
					'thread_num' => '1',
					'display_sequence' => '0',
					'page_name' => 'Private room of admin',
					'permalink' => 'private/Admin/',
					'position_flag' => '1',
					'lang' => '',
					'is_page_meta_node' => '0',
					'is_page_style_node' => '0',
					'is_page_layout_node' => '0',
					'is_page_theme_node' => '0',
					'is_page_column_node' => '0',
					'room_id' => '11',
					'space_type' => '3',
					'show_count' => '0',
					'display_flag' => '1',
					'display_from_date' => NULL,
					'display_to_date' => NULL,
					'display_apply_subpage' => '1',
					'display_reverse_permalink' => NULL,
					'is_approved' => '1',
					'lock_authority_id' => '0',
					'created' => NULL,
					'created_user_id' => '0',
					'created_user_name' => '',
					'modified' => NULL,
					'modified_user_id' => '1',
					'modified_user_name' => '',
				),
				'PageAuthority' =>
				array (
					'hierarchy' => NULL,
				),
			),
		);
		$this->assertEqual($ck , $ans);

		//$userIdが文字列（パラメータ異常系）省略した場合と同じ値が戻る
		$page   = $this->Page->find('first' , array('conditions'=>array('Page.id'=>11)));
		$userId = 'AAAAAAA';
		$ck = $this->Page->findBreadcrumb($page , $userId);
		$ans = array (
			0 =>
			array (
				'Page' =>
				array (
					'id' => '11',
					'root_id' => '11',
					'parent_id' => '3',
					'thread_num' => '1',
					'display_sequence' => '0',
					'page_name' => 'Private room of admin',
					'permalink' => 'private/Admin/',
					'position_flag' => '1',
					'lang' => '',
					'is_page_meta_node' => '0',
					'is_page_style_node' => '0',
					'is_page_layout_node' => '0',
					'is_page_theme_node' => '0',
					'is_page_column_node' => '0',
					'room_id' => '11',
					'space_type' => '3',
					'show_count' => '0',
					'display_flag' => '1',
					'display_from_date' => NULL,
					'display_to_date' => NULL,
					'display_apply_subpage' => '1',
					'display_reverse_permalink' => NULL,
					'is_approved' => '1',
					'lock_authority_id' => '0',
					'created' => NULL,
					'created_user_id' => '0',
					'created_user_name' => '',
					'modified' => NULL,
					'modified_user_id' => '1',
					'modified_user_name' => '',
				),
				'PageAuthority' =>
				array (
					'hierarchy' => NULL,
				),
			),
		);
		$this->assertEqual($ck , $ans);

		//Pageの1レコード分の配列が格納されていなかった場合が未考慮 Notice Error
		//TODO : パラメータ異常系の考慮
		//$ck = $this->Page->findBreadcrumb(array('Page'=>array()));
		//var_export($ck);
	}

/**
 * testFindRoomList method
 *
 * @return void
 */

	public function testFindRoomList() {
		$result = array (
			9 =>
			array (
				'Page' =>
				array (
					'id' => '9',
					'root_id' => '9',
					'thread_num' => '1',
					'display_sequence' => '0',
					'page_name' => 'Public room',
					'permalink' => '',
					'position_flag' => '1',
					'lang' => '',
					'is_page_meta_node' => '0',
					'is_page_style_node' => '0',
					'is_page_layout_node' => '0',
					'is_page_theme_node' => '0',
					'is_page_column_node' => '0',
					'room_id' => '9',
					'space_type' => '1',
					'show_count' => '0',
					'display_flag' => '1',
					'display_from_date' => NULL,
					'display_to_date' => NULL,
					'display_apply_subpage' => '1',
					'display_reverse_permalink' => NULL,
					'is_approved' => '1',
					'lock_authority_id' => '0',
					'created' => NULL,
					'created_user_id' => '0',
					'created_user_name' => '',
					'modified' => NULL,
					'modified_user_id' => '1',
					'modified_user_name' => '',
				),
				'PageUserLink' =>
				array (
					'authority_id' => NULL,
				),
				'PageAuthority' =>
				array (
					'id' => NULL,
					'myportal_use_flag' => NULL,
					'private_use_flag' => NULL,
					'hierarchy' => NULL,
				),
				'Community' =>
				array (
					'publication_range_flag' => NULL,
					'participate_force_all_users' => NULL,
					'participate_flag' => NULL,
					'is_upload' => NULL,
					'photo' => NULL,
				),
				'CommunityLang' =>
				array (
					'community_name' => NULL,
					'summary' => NULL,
				),
			),
			11 =>
			array (
				'Page' =>
				array (
					'id' => '11',
					'root_id' => '11',
					'thread_num' => '1',
					'display_sequence' => '0',
					'page_name' => 'Private room of admin',
					'permalink' => 'Admin',
					'position_flag' => '1',
					'lang' => '',
					'is_page_meta_node' => '0',
					'is_page_style_node' => '0',
					'is_page_layout_node' => '0',
					'is_page_theme_node' => '0',
					'is_page_column_node' => '0',
					'room_id' => '11',
					'space_type' => '3',
					'show_count' => '0',
					'display_flag' => '1',
					'display_from_date' => NULL,
					'display_to_date' => NULL,
					'display_apply_subpage' => '1',
					'display_reverse_permalink' => NULL,
					'is_approved' => '1',
					'lock_authority_id' => '0',
					'created' => NULL,
					'created_user_id' => '0',
					'created_user_name' => '',
					'modified' => NULL,
					'modified_user_id' => '1',
					'modified_user_name' => '',
				),
				'PageUserLink' =>
				array (
					'authority_id' => NULL,
				),
				'PageAuthority' =>
				array (
					'id' => NULL,
					'myportal_use_flag' => NULL,
					'private_use_flag' => NULL,
					'hierarchy' => NULL,
				),
				'Community' =>
				array (
					'publication_range_flag' => NULL,
					'participate_force_all_users' => NULL,
					'participate_flag' => NULL,
					'is_upload' => NULL,
					'photo' => NULL,
				),
				'CommunityLang' =>
				array (
					'community_name' => NULL,
					'summary' => NULL,
				),
			),
		);
		//TODO 先に testFindViewableのテストをしてから書く。

	}

/**
 * testFindViewable method
 *
 * @return void
 */
	public function testFindViewable() {


		$result = array (
			9 =>
			array (
				'Page' =>
				array (
					'id' => '9',
					'root_id' => '9',
					'parent_id' => '1',
					'thread_num' => '1',
					'display_sequence' => '0',
					'page_name' => 'Public room',
					'permalink' => '',
					'position_flag' => '1',
					'lang' => '',
					'is_page_meta_node' => '0',
					'is_page_style_node' => '0',
					'is_page_layout_node' => '0',
					'is_page_theme_node' => '0',
					'is_page_column_node' => '0',
					'room_id' => '9',
					'space_type' => '1',
					'show_count' => '0',
					'display_flag' => '1',
					'display_from_date' => NULL,
					'display_to_date' => NULL,
					'display_apply_subpage' => '1',
					'display_reverse_permalink' => NULL,
					'is_approved' => '1',
					'lock_authority_id' => '0',
					'created' => NULL,
					'created_user_id' => '0',
					'created_user_name' => '',
					'modified' => NULL,
					'modified_user_id' => '1',
					'modified_user_name' => '',
				),
				'PageUserLink' =>
				array (
					'authority_id' => NULL,
				),
				'PageAuthority' =>
				array (
					'id' => NULL,
					'myportal_use_flag' => NULL,
					'private_use_flag' => NULL,
					'hierarchy' => NULL,
				),
				'Community' =>
				array (
					'publication_range_flag' => NULL,
					'participate_force_all_users' => NULL,
					'participate_flag' => NULL,
					'is_upload' => NULL,
					'photo' => NULL,
				),
				'CommunityLang' =>
				array (
					'community_name' => NULL,
					'summary' => NULL,
				),
			),
			11 =>
			array (
				'Page' =>
				array (
					'id' => '11',
					'root_id' => '11',
					'parent_id' => '3',
					'thread_num' => '1',
					'display_sequence' => '0',
					'page_name' => 'Private room of admin',
					'permalink' => 'Admin',
					'position_flag' => '1',
					'lang' => '',
					'is_page_meta_node' => '0',
					'is_page_style_node' => '0',
					'is_page_layout_node' => '0',
					'is_page_theme_node' => '0',
					'is_page_column_node' => '0',
					'room_id' => '11',
					'space_type' => '3',
					'show_count' => '0',
					'display_flag' => '1',
					'display_from_date' => NULL,
					'display_to_date' => NULL,
					'display_apply_subpage' => '1',
					'display_reverse_permalink' => NULL,
					'is_approved' => '1',
					'lock_authority_id' => '0',
					'created' => NULL,
					'created_user_id' => '0',
					'created_user_name' => '',
					'modified' => NULL,
					'modified_user_id' => '1',
					'modified_user_name' => '',
				),
				'PageUserLink' =>
				array (
					'authority_id' => NULL,
				),
				'PageAuthority' =>
				array (
					'id' => NULL,
					'myportal_use_flag' => NULL,
					'private_use_flag' => NULL,
					'hierarchy' => NULL,
				),
				'Community' =>
				array (
					'publication_range_flag' => NULL,
					'participate_force_all_users' => NULL,
					'participate_flag' => NULL,
					'is_upload' => NULL,
					'photo' => NULL,
				),
				'CommunityLang' =>
				array (
					'community_name' => NULL,
					'summary' => NULL,
				),
			),
			16 =>
			array (
				'Page' =>
				array (
					'id' => '16',
					'root_id' => '16',
					'parent_id' => '4',
					'thread_num' => '1',
					'display_sequence' => '1',
					'page_name' => 'UnitTest-A',
					'permalink' => 'community-1',
					'position_flag' => '1',
					'lang' => '',
					'is_page_meta_node' => '0',
					'is_page_style_node' => '0',
					'is_page_layout_node' => '0',
					'is_page_theme_node' => '0',
					'is_page_column_node' => '0',
					'room_id' => '16',
					'space_type' => '4',
					'show_count' => '0',
					'display_flag' => '1',
					'display_from_date' => NULL,
					'display_to_date' => NULL,
					'display_apply_subpage' => '1',
					'display_reverse_permalink' => NULL,
					'is_approved' => '1',
					'lock_authority_id' => '0',
					'created' => '2013-06-24 06:59:37',
					'created_user_id' => '1',
					'created_user_name' => 'admin',
					'modified' => '2013-06-24 06:59:37',
					'modified_user_id' => '1',
					'modified_user_name' => 'admin',
				),
				'PageUserLink' =>
				array (
					'authority_id' => NULL,
				),
				'PageAuthority' =>
				array (
					'id' => NULL,
					'myportal_use_flag' => NULL,
					'private_use_flag' => NULL,
					'hierarchy' => NULL,
				),
				'Community' =>
				array (
					'publication_range_flag' => '11',
					'participate_force_all_users' => true,
					'participate_flag' => '1',
					'is_upload' => true,
					'photo' => 'study.gif',
				),
				'CommunityLang' =>
				array (
					'community_name' => 'UnitTest-A',
					'summary' => 'UnitTest-A UnitTest-A UnitTest-A',
				),
			),
		);
		$ck = $this->Page->findViewable('all' , 'all');
		//var_export($ck);
		$this->assertEqual($result , $ck);

		$ck = $this->Page->findViewable('all' , 1);
		$result = array (
			9 =>
			array (
				'Page' =>
				array (
					'id' => '9',
					'root_id' => '9',
					'parent_id' => '1',
					'thread_num' => '1',
					'display_sequence' => '0',
					'page_name' => 'Public room',
					'permalink' => '',
					'position_flag' => '1',
					'lang' => '',
					'is_page_meta_node' => '0',
					'is_page_style_node' => '0',
					'is_page_layout_node' => '0',
					'is_page_theme_node' => '0',
					'is_page_column_node' => '0',
					'room_id' => '9',
					'space_type' => '1',
					'show_count' => '0',
					'display_flag' => '1',
					'display_from_date' => NULL,
					'display_to_date' => NULL,
					'display_apply_subpage' => '1',
					'display_reverse_permalink' => NULL,
					'is_approved' => '1',
					'lock_authority_id' => '0',
					'created' => NULL,
					'created_user_id' => '0',
					'created_user_name' => '',
					'modified' => NULL,
					'modified_user_id' => '1',
					'modified_user_name' => '',
				),
				'PageUserLink' =>
				array (
					'authority_id' => '2',
				),
				'PageAuthority' =>
				array (
					'id' => '2',
					'myportal_use_flag' => '0',
					'private_use_flag' => '1',
					'hierarchy' => '350',
				),
				'Community' =>
				array (
					'publication_range_flag' => NULL,
					'participate_force_all_users' => NULL,
					'participate_flag' => NULL,
					'is_upload' => NULL,
					'photo' => NULL,
				),
				'CommunityLang' =>
				array (
					'community_name' => NULL,
					'summary' => NULL,
				),
				'Authority' =>
				array (
					'id' => '1',
					'hierarchy' => '500',
					'allow_creating_community' => '4',
					'allow_new_participant' => true,
					'myportal_use_flag' => '1',
					'allow_myportal_viewing_hierarchy' => '1',
					'private_use_flag' => '1',
					'display_participants_editing' => true,
				),
			),
			11 =>
			array (
				'Page' =>
				array (
					'id' => '11',
					'root_id' => '11',
					'parent_id' => '3',
					'thread_num' => '1',
					'display_sequence' => '0',
					'page_name' => 'Private room of admin',
					'permalink' => 'Admin',
					'position_flag' => '1',
					'lang' => '',
					'is_page_meta_node' => '0',
					'is_page_style_node' => '0',
					'is_page_layout_node' => '0',
					'is_page_theme_node' => '0',
					'is_page_column_node' => '0',
					'room_id' => '11',
					'space_type' => '3',
					'show_count' => '0',
					'display_flag' => '1',
					'display_from_date' => NULL,
					'display_to_date' => NULL,
					'display_apply_subpage' => '1',
					'display_reverse_permalink' => NULL,
					'is_approved' => '1',
					'lock_authority_id' => '0',
					'created' => NULL,
					'created_user_id' => '0',
					'created_user_name' => '',
					'modified' => NULL,
					'modified_user_id' => '1',
					'modified_user_name' => '',
				),
				'PageUserLink' =>
				array (
					'authority_id' => '2',
				),
				'PageAuthority' =>
				array (
					'id' => '2',
					'myportal_use_flag' => '0',
					'private_use_flag' => '1',
					'hierarchy' => '350',
				),
				'Community' =>
				array (
					'publication_range_flag' => NULL,
					'participate_force_all_users' => NULL,
					'participate_flag' => NULL,
					'is_upload' => NULL,
					'photo' => NULL,
				),
				'CommunityLang' =>
				array (
					'community_name' => NULL,
					'summary' => NULL,
				),
				'Authority' =>
				array (
					'id' => '1',
					'hierarchy' => '500',
					'allow_creating_community' => '4',
					'allow_new_participant' => true,
					'myportal_use_flag' => '1',
					'allow_myportal_viewing_hierarchy' => '1',
					'private_use_flag' => '1',
					'display_participants_editing' => true,
				),
			),
			16 =>
			array (
				'Page' =>
				array (
					'id' => '16',
					'root_id' => '16',
					'parent_id' => '4',
					'thread_num' => '1',
					'display_sequence' => '1',
					'page_name' => 'UnitTest-A',
					'permalink' => 'community-1',
					'position_flag' => '1',
					'lang' => '',
					'is_page_meta_node' => '0',
					'is_page_style_node' => '0',
					'is_page_layout_node' => '0',
					'is_page_theme_node' => '0',
					'is_page_column_node' => '0',
					'room_id' => '16',
					'space_type' => '4',
					'show_count' => '0',
					'display_flag' => '1',
					'display_from_date' => NULL,
					'display_to_date' => NULL,
					'display_apply_subpage' => '1',
					'display_reverse_permalink' => NULL,
					'is_approved' => '1',
					'lock_authority_id' => '0',
					'created' => '2013-06-24 06:59:37',
					'created_user_id' => '1',
					'created_user_name' => 'admin',
					'modified' => '2013-06-24 06:59:37',
					'modified_user_id' => '1',
					'modified_user_name' => 'admin',
				),
				'PageUserLink' =>
				array (
					'authority_id' => '2',
				),
				'PageAuthority' =>
				array (
					'id' => '2',
					'myportal_use_flag' => '0',
					'private_use_flag' => '1',
					'hierarchy' => '350',
				),
				'Community' =>
				array (
					'publication_range_flag' => '11',
					'participate_force_all_users' => true,
					'participate_flag' => '1',
					'is_upload' => true,
					'photo' => 'study.gif',
				),
				'CommunityLang' =>
				array (
					'community_name' => 'UnitTest-A',
					'summary' => 'UnitTest-A UnitTest-A UnitTest-A',
				),
				'Authority' =>
				array (
					'id' => '1',
					'hierarchy' => '500',
					'allow_creating_community' => '4',
					'allow_new_participant' => true,
					'myportal_use_flag' => '1',
					'allow_myportal_viewing_hierarchy' => '1',
					'private_use_flag' => '1',
					'display_participants_editing' => true,
				),
			),
		);
		//var_export($ck);
		$this->assertEqual($result , $ck);

		//存在しないUser.idに対しての挙動が未考慮
		//TODO:仕様検討
		/*
		$ck = $this->Page->findViewable('all' , 200);
		$result = array (
			9 =>
			array (
				'Page' =>
				array (
					'id' => '9',
					'root_id' => '9',
					'parent_id' => '1',
					'thread_num' => '1',
					'display_sequence' => '0',
					'page_name' => 'Public room',
					'permalink' => '',
					'position_flag' => '1',
					'lang' => '',
					'is_page_meta_node' => '0',
					'is_page_style_node' => '0',
					'is_page_layout_node' => '0',
					'is_page_theme_node' => '0',
					'is_page_column_node' => '0',
					'room_id' => '9',
					'space_type' => '1',
					'show_count' => '0',
					'display_flag' => '1',
					'display_from_date' => NULL,
					'display_to_date' => NULL,
					'display_apply_subpage' => '1',
					'display_reverse_permalink' => NULL,
					'is_approved' => '1',
					'lock_authority_id' => '0',
					'created' => NULL,
					'created_user_id' => '0',
					'created_user_name' => '',
					'modified' => NULL,
					'modified_user_id' => '1',
					'modified_user_name' => '',
				),
				'PageUserLink' =>
				array (
					'authority_id' => NULL,
				),
				'PageAuthority' =>
				array (
					'id' => NULL,
					'myportal_use_flag' => NULL,
					'private_use_flag' => NULL,
					'hierarchy' => NULL,
				),
				'Community' =>
				array (
					'publication_range_flag' => NULL,
					'participate_force_all_users' => NULL,
					'participate_flag' => NULL,
					'is_upload' => NULL,
					'photo' => NULL,
				),
				'CommunityLang' =>
				array (
					'community_name' => NULL,
					'summary' => NULL,
				),
				'Authority' =>
				array (
					'id' => '1',
					'hierarchy' => '500',
					'allow_creating_community' => '4',
					'allow_new_participant' => true,
					'myportal_use_flag' => '1',
					'allow_myportal_viewing_hierarchy' => '1',
					'private_use_flag' => '1',
					'display_participants_editing' => true,
				),
			),
			11 =>
			array (
				'Page' =>
				array (
					'id' => '11',
					'root_id' => '11',
					'parent_id' => '3',
					'thread_num' => '1',
					'display_sequence' => '0',
					'page_name' => 'Private room of admin',
					'permalink' => 'Admin',
					'position_flag' => '1',
					'lang' => '',
					'is_page_meta_node' => '0',
					'is_page_style_node' => '0',
					'is_page_layout_node' => '0',
					'is_page_theme_node' => '0',
					'is_page_column_node' => '0',
					'room_id' => '11',
					'space_type' => '3',
					'show_count' => '0',
					'display_flag' => '1',
					'display_from_date' => NULL,
					'display_to_date' => NULL,
					'display_apply_subpage' => '1',
					'display_reverse_permalink' => NULL,
					'is_approved' => '1',
					'lock_authority_id' => '0',
					'created' => NULL,
					'created_user_id' => '0',
					'created_user_name' => '',
					'modified' => NULL,
					'modified_user_id' => '1',
					'modified_user_name' => '',
				),
				'PageUserLink' =>
				array (
					'authority_id' => NULL,
				),
				'PageAuthority' =>
				array (
					'id' => NULL,
					'myportal_use_flag' => NULL,
					'private_use_flag' => NULL,
					'hierarchy' => NULL,
				),
				'Community' =>
				array (
					'publication_range_flag' => NULL,
					'participate_force_all_users' => NULL,
					'participate_flag' => NULL,
					'is_upload' => NULL,
					'photo' => NULL,
				),
				'CommunityLang' =>
				array (
					'community_name' => NULL,
					'summary' => NULL,
				),
				'Authority' =>
				array (
					'id' => '1',
					'hierarchy' => '500',
					'allow_creating_community' => '4',
					'allow_new_participant' => true,
					'myportal_use_flag' => '1',
					'allow_myportal_viewing_hierarchy' => '1',
					'private_use_flag' => '1',
					'display_participants_editing' => true,
				),
			),
		);
		$this->assertEqual($result , $ck);
        */

		$ck = $this->Page->findViewable(
			'all' ,
			2 ,
			array(
				'conditions'=>array('Page.id'=>9)
			) ,
			array()
		);
		$result = array (
			9 =>
			array (
				'Page' =>
				array (
					'id' => '9',
					'root_id' => '9',
					'parent_id' => '1',
					'thread_num' => '1',
					'display_sequence' => '0',
					'page_name' => 'Public room',
					'permalink' => '',
					'position_flag' => '1',
					'lang' => '',
					'is_page_meta_node' => '0',
					'is_page_style_node' => '0',
					'is_page_layout_node' => '0',
					'is_page_theme_node' => '0',
					'is_page_column_node' => '0',
					'room_id' => '9',
					'space_type' => '1',
					'show_count' => '0',
					'display_flag' => '1',
					'display_from_date' => NULL,
					'display_to_date' => NULL,
					'display_apply_subpage' => '1',
					'display_reverse_permalink' => NULL,
					'is_approved' => '1',
					'lock_authority_id' => '0',
					'created' => NULL,
					'created_user_id' => '0',
					'created_user_name' => '',
					'modified' => NULL,
					'modified_user_id' => '1',
					'modified_user_name' => '',
				),
				'PageUserLink' =>
				array (
					'authority_id' => NULL,
				),
				'PageAuthority' =>
				array (
					'id' => NULL,
					'myportal_use_flag' => NULL,
					'private_use_flag' => NULL,
					'hierarchy' => NULL,
				),
				'Community' =>
				array (
					'publication_range_flag' => NULL,
					'participate_force_all_users' => NULL,
					'participate_flag' => NULL,
					'is_upload' => NULL,
					'photo' => NULL,
				),
				'CommunityLang' =>
				array (
					'community_name' => NULL,
					'summary' => NULL,
				),
				'Authority' =>
				array (
					'id' => '1',
					'hierarchy' => '500',
					'allow_creating_community' => '4',
					'allow_new_participant' => true,
					'myportal_use_flag' => '1',
					'allow_myportal_viewing_hierarchy' => '1',
					'private_use_flag' => '1',
					'display_participants_editing' => true,
				),
			),
		);
		$this->assertEqual($result , $ck);

		//第1引数がallの場合は、第3引数の'fields'で指定した条件は無視される。
		//'conditions'はそのまま有効
		$ck = $this->Page->findViewable(
			'all' ,
			2 ,
			array(
				'conditions'=>array('Page.id'=>9),
				'fields' => array('Page.id' , 'Page.thread_num')
			) ,
			array()
		);
		$result = array (
			9 =>
			array (
				'Page' =>
				array (
					'id' => '9',
					'root_id' => '9',
					'parent_id' => '1',
					'thread_num' => '1',
					'display_sequence' => '0',
					'page_name' => 'Public room',
					'permalink' => '',
					'position_flag' => '1',
					'lang' => '',
					'is_page_meta_node' => '0',
					'is_page_style_node' => '0',
					'is_page_layout_node' => '0',
					'is_page_theme_node' => '0',
					'is_page_column_node' => '0',
					'room_id' => '9',
					'space_type' => '1',
					'show_count' => '0',
					'display_flag' => '1',
					'display_from_date' => NULL,
					'display_to_date' => NULL,
					'display_apply_subpage' => '1',
					'display_reverse_permalink' => NULL,
					'is_approved' => '1',
					'lock_authority_id' => '0',
					'created' => NULL,
					'created_user_id' => '0',
					'created_user_name' => '',
					'modified' => NULL,
					'modified_user_id' => '1',
					'modified_user_name' => '',
				),
				'PageUserLink' =>
				array (
					'authority_id' => NULL,
				),
				'PageAuthority' =>
				array (
					'id' => NULL,
					'myportal_use_flag' => NULL,
					'private_use_flag' => NULL,
					'hierarchy' => NULL,
				),
				'Community' =>
				array (
					'publication_range_flag' => NULL,
					'participate_force_all_users' => NULL,
					'participate_flag' => NULL,
					'is_upload' => NULL,
					'photo' => NULL,
				),
				'CommunityLang' =>
				array (
					'community_name' => NULL,
					'summary' => NULL,
				),
				'Authority' =>
				array (
					'id' => '1',
					'hierarchy' => '500',
					'allow_creating_community' => '4',
					'allow_new_participant' => true,
					'myportal_use_flag' => '1',
					'allow_myportal_viewing_hierarchy' => '1',
					'private_use_flag' => '1',
					'display_participants_editing' => true,
				),
			),
		);
		$this->assertEqual($result , $ck);

		$ck = $this->Page->findViewable(
			'all' ,
			2 ,
			array() ,
			array(
				'isShowAllCommunity'=>true
			)  //公開コミュニティーを含む閲覧可能なすべてのコミュニティー
		);
		$result = array (
			9 =>
			array (
				'Page' =>
				array (
					'id' => '9',
					'root_id' => '9',
					'parent_id' => '1',
					'thread_num' => '1',
					'display_sequence' => '0',
					'page_name' => 'Public room',
					'permalink' => '',
					'position_flag' => '1',
					'lang' => '',
					'is_page_meta_node' => '0',
					'is_page_style_node' => '0',
					'is_page_layout_node' => '0',
					'is_page_theme_node' => '0',
					'is_page_column_node' => '0',
					'room_id' => '9',
					'space_type' => '1',
					'show_count' => '0',
					'display_flag' => '1',
					'display_from_date' => NULL,
					'display_to_date' => NULL,
					'display_apply_subpage' => '1',
					'display_reverse_permalink' => NULL,
					'is_approved' => '1',
					'lock_authority_id' => '0',
					'created' => NULL,
					'created_user_id' => '0',
					'created_user_name' => '',
					'modified' => NULL,
					'modified_user_id' => '1',
					'modified_user_name' => '',
				),
				'PageUserLink' =>
				array (
					'authority_id' => NULL,
				),
				'PageAuthority' =>
				array (
					'id' => NULL,
					'myportal_use_flag' => NULL,
					'private_use_flag' => NULL,
					'hierarchy' => NULL,
				),
				'Community' =>
				array (
					'publication_range_flag' => NULL,
					'participate_force_all_users' => NULL,
					'participate_flag' => NULL,
					'is_upload' => NULL,
					'photo' => NULL,
				),
				'CommunityLang' =>
				array (
					'community_name' => NULL,
					'summary' => NULL,
				),
				'Authority' =>
				array (
					'id' => '1',
					'hierarchy' => '500',
					'allow_creating_community' => '4',
					'allow_new_participant' => true,
					'myportal_use_flag' => '1',
					'allow_myportal_viewing_hierarchy' => '1',
					'private_use_flag' => '1',
					'display_participants_editing' => true,
				),
			),
			11 =>
			array (
				'Page' =>
				array (
					'id' => '11',
					'root_id' => '11',
					'parent_id' => '3',
					'thread_num' => '1',
					'display_sequence' => '0',
					'page_name' => 'Private room of admin',
					'permalink' => 'Admin',
					'position_flag' => '1',
					'lang' => '',
					'is_page_meta_node' => '0',
					'is_page_style_node' => '0',
					'is_page_layout_node' => '0',
					'is_page_theme_node' => '0',
					'is_page_column_node' => '0',
					'room_id' => '11',
					'space_type' => '3',
					'show_count' => '0',
					'display_flag' => '1',
					'display_from_date' => NULL,
					'display_to_date' => NULL,
					'display_apply_subpage' => '1',
					'display_reverse_permalink' => NULL,
					'is_approved' => '1',
					'lock_authority_id' => '0',
					'created' => NULL,
					'created_user_id' => '0',
					'created_user_name' => '',
					'modified' => NULL,
					'modified_user_id' => '1',
					'modified_user_name' => '',
				),
				'PageUserLink' =>
				array (
					'authority_id' => NULL,
				),
				'PageAuthority' =>
				array (
					'id' => NULL,
					'myportal_use_flag' => NULL,
					'private_use_flag' => NULL,
					'hierarchy' => NULL,
				),
				'Community' =>
				array (
					'publication_range_flag' => NULL,
					'participate_force_all_users' => NULL,
					'participate_flag' => NULL,
					'is_upload' => NULL,
					'photo' => NULL,
				),
				'CommunityLang' =>
				array (
					'community_name' => NULL,
					'summary' => NULL,
				),
				'Authority' =>
				array (
					'id' => '1',
					'hierarchy' => '500',
					'allow_creating_community' => '4',
					'allow_new_participant' => true,
					'myportal_use_flag' => '1',
					'allow_myportal_viewing_hierarchy' => '1',
					'private_use_flag' => '1',
					'display_participants_editing' => true,
				),
			),
		);
		$this->assertEqual($result , $ck);

		$ck = $this->Page->findViewable(
			'all' ,
			2 ,
			array() ,
			array(
				'isShowAllCommunity'=>false  //参加コミュニティーのみ
			)
		);
		//var_export($ck);
		$result = array (
			9 =>
			array (
				'Page' =>
				array (
					'id' => '9',
					'root_id' => '9',
					'parent_id' => '1',
					'thread_num' => '1',
					'display_sequence' => '0',
					'page_name' => 'Public room',
					'permalink' => '',
					'position_flag' => '1',
					'lang' => '',
					'is_page_meta_node' => '0',
					'is_page_style_node' => '0',
					'is_page_layout_node' => '0',
					'is_page_theme_node' => '0',
					'is_page_column_node' => '0',
					'room_id' => '9',
					'space_type' => '1',
					'show_count' => '0',
					'display_flag' => '1',
					'display_from_date' => NULL,
					'display_to_date' => NULL,
					'display_apply_subpage' => '1',
					'display_reverse_permalink' => NULL,
					'is_approved' => '1',
					'lock_authority_id' => '0',
					'created' => NULL,
					'created_user_id' => '0',
					'created_user_name' => '',
					'modified' => NULL,
					'modified_user_id' => '1',
					'modified_user_name' => '',
				),
				'PageUserLink' =>
				array (
					'authority_id' => NULL,
				),
				'PageAuthority' =>
				array (
					'id' => NULL,
					'myportal_use_flag' => NULL,
					'private_use_flag' => NULL,
					'hierarchy' => NULL,
				),
				'Community' =>
				array (
					'publication_range_flag' => NULL,
					'participate_force_all_users' => NULL,
					'participate_flag' => NULL,
					'is_upload' => NULL,
					'photo' => NULL,
				),
				'CommunityLang' =>
				array (
					'community_name' => NULL,
					'summary' => NULL,
				),
				'Authority' =>
				array (
					'id' => '1',
					'hierarchy' => '500',
					'allow_creating_community' => '4',
					'allow_new_participant' => true,
					'myportal_use_flag' => '1',
					'allow_myportal_viewing_hierarchy' => '1',
					'private_use_flag' => '1',
					'display_participants_editing' => true,
				),
			),
			11 =>
			array (
				'Page' =>
				array (
					'id' => '11',
					'root_id' => '11',
					'parent_id' => '3',
					'thread_num' => '1',
					'display_sequence' => '0',
					'page_name' => 'Private room of admin',
					'permalink' => 'Admin',
					'position_flag' => '1',
					'lang' => '',
					'is_page_meta_node' => '0',
					'is_page_style_node' => '0',
					'is_page_layout_node' => '0',
					'is_page_theme_node' => '0',
					'is_page_column_node' => '0',
					'room_id' => '11',
					'space_type' => '3',
					'show_count' => '0',
					'display_flag' => '1',
					'display_from_date' => NULL,
					'display_to_date' => NULL,
					'display_apply_subpage' => '1',
					'display_reverse_permalink' => NULL,
					'is_approved' => '1',
					'lock_authority_id' => '0',
					'created' => NULL,
					'created_user_id' => '0',
					'created_user_name' => '',
					'modified' => NULL,
					'modified_user_id' => '1',
					'modified_user_name' => '',
				),
				'PageUserLink' =>
				array (
					'authority_id' => NULL,
				),
				'PageAuthority' =>
				array (
					'id' => NULL,
					'myportal_use_flag' => NULL,
					'private_use_flag' => NULL,
					'hierarchy' => NULL,
				),
				'Community' =>
				array (
					'publication_range_flag' => NULL,
					'participate_force_all_users' => NULL,
					'participate_flag' => NULL,
					'is_upload' => NULL,
					'photo' => NULL,
				),
				'CommunityLang' =>
				array (
					'community_name' => NULL,
					'summary' => NULL,
				),
				'Authority' =>
				array (
					'id' => '1',
					'hierarchy' => '500',
					'allow_creating_community' => '4',
					'allow_new_participant' => true,
					'myportal_use_flag' => '1',
					'allow_myportal_viewing_hierarchy' => '1',
					'private_use_flag' => '1',
					'display_participants_editing' => true,
				),
			),
		);
		$this->assertEqual($result , $ck);

		//isRoom
		$ck = $this->Page->findViewable(
			'all' ,
			2 ,
			array() ,
			array(
				'isRoom'=>true  //ルームのみ取得するかどうか。default false
			)
		);
		$result = array (
			9 =>
			array (
				'Page' =>
				array (
					'id' => '9',
					'root_id' => '9',
					'parent_id' => '1',
					'thread_num' => '1',
					'display_sequence' => '0',
					'page_name' => 'Public room',
					'permalink' => '',
					'position_flag' => '1',
					'lang' => '',
					'is_page_meta_node' => '0',
					'is_page_style_node' => '0',
					'is_page_layout_node' => '0',
					'is_page_theme_node' => '0',
					'is_page_column_node' => '0',
					'room_id' => '9',
					'space_type' => '1',
					'show_count' => '0',
					'display_flag' => '1',
					'display_from_date' => NULL,
					'display_to_date' => NULL,
					'display_apply_subpage' => '1',
					'display_reverse_permalink' => NULL,
					'is_approved' => '1',
					'lock_authority_id' => '0',
					'created' => NULL,
					'created_user_id' => '0',
					'created_user_name' => '',
					'modified' => NULL,
					'modified_user_id' => '1',
					'modified_user_name' => '',
				),
				'PageUserLink' =>
				array (
					'authority_id' => NULL,
				),
				'PageAuthority' =>
				array (
					'id' => NULL,
					'myportal_use_flag' => NULL,
					'private_use_flag' => NULL,
					'hierarchy' => NULL,
				),
				'Community' =>
				array (
					'publication_range_flag' => NULL,
					'participate_force_all_users' => NULL,
					'participate_flag' => NULL,
					'is_upload' => NULL,
					'photo' => NULL,
				),
				'CommunityLang' =>
				array (
					'community_name' => NULL,
					'summary' => NULL,
				),
				'Authority' =>
				array (
					'id' => '1',
					'hierarchy' => '500',
					'allow_creating_community' => '4',
					'allow_new_participant' => true,
					'myportal_use_flag' => '1',
					'allow_myportal_viewing_hierarchy' => '1',
					'private_use_flag' => '1',
					'display_participants_editing' => true,
				),
			),
			11 =>
			array (
				'Page' =>
				array (
					'id' => '11',
					'root_id' => '11',
					'parent_id' => '3',
					'thread_num' => '1',
					'display_sequence' => '0',
					'page_name' => 'Private room of admin',
					'permalink' => 'Admin',
					'position_flag' => '1',
					'lang' => '',
					'is_page_meta_node' => '0',
					'is_page_style_node' => '0',
					'is_page_layout_node' => '0',
					'is_page_theme_node' => '0',
					'is_page_column_node' => '0',
					'room_id' => '11',
					'space_type' => '3',
					'show_count' => '0',
					'display_flag' => '1',
					'display_from_date' => NULL,
					'display_to_date' => NULL,
					'display_apply_subpage' => '1',
					'display_reverse_permalink' => NULL,
					'is_approved' => '1',
					'lock_authority_id' => '0',
					'created' => NULL,
					'created_user_id' => '0',
					'created_user_name' => '',
					'modified' => NULL,
					'modified_user_id' => '1',
					'modified_user_name' => '',
				),
				'PageUserLink' =>
				array (
					'authority_id' => NULL,
				),
				'PageAuthority' =>
				array (
					'id' => NULL,
					'myportal_use_flag' => NULL,
					'private_use_flag' => NULL,
					'hierarchy' => NULL,
				),
				'Community' =>
				array (
					'publication_range_flag' => NULL,
					'participate_force_all_users' => NULL,
					'participate_flag' => NULL,
					'is_upload' => NULL,
					'photo' => NULL,
				),
				'CommunityLang' =>
				array (
					'community_name' => NULL,
					'summary' => NULL,
				),
				'Authority' =>
				array (
					'id' => '1',
					'hierarchy' => '500',
					'allow_creating_community' => '4',
					'allow_new_participant' => true,
					'myportal_use_flag' => '1',
					'allow_myportal_viewing_hierarchy' => '1',
					'private_use_flag' => '1',
					'display_participants_editing' => true,
				),
			),
		);
		$this->assertEqual($result , $ck);


		//listは配列をつくる。
		//'fields'で指定された材料で配列をつくる
		$ck = $this->Page->findViewable(
			'list' ,
			2 ,
			array(
				'fields' => array(
					'Page.id' ,
					'Page.page_name'
				)
			) ,
			array()
		);
		$result = array (
			9 => 'Public room',
			11 => 'Private room of admin',
		);
		$this->assertEqual($result , $ck);

		//menu
		$ck = $this->Page->findViewable(
			'menu' ,
			1 ,
			array() ,
			array()
		);
		$result = array (
			1 =>
			array (
				1 =>
				array (
					1 =>
					array (
						0 =>
						array (
							'Page' =>
							array (
								'id' => '9',
								'root_id' => '9',
								'parent_id' => '1',
								'thread_num' => '1',
								'display_sequence' => '0',
								'page_name' => 'Public room',
								'permalink' => '',
								'position_flag' => '1',
								'lang' => '',
								'is_page_meta_node' => '0',
								'is_page_style_node' => '0',
								'is_page_layout_node' => '0',
								'is_page_theme_node' => '0',
								'is_page_column_node' => '0',
								'room_id' => '9',
								'space_type' => '1',
								'show_count' => '0',
								'display_flag' => '1',
								'display_from_date' => NULL,
								'display_to_date' => NULL,
								'display_apply_subpage' => '1',
								'display_reverse_permalink' => NULL,
								'is_approved' => '1',
								'lock_authority_id' => '0',
								'created' => NULL,
								'created_user_id' => '0',
								'created_user_name' => '',
								'modified' => NULL,
								'modified_user_id' => '1',
								'modified_user_name' => '',
								'visibility_flag' => 1,
							),
							'PageUserLink' =>
							array (
								'authority_id' => '2',
							),
							'PageAuthority' =>
							array (
								'id' => '2',
								'myportal_use_flag' => '0',
								'private_use_flag' => '1',
								'hierarchy' => '350',
							),
							'Community' =>
							array (
								'publication_range_flag' => NULL,
								'participate_force_all_users' => NULL,
								'participate_flag' => NULL,
								'is_upload' => NULL,
								'photo' => NULL,
							),
							'CommunityLang' =>
							array (
								'community_name' => NULL,
								'summary' => NULL,
							),
							'Authority' =>
							array (
								'id' => '1',
								'hierarchy' => '500',
								'allow_creating_community' => '4',
								'allow_new_participant' => true,
								'myportal_use_flag' => '1',
								'allow_myportal_viewing_hierarchy' => '1',
								'private_use_flag' => '1',
								'display_participants_editing' => true,
							),
						),
					),
				),
			),
			3 =>
			array (
				1 =>
				array (
					3 =>
					array (
						0 =>
						array (
							'Page' =>
							array (
								'id' => '11',
								'root_id' => '11',
								'parent_id' => '3',
								'thread_num' => '1',
								'display_sequence' => '0',
								'page_name' => 'Private room of admin',
								'permalink' => 'private/Admin/',
								'position_flag' => '1',
								'lang' => '',
								'is_page_meta_node' => '0',
								'is_page_style_node' => '0',
								'is_page_layout_node' => '0',
								'is_page_theme_node' => '0',
								'is_page_column_node' => '0',
								'room_id' => '11',
								'space_type' => '3',
								'show_count' => '0',
								'display_flag' => '1',
								'display_from_date' => NULL,
								'display_to_date' => NULL,
								'display_apply_subpage' => '1',
								'display_reverse_permalink' => NULL,
								'is_approved' => '1',
								'lock_authority_id' => '0',
								'created' => NULL,
								'created_user_id' => '0',
								'created_user_name' => '',
								'modified' => NULL,
								'modified_user_id' => '1',
								'modified_user_name' => '',
								'visibility_flag' => 1,
							),
							'PageUserLink' =>
							array (
								'authority_id' => '2',
							),
							'PageAuthority' =>
							array (
								'id' => '2',
								'myportal_use_flag' => '0',
								'private_use_flag' => '1',
								'hierarchy' => '350',
							),
							'Community' =>
							array (
								'publication_range_flag' => NULL,
								'participate_force_all_users' => NULL,
								'participate_flag' => NULL,
								'is_upload' => NULL,
								'photo' => NULL,
							),
							'CommunityLang' =>
							array (
								'community_name' => NULL,
								'summary' => NULL,
							),
							'Authority' =>
							array (
								'id' => '1',
								'hierarchy' => '500',
								'allow_creating_community' => '4',
								'allow_new_participant' => true,
								'myportal_use_flag' => '1',
								'allow_myportal_viewing_hierarchy' => '1',
								'private_use_flag' => '1',
								'display_participants_editing' => true,
							),
						),
					),
				),
			),
			4 =>
			array (
				1 =>
				array (
					4 =>
					array (
						1 =>
						array (
							'Page' =>
							array (
								'id' => '16',
								'root_id' => '16',
								'parent_id' => '4',
								'thread_num' => '1',
								'display_sequence' => '1',
								'page_name' => 'UnitTest-A',
								'permalink' => 'community/community-1/',
								'position_flag' => '1',
								'lang' => '',
								'is_page_meta_node' => '0',
								'is_page_style_node' => '0',
								'is_page_layout_node' => '0',
								'is_page_theme_node' => '0',
								'is_page_column_node' => '0',
								'room_id' => '16',
								'space_type' => '4',
								'show_count' => '0',
								'display_flag' => '1',
								'display_from_date' => NULL,
								'display_to_date' => NULL,
								'display_apply_subpage' => '1',
								'display_reverse_permalink' => NULL,
								'is_approved' => '1',
								'lock_authority_id' => '0',
								'created' => '2013-06-24 06:59:37',
								'created_user_id' => '1',
								'created_user_name' => 'admin',
								'modified' => '2013-06-24 06:59:37',
								'modified_user_id' => '1',
								'modified_user_name' => 'admin',
								'visibility_flag' => 1,
							),
							'PageUserLink' =>
							array (
								'authority_id' => '2',
							),
							'PageAuthority' =>
							array (
								'id' => '2',
								'myportal_use_flag' => '0',
								'private_use_flag' => '1',
								'hierarchy' => '350',
							),
							'Community' =>
							array (
								'publication_range_flag' => '11',
								'participate_force_all_users' => true,
								'participate_flag' => '1',
								'is_upload' => true,
								'photo' => 'study.gif',
							),
							'CommunityLang' =>
							array (
								'community_name' => 'UnitTest-A',
								'summary' => 'UnitTest-A UnitTest-A UnitTest-A',
							),
							'Authority' =>
							array (
								'id' => '1',
								'hierarchy' => '500',
								'allow_creating_community' => '4',
								'allow_new_participant' => true,
								'myportal_use_flag' => '1',
								'allow_myportal_viewing_hierarchy' => '1',
								'private_use_flag' => '1',
								'display_participants_editing' => true,
							),
						),
					),
				),
			),
		);

		$this->assertEqual($result , $ck);

		//menu
		$ck = $this->Page->findViewable(
			'menu' ,
			'all' ,
			array() ,
			array()
		);
		$result = array (
			1 =>
			array (
				1 =>
				array (
					1 =>
					array (
						0 =>
						array (
							'Page' =>
							array (
								'id' => '9',
								'root_id' => '9',
								'parent_id' => '1',
								'thread_num' => '1',
								'display_sequence' => '0',
								'page_name' => 'Public room',
								'permalink' => '',
								'position_flag' => '1',
								'lang' => '',
								'is_page_meta_node' => '0',
								'is_page_style_node' => '0',
								'is_page_layout_node' => '0',
								'is_page_theme_node' => '0',
								'is_page_column_node' => '0',
								'room_id' => '9',
								'space_type' => '1',
								'show_count' => '0',
								'display_flag' => '1',
								'display_from_date' => NULL,
								'display_to_date' => NULL,
								'display_apply_subpage' => '1',
								'display_reverse_permalink' => NULL,
								'is_approved' => '1',
								'lock_authority_id' => '0',
								'created' => NULL,
								'created_user_id' => '0',
								'created_user_name' => '',
								'modified' => NULL,
								'modified_user_id' => '1',
								'modified_user_name' => '',
								'visibility_flag' => 1,
							),
							'PageUserLink' =>
							array (
								'authority_id' => NULL,
							),
							'PageAuthority' =>
							array (
								'id' => NULL,
								'myportal_use_flag' => NULL,
								'private_use_flag' => NULL,
								'hierarchy' => NULL,
							),
							'Community' =>
							array (
								'publication_range_flag' => NULL,
								'participate_force_all_users' => NULL,
								'participate_flag' => NULL,
								'is_upload' => NULL,
								'photo' => NULL,
							),
							'CommunityLang' =>
							array (
								'community_name' => NULL,
								'summary' => NULL,
							),
						),
					),
				),
			),
			3 =>
			array (
				1 =>
				array (
					3 =>
					array (
						0 =>
						array (
							'Page' =>
							array (
								'id' => '11',
								'root_id' => '11',
								'parent_id' => '3',
								'thread_num' => '1',
								'display_sequence' => '0',
								'page_name' => 'Private room of admin',
								'permalink' => 'private/Admin/',
								'position_flag' => '1',
								'lang' => '',
								'is_page_meta_node' => '0',
								'is_page_style_node' => '0',
								'is_page_layout_node' => '0',
								'is_page_theme_node' => '0',
								'is_page_column_node' => '0',
								'room_id' => '11',
								'space_type' => '3',
								'show_count' => '0',
								'display_flag' => '1',
								'display_from_date' => NULL,
								'display_to_date' => NULL,
								'display_apply_subpage' => '1',
								'display_reverse_permalink' => NULL,
								'is_approved' => '1',
								'lock_authority_id' => '0',
								'created' => NULL,
								'created_user_id' => '0',
								'created_user_name' => '',
								'modified' => NULL,
								'modified_user_id' => '1',
								'modified_user_name' => '',
								'visibility_flag' => 1,
							),
							'PageUserLink' =>
							array (
								'authority_id' => NULL,
							),
							'PageAuthority' =>
							array (
								'id' => NULL,
								'myportal_use_flag' => NULL,
								'private_use_flag' => NULL,
								'hierarchy' => NULL,
							),
							'Community' =>
							array (
								'publication_range_flag' => NULL,
								'participate_force_all_users' => NULL,
								'participate_flag' => NULL,
								'is_upload' => NULL,
								'photo' => NULL,
							),
							'CommunityLang' =>
							array (
								'community_name' => NULL,
								'summary' => NULL,
							),
						),
					),
				),
			),
			4 =>
			array (
				1 =>
				array (
					4 =>
					array (
						1 =>
						array (
							'Page' =>
							array (
								'id' => '16',
								'root_id' => '16',
								'parent_id' => '4',
								'thread_num' => '1',
								'display_sequence' => '1',
								'page_name' => 'UnitTest-A',
								'permalink' => 'community/community-1/',
								'position_flag' => '1',
								'lang' => '',
								'is_page_meta_node' => '0',
								'is_page_style_node' => '0',
								'is_page_layout_node' => '0',
								'is_page_theme_node' => '0',
								'is_page_column_node' => '0',
								'room_id' => '16',
								'space_type' => '4',
								'show_count' => '0',
								'display_flag' => '1',
								'display_from_date' => NULL,
								'display_to_date' => NULL,
								'display_apply_subpage' => '1',
								'display_reverse_permalink' => NULL,
								'is_approved' => '1',
								'lock_authority_id' => '0',
								'created' => '2013-06-24 06:59:37',
								'created_user_id' => '1',
								'created_user_name' => 'admin',
								'modified' => '2013-06-24 06:59:37',
								'modified_user_id' => '1',
								'modified_user_name' => 'admin',
								'visibility_flag' => 1,
							),
							'PageUserLink' =>
							array (
								'authority_id' => NULL,
							),
							'PageAuthority' =>
							array (
								'id' => NULL,
								'myportal_use_flag' => NULL,
								'private_use_flag' => NULL,
								'hierarchy' => NULL,
							),
							'Community' =>
							array (
								'publication_range_flag' => '11',
								'participate_force_all_users' => true,
								'participate_flag' => '1',
								'is_upload' => true,
								'photo' => 'study.gif',
							),
							'CommunityLang' =>
							array (
								'community_name' => 'UnitTest-A',
								'summary' => 'UnitTest-A UnitTest-A UnitTest-A',
							),
						),
					),
				),
			),
		);
		$this->assertEqual($result , $ck);

		//menu option
		$ck = $this->Page->findViewable(
			'menu' ,
			1 ,
			array() ,
			array('ativePageId'=>16) //'ativePageId': $type='menu'時に使用。アクティブなページIDを指定default null
		);

		$result = array (
			1 =>
			array (
				1 =>
				array (
					1 =>
					array (
						0 =>
						array (
							'Page' =>
							array (
								'id' => '9',
								'root_id' => '9',
								'parent_id' => '1',
								'thread_num' => '1',
								'display_sequence' => '0',
								'page_name' => 'Public room',
								'permalink' => '',
								'position_flag' => '1',
								'lang' => '',
								'is_page_meta_node' => '0',
								'is_page_style_node' => '0',
								'is_page_layout_node' => '0',
								'is_page_theme_node' => '0',
								'is_page_column_node' => '0',
								'room_id' => '9',
								'space_type' => '1',
								'show_count' => '0',
								'display_flag' => '1',
								'display_from_date' => NULL,
								'display_to_date' => NULL,
								'display_apply_subpage' => '1',
								'display_reverse_permalink' => NULL,
								'is_approved' => '1',
								'lock_authority_id' => '0',
								'created' => NULL,
								'created_user_id' => '0',
								'created_user_name' => '',
								'modified' => NULL,
								'modified_user_id' => '1',
								'modified_user_name' => '',
								'active' => false,
								'show' => true,
								'visibility_flag' => 1,
							),
							'PageUserLink' =>
							array (
								'authority_id' => '2',
							),
							'PageAuthority' =>
							array (
								'id' => '2',
								'myportal_use_flag' => '0',
								'private_use_flag' => '1',
								'hierarchy' => '350',
							),
							'Community' =>
							array (
								'publication_range_flag' => NULL,
								'participate_force_all_users' => NULL,
								'participate_flag' => NULL,
								'is_upload' => NULL,
								'photo' => NULL,
							),
							'CommunityLang' =>
							array (
								'community_name' => NULL,
								'summary' => NULL,
							),
							'Authority' =>
							array (
								'id' => '1',
								'hierarchy' => '500',
								'allow_creating_community' => '4',
								'allow_new_participant' => true,
								'myportal_use_flag' => '1',
								'allow_myportal_viewing_hierarchy' => '1',
								'private_use_flag' => '1',
								'display_participants_editing' => true,
							),
						),
					),
				),
			),
			3 =>
			array (
				1 =>
				array (
					3 =>
					array (
						0 =>
						array (
							'Page' =>
							array (
								'id' => '11',
								'root_id' => '11',
								'parent_id' => '3',
								'thread_num' => '1',
								'display_sequence' => '0',
								'page_name' => 'Private room of admin',
								'permalink' => 'private/Admin/',
								'position_flag' => '1',
								'lang' => '',
								'is_page_meta_node' => '0',
								'is_page_style_node' => '0',
								'is_page_layout_node' => '0',
								'is_page_theme_node' => '0',
								'is_page_column_node' => '0',
								'room_id' => '11',
								'space_type' => '3',
								'show_count' => '0',
								'display_flag' => '1',
								'display_from_date' => NULL,
								'display_to_date' => NULL,
								'display_apply_subpage' => '1',
								'display_reverse_permalink' => NULL,
								'is_approved' => '1',
								'lock_authority_id' => '0',
								'created' => NULL,
								'created_user_id' => '0',
								'created_user_name' => '',
								'modified' => NULL,
								'modified_user_id' => '1',
								'modified_user_name' => '',
								'active' => false,
								'show' => true,
								'visibility_flag' => 1,
							),
							'PageUserLink' =>
							array (
								'authority_id' => '2',
							),
							'PageAuthority' =>
							array (
								'id' => '2',
								'myportal_use_flag' => '0',
								'private_use_flag' => '1',
								'hierarchy' => '350',
							),
							'Community' =>
							array (
								'publication_range_flag' => NULL,
								'participate_force_all_users' => NULL,
								'participate_flag' => NULL,
								'is_upload' => NULL,
								'photo' => NULL,
							),
							'CommunityLang' =>
							array (
								'community_name' => NULL,
								'summary' => NULL,
							),
							'Authority' =>
							array (
								'id' => '1',
								'hierarchy' => '500',
								'allow_creating_community' => '4',
								'allow_new_participant' => true,
								'myportal_use_flag' => '1',
								'allow_myportal_viewing_hierarchy' => '1',
								'private_use_flag' => '1',
								'display_participants_editing' => true,
							),
						),
					),
				),
			),
			4 =>
			array (
				1 =>
				array (
					4 =>
					array (
						1 =>
						array (
							'Page' =>
							array (
								'id' => '16',
								'root_id' => '16',
								'parent_id' => '4',
								'thread_num' => '1',
								'display_sequence' => '1',
								'page_name' => 'UnitTest-A',
								'permalink' => 'community/community-1/',
								'position_flag' => '1',
								'lang' => '',
								'is_page_meta_node' => '0',
								'is_page_style_node' => '0',
								'is_page_layout_node' => '0',
								'is_page_theme_node' => '0',
								'is_page_column_node' => '0',
								'room_id' => '16',
								'space_type' => '4',
								'show_count' => '0',
								'display_flag' => '1',
								'display_from_date' => NULL,
								'display_to_date' => NULL,
								'display_apply_subpage' => '1',
								'display_reverse_permalink' => NULL,
								'is_approved' => '1',
								'lock_authority_id' => '0',
								'created' => '2013-06-24 06:59:37',
								'created_user_id' => '1',
								'created_user_name' => 'admin',
								'modified' => '2013-06-24 06:59:37',
								'modified_user_id' => '1',
								'modified_user_name' => 'admin',
								'active' => true,
								'show' => true,
								'visibility_flag' => 1,
							),
							'PageUserLink' =>
							array (
								'authority_id' => '2',
							),
							'PageAuthority' =>
							array (
								'id' => '2',
								'myportal_use_flag' => '0',
								'private_use_flag' => '1',
								'hierarchy' => '350',
							),
							'Community' =>
							array (
								'publication_range_flag' => '11',
								'participate_force_all_users' => true,
								'participate_flag' => '1',
								'is_upload' => true,
								'photo' => 'study.gif',
							),
							'CommunityLang' =>
							array (
								'community_name' => 'UnitTest-A',
								'summary' => 'UnitTest-A UnitTest-A UnitTest-A',
							),
							'Authority' =>
							array (
								'id' => '1',
								'hierarchy' => '500',
								'allow_creating_community' => '4',
								'allow_new_participant' => true,
								'myportal_use_flag' => '1',
								'allow_myportal_viewing_hierarchy' => '1',
								'private_use_flag' => '1',
								'display_participants_editing' => true,
							),
						),
					),
				),
			),
		);
		$this->assertEqual($result , $ck);

		$ck = $this->Page->findViewable(
			'menu' ,
			2 ,
			array() ,
			array('ativePageId'=>16) //'ativePageId': $type='menu'時に使用。アクティブなページIDを指定default null
		);
		$result = array (
			1 =>
			array (
				1 =>
				array (
					1 =>
					array (
						0 =>
						array (
							'Page' =>
							array (
								'id' => '9',
								'root_id' => '9',
								'parent_id' => '1',
								'thread_num' => '1',
								'display_sequence' => '0',
								'page_name' => 'Public room',
								'permalink' => '',
								'position_flag' => '1',
								'lang' => '',
								'is_page_meta_node' => '0',
								'is_page_style_node' => '0',
								'is_page_layout_node' => '0',
								'is_page_theme_node' => '0',
								'is_page_column_node' => '0',
								'room_id' => '9',
								'space_type' => '1',
								'show_count' => '0',
								'display_flag' => '1',
								'display_from_date' => NULL,
								'display_to_date' => NULL,
								'display_apply_subpage' => '1',
								'display_reverse_permalink' => NULL,
								'is_approved' => '1',
								'lock_authority_id' => '0',
								'created' => NULL,
								'created_user_id' => '0',
								'created_user_name' => '',
								'modified' => NULL,
								'modified_user_id' => '1',
								'modified_user_name' => '',
								'active' => false,
								'show' => true,
								'visibility_flag' => 1,
							),
							'PageUserLink' =>
							array (
								'authority_id' => NULL,
							),
							'PageAuthority' =>
							array (
								'id' => NULL,
								'myportal_use_flag' => NULL,
								'private_use_flag' => NULL,
								'hierarchy' => NULL,
							),
							'Community' =>
							array (
								'publication_range_flag' => NULL,
								'participate_force_all_users' => NULL,
								'participate_flag' => NULL,
								'is_upload' => NULL,
								'photo' => NULL,
							),
							'CommunityLang' =>
							array (
								'community_name' => NULL,
								'summary' => NULL,
							),
							'Authority' =>
							array (
								'id' => '1',
								'hierarchy' => '500',
								'allow_creating_community' => '4',
								'allow_new_participant' => true,
								'myportal_use_flag' => '1',
								'allow_myportal_viewing_hierarchy' => '1',
								'private_use_flag' => '1',
								'display_participants_editing' => true,
							),
						),
					),
				),
			),
			3 =>
			array (
				1 =>
				array (
					3 =>
					array (
						0 =>
						array (
							'Page' =>
							array (
								'id' => '11',
								'root_id' => '11',
								'parent_id' => '3',
								'thread_num' => '1',
								'display_sequence' => '0',
								'page_name' => 'Private room of admin',
								'permalink' => 'private/Admin/',
								'position_flag' => '1',
								'lang' => '',
								'is_page_meta_node' => '0',
								'is_page_style_node' => '0',
								'is_page_layout_node' => '0',
								'is_page_theme_node' => '0',
								'is_page_column_node' => '0',
								'room_id' => '11',
								'space_type' => '3',
								'show_count' => '0',
								'display_flag' => '1',
								'display_from_date' => NULL,
								'display_to_date' => NULL,
								'display_apply_subpage' => '1',
								'display_reverse_permalink' => NULL,
								'is_approved' => '1',
								'lock_authority_id' => '0',
								'created' => NULL,
								'created_user_id' => '0',
								'created_user_name' => '',
								'modified' => NULL,
								'modified_user_id' => '1',
								'modified_user_name' => '',
								'active' => false,
								'show' => true,
								'visibility_flag' => 1,
							),
							'PageUserLink' =>
							array (
								'authority_id' => NULL,
							),
							'PageAuthority' =>
							array (
								'id' => NULL,
								'myportal_use_flag' => NULL,
								'private_use_flag' => NULL,
								'hierarchy' => NULL,
							),
							'Community' =>
							array (
								'publication_range_flag' => NULL,
								'participate_force_all_users' => NULL,
								'participate_flag' => NULL,
								'is_upload' => NULL,
								'photo' => NULL,
							),
							'CommunityLang' =>
							array (
								'community_name' => NULL,
								'summary' => NULL,
							),
							'Authority' =>
							array (
								'id' => '1',
								'hierarchy' => '500',
								'allow_creating_community' => '4',
								'allow_new_participant' => true,
								'myportal_use_flag' => '1',
								'allow_myportal_viewing_hierarchy' => '1',
								'private_use_flag' => '1',
								'display_participants_editing' => true,
							),
						),
					),
				),
			),
		);
		$this->assertEqual($result , $ck);

		//User.id 1がログインしている状態をつくる。
		$User = ClassRegistry::init("User");
		$user = $User->find("first" , array('conditions'=>array('User.id'=>1),) );
		Configure::write(NC_SYSTEM_KEY.'.user' , $user['User']);

		$page = $this->Page->find('first' , array('conditions'=>array('Page.id'=>1)) );
		Configure::write(NC_SYSTEM_KEY.'.'.'center_page' , $page);
		$ck = $this->Page->findViewable('all' , 'all');
		$result = array (
			9 =>
			array (
				'Page' =>
				array (
					'id' => '9',
					'root_id' => '9',
					'parent_id' => '1',
					'thread_num' => '1',
					'display_sequence' => '0',
					'page_name' => 'Public room',
					'permalink' => '',
					'position_flag' => '1',
					'lang' => '',
					'is_page_meta_node' => '0',
					'is_page_style_node' => '0',
					'is_page_layout_node' => '0',
					'is_page_theme_node' => '0',
					'is_page_column_node' => '0',
					'room_id' => '9',
					'space_type' => '1',
					'show_count' => '0',
					'display_flag' => '1',
					'display_from_date' => NULL,
					'display_to_date' => NULL,
					'display_apply_subpage' => '1',
					'display_reverse_permalink' => NULL,
					'is_approved' => '1',
					'lock_authority_id' => '0',
					'created' => NULL,
					'created_user_id' => '0',
					'created_user_name' => '',
					'modified' => NULL,
					'modified_user_id' => '1',
					'modified_user_name' => '',
				),
				'PageUserLink' =>
				array (
					'authority_id' => '2',
				),
				'PageAuthority' =>
				array (
					'id' => '2',
					'myportal_use_flag' => '0',
					'private_use_flag' => '1',
					'hierarchy' => '350',
				),
				'Community' =>
				array (
					'publication_range_flag' => NULL,
					'participate_force_all_users' => NULL,
					'participate_flag' => NULL,
					'is_upload' => NULL,
					'photo' => NULL,
				),
				'CommunityLang' =>
				array (
					'community_name' => NULL,
					'summary' => NULL,
				),
				'Authority' =>
				array (
					'id' => '1',
					'hierarchy' => '500',
					'allow_creating_community' => '4',
					'allow_new_participant' => true,
					'myportal_use_flag' => '1',
					'allow_myportal_viewing_hierarchy' => '1',
					'private_use_flag' => '1',
					'display_participants_editing' => true,
				),
			),
			11 =>
			array (
				'Page' =>
				array (
					'id' => '11',
					'root_id' => '11',
					'parent_id' => '3',
					'thread_num' => '1',
					'display_sequence' => '0',
					'page_name' => 'Private room of admin',
					'permalink' => 'Admin',
					'position_flag' => '1',
					'lang' => '',
					'is_page_meta_node' => '0',
					'is_page_style_node' => '0',
					'is_page_layout_node' => '0',
					'is_page_theme_node' => '0',
					'is_page_column_node' => '0',
					'room_id' => '11',
					'space_type' => '3',
					'show_count' => '0',
					'display_flag' => '1',
					'display_from_date' => NULL,
					'display_to_date' => NULL,
					'display_apply_subpage' => '1',
					'display_reverse_permalink' => NULL,
					'is_approved' => '1',
					'lock_authority_id' => '0',
					'created' => NULL,
					'created_user_id' => '0',
					'created_user_name' => '',
					'modified' => NULL,
					'modified_user_id' => '1',
					'modified_user_name' => '',
				),
				'PageUserLink' =>
				array (
					'authority_id' => '2',
				),
				'PageAuthority' =>
				array (
					'id' => '2',
					'myportal_use_flag' => '0',
					'private_use_flag' => '1',
					'hierarchy' => '350',
				),
				'Community' =>
				array (
					'publication_range_flag' => NULL,
					'participate_force_all_users' => NULL,
					'participate_flag' => NULL,
					'is_upload' => NULL,
					'photo' => NULL,
				),
				'CommunityLang' =>
				array (
					'community_name' => NULL,
					'summary' => NULL,
				),
				'Authority' =>
				array (
					'id' => '1',
					'hierarchy' => '500',
					'allow_creating_community' => '4',
					'allow_new_participant' => true,
					'myportal_use_flag' => '1',
					'allow_myportal_viewing_hierarchy' => '1',
					'private_use_flag' => '1',
					'display_participants_editing' => true,
				),
			),
			16 =>
			array (
				'Page' =>
				array (
					'id' => '16',
					'root_id' => '16',
					'parent_id' => '4',
					'thread_num' => '1',
					'display_sequence' => '1',
					'page_name' => 'UnitTest-A',
					'permalink' => 'community-1',
					'position_flag' => '1',
					'lang' => '',
					'is_page_meta_node' => '0',
					'is_page_style_node' => '0',
					'is_page_layout_node' => '0',
					'is_page_theme_node' => '0',
					'is_page_column_node' => '0',
					'room_id' => '16',
					'space_type' => '4',
					'show_count' => '0',
					'display_flag' => '1',
					'display_from_date' => NULL,
					'display_to_date' => NULL,
					'display_apply_subpage' => '1',
					'display_reverse_permalink' => NULL,
					'is_approved' => '1',
					'lock_authority_id' => '0',
					'created' => '2013-06-24 06:59:37',
					'created_user_id' => '1',
					'created_user_name' => 'admin',
					'modified' => '2013-06-24 06:59:37',
					'modified_user_id' => '1',
					'modified_user_name' => 'admin',
				),
				'PageUserLink' =>
				array (
					'authority_id' => '2',
				),
				'PageAuthority' =>
				array (
					'id' => '2',
					'myportal_use_flag' => '0',
					'private_use_flag' => '1',
					'hierarchy' => '350',
				),
				'Community' =>
				array (
					'publication_range_flag' => '11',
					'participate_force_all_users' => true,
					'participate_flag' => '1',
					'is_upload' => true,
					'photo' => 'study.gif',
				),
				'CommunityLang' =>
				array (
					'community_name' => 'UnitTest-A',
					'summary' => 'UnitTest-A UnitTest-A UnitTest-A',
				),
				'Authority' =>
				array (
					'id' => '1',
					'hierarchy' => '500',
					'allow_creating_community' => '4',
					'allow_new_participant' => true,
					'myportal_use_flag' => '1',
					'allow_myportal_viewing_hierarchy' => '1',
					'private_use_flag' => '1',
					'display_participants_editing' => true,
				),
			),
		);
		//var_export($ck);
		$this->assertEqual($result , $ck);

		$ck = $this->Page->findViewable('all' , 1 , array() , array('isShowAllCommunity'=>true));
		$result = array (
			9 =>
			array (
				'Page' =>
				array (
					'id' => '9',
					'root_id' => '9',
					'parent_id' => '1',
					'thread_num' => '1',
					'display_sequence' => '0',
					'page_name' => 'Public room',
					'permalink' => '',
					'position_flag' => '1',
					'lang' => '',
					'is_page_meta_node' => '0',
					'is_page_style_node' => '0',
					'is_page_layout_node' => '0',
					'is_page_theme_node' => '0',
					'is_page_column_node' => '0',
					'room_id' => '9',
					'space_type' => '1',
					'show_count' => '0',
					'display_flag' => '1',
					'display_from_date' => NULL,
					'display_to_date' => NULL,
					'display_apply_subpage' => '1',
					'display_reverse_permalink' => NULL,
					'is_approved' => '1',
					'lock_authority_id' => '0',
					'created' => NULL,
					'created_user_id' => '0',
					'created_user_name' => '',
					'modified' => NULL,
					'modified_user_id' => '1',
					'modified_user_name' => '',
				),
				'PageUserLink' =>
				array (
					'authority_id' => '2',
				),
				'PageAuthority' =>
				array (
					'id' => '2',
					'myportal_use_flag' => '0',
					'private_use_flag' => '1',
					'hierarchy' => '350',
				),
				'Community' =>
				array (
					'publication_range_flag' => NULL,
					'participate_force_all_users' => NULL,
					'participate_flag' => NULL,
					'is_upload' => NULL,
					'photo' => NULL,
				),
				'CommunityLang' =>
				array (
					'community_name' => NULL,
					'summary' => NULL,
				),
				'Authority' =>
				array (
					'id' => '1',
					'hierarchy' => '500',
					'allow_creating_community' => '4',
					'allow_new_participant' => true,
					'myportal_use_flag' => '1',
					'allow_myportal_viewing_hierarchy' => '1',
					'private_use_flag' => '1',
					'display_participants_editing' => true,
				),
			),
			11 =>
			array (
				'Page' =>
				array (
					'id' => '11',
					'root_id' => '11',
					'parent_id' => '3',
					'thread_num' => '1',
					'display_sequence' => '0',
					'page_name' => 'Private room of admin',
					'permalink' => 'Admin',
					'position_flag' => '1',
					'lang' => '',
					'is_page_meta_node' => '0',
					'is_page_style_node' => '0',
					'is_page_layout_node' => '0',
					'is_page_theme_node' => '0',
					'is_page_column_node' => '0',
					'room_id' => '11',
					'space_type' => '3',
					'show_count' => '0',
					'display_flag' => '1',
					'display_from_date' => NULL,
					'display_to_date' => NULL,
					'display_apply_subpage' => '1',
					'display_reverse_permalink' => NULL,
					'is_approved' => '1',
					'lock_authority_id' => '0',
					'created' => NULL,
					'created_user_id' => '0',
					'created_user_name' => '',
					'modified' => NULL,
					'modified_user_id' => '1',
					'modified_user_name' => '',
				),
				'PageUserLink' =>
				array (
					'authority_id' => '2',
				),
				'PageAuthority' =>
				array (
					'id' => '2',
					'myportal_use_flag' => '0',
					'private_use_flag' => '1',
					'hierarchy' => '350',
				),
				'Community' =>
				array (
					'publication_range_flag' => NULL,
					'participate_force_all_users' => NULL,
					'participate_flag' => NULL,
					'is_upload' => NULL,
					'photo' => NULL,
				),
				'CommunityLang' =>
				array (
					'community_name' => NULL,
					'summary' => NULL,
				),
				'Authority' =>
				array (
					'id' => '1',
					'hierarchy' => '500',
					'allow_creating_community' => '4',
					'allow_new_participant' => true,
					'myportal_use_flag' => '1',
					'allow_myportal_viewing_hierarchy' => '1',
					'private_use_flag' => '1',
					'display_participants_editing' => true,
				),
			),
			16 =>
			array (
				'Page' =>
				array (
					'id' => '16',
					'root_id' => '16',
					'parent_id' => '4',
					'thread_num' => '1',
					'display_sequence' => '1',
					'page_name' => 'UnitTest-A',
					'permalink' => 'community-1',
					'position_flag' => '1',
					'lang' => '',
					'is_page_meta_node' => '0',
					'is_page_style_node' => '0',
					'is_page_layout_node' => '0',
					'is_page_theme_node' => '0',
					'is_page_column_node' => '0',
					'room_id' => '16',
					'space_type' => '4',
					'show_count' => '0',
					'display_flag' => '1',
					'display_from_date' => NULL,
					'display_to_date' => NULL,
					'display_apply_subpage' => '1',
					'display_reverse_permalink' => NULL,
					'is_approved' => '1',
					'lock_authority_id' => '0',
					'created' => '2013-06-24 06:59:37',
					'created_user_id' => '1',
					'created_user_name' => 'admin',
					'modified' => '2013-06-24 06:59:37',
					'modified_user_id' => '1',
					'modified_user_name' => 'admin',
				),
				'PageUserLink' =>
				array (
					'authority_id' => '2',
				),
				'PageAuthority' =>
				array (
					'id' => '2',
					'myportal_use_flag' => '0',
					'private_use_flag' => '1',
					'hierarchy' => '350',
				),
				'Community' =>
				array (
					'publication_range_flag' => '11',
					'participate_force_all_users' => true,
					'participate_flag' => '1',
					'is_upload' => true,
					'photo' => 'study.gif',
				),
				'CommunityLang' =>
				array (
					'community_name' => 'UnitTest-A',
					'summary' => 'UnitTest-A UnitTest-A UnitTest-A',
				),
				'Authority' =>
				array (
					'id' => '1',
					'hierarchy' => '500',
					'allow_creating_community' => '4',
					'allow_new_participant' => true,
					'myportal_use_flag' => '1',
					'allow_myportal_viewing_hierarchy' => '1',
					'private_use_flag' => '1',
					'display_participants_editing' => true,
				),
			),
		);
		$this->assertEqual($ck , $result);

		$ck = $this->Page->findViewable('all' , 2 , array() , array('isRoom'=>true));
		$result = array (
			9 =>
			array (
				'Page' =>
				array (
					'id' => '9',
					'root_id' => '9',
					'parent_id' => '1',
					'thread_num' => '1',
					'display_sequence' => '0',
					'page_name' => 'Public room',
					'permalink' => '',
					'position_flag' => '1',
					'lang' => '',
					'is_page_meta_node' => '0',
					'is_page_style_node' => '0',
					'is_page_layout_node' => '0',
					'is_page_theme_node' => '0',
					'is_page_column_node' => '0',
					'room_id' => '9',
					'space_type' => '1',
					'show_count' => '0',
					'display_flag' => '1',
					'display_from_date' => NULL,
					'display_to_date' => NULL,
					'display_apply_subpage' => '1',
					'display_reverse_permalink' => NULL,
					'is_approved' => '1',
					'lock_authority_id' => '0',
					'created' => NULL,
					'created_user_id' => '0',
					'created_user_name' => '',
					'modified' => NULL,
					'modified_user_id' => '1',
					'modified_user_name' => '',
				),
				'PageUserLink' =>
				array (
					'authority_id' => NULL,
				),
				'PageAuthority' =>
				array (
					'id' => NULL,
					'myportal_use_flag' => NULL,
					'private_use_flag' => NULL,
					'hierarchy' => NULL,
				),
				'Community' =>
				array (
					'publication_range_flag' => NULL,
					'participate_force_all_users' => NULL,
					'participate_flag' => NULL,
					'is_upload' => NULL,
					'photo' => NULL,
				),
				'CommunityLang' =>
				array (
					'community_name' => NULL,
					'summary' => NULL,
				),
				'Authority' =>
				array (
					'id' => '1',
					'hierarchy' => '500',
					'allow_creating_community' => '4',
					'allow_new_participant' => true,
					'myportal_use_flag' => '1',
					'allow_myportal_viewing_hierarchy' => '1',
					'private_use_flag' => '1',
					'display_participants_editing' => true,
				),
			),
			11 =>
			array (
				'Page' =>
				array (
					'id' => '11',
					'root_id' => '11',
					'parent_id' => '3',
					'thread_num' => '1',
					'display_sequence' => '0',
					'page_name' => 'Private room of admin',
					'permalink' => 'Admin',
					'position_flag' => '1',
					'lang' => '',
					'is_page_meta_node' => '0',
					'is_page_style_node' => '0',
					'is_page_layout_node' => '0',
					'is_page_theme_node' => '0',
					'is_page_column_node' => '0',
					'room_id' => '11',
					'space_type' => '3',
					'show_count' => '0',
					'display_flag' => '1',
					'display_from_date' => NULL,
					'display_to_date' => NULL,
					'display_apply_subpage' => '1',
					'display_reverse_permalink' => NULL,
					'is_approved' => '1',
					'lock_authority_id' => '0',
					'created' => NULL,
					'created_user_id' => '0',
					'created_user_name' => '',
					'modified' => NULL,
					'modified_user_id' => '1',
					'modified_user_name' => '',
				),
				'PageUserLink' =>
				array (
					'authority_id' => NULL,
				),
				'PageAuthority' =>
				array (
					'id' => NULL,
					'myportal_use_flag' => NULL,
					'private_use_flag' => NULL,
					'hierarchy' => NULL,
				),
				'Community' =>
				array (
					'publication_range_flag' => NULL,
					'participate_force_all_users' => NULL,
					'participate_flag' => NULL,
					'is_upload' => NULL,
					'photo' => NULL,
				),
				'CommunityLang' =>
				array (
					'community_name' => NULL,
					'summary' => NULL,
				),
				'Authority' =>
				array (
					'id' => '1',
					'hierarchy' => '500',
					'allow_creating_community' => '4',
					'allow_new_participant' => true,
					'myportal_use_flag' => '1',
					'allow_myportal_viewing_hierarchy' => '1',
					'private_use_flag' => '1',
					'display_participants_editing' => true,
				),
			),
		);
		$this->assertEqual($ck , $result);

		$ck = $this->Page->findViewable('all' , 2 , array() , array('isShowAllCommunity'=>true , 'isRoom'=>true));
		$result = array (
			9 =>
			array (
				'Page' =>
				array (
					'id' => '9',
					'root_id' => '9',
					'parent_id' => '1',
					'thread_num' => '1',
					'display_sequence' => '0',
					'page_name' => 'Public room',
					'permalink' => '',
					'position_flag' => '1',
					'lang' => '',
					'is_page_meta_node' => '0',
					'is_page_style_node' => '0',
					'is_page_layout_node' => '0',
					'is_page_theme_node' => '0',
					'is_page_column_node' => '0',
					'room_id' => '9',
					'space_type' => '1',
					'show_count' => '0',
					'display_flag' => '1',
					'display_from_date' => NULL,
					'display_to_date' => NULL,
					'display_apply_subpage' => '1',
					'display_reverse_permalink' => NULL,
					'is_approved' => '1',
					'lock_authority_id' => '0',
					'created' => NULL,
					'created_user_id' => '0',
					'created_user_name' => '',
					'modified' => NULL,
					'modified_user_id' => '1',
					'modified_user_name' => '',
				),
				'PageUserLink' =>
				array (
					'authority_id' => NULL,
				),
				'PageAuthority' =>
				array (
					'id' => NULL,
					'myportal_use_flag' => NULL,
					'private_use_flag' => NULL,
					'hierarchy' => NULL,
				),
				'Community' =>
				array (
					'publication_range_flag' => NULL,
					'participate_force_all_users' => NULL,
					'participate_flag' => NULL,
					'is_upload' => NULL,
					'photo' => NULL,
				),
				'CommunityLang' =>
				array (
					'community_name' => NULL,
					'summary' => NULL,
				),
				'Authority' =>
				array (
					'id' => '1',
					'hierarchy' => '500',
					'allow_creating_community' => '4',
					'allow_new_participant' => true,
					'myportal_use_flag' => '1',
					'allow_myportal_viewing_hierarchy' => '1',
					'private_use_flag' => '1',
					'display_participants_editing' => true,
				),
			),
			11 =>
			array (
				'Page' =>
				array (
					'id' => '11',
					'root_id' => '11',
					'parent_id' => '3',
					'thread_num' => '1',
					'display_sequence' => '0',
					'page_name' => 'Private room of admin',
					'permalink' => 'Admin',
					'position_flag' => '1',
					'lang' => '',
					'is_page_meta_node' => '0',
					'is_page_style_node' => '0',
					'is_page_layout_node' => '0',
					'is_page_theme_node' => '0',
					'is_page_column_node' => '0',
					'room_id' => '11',
					'space_type' => '3',
					'show_count' => '0',
					'display_flag' => '1',
					'display_from_date' => NULL,
					'display_to_date' => NULL,
					'display_apply_subpage' => '1',
					'display_reverse_permalink' => NULL,
					'is_approved' => '1',
					'lock_authority_id' => '0',
					'created' => NULL,
					'created_user_id' => '0',
					'created_user_name' => '',
					'modified' => NULL,
					'modified_user_id' => '1',
					'modified_user_name' => '',
				),
				'PageUserLink' =>
				array (
					'authority_id' => NULL,
				),
				'PageAuthority' =>
				array (
					'id' => NULL,
					'myportal_use_flag' => NULL,
					'private_use_flag' => NULL,
					'hierarchy' => NULL,
				),
				'Community' =>
				array (
					'publication_range_flag' => NULL,
					'participate_force_all_users' => NULL,
					'participate_flag' => NULL,
					'is_upload' => NULL,
					'photo' => NULL,
				),
				'CommunityLang' =>
				array (
					'community_name' => NULL,
					'summary' => NULL,
				),
				'Authority' =>
				array (
					'id' => '1',
					'hierarchy' => '500',
					'allow_creating_community' => '4',
					'allow_new_participant' => true,
					'myportal_use_flag' => '1',
					'allow_myportal_viewing_hierarchy' => '1',
					'private_use_flag' => '1',
					'display_participants_editing' => true,
				),
			),
		);
		$this->assertEqual($ck , $result);

	}

	public function test_format_findViewable_options() {
		$base= array(
			'isShowAllCommunity' => false,
			'isMyPortalCurrent' => false,
			'ativePageId' => null,
			'autoLang' => true,
			'isRoom' => false
		);

		$result = $base;
		$ck = $this->Page->_format_findViewable_options();
		$this->assertEqual($ck , $result);

		$array = array(
			'isShowAllCommunity' => true,
		);

		$result = $base;
		$result['isShowAllCommunity'] = true;
		$ck = $this->Page->_format_findViewable_options($array);
		$this->assertEqual($ck , $result);

		$array = array(
			'isShowAllCommunity' => true,
			'hogehoge'=>false
		);
		$result = $base;
		$result['isShowAllCommunity'] = true;
		$result['hogehoge'] = false;
		$ck = $this->Page->_format_findViewable_options($array);
		$this->assertEqual($ck , $result);

		$result = $base;
		$ck = $this->Page->_format_findViewable_options('AAAAAAAA');
		$this->assertEqual($ck , $result);

		$result = $base;
		$ck = $this->Page->_format_findViewable_options(12345);
		$this->assertEqual($ck , $result);
	}

	public function test_format_findViewable_currentMyPortal() {
		$array = array();
		$ck = $this->Page->_format_findViewable_currentMyPortal(array());
		$this->assertEqual($ck , null);

		$ck = $this->Page->_format_findViewable_currentMyPortal('AAAAAAAA');
		$this->assertEqual($ck , null);

		$ck = $this->Page->_format_findViewable_currentMyPortal('');
		$this->assertEqual($ck , null);

		$ck = $this->Page->_format_findViewable_currentMyPortal(1);
		$this->assertEqual($ck , null);

		$array['User'] = array(
			'id'=>"2",
			'login_id'=>'admin_2' ,
			'password'=>'' ,
			'handle'=>'admin',
			'authority_id'=>"1",
			'is_active'=>"1",
			'permalink'=>'admin',
			'myportal_page_id'=>"10",
			'private_page_id'=>"11",
			'avatar'=>NULL,
			'activate_key'=>NULL,
			'lang'=>'ja',
			'timezone_offset'=>"9",
			'email'=>'',
			'mobile_email'=>'',
			'password_regist'=>NULL ,
			'last_login'=>'2013-09-27 00:42:44',
			'previous_login'=>'2013-07-22 08:20:15',
			'created'=>'2013-10-31 04:09:21',
			'created_user_id'=>"1",
			'created_user_name'=>'admin',
			'modified'=>'2013-10-31 09:52:46',
			'modified_user_id'=>'0',
			'modified_user_name'=>''
		);
		$ans = $array['User']['myportal_page_id'];
		$ck = $this->Page->_format_findViewable_currentMyPortal($array);
		$this->assertEqual($ans , $ck);

		$ck = $this->Page->_format_findViewable_currentMyPortal($array['User']);
		$this->assertEqual(null , $ck);
	}

	public function test_format_findViewable_currentPrivate() {
		$array = array();
		$ck = $this->Page->_format_findViewable_currentPrivate(array());
		$this->assertEqual($ck , null);

		$ck = $this->Page->_format_findViewable_currentPrivate('AAAAAAAA');
		$this->assertEqual($ck , null);

		$ck = $this->Page->_format_findViewable_currentPrivate('');
		$this->assertEqual($ck , null);

		$ck = $this->Page->_format_findViewable_currentPrivate(1);
		$this->assertEqual($ck , null);

		$array['User'] = array(
			'id'=>"2",
			'login_id'=>'admin_2' ,
			'password'=>'' ,
			'handle'=>'admin',
			'authority_id'=>"1",
			'is_active'=>"1",
			'permalink'=>'admin',
			'myportal_page_id'=>"10",
			'private_page_id'=>"11",
			'avatar'=>NULL,
			'activate_key'=>NULL,
			'lang'=>'ja',
			'timezone_offset'=>"9",
			'email'=>'',
			'mobile_email'=>'',
			'password_regist'=>NULL ,
			'last_login'=>'2013-09-27 00:42:44',
			'previous_login'=>'2013-07-22 08:20:15',
			'created'=>'2013-10-31 04:09:21',
			'created_user_id'=>"1",
			'created_user_name'=>'admin',
			'modified'=>'2013-10-31 09:52:46',
			'modified_user_id'=>'0',
			'modified_user_name'=>''
		);
		$ans = $array['User']['private_page_id'];
		$ck = $this->Page->_format_findViewable_currentPrivate($array);
		$this->assertEqual($ans , $ck);

		$ck = $this->Page->_format_findViewable_currentPrivate($array['User']);
		$this->assertEqual(null , $ck);

	}

	public function test_format_findViewable_conditions() {
		//言語取得
		$lang = Configure::read(NC_CONFIG_KEY.'.'.'language');

		//conditionsのベース
		$base = array(
			'Page.position_flag' => _ON,
			'Page.display_flag !=' => NC_DISPLAY_FLAG_DISABLE,
			'Page.thread_num !=' => 0,
		);

		$ck = $this->Page->_format_findViewable_conditions(array());
		$this->assertEqual($ck , $base);

		$options = array();
		$options['autoLang'] = true;
		$ans = $base;
		$ans['Page.lang'] = array('', $lang);
		$ck = $this->Page->_format_findViewable_conditions($options);
		$this->assertEqual($ck , $ans);

		$options = array();
		$options['isRoom'] = true;
		$ans = $base;
		$ans[] = "`Page`.`id`=`Page`.`room_id`";
		$ck = $this->Page->_format_findViewable_conditions($options);
		$this->assertEqual($ck , $ans);

		$options = array();
		$options['isRoom'] = true;
		$options['autoLang'] = true;
		$ans = $base;
		$ans['Page.lang'] = array('', $lang);
		$ans[] = "`Page`.`id`=`Page`.`room_id`";
		$ck = $this->Page->_format_findViewable_conditions($options);
		$this->assertEqual($ck , $ans);
	}

	function test_format_findViewable_currents_by_centerPage() {
		$loginUser  = array(
			'id'=>"1",
			'login_id'=>'admin' ,
			'password'=>'hogehoge' ,
			'handle'=>'admin',
			'authority_id'=>"1",
			'is_active'=>"1",
			'permalink'=>'admin',
			'myportal_page_id'=>"1",
			'private_page_id'=>"2",
			'avatar'=>NULL,
			'activate_key'=>NULL,
			'lang'=>'ja',
			'timezone_offset'=>"9",
			'email'=>'',
			'mobile_email'=>'',
			'password_regist'=>NULL ,
			'last_login'=>'2013-09-27 00:42:44',
			'previous_login'=>'2013-07-22 08:20:15',
			'created'=>'2013-10-31 04:09:21',
			'created_user_id'=>"1",
			'created_user_name'=>'admin',
			'modified'=>'2013-10-31 09:52:46',
			'modified_user_id'=>'1',
			'modified_user_name'=>''
		);
		$centerPage['Page'] = array(
			'id'=> 1,
			'root_id'=>1 ,
			'parent_id'=> 0 ,
			'thread_num'=> 0 ,
			'display_sequence'=> 1 ,
			'page_name'=> 'Public room',
			'permalink'=> '',
			'position_flag'=> 1 ,
			'lang'=> '',
			'is_page_meta_node'=> 0,
			'is_page_style_node'=> 0,
			'is_page_layout_node'=> 0,
			'is_page_theme_node'=> 0,
			'is_page_column_node'=> 0,
			'room_id'=> 0,
			'space_type'=> 1,
			'show_count'=> 0,
			'display_flag'=> 1,
			'display_from_date'=> NULL,
			'display_to_date'=> NULL,
			'display_apply_subpage'=> 1,
			'display_reverse_permalink'=> NULL,
			'is_approved'=> 1,
			'lock_authority_id'=> 0,
			'created'=> NULL,
			'created_user_id'=> 0,
			'created_user_name'=> '',
			'modified'=> NULL,
			'modified_user_id'=> 0,
			'modified_user_name'=>''
		);

		$ans = array(1 , 2);
		$ck = $this->Page->_format_findViewable_currents_by_centerPage($centerPage , $loginUser);
		$this->assertEqual($ans , $ck);

		$loginUser  = array(
			'id'=>1,
			'login_id'=>'admin' ,
			'password'=>'hogehoge' ,
			'handle'=>'admin',
			'authority_id'=>"1",
			'is_active'=>"1",
			'permalink'=>'admin',
			'myportal_page_id'=>50,
			'private_page_id'=>2,
			'avatar'=>NULL,
			'activate_key'=>NULL,
			'lang'=>'ja',
			'timezone_offset'=>"9",
			'email'=>'',
			'mobile_email'=>'',
			'password_regist'=>NULL ,
			'last_login'=>'2013-09-27 00:42:44',
			'previous_login'=>'2013-07-22 08:20:15',
			'created'=>'2013-10-31 04:09:21',
			'created_user_id'=>"1",
			'created_user_name'=>'admin',
			'modified'=>'2013-10-31 09:52:46',
			'modified_user_id'=>'1',
			'modified_user_name'=>''
		);
		$centerPage['Page'] = array(
			'id'=> 2,
			'root_id'=>1 ,
			'parent_id'=> 1 ,
			'thread_num'=> 0 ,
			'display_sequence'=> 1 ,
			'page_name'=> 'Public room',
			'permalink'=> '/2/',
			'position_flag'=> 1 ,
			'lang'=> '',
			'is_page_meta_node'=> 0,
			'is_page_style_node'=> 0,
			'is_page_layout_node'=> 0,
			'is_page_theme_node'=> 0,
			'is_page_column_node'=> 0,
			'room_id'=> 100,
			'space_type'=> NC_SPACE_TYPE_MYPORTAL,
			'show_count'=> 0,
			'display_flag'=> 1,
			'display_from_date'=> NULL,
			'display_to_date'=> NULL,
			'display_apply_subpage'=> 1,
			'display_reverse_permalink'=> NULL,
			'is_approved'=> 1,
			'lock_authority_id'=> 0,
			'created'=> NULL,
			'created_user_id'=> 1,
			'created_user_name'=> 'admin',
			'modified'=> NULL,
			'modified_user_id'=> 0,
			'modified_user_name'=>''
		);
		Configure::write(NC_SYSTEM_KEY.'.user' , $loginUser);
		$ans = array(100, 2);
		$ck = $this->Page->_format_findViewable_currents_by_centerPage($centerPage , $loginUser);
		$this->assertEqual($ans , $ck);

		//例外系
		$centerPage = null;
		$loginUser  = null;
		$ans = array(null , null);
		$ck = $this->Page->_format_findViewable_currents_by_centerPage($centerPage , $loginUser);
		$this->assertEqual($ans , $ck);

		$centerPage = array();
		$loginUser  = array();
		$ans = array(null , null);
		$ck = $this->Page->_format_findViewable_currents_by_centerPage($centerPage , $loginUser);
		$this->assertEqual($ans , $ck);

		$centerPage = array(1,2,3,4,5);
		$loginUser  = array();
		$ans = array(null , null);
		$ck = $this->Page->_format_findViewable_currents_by_centerPage($centerPage , $loginUser);
		$this->assertEqual($ans , $ck);

		$loginUser  = array(
			'id'=>1,
			'login_id'=>'admin' ,
			'password'=>'hogehoge' ,
			'handle'=>'admin',
			'authority_id'=>"1",
			'is_active'=>"1",
			'permalink'=>'admin',
			'myportal_page_id'=>50,
			'private_page_id'=>2,
			'avatar'=>NULL,
			'activate_key'=>NULL,
			'lang'=>'ja',
			'timezone_offset'=>"9",
			'email'=>'',
			'mobile_email'=>'',
			'password_regist'=>NULL ,
			'last_login'=>'2013-09-27 00:42:44',
			'previous_login'=>'2013-07-22 08:20:15',
			'created'=>'2013-10-31 04:09:21',
			'created_user_id'=>"1",
			'created_user_name'=>'admin',
			'modified'=>'2013-10-31 09:52:46',
			'modified_user_id'=>'1',
			'modified_user_name'=>''
		);
		$centerPage['Page'] = array();

		$ans = array(50 , 2);
		$ck = $this->Page->_format_findViewable_currents_by_centerPage($centerPage , $loginUser);
		$this->assertEqual($ans , $ck);

		$loginUser = array();
		$centerPage['Page'] = array();

		$ans = array(null , null);
		$ck = $this->Page->_format_findViewable_currents_by_centerPage($centerPage , $loginUser);
		$this->assertEqual($ans , $ck);

		//room_idが戻るパターン
		//$currentUser = $User->currentUser($centerPage, $loginUser);
		//$currentUserが戻ってくるタイプ。
		$loginUser  = array(
			'id'=>1,
			'login_id'=>'admin' ,
			'password'=>'hogehoge' ,
			'handle'=>'admin',
			'authority_id'=>"1",
			'is_active'=>"1",
			'permalink'=>'2',
			'myportal_page_id'=>50,
			'private_page_id'=>2,
			'avatar'=>NULL,
			'activate_key'=>NULL,
			'lang'=>'ja',
			'timezone_offset'=>"9",
			'email'=>'',
			'mobile_email'=>'',
			'password_regist'=>NULL ,
			'last_login'=>'2013-09-27 00:42:44',
			'previous_login'=>'2013-07-22 08:20:15',
			'created'=>'2013-10-31 04:09:21',
			'created_user_id'=>"1",
			'created_user_name'=>'admin',
			'modified'=>'2013-10-31 09:52:46',
			'modified_user_id'=>'1',
			'modified_user_name'=>''
		);
		$centerPage['Page'] = array(
			'id'=> 1,
			'root_id'=>1 ,
			'parent_id'=> 1 ,
			'thread_num'=> 0 ,
			'display_sequence'=> 1 ,
			'page_name'=> 'Public room',
			'permalink'=> '/7/',
			'position_flag'=> 1 ,
			'lang'=> '',
			'is_page_meta_node'=> 0,
			'is_page_style_node'=> 0,
			'is_page_layout_node'=> 0,
			'is_page_theme_node'=> 0,
			'is_page_column_node'=> 0,
			'room_id'=> 100,
			'space_type'=> NC_SPACE_TYPE_MYPORTAL,
			'show_count'=> 0,
			'display_flag'=> 1,
			'display_from_date'=> NULL,
			'display_to_date'=> NULL,
			'display_apply_subpage'=> 1,
			'display_reverse_permalink'=> NULL,
			'is_approved'=> 1,
			'lock_authority_id'=> 0,
			'created'=> NULL,
			'created_user_id'=> 1,
			'created_user_name'=> 'admin',
			'modified'=> NULL,
			'modified_user_id'=> 0,
			'modified_user_name'=>''
		);

		$ans = array(100, 2);
		$ck = $this->Page->_format_findViewable_currents_by_centerPage($centerPage , $loginUser);
		//var_export($ck);
		$this->assertEqual($ans , $ck);

	}

/**
 * testFindIncludeComunityLang method
 *
 * @return void
 */
	public function testFindIncludeComunityLang() {

		$pageId = 1;
		$ans = array('Page' =>
			array (
			  'id' => '1',
			  'root_id' => '0',
			  'parent_id' => '0',
			  'thread_num' => '0',
			  'display_sequence' => '1',
			  'page_name' => 'Public room',
			  'permalink' => '',
			  'position_flag' => '1',
			  'lang' => '',
			  'is_page_meta_node' => '0',
			  'is_page_style_node' => '0',
			  'is_page_layout_node' => '0',
			  'is_page_theme_node' => '0',
			  'is_page_column_node' => '0',
			  'room_id' => '0',
			  'space_type' => '1',
			  'show_count' => '0',
			  'display_flag' => '1',
			  'display_from_date' => NULL,
			  'display_to_date' => NULL,
			  'display_apply_subpage' => '1',
			  'display_reverse_permalink' => NULL,
			  'is_approved' => '1',
			  'lock_authority_id' => '0',
			  'created' => NULL,
			  'created_user_id' => '0',
			  'created_user_name' => '',
			  'modified' => NULL,
			  'modified_user_id' => '0',
			  'modified_user_name' => '',
		  ),
	  'CommunityLang' =>
		  array (
			  'community_name' => NULL,
		  )
		);
		$ck = $this->Page->findIncludeComunityLang($pageId);
		//var_export($ck);
		$this->assertEqual($ck , $ans);

		//$lang = Configure::read(NC_CONFIG_KEY.'.'.'language');
		//var_dump($lang);
		$pageId = 16;
		$ck = $this->Page->findIncludeComunityLang($pageId);
		$ans = array (
			'Page' =>
			array (
				'id' => '16',
				'root_id' => '16',
				'parent_id' => '4',
				'thread_num' => '1',
				'display_sequence' => '1',
				'page_name' => 'Community-1',
				'permalink' => 'community-1',
				'position_flag' => '1',
				'lang' => '',
				'is_page_meta_node' => '0',
				'is_page_style_node' => '0',
				'is_page_layout_node' => '0',
				'is_page_theme_node' => '0',
				'is_page_column_node' => '0',
				'room_id' => '16',
				'space_type' => '4',
				'show_count' => '0',
				'display_flag' => '1',
				'display_from_date' => NULL,
				'display_to_date' => NULL,
				'display_apply_subpage' => '1',
				'display_reverse_permalink' => NULL,
				'is_approved' => '1',
				'lock_authority_id' => '0',
				'created' => '2013-06-24 06:59:37',
				'created_user_id' => '1',
				'created_user_name' => 'admin',
				'modified' => '2013-06-24 06:59:37',
				'modified_user_id' => '1',
				'modified_user_name' => 'admin',
			),
			'CommunityLang' =>
			array (
				'community_name' => 'UnitTest-A',
			),
		);
		$this->assertEqual($ck , $ans);

		//存在しない場合
		$pageId = 99999999999;
		$ck = $this->Page->findIncludeComunityLang($pageId);
		$this->assertEqual($ck , array());

		//パラメータのフォーマットエラーの場合。
		$pageId = 'AAAAAA';
		$ck = $this->Page->findIncludeComunityLang($pageId);
		$this->assertEqual($ck , array());
	}

/**
 * testFindChilds method
 *
 * @return void
 */
	public function testFindChilds() {

		//第一引数はまったく使われていない。
		//とりあえず現状の動きをトレース。

		$page['Page'] = array(
			'id'                => 9,
			'root_id'           => 9 ,
			'parent_id'         => 1 ,
			'thread_num'        => 1 ,
			'display_sequence'  => 0,
			'page_name'         => 'Public room',
			'permalink'         => '',
			'position_flag'     => 1 ,
			'lang'              => '',
			'is_page_meta_node'  => 0,
			'is_page_style_node' => 0,
			'is_page_layout_node'=> 0,
			'is_page_theme_node' => 0,
			'is_page_column_node'=> 0,
			'room_id'           => 9,
			'space_type'        => 1,
			'show_count'        => 0,
			'display_flag'      => 1,
			'display_from_date' => NULL,
			'display_to_date'   => NULL,
			'display_apply_subpage'=> 1,
			'display_reverse_permalink'=> NULL,
			'is_approved'       => 1,
			'lock_authority_id' => 0,
			'created'           => NULL,
			'created_user_id'   => 0,
			'created_user_name' => '',
			'modified'          => NULL,
			'modified_user_id'  => 1,
			'modified_user_name'=>''
		);
		$ck = $this->Page->findChilds('all' , $page);
		$ans = array (
			0 =>
			array (
				'Page' =>
				array (
					'id' => '12',
					'root_id' => '9',
					'parent_id' => '9',
					'thread_num' => '2',
					'display_sequence' => '1',
					'page_name' => '??????',
					'permalink' => '',
					'position_flag' => '1',
					'lang' => 'ja',
					'is_page_meta_node' => '0',
					'is_page_style_node' => '0',
					'is_page_layout_node' => '0',
					'is_page_theme_node' => '0',
					'is_page_column_node' => '0',
					'room_id' => '9',
					'space_type' => '1',
					'show_count' => '110',
					'display_flag' => '1',
					'display_from_date' => NULL,
					'display_to_date' => NULL,
					'display_apply_subpage' => '1',
					'display_reverse_permalink' => NULL,
					'is_approved' => '1',
					'lock_authority_id' => '0',
					'created' => NULL,
					'created_user_id' => '0',
					'created_user_name' => '',
					'modified' => '2013-07-12 07:13:42',
					'modified_user_id' => '1',
					'modified_user_name' => 'admin',
				),
				'PageUserLink' =>
				array (
					'authority_id' => NULL,
				),
				'PageAuthority' =>
				array (
					'id' => NULL,
					'myportal_use_flag' => NULL,
					'private_use_flag' => NULL,
					'hierarchy' => NULL,
				),
			),
		);
		// id:12が1件取得できる
		$this->assertEqual($ck , $ans);

		//空データ。$currentPage['Page']['lang']が存在しないためnotice errorでテストが実行できない。
		//TODO:パラメータのフォーマット異常時の対応を行う。
		//$page['Page'] = array();
		//$ck = $this->Page->findChilds('all' , $page);

		//子孫のいないデータで、子孫を探す。array()が戻る。
		$page = array();
		$page['Page'] = array(
			'id'                => 4,
			'root_id'           => 0 ,
			'parent_id'         => 0 ,
			'thread_num'        => 0 ,
			'display_sequence'  => 4,
			'page_name'         => 'Community',
			'permalink'         => '',
			'position_flag'     => 1 ,
			'lang'              => '',
			'is_page_meta_node'  => 0,
			'is_page_style_node' => 0,
			'is_page_layout_node'=> 0,
			'is_page_theme_node' => 0,
			'is_page_column_node'=> 0,
			'room_id'           => 0,
			'space_type'        => 4,
			'show_count'        => 0,
			'display_flag'      => 1,
			'display_from_date' => NULL,
			'display_to_date'   => NULL,
			'display_apply_subpage'=> 1,
			'display_reverse_permalink'=> NULL,
			'is_approved'       => 1,
			'lock_authority_id' => 0,
			'created'           => NULL,
			'created_user_id'   => 0,
			'created_user_name' => '',
			'modified'          => NULL,
			'modified_user_id'  => 0,
			'modified_user_name'=>''
		);
		$ck = $this->Page->findChilds('all' , $page);
		$this->assertEqual($ck , array());

		$ck = $this->Page->findChilds('all' , $page , null , 1);
		$this->assertEqual($ck , array());

		$ck = $this->Page->findChilds('all' , $page , null , 2);
		$this->assertEqual($ck , array());

		//id:14 , 15の2件分が戻る。
		$page = array();
		$page['Page'] = array(
			'id'                => 11,
			'root_id'           => 11 ,
			'parent_id'         => 3 ,
			'thread_num'        => 1 ,
			'display_sequence'  => 0,
			'page_name'         => 'Private room',
			'permalink'         => 'Admin',
			'position_flag'     => 1 ,
			'lang'              => '',
			'is_page_meta_node'  => 0,
			'is_page_style_node' => 0,
			'is_page_layout_node'=> 0,
			'is_page_theme_node' => 0,
			'is_page_column_node'=> 0,
			'room_id'           => 11,
			'space_type'        => 3,
			'show_count'        => 0,
			'display_flag'      => 1,
			'display_from_date' => NULL,
			'display_to_date'   => NULL,
			'display_apply_subpage'=> 1,
			'display_reverse_permalink'=> NULL,
			'is_approved'       => 1,
			'lock_authority_id' => 0,
			'created'           => NULL,
			'created_user_id'   => 0,
			'created_user_name' => '',
			'modified'          => NULL,
			'modified_user_id'  => 1,
			'modified_user_name'=>''
		);
		$ck = $this->Page->findChilds('all' , $page);
		$this->assertEqual(2 , count($ck));
		//display_flagが変更されるデータのため、
		//順番もかわるため、Page.idから想定される情報が取得できているかどうかを判定するようにした。
		//Page.id 15が１４の値が戻る。
		$this->assertEqual(true , ($ck[0]['Page']['id'] ==15 || $ck[0]['Page']['id'] ==14 ));
		$this->assertEqual(true , ($ck[1]['Page']['id'] ==15 || $ck[1]['Page']['id'] ==14 ));

		//
		$ck = $this->Page->findChilds('all' , $page , null , 4);
		$this->assertEqual(true , ($ck[0]['Page']['id'] ==15 || $ck[0]['Page']['id'] ==14 ));
		$this->assertEqual(true , ($ck[1]['Page']['id'] ==15 || $ck[1]['Page']['id'] ==14 ));

		//言語を指定
		$ck = $this->Page->findChilds('all' , $page , 'eng' , 4);
		$this->assertEqual($ck , array());

		$ck = $this->Page->findChilds('all' , $page , 'ja' , 4);
		$this->assertEqual(true , ($ck[0]['Page']['id'] ==15 || $ck[0]['Page']['id'] ==14 ));
		$this->assertEqual(true , ($ck[1]['Page']['id'] ==15 || $ck[1]['Page']['id'] ==14 ));

		$ck = $this->Page->findChilds('all' , $page , 'ja' , 1);
		$this->assertEqual(true , ($ck[0]['Page']['id'] ==15 || $ck[0]['Page']['id'] ==14 ));
		$this->assertEqual(true , ($ck[1]['Page']['id'] ==15 || $ck[1]['Page']['id'] ==14 ));

		//$ck = $this->Page->findChilds('all' , $page , 'ja' , 7);
		//var_export($ck);

		//2階層目を保存してみる
		$new_page['Page'] = array(
			'root_id'           => 11 ,
			'parent_id'         => 15 ,
			'thread_num'        => 2 ,
			'display_sequence'  => 3,
			'page_name'         => 'Community',
			'permalink'         => '',
			'position_flag'     => 1 ,
			'lang'              => '',
			'is_page_meta_node'  => 0,
			'is_page_style_node' => 0,
			'is_page_layout_node'=> 0,
			'is_page_theme_node' => 0,
			'is_page_column_node'=> 0,
			'room_id'           => 0,
			'space_type'        => 4,
			'show_count'        => 0,
			'display_flag'      => 1,
			'display_from_date' => NULL,
			'display_to_date'   => NULL,
			'display_apply_subpage'=> 1,
			'display_reverse_permalink'=> NULL,
			'is_approved'       => 1,
			'lock_authority_id' => 0,
		);

		//2階層目を保存してみる
		$new_page_2['Page'] = array(
			'root_id'           => 11 ,
			'parent_id'         => 17 ,
			'thread_num'        => 3 ,
			'display_sequence'  => 3,
			'page_name'         => 'Community',
			'permalink'         => '',
			'position_flag'     => 1 ,
			'lang'              => '',
			'is_page_meta_node'  => 0,
			'is_page_style_node' => 0,
			'is_page_layout_node'=> 0,
			'is_page_theme_node' => 0,
			'is_page_column_node'=> 0,
			'room_id'           => 0,
			'space_type'        => 4,
			'show_count'        => 0,
			'display_flag'      => 1,
			'display_from_date' => NULL,
			'display_to_date'   => NULL,
			'display_apply_subpage'=> 1,
			'display_reverse_permalink'=> NULL,
			'is_approved'       => 1,
			'lock_authority_id' => 0,
		);


		$User = ClassRegistry::init("User");
		$user = $User->find("first" , array('conditions'=>array('User.id'=>1),) );
		Configure::write(NC_SYSTEM_KEY.'.user' , $user['User']);

		//id:17 , 18で11の子孫情報を保存
		$this->Page->create();
		$ck = $this->Page->save($new_page , false , false);
		$this->Page->create();
		$ck = $this->Page->save($new_page_2 , false , false);

		//id 14,15,17,18が取得できていないといけないがとれない。
		//TODO：ちゃんと子孫情報がとれるように修正する
		//$ck = $this->Page->findChilds('all' , $page);
		//var_export($ck);

	}

/**
 * testFindCommunityCount method
 *
 * @return void
 */
	public function testFindCommunityCount() {

		//存在するアカウントの場合
		$ck = $this->Page->findCommunityCount(1);
		$this->assertEqual(1 , $ck);

		//存在するアカウントの場合
		//なぜ2はPage.id:2のデータを見れない？
		$ck = $this->Page->findCommunityCount(2);
		$this->assertEqual(0 , $ck);

		//存在しないアカウントの場合
		$ck = $this->Page->findCommunityCount(99999999999);
		$this->assertEqual(0 , $ck);

		$ck = $this->Page->findCommunityCount('all');
		$this->assertEqual(1 , $ck);

		//ログインユーザ
		$User = ClassRegistry::init('User');
		$loginUser = $User->find("first" , array('conditions'=>array('User.id'=>1)) );
		Configure::write(NC_SYSTEM_KEY.'.user' , $loginUser['User']);

		$ck = $this->Page->findCommunityCount();
		$this->assertEqual(1 , $ck);

		$loginUser = $User->find("first" , array('conditions'=>array('User.id'=>2)) );
		Configure::write(NC_SYSTEM_KEY.'.user' , $loginUser['User']);

		$ck = $this->Page->findCommunityCount();
		$this->assertEqual(0 , $ck);

		//未ログイン noticeエラー
		//TODO:未ログイン時の考慮と対応
		//Configure::write(NC_SYSTEM_KEY.'.user' , array());
		//$ck = $this->Page->findCommunityCount();
		//$this->assertEqual(0 , $ck);

		//第2引数$params , 第3引数$optionsはself::findViewable()へ条件を渡しているだけなので一旦省略
		//array_mergeで条件を上書きしている。
		// Page.space_typeやPage.thread_numを上書きされた場合についても今回は一旦省略

	}

/**
 * testGetFieldsArray method
 *
 * @return void
 */
	public function testGetFieldsArray() {

		//$userIdがしていされていて、$spaceTypeが指定されていない場合
		$ck = $this->Page->getFieldsArray(1);
		$ans = array (
			0 => 'Page.*',
			1 => 'PageUserLink.authority_id',
			2 => 'PageAuthority.id',
			3 => 'PageAuthority.myportal_use_flag',
			4 => 'PageAuthority.private_use_flag',
			5 => 'PageAuthority.hierarchy',
			6 => 'Community.publication_range_flag',
			7 => 'Community.participate_force_all_users',
			8 => 'Community.participate_flag',
			9 => 'Community.is_upload',
			10 => 'Community.photo',
			11 => 'CommunityLang.community_name',
			12 => 'CommunityLang.summary',
		);
		$this->assertEqual($ck , $ans);

		//$userIdが空、$spaceTypeが指定されていない場合
		$ck = $this->Page->getFieldsArray('');
		$ans = array (
			0 => 'Page.*',
			1 => 'Community.publication_range_flag',
			2 => 'Community.participate_force_all_users',
			3 => 'Community.participate_flag',
			4 => 'Community.is_upload',
			5 => 'Community.photo',
			6 => 'CommunityLang.community_name',
			7 => 'CommunityLang.summary',
		);
		$this->assertEqual($ck , $ans);

		//$userIdが指定されていて、$spaceTypeに arrayでNC_SPACE_TYPE_GROUPが指定されている状態
		$ck = $this->Page->getFieldsArray(1 , array(NC_SPACE_TYPE_GROUP));
		$ans = array (
			0 => 'Page.*',
			1 => 'PageUserLink.authority_id',
			2 => 'PageAuthority.id',
			3 => 'PageAuthority.myportal_use_flag',
			4 => 'PageAuthority.private_use_flag',
			5 => 'PageAuthority.hierarchy',
			6 => 'Community.publication_range_flag',
			7 => 'Community.participate_force_all_users',
			8 => 'Community.participate_flag',
			9 => 'Community.is_upload',
			10 => 'Community.photo',
			11 => 'CommunityLang.community_name',
			12 => 'CommunityLang.summary',
		);
		$this->assertEqual($ck , $ans);

		//$userIdが指定されていて、$spaceTypeに NC_SPACE_TYPE_GROUPが指定されている状態
		$ck = $this->Page->getFieldsArray(1 , NC_SPACE_TYPE_GROUP);
		$ans = array (
			0 => 'Page.*',
			1 => 'PageUserLink.authority_id',
			2 => 'PageAuthority.id',
			3 => 'PageAuthority.myportal_use_flag',
			4 => 'PageAuthority.private_use_flag',
			5 => 'PageAuthority.hierarchy',
			6 => 'Community.publication_range_flag',
			7 => 'Community.participate_force_all_users',
			8 => 'Community.participate_flag',
			9 => 'Community.is_upload',
			10 => 'Community.photo',
			11 => 'CommunityLang.community_name',
			12 => 'CommunityLang.summary',
		);
		$this->assertEqual($ck , $ans);

		//$userIdが指定されていて、$spaceTypeに文字列が指定された場合（例外）
		$ck = $this->Page->getFieldsArray(1 , 'AAAAA');
		$ans = array (
			0 => 'Page.*',
			1 => 'PageUserLink.authority_id',
			2 => 'PageAuthority.id',
			3 => 'PageAuthority.myportal_use_flag',
			4 => 'PageAuthority.private_use_flag',
			5 => 'PageAuthority.hierarchy',
		);
		$this->assertEqual($ck , $ans);

		//$userIdが指定されておらず、$spaceTypeに NC_SPACE_TYPE_GROUPが指定されている状態
		$ck = $this->Page->getFieldsArray(null , NC_SPACE_TYPE_GROUP);
		$ans = array (
			0 => 'Page.*',
			1 => 'Community.publication_range_flag',
			2 => 'Community.participate_force_all_users',
			3 => 'Community.participate_flag',
			4 => 'Community.is_upload',
			5 => 'Community.photo',
			6 => 'CommunityLang.community_name',
			7 => 'CommunityLang.summary',
		);
		$this->assertEqual($ck , $ans);
		//$userIdが指定されておらず、$spaceTypeに arrayでNC_SPACE_TYPE_GROUPが指定されている状態
		$ck = $this->Page->getFieldsArray(null , array(NC_SPACE_TYPE_GROUP));
		$ans = array (
			0 => 'Page.*',
			1 => 'Community.publication_range_flag',
			2 => 'Community.participate_force_all_users',
			3 => 'Community.participate_flag',
			4 => 'Community.is_upload',
			5 => 'Community.photo',
			6 => 'CommunityLang.community_name',
			7 => 'CommunityLang.summary',
		);
		$this->assertEqual($ck , $ans);
	}

/**
 * testGetJoinsArray method
 *
 * @return void
 */
	public function testGetJoinsArray() {
		//$userIdを指定、$type , $spaceType未指定
		$ck = $this->Page->getJoinsArray(1);
		$ans = array (
			0 =>
			array (
				'type' => 'LEFT',
				'table' => 'page_user_links',
				'alias' => 'PageUserLink',
				'conditions' => '`Page`.`room_id`=`PageUserLink`.`room_id` AND `PageUserLink`.`user_id` =1',
			),
			1 =>
			array (
				'type' => 'LEFT',
				'table' => 'authorities',
				'alias' => 'PageAuthority',
				'conditions' => '`PageAuthority`.`id`=`PageUserLink`.`authority_id`',
			),
			2 =>
			array (
				'type' => 'LEFT',
				'table' => 'communities',
				'alias' => 'Community',
				'conditions' => '`Page`.`root_id`=`Community`.`room_id`',
			),
			3 =>
			array (
				'type' => 'LEFT',
				'table' => 'community_langs',
				'alias' => 'CommunityLang',
				'conditions' => '`Page`.`root_id`=`CommunityLang`.`room_id` AND `CommunityLang`.`lang` =\'eng\'',
			)
		);
		$this->assertEqual($ck , $ans);

		//$userIdがブランクの場合
		$ck = $this->Page->getJoinsArray('');
		$ans = array (
			0 =>
			array (
				'type' => 'LEFT',
				'table' => 'page_user_links',
				'alias' => 'PageUserLink',
				'conditions' => '`Page`.`room_id`=`PageUserLink`.`room_id` AND `PageUserLink`.`user_id` =0',
			),
			1 =>
			array (
				'type' => 'LEFT',
				'table' => 'authorities',
				'alias' => 'PageAuthority',
				'conditions' => '`PageAuthority`.`id`=`PageUserLink`.`authority_id`',
			),
			2 =>
			array (
				'type' => 'LEFT',
				'table' => 'communities',
				'alias' => 'Community',
				'conditions' => '`Page`.`root_id`=`Community`.`room_id`',
			),
			3 =>
			array (
				'type' => 'LEFT',
				'table' => 'community_langs',
				'alias' => 'CommunityLang',
				'conditions' => '`Page`.`root_id`=`CommunityLang`.`room_id` AND `CommunityLang`.`lang` =\'eng\'',
			),
		);
		$this->assertEqual($ck , $ans);

		//$userIdが文字列の場合（例外）
		$ck  = $this->Page->getJoinsArray('AAAAA');
		$ans = array (
			0 =>
			array (
				'type' => 'LEFT',
				'table' => 'page_user_links',
				'alias' => 'PageUserLink',
				'conditions' => '`Page`.`room_id`=`PageUserLink`.`room_id` AND `PageUserLink`.`user_id` =0',
			),
			1 =>
			array (
				'type' => 'LEFT',
				'table' => 'authorities',
				'alias' => 'PageAuthority',
				'conditions' => '`PageAuthority`.`id`=`PageUserLink`.`authority_id`',
			),
			2 =>
			array (
				'type' => 'LEFT',
				'table' => 'communities',
				'alias' => 'Community',
				'conditions' => '`Page`.`root_id`=`Community`.`room_id`',
			),
			3 =>
			array (
				'type' => 'LEFT',
				'table' => 'community_langs',
				'alias' => 'CommunityLang',
				'conditions' => '`Page`.`root_id`=`CommunityLang`.`room_id` AND `CommunityLang`.`lang` =\'eng\'',
			),
		);
		$this->assertEqual($ck , $ans);

		//$userIdが指定されていて、$typeが RIGHT
		$ck  = $this->Page->getJoinsArray(1 , 'RIGHT');
		$ans = array (
			0 =>
			array (
				'type' => 'LEFT',
				'table' => 'page_user_links',
				'alias' => 'PageUserLink',
				'conditions' => '`Page`.`room_id`=`PageUserLink`.`room_id` AND `PageUserLink`.`user_id` =1',
			),
			1 =>
			array (
				'type' => 'LEFT',
				'table' => 'authorities',
				'alias' => 'PageAuthority',
				'conditions' => '`PageAuthority`.`id`=`PageUserLink`.`authority_id`',
			),
			2 =>
			array (
				'type' => 'LEFT',
				'table' => 'communities',
				'alias' => 'Community',
				'conditions' => '`Page`.`root_id`=`Community`.`room_id`',
			),
			3 =>
			array (
				'type' => 'LEFT',
				'table' => 'community_langs',
				'alias' => 'CommunityLang',
				'conditions' => '`Page`.`root_id`=`CommunityLang`.`room_id` AND `CommunityLang`.`lang` =\'eng\'',
			),
		);
		$this->assertEqual($ck , $ans);

		//$userIdが指定されていて、$typeが RIGHT
		$ck  = $this->Page->getJoinsArray(1 , 'INNER');
		$ans = array (
			0 =>
			array (
				'type' => 'INNER',
				'table' => 'page_user_links',
				'alias' => 'PageUserLink',
				'conditions' => '`Page`.`room_id`=`PageUserLink`.`room_id` AND `PageUserLink`.`user_id` =1',
			),
			1 =>
			array (
				'type' => 'LEFT',
				'table' => 'authorities',
				'alias' => 'PageAuthority',
				'conditions' => '`PageAuthority`.`id`=`PageUserLink`.`authority_id`',
			),
			2 =>
			array (
				'type' => 'LEFT',
				'table' => 'communities',
				'alias' => 'Community',
				'conditions' => '`Page`.`root_id`=`Community`.`room_id`',
			),
			3 =>
			array (
				'type' => 'LEFT',
				'table' => 'community_langs',
				'alias' => 'CommunityLang',
				'conditions' => '`Page`.`root_id`=`CommunityLang`.`room_id` AND `CommunityLang`.`lang` =\'eng\'',
			),
		);
		$this->assertEqual($ck , $ans);

		//$userIdがしていされていて、$type無指定　$spaceTypeを NC_SPACE_TYPE_GROUPが指定されている場合
		$ck  = $this->Page->getJoinsArray(1 , null , NC_SPACE_TYPE_GROUP);
		$ans = array (
			0 =>
			array (
				'type' => 'LEFT',
				'table' => 'page_user_links',
				'alias' => 'PageUserLink',
				'conditions' => '`Page`.`room_id`=`PageUserLink`.`room_id` AND `PageUserLink`.`user_id` =1',
			),
			1 =>
			array (
				'type' => 'LEFT',
				'table' => 'authorities',
				'alias' => 'PageAuthority',
				'conditions' => '`PageAuthority`.`id`=`PageUserLink`.`authority_id`',
			),
			2 =>
			array (
				'type' => 'LEFT',
				'table' => 'communities',
				'alias' => 'Community',
				'conditions' => '`Page`.`root_id`=`Community`.`room_id`',
			),
			3 =>
			array (
				'type' => 'LEFT',
				'table' => 'community_langs',
				'alias' => 'CommunityLang',
				'conditions' => '`Page`.`root_id`=`CommunityLang`.`room_id` AND `CommunityLang`.`lang` =\'eng\'',
			),
		);
		$this->assertEqual($ck , $ans);
		//$userIdがしていされていて、$type無指定　$spaceTypeを NC_SPACE_TYPE_GROUP以外が指定されている場合
		$ck = $this->Page->getJoinsArray(1 , null , 'AAAA');
		$ans = array (
			0 =>
			array (
				'type' => 'LEFT',
				'table' => 'page_user_links',
				'alias' => 'PageUserLink',
				'conditions' => '`Page`.`room_id`=`PageUserLink`.`room_id` AND `PageUserLink`.`user_id` =1',
			),
			1 =>
			array (
				'type' => 'LEFT',
				'table' => 'authorities',
				'alias' => 'PageAuthority',
				'conditions' => '`PageAuthority`.`id`=`PageUserLink`.`authority_id`',
			),
		);
		$this->assertEqual($ck , $ans);
	}

/**
 * testAfterFindIds method
 *
 * @return void
 */
	public function testAfterFindIds() {

		//resultがない場合
		$ck = $this->Page->afterFindIds(array() , 1 );
		$this->assertEqual(false , $ck);

		$results = array (
			0 =>
			array (
				'Page' =>
				array (
					'id' => '9',
					'root_id' => '9',
					'parent_id' => '1',
					'thread_num' => '1',
					'display_sequence' => '0',
					'page_name' => 'Public room',
					'permalink' => '',
					'position_flag' => '1',
					'lang' => '',
					'is_page_meta_node' => '0',
					'is_page_style_node' => '0',
					'is_page_layout_node' => '0',
					'is_page_theme_node' => '0',
					'is_page_column_node' => '0',
					'room_id' => '9',
					'space_type' => '1',
					'show_count' => '0',
					'display_flag' => '1',
					'display_from_date' => NULL,
					'display_to_date' => NULL,
					'display_apply_subpage' => '1',
					'display_reverse_permalink' => NULL,
					'is_approved' => '1',
					'lock_authority_id' => '0',
					'created' => NULL,
					'created_user_id' => '0',
					'created_user_name' => '',
					'modified' => NULL,
					'modified_user_id' => '1',
					'modified_user_name' => '',
				),
				'PageUserLink' =>
				array (
					'authority_id' => NULL,
				),
				'PageAuthority' =>
				array (
					'id' => NULL,
					'myportal_use_flag' => NULL,
					'private_use_flag' => NULL,
					'hierarchy' => NULL,
				),
				'Community' =>
				array (
					'publication_range_flag' => NULL,
					'participate_force_all_users' => NULL,
					'participate_flag' => NULL,
					'is_upload' => NULL,
					'photo' => NULL,
				),
				'CommunityLang' =>
				array (
					'community_name' => NULL,
					'summary' => NULL,
				),
			),
			1 =>
			array (
				'Page' =>
				array (
					'id' => '11',
					'root_id' => '11',
					'parent_id' => '3',
					'thread_num' => '1',
					'display_sequence' => '0',
					'page_name' => 'Private room',
					'permalink' => 'Admin',
					'position_flag' => '1',
					'lang' => '',
					'is_page_meta_node' => '0',
					'is_page_style_node' => '0',
					'is_page_layout_node' => '0',
					'is_page_theme_node' => '0',
					'is_page_column_node' => '0',
					'room_id' => '11',
					'space_type' => '3',
					'show_count' => '0',
					'display_flag' => '1',
					'display_from_date' => NULL,
					'display_to_date' => NULL,
					'display_apply_subpage' => '1',
					'display_reverse_permalink' => NULL,
					'is_approved' => '1',
					'lock_authority_id' => '0',
					'created' => NULL,
					'created_user_id' => '0',
					'created_user_name' => '',
					'modified' => NULL,
					'modified_user_id' => '1',
					'modified_user_name' => '',
				),
				'PageUserLink' =>
				array (
					'authority_id' => NULL,
				),
				'PageAuthority' =>
				array (
					'id' => NULL,
					'myportal_use_flag' => NULL,
					'private_use_flag' => NULL,
					'hierarchy' => NULL,
				),
				'Community' =>
				array (
					'publication_range_flag' => NULL,
					'participate_force_all_users' => NULL,
					'participate_flag' => NULL,
					'is_upload' => NULL,
					'photo' => NULL,
				),
				'CommunityLang' =>
				array (
					'community_name' => NULL,
					'summary' => NULL,
				),
			),
		);

		//$type:countにした場合の挙動がおかしい。
		//配列のkeyをidにする機能が実装されている。

		//パラメータエラー countは許可されていない
		$ck = $this->Page->afterFindIds(10 , 'all' , 'count' );
		$this->assertEqual($ck , false);

		//パラメータエラー countは許可されていない...がarrayが戻ってきている。
		//TODO : パラメータエラー系処理の追加
		//$ck = $this->Page->afterFindIds($results , 'all' , 'count' );
		//$this->assertEqual($ck , false);

		$ck = $this->Page->afterFindIds($results , 'all' , 'menu' );
		$ans = array (
			1 =>
			array (
				1 =>
				array (
					1 =>
					array (
						0 =>
						array (
							'Page' =>
							array (
								'id' => '9',
								'root_id' => '9',
								'parent_id' => '1',
								'thread_num' => '1',
								'display_sequence' => '0',
								'page_name' => 'Public room',
								'permalink' => '',
								'position_flag' => '1',
								'lang' => '',
								'is_page_meta_node' => '0',
								'is_page_style_node' => '0',
								'is_page_layout_node' => '0',
								'is_page_theme_node' => '0',
								'is_page_column_node' => '0',
								'room_id' => '9',
								'space_type' => '1',
								'show_count' => '0',
								'display_flag' => '1',
								'display_from_date' => NULL,
								'display_to_date' => NULL,
								'display_apply_subpage' => '1',
								'display_reverse_permalink' => NULL,
								'is_approved' => '1',
								'lock_authority_id' => '0',
								'created' => NULL,
								'created_user_id' => '0',
								'created_user_name' => '',
								'modified' => NULL,
								'modified_user_id' => '1',
								'modified_user_name' => '',
								'visibility_flag' => 1,
							),
							'PageUserLink' =>
							array (
								'authority_id' => NULL,
							),
							'PageAuthority' =>
							array (
								'id' => NULL,
								'myportal_use_flag' => NULL,
								'private_use_flag' => NULL,
								'hierarchy' => NULL,
							),
							'Community' =>
							array (
								'publication_range_flag' => NULL,
								'participate_force_all_users' => NULL,
								'participate_flag' => NULL,
								'is_upload' => NULL,
								'photo' => NULL,
							),
							'CommunityLang' =>
							array (
								'community_name' => NULL,
								'summary' => NULL,
							),
						),
					),
				),
			),
			3 =>
			array (
				1 =>
				array (
					3 =>
					array (
						0 =>
						array (
							'Page' =>
							array (
								'id' => '11',
								'root_id' => '11',
								'parent_id' => '3',
								'thread_num' => '1',
								'display_sequence' => '0',
								'page_name' => 'Private room of admin',
								'permalink' => 'private/Admin/',
								'position_flag' => '1',
								'lang' => '',
								'is_page_meta_node' => '0',
								'is_page_style_node' => '0',
								'is_page_layout_node' => '0',
								'is_page_theme_node' => '0',
								'is_page_column_node' => '0',
								'room_id' => '11',
								'space_type' => '3',
								'show_count' => '0',
								'display_flag' => '1',
								'display_from_date' => NULL,
								'display_to_date' => NULL,
								'display_apply_subpage' => '1',
								'display_reverse_permalink' => NULL,
								'is_approved' => '1',
								'lock_authority_id' => '0',
								'created' => NULL,
								'created_user_id' => '0',
								'created_user_name' => '',
								'modified' => NULL,
								'modified_user_id' => '1',
								'modified_user_name' => '',
								'visibility_flag' => 1,
							),
							'PageUserLink' =>
							array (
								'authority_id' => NULL,
							),
							'PageAuthority' =>
							array (
								'id' => NULL,
								'myportal_use_flag' => NULL,
								'private_use_flag' => NULL,
								'hierarchy' => NULL,
							),
							'Community' =>
							array (
								'publication_range_flag' => NULL,
								'participate_force_all_users' => NULL,
								'participate_flag' => NULL,
								'is_upload' => NULL,
								'photo' => NULL,
							),
							'CommunityLang' =>
							array (
								'community_name' => NULL,
								'summary' => NULL,
							),
						),
					),
				),
			),
		);
		$this->assertEqual($ck , $ans);

		//list でfieldsの指定なし
		$ck = $this->Page->afterFindIds($results , 'all' , 'list' );
		$ans = array (
		   9 => '9',
		   11 => '11',
	   );
		$this->assertEqual($ck , $ans);

		//listで$fieldsの指定あり
		$fields = array(
			'Page.id',
			'Page.page_name',
			'Page.root_id'
		);
		$ck = $this->Page->afterFindIds($results , 'all' , 'list' , $fields );
		$ans = array (
			9 =>
			array (
				9 => 'Public room',
			),
			11 =>
			array (
				11 => 'Private room of admin',
			),
		);
		$this->assertEqual($ck , $ans);

		$fields = array(
			'Page.id',
			'Page.page_name',
		);
		$ck = $this->Page->afterFindIds($results , 'all' , 'list' , $fields );
		$ans = array (
			9  => 'Public room',
			11 => 'Private room of admin',
		);
		$this->assertEqual($ck , $ans);
	}

/**
 * testFindViewableRoom method
 *
 * @return void
 */
	public function testFindViewableRoom() {
		//findVieable()の条件にisRoomを足しただけの機能なので一旦あとまわし
	}

/**
 * testPaginate method
 *
 * @return void
 */
	public function testPaginate() {

		//現状使われていないみたいなので、使われてないなら削除したい。
		//orderが必須になっているのに使われていない。
		$conditions = array('Page.id'=>16);
		$fields= array('Page.id' , 'Page.page_name');
		$order = array();
		$limit = 10;
		$ck = $this->Page->Paginate($conditions , $fields, $order, $limit);
		$ans = array (
			16 => 'UnitTest-A',
		);
		$this->assertEqual($ck , $ans);

		$conditions = array('Page.id'=>13);
		$fields= array('Page.id' , 'Page.page_name');
		$order = array();
		$limit = 10;
		$ck = $this->Page->Paginate($conditions , $fields, $order, $limit);
		$this->assertEqual($ck , false);

		$conditions = array('Page.id'=>11);
		$fields= array('Page.id' , 'Page.page_name');
		$order = array();
		$limit = 10;
		$extra = array(
			'user_id'=>1
		);
		$ck = $this->Page->Paginate($conditions , $fields, $order, $limit , 1 , null , $extra);
		$this->assertEqual($ck , false);

		$conditions = array('Page.id'=>16);
		$fields= array('Page.id' , 'Page.page_name');
		$order = array();
		$limit = 10;
		$extra = array(
			'user_id'=>1
		);
		$ck = $this->Page->Paginate($conditions , $fields, $order, $limit , 1 , null , $extra);
		$ans = array (
			16 => 'UnitTest-A',
		);
		$this->assertEqual($ck , $ans);

		$conditions = array('Page.id'=>16);
		$fields= array('Page.id' , 'Page.page_name');
		$order = array();
		$limit = 'AAA'; //パラメータ異常
		$extra = array(
			'user_id'=>1
		);
		$ck = $this->Page->Paginate($conditions , $fields, $order, $limit , 1 , null , $extra);
		$this->assertEqual($ck , false);

		$conditions = array('Page.id'=>16);
		$fields= array('Page.id' , 'Page.page_name');
		$order = array();
		$limit = 0;
		$extra = array(
			'user_id'=>1
		);
		$ck = $this->Page->Paginate($conditions , $fields, $order, $limit , 1 , null , $extra);
		$ans = array (
			16 => 'UnitTest-A',
		);
		$this->assertEqual($ck , $ans);

		$conditions = array('Page.id'=>16);
		$fields= array('Page.id' , 'Page.page_name');
		$order = array();
		$limit = 0;
		$extra = array(
			'user_id'=>999999999999 //存在しないユーザー
		);
		$ck = $this->Page->Paginate($conditions , $fields, $order, $limit , 1 , null , $extra);
		$this->assertEqual($ck , false);

		//2階層目を保存してみる
		$User = ClassRegistry::init("User");
		$user = $User->find("first" , array('conditions'=>array('User.id'=>1),) );
		Configure::write(NC_SYSTEM_KEY.'.user' , $user['User']);

		$new_page = array (
			'Page' =>
			array (
				'root_id' => '4',
				'parent_id' => '16',
				'thread_num' => '2',
				'display_sequence' => '1',
				'page_name' => 'Private Top',
				'permalink' => 'admin',
				'position_flag' => '1',
				'is_page_meta_node' => '0',
				'is_page_style_node' => '0',
				'is_page_layout_node' => '0',
				'is_page_theme_node' => '0',
				'is_page_column_node' => '0',
				'room_id' => '16',
				'space_type' => '4',
				'show_count' => '130',
				'display_flag' => '0',
				'display_from_date' => NULL,
				'display_to_date' => NULL,
				'display_apply_subpage' => '1',
				'display_reverse_permalink' => NULL,
				'is_approved' => '1',
				'lock_authority_id' => '0'
			),
		);


		//id16の子孫を3件作成
		$this->Page->create();
		$this->Page->save($new_page);

		$this->Page->create();
		$this->Page->save($new_page);

		$conditions = array('Page.parent_id'=>16);
		$fields= array('Page.id' , 'Page.page_name');
		$order = array();
		$limit = 100;
		$extra = array(
			'user_id'=>1
		);

		$ck = $this->Page->Paginate($conditions , $fields, $order, $limit , 1 , null , $extra);
		$ans = array (
			18 => 'Private Top',
			17 => 'Private Top',
		);
		$this->assertEqual($ck , $ans);
	}

/**
 * testPaginateCount method
 *
 * @return void
 */
	public function testPaginateCount() {
	}

/**
 * testDeletePage method
 *
 * @return void
 */
	public function testDeletePage() {
		//まだ未実装部分があるため一旦後回し。
		//トランザクションの必要がある。
	}

/**
 * testDecrementDisplaySeq method
 *
 * @return void
 */
	public function testDecrementDisplaySeq() {
		$page = $this->Page->find('first' , array('conditions' => array('Page.id' => 15)));
		$ck = $this->Page->decrementDisplaySeq($page);
		$this->assertEqual(true , $ck);

		$ck = $this->Page->decrementDisplaySeq($page , 5);
		$this->assertEqual(true , $ck);
	}

/**
 * testIncrementDisplaySeq method
 *
 * @return void
 */
	public function testIncrementDisplaySeq() {

		$page = $this->Page->find('first' , array('conditions' => array('Page.id' => 15)));
		$ck = $this->Page->incrementDisplaySeq($page);
		$this->assertEqual(true , $ck);

		$ck = $this->Page->incrementDisplaySeq($page , 5);
		$this->assertEqual(true , $ck);

		//第二引数を文字列 //パラメータ不正
		//TODO: パラメータの型チェックが不足　SQLエラーが発生する。対応する。
		//$ck = $this->Page->incrementDisplaySeq($page , 'AAAA');
		//$this->assertEqual(true , $ck);

		$ck = $this->Page->incrementDisplaySeq($page , -5000);
		$this->assertEqual(true , $ck);

		//idが抜けていた場合。
		$page2 = $page;
		unset($page2['Page']['id']);
		$ck = $this->Page->incrementDisplaySeq($page2 , -5000);
		$this->assertEqual(true , $ck);

		//何をしても基本的にはtrueが戻ってきているが何も更新されていないようにも見える。
		//var_dump($this->Page->find('all'));
		$page = $this->Page->find('first' , array('conditions' => array('Page.id' => 11)));
		$ck = $this->Page->incrementDisplaySeq($page , 1 ,  array('Page.id' => 11));
		$this->assertEqual(true , $ck);

		//第三引数が不正
		//SQLエラーが発生する。
		//TODO:引数で設定される値が不正だった場合の対応をどうするか検討する。不要？
		//$ck = $this->Page->incrementDisplaySeq($page , 1 ,  array('Page.idddddddddd' => 11));
		//$this->assertEqual(true , $ck);
	}

/**
 * testGetMovePermalink method
 *
 * @return void
 */
	public function testGetMovePermalink() {

		//$page['Page']['permalink']を元に、URLを作っている。
		$page = $this->Page->find('first' , array('conditions' => array('Page.id' => 16)));
		$parent_page = $this->Page->find('first' , array('conditions' => array('Page.id' => 4)));
		$ck = $this->Page->getMovePermalink($page , $parent_page);
		$this->assertEqual($ck , 'community-1');

		$page = $this->Page->find('first' , array('conditions' => array('Page.id' => 16)));
		$parent_page = $this->Page->find('first' , array('conditions' => array('Page.id' => 15)));
		$ck = $this->Page->getMovePermalink($page , $parent_page);
		$this->assertEqual($ck , 'admin/community-1');

		//必要な項目が配列で含まれていない場合は正常に動作しない
		//TODO : パラメータのチェック
		//$ck = $this->Page->getMovePermalink("" , "");
	}

/**
 * testCreateDefaultEntry method
 *
 * @return void
*/
	public function testCreateDefaultEntry() {

		//マイポータル作成, マイルーム作成, ルーム参加
		$user = array();
		$user['User'] = array(
			'id'=>"7",
			'login_id'=>'admin_7' ,
			'password'=>'1a9faaae0428d9f50245c1fb77cad74a25fd917a' ,
			'handle'=>'admin',
			'authority_id'=>"2",
			'is_active'=>"1",
			'permalink'=>'admin_7',
			'myportal_page_id'=>"10",
			'private_page_id'=>"11",
			'avatar'=>NULL,
			'activate_key'=>NULL,
			'lang'=>'ja',
			'timezone_offset'=>"9",
			'email'=>'',
			'mobile_email'=>'',
			'password_regist'=>NULL ,
			'last_login'=>'2013-09-27 00:42:44',
			'previous_login'=>'2013-07-22 08:20:15',
			'created'=>'2013-10-31 04:09:21',
			'created_user_id'=>"1",
			'created_user_name'=>'admin',
			'modified'=>'2013-10-31 09:52:46',
			'modified_user_id'=>'0',
			'modified_user_name'=>''
		);

		//return array($myportalPageId, $privatePageId);
		//生成された Page.idの値。
		$result = array (
			0 => '17',
			1 => '19',
		);

		$ck = $this->Page->createDefaultEntry($user);


		$this->assertEqual($result , $ck);

		//同じユーザの情報で再度実行しても作られる
		//1アカウントに対し1回のみの実行となるようにする必要がある。
		//とりあえず複数件登録されてしまうテストはコメントアウト
		/*
		$result = array (
			0 => '21',
			1 => '23',
		);
		$ck = $this->Page->createDefaultEntry($user);
		$this->assertEqual($result , $ck);
		$this->Page->find('list' , array());
		*/
	}

/**
 * testInsTopRoom method
 *
 * @return void
 */
	public function testInsTopRoom() {

		$User = ClassRegistry::init('User');
		$user_data = $User->find('first' , array('conditions' => array('User.id' => 1)));

		//2レコード作られる。 name : Private room と Private Top
		//id:17と18が作られて、18は17の子になる。
		$spaceType = NC_SPACE_TYPE_PRIVATE;
		$userId = 1;
		$permalink = $user_data['User']['permalink'];
		$ck = $this->Page->insTopRoom($spaceType, $userId, $permalink);
		$this->assertEqual($ck , 17);
		$id = $ck;
		$ck = $this->Page->findById($id );
		$ck2 = $this->Page->findById($id + 1);
		$key = 'Page';

		$this->assertEqual($ck[$key]['room_id'] , $id);
		$this->assertEqual($ck[$key]['page_name'] , 'Private room');
		$this->assertEqual($ck2[$key]['page_name'] , 'Private Top');
		$this->assertEqual($ck2[$key]['root_id'] , $id);

		//2レコード作られる。 name : Myportal と Myportal Top
		//Page.id 19と20が作られて、20は19の子になる。
		$spaceType = NC_SPACE_TYPE_MYPORTAL;
		$userId = 1;
		$permalink = $user_data['User']['permalink'];
		$ck = $this->Page->insTopRoom($spaceType, $userId, $permalink);
		$this->assertEqual($ck , 19);

		$ck = $this->Page->findById(19);
		$ck2 = $this->Page->findById(20);
		$key = 'Page';
		$id = 19;
		$this->assertEqual($ck[$key]['room_id'] , $id);
		$this->assertEqual($ck[$key]['page_name'] , 'Myportal');
		$this->assertEqual($ck2[$key]['page_name'] , 'Myportal Top');
		$this->assertEqual($ck2[$key]['root_id'] , $id);

		//2レコード作られる。 name : Myportal と Myportal Top
		//Page.id 19と20が作られて、21は22の子になる。
		$Authority = ClassRegistry::init('Authority');
		$authority = $Authority->find('first', array(
			'fields' => array('myportal_use_flag', 'private_use_flag'),
			'conditions' => array($Authority->primaryKey => $user_data['User']['authority_id']),
			'recursive' => -1
		));
		$spaceType = NC_SPACE_TYPE_MYPORTAL;
		$userId = 1;
		$permalink = $user_data['User']['permalink'];
		$ck = $this->Page->insTopRoom($spaceType, $userId, $permalink , $authority);
		$this->assertEqual($ck , 21);

		//NC_SPACE_TYPE_PUBLICの場合は作られず、falseが戻る。
		$spaceType = NC_SPACE_TYPE_PUBLIC;
		$userId = 1;
		$permalink = $user_data['User']['permalink'];
		$ck = $this->Page->insTopRoom($spaceType, $userId, $permalink , $authority);
		$this->assertEqual($ck , false);

		//NC_SPACE_TYPE_GROUPの場合は作られずfalseがもどる
		$spaceType = NC_SPACE_TYPE_GROUP;
		$userId = 1;
		$permalink = $user_data['User']['permalink'];
		$ck = $this->Page->insTopRoom($spaceType, $userId, $permalink , $authority);
		$this->assertEqual($ck , false);

	}

/**
 * testUpdPermalinks method
 *
 * @return void
 */
	public function testUpdPermalinks() {

		//root_idがこれのものについて、permalinkの最初の階層が書き換わる機能。
		//現在この機能にトランザクションはかかっていない
		$ans = 'NcPageTest_updPermalinks_1';
		$ck = $this->Page->updPermalinks(11 , $ans);
		$this->assertEqual($ck , true);
		//指定されたPageId
		$ck = $this->Page->find('first' , array('conditions'=>array('Page.id'=>11)));
		$this->assertEqual($ck['Page']['permalink'] , $ans);
		//子孫でpermalinkに/が入っていないデータだった場合
		$ck = $this->Page->find('first' , array('conditions'=>array('Page.id'=>15)));
		$this->assertEqual($ck['Page']['permalink'] , $ans);

	}

/**
 * testFindNodeFlag method
 *
 * @return void
 */
	public function testFindNodeFlag() {

		//Aが親Pageで、その子供にB、その子供にCがあった場合
		//Cのページを表示されたときに、どこのページで設定されたスタイル、あるいは、レイアウト（ヘッダーカラムの表示有無など）を
		//適用されているか判断するメソッド
		//画面でいうとページ設定のページスタイル、ページレイアウトの上部の適用範囲で
		//現在のノード、現在のページを選択した場合に設定される

		$page = $this->Page->find('first' , array('conditions'=>array('Page.id'=>9)));
		$ck = $this->Page->findNodeFlag($page);
		$ans = array (
			'is_page_meta_node' => 0,
			'is_page_style_node' => 0,
			'is_page_layout_node' => 0,
			'is_page_theme_node' => 0,
			'is_page_column_node' => 0,
		);
		$this->assertEqual($ck , $ans);
		$page = $this->Page->find('first' , array('conditions'=>array('Page.id'=>10)));
		$ck = $this->Page->findNodeFlag($page);
		$ans = array (
			'is_page_meta_node' => 0,
			'is_page_style_node' => 0,
			'is_page_layout_node' => 0,
			'is_page_theme_node' => 0,
			'is_page_column_node' => 0,
		);
		$this->assertEqual($ck , $ans);
		$page = $this->Page->find('first' , array('conditions'=>array('Page.id'=>16)));
		$ck = $this->Page->findNodeFlag($page);
		$ans = array (
			'is_page_meta_node' => 0,
			'is_page_style_node' => 0,
			'is_page_layout_node' => 0,
			'is_page_theme_node' => 0,
			'is_page_column_node' => 0,
		);
		$this->assertEqual($ck , $ans);
		$page = $this->Page->find('first' , array('conditions'=>array('Page.id'=>4)));
		$ck = $this->Page->findNodeFlag($page , array( 'is_page_meta_node'=>0 ));
		$ans = array (
			'is_page_meta_node' => 0,
		);
		$this->assertEqual($ck , $ans);
		$page = $this->Page->find('first' , array('conditions'=>array('Page.id'=>4)));
		$ck = $this->Page->findNodeFlag($page , array( 'is_page_meta_node'=>5 ));
		$ans = array (
			'is_page_meta_node' => 5,
		);
		$this->assertEqual($ck , $ans);

		//想定されていないkeyがしていされると、Noticeエラーが発生する。
		//$page = $this->Page->find('first' , array('conditions'=>array('Page.id'=>4)));
		//$ck = $this->Page->findNodeFlag($page , array( 'is_page_meta_node_hogehoge'=>5 ));

		//第二引数がstring(パラメータ異常） メソッド内foreach文でエラーが発生する。
		//$page = $this->Page->find('first' , array('conditions'=>array('Page.id'=>4)));
		//$ck = $this->Page->findNodeFlag($page , 'is_page_meta_node');

		$spaceType = NC_SPACE_TYPE_MYPORTAL;
		$userId = 1;
		$permalink = 'Admin';
		$id = $this->Page->insTopRoom($spaceType, $userId, $permalink);
		//$idには($id+1)の子孫が存在する。

		$page = $this->Page->find('first' , array('conditions'=>array('Page.id'=>($id+1))));
		$ck = $this->Page->findNodeFlag($page);
		$ans = array (
			'is_page_meta_node' => 0,
			'is_page_style_node' => 0,
			'is_page_layout_node' => 0,
			'is_page_theme_node' => 0,
			'is_page_column_node' => 0,
		);
		$this->assertEqual($ck , $ans);

		$page = $this->Page->find('first' , array('conditions'=>array('Page.id'=>($id))));
		$ck = $this->Page->findNodeFlag($page);
		$ans = array (
			'is_page_meta_node' => 0,
			'is_page_style_node' => 0,
			'is_page_layout_node' => 0,
			'is_page_theme_node' => 0,
			'is_page_column_node' => 0,
		);
		$this->assertEqual($ck , $ans);

		//$idに($id+2)の子孫を作成する
		$sample = $this->Page->getDefaultData(NC_SPACE_TYPE_MYPORTAL);
		$sample['Page']['thread_num']                  = 2;
		$sample['Page']['root_id']                     = $id;
		$sample['Page']['is_page_meta_node']           = 1;
		$sample['Page']['Page.is_page_style_node']     = 2;
		$sample['Page']['is_page_layout_node']         = 3;
		$sample['Page']['is_page_theme_node']          = 4;
		$sample['Page']['Page.is_page_column_node']    = 5;
		$this->Page->create();
		$this->Page->save($sample);

		$page = $this->Page->find('first' , array('conditions'=>array('Page.id'=>($id+2))));
		$ck = $this->Page->findNodeFlag($page);
		$ans = array (
			'is_page_meta_node' => '19',
			'is_page_style_node' => 0,
			'is_page_layout_node' => '19',
			'is_page_theme_node' => '19',
			'is_page_column_node' => 0,
		);
		$this->assertEqual($ck , $ans);

		//$idに($id+3)の子孫を作成する
		$sample = $this->Page->getDefaultData(NC_SPACE_TYPE_MYPORTAL);
		$sample['Page']['thread_num']                  = 2;
		$sample['Page']['root_id']                     = $id;
		$sample['Page']['is_page_meta_node']           = 1;
		$sample['Page']['Page.is_page_style_node']     = 2;
		$sample['Page']['is_page_layout_node']         = 3;
		$sample['Page']['is_page_theme_node']          = 4;
		$sample['Page']['Page.is_page_column_node']    = 5;
		$this->Page->create();
		$this->Page->save($sample);

		$page = $this->Page->find('first' , array('conditions'=>array('Page.id'=>($id+3))));
		$ck = $this->Page->findNodeFlag($page);
		$ans = array (
			'is_page_meta_node' => '20',
			'is_page_style_node' => 0,
			'is_page_layout_node' => '20',
			'is_page_theme_node' => '20',
			'is_page_column_node' => 0,
		);
		$this->assertEqual($ck , $ans);

	}

}
