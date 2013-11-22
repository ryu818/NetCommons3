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
 * construct
 * @param integer|string|array $id Set this ID for this model on startup, can also be an array of options, see above.
 * @param string $table Name of database table to use.
 * @param string $ds DataSource connection name.
 * @return  void
 * @since   v 3.0.0.0
 */
	public function __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);

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