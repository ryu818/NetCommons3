<?php
/**
 * Blog定義
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Model
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
//-----------Blog.new_period-----------------------------------------------
define("BLOG_DEFAULT_NEW_PERIOD", 5);				// New記号表示期間デフォルト値

//-----------BlogStyle.widget_type-----------------------------------------
define("BLOG_WIDGET_TYPE_MAIN", 1);				// メイン記事
define("BLOG_WIDGET_TYPE_RECENT_POSTS", 2);		// 最近の投稿
define("BLOG_WIDGET_TYPE_RECENT_COMMENTS", 3);	// 最近のコメント
define("BLOG_WIDGET_TYPE_ARCHIVES", 4);			// アーカイブ
define("BLOG_WIDGET_TYPE_CATEGORIES", 5);		// カテゴリー
define("BLOG_WIDGET_TYPE_NUMBER_POSTS", 6); 	// 表示件数
define("BLOG_WIDGET_TYPE_TAGS", 7);			 	// タグ

define("BLOG_WIDGET_TYPE_CALENDAR", 8);			// カレンダー
define("BLOG_WIDGET_TYPE_RSS", 9);				// RSS
define("BLOG_WIDGET_TYPE_SEARCH", 10);			// 検索	TODO:未作成

//-----------BlogStyle.options position_comments----------------------------------------
define("BLOG_POSITION_COMMENTS_LAST", 0);		// 最後から
define("BLOG_POSITION_COMMENTS_FIRST",  1);		// 最初から

//-----------BlogStyle.options order_comments----------------------------------------
define("BLOG_ORDER_COMMENTS_OLDEST", 0);		// 古いもの順
define("BLOG_ORDER_COMMENTS_NEWEST",  1);		// 新しいもの順

//-----------BlogStyle.options display_type----------------------------------------
define("BLOG_DISPLAY_TYPE_LIST", 0);			// 一覧
define("BLOG_DISPLAY_TYPE_SELECTBOX", 1);		// セレクトボックス表示

//-----------BlogStyle.options display_type(taxonomy)----------------------------------------
define("BLOG_DISPLAY_TYPE_TAGS", 0);			// 分類（タグ）
define("BLOG_DISPLAY_TYPE_CATEGORIES", 1);		// 分類（カテゴリー）

//-----------BlogStyle.options display_type(RSS)----------------------------------------
define("BLOG_DISPLAY_TYPE_POST_ONLY", 0);			// 投稿のみ
define("BLOG_DISPLAY_TYPE_POST_AND_COMMENTS", 1);	// 投稿＋コメント
define("BLOG_DISPLAY_TYPE_COMMENTS_ONLY", 2);		// コメントのみ

//-----------BlogStyle.visible_item----------------------------------------
define("BLOG_DEFAULT_VISIBLE_ITEM", 10);					// 表示件数
define("BLOG_DEFAULT_RECENT_POSTS_VISIBLE_ITEM", 5);		// 表示件数(最近の投稿)
define("BLOG_DEFAULT_RECENT_COMMENTS_VISIBLE_ITEM", 5);		// 表示件数(最近のコメント)
define("BLOG_DEFAULT_ARCHIVES_VISIBLE_ITEM", 5);			// 表示件数(アーカイブ)
define("BLOG_DEFAULT_CATEGORIES_VISIBLE_ITEM", 5);			// 表示件数(カテゴリー)
define("BLOG_DEFAULT_NUMBER_POSTS_VISIBLE_ITEM", 5);		// 表示件数(表示件数)
define("BLOG_DEFAULT_TAGS_VISIBLE_ITEM", 10);				// 表示件数(タグ)

define("BLOG_VISIBLE_ITEM_SELECTBOX", '1|5|10|20|50|100');			// ブログ表示件数セレクトボックスvalue
define("BLOG_VISIBLE_ITEM_ALL_SELECTBOX", '1|5|10|20|50|100|0');	// ブログ表示件数セレクトボックスvalue(0:すべて)
define("BLOG_DEFAULT_VISIBLE_ITEM_COMMENTS", 20);					// コメントの表示数デフォルト値

//-----------BlogComments----------------------------------------
define("BLOG_RECENT_COMMENTS_MAX_LENGTH", 10);					// 最近のコメントの切り取る文字数

