<?php
/**
 * Asset Behavior
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Model.Behavior
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class AssetBehavior extends ModelBehavior {

	public $name = 'Asset';

	/**
	 * ファイルを生成
	 * @param   Model   $Model
	 * @param   string $time
	 * @param   string $format
	 * @return  string $time
	 * @since   v 3.0.0.0
	 */
	public function createFile(Model $Model, $content=null, $ext='js') {
		$hash = md5($content);
		$file = $this->_createAsetFile($hash, $content, $ext);
		return $file;
	}

	protected function _createAsetFile($hash, $content, $ext = 'js') {
		$path = Configure::read('App.www_root') . 'theme' . DS . 'asset' . DS;
		$file_path = $this->_getAsetFile($hash, $ext);
		if(file_exists($path . $file_path)) {
			return $file_path;
		}
		$file = new File($path . $file_path, true);
		if ($file->write($content)) {
			$gzcontent = gzencode($content);
			$gzfile = new File($path . $file_path.'.gz', true);
			$gzfile->write($gzcontent);
			return $file_path;
		}

		return null;
	}

	protected function _deleteAsetFile($file_name) {
		$path = Configure::read('App.www_root') . 'theme' . DS . 'asset' . DS;
		$file = new File($path . $file_name);
		if ($file->delete()) {
			$gzfile = new File($path . $file_name.'.gz');
			$gzfile->delete();
		}
	}

	protected function _getAsetFile($hash, $ext = 'js') {
		$file = "application-" . $hash . '.' . $ext;
		return $file;
	}
}