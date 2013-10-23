<?php
class AllTest extends CakeTestSuite {
	public static function suite() {
		$suite = new CakeTestSuite('All tests');
		//app/Testのテストを実行する
		$suite->addTestDirectory(TESTS . 'Case' . DS . 'Controller');
		$suite->addTestDirectory(TESTS . 'Case' . DS . 'Controller' . DS . 'Component');
		$suite->addTestDirectory(TESTS . 'Case' . DS . 'Model');
		$suite->addTestDirectory(TESTS . 'Case' . DS . 'Model' . DS . 'Behavior');
		$suite->addTestDirectory(TESTS . 'Case' . DS . 'View');
		$suite->addTestDirectory(TESTS . 'Case' . DS . 'View' . DS . 'Helper');

		//lib/Nc以下のテストを実行する
		$suite->addTestDirectory(NC . 'Test/Case' . DS . 'Controller');
		$suite->addTestDirectory(NC . 'Test/Case' . DS . 'Controller' . DS . 'Component');
		$suite->addTestDirectory(NC . 'Test/Case' . DS . 'Model');
		$suite->addTestDirectory(NC . 'Test/Case' . DS . 'Model' . DS . 'Behavior');
		$suite->addTestDirectory(NC . 'Test/Case' . DS . 'View');
		$suite->addTestDirectory(NC . 'Test/Case' . DS . 'View' . DS . 'Helper');

		return $suite;
	}
}
