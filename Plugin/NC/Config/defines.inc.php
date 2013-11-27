<?php
/**
 * ディレクトリ定義
 */
define('NC_UPLOADS_DIR',      ROOT. DS. 'Uploads'. DS);

define('NC_MODINFO_FILENAME',     'modinfo.ini');

//-----------------定義ファイル-------------------------------------------
define('NC_INSTALL_INC_FILE', 'install.inc.php');
define('NC_VERSION_FILE', 'version.php');

//-----------------タイトル区切り文字-------------------------------------------
define('NC_TITLE_SEPARATOR',     ' - ');

//-----------------共通-------------------------------------------
define("_ON",1);
define("_OFF",0);

/**
 * 日付フォーマット
 */
define('NC_DB_DATE_FORMAT', 'Y-m-d H:i:s');
define('NC_VALIDATOR_DATE_TIME', "YmdHis");

//-----------------長さチェック用-------------------------------------------
define('NC_VALIDATOR_TITLE_LEN', 100);
define('NC_VALIDATOR_VARCHAR_LEN', 255);
define('NC_VALIDATOR_TEXTAREA_LEN', 1000);
define('NC_VALIDATOR_WYSIWYG_LEN', 60000);

define('NC_VALIDATOR_PAGE_TITLE_LEN', 100);
define('NC_VALIDATOR_PERMALINK_LEN', 255);

define('NC_VALIDATOR_BLOCK_TITLE_LEN', 100);

define('NC_VALIDATOR_USER_NAME_LEN', 100);

//-----------------禁止URL-------------------------------------------
define('NC_PROHIBITION_URL', '/^(users\/|controls\/)/i');

//-----------------DOCYTPE-------------------------------------------
define('NC_DOCTYPE_STR', '/^[\s\r\n]*<!DOCTYPE html/i');

//-----------------権限(Authority.authority_id)-------------------------------------------
define('NC_AUTH_OTHER_ID', 0);
define('NC_AUTH_CLERK_ID', 6);
define('NC_AUTH_GUEST_ID', 5);
define('NC_AUTH_GENERAL_ID', 4);
define('NC_AUTH_MODERATE_ID', 3);
define('NC_AUTH_CHIEF_ID', 2);
define('NC_AUTH_ADMIN_ID', 1);

//-----------------権限(Authority.hierarchy)-------------------------------------------
// 権限が大きくなるほど、高い権限を有する
define('NC_AUTH_OTHER', 0);
define('NC_AUTH_GUEST', 1);
define('NC_AUTH_MIN_GENERAL', 101);
define('NC_AUTH_GENERAL', 200);
define('NC_AUTH_MIN_MODERATE', 201);
define('NC_AUTH_MODERATE', 300);
define('NC_AUTH_MIN_CHIEF', 301);
define('NC_AUTH_CHIEF', 400);
define('NC_AUTH_MIN_ADMIN', 401);
define('NC_AUTH_ADMIN', 500);

define('NC_AUTH_KEY', 'Auth');
define('NC_CONFIG_KEY', 'Config');
define('NC_THEME_KEY', 'Theme');
define('NC_SYSTEM_KEY', 'System');

//-----------------権限(Authority.allow_creating_community)------------------------------------
define('NC_ALLOW_CREATING_COMMUNITY_OFF', 		0);			// コミュニティー作成不可（デフォルト）
define('NC_ALLOW_CREATING_COMMUNITY_ONLY_USER', 1);			// 参加者のみのコミュニティー作成可
define('NC_ALLOW_CREATING_COMMUNITY_ALL_USER', 	2);			// 一部公開（すべてのログイン会員が閲覧可能）までのコミュニティー作成可
define('NC_ALLOW_CREATING_COMMUNITY_ALL', 		3);			// 公開（すべてのユーザーが閲覧可能）までのコミュニティー作成可
define('NC_ALLOW_CREATING_COMMUNITY_ADMIN', 	4);			// 公開コミュニティーまで作成でき、すべてのコミュニティーの表示順変更、削除が可能

