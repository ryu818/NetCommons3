<?php
App::uses('TimeZoneBehavior', 'Model/Behavior');
App::uses('Page', 'Model/');

/**
 * TimeZoneBehavior Test Case
 *
 */
class NcTimeZoneBehaviorTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'NcUser',
		'NcPage'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->TimeZone = new TimeZoneBehavior();
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->TimeZone);

		parent::tearDown();
	}

/**
 * testNowDate method
 *
 * @return void
 */
	public function testNowDate() {
		$model = new User;
		$model->useDbConfig = 'test';
		$ans = date("Y-m-d");
		$format = 'Y-m-d';
		$ck = $this->TimeZone->nowDate($model , $format);
		$this->assertEqual($ans , $ck);

		//フォーマット未指定の場合は、Y-m-d H:i:sで戻る。
		$ck = $this->TimeZone->nowDate($model);
		$this->assertEqual(date("Y-m-d H:i:s" , strtotime($ck)) , $ck);

		$ck = $this->TimeZone->nowDate($model , "Y-m-d");
		$this->assertEqual(date("Y-m-d" , strtotime($ck)) , $ck);

		$ck = $this->TimeZone->nowDate($model , "Ymd");
		$this->assertEqual(date("Ymd" , strtotime($ck)) , $ck);

		//formatに指定できない文字列 文字列がそのまま戻る。
		$ck = $this->TimeZone->nowDate($model , 'QQ');
		$this->assertEqual('QQ' , $ck);

	}

/**
 * testDate method
 *
 * @return void
 */
	public function testDate() {
		$model = new User;
		$model->useDbConfig = 'test';
		$ans = '2013-11-06 06:11:23';
		$timeUtc = '20131106061123';
		$format = 'Y-m-d H:i:s';
		//timeUtc指定
		$ck = $this->TimeZone->date($model , $timeUtc);
		$this->assertEqual($ans , $ck);
		//フォーマット指定
		$ck = $this->TimeZone->date($model , $timeUtc , $format);
		$this->assertEqual($ans , $ck);

		//フォーマットの変更
		$format = 'YmdHis';
		$ck = $this->TimeZone->date($model , $timeUtc , $format);
		$this->assertEqual($timeUtc , $ck);

		//フォーマットの変更
		$format = 'Ymd';
		$ck = $this->TimeZone->date($model , NULL , $format);
		$this->assertEqual(date($format) , $ck);

		//2013.11.31 ありえない日付を指定。正しい日付に変換される。
		$ck = $this->TimeZone->date($model , '20131131061123');
		$this->assertEqual('2013-12-01 06:11:23' , $ck);

		//2013.11.31 25:11:23 日付は補正されるが、25時を指定した場合はエラー
		//時間がとれないので、1970-01-01 00:00:00で戻る。
		$ck = $this->TimeZone->date($model , '20131131251123');
		$this->assertEqual(null , $ck);
		//時刻として認識されないフォーマット名ので、1970-01-01 00:00:00で戻る
		$ck = $this->TimeZone->date($model , '2013.12.01 06:11:AA');
		$this->assertEqual(null , $ck);

		$ck = $this->TimeZone->date($model , '2013-12-01 06:11:23');
		$this->assertEqual('2013-12-01 06:11:23' , $ck);

		$ck = $this->TimeZone->date($model , '2013/12/01 06:11:23');
		$this->assertEqual('2013-12-01 06:11:23' , $ck);
	}

/**
 * testDateUtc method
 *
 * @return void
 */
	public function testDateUtc() {

		$model = new User;
		$model->useDbConfig = 'test';
		$ck = $this->TimeZone->dateUtc($model , date("Y-m-d H:i:s") , 'Y-m-d');
		$this->assertEqual(date("Y-m-d") , $ck);

		$time = '2013/12/1 16:56:00';
		$ans  = "2013-12-01";
		$ck = $this->TimeZone->dateUtc($model , $time , 'Y-m-d');
		$this->assertEqual($ans , $ck);

		$time = '20000701';
		$ans  = "2000-07-01";
		$ck = $this->TimeZone->dateUtc($model , $time , 'Y-m-d');
		$this->assertEqual($ans , $ck);

		//日付のフォーマットでは無いので、1970-01-01が戻る
		$time = '2013.12.1 16:56:00';
		$ans  = null;
		$ck = $this->TimeZone->dateUtc($model , $time , 'Y-m-d');
		$this->assertEqual($ans , $ck);
		//日付のフォーマットでは無いので、1970-01-01が戻る
		$time = '2013-12-1 AA:56:00';
		$ans  = null;
		$ck = $this->TimeZone->dateUtc($model , $time , 'Y-m-d');
		$this->assertEqual($ans , $ck);
	}

/**
 * testIsFutureDateTime method
 *
 * @return void
 */
	public function testIsFutureDateTime() {
		//(Model $Model, $data, $gm = true)
		$model = new User;
		$model->useDbConfig = 'test';
		//未来日・
		$ck = $this->TimeZone->isFutureDateTime($model , array(date('Y-m-d H:i:s' , time() + 60)));
		$this->assertEqual(true , $ck);
		//過去日
		$ck = $this->TimeZone->isFutureDateTime($model , array(date('Y-m-d H:i:s' , time() - 60)));
		$this->assertEqual(false , $ck);
		//不正な値だった場合 :処理中に"1970-01-01"の値になるため、過去日として判定される。
		$ck = $this->TimeZone->isFutureDateTime($model , array('AAAAAAAA'));
		$this->assertEqual(false , $ck);

		//配列じゃなくても判定する
		//未来日・
		$ck = $this->TimeZone->isFutureDateTime($model , date('Y-m-d H:i:s' , time() + 60));
		$this->assertEqual(true , $ck);
		//過去日
		$ck = $this->TimeZone->isFutureDateTime($model , date('Y-m-d H:i:s' , time() - 60));
		$this->assertEqual(false , $ck);
		//不正な値だった場合　false
		$ck = $this->TimeZone->isFutureDateTime($model , 'BBBBB');
		$this->assertEqual(false , $ck);
	}

/**
 * testInvalidDisplayFromDate method
 *
 * @return void
 */
	public function testInvalidDisplayFromDate() {


	}

/**
 * testInvalidDisplayToDate method
 *
 * @return void
 */
	public function testInvalidDisplayToDate() {
		//invalidDisplayFromDate(Model $Model, $check)

	}

/**
 * testInvalidDisplayFromToDate method
 *
 * @return void
 */
	public function testInvalidDisplayFromToDate() {
	}

}
