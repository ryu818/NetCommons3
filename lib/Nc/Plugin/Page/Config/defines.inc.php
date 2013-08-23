<?php

define("PAGES_COMMUNITY_LIMIT", 10);	// 1ページに表示するページ数	TODO:Configでデフォルトの表示件数を設定できるほうがよい。
define("PAGES_COMMUNITY_VIEWS", 5);		// ページ送り：リストを5ページ分まで表示
define("PAGES_COMMUNITY_LIMIT_SELECT", "10|20|30|40|50|100");
define("PAGES_PARTICIPANT_LIMIT_DEFAULT", "10");
define("PAGES_PARTICIPANT_LIMIT_SELECT", "[10, 15, 20, 30, 50, 100]");

define("PAGES_OPERATION_TIME_LIMIT", 2400);		// ペースト、ショートカット作成、移動時のタイムアウト時間(s)
define("PAGES_OPERATION_MEMORY_LIMIT", "128M");	// ペースト、ショートカット作成、移動時のメモリ使用量
