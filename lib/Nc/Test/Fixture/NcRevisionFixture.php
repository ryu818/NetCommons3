<?php
/**
 * Session
 */

class NcRevisionFixture extends CakeTestFixture {

	public $useDbConfig = 'test';
	public $name = 'Revision';


	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary', 'comment' => 'リビジョンID。WYSIWYGの情報を履歴管理。'),
		'group_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'key' => 'index', 'comment' => '１つの記事毎のリビジョン一覧のグループID。記事(WYSIWYG)の新規登録時のRevision.idをセット。'),
		'pointer' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => '現在、表示位置（group_id毎に１つONとなる）。記事追加、編集時に該当記事をONにする(そのほかのリビジョンがOFFに戻す)。但し、revision_nameが\'auto-draft\'の編集時は、ONにしない。'),
		'is_approved_pointer' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => '承認済表示位置（承認済の記事すべてがONとなる）。承認前の記事にpointerがついていた場合、以前の履歴から最新の承認済記事を表示させるため。'),
		'revision_name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 100, 'collate' => 'utf8_general_ci', 'comment' => '\'publish\'(公開中)、\'draft\'(一時保存中)、\'pending\'(承認待ち)、
　\'auto-draft\'(自動保存中)', 'charset' => 'utf8'),
		'content_id' => array('type' => 'integer', 'null' => false, 'default' => null),
		'content' => array('type' => 'text', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'WYSIWYGの記事コンテンツ。', 'charset' => 'utf8'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'created_user_id' => array('type' => 'integer', 'null' => true, 'default' => null),
		'created_user_name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified_user_id' => array('type' => 'integer', 'null' => true, 'default' => null),
		'modified_user_name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'group_id' => array('column' => array('group_id', 'pointer'), 'unique' => 0)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);

	public $records = array();
}