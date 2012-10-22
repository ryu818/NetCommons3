<?php
/**
 * TimeZone Behavior
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Model.Behavior
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class TimeZoneBehavior extends ModelBehavior {

/**
 * 地域のTimeZoneを考慮した日付を返す
 * @param   Model   $Model
 * @param   string $time
 * @param   string $format
 * @return  string $time
 * @since   v 3.0.0.0
 */
	public function date(Model $Model, $time = null, $format = null) {

   		if($format === null) {
	    	$format =  NC_VALIDATOR_DATE_TIME;
		}
		if ($time === null) {
	    	return gmdate($format);
		}
		if(!preg_match(NC_CHECK_DATETIME, $time)) {
			return $time;
		}
		$time = date(NC_VALIDATOR_DATE_TIME, strtotime($time));

		// 登録時サーバのタイムゾーンを引く
		$summertime_offset = 0;
		// サマータイムも取得できれば考慮する
		if(date("I")) {
			$summertime_offset = -1;
		}

		//if ($time_null_flag) {
			$timezone_offset = -1 * Configure::read(NC_CONFIG_KEY.'.timezone_offset');
		//} else {
		//	$timezone_offset = -1 * $_default_TZ;
		//}
		$timezone_offset += $summertime_offset;
	    $timezone_minute_offset = 0;
		if(round($timezone_offset) != intval($timezone_offset)) {
			$timezone_offset = ($timezone_offset> 0) ? floor($timezone_offset) : ceil($timezone_offset);
			$timezone_minute_offset = ($timezone_offset> 0) ? 30 : -30;			// 0.5minute
		}

		$int_time = mktime(intval(substr($time, 8, 2)) + $timezone_offset, intval(substr($time, 10, 2))+$timezone_minute_offset, intval(substr($time, 12, 2)),
						intval(substr($time, 4, 2)), intval(substr($time, 6, 2)), intval(substr($time, 0, 4)));

		return date($format, $int_time);
    }
}