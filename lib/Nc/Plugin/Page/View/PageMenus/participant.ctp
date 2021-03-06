<?php
/**
 * ページメニュー：参加者修正画面
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.Page.View
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
?>
<div id="pages-menu-edit-participant-<?php echo($page['Page']['id']);?>" class="pages-menu-edit-view pages-menu-edit-participant-outer">
<div class="pages-menu-edit-participant">
	<div class="bold">
		<?php
			$form_params = array('id' => 'pages-menu-edit-participant-form-'.$page['Page']['id'],
					'class' => 'pages-menu-edit-form', 'data-ajax' => '#pages-menu-edit-item-'.$page['Page']['id'],'data-name' => 'participant');
			if($page['Page']['id'] != $page['Page']['room_id']) {
				echo(__d('page', 'Add members'));
				$form_params['data-ajax-confirm'] = h(__d('page','Set a new participant to [%s]. Are you sure?',$page['Page']['page_name']));
			} else {
				echo(__d('page', 'Edit members'));
			}
		?>
	</div>
	<div class="nc-top-description">
		<?php echo(__d('page', 'Set the roles of the room members, and press [Ok] button. To set the roles all at once, press [Select All] button.'));?>
	</div>

	<div class="pages-menu-search-outer">
	<?php
		echo $this->Form->button(__('Search for the members'), array('name' => 'search', 'class' => 'nc-common-btn',
			'type' => 'button',
			'data-ajax' => "#pages-menu-user-search",
			'data-ajax-dialog' => true,
			'data-ajax-dialog-options' => h('{"title" : "'.$this->Js->escape(__('Search for the members')).'","modal" : true, "resizable": true, "width":"800"}'),
			'data-ajax-effect' => 'fold',
			'data-ajax-url' => $this->Html->url(array('plugin' => 'user', 'controller' => 'user', 'action' => 'search', 'post_page_id' => $page['Page']['id'])),
		));
	?>
	</div>
	<?php echo $this->Form->create(null, $form_params); ?>
	<?php echo $this->Form->error('PageUserLink.authority_id'); ?>
	<table id="pages-menu-edit-participant-grid-<?php echo($page['Page']['id']);?>" style="display:none;">
	</table>
	<?php
		if($page['Page']['id'] == $page['Page']['room_id'] && $page['Page']['thread_num'] > 1) {
			$deallocation = $this->Form->button(__d('page', 'Unassign members'), array('name' => 'deallocation', 'class' => 'nc-common-btn nc-button-blue', 'type' => 'button',
				'data-ajax-url' => $this->Html->url(array('plugin' => 'page', 'controller' => 'page_menus', 'action' => 'deallocation', $page['Page']['id'])),
				'data-ajax' => '#pages-menu-edit-item-'.$page['Page']['id'],
				'data-ajax-data' => h('{"token": "'.$this->params['_Token']['key'].'"}'),	// JSONのエラーとなるためh関数を用いてエスケープ
				'data-ajax-confirm' => h(__d('page','Unassign members of [%s]. Are you sure?',$page['Page']['page_name'])),'data-ajax-type' => 'post'
			));
		} else {
			$deallocation = '';
		}
		echo $this->Html->div('submit',
			$this->Form->button(__('Ok'), array('name' => 'ok', 'class' => 'nc-common-btn')).
			$this->Form->button(__('Cancel'), array('name' => 'cancel', 'class' => 'nc-common-btn', 'type' => 'button',
				'onclick' => "$.PageMenu.hideDetail(".$page['Page']['id'].");", 'data-ajax-url' => $this->Html->url(array('plugin' => 'page', 'controller' => 'page_menus', 'action' => 'participant_cancel', $page['Page']['id'])),
				'data-ajax' => '#pages-menu-edit-participant-tmp', 'data-ajax-method' => 'inner', 'data-ajax-callback' => 'return false;')).$deallocation
		);
		if(isset($this->request->data['isSearch']) && $this->request->data['isSearch']) {
			echo $this->Form->hiddenVars('User', array(), false);
			echo $this->Form->hiddenVars('UserItemLink', array(), false);
		}
		echo $this->Form->end();
	?>
</div>
<?php
echo $this->Html->css('plugins/flexigrid', null, array('inline' => true));
echo $this->Html->script('plugins/flexigrid', array('inline' => true));
?>
</div>
<script>
$(function(){
	$("#pages-menu-edit-participant-grid-<?php echo($page['Page']['id']);?>").flexigrid
    (
        {
            url: '<?php echo($this->Html->url(array('plugin' => 'page', 'controller' => 'page_menus', 'action' => 'participant_detail', $page['Page']['id']))); ?>',
            method: 'POST',
            dataType: 'json',
            showToggleBtn: false,
            colModel :
            [
                {display: __d('pages', 'Room members'), name : 'handle', width: 140, height: 44, sortable : true, align: 'left' },
                {display: '<?php echo($this->element('index/auth_list', array('key' => 0, 'auth' => $auth_list[NC_AUTH_CHIEF],   'user_id' => '0', 'selauth'=> true,  'radio'=> false, 'all_selected' => true, 'authority_id' => NC_AUTH_CHIEF_ID)));?>', name : 'chief', width: 120, sortable : true, align: 'center'  },
                {display: '<?php echo($this->element('index/auth_list', array('key' => 0, 'auth' => $auth_list[NC_AUTH_MODERATE],'user_id' => '0', 'selauth'=> true,  'radio'=> false, 'all_selected' => true, 'authority_id' => NC_AUTH_MODERATE_ID)));?>', name : 'moderator', width: 120, sortable : false, align: 'center'  },
                {display: '<?php echo($this->element('index/auth_list', array('key' => 0, 'auth' => $auth_list[NC_AUTH_GENERAL], 'user_id' => '0', 'selauth'=> true,  'radio'=> false, 'all_selected' => true, 'authority_id' => NC_AUTH_GENERAL_ID)));?>', name : 'general', width: 120, sortable : false, align: 'center'  },
                {display: '<?php echo($this->element('index/auth_list', array('key' => 0, 'auth' => $auth_list[NC_AUTH_GUEST],   'user_id' => '0', 'selauth'=> false, 'radio'=> false, 'all_selected' => true, 'authority_id' => NC_AUTH_GUEST_ID)));?>', name : 'guest', width: 120, sortable : false, align: 'center'  }
                <?php if($page['Page']['space_type'] != NC_SPACE_TYPE_PUBLIC): ?>
                ,{display: '<?php echo($this->element('index/auth_list', array('key' => 0, 'auth' => $auth_list[NC_AUTH_OTHER],   'user_id' => '0', 'selauth'=> false, 'radio'=> false, 'all_selected' => true, 'authority_id' => NC_AUTH_OTHER_ID)));?>', name : 'none', width: 120, sortable : false, align: 'center'  }
                <?php endif; ?>
            ],
            sortname: "chief",
            sortorder: "desc",
            usepager: true,
            // useRp: true,
            rpOptions: <?php echo PAGES_PARTICIPANT_LIMIT_SELECT; ?>,
            rp: <?php echo PAGES_PARTICIPANT_LIMIT_DEFAULT; ?>,
            width: '810',
            height: 'auto',
            singleSelect: true,
            resizable : false,
            setParams : function() {
        		var fields = $(":input", $("#pages-menu-edit-participant-form-<?php echo($page['Page']['id']);?>")).serializeArray();
        		return fields;
        	},
            onSuccess : function() {

        	}
        }
    );
});
</script>