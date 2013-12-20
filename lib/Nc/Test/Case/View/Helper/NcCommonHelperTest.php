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
		Configure::write('App' ,
			array (
				'imageBaseUrl' => 'img/',
				'cssBaseUrl' => 'css/',
				'jsBaseUrl' => 'js/',
				'base' => false,
				'baseUrl' => false,
				'dir' => 'app',
				'webroot' => 'webroot',
				'www_root' => '/vagrant_data/app/webroot/',
				'encoding' => 'UTF-8',
				'fullBaseUrl' => 'http://localhost',
			)
		);
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
