<?php
/**
 * BlogPost Fixture
 * Last update 2013.12.02
 */
class NcCommunityTagFixture extends CakeTestFixture {
	public $useDbConfig = 'test';
	public $name = 'CommunityTag';

	public $fields  = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary'),
		'room_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'key' => 'index'),
		'community_sum_tag_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'tag_value' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'タグ名称', 'charset' => 'utf8'),
		'lang' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 10, 'collate' => 'utf8_general_ci', 'comment' => '言語(ja,en等)', 'charset' => 'utf8'),
		'display_sequence' => array('type' => 'integer', 'null' => false, 'default' => '0', 'comment' => 'コミュニティー単位の表示順序(room_id毎の連番)'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'created_user_id' => array('type' => 'integer', 'null' => true, 'default' => null),
		'created_user_name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified_user_id' => array('type' => 'integer', 'null' => true, 'default' => null),
		'modified_user_name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'room_id' => array('column' => array('room_id', 'community_sum_tag_id', 'tag_value'), 'unique' => 0)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);

	public $records = array();

}