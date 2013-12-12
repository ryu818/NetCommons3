<?php
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
		'NcPageTree',
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

	public function testGetParent()
	{
		$ck = $this->PageTree->getParentAll(12);
		$ans = array (
			0 =>
			array (
				'PageTree' =>
				array (
					'id' => '15',
					'parent_id' => '9',
					'child_id' => '12',
					'stratum_num' => '2',
					'created' => '2013-11-11 12:00:00',
					'modified' => '2013-11-11 12:00:00',
				),
			),
			1 =>
			array (
				'PageTree' =>
				array (
					'id' => '16',
					'parent_id' => '1',
					'child_id' => '12',
					'stratum_num' => '1',
					'created' => '2013-11-11 12:00:00',
					'modified' => '2013-11-11 12:00:00',
				),
			),
			2 =>
			array (
				'PageTree' =>
				array (
					'id' => '17',
					'parent_id' => '12',
					'child_id' => '12',
					'stratum_num' => '0',
					'created' => '2013-11-11 12:00:00',
					'modified' => '2013-11-11 12:00:00',
				),
			),
		);
		$this->assertEqual($ck , $ans);
		//存在しないレコード
		$ck = $this->PageTree->getParentAll(99999999);
		$ans = array();
		$this->assertEqual($ck , $ans);
		//子孫のいないページ  //自身のレコードだけがとれる。 stratum_num:0のデータ
		$ck = $this->PageTree->getParentAll(1);
		$ans = array (
			0 =>
			array (
				'PageTree' =>
				array (
					'id' => '1',
					'parent_id' => '1',
					'child_id' => '1',
					'stratum_num' => '0',
					'created' => '2013-11-11 12:00:00',
					'modified' => '2013-11-11 12:00:00',
				),
			),
		);
		$this->assertEqual($ans , $ck);
	}

	//ページの追加
	public function testAddPage() {
		$count = $this->PageTree->find('count');
		$ck = $this->PageTree->addPage(20 , 12);
		$this->assertEqual($ck , true);
		//レコードが増えてる。
		$this->assertNotEqual($count , $this->PageTree->find('count')) ;
		$this->assertEqual(true , ($count < $this->PageTree->find('count')));
		//12の1番めの子孫が20のレコードが存在する。
		$ck = $this->PageTree->find(
			'all' ,
			array('conditions'=>array(
				'PageTree.parent_id'=>12,
				'PageTree.child_id'=>20 )
			)
		);
		//1件
		$this->assertEqual(1 , count($ck));
	}

	public function testGetChild()
	{
		//1の子孫　publicページ
		$ck = $this->PageTree->getChildAll(1);
		$ans = array (
			0 =>
			array (
				'PageTree' =>
				array (
					'id' => '1',
					'parent_id' => '1',
					'child_id' => '1',
					'stratum_num' => '0',
					'created' => '2013-11-11 12:00:00',
					'modified' => '2013-11-11 12:00:00',
				),
			),
			1 =>
			array (
				'PageTree' =>
				array (
					'id' => '9',
					'parent_id' => '1',
					'child_id' => '9',
					'stratum_num' => '1',
					'created' => '2013-11-11 12:00:00',
					'modified' => '2013-11-11 12:00:00',
				),
			),
			2 =>
			array (
				'PageTree' =>
				array (
					'id' => '16',
					'parent_id' => '1',
					'child_id' => '12',
					'stratum_num' => '1',
					'created' => '2013-11-11 12:00:00',
					'modified' => '2013-11-11 12:00:00',
				),
			),
		);
		$this->assertEqual($ck , $ans);

		//存在しないページ
		$ck = $this->PageTree->getChildAll(999999999999);
		$this->assertEqual($ck , array());

		//文字列
		$ck = $this->PageTree->getChildAll('AAAAAAAA');
		$this->assertEqual($ck , array());

		//0
		$ck = $this->PageTree->getChildAll(0);
		$this->assertEqual($ck , array());

		//2の子孫
		$ck = $this->PageTree->getChildAll(2);
		$ans = array (
			0 =>
			array (
				'PageTree' =>
				array (
					'id' => '2',
					'parent_id' => '2',
					'child_id' => '2',
					'stratum_num' => '0',
					'created' => '2013-11-11 12:00:00',
					'modified' => '2013-11-11 12:00:00',
				),
			),
			1 =>
			array (
				'PageTree' =>
				array (
					'id' => '11',
					'parent_id' => '2',
					'child_id' => '10',
					'stratum_num' => '1',
					'created' => '2013-11-11 12:00:00',
					'modified' => '2013-11-11 12:00:00',
				),
			),
			2 =>
			array (
				'PageTree' =>
				array (
					'id' => '19',
					'parent_id' => '2',
					'child_id' => '13',
					'stratum_num' => '1',
					'created' => '2013-11-11 12:00:00',
					'modified' => '2013-11-11 12:00:00',
				),
			),
		);
		$this->assertEqual($ck , $ans);
		//var_export($ck);
	}

	public function testDeletePage() {
		//11を削除
		$ck = $this->PageTree->deletePage(11);
		$this->assertEqual(true , $ck);

		//11の親を取得
		$ck = $this->PageTree->getParentAll(11);
		$this->assertEqual(array() , $ck);

		//11の子を取得
		$ck = $this->PageTree->getChildAll(11);
		$this->assertEqual(array() , $ck);
	}

	public function testDeleteNode() {
		$ck = $this->PageTree->getChildAll(11);
		//var_export($ck);
		//$ck = $this->PageTree->getChildAll(14);
		//var_export($ck);
		$ck = $this->PageTree->deleteNode(11);
		$this->assertEqual(true , $ck);
		$ck = $this->PageTree->getChildAll(11);
		$this->assertEqual(array() , $ck);
		$ck = $this->PageTree->getChildAll(14);
		$this->assertEqual(array() , $ck);
	}

	public function testMoveNode() {
		//11の子供たちに、子を追加する。
		$this->PageTree->addPage(200 , 14);
		$this->PageTree->addPage(210 , 14);
		$this->PageTree->addPage(220 , 14);
		$this->PageTree->addPage(300 , 200);
		$this->PageTree->addPage(310 , 200);
		$this->PageTree->addPage(320 , 200);

		//親が11で子が220をさがしても見つかる
		$ck = $this->PageTree->find(
			'all' ,
			array(
				'conditions'=>array(
					'PageTree.parent_id'=>11,
					'PageTree.child_id'=>220
				)
			)
		);
		$this->assertEqual(count($ck), 1);

		//14の先祖は11
		//14の親11から9に移動する。
		$ck = $this->PageTree->moveNode( 14 , 9 );
		$this->assertEqual(true , $ck);

		//親が11で子が9をさがしても見つからない
		$ck = $this->PageTree->find(
			'all' ,
			array(
				'conditions'=>array(
					'PageTree.parent_id'=>11,
					'PageTree.child_id'=>14
				)
			)
		);

		//親が11で子が220をさがしても見つからない
		$ck = $this->PageTree->find(
			'all' ,
			array(
				'conditions'=>array(
					'PageTree.parent_id'=>11,
					'PageTree.child_id'=>220
				)
			)
		);

		//親が9で子が14をさがしても見つかる
		$ck = $this->PageTree->find(
			'all' ,
			array(
				'conditions'=>array(
					'PageTree.parent_id'=>9,
					'PageTree.child_id'=>14
				)
			)
		);
		$this->assertEqual(1 , count($ck));

		//親が9で子が14をさがしても見つかる
		$ck = $this->PageTree->find(
			'all' ,
			array(
				'conditions'=>array(
					'PageTree.parent_id'=>9,
					'PageTree.child_id'=>220
				)
			)
		);
		$this->assertEqual(1 , count($ck));
	}

}