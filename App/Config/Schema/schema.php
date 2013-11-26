<?php
namespace App\Config\Schema;

use Migrations\Model\CakeSchema;

class AppSchema extends CakeSchema {

	public function before($event = array()) {
		return true;
	}

	public function after($event = array()) {
	}

	public $archives = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary', 'comment' => 'アーカイブID。新着、検索用のデータを管理。記事投稿、編集、削除時に登録。新着、検索モジュールから表示させる。'),
		'parent_model_name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'comment' => '親記事のモデル名称', 'charset' => 'utf8'),
		'parent_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'comment' => '親記事のID（根記事ID等。ブログならば親記事。）'),
		'module_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'index'),
		'content_id' => array('type' => 'integer', 'null' => false, 'default' => null),
		'model_name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'comment' => '記事モデル名称', 'charset' => 'utf8'),
		'unique_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'comment' => '記事ID'),
		'status' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 3, 'comment' => '記事の状態
0:公開中
1: 一時保存中
2:一時保存中(新規投稿->一時保存の場合)	新規投稿時にメール送信用'),
		'is_approved' => array('type' => 'boolean', 'null' => false, 'default' => '1', 'comment' => '承認された記事かどうか。'),
		'user_group_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'comment' => '会員グループID。指定されていれば、所属グループのみ表示する新着（検索）であることを表す。'),
		'access_hierarchy' => array('type' => 'integer', 'null' => false, 'default' => '0', 'comment' => 'アクセス権限
（0,101,201,301のみ）'),
		'count' => array('type' => 'integer', 'null' => false, 'default' => '0', 'comment' => '記事数（1記事にコメントが2件登録してある記事、及びコメントならば、count=3となる）'),
		'title' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '新着（検索）タイトル', 'charset' => 'utf8'),
		'content' => array('type' => 'text', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '新着詳細', 'charset' => 'utf8'),
		'search_content' => array('type' => 'text', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '検索詳細。', 'charset' => 'utf8'),
		'url' => array('type' => 'text', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '新着（検索）リンク先URL。', 'charset' => 'utf8'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'created_user_id' => array('type' => 'integer', 'null' => true, 'default' => null),
		'created_user_name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified_user_id' => array('type' => 'integer', 'null' => true, 'default' => null),
		'modified_user_name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'module_id' => array('column' => array('module_id', 'model_name', 'unique_id'), 'unique' => 0)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);

	public $assets = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary', 'comment' => 'AssetID(CSS,JSを連結して書き出した一覧)
app\\webroot\\theme\\assets下に圧縮したものと、そうでないもののCSS,JSを保持。'),
		'url' => array('type' => 'string', 'null' => false, 'default' => null, 'key' => 'unique', 'collate' => 'utf8_general_ci', 'comment' => 'ファイルパス', 'charset' => 'utf8'),
		'hash_content' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 32, 'collate' => 'utf8_general_ci', 'comment' => 'ファイルコンテンツをHash値した値をセット。', 'charset' => 'utf8'),
		'plugin' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'プラグイン名称ORJs,Css,Nc-Locale,(file_name)', 'charset' => 'utf8'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'url' => array('column' => 'url', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);

	public $authorities = array(
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
1：参加者のみのコミュニティー作成可
2：一部公開（すべてのログイン会員が閲覧可能）までのコミュニティー作成可
3：公開（すべてのユーザーが閲覧可能）までのコミュニティー作成可
4：公開コミュニティーまで作成でき、すべてのコミュニティーの表示順変更、削除が可能'),
		'allow_new_participant' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'コミュニティーで主担ならば、新規に参加者の追加を許すかどうか。新規追加を許さない場合、SNSのような振る舞いになる。'),
		'myportal_use_flag' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 3, 'comment' => 'マイポータルを使用するかどうか。'),
		'allow_myportal_viewing_hierarchy' => array('type' => 'integer', 'null' => false, 'default' => '0', 'comment' => 'マイポータルを閲覧できる権限
（1,101,201,301のみ）'),
		'private_use_flag' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 3, 'comment' => 'マイルームを使用するかどうか。'),
		'public_createroom_flag' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'パブリックスペース内にルームの新規作成を許可するかどうか。'),
		'group_createroom_flag' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'コミュニティー内にルームの新規作成を許可するかどうか。'),
		'myportal_createroom_flag' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'マイポータル内にルームの新規作成を許可するかどうか（未使用）。'),
		'private_createroom_flag' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'マイルーム内にルームの新規作成を許可するかどうか（未使用）。'),
		'allow_htmltag_flag' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'HTMLタグの書き込み制限をするかどうか。'),
		'allow_layout_flag' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'ページスタイルのレイアウト変更を許すかどうか。'),
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

	public $authority_langs = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary', 'comment' => '権限言語ID'),
		'authority_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'key' => 'index'),
		'lang' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 10, 'collate' => 'utf8_general_ci', 'comment' => '言語(ja,en等)', 'charset' => 'utf8'),
		'name' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '権限名', 'charset' => 'utf8'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'created_user_id' => array('type' => 'integer', 'null' => true, 'default' => null),
		'created_user_name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified_user_id' => array('type' => 'integer', 'null' => true, 'default' => null),
		'modified_user_name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'authority_id' => array('column' => array('authority_id', 'lang'), 'unique' => 0)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);

	public $backgrounds = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary', 'comment' => 'バックグラウンドID
