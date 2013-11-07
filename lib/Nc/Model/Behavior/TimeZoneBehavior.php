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
 * グリニッジ標準の日付を返す
 * @param   Model   $Model
 * @param   string $format
 * @return  string $time
 * @since   v 3.0.0.0
 */
	public function nowDate(Model $Model, $format = null) {
		if($format === null) {
			$format =  NC_DB_DATE_FORMAT;
		}
		return gmdate($format);
	}

/**
 * 地方標準時をformatに従い返す
 * @param   Model   $Model
 * @param   string  $timeUtc 協定世界時、nullならば現在の協定世界時
 * @param   string  $format nullならば__('Y-m-d H:i:s')
 * @return  string  Date 日付を表す文字列
 * @since   v 3.0.0.0
 */
	public function date(Model $Model=null, $timeUtc = null, $format = null) {
		if($format === null) {
			$format =  __('Y-m-d H:i:s');
		}
		if ($timeUtc === null) {
			$timeUtc = gmdate(NC_VALIDATOR_DATE_TIME);
		} else {
			$timeUtc = date(NC_VALIDATOR_DATE_TIME, strtotime($timeUtc));
		}
		$timezone_offset = Configure::read(NC_CONFIG_KEY.'.timezone_offset');;
		$timezone_minute_offset = 0;
		if(round($timezone_offset) != intval($timezone_offset)) {
			$timezone_offset = ($timezone_offset> 0) ? floor($timezone_offset) : ceil($timezone_offset);
			$timezone_minute_offset = ($timezone_offset> 0) ? 30 : -30;			// 0.5minute
		}

		$int_time = mktime(intval(substr($timeUtc, 8, 2)) + $timezone_offset, intval(substr($timeUtc, 10, 2))+$timezone_minute_offset, intval(substr($timeUtc, 12, 2)),
				intval(substr($timeUtc, 4, 2)), intval(substr($timeUtc, 6, 2)), intval(substr($timeUtc, 0, 4)));

		return date($format, $int_time);
	}

/**
 * 協定世界時をformatに従い返す
 * @param   Model   $Model
 * @param   string  $time 地域のTimeZoneを考慮した日時（地方標準時）
 * @param   string  $format nullならばNC_DB_DATE_FORMAT
 * @return  string  Date 日付を表す文字列
 * @since   v 3.0.0.0
 */
	public function dateUtc(Model $Model, $time, $format = null) {

   		if($format === null) {
	    	$format =  NC_DB_DATE_FORMAT;
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

/**
 * 未来の日付かどうかチェック(過去の日付は入力不可)
 *
 * @param   Model   $Model
 * @param   array or string    $data
 * @param   boolean  $gm グリニッジに変換して比較するかどうか default:true
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function isFutureDateTime(Model $Model, $data, $gm = true) {
		if($data && !is_array($data)) {
			$data = array($data);
		}
		$values = array_values($data);
		$date_time = $values[0];
		if($gm) {
	    	$date_time = $this->date($Model, $date_time, NC_VALIDATOR_DATE_TIME);
		}
		if(strtotime($date_time) <= strtotime(gmdate(NC_VALIDATOR_DATE_TIME))) {
			return false;
		}
		return true;
	}

/**
 * From公開日付チェック
 *
 * @param   Model   $Model
 * @param  array     $check
 * @return boolean
 * @since   v 3.0.0.0
 */
	public function invalidDisplayFromDate(Model $Model, $check){
		if(isset($Model->data[$Model->alias]['display_flag']) && $Model->data[$Model->alias]['display_flag'] == _ON) {
			// 既に公開中
			return false;
		}

		if(isset($Model->data['parent'.$Model->alias]) && $Model->data['parent'.$Model->alias]['display_flag'] == _OFF) {
			// 親が非公開ならば、公開日付を設定させない。
			return false;
		}
		return true;
	}

/**
 * To公開日付チェック
 *
 * @param   Model   $Model
 * @param  array     $check
 * @return boolean
 * @since   v 3.0.0.0
 */
	public function invalidDisplayToDate(Model $Model, $check){
		if(isset($Model->data[$Model->alias]['display_flag']) && ($Model->data[$Model->alias]['display_flag'] != _ON &&
				empty($Model->data[$Model->alias]['display_from_date']))) {
			// 公開ではないか、公開日付が入力していない
			return false;
		}

		return true;
	}

/**
 * From-To公開日付チェック
 *
 * @param   Model   $Model
 * @param  array     $check
 * @return boolean
 * @since   v 3.0.0.0
 */
	public function invalidDisplayFromToDate(Model $Model, $check){
		if(!empty($Model->data[$Model->alias]['display_from_date']) && !empty($Model->data[$Model->alias]['display_to_date']) &&
				strtotime($Model->data[$Model->alias]['display_from_date']) >= strtotime($Model->data[$Model->alias]['display_to_date'])) {
			// "[公開日付 < 非公開日付]
			return false;
		}

		return true;
	}
}