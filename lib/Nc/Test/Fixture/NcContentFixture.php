<?php
/**
 * Content Fixture
 * Last update 2013.12.04
 */

class NcContentFixture extends CakeTestFixture {
	public $useDbConfig = 'test';
	public $name = 'ConfigLang';

	public $fields  = array(
	'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary', 'comment' => 'コンテンツID（ブロック内のコンテンツを管理）'),
	'module_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
	'title' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'コンテンツ名', 'charset' => 'utf8'),
	'shortcut_type' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 3, 'comment' => 'ショートカットタイプ
0：ショートカットではないコンテンツ
1：閲覧のみ許可なショートカット
2：表示中のルーム権限より閲覧・編集権限を付与するショートカット'),
	'master_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'comment' => 'ショートカットでない場合、自分自身のContent.id。ショートカットの場合、コンテンツ元のContent.id'),
	'room_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
	'display_flag' => array('type' => 'integer', 'null' => false, 'default' => '1', 'length' => 2, 'comment' => 'コンテンツを公開するかどうか。
0：非公開
1：公開
2：利用不可'),
	'is_approved' => array('type' => 'boolean', 'null' => false, 'default' => '1', 'comment' => '承認済かどうか。'),
	'url' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '外部にコンテンツがあった場合にフルパスで指定（未使用）', 'charset' => 'utf8'),
	'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
	'created_user_id' => array('type' => 'integer', 'null' => true, 'default' => null),
	'created_user_name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
	'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
	'modified_user_id' => array('type' => 'integer', 'null' => true, 'default' => null),
	'modified_user_name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
	'indexes' => array(
		'PRIMARY' => array('column' => 'id', 'unique' => 1)
	),
	'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
);

	public $records =  array (
		0 =>
		array (
			'id' => '17',
			'module_id' => '5',
			'title' => '研究ブログ',
			'shortcut_type' => '0',
			'master_id' => '17',
			'room_id' => '9',
			'display_flag' => '1',
			'is_approved' => '1',
			'url' => '',
			'created' => '2013-05-01 03:27:18',
			'created_user_id' => '1',
			'created_user_name' => 'admin',
			'modified' => '2013-06-04 08:14:24',
			'modified_user_id' => '1',
			'modified_user_name' => 'admin',
		),
		1 =>
		array (
			'id' => '18',
			'module_id' => '1',
			'title' => 'お知らせ',
			'shortcut_type' => '0',
			'master_id' => '18',
			'room_id' => '9',
			'display_flag' => '1',
			'is_approved' => '1',
			'url' => '',
			'created' => '2013-05-01 04:09:55',
			'created_user_id' => '2',
			'created_user_name' => 'chief001',
			'modified' => '2013-06-13 00:31:33',
			'modified_user_id' => '1',
			'modified_user_name' => 'admin',
		),
	);

}