「\\webroot\\img\\backgrounds\\patterns」、「\\webroot\\img\\backgrounds\\images」にあるファイルからページスタイル背景用のマスタを作成。'),
		'group_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'type' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 100, 'collate' => 'utf8_general_ci', 'comment' => 'Locale/(jang)/LC_MESSAGE/background.poの名称、「\\webroot\\img\\backgrounds\\patterns(images)」下フォルダ名称に対応する。フォルダでなければ、ファイル名のキャメル記法。', 'charset' => 'utf8'),
		'category' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 100, 'collate' => 'utf8_general_ci', 'comment' => 'Plugin/page/Locale/(jang)/LC_MESSAGE/page.poの背景キーワード名称、「\\webroot\\img\\backgrounds\\patterns(images)」下のファイル名の「_」までの先頭文字列（キャメル記法）。', 'charset' => 'utf8'),
		'color' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 100, 'collate' => 'utf8_general_ci', 'comment' => 'Plugin/page/Locale/(jang)/LC_MESSAGE/page.poの背景色名称、「\\webroot\\img\\backgrounds\\patterns(images)」下のファイル名の「_」までの第2番目の文字列（キャメル記法）。', 'charset' => 'utf8'),
		'file_path' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '「\\webroot\\img\\backgrounds\\patterns(images)」下のファイルパス。', 'charset' => 'utf8'),
		'file_width' => array('type' => 'integer', 'null' => false, 'default' => null, 'comment' => '背景ファイルの広さ'),
		'file_height' => array('type' => 'integer', 'null' => false, 'default' => null, 'comment' => '背景ファイルの高さ'),
		'file_size' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 20, 'comment' => '背景ファイルのサイズ'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);

	public $blocks = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary', 'comment' => 'ブロックID（ページ内にモジュールを配置した際に割り振られるID）'),
		'page_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'key' => 'index'),
		'content_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'module_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'title' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'ブロックタイトル（{X-CONTENT}と記述されていればContent.titleをブロックタイトルとして表示）', 'charset' => 'utf8'),
		'show_title' => array('type' => 'boolean', 'null' => false, 'default' => '1', 'comment' => 'タイトルを表示するかどうか。'),
		'controller_action' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'ページからブロックを表示する際のコントロール、アクション名', 'charset' => 'utf8'),
		'root_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'comment' => 'ブロックルートID（ブロックはグループ化することにより、深さをもつため）'),
		'parent_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'comment' => 'ブロック親ID（ブロックはグループ化することにより、深さをもつため）'),
		'thread_num' => array('type' => 'integer', 'null' => false, 'default' => '0', 'comment' => 'ブロック深さ（ブロックはグループ化することにより、深さをもつため）'),
		'col_num' => array('type' => 'integer', 'null' => false, 'default' => '0', 'comment' => 'ブロック列番号'),
		'row_num' => array('type' => 'integer', 'null' => false, 'default' => '0', 'comment' => 'ブロック行番号'),
		'display_flag' => array('type' => 'boolean', 'null' => false, 'default' => '1', 'comment' => '公開中かどうか。'),
		'display_from_date' => array('type' => 'datetime', 'null' => true, 'default' => null, 'comment' => '公開日付From'),
		'display_to_date' => array('type' => 'datetime', 'null' => true, 'default' => null, 'comment' => '公開日付To'),
		'theme_name' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'ブロックテーマ名', 'charset' => 'utf8'),
		'temp_name' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'ブロックテンプレート名', 'charset' => 'utf8'),
		'left_margin' => array('type' => 'integer', 'null' => true, 'default' => null, 'comment' => 'ブロックレフトマージン'),
		'right_margin' => array('type' => 'integer', 'null' => true, 'default' => null, 'comment' => 'ブロックライトマージン'),
		'top_margin' => array('type' => 'integer', 'null' => true, 'default' => null, 'comment' => 'ブロックトップマージン'),
		'bottom_margin' => array('type' => 'integer', 'null' => true, 'default' => null, 'comment' => 'ブロックボトムマージン'),
		'min_width_size' => array('type' => 'integer', 'null' => false, 'default' => '0', 'comment' => 'ブロック最小の広さ'),
		'min_height_size' => array('type' => 'integer', 'null' => false, 'default' => '0', 'comment' => 'ブロック最小の高さ'),
		'lock_authority_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'comment' => 'ロックされたブロックかどうか（現状、未使用）。ロックされるとブロックの削除、ブロック操作ができなくなる。'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'created_user_id' => array('type' => 'integer', 'null' => true, 'default' => null),
		'created_user_name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified_user_id' => array('type' => 'integer', 'null' => true, 'default' => null),
		'modified_user_name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'page_id' => array('column' => 'page_id', 'unique' => 0)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);

	public $communities = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary'),
		'room_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'key' => 'index'),
		'photo' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'コミュニティーの写真(選択ファイル名 OR (Upload.id).(extension))', 'charset' => 'utf8'),
		'is_upload' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'アップロードファイルを指定したかどうか。'),
		'publication_range_flag' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 3, 'comment' => '公開範囲
