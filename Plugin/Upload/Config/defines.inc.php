<?php
/**
 * Upload定義
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */

//-----------ライブラリー検索（ユーザ検索の種別）--------------------------------
define('UPLOAD_SEARCH_CONDITION_USER_MYSELF', 1);// 自分自身
define('UPLOAD_SEARCH_CONDITION_USER_ALL', 2);// すべて
define('UPLOAD_SEARCH_CONDITION_USER_WITHDRAW', 3);// 退会ユーザー

//-----------ライブラリー検索（文字列検索の種別）--------------------------------
define('UPLOAD_SEARCH_CONDITION_FROM_FILE', 1);// ファイル名・説明から検索
define('UPLOAD_SEARCH_CONDITION_FROM_CREATOR', 2);// 作成者から検索

define('UPLOAD_SEARCH_DEFAULT_LIMIT', 40);// 最初に取得する件数

define('UPLOAD_SEARCH_CREATED_YEARS_AGO', 12);// 日付指定セレクトボックスで何年前まで表示させるか（項目数なのでその年にアップロードされたものがなければカウントされない）

//-----------ファイル詳細--------------------------------
define('UPLOAD_FILEINFO_OPTIONS',"{
	'percent_size_list' : [{'name':'100','value':'100'}, {'name':'90','value':'90'}, {'name':'80','value':'80'}, {'name':'70','value':'70'},
		{'name':'60','value':'60'}, {'name':'50','value':'50'}, {'name':'40','value':'40'}, {'name':'30','value':'30'},
		{'name':'20','value':'20'}, {'name':'10','value':'10'}],
	'float' : '',
	'margin_top_bottom' : '0',
	'margin_left_right' : '0',
	'border_width' : '0',
	'border_style' : '0',
	'border_list' : [{'name':'0','value':''}, {'name':'1','value':'1px'}, {'name':'2','value':'2px'}, {'name':'3','value':'3px'},
					{'name':'4','value':'4px'}, {'name':'5','value':'5px'}, {'name':'6','value':'6px'}],
	'border_style_list' : [{'name':'solid'}, {'name':'double'}, {'name':'dashed'}, {'name':'dotted'}, {'name':'inset'}, {'name':'outset'}]
}");