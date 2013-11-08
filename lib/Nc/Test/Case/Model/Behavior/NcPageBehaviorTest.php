<?php
App::uses('PageBehavior', 'Model/Behavior');
App::uses('Page', 'Model');

/**
 * PageBehavior Test Case
 *
 */
class NcPageBehaviorTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'NcPage',
		'NcUser'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->Page = new PageBehavior();
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
 * testSetPageName method
 *
 * @return void
 */
	public function testSetPageName() {
		/*
		$model = new Page;
		$model->useDbConfig = 'test';
		$page = $model->find('first' , array('conditions'=>array('id'=>1)));
		$ck = $this->Page->setPageName($model, $page);
		*/
	}

/**
 * testGetPermalink method
 *
 * @return void
 */
	public function testGetPermalink() {
		$model = new Page;
		$model->useDbConfig = 'test';

		$ck = $this->Page->getPermalink($model , 'hogehoge', NC_SPACE_TYPE_PUBLIC);
		$this->assertEqual('' , NC_SPACE_PUBLIC_PREFIX); //ブランクなので、hogehoge/になる
		$this->assertEqual(NC_SPACE_PUBLIC_PREFIX .'hogehoge'.'/' , $ck);

		$ck = $this->Page->getPermalink($model , 'hogehoge', NC_SPACE_TYPE_MYPORTAL);
		$this->assertEqual(NC_SPACE_MYPORTAL_PREFIX . '/'.'hogehoge'.'/' , $ck);

		$ck = $this->Page->getPermalink($model , 'hogehoge', NC_SPACE_TYPE_PRIVATE);
		$this->assertEqual(NC_SPACE_PRIVATE_PREFIX . '/'.'hogehoge'.'/' , $ck);

		// NC_SPACE_TYPE_PUBLIC　 NC_SPACE_TYPE_MYPORTAL　NC_SPACE_TYPE_PRIVATE以外のものはすべてNC_SPACE_GROUP_PREFIXがつく。
		$ck = $this->Page->getPermalink($model , 'hogehoge', 9999999999);
		$this->assertEqual(NC_SPACE_GROUP_PREFIX .'/'. 'hogehoge'.'/' , $ck);

		$ck = $this->Page->getPermalink($model , 'hogehoge', -1);
		$this->assertEqual(NC_SPACE_GROUP_PREFIX .'/'. 'hogehoge'.'/' , $ck);

		$ck = $this->Page->getPermalink($model , 'hogehoge', 'AAA');
		$this->assertEqual(NC_SPACE_GROUP_PREFIX .'/'. 'hogehoge'.'/' , $ck);

		//最後に/がついている場合は、二重になっちゃう。
		$ck = $this->Page->getPermalink($model , 'hogehoge/', 'AAA');
		$this->assertEqual(NC_SPACE_GROUP_PREFIX .'/'. 'hogehoge'.'//' , $ck);


	}

/**
 * testUpdateDisplayFlag method
 *
 * @return void
 */
	public function testUpdateDisplayFlag() {
	}

}
