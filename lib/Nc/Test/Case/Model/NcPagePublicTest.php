<?php
/**
 * Page Test Case
 *
 */
class NcPagePublicTest extends CakeTestCase {
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

	private $PagePublic = null;
	private $Page = null;

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$this->PagePublic = ClassRegistry::init('PagePublic');
		$this->Page = ClassRegistry::init('Page');

	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->PagePublic);
		unset($this->Page);
		parent::tearDown();
		Configure::clear(NC_SYSTEM_KEY.'.user');
	}

	public function testGetDefault() {
		$ans = array (
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
		);
		$ck = $this->PagePublic->getDefault();
		$this->assertEqual($ck , $ans);
	}

	public function testSetUserId() {
		//存在するユーザをセットする。
		$ck=$this->PagePublic->setUserId(1);
		$this->assertEqual(true , $ck);

		//存在しないユーザをセットする
		$ck = $this->PagePublic->setUserId(999999999999);
		$this->assertEqual(false , $ck);

		//文字列をセットする（パラメータエラー）
		$ck = $this->PagePublic->setUserId('AAAAAAAA');
		$this->assertEqual(false , $ck);

		//配列をセットする（パラメータエラー
		$ck = $this->PagePublic->setUserId(array(1,2,3));
		$this->assertEqual(false , $ck);

	}

	public function testArrayKeyChangeId() {
		//Page Behaviorで共通化されている
		$ck = $this->PagePublic->find('all');
		$this->assertEqual(true , isset($ck[0]));
		$ck = $this->PagePublic->arrayKeyChangeId($ck);
		$this->assertEqual(false , isset($ck[0]));
	}




}