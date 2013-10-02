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

define("PROPERTY_ELEMENTS_WHITE_LIST", 'body,#container,article,h1,a,hr,#main-container');
define("PROPERTY_KEYS_WHITE_LIST", 'font-family,color,font-size,line-height,border-top-color,border-top-style,border-radius,border-style,border-color,background-color,background-image,background-attachment,background-position-x,background-position-y,background-repeat');

define("PAGES_STYLE_BACKGROUND_ATTACHMENT", 'fixed,scroll');
define("PAGES_STYLE_BACKGROUND_REPEAT", 'repeat,repeat-x,repeat-y,no-repeat');
define("PAGES_STYLE_BACKGROUND_POSITION_X", 'left,right');
define("PAGES_STYLE_BACKGROUND_POSITION_Y", 'top,bottom');

// 背景
define("PAGES_BACKGROUND_LIMIT", 28);