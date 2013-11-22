<?php
/**
 * CommunityInvitationモデル
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Model
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class CommunityInvitation extends AppModel
{
	public $belongsTo = array('User');

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
	}

/**
 * roomID、承認キーから、有効な会員情報を取得する。
 * @param   integer $roomId
 * @param   string  $activateKey
 * @return  Model User
 * @since   v 3.0.0.0
 */
	public function findActivateUser($roomId, $activateKey) {
		return $this->find('first', array(
			'fields' => array(
				'User.*',
				'CommunityInvitation.id',
				'CommunityInvitation.is_pending_approval_mail',
			),
			'conditions' => array(
				$this->alias.'.room_id' => $roomId,
				$this->alias.'.activate_key' => $activateKey,
			),
		));
	}

/**
 * roomID、$userIdから、既に承認待ちメール(招待メール)を取得する
 * @param   integer $roomId
 * @param   string  $userId
 * @param   boolean $isPendingApprovalMail _ON:承認待ちメール _OFF:招待メール
 * @return  Model User
 * @since   v 3.0.0.0
 */
	public function findAwaitingUser($roomId, $userId, $isPendingApprovalMail = _ON) {
		return $this->find('first', array(
			'fields' => array(
				'User.*'
			),
			'conditions' => array(
				$this->alias.'.room_id' => $roomId,
				$this->alias.'.user_id' => $userId,
				$this->alias.'.is_pending_approval_mail' => $isPendingApprovalMail,
			),
		));
	}

/**
 * 有効期限が切れたデータを削除する
 * @param integer $expires Timestamp (defaults time() - PAGES_INVITE_COMMUNITY_SELECT_MEMBERS_LIMIT)
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function gc($expires = null) {
		if (!$expires) {
			$expires = time() - PAGES_INVITE_COMMUNITY_SELECT_MEMBERS_LIMIT;
		} else {
			$expires = time() - $expires;
		}
		return $this->deleteAll(array($this->alias . ".expires <" => $expires), false, false);
	}
}