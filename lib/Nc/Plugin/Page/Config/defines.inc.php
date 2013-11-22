<?php
/**
 * ページDefine
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.Page.Config
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
define("PAGES_COMMUNITY_LIMIT", 10);	// 1ページに表示するページ数	TODO:Configでデフォルトの表示件数を設定できるほうがよい。
define("PAGES_COMMUNITY_VIEWS", 5);		// ページ送り：リストを5ページ分まで表示
define("PAGES_COMMUNITY_LIMIT_SELECT", "10|20|30|40|50|100");
define("PAGES_PARTICIPANT_LIMIT_DEFAULT", "10");
define("PAGES_PARTICIPANT_LIMIT_SELECT", "[10, 15, 20, 30, 50, 100]");

define("PAGES_OPERATION_TIME_LIMIT", 2400);		// ペースト、ショートカット作成、移動時のタイムアウト時間(s)
define("PAGES_OPERATION_MEMORY_LIMIT", "128M");	// ペースト、ショートカット作成、移動時のメモリ使用量

// フォント
define("PAGES_STYLE_FONT", 'Meiryo,Hiragino Kaku Gothic ProN,Hiragino Mincho ProN,MS Gothic,MS Mincho,Arial,Times New Roma,Courier New,Georgia,Helvetica,Lucida Grande,Verdana');

// フォントサイズ
// reset.css参照
define("PAGES_STYLE__BODY_FONT_SIZE", '69.2%:9px,77%:10px,85%:11px,93%:12px,100%:13px,108%:14px,116%:15px,123.1%:16px,131%:17px,138.5%:18px,146.5%:19px,153.9%:20px,161.6%:21px');
define("PAGES_STYLE_FONT_SIZE", '64%,72%,80%,88%,96%,104%,112%,120%,128%,136%,144%,152%,160%');

// 行間
define("PAGES_STYLE_LINE_HEIGHT", '100%,110%,120%,130%,140%,150%,160%,170%,180%,190%,200%,220%,240%,260%');

// 線のスタイル
define("PAGES_STYLE_BORDER_STYLE", 'solid,dotted,dashed');

// 線のスタイル

define("PAGES_STYLE_PROPERTY_ELEMENTS_WHITE_LIST", 'body,#parent-container,article,h1,a,hr,#container');
define("PAGES_STYLE_PROPERTY_KEYS_WHITE_LIST", 'font-family,color,font-size,line-height,border-top-color,border-top-style,border-radius,border-style,border-color,background-color,background-image,background-attachment,background-position,background-repeat,background-size,margin-top,margin-right,margin-bottom,margin-left,width,height,float');

// 背景
define("PAGES_STYLE_BACKGROUND_ATTACHMENT", 'fixed,scroll');
define("PAGES_STYLE_BACKGROUND_ATTACHMENT_DEFAULT", 'scroll');
define("PAGES_STYLE_BACKGROUND_REPEAT", 'repeat,repeat-x,repeat-y,no-repeat');
define("PAGES_STYLE_BACKGROUND_REPEAT_DEFAULT", 'no-repeat');
define("PAGES_STYLE_BACKGROUND_POSITION_DATA", 'left top,center top,right top,left center,center center,right center,left bottom,center bottom,right bottom');
define("PAGES_STYLE_BACKGROUND_SIZE", 'auto,contain,cover');

define("PAGES_STYLE_BACKGROUND_LIMIT", 28);

define("PAGES_STYLE_BACKGROUND_POSITION", 'left top:Left Top,center top:Center Top,right top:Right Top,left center:Left Center,center center:Center Center,right center:Right Center,left bottom:Left Bottom,center bottom:Center Bottom,right bottom:Right Bottom');
define("PAGES_STYLE_BACKGROUND_POSITION_DEFAULT", 'left top');

// 行揃え
define("PAGES_STYLE_ALIGN", 'left:Left align,center:Center align,right:Right align');

// 最小の広さ、高さ
define("PAGES_STYLE_WIDTH_SIZE", 'auto:Auto,100%:100%,by hand:By hand');
define("PAGES_STYLE_HEIGHT_SIZE", 'auto:Auto,by hand:By hand');

// 会員招待->会員選択実行時 検索結果会員数
define("PAGES_INVITE_COMMUNITY_SELECT_MEMBERS_LIMIT", 28);

// 会員招待有効期限 2週間 60s * 60min * 24h * 7d *2
define("PAGES_INVITE_COMMUNITY_EXPIRES", 60 * 60 * 24 * 7 * 2);