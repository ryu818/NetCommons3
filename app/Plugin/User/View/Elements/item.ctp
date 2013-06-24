<?php
/**
 * 会員項目表示　
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
	$options = !empty($item['ItemLang']['options']) ? unserialize($item['ItemLang']['options']) : array();
	if($item['Item']['tag_name'] == 'lang') {
		$options = $languages;
	} else if($item['Item']['tag_name'] == 'authority_id') {
		$options = $authorities;
	}

	if($item['ItemLang']['lang'] == '') {
		foreach($options as $key => $option) {
			$options[$key] = ($item['Item']['tag_name'] == 'lang' || $item['Item']['tag_name'] == 'authority_id') ? __($option) : __d('user_items', $option);
		}
	}
	if(!isset($user['User']['id'])) {
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



?>

<?php if($item['Item']['display_title']): ?>
<dl>
	<dt>
		<?php
			echo $this->Form->label($name, $item['ItemLang']['name']);
		?>
		<?php if($item['Item']['required'] && ($item['Item']['type'] != 'password' || empty($user_id))): ?>
		<span class="require"><?php echo __('*'); ?></span>
		<?php endif; ?>
	</dt>
	<dd>
<?php endif; ?>
		<?php
			if(isset($isEdit) && $isEdit) {
				// 編集
				if($userItemLinkId || isset($publicFlag) || isset($emailReceptionFlag)) {
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
					if($item['Item']['tag_name'] == 'handle') {
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
					$settings = array(
						'escape' => false,
						'value' => $value,
						//'error' => array('attributes' => array(
						//	'selector' => true
						//)),
					);
					$settings = array_merge($settings, $attribute);
					echo $this->Form->textarea($name, $settings);
				} else if($item['Item']['type'] == 'file') {
					echo '<div class="user-avatar"></div>';
				} else if($item['Item']['type'] == 'label') {
					if($item['Item']['tag_name'] == 'created' || $item['Item']['tag_name'] == 'modified' ||
						$item['Item']['tag_name'] == 'password_regist' || $item['Item']['tag_name'] == 'last_login' ||
						$item['Item']['tag_name'] == 'previous_login') {
						if(!empty($value)) {
							echo $this->TimeZone->date($value, __('Y-m-d H:i'));
						}
					} else {
						echo h($value);
					}
				}
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
			} else {
				// 表示 TODO:未作成
			}
		?>
<?php if($item['Item']['display_title']): ?>
	</dd>
</dl>
<?php endif; ?>
