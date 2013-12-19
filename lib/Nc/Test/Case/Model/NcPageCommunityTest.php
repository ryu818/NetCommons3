<?php
/**
 * Page Test Case
 *
 */
class NcPageCommunityTest extends CakeTestCase {
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

	private $PageCommunity = null;

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		$this->PageCommunity = ClassRegistry::init('PageCommunity');

	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->PageCommunity);
		parent::tearDown();
		Configure::clear(NC_SYSTEM_KEY.'.user');
	}

	public function testGetDefault()
	{
		$ans = array (
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
		);
		$ck = $this->PageCommunity->getDefault();
		$this->assertEqual($ck , $ans);
	}
}