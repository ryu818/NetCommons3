<?php 
class BlogSchema extends CakeSchema {

	public function before($event = array()) {
		return true;
	}

	public function after($event = array()) {
	}

	public $blog_comments = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary'),
		'content_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'key' => 'index'),
		'blog_post_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'parent_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'comment' => 'treeビヘイビア用 親コメントのID'),
		'lft' => array('type' => 'integer', 'null' => true, 'default' => null, 'comment' => 'treeビヘイビア用 現在のオブジェクトの左端の座標'),
		'rght' => array('type' => 'integer', 'null' => true, 'default' => null, 'comment' => 'treeビヘイビア用 現在のオブジェクトの右端の座標'),
		'title' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'コメントタイトル', 'charset' => 'utf8'),
		'comment' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'コメント内容', 'charset' => 'utf8'),
		'author' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 100, 'collate' => 'utf8_general_ci', 'comment' => 'コメント入力者名', 'charset' => 'utf8'),
		'author_email' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'コメント入力者メールアドレス', 'charset' => 'utf8'),
		'author_url' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'コメント入力者URL', 'charset' => 'utf8'),
		'author_ip' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 100, 'collate' => 'utf8_general_ci', 'comment' => 'コメント入力者IP', 'charset' => 'utf8'),
		'is_approved' => array('type' => 'boolean', 'null' => false, 'default' => '1', 'comment' => '承認済かどうか。'),
		'blog_name' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'トラックバック受信時のデータ\'blog_name\'', 'charset' => 'utf8'),
		'comment_type' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 20, 'collate' => 'utf8_general_ci', 'comment' => 'コメントの種類
trackback：トラックバック
comment：コメント', 'charset' => 'utf8'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null, 'key' => 'index'),
		'created_user_id' => array('type' => 'integer', 'null' => true, 'default' => null),
		'created_user_name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified_user_id' => array('type' => 'integer', 'null' => true, 'default' => null),
		'modified_user_name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'content_id' => array('column' => array('content_id', 'created'), 'unique' => 1),
			'blog_post_id' => array('column' => 'created', 'unique' => 0)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);

	public $blog_posts = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary'),
		'content_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'key' => 'index'),
		'post_date' => array('type' => 'datetime', 'null' => true, 'default' => null, 'comment' => '記事投稿日'),
		'is_future' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => '記事投稿日が未来かどうか'),
		'title' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '記事タイトル', 'charset' => 'utf8'),
		'permalink' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '固定リンク', 'charset' => 'utf8'),
		'icon_name' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 128, 'collate' => 'utf8_general_ci', 'comment' => 'タイトルの横につくアイコンファイル名', 'charset' => 'utf8'),
		'revision_group_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'comment' => 'revisionテーブルのgroup_id'),
		'vote' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '投票済みユーザのID', 'charset' => 'utf8'),
		'status' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 3, 'comment' => '0：公開中
1：一時保存中
2：一時保存中(新規投稿->一時保存の場合)	　　新規投稿記事メール送信用'),
		'is_approved' => array('type' => 'boolean', 'null' => false, 'default' => '1', 'comment' => '承認済みかどうか'),
		'pre_change_flag' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => '変更前のコンテンツを表示するかどうか。'),
		'pre_change_date' => array('type' => 'datetime', 'null' => true, 'default' => null, 'comment' => '公開日付（pre_change_flagがONの場合、指定することで、自動的にに最新の記事が公開される。）'),
		'post_password' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 20, 'collate' => 'utf8_general_ci', 'comment' => '記事をパスワード保護する場合のパスワード', 'charset' => 'utf8'),
		'to_ping' => array('type' => 'text', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '未送信のトラックバック', 'charset' => 'utf8'),
		'pinged' => array('type' => 'text', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '送信済みのトラックバック', 'charset' => 'utf8'),
		'approved_comment_count' => array('type' => 'integer', 'null' => false, 'default' => '0', 'comment' => '承認済みコメント数'),
		'comment_count' => array('type' => 'integer', 'null' => false, 'default' => '0', 'comment' => 'コメント数'),
		'approved_trackback_count' => array('type' => 'integer', 'null' => false, 'default' => '0', 'comment' => '承認済みトラックバック数'),
		'trackback_count' => array('type' => 'integer', 'null' => false, 'default' => '0', 'comment' => 'トラックバック数'),
		'vote_count' => array('type' => 'integer', 'null' => false, 'default' => '0', 'comment' => '投票数'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'created_user_id' => array('type' => 'integer', 'null' => true, 'default' => null),
		'created_user_name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified_user_id' => array('type' => 'integer', 'null' => true, 'default' => null),
		'modified_user_name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'created_user_id' => array('column' => array('content_id', 'created_user_id'), 'unique' => 0),
			'post_date' => array('column' => array('content_id', 'status', 'post_date', 'id'), 'unique' => 0)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);

	public $blog_styles = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary'),
		'block_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'key' => 'index'),
		'widget_type' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 3, 'comment' => 'ブログウィジットの種類
