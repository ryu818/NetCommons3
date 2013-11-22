<?php
/**
 * PageMailCommunityComponentクラス
 *
 * <pre>
 * コミュニティーの招待メール等のメール送信処理
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       App.Plugin.Page.Controller.Component
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class PageMailCommunityComponent extends Component {
/**
 * _controller
 *
 * @var Controller
 */
	protected $_controller = null;

/**
 * startup
 *
 * @param Controller $controller
 */
	public function startup(Controller $controller) {
		$this->_controller = $controller;
	}

/**
 * 招待メール送信処理
 * TODO:メールは、PMにも送るようにしておかないと、メール送信を受信していない、またはメールアドレスを登録されていない会員が
 * 招待メールを読むことができなくなる。その他のメールの内容から、承認したりするものもPMに登録したほうがよい。
 * 招待メールOR承認メールか等をPMのフラグにもっておいて、招待済になったら、データを削除する等の処理を入れるべき。
 *
 * @param  integer $roomId
 * @param  string  $communityName
 * @param  array $request->data
 * @return boolean
 * 			falseの場合、errListにセット or throw
 * @since   v 3.0.0.0
 */
	public function sendInvite($roomId, $communityName, $data) {
		$CommunityInvitation = ClassRegistry::init('CommunityInvitation');

		// Modelにまとめるほうが望ましい。
		$error = false;
		if(!is_array($data['invite_members'])) {
			$this->_controller->Community->invalidate('invite_members', __('Please be sure to input.'));
			$error = true;
		}
		$notEmptyArr = array('invite_mail_subject', 'invite_mail_body');
		foreach($notEmptyArr as $notEmpty) {
			if(!Validation::notEmpty($data[$notEmpty])) {
				$this->_controller->Community->invalidate($notEmpty, __('Please be sure to input.'));
				$error = true;
			}
		}
		if($error) {
			return false;
		}

		$handles = $this->_controller->User->find('list', array(
			'fields' => array('id', 'handle'),
			'conditions' => array(
				'handle' => $data['invite_members'],
				'is_active' => NC_USER_IS_ACTIVE_ON,
			),
			'recursive' => -1
		));
		$userIds = array_keys($handles);

		if(count($userIds) != count($data['invite_members'])) {
			$this->_controller->Community->invalidate('invite_members', __('page', 'Because there are members that do not exist, can not be invited.'));
			return false;
		}

		$isParticipate = $this->_controller->PageUserLink->isParticipate($roomId, $userIds);
		if($isParticipate) {
			$this->_controller->Community->invalidate('invite_members', __('page', 'Because there are members of participation already, you can not invite.'));
			return false;
		}

		$emailReses = $this->_controller->User->getSendMailsByUserId($userIds);
		if(!$emailReses) {
			$this->_controller->Community->invalidate('invite_members', __('The server encountered an internal error and was unable to complete your request.'));
			return false;
		}

		// Community Invitations登録
		$activateKeys = array();
		foreach($userIds as $userId) {
			$activateKeys[$userId] = Security::generateAuthKey();
			$communityInvitationData[$CommunityInvitation->alias] = array(
				'room_id' => $roomId,
				'user_id' => $userId,
				'activate_key' => $activateKeys[$userId],
				'is_pending_approval_mail' => _OFF,
				'expires' => time() + PAGES_INVITE_COMMUNITY_EXPIRES,
			);
			$CommunityInvitation->create();
			if(!$CommunityInvitation->save($communityInvitationData)) {
				throw new InternalErrorException(__('Failed to register the database, (%s).', 'community_invitations'));
				return false;
			}
		}

		// TODO: PM送信処理未作成

		// メール送信処理
		$this->_controller->Mail->reset();
		$this->_controller->Mail->assignedTags['{X-ROOM}'] = $communityName;
		$format = '<a href="%s">%s</a>';

		$this->_controller->Mail->unEscapeTags[] = '{X-COMMUNITY_URL}';
		$this->_controller->Mail->unEscapeTags[] = '{X-PARTICIPATE_URL}';
		$communityInfParams = array(
			'permalink'=> '/centercolumn',
			'block_type'=> 'blocks',
			'block_id'=> 0,
			'plugin' => 'page',
			'controller' => 'page_menus',
			'action' => 'community_inf',
			$roomId,
		);
		$url = Router::url($communityInfParams, true);
		$this->_controller->Mail->assignedTags['{X-COMMUNITY_URL}'] = sprintf($format, $url, $url);

		$this->_controller->Mail->subject = $data['invite_mail_subject'];
		$n = "\n";

		foreach($emailReses as $userId => $emailRes) {
			$communityParticipateParams = array(
				'permalink'=> '',
				'block_type'=> 'active-blocks',
				'block_id'=> 0,
				'plugin' => 'page',
				'controller' => 'page_menus',
				'action' => 'participate_community',
				'activate_key' => $activateKeys[$userId],
				$roomId,
			);
			$url = Router::url($communityParticipateParams, true);
			$this->_controller->Mail->assignedTags['{X-PARTICIPATE_URL}'] = sprintf($format, $url, $url);
			$this->_controller->Mail->body = __d('page', 'To %s', $handles[$userId]). $n. $data['invite_mail_body'].
				$n . __d('page', "To see the description of {X-ROOM}, please click below.\n{X-COMMUNITY_URL}\n\nTo joint {X-ROOM}, please click the URL below.\n{X-PARTICIPATE_URL}");
			if(!$this->_sendMail($emailRes)) {
				return false;
			}
		}

		return true;
	}