0:参加者のみ（コミュニティー参加者のみが閲覧可能）
1:一部公開（すべてのログイン会員が閲覧可能）
2:公開（すべてのユーザーが閲覧可能）'),
		'participate_as_general' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => '公開コミュニティーの場合のみ指定。一般として参加させるかどうか。チェックがついていた場合、会員新規登録時、コミュニティー登録時にPageUserLinkテーブルに一般として登録する。'),
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

	public $community_langs = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary'),
		'room_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'key' => 'index'),
		'lang' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 10, 'collate' => 'utf8_general_ci', 'comment' => '言語(ja,en等)', 'charset' => 'utf8'),
		'community_name' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'コミュニティー名称', 'charset' => 'utf8'),
		'summary' => array('type' => 'text', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '概要', 'charset' => 'utf8'),
		'description' => array('type' => 'text', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '詳細', 'charset' => 'utf8'),
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

	public $community_sum_tags = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary'),
		'tag_value' => array('type' => 'string', 'null' => false, 'default' => null, 'key' => 'index', 'collate' => 'utf8_general_ci', 'comment' => 'タグ名称', 'charset' => 'utf8'),
		'lang' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 10, 'collate' => 'utf8_general_ci', 'comment' => '言語(ja,en等)', 'charset' => 'utf8'),
		'used_number' => array('type' => 'integer', 'null' => false, 'default' => '0', 'comment' => '該当タグ使用回数。'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'created_user_id' => array('type' => 'integer', 'null' => true, 'default' => null),
		'created_user_name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified_user_id' => array('type' => 'integer', 'null' => true, 'default' => null),
		'modified_user_name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'tag_value_lang' => array('column' => array('tag_value', 'lang'), 'unique' => 1),
			'tag_value' => array('column' => 'tag_value', 'unique' => 0)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);

	public $community_tags = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary'),
		'room_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'key' => 'index'),
		'community_sum_tag_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'tag_value' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'タグ名称', 'charset' => 'utf8'),
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

	public $config_langs = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary', 'comment' => 'Config言語ID'),
		'module_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'key' => 'index', 'comment' => '現状、未使用'),
		'config_name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 64, 'collate' => 'utf8_general_ci', 'comment' => 'Configキー名称', 'charset' => 'utf8'),
		'lang' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 10, 'collate' => 'utf8_general_ci', 'comment' => '言語(ja,en等)', 'charset' => 'utf8'),
		'value' => array('type' => 'text', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'Config値。', 'charset' => 'utf8'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'created_user_id' => array('type' => 'integer', 'null' => true, 'default' => null),
		'created_user_name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified_user_id' => array('type' => 'integer', 'null' => true, 'default' => null),
		'modified_user_name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'module_id' => array('column' => array('module_id', 'config_name', 'lang'), 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);

	public $configs = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary', 'comment' => 'ConfigID（システム管理のデータ一覧。今後、モジュール毎の設定値の保存用としても使用するかもしれないが、現状、未使用）'),
		'module_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'key' => 'index', 'comment' => '現状、未使用'),
		'cat_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'comment' => 'カテゴリーID
0：一般設定
1：ログインとログアウト、サイトの閉鎖
2：サーバー設定
3：メール設定
4：メタ情報
5：表示設定
6：モジュール設定
7：入会退会設定
8：コミュニティー設定、自動登録設定
9：開発者向け
10：セキュリティー関連'),
		'name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 64, 'key' => 'index', 'collate' => 'utf8_general_ci', 'comment' => 'Configキー名称', 'charset' => 'utf8'),
		'type' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 20, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'title' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'Config名デフォルト値（__関数で変換して表示）', 'charset' => 'utf8'),
		'value' => array('type' => 'text', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'Configデフォルト値（__d関数で変換して表示）', 'charset' => 'utf8'),
		'description' => array('type' => 'text', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'Config説明デフォルト値（__d関数で変換して表示）', 'charset' => 'utf8'),
		'options' => array('type' => 'text', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'Configオプションデフォルト値（__d関数で変換して表示）。シリアライズしてセット。', 'charset' => 'utf8'),
		'domain' => array('type' => 'string', 'null' => false, 'default' => 'system', 'length' => 64, 'collate' => 'utf8_general_ci', 'comment' => '__dの第一引数のdomain指定。', 'charset' => 'utf8'),
		'attribute' => array('type' => 'text', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '属性値（例：size="30"等）', 'charset' => 'utf8'),
		'required' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => '必須入力かどうか。'),
		'minlength' => array('type' => 'integer', 'null' => false, 'default' => '0', 'comment' => '最小文字数'),
		'maxlength' => array('type' => 'integer', 'null' => false, 'default' => '0', 'comment' => '最大文字数'),
		'regexp' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '正規表現', 'charset' => 'utf8'),
		'lang_flag' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'ConfigLangモデルにデータを保持するかどうか。'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'created_user_id' => array('type' => 'integer', 'null' => true, 'default' => null),
		'created_user_name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified_user_id' => array('type' => 'integer', 'null' => true, 'default' => null),
		'modified_user_name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'name' => array('column' => array('name', 'module_id'), 'unique' => 0),
			'module_id' => array('column' => array('module_id', 'cat_id'), 'unique' => 0)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);

	public $contents = array(
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

	public $htmlarea_video_urls = array(
		'url' => array('type' => 'text', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'WYSIWYGの取り込み可能VideoUrl', 'charset' => 'utf8'),
		'indexes' => array(

		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);

	public $languages = array(
		'language' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 8, 'key' => 'primary', 'collate' => 'utf8_general_ci', 'comment' => '言語(ja,en等)', 'charset' => 'utf8'),
		'display_name' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '表示名(Japanese,English等。__関数で変換したものを表示させる。)', 'charset' => 'utf8'),
		'display_sequence' => array('type' => 'integer', 'null' => false, 'default' => '0', 'comment' => '表示順序。'),
		'display_flag' => array('type' => 'integer', 'null' => false, 'default' => '1', 'length' => 3, 'key' => 'index', 'comment' => '公開フラグ
1:公開
0:非公開'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'language', 'unique' => 1),
			'display_flag' => array('column' => array('display_flag', 'display_sequence'), 'unique' => 0)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);

	public $module_links = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary', 'comment' => 'モジュールリンクID（ルームごとの配置可能一般モジュールの設定）'),
		'space_type' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 3),
		'authority_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'comment' => 'マイポータル、マイルームで配置可能なモジュールのみ権限ID毎で設定可能（権限管理）'),
		'room_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'key' => 'index', 'comment' => 'マイポータル、マイルームで配置可能なモジュールの設定、各スペースタイプのデフォルト値のみ0を設定。'),
		'module_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'created_user_id' => array('type' => 'integer', 'null' => true, 'default' => null),
		'created_user_name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified_user_id' => array('type' => 'integer', 'null' => true, 'default' => null),
		'modified_user_name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'room_id' => array('column' => array('room_id', 'authority_id', 'space_type'), 'unique' => 0)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);

	public $module_system_links = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary', 'comment' => 'モジュールシステムリンクID（表示する管理系モジュールを権限毎に設定）'),
		'authority_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'module_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'hierarchy' => array('type' => 'integer', 'null' => false, 'default' => '0', 'comment' => '管理系のモジュールを表示した際の管理系モジュールの権限を設定。一部管理系モジュールにて使用（会員管理等）。'),
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

	public $modules = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary', 'comment' => 'モジュールID'),
		'dir_name' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'モジュールディレクトリ名', 'charset' => 'utf8'),
		'version' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 100, 'collate' => 'utf8_general_ci', 'comment' => 'モジュールVersion', 'charset' => 'utf8'),
		'system_flag' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'システムで使用するかどうか。ONの場合、モジュール管理から削除不可。'),
		'disposition_flag' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'モジュールインストール時に利用可能モジュールにデフォルト設定するかどうか。'),
		'controller_action' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'ブロック追加時（管理系モジュールならば、コントロールパネルからアイコンクリック時）、表示コントローラ-アクション名。', 'charset' => 'utf8'),
		'edit_controller_action' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'ブロック編集時（ブロック上部アイコンより遷移する画面）、表示コントローラ-アクション名。', 'charset' => 'utf8'),
		'style_controller_action' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 266, 'collate' => 'utf8_general_ci', 'comment' => 'ブロック表示方法変更クリック時（ブロック上部操作->表示方法変更リンクより遷移する画面）、表示コントローラ-アクション名。', 'charset' => 'utf8'),
		'display_sequence' => array('type' => 'integer', 'null' => false, 'default' => null, 'comment' => '表示順序（モジュール追加のリストボックス、会員管理の表示順で使用）。'),
		'module_icon' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 100, 'collate' => 'utf8_general_ci', 'comment' => 'モジュールアイコン名（管理系モジュールのみ使用）', 'charset' => 'utf8'),
		'temp_name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 100, 'collate' => 'utf8_general_ci', 'comment' => 'モジュールデフォルトテンプレート名（未使用）', 'charset' => 'utf8'),
		'content_has_one' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 3, 'comment' => 'カレンダー等のブロックに依存しないモジュールは、モジュールの追加の時にcontentsテーブルを
