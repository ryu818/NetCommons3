<?php
/**
 * 権限管理 登録確認画面
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
		$options = array('type' => 'post', 'data-ajax' => '#authority-list');
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
		if ($authority['Authority']['myportal_use_flag'] != NC_MYPORTAL_USE_NOT || $authority['Authority']['private_use_flag'] != _OFF) {
			$backAction = 'usable_module';
		} else {
			$backAction = 'detail';
		}
		$title .= ' ['.$authority['Authority']['default_authority_name'].']';
		$backUrl = array('action' => $backAction, $authorityId);
		$backAttr = array('data-ajax' => '#authority-list', 'data-ajax-type' => 'post', 'data-ajax-serialize' => true);
	?>
	<?php
		$bufBackAttr = $backAttr;
		$bufBackAttr['class'] = 'bold';
		echo $this->Html->link($title, array('action' => 'edit', $authorityId), $bufBackAttr);
	?>
	&nbsp;&gt;&gt;&nbsp;
	<?php
		$bufBackAttr = $backAttr;
		$bufBackAttr['class'] = 'bold';
		echo $this->Html->link(__d('authority', 'Set level'), array('action' => 'set_level', $authorityId), $bufBackAttr);
	?>
	&nbsp;&gt;&gt;&nbsp;
	<?php
		echo $this->Html->link(__d('authority', 'Detail setting'), array('action' => 'detail', $authorityId), $bufBackAttr);
	?>
	&nbsp;&gt;&gt;&nbsp;
	<?php if ($backAction == 'usable_module'): ?>
	<?php
		$bufBackAttr = $backAttr;
		$bufBackAttr['class'] = 'bold';
		echo $this->Html->link(__d('authority', 'Usable modules'), array('action' => 'usable_module', $authorityId), $bufBackAttr);
	?>
	&nbsp;&gt;&gt;&nbsp;
	<?php endif; ?>
	<h3 class="bold display-inline">
		<?php echo __d('authority', 'Confirm'); ?>
	</h3>
	<div class="top-description">
		<?php echo __d('authority', 'Please confirm the login contetn, then click [Ok].'); ?>
	</div>

	<fieldset class="form authority-detail authority-confirm">
	<ul class="lists">
		<li>
			<dl>
				<dt>
					<?php
						echo __d('authority', 'Authority name');
					?>
				</dt>
				<dd>
					<?php
						echo h($this->request->data['Authority']['default_authority_name']);
					?>
				</dd>
			</dl>
		</li>
		<li>
			<dl>
				<dt>
					<?php
						echo __d('authority', 'Basic authority');
					?>
				</dt>
				<dd>
					<?php
						echo h($user_authority_name);
					?>
				</dd>
			</dl>
		</li>
		<li>
			<dl>
				<dt>
					<?php
						echo __d('authority', 'Classification');
					?>
				</dt>
				<dd>
					<?php
						$level = intval($this->request->data['Authority']['hierarchy'])%100;
						if($level == 0) {
							$level = 100;
						}
						echo $level;
					?>
				</dd>
			</dl>
		</li>
		<li>
			<dl>
				<dt>
					<?php
						echo __d('authority', 'Use myportal room?');
					?>
				</dt>
				<dd>
					<?php
						switch($authority['Authority']['myportal_use_flag']) {
							case NC_MYPORTAL_USE_NOT:
								$value = __d('authority', 'No');
								break;
							case NC_MYPORTAL_USE_ALL:
								$value = __d('authority', 'Yes');
								break;
							default:
								$value = __d('authority', 'Yes').'['.__d('authority', 'Display only a login member').']';
								break;
						}
						echo $value;
					?>
					<?php if($authority['Authority']['myportal_use_flag'] == NC_MYPORTAL_MEMBERS): ?>
						<div class="authority-confirm-sub-div">
							<?php echo __d('authority', 'Reading authority').':'.h($myportal_viewing_user_authority_name); ?>
						</div>
					<?php endif; ?>
				</dd>
			</dl>
		</li>
		<li>
			<dl>
				<dt>
					<?php
						echo __d('authority', 'Use private room?');
					?>
				</dt>
				<dd>
					<?php
						if($authority['Authority']['private_use_flag'] == _ON) {
							$value = __d('authority', 'Yes');
						} else {
							$value = __d('authority', 'No');
						}
						echo $value;
					?>
				</dd>
			</dl>
		</li>
		<li>
			<dl>
				<dt>
					<?php
						echo __d('authority', 'Community creating authority');
					?>
				</dt>
				<dd>
					<?php
						switch($authority['Authority']['allow_creating_community']) {
							case NC_ALLOW_CREATING_COMMUNITY_OFF:
								$value = __d('authority', 'Community can not create.');
								break;
							case NC_ALLOW_CREATING_COMMUNITY_ONLY_USER:
								$value = __d('authority', 'Allow to create normal community.');
								break;
							case NC_ALLOW_CREATING_COMMUNITY_ALL_USER:
								$value = __d('authority', 'Allow to create partial public community(Login members of all the viewable).');
								break;
							case NC_ALLOW_CREATING_COMMUNITY_ALL:
								$value = __d('authority', 'Allow to create public community(users of all the viewable).');
								break;
							default:
								$value = __d('authority', 'Allow to create public community, display order change of community of all, delete is possible.');
								break;
						}
						echo $value;
					?>
					<?php if($authority['Authority']['allow_new_participant']): ?>
						<div class="authority-confirm-sub-div">
							<?php echo __d('authority', 'Allow to add new participants in the community.'); ?>
						</div>
					<?php endif; ?>
				</dd>
			</dl>
		</li>
		<li>
			<dl>
				<dt>
					<?php
						echo __d('authority', 'Used the participant setting of the room.');
					?>
				</dt>
				<dd>
					<?php
						if($authority['Authority']['display_participants_editing'] == _ON) {
							$value = __d('authority', 'Yes');
						} else {
							$value = __d('authority', 'No');
						}
						echo $value;
					?>
				</dd>
			</dl>
		</li>
		<li>
			<dl>
				<dt>
					<?php
						echo __d('authority', 'Allow HTML tags?');
					?>
				</dt>
				<dd>
					<?php
						if($authority['Authority']['allow_htmltag_flag'] == _ON) {
							$value = __d('authority', 'Permitted');
						} else {
							$value = __d('authority', 'Not permitted');
						}
						echo $value;
					?>
				</dd>
			</dl>
		</li>
		<li>
			<dl>
				<dt>
					<?php
						echo __d('authority', 'Allow to change page layout?');
					?>
				</dt>
				<dd>
					<?php
						if($authority['Authority']['allow_layout_flag'] == _ON) {
							$value = __d('authority', 'Allowed');
						} else {
							$value = __d('authority', 'Not allowed');
						}
						echo $value;
					?>
				</dd>
			</dl>
		</li>
		<li>
			<dl>
				<dt>
					<?php
						echo __d('authority', 'Allow to uploads files?');
					?>
				</dt>
				<dd>
					<?php
						switch($authority['Authority']['allow_attachment']) {
							case NC_ALLOW_ATTACHMENT_ALL:
								$value = __d('authority', 'All files');
								break;
							case NC_ALLOW_ATTACHMENT_IMAGE:
								$value = __d('authority', 'Only image files');
								break;
							default:
								$value = __d('authority', 'Not allowed');
								break;
						}
						echo $value;
					?>
				</dd>
			</dl>
		</li>
		<li>
			<dl>
				<dt>
					<?php
						echo __d('authority', 'Video files paste from the editor');
					?>
				</dt>
				<dd>
					<?php
						if($authority['Authority']['allow_video'] == _ON) {
							$value = __d('authority', 'Allowed');
						} else {
							$value = __d('authority', 'Not allowed');
						}
						echo $value;
					?>
				</dd>
			</dl>
		</li>
		<li>
			<dl>
				<dt>
					<?php
						echo __d('authority', 'The total size of uploads files');
					?>
				</dt>
				<dd>
					<?php
						echo $max_size_options[$authority['Authority']['max_size']];;
					?>
				</dd>
			</dl>
		</li>
	</ul>

	<fieldset class="authority-system-module-fieldset">
		<legend>
			<?php
				$count = 0;
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
				if(!$checked) {
					continue;
				}
				if($count != 0) {
					echo ',&nbsp;';
				}
				echo h($module['Module']['module_name']);
				$count++;
			?>
		<?php endforeach; ?>
	</fieldset>
	<fieldset class="authority-fieldset">
		<legend>
			<?php
				$count = 0;
				echo __d('authority', 'Select site-manager modules to use');
			?>
		</legend>
		<?php foreach ($site_modules as $dirName => $module): ?>
			<?php
				if(count($system_modules_options['checked']) > 0 && $system_modules_options['checked'][0] == 'All' || in_array($dirName, $system_modules_options['checked'])) {
					$checked = true;
				} else {
					$checked = false;
				}
				if(!$checked) {
					continue;
				}
				if($count != 0) {
					echo ',&nbsp;';
				}
				echo h($module['Module']['module_name']);
				$count++;
			?>
		<?php endforeach; ?>
	</fieldset>
	<fieldset class="authority-fieldset">
		<legend>
			<?php echo __d('authority', 'Allow to use the User Manager?');?>
		</legend>
		<?php
			if($user['ModuleSystemLink']['hierarchy'] >= NC_AUTH_MIN_CHIEF) {
				$value = __d('authority', 'User Search, User Login, Delete');
			} else {
				$value = __d('authority', 'Only search user');
			}
			echo h($value);
		?>
	</fieldset>
	<fieldset class="authority-fieldset">
		<legend>
			<?php echo __d('authority', 'Create room?');?>
		</legend>
		<ul>
		<li>
		<?php
			if($authority['Authority']['public_createroom_flag']) {
				echo __d('authority', 'Allow to create room in Public Space.');
			}
		?>
		</li>
		<li>
		<?php
			// TODO: マイポータルもサブグループを作成できるようにするほうが望ましい。myportal_createroom_flag
			// private_createroom_flag
			if($authority['Authority']['group_createroom_flag']) {
				echo __d('authority', 'Allow to create room in Community.');
			}
		?>
		</li>
		</ul>
	</fieldset>
	<fieldset class="authority-fieldset">
		<legend>
			<?php echo __d('authority', 'Page block operation');?>
		</legend>
		<ul>
		<li>
		<?php
			if($authority['Authority']['allow_move_operation']) {
				echo __d('authority', 'Allow to move of the page block.');
			}
		?>
		</li>
		<li>
		<?php
			if($authority['Authority']['allow_copy_operation']) {
				echo __d('authority', 'Allow to copy of the page block.');
			}
		?>
		</li>
		<li>
		<?php
			if($authority['Authority']['allow_shortcut_operation']) {
				echo __d('authority', 'Allow to create shortcut of the page block.');
			}
		?>
		</li>
		<li>
		<?php
			if($authority['Authority']['allow_operation_of_shortcut']) {
				echo __d('authority', 'Allow creating shortcuts, copy or move the shortcut page block.');
			}
		?>
		</li>
		</ul>
	</fieldset>
	<?php
		// TODO:  change_leftcolumn_flag,change_rightcolumn_flag,change_headercolumn_flag,change_footercolumn_flag未作成。
		// カラムチェンジャー用
	?>
	<?php
		$myportalEnrollOptions = array();
		foreach($modules as $module) {
			if(in_array($module['Module']['id'], $myportal_enroll_modules)) {
				$myportalEnrollOptions[$module['Module']['id']] = $module['Module']['module_name'];
			}
		}
		$privateEnrollOptions = array();
		foreach($modules as $module) {
			if(in_array($module['Module']['id'], $private_enroll_modules)) {
				$privateEnrollOptions[$module['Module']['id']] = $module['Module']['module_name'];
			}
		}
	?>
	<?php if ($authority['Authority']['myportal_use_flag'] != NC_MYPORTAL_USE_NOT): ?>
	<fieldset class="authority-fieldset">
		<legend>
			<?php
				$count = 0;
				echo __d('authority', 'Modules installed in %s', __('Myportal'));
			?>
		</legend>
		<?php foreach ($myportalEnrollOptions as $moduleName): ?>
			<?php
				if($count != 0) {
					echo ',&nbsp;';
				}
				echo h($moduleName);
				$count++;
			?>
		<?php endforeach; ?>
	</fieldset>
	<?php endif; ?>
	<?php if ($authority['Authority']['private_use_flag'] != _OFF): ?>
	<fieldset class="authority-fieldset">
		<legend>
			<?php
				$count = 0;
				echo __d('authority', 'Modules installed in %s', __('Private room'));
			?>
		</legend>
		<?php foreach ($privateEnrollOptions as $moduleName): ?>
			<?php
				if($count != 0) {
					echo ',&nbsp;';
				}
				echo h($moduleName);
				$count++;
			?>
		<?php endforeach; ?>
	</fieldset>
	<?php endif; ?>
	</fieldset>
	<?php
		$bufBackAttr = array('name' => 'back', 'class' => 'common-btn', 'type' => 'button', 'data-ajax-url' => $this->Html->url($backUrl));
		$backAttr = array_merge($backAttr, $bufBackAttr);
		echo $this->Html->div('submit align-right',
			$this->Form->button(__('&lt;&lt;Back'), $backAttr).
			$this->Form->button(__('Ok'), array('name' => 'ok', 'class' => 'common-btn', 'type' => 'submit')).
			$this->Form->button(__('Cancel'), array('name' => 'cancel', 'class' => 'common-btn', 'type' => 'button',
				'data-ajax' => '#'.$id, 'data-ajax-method' =>'inner', 'data-ajax-url' =>  $this->Html->url(array('action' => 'index', 'language' => $language))))
		);
		echo $this->element('hidden');
		echo $this->Form->hidden('on_regist' , array('name' => 'on_regist', 'value' => _ON));
		echo $this->Form->end();
	?>
	<script>
	$(function(){
		$.Authority.initUsableModule('<?php echo $id; ?>');
	});
	</script>
</div>