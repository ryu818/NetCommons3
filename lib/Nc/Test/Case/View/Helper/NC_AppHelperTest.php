<?php
/**
 * Created by IntelliJ IDEA.
 * User: nekoget
 * Date: 13/10/23
 * Time: 10:49
 * To change this template use File | Settings | File Templates.
 */

App::uses('Controller', 'Controller');
App::uses('View', 'View');
App::uses('Helper', 'View/AppHelper');

class NC_AppHelperTest extends CakeTestCase {
	public function setUp() {
		parent::setUp();
		$Controller = new Controller();
		$View = new View($Controller);
		$this->app = new AppHelper($View);
	}

	public function testUrl() {
		$text = "test";
		$this->assertEqual('http://localhost/test' , $this->app->url($text , true));
		$this->assertEqual('/test' , $this->app->url($text));

		$text = "http://localhost/test";
		$this->assertEqual('http://localhost/test' , $this->app->url($text , true));
		$this->assertEqual('http://localhost/test' , $this->app->url($text));

		$this->assertEqual(null , $this->app->url());
		$this->assertEqual(null , $this->app->url(null));
	}
}