1：メイン記事
2：最近の投稿
3：最近のコメント
4：アーカイブ
5：カテゴリー
6：表示件数
7：タグ
8：カレンダー
9：RSS
10：検索'),
		'display_flag' => array('type' => 'boolean', 'null' => false, 'default' => '1', 'comment' => '表示するかどうか'),
		'col_num' => array('type' => 'integer', 'null' => false, 'default' => null, 'comment' => 'ブログブロック内での列番号'),
		'row_num' => array('type' => 'integer', 'null' => false, 'default' => null, 'comment' => 'ブログブロック内での行番号'),
		'visible_item' => array('type' => 'integer', 'null' => false, 'default' => '0', 'comment' => '記事の表示件数'),
		'options' => array('type' => 'text', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'ブログウィジットごとの表示方法をシリアライズして設定', 'charset' => 'utf8'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'created_user_id' => array('type' => 'integer', 'null' => true, 'default' => null),
		'created_user_name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified_user_id' => array('type' => 'integer', 'null' => true, 'default' => null),
		'modified_user_name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'block_id' => array('column' => 'block_id', 'unique' => 0)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);

	public $blog_term_links = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary'),
		'content_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'blog_post_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'key' => 'index'),
		'blog_term_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'created_user_id' => array('type' => 'integer', 'null' => true, 'default' => null),
		'created_user_name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified_user_id' => array('type' => 'integer', 'null' => true, 'default' => null),
		'modified_user_name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'blog_post_id' => array('column' => 'blog_post_id', 'unique' => 0)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);

	public $blog_terms = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary'),
		'content_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'key' => 'index'),
		'name' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'カテゴリーかタグの名称', 'charset' => 'utf8'),
		'slug' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '投稿や固定ページを表すカテゴリーかタグの名称であり、固定リンクのURLで利用', 'charset' => 'utf8'),
		'taxonomy' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 32, 'collate' => 'utf8_general_ci', 'comment' => '0：分類（タグ）
1：分類（カテゴリー）', 'charset' => 'utf8'),
		'checked' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => '記事の新規投稿時にカテゴリーやタグとして初期設定の対称にするかどうか'),
		'parent' => array('type' => 'integer', 'null' => false, 'default' => '0', 'comment' => '種類がカテゴリーの場合に親子関係があればblog_termsのid'),
		'count' => array('type' => 'integer', 'null' => false, 'default' => '0', 'comment' => '利用されている件数'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'created_user_id' => array('type' => 'integer', 'null' => true, 'default' => null),
		'created_user_name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified_user_id' => array('type' => 'integer', 'null' => true, 'default' => null),
		'modified_user_name' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'slug' => array('column' => array('content_id', 'taxonomy', 'slug'), 'unique' => 1),
			'taxonomy' => array('column' => array('content_id', 'taxonomy'), 'unique' => 0),
			'name' => array('column' => array('content_id', 'name', 'taxonomy'), 'unique' => 0)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);

	public $blogs = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'key' => 'primary'),
		'content_id' => array('type' => 'integer', 'null' => false, 'default' => '0'),
		'post_hierarchy' => array('type' => 'integer', 'null' => false, 'default' => '301', 'comment' => '記事投稿権限
（101,201,301のみ）'),
		'term_hierarchy' => array('type' => 'integer', 'null' => false, 'default' => '301', 'comment' => '新規カテゴリ、タグの追加を許す権限
（101,201,301のみ）'),
		'vote_flag' => array('type' => 'boolean', 'null' => false, 'default' => '1', 'comment' => '投票有無'),
		'sns_flag' => array('type' => 'boolean', 'null' => false, 'default' => '1', 'comment' => 'Twitter,Facebookアイコン表示有無'),
		'new_period' => array('type' => 'integer', 'null' => false, 'default' => '5', 'comment' => 'New記号表示期間(日)'),
		'mail_flag' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'メール通知するかどうか。'),
		'mail_hierarchy' => array('type' => 'integer', 'null' => false, 'default' => '301', 'comment' => 'メール通知する権限
（1,101,201,301のみ）'),
		'mail_subject' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'メール通知件名', 'charset' => 'utf8'),
		'mail_body' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'メール通知本文', 'charset' => 'utf8'),
		'comment_flag' => array('type' => 'integer', 'null' => false, 'default' => '1', 'length' => 3, 'comment' => 'コメント投稿有無'),
		'comment_required_name' => array('type' => 'boolean', 'null' => false, 'default' => '1', 'comment' => '非会員の場合、名前とメールアドレスの入力を必須にするかどうか。'),
		'comment_image_auth' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'コメントの画像認証を行うかどうか。'),
		'comment_hierarchy' => array('type' => 'integer', 'null' => false, 'default' => '101', 'comment' => 'コメント投稿権限
（1,101,201,301のみ）'),
		'comment_mail_flag' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'コメントをメールで通知するかどうか。
'),
		'comment_mail_hierarchy' => array('type' => 'integer', 'null' => false, 'default' => '301', 'comment' => 'コメント通知する権限
（1,101,201,301のみ）'),
		'comment_mail_subject' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'コメントメール通知件名', 'charset' => 'utf8'),
		'comment_mail_body' => array('type' => 'text', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'コメントメール通知本文', 'charset' => 'utf8'),
		'trackback_transmit_flag' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'トラックバックを送信するかどうか。'),
		'trackback_receive_flag' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'トラックバックを受信するかどうか。'),
		'transmit_blog_name' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'トラックバック送信時タイトル', 'charset' => 'utf8'),
		'approved_flag' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => '投稿の承認で主担の承認が必要かどうか。'),
		'approved_pre_change_flag' => array('type' => 'boolean', 'null' => false, 'default' => '1', 'comment' => '未承認の場合、変更前のコンテンツを表示するかどうか。'),
		'approved_mail_flag' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => '承認メールを通知するかどうか。'),
		'approved_mail_subject' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '承認メール件名', 'charset' => 'utf8'),
		'approved_mail_body' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => '承認メール本文', 'charset' => 'utf8'),
		'comment_approved_flag' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'コメントの主担による承認が必要かどうか。'),
		'trackback_approved_flag' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'トラックバックの主担による承認が必要かどうか。'),
		'comment_approved_mail_flag' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'コメントとトラックバック承認完了通知を行うかどうか。'),
		'comment_approved_mail_subject' => array('type' => 'string', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'コメントとトラックバック承認完了通知メール件名', 'charset' => 'utf8'),
		'comment_approved_mail_body' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
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
