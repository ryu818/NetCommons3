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
	 * テストで作成したファイルを消す
	 * @param $path
	 */
	public function unlinkFile($path) {
		//指定されたファイル
		if(file_exists(realpath($path))) unlink(realpath($path));
		//gzファイル
		if(file_exists(realpath($path . ".gz"))) unlink(realpath($path. ".gz"));
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
		$this->assertEqual(true , file_exists($path . $name));//生成したファイルが存在する。
		$this->assertEqual(true , file_exists($path . $name .  ".gz"));//生成したgzファイルが存在する。
		$text = file_get_contents($path . $name);
		$this->assertEqual($text , $content); //ファイルの中にかかれているものが指定したモノ。
		//ファイルが書き込み可能かしらべる
		$this->assertEqual(true , is_readable($path .  $name));
		$this->assertEqual(true , is_readable($path . $name . '.gz'));

		//テストのため生成したファイルを削除
		$this->unlinkFile(realpath($path .  $name));

		//$pathに最後区切り文字がはいっていない場合  ---------------------------
		$model = new User;
		$path = TMP . "tests";
		$name = "NcFileBehaviorTestFile.txt";
		$content = "unitTest TEXT";
		$ck = $this->File->createFile($model , $path , $name , $content , true);

		$this->assertEqual($name , $ck); //作成に成功した
		$this->assertEqual(true , file_exists($path . DS . $name));//生成したファイルが存在する。
		$this->assertEqual(true , file_exists($path . DS . $name .  ".gz"));//生成したgzファイルが存在する。
		$text = file_get_contents($path . DS . $name);
		$this->assertEqual($text , $content); //ファイルの中にかかれているものが指定したモノ。
		//ファイルが書き込み可能かしらべる
		$this->assertEqual(true , is_readable($path . DS .  $name));
		$this->assertEqual(true , is_readable($path . DS . $name . '.gz'));

		//テストのため生成したファイルを削除
		$this->unlinkFile($path . DS . $name);


		//gzファイルを作らない場合 --------------------------- ----------------
		//$pathに最後区切り文字がはいっていない場合
		$model = new User;
		$path = TMP . "tests";
		$name = "NcFileBehaviorTestFile-2.txt";
		$content = "unitTest TEXT";
		$ck = $this->File->createFile($model , $path , $name , $content ,false);

		$this->assertEqual($name , $ck); //作成に成功したのでファイル名が戻ってくる
		$this->assertEqual(true , file_exists($path . DS . $name));//生成したファイルが存在する。
		$this->assertEqual(false , file_exists($path . DS . $name .  ".gz"));//生成したgzファイルが存在する。
		$text = file_get_contents($path . DS . $name);
		$this->assertEqual($text , $content); //ファイルの中にかかれているものが指定したモノ。
		//ファイルのパーミッションを調べる 0666になっているはず
		//ファイルが書き込み可能かしらべる
		$this->assertEqual(true , is_readable($path . DS .  $name));

		//テストのため生成したファイルを削除
		$this->unlinkFile($path . DS . $name);
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

		//テスト用にファイルを作成
		$model   = new User;
		$path    = TMP . "tests";
		$name    = "NcFileBehaviorTestFile.txt";
		$content = "unitTest TEXT";
		$ck      = $this->File->createFile($model , $path , $name , $content ,true);
		$this->assertEqual($name , $ck); //作成に成功したのでファイル名が戻ってくる
		$this->assertEqual(true , file_exists($path . DS . $name));//生成したファイルが存在する。
		$this->assertEqual(true , file_exists($path . DS . $name .  ".gz"));//生成したgzファイルが存在する。
		$text = file_get_contents($path . DS . $name);
		$this->assertEqual($text , $content); //ファイルの中にかかれているものが指定したモノ。
		//ファイルが書き込み可能かしらべる
		$this->assertEqual(true , is_readable($path . DS .  $name));

		$file_path = $path . DS . $name;
		$gz_path   = $file_path . '.gz';

		//gzファイルも含めて削除 --
		$ck = $this->File->deleteFile($model , $file_path, true);
		//trueが戻る。
		$this->assertEqual(true , $ck);
		//ファイルは削除されたので、存在しないはず
		$this->assertEqual(false , file_exists($file_path));
		$this->assertEqual(false , file_exists($gz_path));
		//テストのため生成したファイルを削除
		$this->unlinkFile($file_path);

		//存在しないファイルを指定して削除。--
		$ck = $this->File->deleteFile($model , $file_path, true);
		//存在しないファイルが指定されたのでfalse
		$this->assertEqual(false , $ck);
		//ファイルは存在しない
		$this->assertEqual(false , file_exists($file_path));
		$this->assertEqual(false , file_exists($gz_path));

		//option省略　gzファイルも同時に消す --
		//ファイル作成
		$ck      = $this->File->createFile($model , $path , $name , $content ,true);
		$this->assertEqual($name , $ck); //作成に成功したのでファイル名が戻ってくる
		$this->assertEqual(true , file_exists($path . DS . $name));//生成したファイルが存在する。
		$this->assertEqual(true , file_exists($path . DS . $name .  ".gz"));//生成したgzファイルが存在する。
		$text = file_get_contents($path . DS . $name);
		$this->assertEqual($text , $content); //ファイルの中にかかれているものが指定したモノ。
		//ファイルが書き込み可能かしらべる
		$this->assertEqual(true , is_readable($path . DS .  $name));
		// ファイル削除 option省略・
		$ck = $this->File->deleteFile($model , $file_path);
		//trueが戻る。
		$this->assertEqual(true , $ck);
		//ファイルは削除されたので、存在しないはず
		$this->assertEqual(false , file_exists($file_path));
		$this->assertEqual(false , file_exists($gz_path));
		//テストのため生成したファイルを削除
		$this->unlinkFile($file_path);

		//存在しないファイルを指定して削除。--
		$ck = $this->File->deleteFile($model , $file_path, true);
		//存在しないファイルが指定されたのでfalse
		$this->assertEqual(false , $ck);
		//ファイルは存在しない
		$this->assertEqual(false , file_exists($file_path));
		$this->assertEqual(false , file_exists($gz_path));

		//指定したファイルのみ削除 --
		$model     = new User;
		$path      = TMP . "tests";
		$name      = "NcFileBehaviorTestFile.txt";
		$content   = "unitTest TEXT";
		$file_path = $path . DS . $name;
		$gz_path   = $file_path . '.gz';
		$ck = $this->File->createFile($model , $path , $name , $content ,true);
		$this->assertEqual($name , $ck); //作成に成功したのでファイル名が戻ってくる
		$this->assertEqual(true , file_exists($file_path));//生成したファイルが存在する。
		$this->assertEqual(true , file_exists($gz_path));//生成したgzファイルが存在する。
		$text = file_get_contents($file_path);
		$this->assertEqual($text , $content); //ファイルの中にかかれているものが指定したモノ。
		//ファイルが書き込み可能かしらべる
		$this->assertEqual(true , is_readable($file_path));

		//gzファイルも含めない削除 --
		$ck = $this->File->deleteFile($model , $file_path,false);
		//trueが戻る。
		$this->assertEqual(true , $ck);
		//ファイルは削除されたので、存在しないはず
		$this->assertEqual(false , file_exists($file_path));
		//gzファイルは存在する
		$this->assertEqual(true , file_exists($gz_path));

		//テストのため生成したファイルを削除
		$this->unlinkFile($file_path);

		//gzファイルが存在しない状態で、gzファイルも含めて削除 --
		$model     = new User;
		$path      = TMP . "tests";
		$name      = "NcFileBehaviorTestFile.txt";
		$content   = "unitTest TEXT";
		$file_path = $path . DS . $name;
		$gz_path   = $file_path . '.gz';
		$ck = $this->File->createFile($model , $path , $name , $content ,false);
		$this->assertEqual($name , $ck); //作成に成功したのでファイル名が戻ってくる
		$this->assertEqual(true , file_exists($file_path));//生成したファイルが存在する。
		$this->assertEqual(false , file_exists($gz_path));//生成したgzファイルは存在しない
		$text = file_get_contents($file_path);
		$this->assertEqual($text , $content); //ファイルの中にかかれているものが指定したモノ。
		//ファイルが書き込み可能かしらべる
		$this->assertEqual(true , is_readable($file_path));

		//gzファイルを含めるファイル削除
		$ck = $this->File->deleteFile($model , $file_path,true);
		//trueが戻る。
		$this->assertEqual(true , $ck);
		//ファイルは削除されたので、存在しないはず
		$this->assertEqual(false , file_exists($file_path));
		//gzファイルはそもそも存在しない。
		$this->assertEqual(false , file_exists($gz_path));

		//テストのため生成したファイルを削除
		$this->unlinkFile($file_path);
	}

