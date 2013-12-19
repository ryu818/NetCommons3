<?php
/**
 * Page Test Case
 *
 */
class NcPageMyPortalTest extends CakeTestCase {
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

	private $PageMyPortal = null;

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$this->PageMyPortal = ClassRegistry::init('PageMyPortal');

	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->PageMyPortal);
		parent::tearDown();
		Configure::clear(NC_SYSTEM_KEY.'.user');
	}

	function testGetDefault()
	{
		$ans = array (
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
		);
		$ck = $this->PageMyPortal->getDefault();
		$this->assertEqual($ck , $ans);

	}

	public function testSetUserId() {
		//存在するユーザをセットする。
		$ck=$this->PageMyPortal->setUserId(1);
		$this->assertEqual(true , $ck);

		//存在しないユーザをセットする
		$ck = $this->PageMyPortal->setUserId(999999999999);
		$this->assertEqual(false , $ck);

		//文字列をセットする（パラメータエラー）
		$ck = $this->PageMyPortal->setUserId('AAAAAAAA');
		$this->assertEqual(false , $ck);

		//配列をセットする（パラメータエラー
		$ck = $this->PageMyPortal->setUserId(array(1,2,3));
		$this->assertEqual(false , $ck);

	}


	function testGetRoomList() {
		$ck = $this->PageMyPortal->getRoomList(1);
		$this->assertEqual(count($ck) , 1);
		$this->assertEqual(10 , $ck[10]['PageMyPortal']['id']);
		$this->assertEqual(10 , $ck[10]['PageMyPortal']['room_id']);

		$ck = $this->PageMyPortal->getRoomList(999999999);
		$this->assertEqual($ck , array()) ;

		$ck = $this->PageMyPortal->getRoomList('AAAAAAAA');
		$this->assertEqual($ck , array()) ;
		$ck = $this->PageMyPortal->getRoomList(array(1,2,3));
		$this->assertEqual($ck , array()) ;
	}


	function testAddTopRoom() {
		$startCount = $this->PageMyPortal->find('count');
		$ck = $this->PageMyPortal->addTopRoom(1);
		$id = $ck; //MyPortalのpageのid
		$endCount = $this->PageMyPortal->find('count');
		$this->assertEqual(true , is_numeric($ck));
		$this->assertEqual($startCount + 2 , $endCount);

		$ck = $this->PageMyPortal->findById($id);
		$ck2 = $this->PageMyPortal->findById($id+1);
		$key = 'PageMyPortal';
		$this->assertEqual($ck[$key]['room_id'] , $id);
		$this->assertEqual($ck[$key]['page_name'] , 'Myportal');
		$this->assertEqual($ck2[$key]['page_name'] , 'Myportal Top');
		$this->assertEqual($ck2[$key]['root_id'] , $id);

		//roomにPageUserLinkが紐付いたかどうか確認する。
		$PageUserLink = ClassRegistry::init("PageUserLink");
		$key    = $PageUserLink->alias;
		$ck     = $PageUserLink->find(
			'count',
			array(
				'conditions'=>array(
					"{$key}.room_id"=>$id,
					"{$key}.user_id"=>1
				)
			)
		);
		//1件
		$this->assertEqual($ck , 1);
	}




}