//-----------------権限(Authority.allow_new_participant)------------------------------------
// _ON:netcommonsのように主担が参加者を追加・変更・削除を行うことができる。
// _OFF:SNSのようにしてコミュニティーの参加者を募り、参加者の新規追加は許さない。

//-----------------権限(Authority.myportal_use_flag)------------------------------------
define('NC_MYPORTAL_USE_NOT', 0);			// 使用しない
define('NC_MYPORTAL_USE_ALL', 1);			// すべて公開
define('NC_MYPORTAL_MEMBERS', 2);			// ログイン会員のみ公開(allow_myportal_viewing_hierarchyの権限以上で閲覧可能とする[主担、モデレーター、一般、ゲスト])

//-----------------display_flag-------------------------------------------

define('NC_DISPLAY_FLAG_OFF', 0);		// 非公開
define('NC_DISPLAY_FLAG_ON', 1);		// 公開中
define('NC_DISPLAY_FLAG_DISABLE', 2);	// 利用不可

//-----------------status-------------------------------------------

define("NC_STATUS_PUBLISH",   0);					// 公開中
define("NC_STATUS_TEMPORARY", 1);					// 一時保存中
define("NC_STATUS_TEMPORARY_BEFORE_RELEASED", 2);	// 一時保存中(新規投稿->一時保存の場合)	新規投稿記事メール送信用
//define("NC_STATUS_PRIVATE",   2);		// 非公開
//define("NC_STATUS_MEMBERS",   3);		// 会員のみ公開

//-----------------Content.shortcut_type-------------------------------------------

define('NC_SHORTCUT_TYPE_OFF', 0);				// ショートカットではないコンテンツ
define('NC_SHORTCUT_TYPE_SHOW_ONLY', 1);		// 閲覧のみ許可
define('NC_SHORTCUT_TYPE_SHOW_AUTH', 2);		// 表示中のルーム権限より閲覧・編集権限を付与する。

//-----------------Community.publication_range_flag-------------------------------------------

define('NC_PUBLICATION_RANGE_FLAG_ONLY_USER', 0);		// 参加者のみ（コミュニティー参加者のみが閲覧可能）
define('NC_PUBLICATION_RANGE_FLAG_LOGIN_USER', 1);		// 一部公開（すべてのログイン会員が閲覧可能）
define('NC_PUBLICATION_RANGE_FLAG_ALL', 2);				// 公開（すべてのユーザーが閲覧可能）

//-----------------Community.participate_flag-------------------------------------------

define('NC_PARTICIPATE_FLAG_ONLY_USER', 0);			// 参加会員のみ
define('NC_PARTICIPATE_FLAG_INVITE', 1);			// 招待制（コミュニティーメンバーから招待を受けた会員のみ参加可能）
define('NC_PARTICIPATE_FLAG_ACCEPT', 2);			// 承認制（主担の承認が必要）
define('NC_PARTICIPATE_FLAG_FREE', 3);				// 参加受付制(希望者は誰でも参加可能）

//-----------------is_approved-------------------------------------------

define('NC_APPROVED_FLAG_OFF', 0);					// 承認待ち
define('NC_APPROVED_FLAG_ON', 1);					// 承認済
define('NC_APPROVED_FLAG_PRE_CHANGE', 2);			// 変更前コンテンツの表示(モジュール内投稿にのみ使用)

//-----------------space_type-------------------------------------------

define('NC_SPACE_TYPE_PUBLIC', 1);
define('NC_SPACE_TYPE_MYPORTAL', 2);
define('NC_SPACE_TYPE_PRIVATE', 3);
define('NC_SPACE_TYPE_GROUP', 4);

//-----------------room_id-------------------------------------------

define('NC_PUBLIC_ROOM_ID', 9);

