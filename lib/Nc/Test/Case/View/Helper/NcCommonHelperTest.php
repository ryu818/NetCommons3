<?php
App::uses('View', 'View');
App::uses('Helper', 'View');
App::uses('CommonHelper', 'View/Helper');

/**
 * CommonHelper Test Case
 * lib/Nc以下のテストなのでNC_をプレフィックスとして使用。
 */
class NcCommonHelperTest extends CakeTestCase {

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$View = new View();
		$this->Common = new CommonHelper($View);
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Common);

		parent::tearDown();
	}

/**
 * testExplodeControllerAction method
 *
 * @return void
 */
	public function testExplodeControllerAction() {
	}

}
