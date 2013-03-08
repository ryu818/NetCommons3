<?php
/**
 * Uploadモデル
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Model
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class Upload extends AppModel
{
	public $name = 'Upload';

	/**
	 * コンテンツIDから削除処理
	 * TODO:実ファイルが削除されていない。
	 *
	 * @param   integer $content_id
	 * @return	boolean true or false
	 * @since   v 3.0.0.0
	 */
	public function deleteByContentId($content_id) {
		$conditions = array(
			'Upload.content_id' => $content_id
		);

		$ret = $this->deleteAll($conditions);
		return $ret;
	}
}