　　　ルーム毎で唯一insertできるようにしておくために用意したカラム（現状、未使用。使用するかどうかは今後、検討）'),
		'copy_operation' => array('type' => 'string', 'null' => false, 'default' => 'disabled', 'length' => 16, 'collate' => 'utf8_general_ci', 'comment' => 'モジュールコピー操作を可能にするかどうか
enable：使用可能だがデフォルト使用不可(システム管理より変更可)
enabled ：使用可能
disabled：使用不可', 'charset' => 'utf8'),
		'shortcut_operation' => array('type' => 'string', 'null' => false, 'default' => 'disabled', 'length' => 16, 'collate' => 'utf8_general_ci', 'comment' => 'モジュールショートカット操作を可能にするかどうか
enable：使用可能だがデフォルト使用不可(システム管理より変更可)
enabled ：使用可能
disabled：使用不可', 'charset' => 'utf8'),
		'move_operation' => array('type' => 'string', 'null' => false, 'default' => 'disabled', 'length' => 16, 'collate' => 'utf8_general_ci', 'comment' => 'モジュール移動操作を可能にするかどうか
enable：使用可能だがデフォルト使用不可(システム管理より変更可)
enabled ：使用可能
disabled：使用不可', 'charset' => 'utf8'),
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

	public $page_columns = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary', 'comment' => 'ページカラム情報ID（ページ設定->ページカラム設定）'),
		'scope' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 3, 'comment' => '適用範囲
1：サイト全体
2：スペースタイプ全体
3：ルーム
4：ノード
5：カレントページのみ'),
		'type' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 3, 'comment' => 'カテゴリわけしたい場合に使用（未使用）'),
		'lang' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 10, 'collate' => 'utf8_general_ci', 'comment' => '適用言語(ja,en等)', 'charset' => 'utf8'),
		'space_type' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 2, 'comment' => 'scopeが2以上で設定。'),
		'page_id' => array('type' => 'integer', 'null' => true, 'default' => '0', 'comment' => 'scopeが3以上で設定。'),
		'header_page_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'comment' => 'ヘッダーカラムページID'),
		'left_page_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'comment' => 'レフトカラムページID'),
		'right_page_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'comment' => 'ライトカラムページID'),
		'footer_page_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'comment' => 'フッターカラムページID'),
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

	public $page_layouts = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary', 'comment' => 'ページレイアウト情報ID（ページ設定->ページレイアウト）
