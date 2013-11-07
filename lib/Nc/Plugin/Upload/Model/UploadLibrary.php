<?php
/**
 * UploadLibraryモデル
 *
 * <pre>
 *  Uploadテーブル追加用 ファイルアップロード
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Plugin.Block.Model
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class UploadLibrary extends AppModel {
	public $useTable = 'uploads';
	public $alias = 'UploadLibrary';

	public $actsAs = array(
		'Upload' => array(
			'file_name' => array(
				'isWysiwyg'=>true
			),
		),
	);

/**
 * バリデート処理
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function __construct() {
		parent::__construct();

		$this->validate = array(
			'file_name' => array(
				 'isValidExtension'  => array(
					'rule' => array('isValidExtension'),
					'message' => __('Invalid extension.')
				),
				'isBelowMaxSize'  => array(
					'rule' => array('isBelowMaxSize', $this->belowMaxSize),
					'message' => __('File size too large. Max %u byte.')
				),
			),
		);
	}
}