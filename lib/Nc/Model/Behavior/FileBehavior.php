<?php
/**
 * File Behavior
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Model.Behavior
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
App::uses('File', 'Utility');

class FileBehavior extends ModelBehavior {

	private $gz_extension = '.gz';

/**
 * 指定パスにあるファイルを作成し、書き込む
 * @param   Model   $Model
 * @param   string  $path
 * @param   string  $file_name
 * @param   string  $content
 * @param   boolean $gz_flag
 * @return	string $file_name or null
 * @since   v 3.0.0.0
 **/
	public function createFile(Model $Model, $path, $file_name, $content, $gz_flag = true) {
		$file_path = $path . $file_name;
		$file = $this->_getInstance($file_path, true);
		if (!$file->write($content)) {
			return null;
		}
		if ($gz_flag) {
			$gz_content = gzencode($content);
			$gz_file = $this->_getInstance($file_path . $this->gz_extension, true);
			$gz_file->write($gz_content);
		}
		return $file_name;
	}

/**
 * パスの最後にある名前の部分を返す(マルチバイト版)
 *
 * @param   Model  $Model
 * @param   string $path
 * @param   string $suffix
 * @return	 string
 * @since   v 3.0.0.0
 **/
	public function basename(Model $Model, $path, $suffix = null){
		$tmp = preg_split('/[\/\\\\]/', $path);
		$res = end($tmp);
		if(strlen($suffix)){
			$suffix = preg_quote($suffix);
			$res = preg_replace("/({$suffix})$/u", "", $res);
		}
		return $res;
	}

/**
 * 指定パスにあるファイル、それに対応したgzファイルを削除する
 *
 * @param   Model  $Model
 * @param   string $file_path
 * @param   boolean $gz_flag
 * @return	void
 * @since   v 3.0.0.0
 **/
	public function deleteFile(Model $Model, $file_path, $gz_flag = true) {
		$file = $this->_getInstance($file_path);
		if ($file->delete() && $gz_flag && file_exists($file_path . $this->gz_extension)) {
			$gz_file = $this->_getInstance($file_path . $this->gz_extension);
			$gz_file->delete();
		}
	}

	protected function _getInstance($file_path, $create_flag=false) {
		return new File($file_path, $create_flag, 0755);
	}

/**
 * 指定パスにあるディレクトリ一覧を返す
 *
 * @param   Model  $Model
 * @param  string	$path	対象パス
 * @param  boolean	$is_file
 * @return	array	array:正常, boolean false:異常
 * @since   v 3.0.0.0
 **/
	public function getCurrentDir(Model $Model, $path, $is_file = false) {
		if ( is_dir($path) ) {
			$dir_list = array();
			$handle = opendir($path);
			while ( false !== ($file = readdir($handle)) ) {
				if ( $file == '.' || $file == '..' ) { continue; }
				// if ( $file == 'CVS' || strtolower($file) == '.svn') { continue; }
				if($is_file) {
					if ( is_file($path. DS . $file) ) {
						$dir_list[] = $file;
					}
				} else if ( is_dir($path. DS . $file) ) {
					$dir_list[] = $file;
				}
			}
			closedir($handle);
			return $dir_list;
		} else {
			return false;
		}
	}

/**
 * 指定パスにあるファイル一覧を返す
 *
 * @param   Model  $Model
 * @param  string	$path	対象パス
 * @return	array	array:正常, boolean false:異常
 * @since   v 3.0.0.0
 **/
	public function getCurrentFile(Model $Model, $path) {
		return $this->getCurrentDir($Model, $path, true) ;
	}
}