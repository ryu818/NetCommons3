<?php
App::uses('Page', 'Model');
App::uses('PageTree' , 'Model');

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
		'NcPageTree'
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
	public function testFindDefault() {

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

		$ck = $this->Page->findDefault(NC_SPACE_TYPE_PUBLIC);
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
		$ck = $this->Page->findDefault(NC_SPACE_TYPE_MYPORTAL);
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
		$ck = $this->Page->findDefault(NC_SPACE_TYPE_PRIVATE );
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
		$ck = $this->Page->findDefault(NC_SPACE_TYPE_GROUP);
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
