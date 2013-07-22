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
							$settings['escape'] = false;
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
					// TODO:未作成
				} else if($item['type'] == 'autoregist_use_items') {
					// TODO:未作成
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