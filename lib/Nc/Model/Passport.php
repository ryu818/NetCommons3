<?php
/**
 * Passportモデル
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Model
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class Passport extends AppModel
{
	//public $belongsTo = 'User';
/**
* construct
* @param   void
* @return  void
* @since   v 3.0.0.0
*/
	public function __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);
	}

/**
 * 自動ログインパスポートキー削除
 * @param   string $cookiePassport
 * @return  void
 * @since   v 3.0.0.0
 */
	public function passportDelete($cookiePassport) {
		if (empty($cookiePassport)) {
			return;
		}
		$condition = array(
			'Passport.passport' => $cookiePassport
		);
		$this->deleteAll($condition);
	}

/**
 * 自動ログインパスポートキー書き込み
 * @param   array  $user
 * @param   array  $passport
 * @return  string $passport
 * @since   v 3.0.0.0
 */
	public function passportWrite($user, $passport = array()) {
		if(isset($user)) {
			//$passport = array();
			$passport['Passport']['user_id'] = $user['id'];//isset($user['User']['id']) ? $user['User']['id'] : $user['Passport']['user_id'];
			$passport['Passport']['passport'] = Security::generateAuthKey();	//識別用にユニークなキーを生成
			//if(isset($user['Passport']['id']))$passport['Passport']['id'] = $user['Passport']['id'];
			$this->save($passport);

			return $passport['Passport']['passport'];
		}

		return '';
	}
}