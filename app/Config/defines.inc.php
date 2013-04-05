<?php
/**
 * ディレクトリ定義
 * plugin等をテンプレートではなくコントローラからカスタマイズする場合に用いる
 * ディレクトリを予め準備しておく。
 */
define('CUSTOM_DIR', 'custom');
if (!defined('CUSTOM')) {
	define('CUSTOM', ROOT . DS . CUSTOM_DIR . DS);
}
/**
 * ディレクトリ定義
 */
define('NC_UPLOADS_DIR',      ROOT. DS. 'uploads'. DS);

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

//-----------------禁止URL-------------------------------------------
define('NC_PROHIBITION_URL', '/^(users\/|controls\/)/i');

//-----------------DOCYTPE-------------------------------------------
define('NC_DOCTYPE_STR', '/^[\s\r\n]*<!DOCTYPE html/i');

//-----------------権限(authorities.authority_id)-------------------------------------------
define('NC_AUTH_OTHER_ID', 0);
define('NC_AUTH_GUEST_ID', 5);
define('NC_AUTH_GENERAL_ID', 4);
define('NC_AUTH_MODERATE_ID', 3);
define('NC_AUTH_CHIEF_ID', 2);
define('NC_AUTH_ADMIN_ID', 1);

//-----------------権限(authorities.hierarchy)-------------------------------------------
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

//-----------------権限(authorities.myportal_use_flag)------------------------------------
define('NC_MYPORTAL_USE_NOT', 0);			// 使用しない
define('NC_MYPORTAL_USE_ALL', 1);			// すべて公開
define('NC_MYPORTAL_MEMBERS', 2);			// ログイン会員のみ公開(allow_myportal_viewing_authorityの権限以上で閲覧可能とする[主坦、モデレータ、一般、ゲスト])

//-----------------display_flag-------------------------------------------

define('NC_DISPLAY_FLAG_OFF', 0);		// 非公開
define('NC_DISPLAY_FLAG_ON', 1);		// 公開中
define('NC_DISPLAY_FLAG_DISABLE', 2);	// 利用不可

//-----------------status-------------------------------------------

define("NC_STATUS_PUBLISH",   0);		// 公開中
define("NC_STATUS_TEMPORARY", 1);		// 一時保存中
define("NC_STATUS_PRIVATE",   2);		// 非公開
define("NC_STATUS_MEMBERS",   3);		// 会員のみ公開

//-----------------community publication_range_flag-------------------------------------------

define('NC_PUBLICATION_RANGE_FLAG_ONLY_USER', 0);		// 参加者のみ（コミュニティー参加者のみが閲覧可能）
define('NC_PUBLICATION_RANGE_FLAG_LOGIN_USER', 1);		// 一部公開（すべてのログイン会員が閲覧可能）
define('NC_PUBLICATION_RANGE_FLAG_ALL', 2);				// 公開（すべてのユーザーが閲覧可能）

//-----------------community participate_flag-------------------------------------------

define('NC_PARTICIPATE_FLAG_ONLY_USER', 0);			// 参加会員のみ
define('NC_PARTICIPATE_FLAG_INVITE', 1);			// 招待制（コミュニティーメンバーから招待を受けた会員のみ参加可能）
define('NC_PARTICIPATE_FLAG_ACCEPT', 2);			// 承認制（主担の承認が必要）
define('NC_PARTICIPATE_FLAG_FREE', 3);				// 参加受付制(希望者は誰でも参加可能）

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
define('NC_PERMALINK_DIR_CONTENT', "^(users\/|controls\/|active-controls\/|active-contents\/|blocks\/|active-blocks\/|img\/|css\/|js\/|frame\/|theme\/)$");
define('NC_PERMALINK_PROHIBITION_DIR_PATTERN', "/".NC_PERMALINK_DIR_CONTENT."/i");

define('NC_SPACE_PUBLIC_PREFIX', '');
define('NC_SPACE_MYPORTAL_PREFIX', 'myportal');
define('NC_SPACE_PRIVATE_PREFIX', 'private');
define('NC_SPACE_GROUP_PREFIX', 'community');
//-----------------page_styles(page_infs)-------------------------------------------
define('NC_PAGE_STYLE_PUBLIC_ID',       1);
define('NC_PAGE_STYLE_MYPORTAL_ID',     2);
define('NC_PAGE_STYLE_PRIVATE_ID',      3);
define('NC_PAGE_STYLE_GROUP_ID',        4);
define('NC_PAGE_STYLE_COMMON_ID',       5);

//-----------------page_columns-------------------------------------------

