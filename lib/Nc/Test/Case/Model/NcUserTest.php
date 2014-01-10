<?php
/**
 * User Test Case
 *
 */
class NcUserTest extends CakeTestCase {

/**
 * Fixtures
 *
 */
	public $fixtures = array(
		'NcUser',
		'NcUserItem',
		'NcUserItemLang',
		'NcAuthority',
		'NcPage',
		'NcPageUserLink',
		'NcCommunity',
		'NcCommunityLang',
		'NcConfig',
		'NcConfigLang',
		'NcUpload'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		//ログアウト状態
		Configure::clear("{NC_SYSTEM_KEY}.user");
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
		if (isset($this->User->data['User']['id'])) {
			unset($this->User->data['User']['id']);
		}

		$data = array('User.login_id' => 'admin');
		$ck = $this->User->duplicate($data);
		$this->assertEqual($ck, false);

		//User.idと同じ 1件以上あるので、false;
		$this->User->data['User']['id'] = 16;
		$data = array('User.login_id' => 'admin');
		$ck = $this->User->duplicate($data);
		$this->assertEqual($ck, false);

		//User.idと同じ 同じidなのでOK
		$this->User->data['User']['id'] = 1;
		$data = array('User.login_id' => 'admin');
		$ck = $this->User->duplicate($data);
		$this->assertEqual($ck, true);

		//存在しない
		$this->User->data['User']['id'] = 1;
		$data = array('User.login_id' => 'adminadminadminadminadminadminadminadmin');
		$ck = $this->User->duplicate($data);
		$this->assertEqual($ck, true);

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
		if (isset($this->User->data['User']['id'])) {
			unset($this->User->data['User']['id']);
		}

		//重複データはない
		$data = array(
			0 => 'hoge',
		);
		$ck = $this->User->duplicateEmail($data);
		$this->assertEqual($ck, true);

		//User.id:7を更新
		$newData = array('User' => array(
			'id' => "7",
			'authority_id' => "1",
			'is_active' => "1",
			'permalink' => '',
			'myportal_page_id' => "10",
			'private_page_id' => "11",
			'avatar' => null,
			'activate_key' => null,
			'lang' => 'ja',
			'timezone_offset' => "9",
			'email' => 'hoge',
			'mobile_email' => '',
			'password_regist' => null,
			'last_login' => '2013-09-27 00:42:44',
			'previous_login' => '2013-07-22 08:20:15',
		));
		$this->User->save($newData);

		//重複データありの状態　arrayで使われるのは1件目だけ。
		$data = array(
			0 => 'hoge',
			1 => 'fuga'
		);
		$ck = $this->User->duplicateEmail($data);
		$this->assertEqual($ck, false);

		//重複データなしの状態
		$data = array(
			0 => 'fuga',
			1 => 'hoge'
		);
		$ck = $this->User->duplicateEmail($data);
		$this->assertEqual($ck, true);

		//重複データありの状態 id同じの場合 true
		$this->User->data['User']['id'] = 7;
		$data = array(
			0 => 'hoge',
			1 => 'fuga'
		);
		$ck = $this->User->duplicateEmail($data);
		$this->assertEqual($ck, true);

		$this->User->data['User']['id'] = 6;
		$data = array(
			0 => '',
		);
		$ck = $this->User->duplicateEmail($data);
		$this->assertEqual($ck, false);

		//空array
		$data = array();
		$ck = $this->User->duplicateEmail($data);
		$this->assertEqual($ck, false);

		//文字列
		$data = 'fuga';
		$ck = $this->User->duplicateEmail($data);
		$this->assertEqual($ck, true);

		//文字列　重複あり
		$data = 'hoge';
		$ck = $this->User->duplicateEmail($data);
		$this->assertEqual($ck, false);
	}

/**
 * testInvalidPermalink method
 *
 * @return void
 */
	public function testInvalidPermalink() {
		//文字列
		$data['permalink'] = 'hoge';
		$ck = $this->User->invalidPermalink($data);
		$this->assertEqual($ck, true);

		//文字列 %禁止
		$data['permalink'] = 'hoge%hoge';
		$ck = $this->User->invalidPermalink($data);
		$this->assertEqual($ck, false);

		//文字列 スペース禁止
		$data['permalink'] = 'hoge hoge';
		$ck = $this->User->invalidPermalink($data);
		$this->assertEqual($ck, false);

		//文字列 #禁止
		$data['permalink'] = 'hoge&hoge';
		$ck = $this->User->invalidPermalink($data);
		$this->assertEqual($ck, false);

		//文字列 <禁止
		$data['permalink'] = 'hoge<hoge';
		$ck = $this->User->invalidPermalink($data);
		$this->assertEqual($ck, false);

		//文字列 >禁止
		$data['permalink'] = 'hoge>hoge';
		$ck = $this->User->invalidPermalink($data);
		$this->assertEqual($ck, false);

		//文字列 \禁止
		$data['permalink'] = 'hoge\hoge';
		$ck = $this->User->invalidPermalink($data);
		$this->assertEqual($ck, false);

		//文字列 +禁止
		$data['permalink'] = 'hoge+hoge';
		$ck = $this->User->invalidPermalink($data);
		$this->assertEqual($ck, false);

		//文字列 "禁止
		$data['permalink'] = 'hoge"hoge';
		$ck = $this->User->invalidPermalink($data);
		$this->assertEqual($ck, false);

		//文字列 '禁止
		$data['permalink'] = "hoge'hoge";
		$ck = $this->User->invalidPermalink($data);
		$this->assertEqual($ck, false);

		//文字列 &は禁止
		$data['permalink'] = 'hoge&hoge';
		$ck = $this->User->invalidPermalink($data);
		$this->assertEqual($ck, false);

		//文字列 ?は禁止
		$data['permalink'] = 'hoge?hoge';
		$ck = $this->User->invalidPermalink($data);
		$this->assertEqual($ck, false);

		//文字列 .で終わるの禁止
		$data['permalink'] = 'hogehoge.';
		$ck = $this->User->invalidPermalink($data);
		$this->assertEqual($ck, false);

		//文字列 =禁止
		$data['permalink'] = 'hoge=hoge';
		$ck = $this->User->invalidPermalink($data);
		$this->assertEqual($ck, false);

		//文字列 /禁止
		$data['permalink'] = 'hoge/hoge';
		$ck = $this->User->invalidPermalink($data);
		$this->assertEqual($ck, false);

		//文字列 ~禁止
		$data['permalink'] = 'hoge~hoge';
		$ck = $this->User->invalidPermalink($data);
		$this->assertEqual($ck, false);

		//文字列 :禁止
		$data['permalink'] = 'hoge:hoge';
		$ck = $this->User->invalidPermalink($data);
		$this->assertEqual($ck, false);

		//文字列 ;禁止
		$data['permalink'] = 'hoge;hoge';
		$ck = $this->User->invalidPermalink($data);
		$this->assertEqual($ck, false);

		//文字列,禁止
		$data['permalink'] = 'hoge,hoge';
		$ck = $this->User->invalidPermalink($data);
		$this->assertEqual($ck, false);

		//文字列 $禁止
		$data['permalink'] = 'hoge$hoge';
		$ck = $this->User->invalidPermalink($data);
		$this->assertEqual($ck, false);

		//文字列 @禁止
		$data['permalink'] = 'hoge@hoge';
		$ck = $this->User->invalidPermalink($data);
		$this->assertEqual($ck, false);

		//文字列 .から始まるの禁止
		$data['permalink'] = '.hogehoge';
		$ck = $this->User->invalidPermalink($data);
		$this->assertEqual($ck, false);

		//文字列 |禁止
		$data['permalink'] = 'hoge|hoge';
		$ck = $this->User->invalidPermalink($data);
		$this->assertEqual($ck, false);

		//文字列 .]禁止
		$data['permalink'] = 'hoge]hoge';
		$ck = $this->User->invalidPermalink($data);
		$this->assertEqual($ck, false);

		//文字列 .[禁止
		$data['permalink'] = 'hoge[hoge';
		$ck = $this->User->invalidPermalink($data);
		$this->assertEqual($ck, false);

		//文字列 .!禁止
		$data['permalink'] = 'hoge!hoge';
		$ck = $this->User->invalidPermalink($data);
		$this->assertEqual($ck, false);

		//文字列 (禁止
		$data['permalink'] = 'hoge(hoge';
		$ck = $this->User->invalidPermalink($data);
		$this->assertEqual($ck, false);

		//文字列 (禁止
		$data['permalink'] = 'hoge(hoge';
		$ck = $this->User->invalidPermalink($data);
		$this->assertEqual($ck, false);

		//文字列 )禁止
		$data['permalink'] = 'hoge)hoge';
		$ck = $this->User->invalidPermalink($data);
		$this->assertEqual($ck, false);

		//文字列 *禁止
		$data['permalink'] = 'hoge*hoge';
		$ck = $this->User->invalidPermalink($data);
		$this->assertEqual($ck, false);

		//文字列 *禁止
		$data['permalink'] = 'hoge*hoge';
		$ck = $this->User->invalidPermalink($data);
		$this->assertEqual($ck, false);

		//ブランク
		$data['permalink'] = '';
		$ck = $this->User->invalidPermalink($data);
		$this->assertEqual($ck, true);

		//文字列 .はOK
		$data['permalink'] = 'hoge.hoge';
		$ck = $this->User->invalidPermalink($data);
		$this->assertEqual($ck, true);

		//文字列 日本語はOK
		$data['permalink'] = '日本語';
		$ck = $this->User->invalidPermalink($data);
		$this->assertEqual($ck, true);

		//文字列 _OK
		$data['permalink'] = 'hoge_hoge';
		$ck = $this->User->invalidPermalink($data);
		$this->assertEqual($ck, true);

		//文字列 - OK
		$data['permalink'] = 'hoge-hoge';
		$ck = $this->User->invalidPermalink($data);
		$this->assertEqual($ck, true);

		//文字列 大文字小文字OK
		$data['permalink'] = 'hogeHoge';
		$ck = $this->User->invalidPermalink($data);
		$this->assertEqual($ck, true);

		//文字列 数字OK
		$data['permalink'] = 12345678910;
		$ck = $this->User->invalidPermalink($data);
		$this->assertEqual($ck, true);

		$data = 12345678910;
		$ck = $this->User->invalidPermalink($data);
		$this->assertEqual($ck, true);

		//空arrayだった場合はブランクと同じ扱い、true
		$data = array();
		$ck = $this->User->invalidPermalink($data);
		$this->assertEqual($ck, true);

		//文字列も許可 OK
		$data = "AAAAAA";
		$ck = $this->User->invalidPermalink($data);
		$this->assertEqual($ck, true);

		//null もダメ
		$data = null;
		$ck = $this->User->invalidPermalink($data);
		$this->assertEqual($ck, false);

		//boolだめ。
		$data = false;
		$ck = $this->User->invalidPermalink($data);
		$this->assertEqual($ck, false);

		$data = true;
		$ck = $this->User->invalidPermalink($data);
		$this->assertEqual($ck, false);

		//class objectダメ
		$data = $this->User;
		$ck = $this->User->invalidPermalink($data);
		$this->assertEqual($ck, false);
	}

/**
 * testUpdateLastLogin method
 *
 * @return void
 */
	public function testUpdateLastLogin() {
		//既存ユーザ
		$user = $this->User->findById(1);
		$ck = $this->User->updateLastLogin($user);
		$this->assertEqual($ck, true);

		//既存ユーザ
		$user = $this->User->findById(7);
		$ck = $this->User->updateLastLogin($user);
		$this->assertEqual($ck, true);

		//パラメータ異常
		$user = array();
		$ck = $this->User->updateLastLogin($user);
		$this->assertEqual($ck, false);

		//パラメータ異常　必須項目がない状態。
		$user = $this->User->findById(7);
		unset( $user['User']['last_login']); //必須項目
		$ck = $this->User->updateLastLogin($user);
		$this->assertEqual($ck, false);

		//$userが文字列（パラメータ異常）
		$ck = $this->User->updateLastLogin('AAAA');
		$this->assertEqual($ck, false);

		//$userが空array（パラメータ異常）
		$ck = $this->User->updateLastLogin(array());
		$this->assertEqual($ck, false);

		//$userが数字（パラメータ異常）
		$ck = $this->User->updateLastLogin(1);
		$this->assertEqual($ck, false);

		//$userがブランク（パラメータ異常）
		$ck = $this->User->updateLastLogin("");
		$this->assertEqual($ck, false);

		//$userがnull（パラメータ異常）
		$ck = $this->User->updateLastLogin(null);
		$this->assertEqual($ck, false);
	}

/**
 * testCurrentUser method
 *
 * @return void
 */
	public function testCurrentUser() {
		//マイポータル、マイルーム以外ならば
		$centerPage = array(
			'Page' => array(
				'id' => 9,
				'root_id' => 9,
				'parent_id' => 1,
				'thread_num' => 1,
				'display_sequence' => 0,
				'page_name' => 'Public room',
				'permalink' => '',
				'position_flag' => 1,
				'lang' => '',
				'is_page_meta_node' => 0,
				'is_page_style_node' => 0,
				'is_page_layout_node' => 0,
				'is_page_theme_node' => 0,
				'is_page_column_node' => 0,
				'room_id' => 9,
				'space_type' => NC_SPACE_TYPE_GROUP,
				'show_count' => 0,
				'display_flag' => 1,
				'display_from_date' => null,
				'display_to_date' => null,
				'display_apply_subpage' => 1,
				'display_reverse_permalink' => null,
				'is_approved' => 1,
				'lock_authority_id' => 0,
				'created' => null,
				'created_user_id' => 1,
				'created_user_name' => '',
				'modified' => null,
				'modified_user_id' => 1,
				'modified_user_name' => ''
			)
		);
		$ck = $this->User->currentUser($centerPage);
		$this->assertEqual('', $ck);

		//マイポータルの場合
		//User.permalinkとPage.permarinkが同じデータがあった場合
		$centerPage = array(
			'Page' => array(
				'id' => 9,
				'root_id' => 9,
				'parent_id' => 1,
				'thread_num' => 1,
				'display_sequence' => 0,
				'page_name' => 'my portal',
				'permalink' => 'admin',
				'position_flag' => 1,
				'lang' => '',
				'is_page_meta_node' => 0,
				'is_page_style_node' => 0,
				'is_page_layout_node' => 0,
				'is_page_theme_node' => 0,
				'is_page_column_node' => 0,
				'room_id' => 9,
				'space_type' => NC_SPACE_TYPE_MYPORTAL,
				'show_count' => 0,
				'display_flag' => 1,
				'display_from_date' => null,
				'display_to_date' => null,
				'display_apply_subpage' => 1,
				'display_reverse_permalink' => null,
				'is_approved' => 1,
				'lock_authority_id' => 0,
				'created' => null,
				'created_user_id' => 1,
				'created_user_name' => '',
				'modified' => null,
				'modified_user_id' => 1,
				'modified_user_name' => ''
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
				'avatar' => null,
				'activate_key' => null,
				'lang' => 'ja',
				'timezone_offset' => '9',
				'email' => '',
				'mobile_email' => '',
				'password_regist' => null,
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
		$this->assertEqual($ck, $ans);

		//マイポータルの場合
		//User.permalinkとPage.permarinkが同じデータがあった場合
		//permalinkの/区切りの最初の部分が合致している
		$centerPage = array(
			'Page' => array(
				'id' => 9,
				'root_id' => 9,
				'parent_id' => 1,
				'thread_num' => 3,
				'display_sequence' => 0,
				'page_name' => 'my portal',
				'permalink' => 'admin/hoge/hoge',
				'position_flag' => 1,
				'lang' => '',
				'is_page_meta_node' => 0,
				'is_page_style_node' => 0,
				'is_page_layout_node' => 0,
				'is_page_theme_node' => 0,
				'is_page_column_node' => 0,
				'room_id' => 9,
				'space_type' => NC_SPACE_TYPE_MYPORTAL,
				'show_count' => 0,
				'display_flag' => 1,
				'display_from_date' => null,
				'display_to_date' => null,
				'display_apply_subpage' => 1,
				'display_reverse_permalink' => null,
				'is_approved' => 1,
				'lock_authority_id' => 0,
				'created' => null,
				'created_user_id' => 1,
				'created_user_name' => '',
				'modified' => null,
				'modified_user_id' => 1,
				'modified_user_name' => ''
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
				'avatar' => null,
				'activate_key' => null,
				'lang' => 'ja',
				'timezone_offset' => '9',
				'email' => '',
				'mobile_email' => '',
				'password_regist' => null,
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
		$this->assertEqual($ck, $ans);

		//　/からなので、permalinkの1つめはブランクが格納されている状態になっている。
		$centerPage = array('Page' => array(
			'id' => 1,
			'root_id' => 1,
			'parent_id' => 1,
			'thread_num' => 1,
			'display_sequence' => 1,
			'page_name' => 'Public room',
			'permalink' => '/', // /だけが登録される場合はない。データ不整合な状態。
			'position_flag' => 1,
			'lang' => '',
			'is_page_meta_node' => 0,
			'is_page_style_node' => 0,
			'is_page_layout_node' => 0,
			'is_page_theme_node' => 0,
			'is_page_column_node' => 0,
			'room_id' => 100,
			'space_type' => NC_SPACE_TYPE_MYPORTAL,
			'show_count' => 0,
			'display_flag' => 1,
			'display_from_date' => null,
			'display_to_date' => null,
			'display_apply_subpage' => 1,
			'display_reverse_permalink' => null,
			'is_approved' => 1,
			'lock_authority_id' => 0,
			'created' => null,
			'created_user_id' => 1,
			'created_user_name' => 'admin',
			'modified' => null,
			'modified_user_id' => 0,
			'modified_user_name' => ''
		));
		$ck = $this->User->currentUser($centerPage);
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
				'avatar' => null,
				'activate_key' => null,
				'lang' => 'ja',
				'timezone_offset' => '9',
				'email' => '',
				'mobile_email' => '',
				'password_regist' => null,
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
		$this->assertEqual($ck, $ans);

		//空array （パラメータ異常）
		$centerPage = array();
		$ck = $this->User->currentUser($centerPage);
		$this->assertEqual(false, $ck);

		//文字列 （パラメータ異常）
		$centerPage = 'hogefuga';
		$ck = $this->User->currentUser($centerPage);
		$this->assertEqual(false, $ck);

		//blank （パラメータ異常）
		$centerPage = '';
		$ck = $this->User->currentUser($centerPage);
		$this->assertEqual(false, $ck);
	}

/**
 * testFindParticipant method
 *
 * @return void
 */
	public function testFindParticipant() {
		//TODO 一旦後回し。
		//このメソッドは本当にModel/Userが適切？
		$Page = ClassRegistry::init('Page');

		//Page.id:9のデータで取得
		$page = $Page->findById(9);
		$participantType = NC_PARTICIPANT_TYPE_DEFAULT_ENABLED;
		$conditions = array();
		$joins = array();
		$startPage = 1;
		$limit = 30;
		$sortname = 'chief';
		$sortorder = 'DESC';

		$ck = $this->User->findParticipant(
			$page,
			$participantType,
			$conditions,
			$joins,
			$startPage,
			$limit,
			$sortname,
			$sortorder
		);

		$ans = array (
			0 => 3,
			1 =>
			array (
				0 =>
				array (
					'PageUserLink' =>
					array (
						'id' => '1',
						'user_id' => '1',
						'authority_id' => '2',
					),
					'Page' =>
					array (
						'space_type' => '1',
						'root_id' => '9',
					),
					'User' =>
					array (
						'id' => '1',
						'handle' => 'admin',
						'authority_id' => '1',
					),
					'PageAuthority' =>
					array (
						'id' => '2',
						'hierarchy' => '350',
					),
					'Authority' =>
					array (
						'id' => '1',
						'display_participants_editing' => true,
						'hierarchy' => '500',
					),
					'Community' =>
					array (
						'publication_range_flag' => null,
						'participate_force_all_users' => null,
						'participate_flag' => null,
					),
					'AuthorityParent' =>
					array (
						'id' => null,
						'hierarchy' => null,
					),
				),
				1 =>
				array (
					'PageUserLink' =>
					array (
						'id' => null,
						'user_id' => null,
						'authority_id' => null,
					),
					'Page' =>
					array (
						'space_type' => null,
						'root_id' => null,
					),
					'User' =>
					array (
						'id' => '2',
						'handle' => 'admin',
						'authority_id' => '1',
					),
					'PageAuthority' =>
					array (
						'id' => null,
						'hierarchy' => null,
					),
					'Authority' =>
					array (
						'id' => '1',
						'display_participants_editing' => true,
						'hierarchy' => '500',
					),
					'Community' =>
					array (
						'publication_range_flag' => null,
						'participate_force_all_users' => null,
						'participate_flag' => null,
					),
					'AuthorityParent' =>
					array (
						'id' => null,
						'hierarchy' => null,
					),
				),
				2 =>
				array (
					'PageUserLink' =>
					array (
						'id' => null,
						'user_id' => null,
						'authority_id' => null,
					),
					'Page' =>
					array (
						'space_type' => null,
						'root_id' => null,
					),
					'User' =>
					array (
						'id' => '7',
						'handle' => 'admin',
						'authority_id' => '1',
					),
					'PageAuthority' =>
					array (
						'id' => null,
						'hierarchy' => null,
					),
					'Authority' =>
					array (
						'id' => '1',
						'display_participants_editing' => true,
						'hierarchy' => '500',
					),
					'Community' =>
					array (
						'publication_range_flag' => null,
						'participate_force_all_users' => null,
						'participate_flag' => null,
					),
					'AuthorityParent' =>
					array (
						'id' => null,
						'hierarchy' => null,
					),
				),
			),
		);
		$this->assertEqual($ck, $ans);

		//$page 以外はoptionなので同じ値になる。
		$ck = $this->User->findParticipant($page);
		$ans = array (
			0 => 3,
			1 =>
			array (
				0 =>
				array (
					'PageUserLink' =>
					array (
						'id' => '1',
						'user_id' => '1',
						'authority_id' => '2',
					),
					'Page' =>
					array (
						'space_type' => '1',
						'root_id' => '9',
					),
					'User' =>
					array (
						'id' => '1',
						'handle' => 'admin',
						'authority_id' => '1',
					),
					'PageAuthority' =>
					array (
						'id' => '2',
						'hierarchy' => '350',
					),
					'Authority' =>
					array (
						'id' => '1',
						'display_participants_editing' => true,
						'hierarchy' => '500',
					),
					'Community' =>
					array (
						'publication_range_flag' => null,
						'participate_force_all_users' => null,
						'participate_flag' => null,
					),
					'AuthorityParent' =>
					array (
						'id' => null,
						'hierarchy' => null,
					),
				),
				1 =>
				array (
					'PageUserLink' =>
					array (
						'id' => null,
						'user_id' => null,
						'authority_id' => null,
					),
					'Page' =>
					array (
						'space_type' => null,
						'root_id' => null,
					),
					'User' =>
					array (
						'id' => '2',
						'handle' => 'admin',
						'authority_id' => '1',
					),
					'PageAuthority' =>
					array (
						'id' => null,
						'hierarchy' => null,
					),
					'Authority' =>
					array (
						'id' => '1',
						'display_participants_editing' => true,
						'hierarchy' => '500',
					),
					'Community' =>
					array (
						'publication_range_flag' => null,
						'participate_force_all_users' => null,
						'participate_flag' => null,
					),
					'AuthorityParent' =>
					array (
						'id' => null,
						'hierarchy' => null,
					),
				),
				2 =>
				array (
					'PageUserLink' =>
					array (
						'id' => null,
						'user_id' => null,
						'authority_id' => null,
					),
					'Page' =>
					array (
						'space_type' => null,
						'root_id' => null,
					),
					'User' =>
					array (
						'id' => '7',
						'handle' => 'admin',
						'authority_id' => '1',
					),
					'PageAuthority' =>
					array (
						'id' => null,
						'hierarchy' => null,
					),
					'Authority' =>
					array (
						'id' => '1',
						'display_participants_editing' => true,
						'hierarchy' => '500',
					),
					'Community' =>
					array (
						'publication_range_flag' => null,
						'participate_force_all_users' => null,
						'participate_flag' => null,
					),
					'AuthorityParent' =>
					array (
						'id' => null,
						'hierarchy' => null,
					),
				),
			),
		);
		$this->assertEqual($ck, $ans);

		//TODO : $participantType の値別の値の調査
		$page = $Page->findById(16);
		$participantType = 0; //参加者のみ表示
		$ck = $this->User->findParticipant(
			$page,
			$participantType,
			$conditions,
			$joins,
			$startPage,
			$limit,
			$sortname,
			$sortorder
		);
		//var_export($ck);
		//TODO Noticeエラー対応
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
		//TODO : 実装検討
		//既存ユーザ
		//$ck = $this->User->deleteUser(1);
		//$this->assertEqual($ck, true);

		//既存ユーザ false?　なぜ？
		//$ck = $this->User->deleteUser(7);
		//$this->assertEqual($ck, true);

		//存在しないユーザ
		//$ck = $this->User->deleteUser(999999999999);
		//$this->assertEqual($ck, true);

		//文字列
		//$ck = $this->User->deleteUser("AAAAAAAAAA");
		//$this->assertEqual($ck, true);

		//空配列
		//$ck = $this->User->deleteUser(array());
		//$this->assertEqual($ck, true);

		//var_export($ck);
	}

/**
 * testConvertAvatarDisplay method
 *
 * @return void
 */
	public function testConvertAvatarDisplay() {
	}
}
