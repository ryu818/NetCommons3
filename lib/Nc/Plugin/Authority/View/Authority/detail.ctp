<?php
/**
 * 権限管理 権限詳細画面
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.User.View
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
?>
<div id="authority-list" class="authority-edit ui-tabs-panel ui-widget-content ui-corner-all">
	<?php
		$authorityId = null;
		$options = array('action' => 'detail2', 'data-ajax' => '#authority-list');
		if(!empty($authority['Authority']['id'])) {
			$options['url'] = array($authority['Authority']['id']);
			$authorityId = $authority['Authority']['id'];
		}
		echo $this->Form->create('Authority', $options);

		if(empty($authorityId)) {
			$title = __d('authority', 'Add new authority');
		} else {
			$title = __d('authority', 'Edit authority');
		}
		$title .= ' ['.$authority['Authority']['default_name'].']';
		$backUrl = array('action' => 'set_level', $authorityId);
		$backAttr = array('data-ajax' => '#authority-list', 'data-ajax-type' => 'post', 'data-ajax-serialize' => true);
	?>
	<?php
		$bufBackAttr = $backAttr;
		$bufBackAttr['class'] = 'bold';
		echo $this->Html->link($title, array('action' => 'edit', $authorityId), $bufBackAttr);
	?>
	&nbsp;&gt;&gt;&nbsp;
	<?php
		echo $this->Html->link(__d('authority', 'Set level'), $backUrl, $bufBackAttr);
	?>
	&nbsp;&gt;&gt;&nbsp;
	<h3 class="bold display-inline">
		<?php echo __d('authority', 'Detail setting'); ?>
	</h3>
	<div class="top-description">
		<?php echo __d('authority', 'Make sure your change, and press [Next].'); ?>
	</div>


	<fieldset class="form authority-detail">
	<ul class="lists">
		<li>
			<dl>
				<dt>
					<?php
						$columnName = 'myportal_use_flag';
						echo $this->Form->label('Authority.'.$columnName, __d('authority', 'Use myportal room?'));
					?>
					<span class="require"><?php echo __('*'); ?></span>
				</dt>
				<dd>
					<?php
						$settings = array(
							'type' => 'radio',
							'options' => array(NC_MYPORTAL_USE_ALL => __d('authority', 'Yes'), NC_MYPORTAL_MEMBERS => __d('authority', 'Yes').'['.__d('authority', 'Display only a login member').']', NC_MYPORTAL_USE_NOT => __d('authority', 'No')),
							'value' => $authority['Authority'][$columnName],
							'div' => false,
							'legend' => false,
							'disabled' => $authorityDisabled['Authority'][$columnName],
						);
						echo $this->Form->input('Authority.'.$columnName, $settings);
					?>
					<div id="authority-select-authority<?php echo($id); ?>" class="hr authority-select-authority"<?php if($authority['Authority'][$columnName] != NC_MYPORTAL_MEMBERS): ?> style="display:none;"<?php endif; ?>>
					<?php
						$columnName = 'allow_myportal_viewing_hierarchy';
						echo $this->Form->label('Authority.'.$columnName, __d('authority', 'Reading authority'));
						echo $this->Form->authoritySlider('Authority.'.$columnName, array('value' => $authority['Authority'][$columnName], 'min_authority_id' => NC_AUTH_GUEST_ID));
					?>
					</div>
				</dd>
			</dl>
		</li>
		<li>
			<dl>
				<dt>
					<?php
						$columnName = 'private_use_flag';
						echo $this->Form->label('Authority.'.$columnName, __d('authority', 'Use private room?'));
					?>
					<span class="require"><?php echo __('*'); ?></span>
				</dt>
				<dd>
					<?php
						$settings = array(
							'type' => 'radio',
							'options' => array(_ON => __d('authority', 'Yes'), _OFF => __d('authority', 'No')),
							'value' => $authority['Authority'][$columnName],
							'div' => false,
							'legend' => false,
							'disabled' => $authorityDisabled['Authority'][$columnName],
						);
						echo $this->Form->input('Authority.'.$columnName, $settings);
					?>
				</dd>
			</dl>
		</li>
		<li>
			<dl>
				<dt>
					<?php
						$columnName = 'display_participants_editing';
						echo $this->Form->label('Authority.'.$columnName, __d('authority', 'Used the participant setting of the room.'));
					?>
					<span class="require"><?php echo __('*'); ?></span>
				</dt>
				<dd>
					<?php
						if($authority['Authority']['system_flag']) {
							$disabled = true;
						} else {
							$disabled = $authorityDisabled['Authority'][$columnName];
						}
						$settings = array(
							'type' => 'radio',
							'options' => array(_ON => __d('authority', 'Yes'), _OFF => __d('authority', 'No')),
							'value' => $authority['Authority'][$columnName],
							'div' => false,
							'legend' => false,
							'disabled' => $disabled,
						);
						echo $this->Form->input('Authority.'.$columnName, $settings);
					?>
				</dd>
			</dl>
		</li>
		<li>
			<dl>
				<dt>
					<?php
						$columnName = 'allow_htmltag_flag';
						echo $this->Form->label('Authority.'.$columnName, __d('authority', 'Allow HTML tags?'));
					?>
					<span class="require"><?php echo __('*'); ?></span>
				</dt>
				<dd>
					<?php
						$settings = array(
							'type' => 'radio',
							'options' => array(_ON => __d('authority', 'Permitted'), _OFF => __d('authority', 'Not permitted')),
							'value' => $authority['Authority'][$columnName],
							'div' => false,
							'legend' => false,
							'disabled' => $authorityDisabled['Authority'][$columnName],
						);
						echo $this->Form->input('Authority.'.$columnName, $settings);
					?>
				</dd>
			</dl>
		</li>
		<li>
			<dl>
				<dt>
					<?php
						$columnName = 'allow_meta_flag';
						echo $this->Form->label('Authority.'.$columnName,  __d('authority', 'Allow to change page information?'));
					?>
					<span class="require"><?php echo __('*'); ?></span>
				</dt>
				<dd>
					<?php
						$settings = array(
							'type' => 'radio',
							'options' => array(_ON => __d('authority', 'Allowed'), _OFF => __d('authority', 'Not allowed')),
							'value' => $authority['Authority'][$columnName],
							'div' => false,
							'legend' => false,
							'disabled' => $authorityDisabled['Authority'][$columnName],
						);
						echo $this->Form->input('Authority.'.$columnName, $settings);
					?>
				</dd>
			</dl>
		</li>
		<li>
			<dl>
				<dt>
					<?php
						$columnName = 'allow_theme_flag';
						echo $this->Form->label('Authority.'.$columnName,  __d('authority', 'Allow to change page theme?'));
					?>
					<span class="require"><?php echo __('*'); ?></span>
				</dt>
				<dd>
					<?php
						$settings = array(
							'type' => 'radio',
							'options' => array(_ON => __d('authority', 'Allowed'), _OFF => __d('authority', 'Not allowed')),
							'value' => $authority['Authority'][$columnName],
							'div' => false,
							'legend' => false,
							'disabled' => $authorityDisabled['Authority'][$columnName],
						);
						echo $this->Form->input('Authority.'.$columnName, $settings);
					?>
				</dd>
			</dl>
		</li>
		<li>
			<dl>
				<dt>
					<?php
						$columnName = 'allow_style_flag';
						echo $this->Form->label('Authority.'.$columnName,  __d('authority', 'Allow to change page style?'));
					?>
					<span class="require"><?php echo __('*'); ?></span>
				</dt>
				<dd>
					<?php
						$settings = array(
							'type' => 'radio',
							'options' => array(NC_ALLOWED_TO_EDIT_CSS => __d('authority', 'Allowed to edit the CSS'), _ON => __d('authority', 'Allowed'), _OFF => __d('authority', 'Not allowed')),
							'value' => $authority['Authority'][$columnName],
							'div' => false,
							'legend' => false,
							'disabled' => $authorityDisabled['Authority'][$columnName],
						);
						echo $this->Form->input('Authority.'.$columnName, $settings);
					?>
				</dd>
			</dl>
		</li>
		<li>
			<dl>
				<dt>
					<?php
						$columnName = 'allow_layout_flag';
						echo $this->Form->label('Authority.'.$columnName,  __d('authority', 'Allow to change page layout?'));
					?>
					<span class="require"><?php echo __('*'); ?></span>
				</dt>
				<dd>
					<?php
						$settings = array(
							'type' => 'radio',
							'options' => array(_ON => __d('authority', 'Allowed'), _OFF => __d('authority', 'Not allowed')),
							'value' => $authority['Authority'][$columnName],
							'div' => false,
							'legend' => false,
							'disabled' => $authorityDisabled['Authority'][$columnName],
						);
						echo $this->Form->input('Authority.'.$columnName, $settings);
					?>
				</dd>
			</dl>
		</li>
		<li>
			<dl>
				<dt>
					<?php
						$columnName = 'allow_attachment';
						echo $this->Form->label('Authority.'.$columnName, __d('authority', 'Allow to uploads files?'));
					?>
					<span class="require"><?php echo __('*'); ?></span>
				</dt>
				<dd>
					<?php
						$settings = array(
							'type' => 'radio',
							'options' => array(NC_ALLOW_ATTACHMENT_ALL => __d('authority', 'All files'), NC_ALLOW_ATTACHMENT_IMAGE => __d('authority', 'Only image files'), NC_ALLOW_ATTACHMENT_NO => __d('authority', 'Not allowed')),
							'value' => $authority['Authority'][$columnName],
							'div' => false,
							'legend' => false,
							'disabled' => $authorityDisabled['Authority'][$columnName],
						);
						echo $this->Form->input('Authority.'.$columnName, $settings);
					?>
				</dd>
			</dl>
		</li>
		<li>
			<dl>
				<dt>
					<?php
						$columnName = 'allow_video';
						echo $this->Form->label('Authority.'.$columnName,  __d('authority', 'Video files paste from the editor'));
					?>
					<span class="require"><?php echo __('*'); ?></span>
				</dt>
				<dd>
					<?php
						$settings = array(
							'type' => 'radio',
							'options' => array(_ON => __d('authority', 'Allowed'), _OFF => __d('authority', 'Not allowed')),
							'value' => $authority['Authority'][$columnName],
							'div' => false,
							'legend' => false,
							'disabled' => $authorityDisabled['Authority'][$columnName],
						);
						echo $this->Form->input('Authority.'.$columnName, $settings);
					?>
				</dd>
			</dl>
		</li>
		<li>
			<dl>
				<dt>
					<?php
						$columnName = 'max_size';
						echo $this->Form->label('Authority.'.$columnName,  __d('authority', 'The total size of uploads files'));
					?>
					<span class="require"><?php echo __('*'); ?></span>
				</dt>
				<dd>
					<?php
						$settings = array(
							'type' => 'select',
							'options' => $max_size_options,
							'value' => $authority['Authority'][$columnName],
							'label' => false,
							'div' => false
						);
						echo $this->Form->input('Authority.'.$columnName, $settings);
					?>
				</dd>
			</dl>
		</li>
	</ul>
	<fieldset class="authority-fieldset">
		<legend>
			<?php echo __d('authority', 'Page block operation');?>
		</legend>
		<ul>
		<li>
		<?php
			$columnName = 'allow_move_operation';
			$settings = array(
				'type' => 'checkbox',
				'value' => $authority['Authority'][$columnName],
				'checked' => ($authority['Authority'][$columnName]) ? true : false,
				'label' => __d('authority', 'Allow to move of the page block.'),
				'div' => false,
				'disabled' => $authorityDisabled['Authority'][$columnName],
			);
			echo $this->Form->input('Authority.'.$columnName, $settings);
		?>
		</li>
		<li>
		<?php
			$columnName = 'allow_copy_operation';
			$settings = array(
				'type' => 'checkbox',
				'value' => $authority['Authority'][$columnName],
				'checked' => ($authority['Authority'][$columnName]) ? true : false,
				'label' => __d('authority', 'Allow to copy of the page block.'),
				'div' => false,
				'disabled' => $authorityDisabled['Authority'][$columnName],
			);
			echo $this->Form->input('Authority.'.$columnName, $settings);
		?>
		</li>
		<li>
		<?php
			$columnName = 'allow_shortcut_operation';
			$settings = array(
				'type' => 'checkbox',
				'value' => $authority['Authority'][$columnName],
				'checked' => ($authority['Authority'][$columnName]) ? true : false,
				'label' => __d('authority', 'Allow to create shortcut of the page block.'),
				'div' => false,
				'disabled' => $authorityDisabled['Authority'][$columnName],
			);
			echo $this->Form->input('Authority.'.$columnName, $settings);
		?>
		</li>
		<?php
			$columnName = 'allow_operation_of_shortcut';
			$settings = array(
				'type' => 'checkbox',
				'value' => $authority['Authority'][$columnName],
				'checked' => ($authority['Authority'][$columnName]) ? true : false,
				'label' => __d('authority', 'Allow creating shortcuts, copy or move the shortcut page block.'),
				'div' => false,
				'disabled' => $authorityDisabled['Authority'][$columnName],
			);
			echo $this->Form->input('Authority.'.$columnName, $settings);
		?>
		</li>
		</ul>
	</fieldset>
	<?php
		// TODO:  change_leftcolumn_flag,change_rightcolumn_flag,change_headercolumn_flag,change_footercolumn_flag未作成。
		// カラムチェンジャー用
	?>
	</fieldset>
	<?php
		$bufBackAttr = array('name' => 'back', 'class' => 'common-btn', 'type' => 'button', 'data-ajax-url' => $this->Html->url($backUrl));
		$backAttr = array_merge($backAttr, $bufBackAttr);
		echo $this->Html->div('submit align-right',
			$this->Form->button(__('&lt;&lt;Back'), $backAttr).
			$this->Form->button(__('Next&gt;&gt;'), array('name' => 'next', 'class' => 'common-btn', 'type' => 'submit')).
			$this->Form->button(__('Cancel'), array('name' => 'cancel', 'class' => 'common-btn', 'type' => 'button',
				'data-ajax' => '#'.$id, 'data-ajax-method' => 'inner', 'data-ajax-url' =>  $this->Html->url(array('action' => 'index', 'language' => $language))))
		);
		echo $this->element('hidden');
		echo $this->Form->end();
	?>
	<script>
	$(function(){
		$.Authority.initDetail('<?php echo $id; ?>', <?php echo NC_MYPORTAL_MEMBERS; ?>);
	});
	</script>
</div>