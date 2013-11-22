<?php
/**
 * 権限管理 権限詳細画面(その２)
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
		$options = array('action' => 'usable_module', 'data-ajax' => '#authority-list');
		if(!empty($authority['Authority']['id'])) {
			$options['url'] = array($authority['Authority']['id']);
			$authorityId = $authority['Authority']['id'];
		}
		$options['data-confirm-url'] = $this->Html->url(array('action' => 'confirm', $authorityId));
		echo $this->Form->create('Authority', $options);

		if(empty($authorityId)) {
			$title = __d('authority', 'Add new authority');
		} else {
			$title = __d('authority', 'Edit authority');
		}
		$title .= ' ['.$authority['Authority']['default_name'].']';
		$backUrl = array('action' => 'detail', $authorityId);
		$backAttr = array('data-ajax' => '#authority-list', 'data-ajax-type' => 'post', 'data-ajax-serialize' => true);
	?>
	<?php
		$bufBackAttr = $backAttr;
		$bufBackAttr['class'] = 'bold';
		echo $this->Html->link($title, array('action' => 'edit', $authorityId), $bufBackAttr);
	?>
	&nbsp;&gt;&gt;&nbsp;
	<?php
		echo $this->Html->link(__d('authority', 'Set level'), array('action' => 'set_level', $authorityId), $bufBackAttr);
	?>
	&nbsp;&gt;&gt;&nbsp;
	<?php
		echo $this->Html->link(__d('authority', 'Detail setting'), $backUrl, $bufBackAttr);
	?>
	&nbsp;&gt;&gt;&nbsp;
	<h3 class="bold display-inline">
		<?php echo __d('authority', 'Detail setting (Part 2)'); ?>
	</h3>
	<div class="nc-top-description">
		<?php echo __d('authority', 'Make sure your change, and press [Next].'); ?>
	</div>


	<fieldset class="form authority-detail">
	<ul class="nc-lists">
		<li>
			<dl>
				<dt>
					<?php
						$columnName = 'allow_creating_community';
						echo $this->Form->label('Authority.'.$columnName,  __d('authority', 'Community creating authority'));
					?>
					<span class="require"><?php echo __('*'); ?></span>
				</dt>
				<dd>
					<?php
						$options = array(
							NC_ALLOW_CREATING_COMMUNITY_OFF => __d('authority', 'Community can not create.'),
							NC_ALLOW_CREATING_COMMUNITY_ONLY_USER => __d('authority', 'Allow to create private community.'),
							NC_ALLOW_CREATING_COMMUNITY_ALL_USER => __d('authority', 'Allow to create public community.'),
						);
						if($authority['Authority']['hierarchy'] >= NC_AUTH_MIN_CHIEF) {
							// 「全会員を強制的に参加させる」コミュニティーは主担以上で設定可能
							$options[NC_ALLOW_CREATING_COMMUNITY_FORCE_ALL] = __d('authority', 'Allow to create public/private community[Join to force all members.].');
							$options[NC_ALLOW_CREATING_COMMUNITY_ADMIN] = __d('authority', 'Allow to create all communities, display order change, or delete.');
						}
						$settings = array(
							'type' => 'select',
							'options' => $options,
							'value' => $authority['Authority'][$columnName],
							'label' => false,
							'div' => false,
							'class' => 'authority-community-creating-authority',
							'disabled' => $authorityDisabled['Authority'][$columnName],
						);
						echo $this->Form->input('Authority.'.$columnName, $settings);
					?>
					<div class="note">
						<ul class="nc-lists authority-note-lists">
							<li>
								<dl>
									<dt>
										<?php echo __d('authority', 'Private community'); ?>
									</dt>
									<dd>
										:<?php echo __d('authority', 'Members of all the viewable.'); ?>
									</dd>
								</dl>
							</li>
							<li>
								<dl>
									<dt>
										<?php echo __d('authority', 'Public community'); ?>
									</dt>
									<dd>
										:<?php echo __d('authority', 'Login members of all the viewable.'); ?>
									</dd>
								</dl>
							</li>
						</ul>
						<?php echo __d('authority', 'The default authority of the community[Join to force all members.] can be set from system management.'); ?>
					</div>

					<?php
						$columnName = 'allow_new_participant';
						$settings = array(
							'type' => 'checkbox',
							'value' => $authority['Authority'][$columnName],
							'checked' => ($authority['Authority'][$columnName]) ? true : false,
							'label' => __d('authority', 'If the chief in the community, you can add a participation member freely and possible to select "Only participants" in how to participate.'),
							'div' => false,
							'disabled' => $authorityDisabled['Authority'][$columnName],
						);
						echo $this->Form->input('Authority.'.$columnName, $settings);
					?>
					<div class="note">
						<?php echo __d('authority', 'If you do not allow new participants, available as SNS.'); ?>
					</div>
				</dd>
			</dl>
		</li>

	</ul>
	<fieldset class="authority-fieldset">
		<legend>
			<?php echo __d('authority', 'Create room?');?>
		</legend>
		<ul>
		<li>
		<?php
			$columnName = 'public_createroom_flag';
			$settings = array(
				'type' => 'checkbox',
				'value' => $authority['Authority'][$columnName],
				'checked' => ($authority['Authority'][$columnName]) ? true : false,
				'label' => __d('authority', 'Allow to create room in Public Space.'),
				'div' => false,
				'disabled' => $authorityDisabled['Authority'][$columnName],
			);
			echo $this->Form->input('Authority.'.$columnName, $settings);
		?>
		</li>
		<li>
		<?php
			// TODO: マイポータルもサブグループを作成できるようにするほうが望ましい。myportal_createroom_flag
			// private_createroom_flag

			$columnName = 'group_createroom_flag';
			$settings = array(
				'type' => 'checkbox',
				'value' => $authority['Authority'][$columnName],
				'checked' => ($authority['Authority'][$columnName]) ? true : false,
				'label' => __d('authority', 'Allow to create room in Community.'),
				'div' => false,
				'disabled' => $authorityDisabled['Authority'][$columnName],
			);
			echo $this->Form->input('Authority.'.$columnName, $settings);
		?>
		</li>
		</ul>
	</fieldset>
	<fieldset class="authority-system-module-fieldset">
		<legend>
			<?php
				echo __d('authority', 'Select system-control modules to use');
			?>
		</legend>
		<?php foreach ($system_modules as $dirName => $module): ?>
			<?php
				if(count($system_modules_options['checked']) > 0 && $system_modules_options['checked'][0] == 'All' || in_array($dirName, $system_modules_options['checked'])) {
					$checked = true;
				} else {
					$checked = false;
				}
				if(count($system_modules_options['enabled']) > 0 && $system_modules_options['enabled'][0] == 'All' || in_array($dirName, $system_modules_options['enabled'])) {
					$disabled = false;
				} else {
					$disabled = true;
				}

				$settings = array(
					'id' => 'ModuleSystemLink' . $id .'-'. $module['Module']['id'],
					'type' => 'checkbox',
					'value' => $module['Module']['dir_name'],
					'checked' => $checked,
					'label' => $module['Module']['module_name'],
					'div' => false,
					'disabled' => $disabled,
				);
				echo $this->Form->input('ModuleSystemLink.'.$module['Module']['id'].'.dir_name', $settings);
			?>
		<?php endforeach; ?>
	</fieldset>
	<fieldset class="authority-fieldset">
		<legend>
			<?php echo __d('authority', 'Select site-manager modules to use');?>
		</legend>
		<?php foreach ($site_modules as $dirName => $module): ?>
			<?php
				if(count($system_modules_options['checked']) > 0 && $system_modules_options['checked'][0] == 'All' || in_array($dirName, $system_modules_options['checked'])) {
					$checked = true;
				} else {
					$checked = false;
				}
				if(count($system_modules_options['enabled']) > 0 && $system_modules_options['enabled'][0] == 'All' || in_array($dirName, $system_modules_options['enabled'])) {
					$disabled = false;
				} else {
					$disabled = true;
				}

				$settings = array(
					'id' => 'ModuleSystemLink' . $id .'-'. $module['Module']['id'],
					'type' => 'checkbox',
					'value' => $module['Module']['dir_name'],
					'checked' => $checked,
					'label' => $module['Module']['module_name'],
					'div' => false,
					'disabled' => $disabled,
				);
				echo $this->Form->input('ModuleSystemLink.'.$module['Module']['id'].'.dir_name', $settings);
			?>
		<?php endforeach; ?>
	</fieldset>
	<fieldset class="authority-fieldset">
		<legend>
			<?php echo __d('authority', 'Allow to use the User Manager?');?>
		</legend>
		<?php
			if($authority['Authority']['hierarchy'] < NC_AUTH_MIN_CHIEF || $authority['Authority']['id'] == AUTHORITY_SYSTEM_ADMIN_ID
				 || $authority['Authority']['id'] == NC_AUTH_CLERK_ID) {
				$disabled = true;
			} else {
				$disabled = false;
			}
			$options = array(
				NC_AUTH_CHIEF => __d('authority', 'User Search, User Login, Delete'),
				NC_AUTH_GENERAL => __d('authority', 'Only search user'),
			);
			$settings = array(
				'type' => 'select',
				'options' => $options,
				'value' => ($user['ModuleSystemLink']['hierarchy'] >= NC_AUTH_MIN_CHIEF) ?  NC_AUTH_CHIEF : NC_AUTH_GENERAL,
				'label' => false,
				'div' => false,
				'disabled' => $disabled,
			);
			echo $this->Form->input('ModuleSystemLink.'.$user['Module']['id'].'.hierarchy', $settings);
		?>
		<div class="note">
			<?php echo __d('authority', 'The user whose authority is under the basic authority also can view and edit after setting the [User Search, User Login, Delete].'); ?>
		</div>
	</fieldset>
	</fieldset>
	<?php
		$bufBackAttr = array('name' => 'back', 'class' => 'nc-common-btn', 'type' => 'button', 'data-ajax-url' => $this->Html->url($backUrl));
		$backAttr = array_merge($backAttr, $bufBackAttr);
		echo $this->Html->div('submit align-right',
			$this->Form->button(__('&lt;&lt;Back'), $backAttr).
			$this->Form->button(__('Next&gt;&gt;'), array('name' => 'next', 'class' => 'nc-common-btn', 'type' => 'submit')).
			$this->Form->button(__('Cancel'), array('name' => 'cancel', 'class' => 'nc-common-btn', 'type' => 'button',
				'data-ajax' => '#'.$id, 'data-ajax-method' => 'inner', 'data-ajax-url' =>  $this->Html->url(array('action' => 'index', 'language' => $language))))
		);
		echo $this->element('hidden');
		echo $this->Form->end();
	?>
	<script>
	$(function(){
		$.Authority.initDetail2('<?php echo $id; ?>', <?php echo NC_MYPORTAL_MEMBERS; ?>);
	});
	</script>
</div>