'),
		'scope' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 3, 'comment' => '適用範囲
1：サイト全体
2：スペースタイプ全体
3：ルーム
4：ノード
5：カレントページのみ'),
		'type' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 3, 'comment' => 'カテゴリわけしたい場合に使用（未使用）'),
		'lang' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 10, 'collate' => 'utf8_general_ci', 'comment' => '適用言語(ja,en等)', 'charset' => 'utf8'),
		'space_type' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 2, 'comment' => 'scopeが2以上で設定。'),
		'page_id' => array('type' => 'integer', 'null' => true, 'default' => '0', 'comment' => 'scopeが3以上で設定。'),
		'is_display_header' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'ヘッダーカラムを表示するかどうか。'),
		'is_display_left' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'レフトカラムを表示するかどうか。'),
		'is_display_right' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'ライトカラムを表示するかどうか。'),
		'is_display_footer' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'フッターカラムを表示するかどうか。'),
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

	public $page_metas = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary', 'comment' => 'ページメタ情報ID（ページ設定->ページ情報）'),
		'scope' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 3, 'comment' => '適用範囲
1：サイト全体
2：スペースタイプ全体
3：ルーム
4：ノード
5：カレントページのみ'),
		'type' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 3, 'comment' => 'カテゴリわけしたい場合に使用（未使用）'),
		'lang' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 10, 'collate' => 'utf8_general_ci', 'comment' => '適用言語(ja,en等)', 'charset' => 'utf8'),
		'space_type' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 2, 'comment' => 'scopeが2以上で設定。'),
		'page_id' => array('type' => 'integer', 'null' => true, 'default' => '0', 'comment' => 'scopeが3以上で設定。'),
		'title' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'タイトル名(titleタグ)', 'charset' => 'utf8'),
		'meta_keywords' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'キーワード名(metaタグ name="keywords")', 'charset' => 'utf8'),
		'meta_description' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'キーワード名(metaタグ name="description")', 'charset' => 'utf8'),
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

	public $page_styles = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary', 'comment' => 'ページスタイル情報ID（ページ設定->ページスタイル）'),
		'scope' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 3, 'comment' => '適用範囲
1：サイト全体
2：スペースタイプ全体
3：ルーム
4：ノード
5：カレントページのみ'),
		'type' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 3, 'comment' => 'タイプ毎に設定可能。
1：フォント設定
2：背景
3：表示位置
4：カスタム設定'),
		'lang' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 10, 'collate' => 'utf8_general_ci', 'comment' => '適用言語(ja,en等)', 'charset' => 'utf8'),
		'space_type' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 2, 'comment' => 'scopeが2以上で設定。'),
		'page_id' => array('type' => 'integer', 'null' => true, 'default' => '0', 'comment' => 'scopeが3以上で設定。'),
		'align' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'comment' => '行揃え
left：左寄せ
center：中央揃え
right：右寄せ', 'charset' => 'utf8'),
		'width' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'comment' => '最小の広さ(autoならば自動、100%、(%d)px)', 'charset' => 'utf8'),
		'height' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'comment' => '最小の高さ(autoならば自動、100%、(%d)px)', 'charset' => 'utf8'),
		'original_background_image' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'comment' => 'オリジナル背景画像ファイル名（(upload_id).(extension)）', 'charset' => 'utf8'),
		'original_background_repeat' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'comment' => 'オリジナル背景画像のrepeat(background-repeat)', 'charset' => 'utf8'),
		'original_background_position' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'comment' => 'オリジナル背景画像の位置(background-position)', 'charset' => 'utf8'),
		'original_background_attachment' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'comment' => '背景画像を固定する(background-attachment)', 'charset' => 'utf8'),
		'file' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 48, 'collate' => 'utf8_general_ci', 'comment' => '選択CSSファイル名', 'charset' => 'utf8'),
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

	public $page_sum_views = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary', 'comment' => 'ページ集計ID（ページ設定->よく見るページ）'),
		'user_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'page_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'sum' => array('type' => 'integer', 'null' => false, 'default' => '0', 'comment' => 'ページビュー数'),
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

	public $page_themes = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary', 'comment' => 'ページテーマ情報ID（ページ設定->ページテーマ）'),
		'scope' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 3, 'comment' => '適用範囲
