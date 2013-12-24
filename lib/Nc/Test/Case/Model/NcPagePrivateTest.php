<?php
/**
 * Page Test Case
 *
 */
class NcPagePrivateTest extends CakeTestCase {
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

	private $PagePrivate = null;

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();

		//User.id:1がログインしている状態
		$User = ClassRegistry::init('User');
		$userData = $User->findById(1);
		Configure::write(NC_SYSTEM_KEY.'.user' , $userData['User']);

		$this->PagePrivate = ClassRegistry::init('PagePrivate');

	}

	/**
	 * tearDown method
	 *
	 * @return void
	 */
	public function tearDown() {
		unset($this->PagePrivate);
		parent::tearDown();
		Configure::clear(NC_SYSTEM_KEY.'.user');
	}



	public function testSetUserId() {
		//存在するユーザをセットする。
		$ck=$this->PagePrivate->setUserId(1);
		$this->assertEqual(true , $ck);

		//存在しないユーザをセットする
		$ck = $this->PagePrivate->setUserId(999999999999);
		$this->assertEqual(false , $ck);

		//文字列をセットする（パラメータエラー）
		$ck = $this->PagePrivate->setUserId('AAAAAAAA');
		$this->assertEqual(false , $ck);

		//配列をセットする（パラメータエラー
		$ck = $this->PagePrivate->setUserId(array(1,2,3));
		$this->assertEqual(false , $ck);

	}

	public function testArrayKeyChangeId(){
		//他の分割したModel/Pageでも使う予定のコードなので、Page Behaviorへ移動した。
		$ck = $this->PagePrivate->find('all');
		$this->assertEqual(true , isset($ck[0]));
		$ck = $this->PagePrivate->arrayKeyChangeId($ck);
		$this->assertEqual(false , isset($ck[0]));
	}

	public function testGetRoomList() {

		//User.id:1でログインしている状態
		$ck = $this->PagePrivate->getRoomList();
		$this->assertEqual(is_array($ck) , true);
		$this->assertEqual(11 , $ck[11]['PagePrivate']['id']);

		//ログインしていない状態1
		Configure::clear(NC_SYSTEM_KEY.'.user');
		//$this->PagePrivate->setUserId(9999999999);
		$ck = $this->PagePrivate->getRoomList(9999999999);
		$this->assertEqual(array() , $ck);


		$ck = $this->PagePrivate->getRoomList(1);
		$this->assertEqual(is_array($ck) , true);
		$this->assertEqual(11 , $ck[11]['PagePrivate']['id']);

		//User.id:1がセットされる
		$ck = $this->PagePrivate->setUserId(1);
		$this->assertEqual(true , $ck);
		$ck = $this->PagePrivate->getRoomList();
		$this->assertEqual(is_array($ck) , true);
		$this->assertEqual(11 , $ck[11]['PagePrivate']['id']);

		//User,id：9がセットされる。//存在しないユーザなのでfalse
		$ck = $this->PagePrivate->setUserId(9999999999);
		$this->assertEqual(false , $ck);
		$ck = $this->PagePrivate->getRoomList();
		$this->assertEqual(array() , $ck);
	}

	public function testaddTopRoom() {
		$startCount = $this->PagePrivate->find('count');

		$ck = $this->PagePrivate->addTopRoom(2);
		$this->assertEqual(true , is_numeric($ck));
		$id = $ck;
		$endCount = $this->PagePrivate->find('count');
		$this->assertEqual($startCount+2 , $endCount);
		$this->assertEqual(true , is_numeric($id)); //idが戻る
		$ck = $this->PagePrivate->findById($id);
		$this->assertEqual($ck['PagePrivate']['id'] , $ck['PagePrivate']['room_id']);
		$this->assertEqual($ck['PagePrivate']['parent_id'] , NC_TOP_PRIVATE_ID);

		//roomにPageUserLinkが紐付いたかどうか確認する。
		$PageUserLink = ClassRegistry::init("PageUserLink");
		$key    = $PageUserLink->alias;
		$ck     = $PageUserLink->find(
			'count',
			array(
				'conditions'=>array(
					"{$key}.room_id"=>$id,
					"{$key}.user_id"=>2
				)
			)
		);
		//1件
		$this->assertEqual($ck , 1);


		$ck = $this->PagePrivate->addTopRoom(99999999);
		$this->assertEqual(false , $ck);

		$ck = $this->PagePrivate->addTopRoom('AAAAAAA');
		$this->assertEqual(false , $ck);

		$ck = $this->PagePrivate->addTopRoom(array());
		$this->assertEqual(false , $ck);
	}






}