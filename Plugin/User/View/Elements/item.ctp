<?php
/**
 * 会員編集時、会員項目表示 OR 対象会員絞り込みの会員項目表示
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.User.View
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
if($item['UserItem']['tag_name'] != '' && $item['UserItem']['tag_name'] != 'username') {
	$name = 'User.'.$item['UserItem']['tag_name'];
	$value = isset($user['User'][$item['UserItem']['tag_name']]) ? $user['User'][$item['UserItem']['tag_name']] : null;
} else {
	$name = 'UserItemLink.'.$item['UserItem']['id'].'.content';
	$value = isset($user_item_links[$item['UserItem']['id']]['UserItemLink']) ? $user_item_links[$item['UserItem']['id']]['UserItemLink']['content'] : null;
}
$userItemLinkId =  isset($user_item_links[$item['UserItem']['id']]['UserItemLink']) ? $user_item_links[$item['UserItem']['id']]['UserItemLink']['id'] : 0;
$publicFlag = null;
if($item['UserItem']['allow_public_flag']) {
	if(isset($user_item_links[$item['UserItem']['id']]['UserItemLink'])) {
		$publicFlag = $user_item_links[$item['UserItem']['id']]['UserItemLink']['public_flag'];
	} else {
		$publicFlag = _ON;
	}
}
$emailReceptionFlag = null;
if($item['UserItem']['allow_email_reception_flag']) {
	if(isset($user_item_links[$item['UserItem']['id']]['UserItemLink'])) {
		$emailReceptionFlag = $user_item_links[$item['UserItem']['id']]['UserItemLink']['email_reception_flag'];
	} else {
		$emailReceptionFlag = _ON;
	}
}
if(!isset($item['UserItemLang']['name'])) {
	$item['UserItemLang']['name'] = $item['UserItem']['default_name'];
}
$attribute = array();
if(!empty($item['UserItem']['attribute'])) {
	$attributeNames = array('class', 'cols', 'rows', 'style', 'size', 'div', 'maxlength');
	$regexp = "\s*=\s*([\"'])?([^ \"']*)";
	foreach($attributeNames as $attributeName) {
		if(preg_match("/".$attributeName.$regexp."/i" , $item['UserItem']['attribute'], $matches)) {
			$attribute[$attributeName] = $matches[2];
		}
	}
}

$options = array();
if($item['UserItem']['type'] == 'checkbox' || $item['UserItem']['type'] == 'radio' || $item['UserItem']['type'] == 'select') {
	if($item['UserItem']['tag_name'] == 'lang') {
		$options = $languages;
	} else if($item['UserItem']['tag_name'] == 'authority_id') {
		$options = $authorities;
	} else {
		if(!isset($item['UserItemLang']['options'])) {
			$item['UserItemLang']['options'] = $item['UserItem']['default_options'];
		}
		$options = !empty($item['UserItemLang']['options']) ? unserialize($item['UserItemLang']['options']) : array();
	}

	if($item['UserItem']['tag_name'] != 'authority_id') {
		foreach($options as $key => $option) {
			$options[$key] = ($item['UserItem']['tag_name'] == 'lang') ? __($option) : $option;
		}
	}

	if(!$isEdit) {
		$bufOptions = $options;
		$options = array();
		if($item['UserItem']['type'] == 'select') {
			$options[''] = __('-- Not specify --');
		} else if($item['UserItem']['type'] == 'radio') {
			$options[''] = __('Not specified');
		}
		foreach($bufOptions as $bufKey => $bufOption) {
			$options[$bufKey] = $bufOption;
		}
		$value = '';

	} else if(!isset($user['User']['id'])) {
		// 新規
		if($item['UserItem']['tag_name'] == 'timezone_offset') {
			$value = Configure::read(NC_CONFIG_KEY.'.'.'timezone_offset');
		} else if($item['UserItem']['tag_name'] == 'lang') {
			$value = Configure::read(NC_CONFIG_KEY.'.'.'language');
		} else if($item['UserItem']['tag_name'] == 'authority_id') {
			$value = NC_AUTH_GENERAL_ID;	// 固定値
		} else if($item['UserItem']['default_selected'] != '') {
			$value = unserialize($item['UserItem']['default_selected']);
		} else {
			$value = null;
		}
	} else {
		// 編集
		if($value != '' && $value != null) {
			if($item['UserItem']['tag_name'] == '') {
				$bufValue = @unserialize($value);
				if($bufValue !== false) {
					$value = $bufValue;
				}
			}
		} else {
			$value = null;
		}
	}
	if($value === false) {
		$value = null;
	} else if(is_array($value) && $item['UserItem']['type'] != 'checkbox') {
		$bufValueArr = $value;
		foreach($bufValueArr as $key => $bufValue) {
			if($bufValue) {
				$value = $key;
			}
		}
	}
}

if(!$isEdit && ($item['UserItem']['type'] == 'file' || $item['UserItem']['tag_name'] == 'password' ||
	$item['UserItem']['tag_name'] == 'timezone_offset' || $item['UserItem']['tag_name'] == 'lang' ||
	$item['UserItem']['tag_name'] == 'previous_login' || $item['UserItem']['tag_name'] == 'created_user_id' ||
	$item['UserItem']['tag_name'] == 'created_user_name' || $item['UserItem']['tag_name'] == 'modified_user_name' ||
	$item['UserItem']['tag_name'] == 'modified_user_name')) {
	// 対象会員の絞り込み非表示項目
	return;
}

?>

<?php if(!$isEdit || $item['UserItem']['display_title']): ?>
<dl>
	<dt>
		<?php
			echo $this->Form->label($name, $item['UserItemLang']['name']);
		?>
		<?php if($isEdit && $item['UserItem']['required'] && ($item['UserItem']['type'] != 'password' || empty($user_id))): ?>
		<span class="require"><?php echo __('*'); ?></span>
		<?php endif; ?>
	</dt>
	<dd>
<?php endif; ?>
		<?php
			if($isEdit && ($userItemLinkId || isset($publicFlag) || isset($emailReceptionFlag))) {
				echo $this->Form->hidden('UserItemLink.'.$item['UserItem']['id'].'.id' , array('value' => $userItemLinkId));
			}
			if ($item['UserItem']['type'] == 'text' || $item['UserItem']['type'] == 'email' || $item['UserItem']['type'] == 'mobile_email') {
				$settings = array(
					'type' => 'text',
					'value' => $value,
					'label' => false,
					'div' => false,
					'maxlength' => !empty($item['UserItem']['maxlength']) ? $item['UserItem']['maxlength'] : null,
					'size' => 15,
					'error' => array('attributes' => array(
						'selector' => true
					)),
				);
				if($isEdit && $item['UserItem']['tag_name'] == 'handle') {
					$settings['onchange'] = "$.User.chgHandle('".$id."', this);";
					echo "<span class=\"display-none\">".h($value)."</span>";
				}
				$settings = array_merge($settings, $attribute);
				echo $this->Form->input($name, $settings);
				if($item['UserItem']['type'] != 'text' && $value != '') {
					$this->assign('isEmail', '1');
				}
			} else if($item['UserItem']['type'] == 'password') {
				/*TODO:パスワードは自分自身ならばパスワード確認も表示するべき*/
				$settings = array(
					'type' => 'password',
					'value' => "",
					'label' => false,
					'div' => false,
					'maxlength' => !empty($item['UserItem']['maxlength']) ? $item['UserItem']['maxlength'] : null,
					'size' => 15,
					'error' => array('attributes' => array(
						'selector' => true
					)),
					'autocomplete' => 'off',
					'required' => empty($user['User']['id']) ? true : false,
				);
				$settings = array_merge($settings, $attribute);
				echo $this->Form->input($name, $settings);
			} else if($item['UserItem']['type'] == 'checkbox' || $item['UserItem']['type'] == 'radio' || $item['UserItem']['type'] == 'select') {
				$settings = array(
					'type' => $item['UserItem']['type'],
					'options' => $options,
					'value' => $value,
					'label' => ($item['UserItem']['type'] == 'select') ? false : true,
					'div' => false,
					'legend' => false,
				);
				$settings = array_merge($settings, $attribute);
				echo $this->Form->input($name, $settings);

			} else if($item['UserItem']['type'] == 'textarea') {
				if($isEdit) {
					$settings = array(
						'escape' => false,
						'value' => $value,
						//'error' => array('attributes' => array(
						//	'selector' => true
						//)),
					);
					$settings = array_merge($settings, $attribute);
					echo $this->Form->textarea($name, $settings);
				} else {
					$settings = array(
						'type' => 'text',
						'value' => $value,
						'label' => false,
						'div' => false,
						'maxlength' => !empty($item['UserItem']['maxlength']) ? $item['UserItem']['maxlength'] : null,
						'size' => 15,
						'error' => array('attributes' => array(
							'selector' => true
						)),
					);

					echo $this->Form->input($name, $settings);
				}
			} else if($item['UserItem']['type'] == 'file') {
				echo '<div class="user-avatar-outer">'
						.$this->element('avatar', array('name'=>$name,'avatar'=>$value, 'item' => $item))
					.'</div>';
				echo $this->Form->hidden($name , array(
					'value' => $value
				));
				echo '<div class="user-avatar-btn">';
					echo '<span class="common-btn-min upload-btn user-upload-btn">';
						echo '<span>'.__('Select file').'</span>';
						echo $this->Form->input($name.'.file', array(
							'type' => 'file',
							'class' => 'upload-btn-inputfile',
							'label' => false,
							'div' => false,
						));
					echo '</span>';
					echo $this->Form->button(__('Delete'), array(
						'name' => 'avatar-delete',
						'class' => 'common-btn-min middle',
						'type' => 'button',
						'onclick' => "$.User.deleteAvatar('".$id."');return false;"
					));
				echo '</div>';
			} else if($item['UserItem']['type'] == 'label') {
				if($item['UserItem']['tag_name'] == 'created' || $item['UserItem']['tag_name'] == 'modified' ||
					$item['UserItem']['tag_name'] == 'password_regist' || $item['UserItem']['tag_name'] == 'last_login' ||
					$item['UserItem']['tag_name'] == 'previous_login') {
					if(!$isEdit) {
						for($i = 0; $i < 2; $i++) {
							$settings = array(
								'type' => 'text',
								'value' => '',			///////// TODO:未作成
								'label' => false,
								'div' => false,
								'maxlength' => 4,
								'size' => 5,
								'error' => array('attributes' => array(
									'selector' => true
								)),
							);
							echo '<div class="user-search-date">'.$this->Form->input($name.'.'.$i, $settings).'&nbsp;';
							switch($item['UserItem']['tag_name']) {
								case 'last_login':
									if($i == 0) {
										echo __d('user', 'not logged more than <span style=\'color:#ff0000;\'>X</span>days');
									} else {
										echo __d('user', 'logged within <span style=\'color:#ff0000;\'>X</span>days');
									}
									break;
								default:
									if($i == 0) {
										echo __d('user', 'more than <span style=\'color:#ff0000;\'>X</span>days ago');
									} else {
										echo __d('user', 'within <span style=\'color:#ff0000;\'>X</span>days');
									}
									break;
							}
							echo '</div>';
						}
					} else if(!empty($value)) {
						echo $this->TimeZone->date($value, __('Y-m-d H:i'));
					}
				} else {
					echo h($value);
				}
			} else if($item['UserItem']['type'] == 'communities') {
				// 参加コミュニティー
				if($hierarchy >= NC_AUTH_MIN_CHIEF) {
					$notMembers = array('0' => __d('user', 'Members not in any communuty'));
					if(is_array($communities)) {
						$communities = array_merge($notMembers , $communities);
					} else {
						$communities = $notMembers;
					}
				}
				echo $this->Form->selectRooms($name, $communities, array('empty' => array('' => __('-- Not specify --'))));
			}
			if($isEdit) {
				if($item['UserItemLang']['description'] != '' && $item['UserItemLang']['description'] != null) {
					if(!isset($item['UserItemLang']['description'])) {
						$description = $item['UserItem']['default_description'];
					} else {
						$description = $item['UserItemLang']['description'];
					}
					echo '<div class="note">'.h($description).'</div>';
				}
				if (isset($publicFlag) || isset($emailReceptionFlag)) {
					echo '<div class="user-checkbox">';
				}
				if (isset($publicFlag)) {
					$publicFlagName = 'UserItemLink.'.$item['UserItem']['id'].'.public_flag';
					$settings = array(
						'type' => 'checkbox',
						'value' => _ON,
						'label' => __('Public'),
						'div' => false,
						'legend' => false,
						'checked' => ($publicFlag) ? true : false,
					);
					echo $this->Form->input($publicFlagName, $settings);
				}
				if (isset($emailReceptionFlag)) {
					$emailReceptionFlagName = 'UserItemLink.'.$item['UserItem']['id'].'.email_reception_flag';
					$settings = array(
						'type' => 'checkbox',
						'value' => _ON,
						'label' => __('Send to this address'),
						'div' => false,
						'legend' => false,
						'checked' => ($emailReceptionFlag) ? true : false,
					);
					echo $this->Form->input($emailReceptionFlagName, $settings);
				}
				if (isset($publicFlag) || isset($emailReceptionFlag)) {
					echo '</div>';
				}
			}
		?>
<?php if($item['UserItem']['display_title']): ?>
	</dd>
</dl>
<?php endif; ?>
