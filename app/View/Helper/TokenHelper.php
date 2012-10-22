<?php
/**
 * CSRF対策用Token出力ヘルパー
 *
 * <pre>
 * 権限をチェックするComponentクラス
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       App.Controllers.Components
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class TokenHelper extends AppHelper {
	public $helpers = array('Form');

	public function create($name = 'nc_token', $id = null) {
		$hashed_id = $this->getHashedSessionId();
		$attr = array(
			'type' => 'hidden',
			'value' => $hashed_id
		);
		if(isset($id)) {
			$attr['id'] = $id;
		}

		return $this->Form->input($name, $attr);
    }

    public function getHashedSessionId() {
    	$hash_type = 'md5';
    	$session_id = CakeSession::id();
    	$system_flag = Configure::read(NC_SYSTEM_KEY.'.system_flag');
    	$system_flag = ($system_flag) ? _ON : _OFF;
    	return Security::hash($system_flag. '_'. $session_id. Configure::read('Security.salt'), $hash_type);
    }
}