/**
 * 承認メール送信処理
 * TODO:メールは、PMにも送るようにしておかないと、メール送信を受信していない、またはメールアドレスを登録されていない会員が
 * 承認メールを読むことができなくなる。その他のメールの内容から、承認したりするものもPMに登録したほうがよい。
 * 招待メールOR承認メールか等をPMのフラグにもっておいて、招待済になったら、データを削除する等の処理を入れるべき。
 *
 * @param  integer $roomId
 * @param  string  $communityName
 * @param  Model User $user
 * @param  string  $subject メール件名
 * @param  string  $body メール本文
 * @return boolean
 *            flash or throw
 * @since   v 3.0.0.0
 */
	public function sendApproval($roomId, $communityName, $user, $subject, $body) {
		$PageUserLink = ClassRegistry::init('PageUserLink');
		$CommunityInvitation = ClassRegistry::init('CommunityInvitation');
		$userId = $user['User']['id'];
		$isParticipate = $this->_controller->PageUserLink->isParticipate($roomId, $userId);
		if($isParticipate) {
			$this->_controller->flash(__d('page', 'Already participating.'), '');
			return false;
		}

		// roomIdのコミュニティーの主担すべてにメール送信
		$chiefUserIds = $PageUserLink->findChiefByRoomId($roomId);
		if(!$chiefUserIds) {
			throw new InternalErrorException(__('Failed to obtain the database, (%s).', 'page_user_links'));
			return false;
		}
		$emailReses = $this->_controller->User->getSendMailsByUserId($chiefUserIds);
		if(!$emailReses) {
			$this->_controller->flash(__('The server encountered an internal error and was unable to complete your request.'), '');
			return false;
		}

		// Community Invitations登録
		$activateKey = Security::generateAuthKey();
		$communityInvitationData[$CommunityInvitation->alias] = array(
			'room_id' => $roomId,
			'user_id' => $userId,
			'activate_key' => $activateKey,
			'is_pending_approval_mail' => _ON,
			'expires' => time() + PAGES_INVITE_COMMUNITY_EXPIRES,
		);
		$CommunityInvitation->create();
		if(!$CommunityInvitation->save($communityInvitationData)) {
			throw new InternalErrorException(__('Failed to register the database, (%s).', 'community_invitations'));
			return false;
		}

		// TODO PM送信処理未作成

		$this->_controller->Mail->reset();
		$this->_controller->Mail->userId = $userId;
		$this->_controller->Mail->assignedTags['{X-USER}'] = $user['User']['handle'];
		$this->_controller->Mail->assignedTags['{X-ROOM}'] = $communityName;
		$format = '<a href="%s">%s</a>';

		$this->_controller->Mail->unEscapeTags[] = '{X-USER_URL}';
		$this->_controller->Mail->unEscapeTags[] = '{X-APPROVE_URL}';
		// TODO: {X-USER_URL} マイポータルが使用できるならば、マイポータルのURLを表示するように修正予定
		// TODO: {X-USER_URL} 現状、会員情報のモジュールができていないため、{X-USER_URL}のURLはdummy。今後、テスト予定
		$userInfParams = array(
			'permalink'=> '/centercolumn',
			'block_type'=> 'blocks',
			'block_id'=> 0,
			'plugin' => 'user_inf',
			'controller' => 'user_inf',
			'action' => 'index',
			$userId,
		);
		$url = Router::url($userInfParams, true);
		$this->_controller->Mail->assignedTags['{X-USER_URL}'] = sprintf($format, $url, $url);

		$communityParticipateParams = array(
			'permalink'=> '',
			'block_type'=> 'active-blocks',
			'block_id'=> 0,
			'plugin' => 'page',
			'controller' => 'page_menus',
			'action' => 'participate_community',
			'activate_key' => $activateKey,
			$roomId,
		);
		$url = Router::url($communityParticipateParams, true);
		$this->_controller->Mail->assignedTags['{X-APPROVE_URL}'] = sprintf($format, $url, $url);

		$this->_controller->Mail->subject = $subject;
		$n = "\n";

		$this->_controller->Mail->body = $body.
			$n. $n . __d('page', "To allow participant and send a mail to the user, please click the link below.\n{X-APPROVE_URL}");
		foreach($emailReses as $userId => $emailRes) {
			if(!$this->_sendMail($emailRes)) {
				return false;
			}
		}
		return true;
	}


