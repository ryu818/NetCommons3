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
		'NcUserItem',
		'NcUserItemLang',
		'NcAuthority'
	);

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		//ログアウト状態
		Configure::clear(NC_SYSTEM_KEY.'.user');
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
		//User.idと同じ 1件以上あるので、false;
		//idは設定されていない状態
		if(isset($this->User->data['User']['id'])) unset($this->User->data['User']['id']);
		$data = array('User.login_id'=>'admin');
		$ck = $this->User->duplicate($data);
		$this->assertEqual($ck , false);

		//User.idと同じ 1件以上あるので、false;
		$this->User->data['User']['id'] = 16;
		$data = array('User.login_id'=>'admin');
		$ck = $this->User->duplicate($data);
		$this->assertEqual($ck , false);

		//User.idと同じ 同じidなのでOK
		$this->User->data['User']['id'] = 1;
		$data = array('User.login_id'=>'admin');
		$ck = $this->User->duplicate($data);
		$this->assertEqual($ck , true);

		//存在しない
		$this->User->data['User']['id'] = 1;
		$data = array('User.login_id'=>'adminadminadminadminadminadminadminadmin');
		$ck = $this->User->duplicate($data);
		$this->assertEqual($ck , true);

		//検索条件がおかしい
		//SQLErrorでエラーが発生する。
		//$this->User->data['User']['id'] = 1;
		//$data = array('User.login_id22222'=>'admin');
		//$ck = $this->User->duplicate($data);

	}

	/**
	 * testDuplicateEmail method
	 *
	 * @return void
	 */
	public function testDuplicateEmail() {
		//idは設定されていない状態
		if(isset($this->User->data['User']['id'])) unset($this->User->data['User']['id']);

		//重複データはない
		$data = array(
			0=>'hoge',
		);
		$ck = $this->User->duplicateEmail($data);
		$this->assertEqual($ck , true);

		//User.id:7を更新
		$new_data = array('User'=>array(
			'id'=>"7",
			'authority_id'=>"1",
			'is_active'=>"1",
			'permalink'=>'',
			'myportal_page_id'=>"10",
			'private_page_id'=>"11",
			'avatar'=>NULL,
			'activate_key'=>NULL,
			'lang'=>'ja',
			'timezone_offset'=>"9",
			'email'=>'hoge',
			'mobile_email'=>'',
			'password_regist'=>NULL ,
			'last_login'=>'2013-09-27 00:42:44',
			'previous_login'=>'2013-07-22 08:20:15',
		));
		$this->User->save($new_data);

		//重複データありの状態　arrayで使われるのは1件目だけ。
		$data = array(
			0=>'hoge',
			1=>'fuga'
		);
		$ck = $this->User->duplicateEmail($data);
		$this->assertEqual($ck , false);

		//重複データなしの状態
		$data = array(
			0=>'fuga',
			1=>'hoge'
		);
		$ck = $this->User->duplicateEmail($data);
		$this->assertEqual($ck , true);


		//重複データありの状態  id同じの場合 true
		$this->User->data['User']['id'] = 7;
		$data = array(
			0=>'hoge',
			1=>'fuga'
		);
		$ck = $this->User->duplicateEmail($data);
		$this->assertEqual($ck , true);

		$this->User->data['User']['id'] = 6;
		$data = array(
			0=>'',
		);
		$ck = $this->User->duplicateEmail($data);
		$this->assertEqual($ck , false);

		//空array
		$data = array();
		$ck = $this->User->duplicateEmail($data);
		$this->assertEqual($ck , false);

		//文字列
		$data = 'fuga';
		$ck = $this->User->duplicateEmail($data);
		$this->assertEqual($ck , true);

		//文字列　重複あり
		$data = 'hoge';
		$ck = $this->User->duplicateEmail($data);
		$this->assertEqual($ck ,false);
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

	   //マイポータル、マイルーム以外ならば''
		$centerPage = array(
			'Page'=>array(
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
				'space_type'        => NC_SPACE_TYPE_GROUP,
				'show_count'        => 0,
				'display_flag'      => 1,
				'display_from_date' => NULL,
				'display_to_date'   => NULL,
				'display_apply_subpage'=> 1,
				'display_reverse_permalink'=> NULL,
				'is_approved'       => 1,
				'lock_authority_id' => 0,
				'created'           => NULL,
				'created_user_id'   => 1,
				'created_user_name' => '',
				'modified'          => NULL,
				'modified_user_id'  => 1,
				'modified_user_name'=>''
			)
		);
		$ck = $this->User->currentUser($centerPage);
		$this->assertEqual('' , $ck);


		//マイポータルの場合
		//User.permalinkとPage.permarinkが同じデータがあった場合
		$centerPage = array(
			'Page'=>array(
				'id'                => 9,
				'root_id'           => 9 ,
				'parent_id'         => 1 ,
				'thread_num'        => 1 ,
				'display_sequence'  => 0,
				'page_name'         => 'my portal',
				'permalink'         => 'admin',
				'position_flag'     => 1 ,
				'lang'              => '',
				'is_page_meta_node'  => 0,
				'is_page_style_node' => 0,
				'is_page_layout_node'=> 0,
				'is_page_theme_node' => 0,
				'is_page_column_node'=> 0,
				'room_id'           => 9,
				'space_type'        => NC_SPACE_TYPE_MYPORTAL,
				'show_count'        => 0,
				'display_flag'      => 1,
				'display_from_date' => NULL,
				'display_to_date'   => NULL,
				'display_apply_subpage'=> 1,
				'display_reverse_permalink'=> NULL,
				'is_approved'       => 1,
				'lock_authority_id' => 0,
				'created'           => NULL,
				'created_user_id'   => 1,
				'created_user_name' => '',
				'modified'          => NULL,
				'modified_user_id'  => 1,
				'modified_user_name'=>''
			)
		);
		$ck = $this->User->currentUser($centerPage);
		$ans = array (
			'User' =>
			array (
				'id' => '1',
				'login_id' => 'admin',
				'password' => '1a9faaae0428d9f50245c1fb77cad74a25fd917a',
				'handle' => 'admin',
				'authority_id' => '1',
				'is_active' => '1',
				'permalink' => 'admin',
				'myportal_page_id' => '10',
				'private_page_id' => '11',
				'avatar' => NULL,
				'activate_key' => NULL,
				'lang' => 'ja',
				'timezone_offset' => '9',
				'email' => '',
				'mobile_email' => '',
				'password_regist' => NULL,
				'last_login' => '2013-09-27 00:42:44',
				'previous_login' => '2013-07-22 08:20:15',
				'created' => '2013-10-31 04:09:21',
				'created_user_id' => '1',
				'created_user_name' => 'admin',
				'modified' => '2013-10-31 09:52:46',
				'modified_user_id' => '1',
				'modified_user_name' => '',
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

		//マイポータルの場合
		//User.permalinkとPage.permarinkが同じデータがあった場合
		//permalinkの/区切りの最初の部分が合致している
		$centerPage = array(
			'Page'=>array(
				'id'                => 9,
				'root_id'           => 9 ,
				'parent_id'         => 1 ,
				'thread_num'        => 3 ,
				'display_sequence'  => 0,
				'page_name'         => 'my portal',
				'permalink'         => 'admin/hoge/hoge',
				'position_flag'     => 1 ,
				'lang'              => '',
				'is_page_meta_node'  => 0,
				'is_page_style_node' => 0,
				'is_page_layout_node'=> 0,
				'is_page_theme_node' => 0,
				'is_page_column_node'=> 0,
				'room_id'           => 9,
				'space_type'        => NC_SPACE_TYPE_MYPORTAL,
				'show_count'        => 0,
				'display_flag'      => 1,
				'display_from_date' => NULL,
				'display_to_date'   => NULL,
				'display_apply_subpage'=> 1,
				'display_reverse_permalink'=> NULL,
				'is_approved'       => 1,
				'lock_authority_id' => 0,
				'created'           => NULL,
				'created_user_id'   => 1,
				'created_user_name' => '',
				'modified'          => NULL,
				'modified_user_id'  => 1,
				'modified_user_name'=>''
			)
		);
		$ck = $this->User->currentUser($centerPage);
		$ans = array (
			'User' =>
			array (
				'id' => '1',
				'login_id' => 'admin',
				'password' => '1a9faaae0428d9f50245c1fb77cad74a25fd917a',
				'handle' => 'admin',
				'authority_id' => '1',
				'is_active' => '1',
				'permalink' => 'admin',
				'myportal_page_id' => '10',
				'private_page_id' => '11',
				'avatar' => NULL,
				'activate_key' => NULL,
				'lang' => 'ja',
				'timezone_offset' => '9',
				'email' => '',
				'mobile_email' => '',
				'password_regist' => NULL,
				'last_login' => '2013-09-27 00:42:44',
				'previous_login' => '2013-07-22 08:20:15',
				'created' => '2013-10-31 04:09:21',
				'created_user_id' => '1',
				'created_user_name' => 'admin',
				'modified' => '2013-10-31 09:52:46',
				'modified_user_id' => '1',
				'modified_user_name' => '',
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

		//　/からなので、permalinkの1つめはブランクが格納されている状態になっている。
		$centerPage= array('Page'=>array(
			'id'=> 1,
			'root_id'=>1 ,
			'parent_id'=> 1 ,
			'thread_num'=> 1 ,
			'display_sequence'=> 1 ,
			'page_name'=> 'Public room',
			'permalink'=> '/',  // /だけが登録される場合はない。データ不整合な状態。
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
		));
		$ck = $this->User->currentUser($centerPage);;
		$ans = array (
			'User' =>
			array (
				'id' => '7',
				'login_id' => 'admin_7',
				'password' => '1a9faaae0428d9f50245c1fb77cad74a25fd917a',
				'handle' => 'admin',
				'authority_id' => '1',
				'is_active' => '1',
				'permalink' => '',
				'myportal_page_id' => '10',
				'private_page_id' => '11',
				'avatar' => NULL,
				'activate_key' => NULL,
				'lang' => 'ja',
				'timezone_offset' => '9',
				'email' => '',
				'mobile_email' => '',
				'password_regist' => NULL,
				'last_login' => '2013-09-27 00:42:44',
				'previous_login' => '2013-07-22 08:20:15',
				'created' => '2013-10-31 04:09:21',
				'created_user_id' => '1',
				'created_user_name' => 'admin',
				'modified' => '2013-10-31 09:52:46',
				'modified_user_id' => '0',
				'modified_user_name' => '',
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

		//空array （パラメータ異常）
		$centerPage = array();
		$ck = $this->User->currentUser($centerPage);
		$this->assertEqual(false , $ck);

		//文字列  （パラメータ異常）
		$centerPage = 'hogefuga';
		$ck = $this->User->currentUser($centerPage);
		$this->assertEqual(false , $ck);

		//blank  （パラメータ異常）
		$centerPage = '';
		$ck = $this->User->currentUser($centerPage);
		$this->assertEqual(false , $ck);
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
