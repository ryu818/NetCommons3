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
 * 地方標準時をformatに従い返す
 *
 * @param   string  $timeUtc 協定世界時、nullならば現在の協定世界時
 * @param   string  $format  nullならば__('Y-m-d H:i:s')
 * @return  string  Date 日付を表す文字列
 * @since   v 3.0.0.0
 */
	public function date($timeUtc = null, $format = null) {
		// TimeZoneBehaviorのdateファンクションを呼び出し
		App::uses('TimeZoneBehavior', 'Model/Behavior');
		$timeZoneBehavior = new TimeZoneBehavior();
		return $timeZoneBehavior->date(new Model(), $timeUtc, $format);
    }
/**
 * 協定世界時を地方標準時に変換し、日付を年、月、日、日付、時間、Atom, Full形式で取得
 *
 * @param   string  $timeUtc 協定世界時、nullならば現在の協定世界時
 * @param   string  $format  nullならば__('Y-m-d H:i:s')
 * @return  array key: year, month, day, date, time, atom_date, full_date(__('Y-m-d H:i:s'))
 * @since   v 3.0.0.0
 */
    public function dateValues($timeUtc = null) {
		$post_date = $this->date($timeUtc);
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