1：サイト全体
2：スペースタイプ全体
3：ルーム
4：ノード
5：カレントページのみ'),
		'type' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 3, 'comment' => 'カテゴリわけしたい場合に使用（未使用）'),
		'lang' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 10, 'collate' => 'utf8_general_ci', 'comment' => '適用言語(ja,en等)', 'charset' => 'utf8'),
		'space_type' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 2, 'comment' => 'scopeが2以上で設定。'),
		'page_id' => array('type' => 'integer', 'null' => true, 'default' => '0', 'comment' => 'scopeが3以上で設定。'),
		'theme_name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'comment' => 'テーマ名', 'charset' => 'utf8'),
		'temp_name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'comment' => 'テンプレート名（未使用）', 'charset' => 'utf8'),
		'center_theme' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'comment' => 'センターカラムテーマ名', 'charset' => 'utf8'),
		'header_theme' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'comment' => 'ヘッダーカラムテーマ名', 'charset' => 'utf8'),
		'left_theme' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'comment' => 'レフトカラムテーマ名', 'charset' => 'utf8'),
		'right_theme' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'comment' => 'ライトカラムテーマ名', 'charset' => 'utf8'),
		'footer_theme' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'comment' => 'フッターカラムテーマ名', 'charset' => 'utf8'),
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

	public $page_user_links = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary'),
		'room_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'key' => 'index'),
		'user_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'authority_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'created_user_id' => array('type' => 'integer', 'null' => true, 'default' => null),
		'created_user_name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified_user_id' => array('type' => 'integer', 'null' => true, 'default' => null),
		'modified_user_name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'room_id' => array('column' => array('room_id', 'user_id'), 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);

	public $pages = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary', 'comment' => 'ページID（ページとルームの情報を保持）'),
		'root_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'comment' => 'ルートページID（深さ１のページをルートとする。深さ0のものは0）'),
		'parent_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'comment' => '親ページID（深さ0のものは0）'),
		'thread_num' => array('type' => 'integer', 'null' => false, 'default' => '0', 'comment' => '深さ（ルートノードは0）'),
		'display_sequence' => array('type' => 'integer', 'null' => false, 'default' => '0', 'comment' => '深さ１のページ単位で1から連番を振る。深さ１より大きいものは、そのノード単位で1から連番を振る(レフト・ライト、ヘッダー、フッターカラムは0)。'),
		'page_name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 30, 'collate' => 'utf8_general_ci', 'comment' => 'ページ名', 'charset' => 'utf8'),
		'permalink' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '固定リンク', 'charset' => 'utf8'),
		'position_flag' => array('type' => 'boolean', 'null' => false, 'default' => '1', 'comment' => 'センターカラムならば1'),
		'lang' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 10, 'collate' => 'utf8_general_ci', 'comment' => '言語(ja,en等)', 'charset' => 'utf8'),
		'is_page_meta_node' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'ページメタ情報が現ページID、またはノードに設定されているかどうか。'),
		'is_page_style_node' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'ページスタイル情報が現ページID、またはノードに設定されているかどうか。'),
		'is_page_layout_node' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'ページレイアウト情報が現ページID、またはノードに設定されているかどうか。'),
		'is_page_theme_node' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'ページテーマ情報が現ページID、またはノードに設定されているかどうか。'),
		'is_page_column_node' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'ページカラム情報が現ページID、またはノードに設定されているかどうか。'),
		'room_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'space_type' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 2, 'comment' => 'スペースタイプ
1：パブリックスペース
2：マイポータル
3：プライベートスペース（マイルーム）
4：コミュニティー'),
		'show_count' => array('type' => 'integer', 'null' => false, 'default' => '0', 'comment' => 'ページのブロックの追加、移動、削除時にインクリメント。複数人が同時にブロックの配置を変更するとBlock.row_num,Block.col_numが狂うため、同時に編集を許さないようにするために用いる。'),
		'display_flag' => array('type' => 'integer', 'null' => false, 'default' => '1', 'length' => 2, 'comment' => '公開中かどうか。'),
		'display_from_date' => array('type' => 'datetime', 'null' => true, 'default' => null, 'comment' => '公開日付From'),
		'display_to_date' => array('type' => 'datetime', 'null' => true, 'default' => null, 'comment' => '公開日付To'),
		'display_apply_subpage' => array('type' => 'boolean', 'null' => false, 'default' => '1', 'comment' => '公開日付Fromで公開になった場合に下位ページにも適用するかどうか。'),
		'display_reverse_permalink' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'is_approved' => array('type' => 'boolean', 'null' => false, 'default' => '1', 'comment' => '承認済ページかどうか（現状、未使用）'),
		'lock_authority_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'comment' => 'ロックされたページかどうか（現状、未使用）。ロックされるとページのブロックの追加等の操作ができなくなる。'),
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

	public $passports = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary', 'comment' => 'パスポートID（自動ログインパスポート保存用）'),
		'user_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'passport' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 60, 'collate' => 'utf8_general_ci', 'comment' => '自動ログイン用パスポート（クッキーに記録）', 'charset' => 'utf8'),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);

	public $revisions = array(
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

	public $sessions = array(
		'id' => array('type' => 'string', 'null' => false, 'default' => null, 'key' => 'primary', 'collate' => 'utf8_general_ci', 'comment' => 'sessionID', 'charset' => 'utf8'),
		'data' => array('type' => 'text', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'sessionデータ', 'charset' => 'utf8'),
		'expires' => array('type' => 'integer', 'null' => true, 'default' => null, 'comment' => '有効期限'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);

	public $temp_datas = array(
		'id' => array('type' => 'string', 'null' => false, 'default' => null, 'key' => 'primary', 'collate' => 'utf8_general_ci', 'comment' => '一時保存用ID（現状、コピー元、先ページIDで使用）。SessionID単位ではなく、一時的にデータを保持する場合に使用する。', 'charset' => 'utf8'),
		'data' => array('type' => 'text', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '一時保存データ', 'charset' => 'utf8'),
		'expires' => array('type' => 'integer', 'null' => true, 'default' => null, 'comment' => '有効期限'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);

	public $upload_links = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary', 'comment' => 'アップロードリンクID'),
		'upload_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'key' => 'index'),
		'plugin' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'comment' => 'アップロードを行ったモジュールディレクトリ名', 'charset' => 'utf8'),
		'content_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'unique_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'comment' => 'アップロードを行った記事等のユニークID'),
		'model_name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'comment' => 'アップロードを行った記事等（WYSIWYGならばRevision）のモデル名', 'charset' => 'utf8'),
		'field_name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'comment' => 'アップロードを行った記事等（WYSIWYGならばcontent）のフィールド名', 'charset' => 'utf8'),
		'access_hierarchy' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 3, 'comment' => '表示可能権限
0,101,201,301のみ'),
		'is_use' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 4, 'comment' => 'ファイル使用中かどうか。
Revisionから復元処理があるため、一度、記事として登録されたファイルは、登録される。'),
		'download_password' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'ダウンロード用パスワード', 'charset' => 'utf8'),
		'check_component_action' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '表示チェック用コンポーネント、アクション名をカンマ区切りで設定。記述コンポーネント、アクション名がすべてtrueならば閲覧可能（アクション名のdefaultはcheckメソッド）。
