<?php
/**
 * Validation Behavior
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Model.Behavior
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class ValidationBehavior extends ModelBehavior {
/**
 * テーマ存在チェック
 *
 * @param   Model $Model
 * @param   array    $data
 * @return  boolean
 * @access	@since   v 3.0.0.0
 */
	function existsTheme(Model $Model, $data) {
		$values = array_values($data);
		$frame_name = $values[0];
		$frame_arr = explode('.', $frame_name);
		$frame_path = $frame_arr[0];

		$paths = App::path('Frame');
		foreach ($paths as $path) {
			$ini_path = $path . $frame_path. DS . 'Config' . DS . NC_THEME_INIFILE;
			if (is_file($path . $frame_path. DS . 'View' . DS . 'index.ctp') &&
					is_file($ini_path)) {
				$frame_full_path = $path . $frame_path . DS;
				break;
			}
		}

		if(!isset($frame_full_path)) {
			return false;
		}

		$parse_ini = parse_ini_file($ini_path, true);
		if($Model->name == 'Block' && !isset($parse_ini['Category']['Block'])) {
			// Blockテーマ
			return false;

		} else if( !isset($parse_ini['Category']['Page'])) {
			// Pageテーマ
			return false;
		}
		return true;
    }
}