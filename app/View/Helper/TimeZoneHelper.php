<?php
/**
 * タイムゾーンで変換し、日付を表示するヘルパー
 *
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       App.Controllers.Components
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class TimeZoneHelper extends AppHelper {
	public function date($time = null, $format = null) {
		if($format === null) {
	    	$format =  __('Y-m-d H:i:s');
		}
		if ($time === null) {
	    	$time = gmdate($format);
		} else {
			$time = date(NC_VALIDATOR_DATE_TIME, strtotime($time));
		}
		$timezone_offset = Configure::read(NC_CONFIG_KEY.'.timezone_offset');;
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