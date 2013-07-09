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
if($item['Item']['tag_name'] != '' && $item['Item']['tag_name'] != 'username') {
	$name = 'User.'.$item['Item']['tag_name'];
	$value = isset($user['User'][$item['Item']['tag_name']]) ? $user['User'][$item['Item']['tag_name']] : null;
} else {
	$name = 'UserItemLink.'.$item['Item']['id'].'.content';
	$value = isset($user_item_links[$item['Item']['id']]['UserItemLink']) ? $user_item_links[$item['Item']['id']]['UserItemLink']['content'] : null;
}
$userItemLinkId =  isset($user_item_links[$item['Item']['id']]['UserItemLink']) ? $user_item_links[$item['Item']['id']]['UserItemLink']['id'] : 0;
$publicFlag = null;
if($item['Item']['allow_public_flag']) {
	if(isset($user_item_links[$item['Item']['id']]['UserItemLink'])) {
		$publicFlag = $user_item_links[$item['Item']['id']]['UserItemLink']['public_flag'];
	} else {
		$publicFlag = _ON;
	}
}
$emailReceptionFlag = null;
if($item['Item']['allow_email_reception_flag']) {
	if(isset($user_item_links[$item['Item']['id']]['UserItemLink'])) {
		$emailReceptionFlag = $user_item_links[$item['Item']['id']]['UserItemLink']['email_reception_flag'];
	} else {
		$emailReceptionFlag = _ON;
	}
}
if($item['ItemLang']['lang'] == '') {
	$item['ItemLang']['name'] = __d('user_items', $item['ItemLang']['name']);
}
$attribute = array();
if(!empty($item['Item']['attribute'])) {
	$attributeNames = array('class', 'cols', 'rows', 'style');
	$regexp = "\s*=\s*([\"'])?([^ \"']*)";
	foreach($attributeNames as $attributeName) {
		if(preg_match("/".$attributeName.$regexp."/i" , $item['Item']['attribute'], $matches)) {
			$attribute[$attributeName] = $matches[2];
		}
	}
}

