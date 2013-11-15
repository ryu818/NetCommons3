<?php
App::uses('Page', 'Model');
App::uses('PageTree' , 'Model');

/**
 * Page Test Case
 *
 */
class NcPageTreeTest extends CakeTestCase {
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
		$this->PageTree = ClassRegistry::init('PageTree');


	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->PageTree);

		parent::tearDown();
	}

	public function testHoge()
	{

	}

}