//-----------------TOPノード-------------------------------------------
define('NC_TOP_PUBLIC_ID',       1);
define('NC_TOP_MYPORTAL_ID',     2);
define('NC_TOP_PRIVATE_ID',      3);
define('NC_TOP_GROUP_ID',        4);

//-----------------page_id-------------------------------------------

define('NC_HEADER_PAGE_ID', 5);
define('NC_LEFT_PAGE_ID', 6);
define('NC_RIGHT_PAGE_ID', 7);
define('NC_FOOTER_PAGE_ID', 8);

//-----------------permalink-------------------------------------------

define('NC_PERMALINK_CONTENT', '(%| |#|<|>|\+|\\\\|\"|\'|&|\?|\.$|=|\/|~|:|;|,|\$|@|^\.|\||\]|\[|\!|\(|\)|\*)');
define('NC_PERMALINK_PROHIBITION', "/".NC_PERMALINK_CONTENT."/i");
define('NC_PERMALINK_PROHIBITION_REPLACE', "-");
define('NC_PERMALINK_DIR_CONTENT', "\/(users|controls|active-controls|active-contents|blocks|active-blocks|img|css|js|frame|theme|nc-downloads)\/$");
define('NC_PERMALINK_PROHIBITION_DIR_PATTERN', "/".NC_PERMALINK_DIR_CONTENT."/i");

define('NC_SPACE_PUBLIC_PREFIX', '');
define('NC_SPACE_MYPORTAL_PREFIX', 'myportal');
define('NC_SPACE_PRIVATE_PREFIX', 'private');
define('NC_SPACE_GROUP_PREFIX', 'community');
//-----------------page_styles, page_infs, page_columns-------------------------------------------
// scope
define('NC_PAGE_SCOPE_SITE', 1);	// サイト全体
define('NC_PAGE_SCOPE_SPACE', 2);	// スペースタイプ全体
define('NC_PAGE_SCOPE_ROOM', 3);	// ルーム
define('NC_PAGE_SCOPE_NODE', 4);	// ノード
define('NC_PAGE_SCOPE_CURRENT', 5);	// カレントページのみ
// page_styles.type
define('NC_PAGE_TYPE_FONT_ID', 1);			// フォント設定
define('NC_PAGE_TYPE_BACKGROUND_ID', 2);	// 背景
define('NC_PAGE_TYPE_DISPLAY_ID', 3);		// 表示位置
define('NC_PAGE_TYPE_EDIT_CSS_ID', 4);		// カスタム設定

//-----------------configs-------------------------------------------
define('NC_SYSTEM_CATID',      0);
define('NC_LOGIN_CATID',       1);
define('NC_SERVER_CATID',      2);
define('NC_MAIL_CATID',        3);
define('NC_META_CATID',        4);
define('NC_STYLE_CATID',       5);
define('NC_MODULE_CATID',      6);
define('NC_MEMBERSHIP_CATID',  7);
define('NC_COMMUNITY_CATID',   8);
define('NC_DEVELOPMENT_CATID', 9);
define('NC_SECURITY_CATID',   10);

//-----------------autologin_use-------------------------------------------

define('NC_AUTOLOGIN_OFF', 0);		// 自動ログインOFF
define('NC_AUTOLOGIN_LOGIN', 1);	// ログインIDをクッキーに保持
define('NC_AUTOLOGIN_ON', 2);		// 自動ログイン

//-----------User.col_num-----------------------------------------------
define("NC_USER_MAX_COL_NUM", 3);				// 項目設定- 列数の最大数

//-----------------User.is_active-------------------------------------------

define('NC_USER_IS_ACTIVE_OFF',     0);		//利用不可
define('NC_USER_IS_ACTIVE_ON',      1);		//利用可能
define('NC_USER_IS_ACTIVE_PENDING', 2);		//承認待ち
define('NC_USER_IS_ACTIVE_MAILED',  3);		//承認済み