$options = array();
if($item['Item']['type'] == 'checkbox' || $item['Item']['type'] == 'radio' || $item['Item']['type'] == 'select') {
	if($item['Item']['tag_name'] == 'lang') {
		$options = $languages;
	} else if($item['Item']['tag_name'] == 'authority_id') {
		$options = $authorities;
	} else {
		$options = !empty($item['ItemLang']['options']) ? unserialize($item['ItemLang']['options']) : array();
	}

	if($item['ItemLang']['lang'] == '') {
		foreach($options as $key => $option) {
			if($item['Item']['tag_name'] == 'authority_id') {
				continue;
			}
			$options[$key] = ($item['Item']['tag_name'] == 'lang') ? __($option) : __d('user_items', $option);
		}
	}

	if(!$isEdit) {
		$bufOptions = $options;
		$options = array();
		if($item['Item']['type'] == 'select') {
			$options[''] = __('-- Not specify --');
		} else if($item['Item']['type'] == 'radio') {
			$options[''] = __('Not specified');
		}
		foreach($bufOptions as $bufKey => $bufOption) {
			$options[$bufKey] = $bufOption;
		}
		$value = '';

	} else if(!isset($user['User']['id'])) {
		// 新規
		if($item['Item']['tag_name'] == 'timezone_offset') {
			$value = Configure::read(NC_CONFIG_KEY.'.'.'timezone_offset');
		} else if($item['Item']['tag_name'] == 'lang') {
			$value = Configure::read(NC_CONFIG_KEY.'.'.'language');
		} else if($item['Item']['tag_name'] == 'authority_id') {
			$value = NC_AUTH_GENERAL_ID;	// 固定値
		} else if($item['Item']['default_selected'] != '') {
			$value = unserialize($item['Item']['default_selected']);
		} else {
			$value = null;
		}
	} else {
		// 編集
		if($value != '' && $value != null) {
			if($item['Item']['tag_name'] == '') {
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
	} else if(is_array($value) && $item['Item']['type'] != 'checkbox') {
		$bufValueArr = $value;
		foreach($bufValueArr as $key => $bufValue) {
			if($bufValue) {
				$value = $key;
			}
		}
	}
}

if(!$isEdit && ($item['Item']['type'] == 'file' || $item['Item']['tag_name'] == 'password' ||
	$item['Item']['tag_name'] == 'timezone_offset' || $item['Item']['tag_name'] == 'lang' ||
	$item['Item']['tag_name'] == 'previous_login' || $item['Item']['tag_name'] == 'created_user_id' ||
	$item['Item']['tag_name'] == 'created_user_name' || $item['Item']['tag_name'] == 'modified_user_name' ||
	$item['Item']['tag_name'] == 'modified_user_name')) {
	// 対象会員の絞り込み非表示項目
	return;
}

?>

<?php if(!$isEdit || $item['Item']['display_title']): ?>
<dl>
	<dt>
		<?php
			echo $this->Form->label($name, $item['ItemLang']['name']);
		?>
		<?php if($isEdit && $item['Item']['required'] && ($item['Item']['type'] != 'password' || empty($user_id))): ?>
		<span class="require"><?php echo __('*'); ?></span>
		<?php endif; ?>
	</dt>
	<dd>
<?php endif; ?>
		<?php
			if($isEdit && ($userItemLinkId || isset($publicFlag) || isset($emailReceptionFlag))) {
				echo $this->Form->hidden('UserItemLink.'.$item['Item']['id'].'.id' , array('value' => $userItemLinkId));
			}
			if ($item['Item']['type'] == 'text' || $item['Item']['type'] == 'email' || $item['Item']['type'] == 'mobile_email') {
				$settings = array(
					'type' => 'text',
					'value' => $value,
					'label' => false,
					'div' => false,
					'maxlength' => !empty($item['Item']['maxlength']) ? $item['Item']['maxlength'] : null,
					'size' => 15,
					'error' => array('attributes' => array(
						'selector' => true
					)),
				);
				if($isEdit && $item['Item']['tag_name'] == 'handle') {
					$settings['onchange'] = "$.User.chgHandle('".$id."', this);";
					echo "<span class=\"display-none\">".h($value)."</span>";
				}
				$settings = array_merge($settings, $attribute);
				echo $this->Form->input($name, $settings);
				if($item['Item']['type'] != 'text' && $value != '') {
					$this->assign('isEmail', '1');
				}
			} else if($item['Item']['type'] == 'password') {
				/*TODO:パスワードは自分自身ならばパスワード確認も表示するべき*/
				$settings = array(
					'type' => 'password',
					'value' => "",
					'label' => false,
					'div' => false,
					'maxlength' => !empty($item['Item']['maxlength']) ? $item['Item']['maxlength'] : null,
					'size' => 15,
					'error' => array('attributes' => array(
						'selector' => true
					)),
					'autocomplete' => 'off',
					'required' => empty($user['User']['id']) ? true : false,
				);
				$settings = array_merge($settings, $attribute);
				echo $this->Form->input($name, $settings);
			} else if($item['Item']['type'] == 'checkbox' || $item['Item']['type'] == 'radio' || $item['Item']['type'] == 'select') {
				$settings = array(
					'type' => $item['Item']['type'],
					'options' => $options,
					'value' => $value,
					'label' => ($item['Item']['type'] == 'select') ? false : true,
					'div' => false,
					'legend' => false,
				);
				$settings = array_merge($settings, $attribute);
				echo $this->Form->input($name, $settings);

			} else if($item['Item']['type'] == 'textarea') {
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
						'maxlength' => !empty($item['Item']['maxlength']) ? $item['Item']['maxlength'] : null,
						'size' => 15,
						'error' => array('attributes' => array(
							'selector' => true
						)),
					);

					echo $this->Form->input($name, $settings);
				}
			} else if($item['Item']['type'] == 'file') {
				echo '<div class="user-avatar"></div>';
			} else if($item['Item']['type'] == 'label') {
				if($item['Item']['tag_name'] == 'created' || $item['Item']['tag_name'] == 'modified' ||
					$item['Item']['tag_name'] == 'password_regist' || $item['Item']['tag_name'] == 'last_login' ||
					$item['Item']['tag_name'] == 'previous_login') {
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
							switch($item['Item']['tag_name']) {
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
			} else if($item['Item']['type'] == 'communities') {
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
				if($item['ItemLang']['description'] != '' && $item['ItemLang']['description'] != null) {
					if($item['ItemLang']['lang'] == '') {
						$description = __d('user_items', $item['ItemLang']['description']);
					} else {
						$description = $item['ItemLang']['description'];
					}
					echo '<div class="note">'.h($description).'</div>';
				}
				if (isset($publicFlag) || isset($emailReceptionFlag)) {
					echo '<div class="user-checkbox">';
				}
				if (isset($publicFlag)) {
					$publicFlagName = 'UserItemLink.'.$item['Item']['id'].'.public_flag';
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
					$emailReceptionFlagName = 'UserItemLink.'.$item['Item']['id'].'.email_reception_flag';
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
<?php if($item['Item']['display_title']): ?>
	</dd>
</dl>
<?php endif; ?>
