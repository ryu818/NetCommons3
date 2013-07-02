<?php
/**
 * 会員管理 会員編集->参加権限選択画面
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.User.View
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
?>
<div id="<?php echo $id ?>">
	<?php
		echo $this->Form->create('User', array('data-ajax' => '#'.$id, $user_id));
	?>
	<?php
		echo $this->Html->link(__d('user', 'Edit member info[%s]', $user['User']['handle']), array('action' => 'edit', $user_id), array('data-ajax' => '#'.$id, 'data-ajax-type' => 'post', 'data-ajax-serialize' => true, 'class' => 'bold'));
	?>
	&nbsp;>>&nbsp;
	<?php
		echo $this->Html->link(__d('user', 'Select Groups to join'), array('action' => 'select_group', $user_id), array('data-ajax' => '#'.$id, 'data-ajax-type' => 'post', 'data-ajax-serialize' => true, 'class' => 'bold'));
	?>
	&nbsp;>>&nbsp;
	<h3 class="bold display-inline">
		<?php echo __d('user', 'Set authority'); ?>
	</h3>
	<div class="top-description">
		<?php echo __d('user', 'Set the role of this member in each grouproom, and press [%1$s]to proceed.<br/>Remember, this member is a <span class=\'bold\'>%2$s.</span><br/>By pressing [%3$s], you can set the role of this member all at once.', __('Ok'), $user_authority_name,  __('Select All'));?>
	</div>

	<table id="user-room-list-<?php echo $user_id; ?>" class="user-room-list" summary="<?php echo __d('user', 'Display a list of rooms.'); ?>">
		<tbody>
		<?php foreach($page_user_links as $key => $pageUserLink): ?>
			<?php
				if($pageUserLink['Page']['space_type'] == NC_SPACE_TYPE_PUBLIC){
					$defaultEntryAuthorityId = Configure::read(NC_CONFIG_KEY.'.default_entry_public_authority_id');
				} else {
					$defaultEntryAuthorityId = Configure::read(NC_CONFIG_KEY.'.default_entry_group_authority_id');
				}
			?>
			<tr>
				<td class="<?php if($pageUserLink['Page']['space_type'] == NC_SPACE_TYPE_PUBLIC){ echo "user-auth-public";} else { echo "user-auth-community"; } ?>">
					<?php
						$nbsp = '';
						for ($i = 1; $i < $pageUserLink['Page']['thread_num']; $i++) {
							$nbsp .= '&nbsp;&nbsp;&nbsp;';
						}
						echo $nbsp.'<span title="'.h($pageUserLink['Page']['page_name']).'">'.h($pageUserLink['Page']['page_name'])."</span><input type=\"hidden\" name=\"data[PageUserLink][".$pageUserLink['PageUserLink']['room_id']."][room_id]\" value=\"".$pageUserLink['PageUserLink']['room_id']."\" />";
					 ?>
				</td>
				<td>
					<?php echo $this->element('select_auth/auth_list', array('auth' => $auth_list[NC_AUTH_CHIEF],   'room_id' => $pageUserLink['PageUserLink']['room_id'], 'prefix' => 'user-auth', 'selauth'=> true,  'radio'=> true, 'def_hierarchy' => NC_AUTH_CHIEF,    'def_authority_id' => NC_AUTH_CHIEF_ID, 'authority_id' => $pageUserLink['PageUserLink']['authority_id'], 'defaultEntryAuthorityId' => $defaultEntryAuthorityId)); ?>
				</td>
				<td>
					<?php echo $this->element('select_auth/auth_list', array('auth' => $auth_list[NC_AUTH_MODERATE],'room_id' => $pageUserLink['PageUserLink']['room_id'], 'prefix' => 'user-auth', 'selauth'=> true,  'radio'=> true, 'def_hierarchy' => NC_AUTH_MODERATE, 'def_authority_id' => NC_AUTH_MODERATE_ID, 'authority_id' => $pageUserLink['PageUserLink']['authority_id'], 'defaultEntryAuthorityId' => $defaultEntryAuthorityId)); ?>
				</td>
				<td>
					<?php echo $this->element('select_auth/auth_list', array('auth' => $auth_list[NC_AUTH_GENERAL], 'room_id' => $pageUserLink['PageUserLink']['room_id'], 'prefix' => 'user-auth', 'selauth'=> true,  'radio'=> true, 'def_hierarchy' => NC_AUTH_GENERAL,  'def_authority_id' => NC_AUTH_GENERAL_ID,  'authority_id' => $pageUserLink['PageUserLink']['authority_id'], 'defaultEntryAuthorityId' => $defaultEntryAuthorityId)); ?>
				</td>
				<td>
					<?php echo $this->element('select_auth/auth_list', array('auth' => $auth_list[NC_AUTH_GUEST],   'room_id' => $pageUserLink['PageUserLink']['room_id'], 'prefix' => 'user-auth', 'selauth'=> false, 'radio'=> true, 'def_hierarchy' => NC_AUTH_GUEST,    'def_authority_id' => NC_AUTH_GUEST_ID,    'authority_id' => $pageUserLink['PageUserLink']['authority_id'], 'defaultEntryAuthorityId' => $defaultEntryAuthorityId)); ?>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>

	<?php
		echo $this->Html->div('submit align-right',
			$this->Form->button(__('<<Back'), array('name' => 'back', 'class' => 'common-btn', 'type' => 'button', 'data-ajax' => '#'.$id, 'data-ajax-url' => $this->Html->url(array('action' => 'select_group', $user_id)), 'data-ajax-type' => 'post', 'data-ajax-serialize' => true)).
			$this->Form->button(__('Ok'), array('name' => 'next', 'class' => 'common-btn', 'type' => 'submit')).
			$this->Form->button(__('Cancel'), array('name' => 'cancel', 'class' => 'common-btn', 'type' => 'button', 'onclick' => '$.User.memberQuit('.$user_id.'); return false;'))
		);
		echo $this->Form->hidden('submit' , array('name' => 'submit', 'value' => _ON));
		echo $this->Form->end();
	?>
	<script>
	$(function(){
		$.User.selectAuthInit('<?php echo $id; ?>');
		$("#user-room-list-<?php echo $user_id; ?>").flexigrid
		(
			{
				showToggleBtn: false,
				colModel :
					[
						{display: '<?php echo(__d('user', 'Groups')); ?>', name : 'room', width: 140, height: 44, sortable : false, align: 'left' },
						{display: '<?php echo($this->element('select_auth/auth_list', array('auth' => $auth_list[NC_AUTH_CHIEF],   'room_id' => '0', 'selauth'=> true,  'radio'=> false, 'all_selected' => true, 'authority_id' => NC_AUTH_CHIEF_ID)));?>', name : 'chief', width: 120, sortable : true, align: 'center'  },
						{display: '<?php echo($this->element('select_auth/auth_list', array('auth' => $auth_list[NC_AUTH_MODERATE],'room_id' => '0', 'selauth'=> true,  'radio'=> false, 'all_selected' => true, 'authority_id' => NC_AUTH_MODERATE_ID)));?>', name : 'moderator', width: 120, sortable : false, align: 'center'  },
						{display: '<?php echo($this->element('select_auth/auth_list', array('auth' => $auth_list[NC_AUTH_GENERAL], 'room_id' => '0', 'selauth'=> true,  'radio'=> false, 'all_selected' => true, 'authority_id' => NC_AUTH_GENERAL_ID)));?>', name : 'general', width: 120, sortable : false, align: 'center'  },
						{display: '<?php echo($this->element('select_auth/auth_list', array('auth' => $auth_list[NC_AUTH_GUEST],   'room_id' => '0', 'selauth'=> false, 'radio'=> false, 'all_selected' => true, 'authority_id' => NC_AUTH_GUEST_ID)));?>', name : 'guest', width: 120, sortable : false, align: 'center'  }
					],
				width: '700',
				height: <?php if(count($page_user_links) > 10):?>'340'<?php else:?>'auto'<?php endif;?>,
				singleSelect: true,
				resizable : false
			}
		);
		<?php if(isset($is_success) && $is_success): ?>
			$.User.successSelectAuth(<?php echo $user_id; ?>, "<?php echo $this->Js->escape($this->Form->error('User.authority_id')); ?>");
		<?php endif; ?>
	});
	</script>
</div>