define('NC_PAGE_COLUMN_PUBLIC_ID',       1);
define('NC_PAGE_COLUMN_MYPORTAL_ID',     2);
define('NC_PAGE_COLUMN_PRIVATE_ID',      3);
define('NC_PAGE_COLUMN_GROUP_ID',        4);
//define('NC_PAGE_COLUMN_COMMON_ID',       5);

//-----------------configs-------------------------------------------
define('NC_SYSTEM_CATID',      0);
define('NC_LOGIN_CATID',       1);
define('NC_SERVER_CATID',      2);
define('NC_MAIL_CATID',        3);
define('NC_META_CATID',        4);
define('NC_MEMBERSHIP_CATID',  5);
define('NC_DEVELOPMENT_CATID', 6);
define('NC_SECURITY_CATID',    7);

//-----------------autologin_use-------------------------------------------

define('NC_AUTOLOGIN_OFF', 0);		// 自動ログインOFF
define('NC_AUTOLOGIN_LOGIN', 1);	// ログインIDをクッキーに保持
define('NC_AUTOLOGIN_ON', 2);		// 自動ログイン

//-----------------User.active_flag-------------------------------------------

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
define('NC_HEADER_MENU_CLICK', 2);					//クリック時表示
define('NC_HEADER_MENU_ALWAYS', 3);					//常に表示

//-----------------Item.type-------------------------------------------

define('NC_ITEM_TYPE_TEXT',         "text");
define('NC_ITEM_TYPE_CHECKBOX',     "checkbox");
define('NC_ITEM_TYPE_RADIO',        "radio");
define('NC_ITEM_TYPE_SELECT',       "select");
define('NC_ITEM_TYPE_TEXTAREA',     "textarea");
define('NC_ITEM_TYPE_EMAIL',        "email");
define('NC_ITEM_TYPE_MOBILE_EMAIL', "mobile_email");
define('NC_ITEM_TYPE_LABEL',        "label");
define('NC_ITEM_TYPE_PASSWORD',     "password");
define('NC_ITEM_TYPE_FILE',         "file");

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

//-----------------アップロード関連-------------------------------------------
define("NC_ALLOW_ATTACHMENT_NO", 0);
define("NC_ALLOW_ATTACHMENT_IMAGE" ,1);
define("NC_ALLOW_ATTACHMENT_ALL", 2);

// 画像最大アップロードサイズ
define("NC_UPLOAD_MAX_SIZE_IMAGE", 2000000);
define("NC_UPLOAD_MAX_SIZE_ATTACHMENT", 2000000);

define("NC_UPLOAD_MAX_WIDTH_IMAGE", 1024);
define("NC_UPLOAD_MAX_HEIGHT_IMAGE", 1280);

define("NC_UPLOAD_MAX_WIDTH_AVATAR", 145);
define("NC_UPLOAD_MAX_HEIGHT_AVATAR", 145);
define("NC_UPLOAD_MAX_WIDTH_AVATAR_THUMBNAIL", 66);
define("NC_UPLOAD_MAX_HEIGHT_AVATAR_THUMBNAIL", 66);
define("NC_UPLOAD_USER_CONTROLLER_ACTION", 'nccommon/download_avatar');

define('NC_UPLOAD_FOLDER_MODE', 0777);
define('NC_UPLOAD_FILE_MODE', 0666);

//define('NC_UPLOAD_IMAGEFILE_TYPE', 'image/gif,image/jpg,image/jpeg,image/pjpeg,image/pipeg,image/png,image/x-png,image/tiff,image/bmp');

define('NC_UPLOAD_IMAGEFILE_EXTENSION', 'gif,jpg,jpe,jpeg,png,bmp');
define('NC_UPLOAD_ATTACHMENT_EXTENSION', 'Config');		// configテーブルの許す拡張子の一覧から拡張子チェックを行う
define('NC_UPLOAD_COMPRESSIONFILE_EXTENSION', 'zip,tar,tgz,gz');

define("NC_CATEGORY_INIFILE",          "category.ini");
define("NC_THEME_INIFILE",             "theme.ini");

//-----------------JS,CSSファイル関連-------------------------------------------
define("NC_ASSET_PREFIX", 'application-');
define("NC_ASSET_GC_PROBABILITY", 100);	// JS、CSSファイルガーベージコレクション発生確率(Page表示時：100回に一度)
define("NC_ASSET_GC_LIFETIME", 604800);		// JS、CSSファイル保持期間（デフォルト1週間）
//-----------------Htmlarea 履歴-------------------------------------------
define("NC_REVISION_RETENTION_NUMBER", 20);		// 履歴の保存最大個数を超えた場合、古いものから削除