/**
 * 承認待ちメール、参加通知、退会通知送信処理
 *
 * @param  integer $roomId
 * @param  string  $communityName
 * @param  Model User $user
 * @param  string  $subject メール件名
 * @param  string  $body メール本文
 * @param  boolean $toChief ルームに参加している主担にメール送信するかどうか。falseの場合$userに送信
 * @return boolean
 *            flash or throw
 * @since   v 3.0.0.0
 */
	public function sendNotification($roomId, $communityName, $user, $subject, $body, $toChief = false) {
		$PageUserLink = ClassRegistry::init('PageUserLink');
		$userId = $user['User']['id'];

		if($toChief) {
			// roomIdのコミュニティーの主担すべてにメール送信
			$chiefUserIds = $PageUserLink->findChiefByRoomId($roomId);
			if(!$chiefUserIds) {
				throw new InternalErrorException(__('Failed to obtain the database, (%s).', 'page_user_links'));
				return false;
			}
			$emailReses = $this->_controller->User->getSendMailsByUserId($chiefUserIds);
		} else {
			// userに送信
			$emailReses = null;
			$emailRes = $this->_controller->User->getSendMailsByUserId($userId);
			if($emailRes) {
				$emailReses = array(
					$userId => $emailRes
				);
			}
		}
		if(!$emailReses) {
			$this->_controller->flash(__('The server encountered an internal error and was unable to complete your request.'), '');
			return false;
		}

		$this->_controller->Mail->reset();
		$this->_controller->Mail->userId = $userId;
		$this->_controller->Mail->assignedTags['{X-USER}'] = $user['User']['handle'];
		$this->_controller->Mail->assignedTags['{X-ROOM}'] = $communityName;
		$format = '<a href="%s">%s</a>';

		$this->_controller->Mail->unEscapeTags[] = '{X-COMMUNITY_URL}';
		$communityInfParams = array(
			'permalink'=> '/centercolumn',
			'block_type'=> 'blocks',
			'block_id'=> 0,
			'plugin' => 'page',
			'controller' => 'page_menus',
			'action' => 'community_inf',
			$roomId,
		);
		$url = Router::url($communityInfParams, true);
		$this->_controller->Mail->assignedTags['{X-COMMUNITY_URL}'] = sprintf($format, $url, $url);

		$this->_controller->Mail->subject = $subject;
		$n = "\n";

		$this->_controller->Mail->body = $body;
		foreach($emailReses as $userId => $emailRes) {
			if(!$this->_sendMail($emailRes)) {
				return false;
			}
		}

		return true;
	}

/**
 * メール送信処理
 *
 * @param  array $emailRes
 * @return boolean
 *            false : throw
 * @since   v 3.0.0.0
 */
	protected function _sendMail($emailRes) {
		$this->_controller->Mail->mails = $emailRes['email'];
		if(count($this->_controller->Mail->mails) > 0 && !$this->_controller->Mail->send()) {
			throw new InternalErrorException(__('Failed to send the mail.'));
			return false;
		}

		$this->_controller->Mail->mails = $emailRes['mobileEmail'];
		if(count($this->_controller->Mail->mails) > 0 && !$this->_controller->Mail->send('auto', true)) {
			throw new InternalErrorException(__('Failed to send the mail.'));
			return false;
		}
		return true;
	}
}