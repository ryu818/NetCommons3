<?php
class NcAllTest extends CakeTestSuite {

	/**
	 * NetCommons3の本体及び主要プラグインの一括テスト
	 * @return CakeTestSuite
	 * @since   v 3.0.0.0
	 */
	public static function suite() {
		$suite = new CakeTestSuite('All tests');
		//lib/Nc以下のテストを実行する
		//app/Testは必要に応じて、app/Test以下に書く。
		$testDirList = array(
			'Test' . DS . 'Case' . DS . 'Controller',
			'Test' . DS . 'Case' . DS . 'Controller' . DS . 'Component',
			'Test' . DS . 'Case' . DS . 'Model',
			'Test' . DS . 'Case' . DS . 'View',
			'Test' . DS . 'Case' . DS . 'View' . DS . 'Helper'
		);
		//pluginのパス一覧取得
		$TestList = NcAllTest::getPluginList();
		//lib/Nc/をリストに追加
		$TestList[] = NC;
		if($TestList)
		{
			foreach($TestList as $p)
			{
				foreach($testDirList as $t)
				{
					$testDir = $p. DS .$t ;
					if(is_dir($testDir))
					{
						$suite->addTestDirectory($testDir);
					}
				}
			}
		}
		return $suite;
	}


	/**
	 * testのため、pluginの格納されているフォルダのパス一覧を取得する。
	 *
	 * @return array
	 * @since   v 3.0.0.0
	 */
	private static function getPluginList()
	{
		//プラグインにテストコードがあれば実行する。
		$pluginDir = NC. 'Plugin' . DS;
		$d = dir($pluginDir);
		$plginList = array();
		while(false !== $entry=$d->read())
		{
			if(is_dir($pluginDir.$entry) && $entry !='.' && $entry != '..')
			{
				$plginList[] = $pluginDir.$entry;
			}
		}
		$d->close();
		return $plginList;
	}
}
