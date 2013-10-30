<?php
App::uses('FileBehavior', 'Model/Behavior');

/**
 * FileBehavior Test Case
 *
 */
class NcFileBehaviorTest extends CakeTestCase {

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->File = new FileBehavior();
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->File);

		parent::tearDown();
	}

/**
 * testCreateFile method
 *
 * @return void
 */
	public function testCreateFile() {

		//ファイルが作れるか試す。
		//$pathに最後区切り文字がはいっている場合。  ---------------------------
		$model = new User;
		$path = TMP . "tests" . DS;
		$name = "NcFileBehaviorTestFile.txt";
		$content = "unitTest TEXT";
		$ck = $this->File->createFile($model , $path , $name , $content , true);

		$this->assertEqual($name , $ck); //作成に成功した
		$this->assertEqual(true , is_file($path . $name));//生成したファイルが存在する。
		$this->assertEqual(true , is_file($path . $name .  ".gz"));//生成したgzファイルが存在する。
		$text = file_get_contents($path . $name);
		$this->assertEqual($text , $content); //ファイルの中にかかれているものが指定したモノ。
		//ファイルが書き込み可能かしらべる
		$this->assertEqual(true , is_readable($path .  $name));
		$this->assertEqual(true , is_readable($path . $name . '.gz'));

		//テストのため生成したファイルを削除

		if(is_file(realpath($path .  $name))) unlink(realpath($path .  $name));
		if(is_file($path .  $name . ".gz")) unlink($path . $name . ".gz");

		//$pathに最後区切り文字がはいっていない場合  ---------------------------
		$model = new User;
		$path = TMP . "tests";
		$name = "NcFileBehaviorTestFile.txt";
		$content = "unitTest TEXT";
		$ck = $this->File->createFile($model , $path , $name , $content , true);

		$this->assertEqual($name , $ck); //作成に成功した
		$this->assertEqual(true , is_file($path . DS . $name));//生成したファイルが存在する。
		$this->assertEqual(true , is_file($path . DS . $name .  ".gz"));//生成したgzファイルが存在する。
		$text = file_get_contents($path . DS . $name);
		$this->assertEqual($text , $content); //ファイルの中にかかれているものが指定したモノ。
		//ファイルが書き込み可能かしらべる
		$this->assertEqual(true , is_readable($path . DS .  $name));
		$this->assertEqual(true , is_readable($path . DS . $name . '.gz'));

		//テストのため生成したファイルを削除
		if(is_file($path . DS . $name)) unlink($path . DS . $name);
		if(is_file($path . DS . $name . ".gz")) unlink($path . DS . $name . ".gz");

		//gzファイルを作らない場合 --------------------------- ----------------
		//$pathに最後区切り文字がはいっていない場合
		$model = new User;
		$path = TMP . "tests";
		$name = "NcFileBehaviorTestFile-2.txt";
		$content = "unitTest TEXT";
		$ck = $this->File->createFile($model , $path , $name , $content ,false);

		$this->assertEqual($name , $ck); //作成に成功したのでファイル名が戻ってくる
		$this->assertEqual(true , is_file($path . DS . $name));//生成したファイルが存在する。
		$this->assertEqual(false , is_file($path . DS . $name .  ".gz"));//生成したgzファイルが存在する。
		$text = file_get_contents($path . DS . $name);
		$this->assertEqual($text , $content); //ファイルの中にかかれているものが指定したモノ。
		//ファイルのパーミッションを調べる 0666になっているはず
		//ファイルが書き込み可能かしらべる
		$this->assertEqual(true , is_readable($path . DS .  $name));

		//テストのため生成したファイルを削除
		if(is_file($path . DS . $name)) unlink($path . DS . $name);
		if(is_file($path . DS . $name . ".gz")) unlink($path . DS . $name . ".gz");

	}

/**
 * testBasename method
 *
 * @return void
 */
	public function testBasename() {
		$model = new User;

		//ファイル名の場合
		$path = TMP . "tests" . DS;
		$name = "testfile";
		$ck   = $this->File->basename($model , $path . $name);
		$this->assertEqual($name , $ck);

		//パスの最後にフォルダの区切り文字がある場合
		$path = TMP . "tests" . DS;
		$name = 'testfile' . DS;
		$ck   = $this->File->basename($model , $path . $name);
		$this->assertEqual(basename($path . $name) , $ck);

		//日本語の場合
		$path = TMP . "tests" . DS;
		$name = 'テストファイル';
		$ck   = $this->File->basename($model , $path . $name);
		$this->assertEqual(basename($path . $name) , $ck);

		//日本語名のフォルダの場合
		$path = TMP . "tests" . DS;
		$name = 'テストファイル' . DS;
		$ck   = $this->File->basename($model , $path . $name);
		$this->assertEqual(basename($path . $name) , $ck);

		//suffixがつく場合
		$path   = TMP . "tests" . DS;
		$name   = 'testtest.txt';
		$suffix = '.txt';
		$ans    = 'testtest';
		$ck     = $this->File->basename($model , $path . $name , $suffix);
		$this->assertEqual($ans , $ck);

		//suffixがつく場合 最後がフォルダ
		$path   = TMP . "tests" . DS;
		$name   = 'testtest.txt' . DS;
		$suffix = '.txt';
		$ans    = 'testtest';
		$ck     = $this->File->basename($model , $path . $name , $suffix);
		$this->assertEqual($ans , $ck);

	}

/**
 * testDeleteFile method
 *
 * @return void
 */
	public function testDeleteFile() {
	}

/**
 * testGetCurrentDir method
 *
 * @return void
 */
	public function testGetCurrentDir() {
	}

/**
 * testGetCurrentFile method
 *
 * @return void
 */
	public function testGetCurrentFile() {
	}

}
