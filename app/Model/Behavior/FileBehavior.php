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

	var $gz_extension = '.gz';

	public function createFile(Model $Model, $path, $file_name, $content, $gz_flag) {
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

	public function deleteFile(Model $Model, $file_path) {
		$file = $this->_getInstance($file_path);
		if ($file->delete() && file_exists($file_path . $this->gz_extension)) {
			$gz_file = $this->_getInstance($file_path . $this->gz_extension);
			$gz_file->delete();
		}
	}

	protected function _getInstance($file_path, $create_flag=false) {
		return new File($file_path, $create_flag);
	}
}