/**
 * testGetCurrentDir method
 *
 * @return void
 */
	public function testGetCurrentDir() {
		$model = new User;
		$path = TMP . "tests" . DS . 'NcFileBehaviorTestDir' . DS;
		if(! is_dir($path)) mkdir($path , 0777);

		//フォルダの中をしらべる。フォルダは０件。array()が戻る
		$ck = $this->File->getCurrentDir($model, $path);
		$this->assertEqual(array() , $ck);
		//テストで使用したフォルダを削除
		if(is_dir($path)) rmdir($path);
		//存在しないフォルダに対して事項する。falseが戻る。
		$ck = $this->File->getCurrentDir($model, $path);
		$this->assertEqual(false , $ck);

		$dirArray = array('test1' , 'test2' , 'test3');

		$this->assertEqual( true , mkdir($path , 0777));
		foreach($dirArray as $i)
		{
			if(! is_dir($path . DS . $i)) $this->assertEqual( true , mkdir($path . DS . $i , 0777));
		}
		//設置したフォルダと同じものが情報として戻ってきた。
		$ck = $this->File->getCurrentDir($model, $path);
		$this->assertEqual(sort($dirArray), sort($ck));

		//ファイルと混在していた場合--
		$name = "testFile.txt";
		$content = "unitTest TEXT";
		$ck = $this->File->createFile($model , $path , $name , $content , true);

		$this->assertEqual($name , $ck); //作成に成功した
		$this->assertEqual(true , file_exists($path . $name));//生成したファイルが存在する。
		$this->assertEqual(true , file_exists($path . $name .  ".gz"));//生成したgzファイルが存在する。
		$text = file_get_contents($path . $name);
		$this->assertEqual($text , $content); //ファイルの中にかかれているものが指定したモノ。
		//ファイルが書き込み可能かしらべる
		$this->assertEqual(true , is_readable($path .  $name));
		$this->assertEqual(true , is_readable($path . $name . '.gz'));

		//ファイルが混在している状態になった。
		// フォルダ test1, test2, test3 ファイル testFile.txt testFile.txt.gz

		$ck = $this->File->getCurrentDir($model, $path);
		$this->assertEqual(sort($dirArray), sort($ck));

		//optionをfalse指定。
		$ck = $this->File->getCurrentDir($model, $path , false);
		$this->assertEqual(sort($dirArray), sort($ck));

		//第三引数をtrue指定 ファイルだけの一覧がとれる。
		$ck = $this->File->getCurrentDir($model, $path ,true);
		$fileArray =  array($name , $name . '.gz');
		$this->assertEqual(sort($fileArray), sort($ck));


		//テストのため生成したファイルを削除
		$this->unlinkFile(realpath($path .  $name));


		//テストで使用したフォルダを削除
		foreach($dirArray as $i)
		{
			if(is_dir($path . DS . $i)) $this->assertEqual( true , rmdir($path . DS . $i));
		}
		if(is_dir($path)) rmdir($path);

		//$is_file = trueの場合





	}

/**
 * testGetCurrentFile method
 *
 * @return void
 */
	public function testGetCurrentFile() {
	}

}