//-----------------自動登録-------------
define('NC_AUTOREGIST_SELF', 0);					//会員自身の確認が必要
define('NC_AUTOREGIST_AUTO' ,1);					//自動的にアカウントを有効にする
define('NC_AUTOREGIST_ADMIN', 2);					//管理者の承認が必要

//-----------------ヘッダーメニュー表示-------------
define('NC_HEADER_MENU_NONE', 0);					//ログイン前非表示
define('NC_HEADER_MENU_MOUSEOVER' ,1);				//マウスオーバー時表示
define('NC_HEADER_MENU_ALWAYS', 2);					//常に表示

//-----------------SSL設定-------------
define('NC_USE_SSL_ALWAYS', 3);				//常にSSLを有効にする
define('NC_USE_SSL_AFTER_LOGIN', 2);		//ログイン後にSSLを有効にする
define('NC_USE_SSL_FOR_LOGIN', 1);			//ログインと新規登録のみでSSLを有効にする
define('NC_USE_SSL_NO_USE', 0);				//SSLを有効にしない

//-----------------システム管理者ID-------------------------------------------
define('NC_SYSTEM_USER_ID',       1);

//-----------------DELETE-------------------------------------------
define('NC_DELETE_MOVE_PARENT',       2);	// 子グループを削除する場合、親のコンテンツへ
/**
 * Mode
 */
define('NC_GENERAL_MODE', 0);
define('NC_BLOCK_MODE', 1);

//-----------------ページ送り：リストを5ページ分まで表示----------------------
define('NC_PAGINATE_VIEWS', 5);

//-----------------UserItem.id-------------------------------------------

define('NC_ITEM_ID_LOGIN_ID',			1);
define('NC_ITEM_ID_PASSWORD',			2);
define('NC_ITEM_ID_USERNAME',			3);
define('NC_ITEM_ID_HANDLE',				4);
define('NC_ITEM_ID_EMAIL',				5);
define('NC_ITEM_ID_MOBILE_EMAIL',		6);
define('NC_ITEM_ID_TIMEZONE_OFFSET',	7);
define('NC_ITEM_ID_LANG',				8);
define('NC_ITEM_ID_AUTHORITY_ID',		9);
define('NC_ITEM_ID_IS_ACTIVE',			10);
define('NC_ITEM_ID_PERMALINK',			11);
define('NC_ITEM_ID_AVATAR',				12);
define('NC_ITEM_ID_CREATED',			13);
define('NC_ITEM_ID_CREATED_USER_NAME',	14);
define('NC_ITEM_ID_MODIFIED',			15);
define('NC_ITEM_ID_MODIFIED_USER_NAME',	16);
define('NC_ITEM_ID_PASSWORD_REGIST',	17);
define('NC_ITEM_ID_LAST_LOGIN',			18);
define('NC_ITEM_ID_PREVIOUS_LOGIN',		19);

//-----------------アップロード関連-------------------------------------------
// Authority.allow_attachment
define("NC_ALLOW_ATTACHMENT_NO", 0);
define("NC_ALLOW_ATTACHMENT_IMAGE" ,1);
define("NC_ALLOW_ATTACHMENT_ALL", 2);

// 画像最大アップロードサイズ
define("NC_UPLOAD_MAX_SIZE_IMAGE", 2000000);
define("NC_UPLOAD_MAX_SIZE_ATTACHMENT", 2000000);

define("NC_UPLOAD_MAX_WIDTH_AVATAR", 170);
define("NC_UPLOAD_MAX_HEIGHT_AVATAR", 170);
define("NC_UPLOAD_AVATAR_RESIZE_MODE", '['.NC_UPLOAD_MAX_WIDTH_AVATAR.'x'.NC_UPLOAD_MAX_HEIGHT_AVATAR.']');
define("NC_UPLOAD_MAX_WIDTH_AVATAR_THUMBNAIL", 66);
define("NC_UPLOAD_MAX_HEIGHT_AVATAR_THUMBNAIL", 66);
define("NC_UPLOAD_AVATAR_THUMBNAIL_RESIZE_MODE", '['.NC_UPLOAD_MAX_WIDTH_AVATAR_THUMBNAIL.'x'.NC_UPLOAD_MAX_HEIGHT_AVATAR_THUMBNAIL.']');
define("NC_UPLOAD_LIBRARY_THUMBNAIL_WIDTH_RESIZE_MODE", '110w');
define("NC_UPLOAD_LIBRARY_THUMBNAIL_HEIGHT_RESIZE_MODE", '110h');

