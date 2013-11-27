<?php
use Migrations\Model\CakeSchema;

class AnnouncementSchema extends CakeSchema {

	public function before($event = array()) {
		return true;
	}

	public function after($event = array()) {
	}

	public $announcement_edits = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary', 'comment' => 'お知らせ編集ID'),
		'content_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'post_hierarchy' => array('type' => 'integer', 'null' => false, 'default' => '301', 'comment' => '記事投稿権限
（101,201,301のみ）'),
		'approved_flag' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => '主担の承認が必要かどうか。'),
		'approved_pre_change_flag' => array('type' => 'boolean', 'null' => false, 'default' => '1', 'comment' => '未承認の場合、変更前のコンテンツを表示するかどうか。'),
		'approved_mail_flag' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => '承認されたらメールで通知するかどうか。'),
		'approved_mail_subject' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '承認後のメールで通知の件名', 'charset' => 'utf8'),
		'approved_mail_body' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '承認後のメールで通知の内容', 'charset' => 'utf8'),
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

	public $announcements = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary', 'comment' => 'お知らせID'),
		'content_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'revision_group_id' => array('type' => 'integer', 'null' => true, 'default' => null),
		'status' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 3, 'comment' => '0：公開中
1：一時保存中
2：一時保存中(新規投稿->一時保存の場合)	　　新規投稿記事メール送信用'),
		'is_approved' => array('type' => 'boolean', 'null' => false, 'default' => '1', 'comment' => '承認済かどうか。'),
		'pre_change_flag' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => '変更前のコンテンツを表示するかどうか。'),
		'pre_change_date' => array('type' => 'datetime', 'null' => true, 'default' => null, 'comment' => '公開日付（pre_change_flagがONの場合、指定することで、自動的にに最新の記事が公開される。）'),
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
