<div id="nc-block-style<?php echo($block_id); ?>">
<div id="nc-block-style-tab<?php echo($block_id); ?>">
	<ul>
		<li><a href="#nc-block-style-tab-init<?php echo($block_id); ?>"><span><?php echo(__('General setting'));?></span></a></li>
		<li><a href="#nc-block-style-tab-theme<?php echo($block_id); ?>"><span><?php echo(__('Theme'));?></span></a></li>
	</ul>
	<div id="nc-block-style-tab-init<?php echo($block_id); ?>" class="nc-block-style-init-outer">
		<?php
			echo $this->Form->create(null, array('id' => 'PageIndexForm'.$block_id, 'data-ajax-replace' => '#nc-block-style'.$block_id));
			$locale = Configure::read(NC_SYSTEM_KEY.'.locale');
		?>
		<fieldset class="form">
			<ul class="lists nc-block-style-lists">
				<li>
					<dl>
						<dt>
							<label for="nc-block-style-title-<?php echo($block_id);?>">
								<?php echo(__d('block', 'Block title'));?>
							</label>
						</dt>
						<dd>
							<?php
								$settings = array(
									'id' => "nc-block-style-title-".$block_id,
									'value' => $block['Block']['title'],
									'label' => false,
									'div' => false,
									'class' => 'nc-block-style-input',
									'maxlength' => NC_VALIDATOR_BLOCK_TITLE_LEN,
									'size' => 25,
									'type' => 'text',
									'error' => array('attributes' => array(
										'selector' => $this->Js->escape("$('[name=data\\[Block\\]\\[title\\]]', $('#nc-block-style-tab-init".$block_id."'))")
									))
								);
								echo $this->Form->input('Block.title', $settings);
							?>
							<?php
								$settings = array(
									'id' => "nc-block-style-show-title".$block_id,
									'value' => _ON,
									'checked' => ($block['Block']['show_title']) ? 'checked' : false,
									'label' =>'&nbsp;'.__d('block', 'To hide the block name.'),
									'type' => 'checkbox'
								);
								echo $this->Form->input('Block.show_title', $settings);
							?>
						</dd>
					</dl>
				</li>
				<?php if (isset($templates)): ?>
				<li>
					<dl>
						<dt>
							<label for="nc-block-style-template-<?php echo($block_id);?>">
								<?php echo(__('Template'));?>
							</label>
						</dt>
						<dd>
							<?php
								$settings = array(
									'id' => "nc-block-style-template-".$block_id,
									'value' => $block['Block']['temp_name'],
									'label' => false,
									'div' => false,
									'type' =>'select',
									'class' => 'nc-block-style-input',
									'options' => $templates
								);
								echo $this->Form->input('Block.temp_name', $settings);
							?>
						</dd>
					</dl>
				</li>
				<?php endif; ?>
				<li>
					<dl>
						<dt>
							<label for="nc-block-style-display_flag-<?php echo($block_id);?>">
								<?php echo(__('Publishing setting'));?>
							</label>
						</dt>
						<dd>
							<div class="nc-block-style-display-flag">
							<?php
								$settings = array(
									'id' => "nc-block-style-display_flag-".$block_id,
									'value' => ($block['Block']['display_flag']) ? _ON : _OFF,
									'label' => false,
									'div' => false,
									'type' =>'select',
									'class' => 'nc-block-style-display-flag',
									'options' => array(
										_ON => __('Public'),
										_OFF => __('Private')
									),
									'onchange' => '$.BlockStyle.chgDisplayFlag(this, \''.'#nc-block-style-display-from-date-'.$block_id.'\');',
								);
								echo $this->Form->input('Block.display_flag', $settings);
							?>
							</div>
							<?php
								if($this->request->is('post')) {
									$display_from_date = $block['Block']['display_from_date'];
								} else if(!empty($block['Block']['display_from_date'])) {
									$display_from_date = $this->TimeZone->date($block['Block']['display_from_date']);
									$display_from_date = date(__('Y-m-d H:i'), strtotime($display_from_date));
								} else {
									$display_from_date = '';
								}

								if($this->request->is('post')) {
									$display_to_date = $block['Block']['display_to_date'];
								} else if(!empty($block['Block']['display_to_date'])) {
									$display_to_date = $this->Timezone->date($block['Block']['display_to_date']);
									$display_to_date = date(__('Y-m-d H:i'), strtotime($display_to_date));
								} else {
									$display_to_date = '';
								}

								$settings = array(
									'id' => "nc-block-style-display-from-date-".$block_id,
									'value' => $display_from_date,
									'label' => false,
									'div' => false,
									'class'  => 'nc-datetime',
									'maxlength' => 16,
									'size' => 15,
									'type' => 'text',
									'error' => array('attributes' => array(
										'selector' => $this->Js->escape("$('[name=data\\[Block\\]\\[display_from_date\\]]', $('#nc-block-style-tab-init".$block_id."'))")
									))
								);
								if($block['Block']['display_flag']) {
									$settings['disabled'] = 'disabled';
								}
								echo $this->Form->input('Block.display_from_date', $settings);
							?>

							<div class="nc-block-style-display-to-date">
								<?php echo(__('&nbsp;-&nbsp;'));?>
								<?php
									$settings = array(
										'id' => "nc-block-style-display-to-date-".$block_id,
										'value' => $display_to_date,
										'label' => false,
										'div' => false,
										'class'  => 'nc-datetime',
										'size' => 15,
										'maxlength' => 16,
										'type' => 'text',
										'error' => array('attributes' => array(
											'selector' => $this->Js->escape("$('[name=data\\[Block\\]\\[display_to_date\\]]', $('#nc-block-style-tab-init".$block_id."'))")
										))
									);
									echo $this->Form->input('Block.display_to_date', $settings);
								?>
							</div>
						</dd>
					</dl>
				</li>
				<li>
					<dl>
						<dt>
							<label for="nc-block-style-min-width-<?php echo($block_id);?>">
								<?php echo(__d('block', 'Minimum width'));?>
							</label>
						</dt>
						<dd>
							<?php
								$settings = array(
									'id' => "nc-block-style-min-width-".$block_id,
									'value' => ($block['Block']['min_width_size'] == BLOCK_STYLE_MIN_SIZE_AUTO || $block['Block']['min_width_size'] == BLOCK_STYLE_MIN_SIZE_100) ? $block['Block']['min_width_size'] : BLOCK_STYLE_MIN_SIZE_BY_HAND,
									'label' => false,
									'div' => false,
									'type' =>'select',
									'class' => 'nc-block-style-min-size',
									'options' => array(
										BLOCK_STYLE_MIN_SIZE_AUTO => __('Auto'),
										BLOCK_STYLE_MIN_SIZE_100 => __('100%'),
										BLOCK_STYLE_MIN_SIZE_BY_HAND => __('By hand'),
									),
									'onchange' => '$.BlockStyle.chgSize(this, \''.'#nc-block-style-min-width-size-'.$block_id.'\');'
								);
								echo $this->Form->input('Block.min_width_size_select', $settings);
							?>
							<?php
								$settings = array(
									'id' => "nc-block-style-min-width-size-".$block_id,
									'value' => $block['Block']['min_width_size'],
									'label' => false,
									'div' => false,
									'class' => 'nc-block-style-min-size-text align-right',
									'size' => 5,
									'maxlength' => 4,
									'type' => 'text',
									'error' => array('attributes' => array(
										'selector' => $this->Js->escape("$('[name=data\\[Block\\]\\[min_width_size\\]]', $('#nc-block-style-tab-init".$block_id."'))")
									))
								);
								if($block['Block']['min_width_size'] <= 0) {
									$settings['style'] = 'display:none;';
								}
								echo $this->Form->input('Block.min_width_size', $settings);
							?>
						</dd>
					</dl>
				</li>
				<li>
					<dl>
						<dt>
							<label for="">
								<?php echo(__d('block', 'Minimum height'));?>
							</label>
						</dt>
						<dd>
							<?php
								$settings = array(
									'id' => "nc-block-style-min-height-".$block_id,
									'value' => ($block['Block']['min_height_size'] == BLOCK_STYLE_MIN_SIZE_AUTO || $block['Block']['min_height_size'] == BLOCK_STYLE_MIN_SIZE_100) ? $block['Block']['min_height_size'] : BLOCK_STYLE_MIN_SIZE_BY_HAND,
									'label' => false,
									'div' => false,
									'type' =>'select',
									'class' => 'nc-block-style-min-size',
									'options' => array(
										BLOCK_STYLE_MIN_SIZE_AUTO => __('Auto'),
										BLOCK_STYLE_MIN_SIZE_100 => __('100%'),
										BLOCK_STYLE_MIN_SIZE_BY_HAND => __('By hand'),
									),
									'onchange' => '$.BlockStyle.chgSize(this, \''.'#nc-block-style-min-height-size-'.$block_id.'\');',
								);
								echo $this->Form->input('Block.min_height_size_select', $settings);
							?>
							<?php
								$settings = array(
									'id' => "nc-block-style-min-height-size-".$block_id,
									'value' => $block['Block']['min_height_size'],
									'label' => false,
									'div' => false,
									'class' => 'nc-block-style-min-size-text align-right',
									'size' => 5,
									'maxlength' => 4,
									'type' => 'text',
									'error' => array('attributes' => array(
										'selector' => $this->Js->escape("$('[name=data\\[Block\\]\\[min_height_size\\]]', $('#nc-block-style-tab-init".$block_id."'))")
									))
								);
								if($block['Block']['min_height_size'] <= 0) {
									$settings['style'] = 'display:none;';
								}
								echo $this->Form->input('Block.min_height_size', $settings);
							?>
						</dd>
					</dl>
				</li>
				<li>
					<dl>
						<dt>
							<label for="">
								<?php echo(__d('block', 'Margin'));?>
							</label>
						</dt>
						<dd class="nc-block-style-margin">
							<?php
								$settings = array(
									'value' => $block['Block']['top_margin'],
									'label' => false,
									'class' => 'nc-block-style-top-margin align-right',
									'size' => 4,
									'maxlength' => 3,
									'type' => 'text',
									'after' => '&nbsp;'.__('px'),
									'before' => __d('block', 'Top').'&nbsp;:&nbsp;',
								);
								$settings['error'] = array('attributes' => array(
									'selector' => $this->Js->escape("$('[name=data\\[Block\\]\\[top_margin\\]]', $('#nc-block-style-tab-init".$block_id."'))")
								));
								echo $this->Form->input('Block.top_margin', $settings);

								$settings['value'] = $block['Block']['right_margin'];
								$settings['before'] = __d('block', 'Right').'&nbsp;:&nbsp;';
								$settings['error'] = array('attributes' => array(
									'selector' => $this->Js->escape("$('[name=data\\[Block\\]\\[right_margin\\]]', $('#nc-block-style-tab-init".$block_id."'))")
								));
								echo $this->Form->input('Block.right_margin', $settings);

								$settings['value'] = $block['Block']['bottom_margin'];
								$settings['before'] = __d('block', 'Bottom').'&nbsp;:&nbsp;';
								$settings['error'] = array('attributes' => array(
									'selector' => $this->Js->escape("$('[name=data\\[Block\\]\\[bottom_margin\\]]', $('#nc-block-style-tab-init".$block_id."'))")
								));
								echo $this->Form->input('Block.bottom_margin', $settings);

								$settings['value'] = $block['Block']['left_margin'];
								$settings['before'] = __d('block', 'Left').'&nbsp;:&nbsp;';
								$settings['error'] = array('attributes' => array(
									'selector' => $this->Js->escape("$('[name=data\\[Block\\]\\[left_margin\\]]', $('#nc-block-style-tab-init".$block_id."'))")
								));
								echo $this->Form->input('Block.left_margin', $settings);
							?>
						</dd>
					</dl>
				</li>
			</ul>
		</fieldset>
		<?php
			echo $this->Form->hidden('is_apply' , array('name' => 'is_apply', 'id' => "nc-block-style-apply-".$block_id, 'value' => _ON));
			echo $this->Html->div('submit',
				$this->Form->button(__('Ok'), array('name' => 'ok', 'class' => 'common-btn', 'type' => 'button',
					'onclick' => '$.BlockStyle.clickSubmit(this, \''.'#nc-block-style-apply-'.$block_id.'\'); $(this.form).submit();')).
				$this->Form->button(__('Cancel'), array('name' => 'cancel', 'class' => 'common-btn', 'type' => 'button',
					'onclick' => '$(\'#nc-block-style-dialog'.$block_id.'\').remove(); return false;')).
				$this->Form->button(__('Apply'), array('name' => 'apply', 'class' => 'common-btn',
					'onclick' => '$.BlockStyle.clickSubmit(this, \''.'#nc-block-style-apply-'.$block_id.'\');'))
			);
			echo $this->Form->end();
		?>
	</div>
	<div id="nc-block-style-tab-theme<?php echo($block_id); ?>" class="nc-block-style-theme-outer">

	</div>
</div>
<?php
	echo $this->Html->css(array('Block.style/index', 'plugins/jquery-ui-timepicker-addon.css'));
	echo $this->Html->script(array('Block.style/index', 'plugins/jquery-ui-timepicker-addon.js', 'locale/'.$locale.'/plugins/jquery-ui-timepicker.js'));
?>
<script>
$(function(){
	$('#nc-block-style-tab<?php echo($block_id); ?>').BlockStyle(<?php echo($active_tab);?>, <?php echo($block_id);?>);
});
</script>
</div>