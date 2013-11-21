<?php
/**
 * UserItemLangモデル
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Model
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class UserItemLang extends AppModel
{
	/**
	 * construct
	 * @param   void
	 * @return  void
	 * @since   v 3.0.0.0
	 */
	public function __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);
	}

	// TODO: バリデート処理未作成
}