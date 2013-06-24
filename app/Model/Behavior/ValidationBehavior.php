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
 * 複数カラム重複チェック
 *
 * @param   Model $Model
 * @param   array    $data
 * @param   array    $fields
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function isUniqueWith(Model $Model, $data, $fields) {
		if (!is_array($fields)) {
			$fields = array($fields);
		}
		$fields = array_merge($data, $fields);
		return $Model->isUnique($fields, false);
	}

/**
 * テーマ存在チェック
 *
 * @param   Model $Model
 * @param   array    $data
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function existsTheme(Model $Model, $data) {
		$values = array_values($data);
		$theme_name = $values[0];
		if($theme_name == '') {
			return true;
		}
		$theme_full_path = $this->getThemePath($Model, $theme_name);

		if(!$theme_full_path) {
			return false;
		}
		$ini_path = $theme_full_path . 'Config' . DS . NC_THEME_INIFILE;

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

/**
 * テーマパス取得
 *
 * @param   Model $Model
 * @param   string    $theme_name
 * @return  string theme_path or boolean false
 * @since   v 3.0.0.0
 */
    public function getThemePath(Model $Model, $theme_name = null) {
    	if(isset($theme_name)) {
			$theme_arr = explode('.', $theme_name);
			$theme_path = $theme_arr[0];
    	}
    	$paths = App::path('Frame');

    	foreach ($paths as $path) {
    		if(!isset($theme_name)) {
    			// カテゴリーiniの場所から判断
    			$cat_ini_path = $path . 'Config' . DS . NC_CATEGORY_INIFILE;
    			if(is_file($cat_ini_path)) {
    				 return $path;
    			}
    		} else {
    			$ini_path = $path . 'Plugin' . DS . $theme_path. DS . 'Config' . DS . NC_THEME_INIFILE;
    			if (is_file($path . 'Plugin' . DS . $theme_path. DS . 'View' . DS . 'index.ctp') &&
    					is_file($ini_path)) {
    				return $path . 'Plugin' . DS . $theme_path . DS;
    			}
    		}
    	}
    	return false;
    }
}