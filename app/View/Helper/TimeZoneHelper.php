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

	public $helpers = array('Time');

/**
 * タイムゾーンで変換し、日付を表示
 * @param   $time nullならば現在の時刻
 * @param   $format nullならば__('Y-m-d H:i:s')
 * @return  string Date format
 * @since   v 3.0.0.0
 */
	// TODO:TimeZoneBehaviorと同一ファンクション
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
/**
 * タイムゾーンで変換し、日付を年、月、日、日付、時間、Atom, Full形式で取得
 * @param   $time nullならば現在の時刻
 * @param   $format nullならば__('Y-m-d H:i:s')
 * @return  array key: year, month, day, date,time, atom_date, full_date(__('Y-m-d H:i:s'))
 * @since   v 3.0.0.0
 */
    public function date_values($time = null) {
		$post_date = $this->date($time);
		$int_post_date = strtotime($post_date);

		return array(
			'year' => date('Y', $int_post_date),
			'month' => date('m', $int_post_date),
			'day' => date('d', $int_post_date),
			'date' => date(__('(Y-m-d)'), $int_post_date),
			'time' => date(__('h:i A'), $int_post_date),
			'atom_date' => $this->Time->toAtom($int_post_date),
			'full_date' => $post_date,
		);
    }

/**
 * 公開日付（非公開日付）が設定されている場合のタイトルを取得
 * @param   date $display_from_date
 * @param   date $display_to_date
 * @return  string $title
 * @since   v 3.0.0.0
 */
    public function getPublishedLabel($display_from_date, $display_to_date = null) {
    	$display_from_date = !empty($display_from_date) ? $this->date($display_from_date) : null;
    	$display_to_date = !empty($display_to_date) ? $this->date($display_to_date) : null;
    	$title = '';
    	if(!empty($display_from_date) && !empty($display_to_date)) {
    		$title = h(__('Published until from [%s] to [%s]', $display_from_date, $display_to_date));
    	} else if(!empty($display_from_date)) {
    		$title = h(__('Published from [%s]', $display_from_date));
    	} else if(!empty($display_to_date)) {
    		$title = h(__('Published until [%s]', $display_to_date));
    	}
    	return $title;
    }
}