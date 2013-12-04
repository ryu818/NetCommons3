<?php
/**
 * コミュニティー招待画面
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.Page.View
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
?>
<div id="pages-menu-invite-community">
<?php
	echo $this->Form->create('Community', array('data-ajax' => '#pages-menu-invite-community', 'data-ajax-callback' => 'if($.Common.isSetFlash(res)) {$(\'#pages-menu-community-inf'.'\').dialog(\'close\');}'));
?>
	<fieldset class="form">
		<ul class="nc-lists pages-menu-invite-community-list">
			<li>
				<dl>
					<dt>
						<?php
							$selectMembersId = 'pages-menus-invite-member-'.$community['Community']['room_id'];
							$name = "invite_members";
							echo $this->Form->label($name,  __d('page', 'Members to be invited:'), array('for' => $selectMembersId));
						?>
						<span class="require">
							<?php echo __('*');?>
						</span>
					</dt>
					<dd>
						<?php
							$value = array();
							if(isset($configs['invite_members']) && is_array($configs['invite_members'])) {
								foreach($configs['invite_members'] as $inviteMember) {
									$value[$inviteMember] = $inviteMember;
								}
							}
							$settings = array(
								'id' => $selectMembersId,
								'name' => $name,
								'data-placeholder' => __d('page', 'Members to be invited:'),
								'class' => 'pages-menu-invite-community',
								'label' => false,
								'div' => false,
								'type' =>'select',
								'value' => $value,
								'options' => $value,
								'multiple' => true,
							);
							echo $this->Form->input($name, $settings);
						?>
						<div class="pages-menu-invite-community-select-members">
							<?php
							$url = array('plugin' => 'user', 'controller' => 'user', 'action' => 'search', 'select_member_room_id' => $community['Community']['room_id']);
							echo $this->Html->link('<span class="ui-icon ui-icon-plus float-left"></span>'.h(__d('page', 'Select members')), $url, array(
								'class' => 'nowrap',
								'escape' => false,
								'data-ajax' => "#pages-menu-invite-community-search",
								'data-ajax-dialog' => true,
								'data-ajax-dialog-options' => h('{"title" : "'.$this->Js->escape(__d('page', 'Select members')).'","modal" : true, "resizable": true, "width":"800"}'),
								'data-ajax-effect' => 'fold',
								'data-ajax-force' => 'true',
							));
							?>
						</div>
					</dd>
				</dl>
			</li>
			<li>
				<dl>
					<dt>
						<?php
							$name = "invite_mail_subject";
							echo $this->Form->label($name,  __('E-mail Subject:'));
						?>
						<span class="require">
							<?php echo __('*');?>
						</span>
					</dt>
					<dd>
						<?php
							$settings = array(
								'name' => $name,
								'type' => 'text',
								'value' => $configs['community_mail_invite_subject'],
								'label' => false,
								'maxlength' => NC_VALIDATOR_TITLE_LEN,
								'size' => 23,
								'error' => array('attributes' => array(
									'selector' => true
								))
							);
							echo $this->Form->input($name, $settings);
						?>
					</dd>
				</dl>
			</li>
			<li>
				<dl>
					<dt>
						<?php
							$name = "invite_mail_body";
							echo $this->Form->label($name,  __('Message：'));
						?>
						<span class="require">
							<?php echo __('*');?>
						</span>
					</dt>
					<dd>
						<?php
							$settings = array(
								'name' => $name,
								'type' => 'textarea',
								'value' => $configs['community_mail_invite_body'],
								'label' => false,
								'error' => array('attributes' => array(
									'selector' => true
								))
							);
							echo $this->Form->input($name, $settings);
							/* TODO:プレビューリンク、画面未作成 */
						?>
					</dd>
				</dl>
			</li>
		</ul>
	</fieldset>
	<?php
		if(isset($this->request->named['is_center'])) {
			// コミュニティー情報センター表示時
			$cancel = $this->Form->button(__('Close'), array('name' => 'close', 'class' => 'nc-common-btn', 'type' => 'button',
				'data-ajax-effect' => 'fold', 'onclick' => '$(\'#pages-menu-community-inf'.'\').dialog(\'close\'); return false;'));
		} else {
			$cancel = $this->Form->button(__('Cancel'), array('name' => 'cancel', 'class' => 'nc-common-btn', 'type' => 'button',
				'data-ajax-url' =>  $this->Html->url(array('plugin' => 'page', 'controller' => 'page_menus', 'action' => 'community_inf', $community['Community']['room_id'])),
				'data-ajax' => '#pages-menu-community-inf',
				'data-ajax-dialog' => 'true',
				'data-ajax-force' => 'true',
				'data-ajax-dialog-options' => h('{"title" : "'.$this->Js->escape(__d('page', '[%s] Community information', $community_lang['CommunityLang']['community_name'])).'", "resizable": true}'),
			));
		}
		echo $this->Html->div('submit',
			$this->Form->button(__d('page', 'Invite'), array('name' => 'ok', 'class' => 'nc-common-btn', 'type' => 'submit')).
			$cancel
		);
		echo $this->Form->end();
		echo $this->Html->script('Page.invite_community');
		echo $this->Html->css('Page.community_inf');
	?>
	<script>
	$(function(){
		$.PageInviteCommunity.inviteCommunityInit();
	});
	</script>
</div>