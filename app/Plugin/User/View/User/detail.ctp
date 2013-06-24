<?php
/**
 * 会員管理 会員一覧Grid
 * TODO:会員一覧に表示する項目を設定できるようにすることが望ましい。
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.User.View
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
	$loginUser = $this->Session->read(NC_AUTH_KEY.'.'.'User');
	$ret = array();
	$ret['page'] = $page_num;
	$ret['total'] = $total;
	$editLink = $this->Html->url(array('action' => 'edit', 'language' => $language), true);
	$groupLink = $this->Html->url(array('action' => 'select_group', 'language' => $language), true);
	$deleteLink = $this->Html->url(array('action' => 'index', 'language' => $language), true);
	foreach($users as $key => $user) {
		$label = "<label for=\"user_delete".$id."_".$user['User']['id']."\" class=\"display-block\">";
		$labelEnd = "</label>";

		$ret['rows'][$key]['cell'][] = $label.h($user['User']['handle']).$labelEnd;
		$ret['rows'][$key]['cell'][] = $label.h($user['UsersItemsLinkUsername']['content']).$labelEnd;
		if($user['Authority']['system_flag']) {
			$authority_name = h(__($user['Authority']['authority_name']));
		} else {
			$authority_name = h($user['Authority']['authority_name']);
		}
		$ret['rows'][$key]['cell'][] = $label.$authority_name.$labelEnd;
		if($user['User']['is_active'] === '') {
			$isActive = '';
		} else if($user['User']['is_active']) {
			$isActive = __('Active');
		} else {
			$isActive = __('Nonactive');
		}
		$ret['rows'][$key]['cell'][] = $label.$isActive.$labelEnd;
		if(!empty($user['User']['created'])) {
			$created = $this->TimeZone->date($user['User']['created'], __('y-m-d H:i'));
		} else {
			$created = '';
		}
		$ret['rows'][$key]['cell'][] = $label.$created.$labelEnd;
		if(!empty($user['User']['last_login'])) {
			$lastLogin = $this->TimeZone->date($user['User']['last_login'], __('y-m-d H:i'));
		} else {
			$lastLogin = '';
		}
		$ret['rows'][$key]['cell'][] = $label.$lastLogin.$labelEnd;

		$adminStr = '';
		if ($hierarchy >= NC_AUTH_MIN_CHIEF) {
			$editTitle = __d('user', 'Edit member info[%s]', $user['User']['handle']);
			$adminStr .= $this->Html->link(__('Edit'), $editLink.'/'.$user['User']['id'], array(
				'title' => $editTitle,
				'onclick' =>'$.User.memberEdit(this); return false;',
				'data-user-id' => $user['User']['id'],
			));
			$adminStr .= "&nbsp;|&nbsp;".$this->Html->link(__d('user', 'Select Groups to join'), $groupLink.'/'.$user['User']['id'], array(
				'title' => __d('user', 'Select Groups to join'),
				'data-tab-title' => $editTitle,
				'onclick' =>'$.User.memberEdit(this); return false;',
				'data-user-id' => $user['User']['id'],
			));
			if($user['Authority']['hierarchy'] <= $loginUser['hierarchy'] && $user['User']['id'] != NC_SYSTEM_USER_ID) {
				$deleteTitle = __d('user', 'Delete member [%s]', $user['User']['handle']);
				$adminStr .= "&nbsp;|&nbsp;".$this->Html->link(__('Delete'), $deleteLink, array(
					'title' => $deleteTitle,
					'data-ajax-inner' => '#user-init-tab-list',
					'data-ajax-type' => 'post',
					'data-ajax-data' => '{"data[User]['.$user['User']['id'].'][delete]": "'._ON.'", "data[_Token][key]": "'.$this->params['_Token']['key'].'"}',
					'data-ajax-confirm' => __('Deleting %s. <br />Are you sure to proceed?', $user['User']['handle']),
				));
			}
			// メール　承認する
		}

		$ret['rows'][$key]['cell'][] = $adminStr;

		// 削除チェックボックス
		if($user['Authority']['hierarchy'] <= $loginUser['hierarchy'] && $user['User']['id'] != NC_SYSTEM_USER_ID) {
			$settings = array(
				'id' => 'Delete'.$user['User']['id'].$id,
				'type' => 'checkbox',
				'value' => _ON,
				'label' => false,
				'div' => false,
				'legend' => false,
				'checked' => false,
			);
			$ret['rows'][$key]['cell'][] = '<label class="display-block" for="'.$settings['id'].'">'.$this->Form->input('User.'.$user['User']['id'].'.delete', $settings).'</label>';
		} else {
			$ret['rows'][$key]['cell'][] = "";
		}
		/*
		if ($hierarchy >= NC_AUTH_MIN_CHIEF) {
			// TODO:メール未作成
			$confirm = h(addslashes(__('Deleting %s. <br />Are you sure to proceed?', $user['User']['handle'])));	//$javascript->escapeString
			$admin = "メール";
			if($user['Authority']['hierarchy'] <= $loginHierarchy) {
				$admin .= "&nbsp;｜".
					"&nbsp;<a href=\"#\" onclick=\"$._nc[&quot;users&quot;].show(&quot;#".$id."_content&quot;,&quot;edit/".h($user['User']['permalink'])."&quot;,"._ON.");return false;\">".__('Edit')."</a>";
				if($user['User']['id'] != 1)
					$admin .= "&nbsp;｜&nbsp;<a href=\"#\" onclick=\"if ($._nc[&quot;common&quot;].confirm(&quot;".$confirm."&quot;)) { $._nc[&quot;users&quot;].delete(&quot;".h($user['User']['id'])."&quot;,&quot;".h($user['User']['permalink'])."&quot;);} return false;\">".__('Delete')."</a>";
			}
			$ret['rows'][$key]['cell'][] = $admin;
			if($user['Authority']['hierarchy'] <= $loginHierarchy && $user['User']['id'] != NC_SYSTEM_USER_ID) {
				$ret['rows'][$key]['cell'][] = "<input class=\"user_delete\" type=\"checkbox\" onclick=\"$._nc.users.chgRadio(this);\" value=\"1\" name=\"delete_users[".$user['User']['id']."]\" id=\"user_delete".$id."_".$user['User']['id']."\" />";
			} else {
				$ret['rows'][$key]['cell'][] = "";
			}
		}*/
	}
	echo $this->Js->object($ret);
?>