<?php
/**
 * Themeモデル
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Model
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class Theme extends AppModel
{
	public $name = 'Theme';

	public $actsAs = array('File','Validation');

	public $useTable = false;

/**
 * 自動ログインパスポートキー削除
 * @param   string  $current_theme_name
 * @param   string $theme_kind Block or Page
 * @return  array
 * 				array  $category_list,
 *				array  $theme_list,
 *				array  $image_list,
 *				string $act_category
 * @since   v 3.0.0.0
 */
	public function getThemeList($current_theme_name, $theme_kind = 'Block') {
        // ブロックカテゴリ一覧取得
		$theme_path = $this->getThemePath();

		$locale = Configure::read(NC_SYSTEM_KEY.'.locale');
		$theme_config_path = $theme_path . 'Config' . DS;
		$ThumbnailName = "Thumbnail.gif";
		$categories_list = parse_ini_file($theme_config_path.NC_CATEGORY_INIFILE, true);
		$category_list = $categories_list[$theme_kind];

		$po = I18n::loadPo($theme_path . 'Locale' . DS . $locale . DS . 'LC_MESSAGES' . DS . 'frame.po');

		$theme_list = array();
		$image_list = array();
		$include_theme_list = array();
		$act_category = '';

		$paths = App::path('Frame');
		foreach ($paths as $path) {
			// テーマPlugin下はマージ
			$plugin_path = $path . 'Plugin' . DS;
			$themes_arr = $this->getCurrentDir($plugin_path);
			if($themes_arr === false) {
				continue;
			}
			foreach($themes_arr as $theme_name) {
				if(isset($include_theme_list[$theme_name])) {
					continue;
				}
				$include_theme_list[$theme_name] = true;
				$css_path = $plugin_path . $theme_name . DS . 'webroot' . DS . 'css' . DS;
				$img_path = $plugin_path . $theme_name . DS . 'webroot' . DS . 'img' . DS;

				//参加カテゴリiniファイル読み込み
				$themeconf_list = null;
				$themeIniPath = $plugin_path . $theme_name . DS . 'Config' . DS . NC_THEME_INIFILE;
				if(file_exists($themeIniPath)) {
					$themeconf_list = parse_ini_file($themeIniPath, true);
				}
				if($themeconf_list != null) {
					if(!isset($themeconf_list['Category'][$theme_kind])) {
						continue;
					}
					$category_key = $themeconf_list['Category'][$theme_kind];
					if(isset($category_list[$category_key]) && isset($po[$category_key])) {
						$category_list[$category_key] = $po[$category_key];
					}
					$po_path = $plugin_path . $theme_name . DS . 'Locale' . DS . $locale . DS . 'LC_MESSAGES' . DS . Inflector::underscore($theme_name) . '.po';
					$po_theme = I18n::loadPo($po_path);

					$child_themes_arr = $this->getCurrentDir($css_path);

					if(isset($child_themes_arr[0])) {
						foreach($child_themes_arr as $child_theme) {


							if(isset($themeconf_list['Locale'][$theme_name.'.'.$child_theme]) &&
									isset($po_theme[$themeconf_list['Locale'][$theme_name.'.'.$child_theme]])) {
								$theme_list[$category_key][$theme_name.'.'.$child_theme] = $po_theme[$themeconf_list['Locale'][$theme_name.'.'.$child_theme]];
							} else if(isset($themeconf_list['Locale'][$theme_name]) &&
									isset($po_theme[$themeconf_list['Locale'][$theme_name]])) {
								$theme_list[$category_key][$theme_name.'.'.$child_theme] = $po_theme[$themeconf_list['Locale'][$theme_name]];
							} else if(isset($themeconf_list['Locale'][$theme_name])) {
								$theme_list[$category_key][$theme_name.'.'.$child_theme] = $themeconf_list['Locale'][$theme_name];
							} else {
								$theme_list[$category_key][$theme_name.'.'.$child_theme] = __('Untitled');
							}

							$child_theme_thumbnail = strtolower($theme_kind) . DS . $child_theme . DS . $ThumbnailName;
							$theme_thumbnail = strtolower($theme_kind) . DS . $ThumbnailName;
							$thumbnail = strtolower($theme_kind) . DS . $ThumbnailName;

							if(file_exists($img_path . $child_theme_thumbnail)) {
								$image_list[$theme_name.'.'.$child_theme] = $child_theme_thumbnail;
							}else if(file_exists($img_path . $theme_thumbnail)) {
								$image_list[$theme_name.'.'.$child_theme] = $theme_thumbnail;
							} else {
								$image_list[$theme_name.'.'.$child_theme] = $thumbnail;
							}
							if(isset($current_theme_name) && $current_theme_name == $theme_name.'.'.$child_theme) {
								$act_category = $category_key;
							}
						}
					} else {


						if(isset($themeconf_list['Locale'][$theme_name]) &&
								isset($po_theme[$themeconf_list['Locale'][$theme_name]])) {
							$theme_list[$category_key][$theme_name] = $po_theme[$themeconf_list['Locale'][$theme_name]];
						} else if(isset($themeconf_list['Locale'][$theme_name])) {
							$theme_list[$category_key][$theme_name] = $themeconf_list['Locale'][$theme_name];
						} else {
							$theme_list[$category_key][$theme_name] = __('Untitled');
						}

						$theme_thumbnail = strtolower($theme_kind) . DS . $ThumbnailName;
						$thumbnail = strtolower($theme_kind) . DS . $ThumbnailName;
						if(file_exists($img_path . $theme_thumbnail)) {
							$image_list[$theme_name] = $theme_thumbnail;
						} else {
							$image_list[$theme_name] = $thumbnail;
						}

						if(isset($current_theme_name) && $current_theme_name == $theme_name) {
							$act_category = $category_key;
						}
					}
				}
			}
		}
		return array(
			$category_list,
			$theme_list,
			$image_list,
			$act_category
		);
    }
}