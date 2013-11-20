<?php
/**
 * TimeHelperのNc版
 *     時間関連の共通メソッド
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.View.Helper
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
App::uses('TimeHelper', 'View/Helper');
class NcTimeHelper extends TimeHelper {
/**
 * 秒から日付文字列(○日○分○秒)を取得
 *
 * @param   integer $second
 * @return  string  ○日○分○秒
 * @since   v 3.0.0.0
 */
	public function secondToDayHourMinute($second) {
		$ret = '';
		$time = array();
		$day = 86400;
		$hour = 3600;
		$min = 60;

		$time['day'] = floor($second/$day);
		$second %= $day;
		$time['hour'] = floor($second/$hour);
		$second %= $hour;
		$time['min'] = floor($second/$min);
		$time['sec'] = $second % $min;

		if($time['day'] != 0) {
			$ret .= __('%sd', $time['day']);
		}
		if($time['hour'] != 0) {
			$ret .= __(' %sh', $time['hour']);
		}
		if($time['min'] != 0) {
			$ret .= __(' %smin', $time['min']);
		}
		if($time['sec'] != 0) {
			$ret .= __(' %ss', $time['sec']);
		}

		return trim($ret);
	}
}