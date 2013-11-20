<?php
/**
 * NcCommunityFixture
 * Last update 2013.11.20
 */

class NcCommunityFixture extends CakeTestFixture {
	public $useDbConfig = 'test';
	public $name = 'Community';

	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary'),
		'room_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'key' => 'index'),
		'photo' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'コミュニティーの写真(選択ファイル名 OR (Upload.id)_library.(extension))', 'charset' => 'utf8'),
		'is_upload' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'アップロードファイルを指定したかどうか。'),
		'publication_range_flag' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 3, 'comment' => '公開範囲
0:非公開（コミュニティー参加者のみが閲覧可能）
1:公開（すべてのログイン会員が閲覧可能）'),
		'participate_force_all_users' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => '公開コミュニティーの場合のみ指定。強制的に一般(ゲスト)として参加させるかどうか。チェックがついていた場合、PageUserLinkテーブルに存在していなくてもコミュニティー一覧にも表示させる。'),
		'participate_flag' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 3, 'comment' => '参加方法
0:参加会員のみ
1:招待制（コミュニティーメンバーから招待を受けた会員のみ参加可能）
2:承認制（主担の承認が必要）
3:参加受付制(希望者は誰でも参加可能）'),
		'invite_hierarchy' => array('type' => 'integer', 'null' => false, 'default' => '301', 'comment' => '招待を許可する権限
（0,101,201,301のみ）'),
		'is_participate_notice' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => '参加者をメール通知するかどうか。'),
		'participate_notice_hierarchy' => array('type' => 'integer', 'null' => false, 'default' => '301', 'comment' => '参加者をメール通知する権限。
（0,101,201,301のみ）'),
		'is_resign_notice' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => '退会者をメール通知するかどうか。'),
		'resign_notice_hierarchy' => array('type' => 'integer', 'null' => false, 'default' => '301', 'comment' => '退会者をメール通知する権限。
（0,101,201,301のみ）'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'created_user_id' => array('type' => 'integer', 'null' => true, 'default' => null),
		'created_user_name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified_user_id' => array('type' => 'integer', 'null' => true, 'default' => null),
		'modified_user_name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'room_id' => array('column' => 'room_id', 'unique' => 0)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);

	public $records = array(
		array(
			'id'=>1 ,
			'room_id'=>16,
			'photo'=>'study.gif',
			'is_upload'=>10 ,
			'publication_range_flag'=>11 ,
			'participate_force_all_users'=>10 ,
			'participate_flag'=>1 ,
			'invite_hierarchy'=>301 ,
			'is_participate_notice'=>1 ,
			'participate_notice_hierarchy'=>301 ,
			'is_resign_notice'=>1,
			'resign_notice_hierarchy'=>301 ,
			'created'=>'2013-11-20 05:41:20',
			'created_user_id'=>1 ,
			'created_user_name'=>'admin',
			'modified'=>'2013-11-20 05:41:20',
			'modified_user_id'=> 1 ,
			'modified_user_name'=> 'admin'
		)
	);
}