<?php

/**
 * Community Test Case
 *
 */
class NcCommunityTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'NcCommunity',
		'NcConfig',
		'NcCommunityLang',
		'NcRevision',
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$ck = $this->Community = ClassRegistry::init('Community');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Community);

		parent::tearDown();
	}

/**
 * testGetDefault method
 *
 * @return void
 */
	public function testGetDefault() {
		$ck = $this->Community->getDefault();
		$ans = array (
			'Community' =>
			array (
				'photo' => 'community.gif',
				'is_upload' => 0,
				'publication_range_flag' => 0,
				'participate_flag' => '1',
				'participate_force_all_users' => 0,
				'invite_hierarchy' => 301,
				'is_participate_notice' => '1',
				'participate_notice_hierarchy' => 301,
				'is_resign_notice' => '1',
				'resign_notice_hierarchy' => 301,
			),
		);
		$this->assertEqual($ck , $ans);
	}

/**
 * testGetCommunityData method
 *
 * @return void
 */
	public function testGetCommunityData() {

		$room_id = 16;
		$ck = $this->Community->getCommunityData($room_id);
		$ans = array (
			0 =>
			array (
				'Community' =>
				array (
					'id' => '1',
					'room_id' => '16',
					'photo' => 'study.gif',
					'is_upload' => true,
					'publication_range_flag' => '11',
					'participate_force_all_users' => true,
					'participate_flag' => '1',
					'invite_hierarchy' => '301',
					'is_participate_notice' => true,
					'participate_notice_hierarchy' => '301',
					'is_resign_notice' => true,
					'resign_notice_hierarchy' => '301',
					'created' => '2013-11-20 05:41:20',
					'created_user_id' => '1',
					'created_user_name' => 'admin',
					'modified' => '2013-11-20 05:41:20',
					'modified_user_id' => '1',
					'modified_user_name' => 'admin',
				),
			),
			1 =>
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
			2 =>
			array (
				'CommunityTag' =>
				array (
					'tag_value' => '',
				),
			),
		);
		$this->assertEqual($ck , $ans);

		//存在しないコミュニティ
		$room_id = 15;
		$ck = $this->Community->getCommunityData($room_id);
		$this->assertEqual($ck , false); //nullか空arrayでも良いかも。パラメータは正しい
	}

/**
 * testGetSearchParams method
 *
 * @return void
 */
	public function testGetSearchParams() {

		//$ck = $this->Community->getSearchParams(&$paginate, $request);
	}

}
