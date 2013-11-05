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
		$ck = $model->find('first' ,
			array(
				'conditions'=>array("id"=>1)
			)
		);
		//1つ減った値になる
		$this->assertEqual( 0, $ck['User']['modified_user_id']);

		$model = new User;
		$model->useDbConfig = 'test';
		$scope = '1';
		$targetColname = 'modified_user_id';
		$ck  = $this->Common->decrementSeq($model, $scope, $targetColname);
		$this->assertEqual(true , $ck);
		$ck = $model->find('first' ,
			array(
				'conditions'=>array("id"=>1)
			)
		);
		//0から1つ減った値になる
		$this->assertEqual( -1, $ck['User']['modified_user_id']);
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
		$ck = $model->find('first' ,
			array(
				'conditions'=>array("id"=>1)
			)
		);
		//1つ増えた値になる
		$this->assertEqual( 2, $ck['User']['modified_user_id']);

		$model = new User;
		$model->useDbConfig = 'test';
		$scope = '1';
		$targetColname = 'modified_user_id';
		$ck  = $this->Common->incrementSeq($model, $scope, $targetColname);
		$this->assertEqual(true , $ck);
		$ck = $model->find('first' ,
			array(
				'conditions'=>array("id"=>1)
			)
		);
		//1つ増えた値になる
		$this->assertEqual( 3, $ck['User']['modified_user_id']);

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
		$ck = $model->find('first' ,
			array(
				'conditions'=>array("id"=>1)
			)
		);
		$sql = $this->Common->getSqlInfo($model , 'test');
		$sql_text = 'SELECT `User`.`id`, `User`.`login_id`, `User`.`password`, `User`.`handle`, `User`.`authority_id`, `User`.`is_active`, `User`.`permalink`, `User`.`myportal_page_id`, `User`.`private_page_id`, `User`.`avatar`, `User`.`activate_key`, `User`.`lang`, `User`.`timezone_offset`, `User`.`email`, `User`.`mobile_email`, `User`.`password_regist`, `User`.`last_login`, `User`.`previous_login`, `User`.`created`, `User`.`created_user_id`, `User`.`created_user_name`, `User`.`modified`, `User`.`modified_user_id`, `User`.`modified_user_name` FROM `netcommons3_test`.`nc3_users` AS `User`   WHERE `id` = 1    LIMIT 1';
		$this->assertEqual($sql_text , $sql['query']);

	}

}
