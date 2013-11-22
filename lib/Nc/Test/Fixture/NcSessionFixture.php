<?php
/**
 * Session
 */

class NcSessionFixture extends CakeTestFixture {

	public $useDbConfig = 'test';
	public $name = 'Session';

	public $fields = array(
		'id' => array('type' => 'string', 'null' => false, 'default' => null, 'key' => 'primary', 'collate' => 'utf8_general_ci', 'comment' => 'sessionID', 'charset' => 'utf8'),
		'data' => array('type' => 'text', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'sessionデータ', 'charset' => 'utf8'),
		'expires' => array('type' => 'integer', 'null' => true, 'default' => null, 'comment' => '有効期限'),
		'indexes' => array(
		'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);

	public $records = array();
}