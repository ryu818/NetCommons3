<?php
/**
 *UserLink
 */

class NcPageUserLinkFixture extends CakeTestFixture {

	public $useDbConfig = 'test';
	public $name = 'PageUserLink';

	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary'),
		'room_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'key' => 'index'),
		'user_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'authority_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'created_user_id' => array('type' => 'integer', 'null' => true, 'default' => null),
		'created_user_name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified_user_id' => array('type' => 'integer', 'null' => true, 'default' => null),
		'modified_user_name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'room_id' => array('column' => array('room_id', 'user_id'), 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);

	public $records = array(
		array(
			'id'=>1,
			'room_id'=>9,
			'user_id'=>1,
			'authority_id'=>2,
			'created'=> NULL,
			'created_user_id'=> 0,
			'created_user_name'=>'',
			'modified'=> NULL,
			'modified_user_id'=> 0,
			'modified_user_name'=>''
		),
		array(
			'id'=>28,
			'room_id'=>10,
			'user_id'=>1,
			'authority_id'=>2,
			'created'=> NULL,
			'created_user_id'=> 0,
			'created_user_name'=>'',
			'modified'=> NULL,
			'modified_user_id'=> 0,
			'modified_user_name'=>''
		),
		array(
			'id'=>29,
			'room_id'=>11,
			'user_id'=>1,
			'authority_id'=>2,
			'created'=> NULL,
			'created_user_id'=> 0,
			'created_user_name'=>'',
			'modified'=> NULL,
			'modified_user_id'=> 0,
			'modified_user_name'=>''
		),
		array(
			'id'=>30,
			'room_id'=>16,
			'user_id'=>1,
			'authority_id'=>2,
			'created'=> '2013-11-20 05:41:20',
			'created_user_id'=> 1,
			'created_user_name'=>'admin',
			'modified'=> '2013-11-20 05:41:20',
			'modified_user_id'=> 1,
			'modified_user_name'=>'admin'
		)
	);
}