define('NC_UPLOAD_FOLDER_MODE', 0777);
define('NC_UPLOAD_FILE_MODE', 0666);

//-----------画像解像度--------------------------------------------------------
define('NC_UPLOAD_RESOLUTION_IMAGE_LARGE_WIDTH', 800);// 大-幅
define('NC_UPLOAD_RESOLUTION_IMAGE_LARGE_HEIGHT', 600);// 大-高さ
define('NC_UPLOAD_RESOLUTION_IMAGE_MIDDLE_WIDTH', 640);// 中-幅
define('NC_UPLOAD_RESOLUTION_IMAGE_MIDDLE_HEIGHT', 480);// 中-高さ
define('NC_UPLOAD_RESOLUTION_IMAGE_SMALL_WIDTH', 480);// 小-幅
define('NC_UPLOAD_RESOLUTION_IMAGE_SMALL_HEIGHT', 360);// 小-高さ
define('NC_UPLOAD_RESOLUTION_IMAGE_ICON_SIZE', 48);// アイコン

//define('NC_UPLOAD_IMAGEFILE_TYPE', 'image/gif,image/jpg,image/jpeg,image/pjpeg,image/pipeg,image/png,image/x-png,image/tiff,image/bmp');

define('NC_UPLOAD_IMAGEFILE_EXTENSION', 'gif,jpg,jpe,jpeg,png,bmp');
define('NC_UPLOAD_IMAGEFILE_PHP_EXTENSION', 'gif,jpg,jpe,jpeg,png');
define('NC_UPLOAD_COMPRESSIONFILE_EXTENSION', 'zip,tar,tgz,gz');

define('NC_UPLOAD_ALLOW_CHAR_FOLDER', '/^[^\/\?\|:<>*\\\'\"\.\\\]*$/');
define('NC_UPLOAD_ALLOW_CHAR_FILE', '/^[^\/\?\|:<>*\\\'\"\\\]*$/');

define("NC_CATEGORY_INIFILE",          "category.ini");
define("NC_THEME_INIFILE",             "theme.ini");

//-----------------JS,CSSファイル関連-------------------------------------------
define('NC_ASSET_PREFIX', 'application-');
define('NC_ASSET_GC_PROBABILITY', 100);		// JS、CSSファイルガーベージコレクション発生確率(Page表示時：100回に一度)
define('NC_ASSET_GC_LIFETIME', 604800);		// JS、CSSファイル保持期間（デフォルト1週間）
define('NC_ASSET_GZIP', 1);					// gzip圧縮の有効化
//-----------------Revision 履歴(リビジョン)-------------------------------------------
define("NC_REVISION_RETENTION_NUMBER", 20);					// 履歴の保存最大個数を超えた場合、古いものから削除
define("NC_REVISION_SHOW_LIMIT", 5);						// WYSIWYG編集画面のリビジョンの表示件数
//define("NC_REVISION_AUTO_DRAFT_GC_LIFETIME", 2592000);		// 自動保存のデータ保持期間（デフォルト：30日）

//-----------------背景ファイル関連-------------------------------------------
define("NC_PAGES_BACKGROUND_COLOR_STYLE", 'Black,Gray,Brown,White,Blue,Red,Green,Yellow,Other');		// 色
define("NC_PAGES_BACKGROUND_CATEGORY_STYLE", 'Stripe,Dot,Square,Checkered,Tile,Wall,Picture,Other');	// カテゴリー
