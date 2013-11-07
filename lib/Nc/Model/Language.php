<?php
/**
 * Languageモデル
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Model
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class Language extends AppModel
{
	public $primaryKey = 'language';

/**
 * 言語リスト取得
 * @param   void
 * @return  array
 * @since   v 3.0.0.0
 */
	public function findSelectList() {
		return $this->find('list', array(
			'fields' => array('display_name'),
			'conditions' => array('display_flag' => _ON),
			'order' => array('display_sequence' => 'ASC')
		));
	}
}