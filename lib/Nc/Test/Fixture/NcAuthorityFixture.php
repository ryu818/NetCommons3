<?php
/*
 * Authority
 * */

class NcAuthorityFixture extends CakeTestFixture {
	public $useDbConfig = 'test';
	public $name = 'Authority';

	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary', 'comment' => '権限ID（会員権限（ベース権限）毎の振る舞いと、ルーム権限における権限(hierarchy)を設定）'),
		'default_name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 64, 'collate' => 'utf8_general_ci', 'comment' => '権限名のデフォルトの項目名。新規追加か、langがenならば、default_nameを更新。該当言語の権限名がなければ、こちらを表示する。', 'charset' => 'utf8'),
		'system_flag' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'システムで使用するかどうか。ONの場合、権限管理から削除不可。'),
		'hierarchy' => array('type' => 'integer', 'null' => false, 'default' => '0', 'comment' => 'ヒエラルキー：この値の上下でモジュール内の記事を編集できるかどうかを判定する。モデレーター以上で同じレベル同士で編集可。
	0：不参加
	1：ゲスト
	101-200：一般
	201-300：モデレーター
	301-400：主担
	401-500：管理者'),
		'allow_creating_community' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 3, 'comment' => 'コミュニティー作成権限
	0：コミュニティー作成不可（デフォルト）
	1：非公開(参加者のみ)のコミュニティー作成可
	2：公開（すべてのログイン会員が閲覧可能）までのコミュニティー作成可
	3：公開・非公開「全会員を強制的に参加させる。」までのコミュニティー作成可。
	4：公開コミュニティーまで作成でき、すべてのコミュニティーの表示順変更、削除が可能'),
		'allow_new_participant' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'コミュニティーで主担ならば、参加会員を自由に追加でき、参加方法で「参加者のみ」を選択可能にするかどうか。この項目がOFFの場合、SNSのような振る舞いになる。'),
		'myportal_use_flag' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 3, 'comment' => 'マイポータルを使用するかどうか。'),
		'allow_myportal_viewing_hierarchy' => array('type' => 'integer', 'null' => false, 'default' => '0', 'comment' => 'マイポータルを閲覧できる権限
	（1,101,201,301のみ）'),
		'private_use_flag' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 3, 'comment' => 'マイルームを使用するかどうか。'),
		'public_createroom_flag' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'パブリックスペース内にルームの新規作成を許可するかどうか。'),
		'group_createroom_flag' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'コミュニティー内にルームの新規作成を許可するかどうか。'),
		'myportal_createroom_flag' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'マイポータル内にルームの新規作成を許可するかどうか（未使用）。'),
		'private_createroom_flag' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'マイルーム内にルームの新規作成を許可するかどうか（未使用）。'),
		'allow_htmltag_flag' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'HTMLタグの書き込み制限をするかどうか。'),
		'allow_meta_flag' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'ページ情報の変更を許すかどうか。'),
		'allow_theme_flag' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'ページテーマの変更を許すかどうか。'),
		'allow_style_flag' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 3, 'comment' => 'ページスタイルの変更を許すかどうか。
	0：許可しない。
	1：許可する。
	2：CSSの編集まで許可する。'),
		'allow_layout_flag' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'ページレイアウトの変更を許すかどうか。'),
		'allow_attachment' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 3, 'comment' => '編集画面からのファイルアップロードを行うかどうか。
	0:許可しない。
	1:画像のみ。
	2:画像、ファイル。'),
		'allow_video' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => '編集画面からの動画ファイル貼り付けを許すかどうか。'),
		'max_size' => array('type' => 'integer', 'null' => false, 'default' => '0', 'comment' => 'アップロードファイルの容量（バイト数）'),
		'change_leftcolumn_flag' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => '左カラムの変更を許すかどうか。'),
		'change_rightcolumn_flag' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => '右カラムの変更を許すかどうか。'),
		'change_headercolumn_flag' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'ヘッダーカラムの変更を許すかどうか。'),
		'change_footercolumn_flag' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'フッターカラムの変更を許すかどうか。'),
		'display_participants_editing' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => '参加者設定画面で使用するかどうか。'),
		'allow_move_operation' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'ページ・ブロックの移動を許可するかどうか。'),
		'allow_copy_operation' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'ページ・ブロックのコピーを許可するかどうか。'),
		'allow_shortcut_operation' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'ページ・ブロックのショートカット作成を許可するかどうか。'),
		'allow_operation_of_shortcut' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'ショートカットブロックのコピー・移動・ショートカット作成を許可するかどうか。'),
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

	public $records = array(
		array (
			'id' => '1',
			'default_name' => 'Administrator',
			'system_flag' => true,
			'hierarchy' => '500',
			'allow_creating_community' => '4',
			'allow_new_participant' => true,
			'myportal_use_flag' => '1',
			'allow_myportal_viewing_hierarchy' => '1',
			'private_use_flag' => '1',
			'public_createroom_flag' => true,
			'group_createroom_flag' => true,
			'myportal_createroom_flag' => false,
			'private_createroom_flag' => false,
			'allow_htmltag_flag' => true,
			'allow_meta_flag' => true,
			'allow_theme_flag' => true,
			'allow_style_flag' => '2',
			'allow_layout_flag' => true,
			'allow_attachment' => '2',
			'allow_video' => true,
			'max_size' => '1073741824',
			'change_leftcolumn_flag' => true,
			'change_rightcolumn_flag' => true,
			'change_headercolumn_flag' => true,
			'change_footercolumn_flag' => true,
			'display_participants_editing' => true,
			'allow_move_operation' => true,
			'allow_copy_operation' => true,
			'allow_shortcut_operation' => true,
			'allow_operation_of_shortcut' => true,
			'created' => NULL,
			'created_user_id' => '0',
			'created_user_name' => '',
			'modified' => '2013-11-12 09:06:14',
			'modified_user_id' => '1',
			'modified_user_name' => 'admin',
		),
		array (
			'id' => '2',
			'default_name' => 'Room Manager',
			'system_flag' => true,
			'hierarchy' => '350',
			'allow_creating_community' => '2',
			'allow_new_participant' => true,
			'myportal_use_flag' => '0',
			'allow_myportal_viewing_hierarchy' => '1',
			'private_use_flag' => '1',
			'public_createroom_flag' => true,
			'group_createroom_flag' => true,
			'myportal_createroom_flag' => false,
			'private_createroom_flag' => false,
			'allow_htmltag_flag' => false,
			'allow_meta_flag' => true,
			'allow_theme_flag' => true,
			'allow_style_flag' => '1',
			'allow_layout_flag' => true,
			'allow_attachment' => '2',
			'allow_video' => true,
			'max_size' => '104857600',
			'change_leftcolumn_flag' => false,
			'change_rightcolumn_flag' => false,
			'change_headercolumn_flag' => false,
			'change_footercolumn_flag' => false,
			'display_participants_editing' => true,
			'allow_move_operation' => true,
			'allow_copy_operation' => true,
			'allow_shortcut_operation' => true,
			'allow_operation_of_shortcut' => true,
			'created' => '2012-07-17 06:10:25',
			'created_user_id' => '0',
			'created_user_name' => '',
			'modified' => '2013-07-09 00:48:58',
			'modified_user_id' => '1',
			'modified_user_name' => 'admin',
		),
		array (
			'id' => '3',
			'default_name' => 'Moderator',
			'system_flag' => true,
			'hierarchy' => '250',
			'allow_creating_community' => '0',
			'allow_new_participant' => false,
			'myportal_use_flag' => '0',
			'allow_myportal_viewing_hierarchy' => '1',
			'private_use_flag' => '1',
			'public_createroom_flag' => false,
			'group_createroom_flag' => false,
			'myportal_createroom_flag' => false,
			'private_createroom_flag' => false,
			'allow_htmltag_flag' => false,
			'allow_meta_flag' => true,
			'allow_theme_flag' => true,
			'allow_style_flag' => '0',
			'allow_layout_flag' => false,
			'allow_attachment' => '2',
			'allow_video' => false,
			'max_size' => '52428800',
			'change_leftcolumn_flag' => false,
			'change_rightcolumn_flag' => false,
			'change_headercolumn_flag' => false,
			'change_footercolumn_flag' => false,
			'display_participants_editing' => true,
			'allow_move_operation' => false,
			'allow_copy_operation' => false,
			'allow_shortcut_operation' => false,
			'allow_operation_of_shortcut' => false,
			'created' => NULL,
			'created_user_id' => '0',
			'created_user_name' => '',
			'modified' => '2013-07-09 00:49:53',
			'modified_user_id' => '1',
			'modified_user_name' => 'admin',
		),
		array (
			'id' => '4',
			'default_name' => 'Common User',
			'system_flag' => true,
			'hierarchy' => '150',
			'allow_creating_community' => '0',
			'allow_new_participant' => false,
			'myportal_use_flag' => '1',
			'allow_myportal_viewing_hierarchy' => '1',
			'private_use_flag' => '1',
			'public_createroom_flag' => false,
			'group_createroom_flag' => false,
			'myportal_createroom_flag' => false,
			'private_createroom_flag' => false,
			'allow_htmltag_flag' => false,
			'allow_meta_flag' => true,
			'allow_theme_flag' => true,
			'allow_style_flag' => '0',
			'allow_layout_flag' => false,
			'allow_attachment' => '1',
			'allow_video' => false,
			'max_size' => '10485760',
			'change_leftcolumn_flag' => false,
			'change_rightcolumn_flag' => false,
			'change_headercolumn_flag' => false,
			'change_footercolumn_flag' => false,
			'display_participants_editing' => true,
			'allow_move_operation' => false,
			'allow_copy_operation' => false,
			'allow_shortcut_operation' => false,
			'allow_operation_of_shortcut' => false,
			'created' => '2012-07-17 06:10:37',
			'created_user_id' => '0',
			'created_user_name' => '',
			'modified' => '2013-07-08 11:11:47',
			'modified_user_id' => '1',
			'modified_user_name' => 'admin',
		),
		array (
			'id' => '5',
			'default_name' => 'Guest',
			'system_flag' => true,
			'hierarchy' => '1',
			'allow_creating_community' => '0',
			'allow_new_participant' => false,
			'myportal_use_flag' => '0',
			'allow_myportal_viewing_hierarchy' => '1',
			'private_use_flag' => '0',
			'public_createroom_flag' => false,
			'group_createroom_flag' => false,
			'myportal_createroom_flag' => false,
			'private_createroom_flag' => false,
			'allow_htmltag_flag' => false,
			'allow_meta_flag' => true,
			'allow_theme_flag' => true,
			'allow_style_flag' => '0',
			'allow_layout_flag' => false,
			'allow_attachment' => '0',
			'allow_video' => false,
			'max_size' => '10485760',
			'change_leftcolumn_flag' => false,
			'change_rightcolumn_flag' => false,
			'change_headercolumn_flag' => false,
			'change_footercolumn_flag' => false,
			'display_participants_editing' => true,
			'allow_move_operation' => false,
			'allow_copy_operation' => false,
			'allow_shortcut_operation' => false,
			'allow_operation_of_shortcut' => false,
			'created' => '2012-07-17 06:10:51',
			'created_user_id' => '0',
			'created_user_name' => '',
			'modified' => '2012-07-17 06:10:51',
			'modified_user_id' => '1',
			'modified_user_name' => 'admin',
		),
			array (
				'id' => '6',
				'default_name' => 'Clerk',
				'system_flag' => true,
				'hierarchy' => '400',
				'allow_creating_community' => '2',
				'allow_new_participant' => true,
				'myportal_use_flag' => '0',
				'allow_myportal_viewing_hierarchy' => '1',
				'private_use_flag' => '1',
				'public_createroom_flag' => true,
				'group_createroom_flag' => true,
				'myportal_createroom_flag' => false,
				'private_createroom_flag' => false,
				'allow_htmltag_flag' => false,
				'allow_meta_flag' => true,
				'allow_theme_flag' => true,
				'allow_style_flag' => '1',
				'allow_layout_flag' => true,
				'allow_attachment' => '2',
				'allow_video' => true,
				'max_size' => '104857600',
				'change_leftcolumn_flag' => false,
				'change_rightcolumn_flag' => false,
				'change_headercolumn_flag' => false,
				'change_footercolumn_flag' => false,
				'display_participants_editing' => false,
				'allow_move_operation' => true,
				'allow_copy_operation' => true,
				'allow_shortcut_operation' => true,
				'allow_operation_of_shortcut' => true,
				'created' => '2012-07-17 06:10:25',
				'created_user_id' => '0',
				'created_user_name' => '',
				'modified' => '2013-07-09 00:48:58',
				'modified_user_id' => '1',
				'modified_user_name' => 'admin',
			)
	);

}