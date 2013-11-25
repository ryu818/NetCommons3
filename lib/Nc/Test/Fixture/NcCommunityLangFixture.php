<?php
/**
 * BlogPost Fixture
 * Last update 2013.11.20
 */
class NcCommunityLangFixture extends CakeTestFixture {
	public $useDbConfig = 'test';
	public $name = 'CommunityLang';

	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary'),
		'room_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'key' => 'index'),
		'lang' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 10, 'collate' => 'utf8_general_ci', 'comment' => '言語(ja,en等)', 'charset' => 'utf8'),
		'community_name' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'コミュニティー名称', 'charset' => 'utf8'),
		'summary' => array('type' => 'text', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '概要', 'charset' => 'utf8'),
		'revision_group_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'comment' => '活動の概要へのRevison.id'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'created_user_id' => array('type' => 'integer', 'null' => true, 'default' => null),
		'created_user_name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified_user_id' => array('type' => 'integer', 'null' => true, 'default' => null),
		'modified_user_name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'room_id' => array('column' => array('room_id', 'lang'), 'unique' => 0)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);

	public $records = array(
	   array(
			'id'=>1,
			'room_id'=> 16,
			'lang'=>'ja',
			'community_name'=>'新規コミュニティー-1',
			'summary'=>'コミュニティーA',
			'revision_group_id'=>0,
			'created'=>'2013-11-20 05:41:20',
			'created_user_id'=>1,
			'created_user_name'=>'admin',
			'modified'=>'2013-11-20 05:41:20',
			'modified_user_id'=>1,
			'modified_user_name'=>'admin'
		),
		array(
			'id'=>2,
			'room_id'=> 16,
			'lang'=>'eng',
			'community_name'=>'UnitTest-A',
			'summary'=>'UnitTest-A UnitTest-A UnitTest-A',
			'revision_group_id'=>0,
			'created'=>'2013-11-20 05:41:20',
			'created_user_id'=>1,
			'created_user_name'=>'admin',
			'modified'=>'2013-11-20 05:41:20',
			'modified_user_id'=>1,
			'modified_user_name'=>'admin'
		)
	);

}