[プラグイン名].[コンポーネント名][アクション名]（Camel形式）の形式で登録。', 'charset' => 'utf8'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'created_user_id' => array('type' => 'integer', 'null' => true, 'default' => null),
		'created_user_name' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified_user_id' => array('type' => 'integer', 'null' => true, 'default' => null),
		'modified_user_name' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'upload_id' => array('column' => array('upload_id', 'id'), 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);

	public $uploads = array(
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

	public $user_group_links = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary', 'comment' => 'グループリンクID（グループに誰が所属しているかを指定）'),
		'user_group_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'comment' => 'Group.id'),
		'user_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
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

	public $user_groups = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary', 'comment' => 'グループID（回覧板モジュールのようにグループ単位で新着、検索情報の表示可否を行いたい場合に使用）'),
		'module_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'content_id' => array('type' => 'integer', 'null' => false, 'default' => null),
		'name' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'グループ名', 'charset' => 'utf8'),
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

	public $user_item_authority_links = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary', 'comment' => '会員項目のベース権限毎の編集・閲覧権限ID'),
		'user_item_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'key' => 'index'),
		'user_authority_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 3, 'comment' => '会員権限（ベース権限）
1:管理者
2:主担
3:モデレーター
4:一般
5:ゲスト'),
		'edit_lower_hierarchy' => array('type' => 'integer', 'null' => false, 'default' => '0', 'comment' => '編集権限
（0,1,101,201,301,401のみ）'),
		'show_lower_hierarchy' => array('type' => 'integer', 'null' => false, 'default' => '0', 'comment' => '閲覧権限
（0,1,101,201,301,401のみ）'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'created_user_id' => array('type' => 'integer', 'null' => true, 'default' => null),
		'created_user_name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified_user_id' => array('type' => 'integer', 'null' => true, 'default' => null),
		'modified_user_name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'item_id' => array('column' => array('user_item_id', 'user_authority_id'), 'unique' => 0)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);

	public $user_item_langs = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary', 'comment' => '会員項目言語毎のID'),
		'user_item_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'key' => 'index'),
		'lang' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 10, 'collate' => 'utf8_general_ci', 'comment' => '言語(ja,en等)', 'charset' => 'utf8'),
		'name' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '項目名', 'charset' => 'utf8'),
		'description' => array('type' => 'text', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '項目説明', 'charset' => 'utf8'),
		'options' => array('type' => 'text', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '項目オプション値をシリアライズしたデータとしてセット。', 'charset' => 'utf8'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'created_user_id' => array('type' => 'integer', 'null' => true, 'default' => null),
		'created_user_name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified_user_id' => array('type' => 'integer', 'null' => true, 'default' => null),
		'modified_user_name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'room_id' => array('column' => array('user_item_id', 'lang'), 'unique' => 0)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);

	public $user_item_links = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary', 'comment' => '会員と会員項目リンクID（会員の項目コンテンツをセット）'),
		'user_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'key' => 'index'),
		'lang' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 10, 'collate' => 'utf8_general_ci', 'comment' => '言語(ja,en等)', 'charset' => 'utf8'),
		'user_item_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'public_flag' => array('type' => 'boolean', 'null' => false, 'default' => '1', 'comment' => '公開するかどうか。UserItem.allow_public_flagがONの場合のみ有効。'),
		'email_reception_flag' => array('type' => 'boolean', 'null' => false, 'default' => '1', 'comment' => '公開するかどうか。UserItem.allow_email_reception_flagがONの場合のみ有効。'),
		'content' => array('type' => 'text', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '会員項目のコンテンツ。', 'charset' => 'utf8'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'user_id' => array('column' => array('user_id', 'lang', 'user_item_id'), 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);

	public $user_items = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary', 'comment' => '会員項目ID(会員のログインID、パスワード等の項目を保持)'),
		'default_name' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '会員項目のデフォルトの項目名。新規追加か、langがenならば、更新。該当言語の項目名がなければ、こちらを表示する。', 'charset' => 'utf8'),
		'default_description' => array('type' => 'text', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '会員項目のデフォルトの項目説明。新規追加か、langがenならば、更新。該当言語の項目説明がなければ、こちらを表示する。', 'charset' => 'utf8'),
		'default_options' => array('type' => 'text', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '会員項目のデフォルトの項目オプション値。新規追加か、langがenならば、更新。該当言語の項目オプション値がなければ、こちらを表示する。選択式、リストボックス時に設定され、シリアライズしたデータを登録する。', 'charset' => 'utf8'),
		'type' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 20, 'collate' => 'utf8_general_ci', 'comment' => '項目タイプ
