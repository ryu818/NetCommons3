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
		'NcAuthority',
		'NcUser',
		'NcPageUserLink',
		'NcPage'
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

	/**
	 * test_checkParticipateForceAllUsers
	 * @return void
	 */
	public function test_checkParticipateForceAllUsers() {

		//未ログイン状態
		$check = array('Page'=>array('id'=>16));
		$ck = $this->Community->_checkParticipateForceAllUsers($check);
		$this->assertEqual($ck , false);

		//存在しないid
		$check = array('Page'=>array('id'=>99999999999));
		$ck = $this->Community->_checkParticipateForceAllUsers($check);
		$this->assertEqual($ck , false);


		//login状態を作成
		$User = ClassRegistry::init('User');
		$login_user = $User->find('first' , array('conditions'=>array('User.id'=>1)));
		Configure::write(NC_SYSTEM_KEY.'.user' , $login_user['User']);

		//Page
		$check = array('Page'=>array(
			'id'              =>16 ,
			'participate_flag'=>1
		));
		$ck = $this->Community->_checkParticipateForceAllUsers($check);
		$this->assertEqual($ck , true);

		$check = array('Page'=>array(
			'id'              =>16 ,
			'participate_flag'=>0
		));
		$ck = $this->Community->_checkParticipateForceAllUsers($check);
		$this->assertEqual($ck , true);

		//存在しないページ
		$check = array('Page'=>array(
			'id'              =>9999999999 ,
			'participate_flag'=>1
		));
		$ck = $this->Community->_checkParticipateForceAllUsers($check);
		$this->assertEqual($ck , true);

		//権限をGESTな状態でログイン状態にする----
		$login_user['User']['authority_id'] = 5;
		$User->save($login_user);
		Configure::write(NC_SYSTEM_KEY.'.user' , $login_user['User']);
		//------------------------------------

		$check = array('Page'=>array(
			'id'              =>16 ,
			'participate_flag'=>1
		));
		$ck = $this->Community->_checkParticipateForceAllUsers($check);
		$this->assertEqual($ck , false);

		$check = array('Page'=>array(
			'id'              =>16 ,
			'participate_flag'=>0
		));
		$ck = $this->Community->_checkParticipateForceAllUsers($check);
		$this->assertEqual($ck , false);

		//存在しないページ
		$check = array('Page'=>array(
			'id'              =>9999999999 ,
			'participate_flag'=>1
		));
		$ck = $this->Community->_checkParticipateForceAllUsers($check);
		$this->assertEqual($ck , false);

		//
		//権限4　Common Userな状態でログイン状態にする----
		$login_user['User']['authority_id'] = 4;
		$User->save($login_user);
		Configure::write(NC_SYSTEM_KEY.'.user' , $login_user['User']);
		//------------------------------------4

		$check = array('Page'=>array(
			'id'              =>16 ,
			'participate_flag'=>1
		));
		$ck = $this->Community->_checkParticipateForceAllUsers($check);
		$this->assertEqual($ck , false);

		$check = array('Page'=>array(
			'id'              =>16 ,
			'participate_flag'=>0
		));
		$ck = $this->Community->_checkParticipateForceAllUsers($check);
		$this->assertEqual($ck , false);

		//存在しないページ
		$check = array('Page'=>array(
			'id'              =>9999999999 ,
			'participate_flag'=>1
		));
		$ck = $this->Community->_checkParticipateForceAllUsers($check);
		$this->assertEqual($ck , false);

		//権限3　Moderatorな状態でログイン状態にする----
		$login_user['User']['authority_id'] = 3;
		$User->save($login_user);
		Configure::write(NC_SYSTEM_KEY.'.user' , $login_user['User']);
		//------------------------------------4

		$check = array('Page'=>array(
			'id'              =>16 ,
			'participate_flag'=>1
		));
		$ck = $this->Community->_checkParticipateForceAllUsers($check);
		$this->assertEqual($ck , false);

		$check = array('Page'=>array(
			'id'              =>16 ,
			'participate_flag'=>0
		));
		$ck = $this->Community->_checkParticipateForceAllUsers($check);
		$this->assertEqual($ck , false);

		//存在しないページ
		$check = array('Page'=>array(
			'id'              =>9999999999 ,
			'participate_flag'=>1
		));
		$ck = $this->Community->_checkParticipateForceAllUsers($check);
		$this->assertEqual($ck , false);

		//権限2　Room Managerな状態でログイン状態にする----
		$login_user['User']['authority_id'] = 2;
		$User->save($login_user);
		Configure::write(NC_SYSTEM_KEY.'.user' , $login_user['User']);
		//------------------------------------4

		$check = array('Page'=>array(
			'id'              =>16 ,
			'participate_flag'=>1
		));
		$ck = $this->Community->_checkParticipateForceAllUsers($check);
		$this->assertEqual($ck , true);

		$check = array('Page'=>array(
			'id'              =>16 ,
			'participate_flag'=>0
		));
		$ck = $this->Community->_checkParticipateForceAllUsers($check);
		$this->assertEqual($ck , true);


		//存在しないページ
		$check = array('Page'=>array(
			'id'              =>9999999999 ,
			'participate_flag'=>1
		));
		$ck = $this->Community->_checkParticipateForceAllUsers($check);
		$this->assertEqual($ck , true);

	}

}
