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
		'NcRevision'
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
	}

/**
 * testAfterFindIds method
 *
 * @return void
 */
	public function testAfterFindIds() {
	}

/**
 * testFindBreadcrumb method
 *
 * @return void
 */
	public function testFindBreadcrumb() {
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
 * testFindViewableRoom method
 *
 * @return void
 */
	public function testFindViewableRoom() {
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
				),
			),
		);
		$ck = $this->Page->findViewable('all' , 'all');
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

	function test_format_findViewable_currents_by_centerPage()
	{
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
	}

/**
 * testFindChilds method
 *
 * @return void
 */
	public function testFindChilds() {
	}

/**
 * testFindCommunityCount method
 *
 * @return void
 */
	public function testFindCommunityCount() {
	}

/**
 * testPaginate method
 *
 * @return void
 */
	public function testPaginate() {
	}

/**
 * testPaginateCount method
 *
 * @return void
 */
	public function testPaginateCount() {
	}

/**
 * testGetFieldsArray method
 *
 * @return void
 */
	public function testGetFieldsArray() {
	}

/**
 * testGetJoinsArray method
 *
 * @return void
 */
	public function testGetJoinsArray() {
	}

/**
 * testDeletePage method
 *
 * @return void
 */
	public function testDeletePage() {
	}

/**
 * testDecrementDisplaySeq method
 *
 * @return void
 */
	public function testDecrementDisplaySeq() {
	}

/**
 * testIncrementDisplaySeq method
 *
 * @return void
 */
	public function testIncrementDisplaySeq() {
	}

/**
 * testGetMovePermalink method
 *
 * @return void
 */
	public function testGetMovePermalink() {
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
	}

/**
 * testUpdPermalinks method
 *
 * @return void
 */
	public function testUpdPermalinks() {
	}

/**
 * testFindNodeFlag method
 *
 * @return void
 */
	public function testFindNodeFlag() {
	}

}
