<?php
/**
 * TempDataモデル
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Model
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class TempData extends AppModel
{
	//public $useTable = 'temporaries';

/**
 * 有効時間（秒）
 *
 * @var int
 */
	protected $_timeout;

/**
 * コンストラクタ
 *
 */
	public function __construct() {
		parent::__construct();
		// 1日
		$this->_timeout = 60 * 60 * 24;
	}

/**
 * ID取得
 *
 */
	protected function __id($id) {
		return Security::hash($id);
	}
/**
 * データ書き込み
 *
 * @param integer $id ID that uniquely identifies session in database
 * @param mixed $data The value of the data to be saved.
 * @return boolean True for successful write, false otherwise.
 */
	public function write($id, $data) {
		$id = $this->__id($id);
		if (!$id) {
			return false;
		}
		$expires = time() + $this->_timeout;
		$record = compact('id', 'data', 'expires');
		//$record[$this->primaryKey] = $id;
		return $this->save($record);
	}

/**
 * データ取得
 *
 * @param integer|string $id The key of the value to read
 * @return mixed The value of the key or false if it does not exist
 */
	public function read($id) {
		$id = $this->__id($id);
		$row = $this->find('first', array(
			'conditions' => array('id' => $id)
		));

		if (empty($row[$this->alias]['data'])) {
			return false;
		}

		return $row[$this->alias]['data'];
	}

/**
 * 削除処理
 *
 * @param integer $id ID that uniquely identifies session in database
 * @return boolean True for successful delete, false otherwise.
 */
	public function destroy($id) {
		$id = $this->__id($id);
		return $this->delete($id);
	}

/**
 * ガーベージコレクション
 *
 * @param integer $expires Timestamp (defaults to current time)
 * @return boolean Success
 */
	public function gc($expires = null) {
		if (!$expires) {
			$expires = time();
		} else {
			$expires = time() - $expires;
		}
		return $this->deleteAll(array($this->alias . ".expires <" => $expires), false, false);
	}
}