\'text\'：テキスト,
\'password\'：パスワード,
\'email\'：eメール,
\'mobile_email\'：モバイルeメール,
\'select\'：リストボックス,
\'file\'：ファイル,
\'label\'：ラベル,
\'radio\'：ラジオボタン,
\'textarea\'：テキストエリア,
\'checkbox\'：チェックボックス', 'charset' => 'utf8'),
		'tag_name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'comment' => 'Userモデルに値が格納する項目の場合、Userモデルのカラム名をセット。', 'charset' => 'utf8'),
		'is_system' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'システムで使用するかどうか。ONの場合、項目設定から削除不可。'),
		'allow_self_edit' => array('type' => 'boolean', 'null' => false, 'default' => '1', 'comment' => '各自、編集を許すかどうか。但し、管理者・事務局は編集可能。'),
		'required' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => '必須項目にするかどうか。'),
		'allow_duplicate' => array('type' => 'boolean', 'null' => false, 'default' => '1', 'comment' => '重複を許可するかどうか。'),
		'minlength' => array('type' => 'integer', 'null' => false, 'default' => null, 'comment' => '最小文字数'),
		'maxlength' => array('type' => 'integer', 'null' => false, 'default' => null, 'comment' => '最大文字数'),
		'regexp' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '正規表現。パターンマッチングによるエラーチェックを記述。', 'charset' => 'utf8'),
		'display_flag' => array('type' => 'boolean', 'null' => false, 'default' => '1', 'comment' => '公開するかどうか。'),
		'allow_public_flag' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => '各自で公開・非公開を設定可能にするかどうか。'),
		'allow_email_reception_flag' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => '各自でメールの受信可否を設定可能にするかどうか。'),
		'list_num' => array('type' => 'integer', 'null' => false, 'default' => '1', 'comment' => '表示位置：何個目のリストか。'),
		'col_num' => array('type' => 'integer', 'null' => false, 'default' => '1', 'comment' => '表示位置：何列目か。'),
		'row_num' => array('type' => 'integer', 'null' => false, 'default' => '1', 'comment' => '表示位置：何行目か。'),
		'attribute' => array('type' => 'text', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '項目属性値（例：size=\'30\' style=\'padding:0px 3px\'）', 'charset' => 'utf8'),
		'default_selected' => array('type' => 'text', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '選択式、リストボックス時のデフォルトチェックする項目をシリアライズしたデータとしてセット。', 'charset' => 'utf8'),
		'display_title' => array('type' => 'boolean', 'null' => false, 'default' => '1', 'comment' => 'タイトルを表示するかどうか。'),
		'is_lang' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => '言語毎で設定可能にするかどうか。'),
		'autoregist_use' => array('type' => 'string', 'null' => false, 'default' => 'hide', 'length' => 16, 'collate' => 'utf8_general_ci', 'comment' => '自動登録時に使用するかどうか。', 'charset' => 'utf8'),
		'autoregist_sendmail' => array('type' => 'boolean', 'null' => false, 'default' => '1', 'comment' => '自動登録時に管理者にメール通知する項目かどうか。'),
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

	public $users = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary', 'comment' => '会員ID'),
		'login_id' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'comment' => 'ログインID', 'charset' => 'utf8'),
		'password' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'comment' => 'パスワード', 'charset' => 'utf8'),
		'handle' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 100, 'collate' => 'utf8_general_ci', 'comment' => 'ハンドル名', 'charset' => 'utf8'),
		'authority_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'is_active' => array('type' => 'integer', 'null' => false, 'default' => '1', 'length' => 3, 'comment' => '利用可能かどうか
0：利用不可
1：利用可能
2：承認待ち
3：承認済み'),
		'permalink' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'マイポータル、マイルーム固定リンク名', 'charset' => 'utf8'),
		'myportal_page_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'comment' => 'マイポータル直下のページID'),
		'private_page_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'comment' => 'マイルーム直下のページID'),
		'avatar' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'comment' => 'アバターファイル名', 'charset' => 'utf8'),
		'activate_key' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 8, 'collate' => 'utf8_general_ci', 'comment' => '自動登録用承認用キー', 'charset' => 'utf8'),
		'lang' => array('type' => 'string', 'null' => false, 'default' => 'ja', 'length' => 50, 'collate' => 'utf8_general_ci', 'comment' => 'ログイン後のデフォルト言語(ja,en等)　', 'charset' => 'utf8'),
		'timezone_offset' => array('type' => 'integer', 'null' => false, 'default' => '9', 'comment' => 'ログイン後のデフォルトタイムゾーン'),
		'email' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 100, 'collate' => 'utf8_general_ci', 'comment' => 'eメール', 'charset' => 'utf8'),
		'mobile_email' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 100, 'collate' => 'utf8_general_ci', 'comment' => 'モバイルeメール', 'charset' => 'utf8'),
		'password_regist' => array('type' => 'datetime', 'null' => true, 'default' => null, 'comment' => 'パスワード登録日時'),
		'last_login' => array('type' => 'datetime', 'null' => true, 'default' => null, 'comment' => 'Lastログイン日時'),
		'previous_login' => array('type' => 'datetime', 'null' => true, 'default' => null, 'comment' => '１つ前のLastログイン日時'),
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
