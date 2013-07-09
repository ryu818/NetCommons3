<?php
/**
 * 権限管理 権限編集・権限追加画面
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
	?>
	<h3 class="bold display-inline">
		<?php echo $title; ?>
	</h3>
	<div class="top-description">
		<?php echo __d('authority', 'Enter the title of the authority, specify the level of the authority, and press [Next].'); ?>
	</div>

	<fieldset class="form">
	<ul class="lists">
		<li>
			<dl>
				<dt>
					<?php
						echo $this->Form->label('Authority.default_authority_name', __d('authority', 'Authority name'));
					?>
					<span class="require"><?php echo __('*'); ?></span>
				</dt>
				<dd>
					<?php
						$settings = array(
							'type' => 'text',
							'value' => $authority['Authority']['default_authority_name'],
							'label' => false,
							'div' => false,
							'maxlength' => NC_VALIDATOR_TITLE_LEN,
							'class' => 'authority-name',
							'size' => 35,
							'error' => array('attributes' => array(
								'selector' => true
							))
						);
						echo $this->Form->input('Authority.default_authority_name', $settings);
					?>
				</dd>
			</dl>
		</li>
		<li>
			<dl>
				<dt>
					<?php
						echo $this->Form->label('Authority.base_authority_id', __d('authority', 'Basic authority'));
					?>
					<span class="require"><?php echo __('*'); ?></span>
				</dt>
				<dd>
					<?php
						$disabled = false;
						if(isset($authority['Authority']['system_flag']) && $authority['Authority']['system_flag']) {
							$disabled = true;
						}
						$baseAuthorityOptions[NC_AUTH_ADMIN_ID] = __('Administrator');
						$baseAuthorityOptions[NC_AUTH_CHIEF_ID] = __('Room Manager');
						$baseAuthorityOptions[NC_AUTH_MODERATE_ID] = __('Moderator');
						$baseAuthorityOptions[NC_AUTH_GENERAL_ID] = __('Common User');
						$baseAuthorityOptions[NC_AUTH_GUEST_ID] = __('Guest');
						$settings = array(
							'type' =>'select',
							'value' => $authority['Authority']['base_authority_id'],
							'label' => false,
							'div' => false,
							'options' => $baseAuthorityOptions,
							'error' => array('attributes' => array(
								'selector' => true
							)),
							'disabled' => $disabled,
						);
						echo $this->Form->input('Authority.base_authority_id', $settings);
					?>
				</dd>
			</dl>
		</li>
	</ul>
	</fieldset>

	<ul id="authority-edit-desc<?php echo($id); ?>" class="lists authority-edit-desc">
		<li id="authority-edit-desc<?php echo($id); ?>-<?php echo(NC_AUTH_ADMIN_ID); ?>">
			<dl class="clearfix">
				<dt>
					<?php echo __('Administrator');?>
				</dt>
				<dd>
					<?php echo __d('authority', 'Super user of the system. The one with this authority can browse and edit all the acquired data of the users. He/She is also a system manager of the NetCommons.');?>
				</dd>
			</dl>
		</li>
		<li id="authority-edit-desc<?php echo($id); ?>-<?php echo(NC_AUTH_CHIEF_ID); ?>" class="authority-even">
			<dl class="clearfix">
				<dt>
					<?php echo __('Room Manager');?>
				</dt>
				<dd>
					<?php echo  __d('authority', 'A head of a grouproom. The one with this authority can design and manage a grouproom by using modules and assining roles to group members.');?>
				</dd>
			</dl>
		</li>
		<li id="authority-edit-desc<?php echo($id); ?>-<?php echo(NC_AUTH_MODERATE_ID); ?>">
			<dl class="clearfix">
				<dt>
					<?php echo __('Moderator');?>
				</dt>
				<dd>
					<?php echo __d('authority', 'An assistant in a grouproom. He/She is expected to help the head of the grouproom.');?>
				</dd>
			</dl>
		</li>
		<li id="authority-edit-desc<?php echo($id); ?>-<?php echo(NC_AUTH_GENERAL_ID); ?>" class="authority-even">
			<dl class="clearfix">
				<dt>
					<?php echo __('Common User');?>
				</dt>
				<dd>
					<?php echo __d('authority', 'A common user');?>
				</dd>
			</dl>
		</li>
		<li id="authority-edit-desc<?php echo($id); ?>-<?php echo(NC_AUTH_GUEST_ID); ?>">
			<dl class="clearfix">
				<dt>
					<?php echo __('Guest');?>
				</dt>
				<dd>
					<?php echo __d('authority', 'A guest user. The one with this authority can browse the information, but is not allowed to write or edit the information.');?>
				</dd>
			</dl>
		</li>
	</ul>
	<?php
		echo $this->Html->div('submit align-right',
			$this->Form->button(__('Next&gt;&gt;'), array('name' => 'next', 'class' => 'common-btn', 'type' => 'submit')).
			$this->Form->button(__('Cancel'), array('name' => 'cancel', 'class' => 'common-btn', 'type' => 'button',
				'data-ajax-inner' => '#'.$id, 'data-ajax-url' =>  $this->Html->url(array('action' => 'index', 'language' => $language))))
		);
		echo $this->element('hidden');
		echo $this->Form->hidden('on_next' , array('name' => 'on_next', 'value' => _ON));
		echo $this->Form->end();
	?>
	<script>
	$(function(){
		$.Authority.initEdit('<?php echo($id); ?>');
	});
	</script>
</div>