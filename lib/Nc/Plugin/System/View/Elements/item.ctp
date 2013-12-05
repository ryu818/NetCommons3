<?php
	$name = 'ConfigRegist.'.$item['name'];

	if($item['type'] == 'checkbox' && is_string($item['value'])) {
		$item['value'] = explode('|',$item['value']);
	}
	$titleArgs = isset($titleArgs) ? $titleArgs : null;
	$descriptionArgs = isset($descriptionArgs) ? $descriptionArgs : null;
?>
<li id="<?php echo $item['liId']; ?>"<?php if(isset($item['display']) && $item['display'] == 'none'): ?> style="display:none;"<?php endif; ?>>
	<dl>
		<dt>
			<?php
				if(!isset($item['domain']) || $item['domain'] == '') {
					echo $this->Form->label($name, __($item['title'], $titleArgs));
				} else {
					echo $this->Form->label($name, __d($item['domain'], $item['title'], $titleArgs));
				}
			?>
			<?php if($item['required'] == _ON): ?>
			<span class="require">
				<?php echo __('*');?>
			</span>
			<?php endif; ?>
		</dt>
		<dd>
			<?php
				switch($item['type']) {
					case 'pages':
					case 'modules_operation':
					case 'autoregist_use_items':
						break;
					case 'radio':
						$settings = array(
							'type' => 'radio',
							'value' => $item['value'],
							'options' => isset($options) ? $options : $item['options'],
							//'label' => false,
							'div' => false,
							'legend' => false,
							'separator' => isset($separator) ? '<br />' : '',
							'onchange' => isset($item['onclick']) ? $item['onclick'] : false,
						);
						break;
					case 'select':
					case 'checkbox':
						$settings = array(
							'type' => 'select',
							'value' => $item['value'],
							'options' => isset($options) ? $options : $item['options'],
							'label' => false,
							'div' => false,
							//'legend' => false,
							'separator' => isset($separator) ? '<br />' : '',
							'onchange' => isset($item['onchange']) ? $item['onchange'] : false,
						);
						if($item['type'] == 'checkbox') {
							$settings['multiple'] = 'checkbox';
						}
						break;
					default:
						$settings = array(
							'type' => 'text',
							'value' => $item['value'],
							'label' => false,
							'div' => false,
							'error' => array('attributes' => array(
								'selector' => true
							)),
						);
						if($item['type'] == 'textarea') {
							$settings['type'] = 'textarea';
						}
				}
				if($item['type'] == 'pages') {
					// 標準の開始ページ
					$display = '';
					if(!isset($first_startpage) || $first_startpage['Page']['space_type'] != NC_SPACE_TYPE_GROUP) {
						$display = ' style="display:none;"';
					}
					$attributes = array(
						'value' => (isset($isCommunity) && $isCommunity) ? -3 :$item['value'],
						'onchange' => "$.System.displayCommunityList('communities".$id."', this);",
					);
					echo $this->Form->selectRooms($name, $pages, $attributes);
					echo '<div id="communities'.$id.'"'.$display.' class="system-communities">'.$this->element('community_list').'</div>';
				} else if($item['type'] == 'modules_operation') {
					echo '<div>';
					foreach ($modules_operation as $module) {
						$checked = false;
						$disabled = false;
						$key = str_replace('_modules', '', $item['name']);
						if ($module['Module'][$key] == 'enabled') {
							$checked = true;
						} elseif ($module['Module'][$key] == 'disabled') {
							$disabled = true;
						}
						$settings = array(
							'id' => $item['liId'].$module['Module']['id'],
							'type' => 'checkbox',
							'value' => _ON,
							'checked' => $checked,
							'label' => $module['Module']['module_name'],
							'div' => false,
							'disabled' => $disabled
						);
						echo $this->Form->input('Module.'.$module['Module']['id'].'.'.$item['name'], $settings);
					}
					echo '</div>';
				} else if($item['type'] == 'autoregist_use_items') {
					echo '<div class="align-right">'
							.'<a href="#" class="link" onclick="$.System.toggleAutoregistUseItems(this, true);return false;">'.__d('system', 'Display all the items').'</a>'
							.'<a href="#" class="link" onclick="$.System.toggleAutoregistUseItems(this, false);return false;" style="display:none;">'.__d('system', 'Display only the selected items').'</a>'
						.'</div>';

					$autoregist_use_items_options = array(
						'optional' => __d('system', 'Voluntary'),
						'required' => __d('system', 'Required'),
						'hide' => __d('system', 'Not show')
					);
					$autoregist_sendmail_options = array(
						_ON => __d('system', 'Output it to email.'),
						_OFF => __d('system', 'Do not output it to email.')
					);
					echo '<ul>';
					foreach ($autoregist_use_items as $useItem) {
						$autoregist_use_settings = array(
							'type' => 'select',
							'value' => $useItem['UserItem']['autoregist_use'],
							'options' => $autoregist_use_items_options,
							'label' => false,
							'div' => false
						);
						if ($useItem['UserItem']['tag_name'] == 'login_id'
							|| $useItem['UserItem']['tag_name'] == 'password'
							|| $useItem['UserItem']['tag_name'] == 'handle') {
							$autoregist_use_settings['disabled'] = true;
						}
						$autoregist_sendmail_settings = array(
							'type' => 'select',
							'value' => ($useItem['UserItem']['autoregist_sendmail'] == _ON) ? _ON : _OFF,
							'options' => $autoregist_sendmail_options,
							'label' => false,
							'div' => false
						);
						$hideClass = '';
						if ($useItem['UserItem']['autoregist_use'] == 'hide') {
							$hideClass .= ' system-autoregist-item-hide none';
						}
						if(!isset($useItem['UserItemLang']['name'])) {
							$useItem['UserItemLang']['name'] = $useItem['UserItem']['default_name'];
						}
						echo '<li class="system-autoregist-use-items'.$hideClass.'"><dl>'
								.'<dt><span>'.h($useItem['UserItemLang']['name']).'</span></dt>'
								.'<dd>'
									.'<span>'.$this->Form->input('UserItem.'.$useItem['UserItem']['id'].'.autoregist_use', $autoregist_use_settings).'</span>'
									.'<span>'.$this->Form->input('UserItem.'.$useItem['UserItem']['id'].'.autoregist_sendmail', $autoregist_sendmail_settings).'</span>'
								.'</dd>'
							.'</dl></li>';
					}
					echo '</ul>';
					echo $this->Form->error('ConfigRegist.autoregist_use_items');
				} else {
					$settings = array_merge($settings, $item['attribute']);
					echo $this->Form->input($name, $settings);
					if($item['name'] == 'upload_normal_width_size') {
						$settings['value'] = $nextItem['value'];
						echo __d('system', '&nbsp;x&nbsp;').$this->Form->input('ConfigRegist.upload_normal_height_size', $settings);
					}
				}
			?>
			<?php if(isset($item['description']) && $item['description'] != ''): ?>
			<div class="note">
				<?php
					if(!isset($item['domain']) || $item['domain'] == '') {
						echo __($item['description'], $descriptionArgs);
					} else {
						echo __d($item['domain'], $item['description'], $descriptionArgs);
					}
				?>
			</div>
			<?php endif; ?>
		</dd>
	</dl>
</li>