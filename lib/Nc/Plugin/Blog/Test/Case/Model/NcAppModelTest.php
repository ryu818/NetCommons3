<?php
App::uses('AppModel', 'Model');

/**
 * AppModel Test Case
 *
 */
class NcAppModelTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */

	public $fixtures = array(
		'NcApp'
	);


/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->AppModel = ClassRegistry::init('AppModel');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->AppModel);
		parent::tearDown();
	}

	/**
	 *
	 */
	public function testHoge()
	{
		$true = true;
	  	$this->assertTrue($true);
	}

}
