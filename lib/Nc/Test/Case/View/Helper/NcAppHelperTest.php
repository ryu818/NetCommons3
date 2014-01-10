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

class NcAppHelperTest extends CakeTestCase {
	public function setUp() {
		parent::setUp();
		//var_export(Configure::read('App'));
		Configure::write('App' ,
			array (
				'imageBaseUrl' => 'img/',
				'cssBaseUrl' => 'css/',
				'jsBaseUrl' => 'js/',
				'base' => false,
				'baseUrl' => false,
				'dir' => 'app',
				'webroot' => 'webroot',
				'www_root' => '/vagrant_data/app/webroot/',
				'encoding' => 'UTF-8',
				'fullBaseUrl' => 'http://localhost',
			)
		);
		$Controller = new Controller();
		$View = new View($Controller);
		$this->app = new AppHelper($View);
	}

/**
 * testUrl  method
 *
 * @return void
 */
	public function testUrl() {
		$text = "test";
		$host = FULL_BASE_URL;

		$this->assertEqual($host.'/test' , $this->app->url($text , true));
		$this->assertEqual('/test', $this->app->url($text));

		$text = $host.'/test';
		$this->assertEqual($text, $this->app->url($text , true));
		$this->assertEqual($text, $this->app->url($text));

		$this->assertEqual(null, $this->app->url());
		$this->assertEqual(null, $this->app->url(null));
	}
}
