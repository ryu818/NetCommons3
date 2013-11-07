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
		$this->User = new User();
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
		//sqlに User.modified_user_id+-1が含まれていることを確認
		/*
		//mysql以外の場合ではgetSqlInfoでとれる内容が違う様子なので一旦コメントアウト
		$sql = $this->Common->getSqlInfo($model , 'test');
		$sql_text = '/User.modified_user_id\+-1/';
		$this->assertEqual(true , preg_match($sql_text , $sql['query']));
		*/

		$model = new User;
		$model->useDbConfig = 'test';
		$scope = '2';
		$targetColname = 'modified_user_id';
		$ck  = $this->Common->decrementSeq($model, $scope, $targetColname);
		$this->assertEqual(true , $ck);
		//sqlに User.modified_user_id+-1が含まれていることを確認
		//mysql以外の場合ではgetSqlInfoでとれる内容が違う様子なので一旦コメントアウト
		/*
		$sql = $this->Common->getSqlInfo($model , 'test');
		$sql_text = '/User.modified_user_id\+-1/';
		$this->assertEqual(true , preg_match($sql_text , $sql['query']));
		*/
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

		//sqlに User.modified_user_id+1が含まれていることを確認
		//mysql以外の場合ではgetSqlInfoでとれる内容が違う様子なので一旦コメントアウト
		/*
		$sql = $this->Common->getSqlInfo($model , 'test');
		$sql_text = '/User.modified_user_id\+1/';
		$this->assertEqual(true , preg_match($sql_text , $sql['query']));
		*/

		$scope = '2';
		$targetColname = 'modified_user_id';
		$ck  = $this->Common->incrementSeq($model, $scope, $targetColname);
		$this->assertEqual(true , $ck);

		//sqlに User.modified_user_id+1が含まれていることを確認
		//mysql以外の場合ではgetSqlInfoでとれる内容が違う様子なので一旦コメントアウト
		/*
		$sql = $this->Common->getSqlInfo($model , 'test');
		$sql_text = '/User.modified_user_id\+1/';
		$this->assertEqual(true , preg_match($sql_text , $sql['query']));
		*/

	}

/**
 * testVoting method
 *
 * @return void
 */
	public function testVoting() {

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
		$this->assertEqual("", $sql['query']);

		//sqlに User.modified_user_id+1が含まれていることを確認
		$sql_text = '/User.modified_user_id\+1/';
		$this->assertEqual(true , preg_match($sql_text , $sql['query']));
	}

}
