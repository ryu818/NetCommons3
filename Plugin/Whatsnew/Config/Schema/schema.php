<?php
use Migrations\Model\CakeSchema;

class WhatsnewSchema extends CakeSchema {

	public function before($event = array()) {
		return true;
	}

	public function after($event = array()) {
	}

	public $whatsnew_select_rooms = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary', 'comment' => '新着選択ルームID（指定したルームのみの新着を表示する場合に指定）'),
		'block_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'room_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);

	public $whatsnew_select_users = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary', 'comment' => '新着選択会員ID（指定した会員のマイポータルのみの新着を表示する場合に指定）'),
		'block_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'user_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);

	public $whatsnew_styles = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary', 'comment' => '新着スタイルID（新着のスタイルの設定）'),
		'block_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'display_type' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 3, 'comment' => '表示タイプ
0: ルーム毎に表示
1: フラットに表示'),
		'display_period_type' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => '表示期間タイプ
0: 表示期間リストボックス非表示
1: 表示期間リストボックス表示'),
		'display_days' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 3),
		'display_number' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 3, 'comment' => '表示日数'),
		'is_display_title' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'タイトルの表示するかどうか。'),
		'is_display_description' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => '詳細表示するかどうか。'),
		'is_display_room_name' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'ルーム名の表示をするかどうか。'),
		'is_display_module_name' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'モジュール名の表示をするかどうか。'),
		'is_display_user_name' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'ハンドル名の表示をするかどうか。'),
		'is_display_created' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => '日時の表示をするかどうか。'),
		'title_truncate_num' => array('type' => 'integer', 'null' => false, 'default' => '0', 'comment' => 'タイトル最大文字数'),
		'description_truncate_num' => array('type' => 'integer', 'null' => false, 'default' => '0', 'comment' => '詳細最大文字数'),
		'allow_rss_feed' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'RSSフィードのリンクを表示するかどうか。'),
		'display_modules' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '表示モジュール', 'charset' => 'utf8'),
		'myportal_flag' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'マイポータルの新着を表示するかどうか。'),
		'myroom_flag' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'マイルームの新着を表示するかどうか。'),
		'select_myportal_users' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'マイポータルの新着を表示する場合、会員の絞り込みをしているかどうか。'),
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

}
