<?php
/**
 * Upload Fixture
 */

class NcUploadFixture extends CakeTestFixture {

	public $useDbConfig = 'test';
	public $name = 'Upload';

	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary', 'comment' => 'アップロードID'),
		'user_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'comment' => '所有者会員ID'),
		'file_name' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'ファイル名', 'charset' => 'utf8'),
		'alt' => array('type' => 'text', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'ファイルalt属性', 'charset' => 'utf8'),
		'caption' => array('type' => 'text', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'ファイルキャプション名（未使用）', 'charset' => 'utf8'),
		'description' => array('type' => 'text', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'ファイル説明', 'charset' => 'utf8'),
		'file_size' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 20, 'comment' => 'ファイルサイズ'),
		'file_path' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'ファイルパス', 'charset' => 'utf8'),
		'mimetype' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'MIMEタイプ
画像,音声,動画などの判断に使用', 'charset' => 'utf8'),
		'extension' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '拡張子', 'charset' => 'utf8'),
		'plugin' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'comment' => 'アップロードを行ったモジュールディレクトリ名', 'charset' => 'utf8'),
		'upload_model_name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'comment' => 'アップロードを行ったモデル名', 'charset' => 'utf8'),
		'is_delete_from_library' => array('type' => 'boolean', 'null' => false, 'default' => '1', 'comment' => 'アップロードのライブラリー一覧から削除を許すかどうか。'),
		'is_use' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => '使用フラグ
UploadLinkの対応するレコードが１件もない場合は「0」となる'),
		'is_wysiwyg' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'WYSIWYGでアップロードされたかどうか。'),
		'download_count' => array('type' => 'integer', 'null' => false, 'default' => '0', 'comment' => 'ダウンロード数'),
		'year' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 4, 'comment' => 'アップロード年'),
		'month' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 2, 'comment' => 'アップロード月'),
		'day' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 2, 'comment' => 'アップロード日付'),
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