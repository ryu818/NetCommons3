<?php

/**
 * User Test Case
 *
 */
class NcUserTest extends CakeTestCase {

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = array(
		'NcUser',
		'NcAuthority'
	);

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$this->User = ClassRegistry::init('User');
	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->User);

		parent::tearDown();
	}

	/**
	 * testDuplicate method
	 *
	 * @return void
	 */
	public function testDuplicate() {
	}

	/**
	 * testDuplicateEmail method
	 *
	 * @return void
	 */
	public function testDuplicateEmail() {
	}

	/**
	 * testInvalidPermalink method
	 *
	 * @return void
	 */
	public function testInvalidPermalink() {
	}

	/**
	 * testUpdateLastLogin method
	 *
	 * @return void
	 */
	public function testUpdateLastLogin() {
	}

	/**
	 * testCurrentUser method
	 *
	 * @return void
	 */
	public function testCurrentUser() {
		$centerPage = array();
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
			'created_user_id'=> 7,
			'created_user_name'=> 'admin',
			'modified'=> NULL,
			'modified_user_id'=> 0,
			'modified_user_name'=>''
		);
		$ck = $this->User->currentUser($centerPage);
		//$ckで戻る値はUserの1レコード分の情報
		//idは1っぽいけど、本来戻ってこないといけない情報と違う気がする。
		//id:7のレコードと混ざった結果になってる？？？
	}

	/**
	 * testFindParticipant method
	 *
	 * @return void
	 */
	public function testFindParticipant() {
	}

	/**
	 * testGetSendMails method
	 *
	 * @return void
	 */
	public function testGetSendMails() {
	}

	/**
	 * testGetSendMailsByPageId method
	 *
	 * @return void
	 */
	public function testGetSendMailsByPageId() {
	}

	/**
	 * testGetSendMailsByUserId method
	 *
	 * @return void
	 */
	public function testGetSendMailsByUserId() {
	}

	/**
	 * testGetRefineSearch method
	 *
	 * @return void
	 */
	public function testGetRefineSearch() {
	}

	/**
	 * testDeleteUser method
	 *
	 * @return void
	 */
	public function testDeleteUser() {
	}

	/**
	 * testConvertAvatarDisplay method
	 *
	 * @return void
	 */
	public function testConvertAvatarDisplay() {
	}

}
