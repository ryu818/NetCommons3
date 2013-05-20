<?php 
class WhatsnewSchema extends CakeSchema {

	public function before($event = array()) {
		return true;
	}

	public function after($event = array()) {
	}

	public $whatsnew_select_rooms = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary'),
		'block_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'room_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'MyISAM')
	);

	public $whatsnew_select_users = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary'),
		'block_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'user_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'MyISAM')
	);

	public $whatsnew_styles = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary'),
		'block_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'display_type' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 3),
		'display_period_type' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'display_days' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 3),
		'display_number' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 3),
		'is_display_title' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'is_display_description' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'is_display_room_name' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'is_display_module_name' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'is_display_user_name' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'is_display_created' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'allow_rss_feed' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'display_modules' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'myportal_flag' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'myroom_flag' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'select_myportal_users' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'created_user_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'created_user_name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified_user_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'modified_user_name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'MyISAM')
	);

}
