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

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$this->PagePublic = ClassRegistry::init('PagePublic');

	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->PagePublic);
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
}