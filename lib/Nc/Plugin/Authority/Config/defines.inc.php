<?php
/**
 * 権限管理 定義ファイル
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.User.View
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
define('AUTHORITY_SYSTEM_ADMIN_ID',	1);

define('AUTHORITY_ALLOW_CREATING_COMMUNITY_LIST', NC_ALLOW_CREATING_COMMUNITY_ADMIN.'|'.NC_ALLOW_CREATING_COMMUNITY_ALL_USER.
	'|'.NC_ALLOW_CREATING_COMMUNITY_ONLY_USER.'|'.NC_ALLOW_CREATING_COMMUNITY_ONLY_USER.'|'.NC_ALLOW_CREATING_COMMUNITY_OFF.'|'.NC_ALLOW_CREATING_COMMUNITY_ALL_USER);
define('AUTHORITY_ALLOW_NEW_PARTICIPANT_LIST', '1|1|0|0|0|1');
define('AUTHORITY_MYPORTAL_USE_FLAG_LIST', '0|0|1|1|0|0');
define('AUTHORITY_MYPORTAL_VIEWING_HIERARCHY_LIST', NC_AUTH_GUEST.'|'.NC_AUTH_GUEST.'|'.NC_AUTH_GUEST.'|'.NC_AUTH_GUEST.'|'.NC_AUTH_GUEST.'|'.NC_AUTH_GUEST);
define('AUTHORITY_PRIVATE_USE_FLAG_LIST', '1|1|1|1|0|1');

define('AUTHORITY_PUBLIC_CREATEROOM_FLAG_LIST', '1|1|0|0|0|1');
define('AUTHORITY_GROUP_CREATEROOM_FLAG_LIST', '1|1|0|0|0|1');
define('AUTHORITY_MYPORTAL_CREATEROOM_FLAG_LIST', '0|0|0|0|0|0');
define('AUTHORITY_PRIVATE_CREATEROOM_FLAG_LIST', '0|0|0|0|0|0');

define('AUTHORITY_ALLOW_HTMLTAG_FLAG_LIST', '1|0|0|0|0|0');
define('AUTHORITY_ALLOW_LAYOUT_FLAG_LIST', '1|1|0|0|0|1');
define('AUTHORITY_ALLOW_ATTACHMENT', "2|2|2|1|0|2");
define('AUTHORITY_ALLOW_VIDEO', "1|0|0|0|0|1");
define('AUTHORITY_MAX_SIZE', "1073741824|104857600|52428800|10485760|10485760|104857600");

define('AUTHORITY_CHANGE_LEFTCOLUMN_FLAG', "1|0|0|0|0|0");
define('AUTHORITY_CHANGE_RIGHTCOLUMN_FLAG', "1|0|0|0|0|0");
define('AUTHORITY_CHANGE_HEADERCOLUMN_FLAG', "1|0|0|0|0|0");
define('AUTHORITY_CHANGE_FOOTERCOLUMN_FLAG', "1|0|0|0|0|0");

define('AUTHORITY_ALLOW_MOVE_OPERATION', "1|1|0|0|0|1");
define('AUTHORITY_ALLOW_COPY_OPERATION', "1|1|0|0|0|1");
define('AUTHORITY_ALLOW_SHORTCUT_OPERATION', "1|1|0|0|0|1");
define('AUTHORITY_ALLOW_OPERATION_OF_SHORTCUT', "1|1|0|0|0|1");


define('AUTHORITY_ALLOW_CREATING_COMMUNITY_DISABLED', '1|0|0|0|1|0');
define('AUTHORITY_ALLOW_NEW_PARTICIPANT_DISABLED', '1|0|0|0|1|0');
define('AUTHORITY_MYPORTAL_USE_FLAG_DISABLED', '0|0|0|0|1|0');
define('AUTHORITY_MYPORTAL_VIEWING_HIERARCHY_DISABLED', '0|0|0|0|1|0');
define('AUTHORITY_PRIVATE_USE_FLAG_DISABLED', '0|0|0|0|1|0');

define('AUTHORITY_PUBLIC_CREATEROOM_FLAG_DISABLED', '0|0|0|1|1|0');
define('AUTHORITY_GROUP_CREATEROOM_FLAG_DISABLED', '0|0|0|1|1|0');
define('AUTHORITY_MYPORTAL_CREATEROOM_FLAG_DISABLED', '1|1|1|1|1|1');
define('AUTHORITY_PRIVATE_CREATEROOM_FLAG_DISABLED', '1|1|1|1|1|1');

define('AUTHORITY_ALLOW_HTMLTAG_FLAG_DISABLED', '1|0|0|1|1|0');
define('AUTHORITY_ALLOW_LAYOUT_FLAG_DISABLED', '1|0|0|0|1|0');
define('AUTHORITY_ALLOW_ATTACHMENT_DISABLED', "1|0|0|0|1|0");
define('AUTHORITY_ALLOW_VIDEO_DISABLED', "1|0|0|0|1|0");

define('AUTHORITY_CHANGE_LEFTCOLUMN_FLAG_DISABLED', "1|0|0|0|1|0");
define('AUTHORITY_CHANGE_RIGHTCOLUMN_FLAG_DISABLED', "1|0|0|0|1|0");
define('AUTHORITY_CHANGE_HEADERCOLUMN_FLAG_DISABLED', "1|0|0|0|1|0");
define('AUTHORITY_CHANGE_FOOTERCOLUMN_FLAG_DISABLED', "1|0|0|0|1|0");

define('AUTHORITY_ALLOW_MOVE_OPERATION_DISABLED', "0|0|0|0|1|0");
define('AUTHORITY_ALLOW_COPY_OPERATION_DISABLED', "0|0|0|0|1|0");
define('AUTHORITY_ALLOW_SHORTCUT_OPERATION_DISABLED', "0|0|0|0|1|0");
define('AUTHORITY_ALLOW_OPERATION_OF_SHORTCUT_DISABLED', "0|0|0|0|1|0");

// DISPLAY_PARTICIPANTS_EDITING

define('AUTHORITY_MAX_SIZE_LIST', "5242880|10485760|20971520|52428800|104857600|209715200|524288000|1073741824");

define('AUTHORITY_SYSTEM_CONTROL_MODULES', "Authority|Module|System|Backup|Policy|Mobile");

//; white_list
define('AUTHORITY_SYSTEM_MODULES_SYSADMIN_DEFAULT', "All");
define('AUTHORITY_SYSTEM_MODULES_ADMIN_DEFAULT', "User|Page|Backup|Holiday|Security");
define('AUTHORITY_SYSTEM_MODULES_CLERK_DEFAULT', "User|Page");
define('AUTHORITY_SYSTEM_MODULES_CHIEF_DEFAULT', "User|Page");
define('AUTHORITY_SYSTEM_MODULES_MODERATE_DEFAULT', "User|Page");
define('AUTHORITY_SYSTEM_MODULES_GENERAL_DEFAULT', "User|Page");
define('AUTHORITY_SYSTEM_MODULES_GUEST_DEFAULT', "User|Page");

//;white_list
define('AUTHORITY_SYSTEM_MODULES_SYSADMIN_ENABLED', "");
define('AUTHORITY_SYSTEM_MODULES_ADMIN_ENABLED', "All");
define('AUTHORITY_SYSTEM_MODULES_CLERK_ENABLED', "Page|Holiday");
define('AUTHORITY_SYSTEM_MODULES_CHIEF_ENABLED', "User|Page|Holiday");
define('AUTHORITY_SYSTEM_MODULES_MODERATE_ENABLED', "User|Page|Holiday");
define('AUTHORITY_SYSTEM_MODULES_GENERAL_ENABLED', "User|Page");
define('AUTHORITY_SYSTEM_MODULES_GUEST_ENABLED', "User|Page");
