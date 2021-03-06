<?php
App::uses('CommonBehavior', 'Model/Behavior');
App::uses('User', 'Model');
/**
 * CommonBehavior Test Case
 *
 */
class NcCommonBehaviorTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'NcUser'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->Common = new CommonBehavior();

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
 * testDecrementSeq method
 *
 * @return void
 */
	public function testDecrementSeq() {

		$model = new User;
		$model->useDbConfig = 'test';
		$scope = '1';
		$targetColname = 'modified_user_id';
		$ck  = $this->Common->decrementSeq($model, $scope, $targetColname);
		$this->assertEqual(true , $ck);
		//sqlに modified_user_id+-1が含まれていることを確認
		$sql = $this->Common->getSqlInfo($model , 'test');
		$sql_text = '/modified_user_id\+-1/';
		$this->assertEqual(true , preg_match($sql_text , $sql['query']));

		$model = new User;
		$model->useDbConfig = 'test';
		$scope = '2';
		$targetColname = 'modified_user_id';
		$ck  = $this->Common->decrementSeq($model, $scope, $targetColname);
		$this->assertEqual(true , $ck);
		//sqlにmodified_user_id+-1が含まれていることを確認
		$sql = $this->Common->getSqlInfo($model , 'test');
		$sql_text = '/modified_user_id\+-1/';
		$this->assertEqual(true , preg_match($sql_text , $sql['query']));
	}

/**
 * testIncrementSeq method
 *
 * @return void
 */
	public function testIncrementSeq() {
		$model = new User;
		$model->useDbConfig = 'test';
		$scope = '1';
		$targetColname = 'modified_user_id';
		$ck  = $this->Common->incrementSeq($model, $scope, $targetColname);
		$this->assertEqual(true , $ck);

		//sqlにmodified_user_id+1が含まれていることを確認
		$sql = $this->Common->getSqlInfo($model , 'test');
		$sql_text = '/modified_user_id\+1/';
		$this->assertEqual(true , preg_match($sql_text , $sql['query']));

		$scope = '2';
		$targetColname = 'modified_user_id';
		$ck  = $this->Common->incrementSeq($model, $scope, $targetColname);
		$this->assertEqual(true , $ck);

		//sqlにmodified_user_id+1が含まれていることを確認
		$sql = $this->Common->getSqlInfo($model , 'test');
		$sql_text = '/modified_user_id\+1/';
		$this->assertEqual(true , preg_match($sql_text , $sql['query']));


	}

/**
 * testVoting method
 *
 * @return void
 */
	public function testVoting() {
		//TODO : Plugin/Blog で Model/BlogPost 経由でテストコードを用意する
	}

/**
 * testGetSqlInfo method
 *
 * @return void
 */
	public function testGetSqlInfo() {

		$model = new User;
		$model->useDbConfig = 'test';
		$scope = '1';
		$targetColname = 'modified_user_id';
		$ck  = $this->Common->incrementSeq($model, $scope, $targetColname);
		$this->assertEqual(true , $ck);

		$sql = $this->Common->getSqlInfo($model , 'test');

		//sqlにmodified_user_id+1が含まれていることを確認
		$sql_text = '/modified_user_id\+1/';
		$this->assertEqual(true , preg_match($sql_text , $sql['query']));
	}

}
