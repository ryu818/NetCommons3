<?php
/**
 * PageStyleモデル
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Model
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class PageStyle extends AppModel
{
	public $name = 'PageStyle';
	public $actsAs = array('File');

	private $css_extension = '.css';

	/**
	 * ページスタイル用CSSファイル生成
	 * @param   String    $content
	 * @return  String    $css_file
	 * @since   v 3.0.0.0
	 */
	public function createCssFile($content) {
		$path = $this->getPath();
		$hash = md5($content);
		$file_name = $this->getFile($hash);
		$css_file = $this->createFile($path, $file_name, $content, true);
		return $css_file;
	}

	/**
	 * ページスタイル用CSSファイル削除
	 * @param   String    $css_file
	 * @since   v 3.0.0.0
	 */
	public function deleteCssFile($css_file) {
		$path = $this->getPath();
		$file_path = $path . $css_file;
		// ファイルが存在すれば削除
		if (file_exists($file_path)) {
			$this->deleteFile($file_path);
		}
	}

	/**
	 * ページスタイル用CSSファイル格納パス取得
	 * @return  String    $css_file
	 * @since   v 3.0.0.0
	 */
	public function getPath() {
		$path = Configure::read('App.www_root') . 'theme' . DS . 'page_styles' . DS;
		return $path;
	}

	/**
	 * ページスタイル用CSSファイル名取得
	 * @param   String   $hash
	 * @return  String   $file
	 * @since   v 3.0.0.0
	 */
	public function getFile($hash) {
		// TODO ファイル名要検討
		$file = 'application-' . $hash . $this->css_extension;
		return $file;
	}

}
