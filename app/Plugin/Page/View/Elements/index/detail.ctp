<?php
	$is_display = false;
	if(($page['Page']['space_type'] == NC_SPACE_TYPE_GROUP && $page['Page']['thread_num'] == 1) || $page['Page']['display_sequence'] != 1) {
		// コミュニティー以外のTopNodeでもなく、各ノードのトップページでなければ。
		$is_display = true;
	}
?>
<div class="pages-menu-edit-outer">
<fieldset class="form">
	<ul class="lists">
	<li>
		<dl>
			<dt>
				<label for="pages-menu-edit-permalink-<?php echo($page['Page']['id']);?>">
					<?php echo(__('Permalink'));?>
				</label>
			</dt>
			<dd>
				<div class="bold">
					<?php
						$permalink_prefix = '/';	//$this->webroot;
							$parent_permalink = '';
							if($page['Page']['space_type'] == NC_SPACE_TYPE_MYPORTAL)
								$permalink_prefix .= NC_SPACE_MYPORTAL_PREFIX.'/';
							else if($page['Page']['space_type'] == NC_SPACE_TYPE_PRIVATE)
								$permalink_prefix .= NC_SPACE_PRIVATE_PREFIX.'/';
							else if($page['Page']['space_type'] == NC_SPACE_TYPE_GROUP)
								$permalink_prefix .= NC_SPACE_GROUP_PREFIX.'/';
							if(!empty($parent_page['Page']['permalink'])) {
								$parent_permalink = h($parent_page['Page']['permalink']);
								$permalink_prefix .= $parent_permalink.'/';
							}
						?>
					<?php echo($permalink_prefix); ?>
				</div>
				<?php if(($page['Page']['space_type'] == NC_SPACE_TYPE_GROUP && $page['Page']['thread_num'] == 1) || $page['Page']['display_sequence'] != 1): ?>
					<?php
						$permalink_arr = explode('/', trim($page['Page']['permalink'], '/'));
						if(count($permalink_arr) > 0) {
							$page['Page']['permalink'] = $permalink_arr[count($permalink_arr) - 1];
						} else {
							$page['Page']['permalink'] = '';
						}
						$settings = array(
							'id' => "pages-menu-edit-permalink-".$page['Page']['id'],
							'value' => $page['Page']['permalink'],
							'label' => false,
							'div' => false,
							'maxlength' => 50,
							'size' => 25,
						);
						if(!empty($is_child)) {
							$settings['error'] = false;
						} else {
							$settings['error'] = array('attributes' => array(
								'selector' => $this->Js->escape("$('[name=data\\[Page\\]\\[permalink\\]]', $('#PagesMenuForm-".$page['Page']['id']."'))")
							));
						}
						echo $this->Form->input('Page.permalink', $settings);
					?>
				<?php else: ?>
					<input name="data[Page][permalink]" type="hidden" value="<?php echo(h($page['Page']['permalink'])); ?>" />
					<?php echo($this->Form->error('Page.permalink')); ?>
				<?php endif; ?>

			</dd>
		</dl>
	</li>
	<?php if($is_display): ?>
	<?php
		$locale = Configure::read(NC_SYSTEM_KEY.'.locale');
		if(isset($error_flag) && $error_flag) {
			$display_from_date = $page['Page']['display_from_date'];
		} else if(!empty($page['Page']['display_from_date'])) {
			$page['Page']['display_from_date'] = $this->TimeZone->date($page['Page']['display_from_date']);
			$display_from_date = date(__('Y-m-d H:i'), strtotime($page['Page']['display_from_date']));
		} else {
			$display_from_date = '';
		}

		if(isset($error_flag) && $error_flag) {
			$display_to_date = $page['Page']['display_to_date'];
		} else if(!empty($page['Page']['display_to_date'])) {
			$page['Page']['display_to_date'] = $this->Timezone->date($page['Page']['display_to_date']);
			$display_to_date = date(__('Y-m-d H:i'), strtotime($page['Page']['display_to_date']));
		} else {
			$display_to_date = '';
		}
	?>
	<li>
		<dl>
			<dt>
				<label for="pages-menu-edit-display-from-date-<?php echo($page['Page']['id']);?>">
					<?php echo(__('Published Date'));?>
				</label>
			</dt>
			<dd>
				<?php
					$settings = array(
						'id' => "pages-menu-edit-display-from-date-".$page['Page']['id'],
						'value' => $display_from_date,
						'label' => false,
						'div' => false,
						'class'  => 'nc-datetime',
						'maxlength' => 16,
						'size' => 15,
						'type' => 'text'
					);
					if($page['Page']['display_flag']) {
						$settings['disabled'] = 'disabled';
					}
					if(isset($is_child)) {
						$settings['error'] = false;
					} else {
						$settings['error'] = array('attributes' => array(
							'selector' => $this->Js->escape("$('[name=data\\[Page\\]\\[display_from_date\\]]', $('#PagesMenuForm-".$page['Page']['id']."'))")
						));
					}
					echo $this->Form->input('Page.display_from_date', $settings);
				?>
				<?php
					$settings = array(
						'id' => "pages-menu-edit-display-apply-subpage-".$page['Page']['id'],
						'value' => _ON,
						'checked' => ($page['Page']['display_apply_subpage']) ? 'checked' : false,
						'label' =>__d('page', 'Apply to the subpage.'),
						'type' => 'checkbox'
					);
					if($page['Page']['display_flag']) {
						$settings['disabled'] = 'disabled';
					}
					if(isset($is_child)) {
						$settings['error'] = false;
					}
					echo $this->Form->input('Page.display_apply_subpage', $settings);
				?>

				<div class="pages-menu-edit-display-to-date">
					<?php echo(__('&nbsp;-&nbsp;'));?>
				<?php
					$settings = array(
						'id' => "pages-menu-edit-display-to-date-".$page['Page']['id'],
						'value' => $display_to_date,
						'label' => false,
						'div' => false,
						'class'  => 'nc-datetime',
						'size' => 15,
						'maxlength' => 16,
						'type' => 'text'
					);
					if(isset($is_child)) {
						$settings['error'] = false;
					} else {
						$settings['error'] = array('attributes' => array(
							'selector' => $this->Js->escape("$('[name=data\\[Page\\]\\[display_to_date\\]]', $('#PagesMenuForm-".$page['Page']['id']."'))")
						));
					}
					echo $this->Form->input('Page.display_to_date', $settings);
				?>
				</div>
			</dd>
		</dl>
	</li>
	<?php endif; ?>
	</ul>
	<?php if(!isset($is_community) || !$is_community): ?>
	<div class="btn-bottom">
		<input type="submit" class="common-btn" name="ok" value="<?php echo( __('Ok')); ?>" />
		<input type="button" class="common-btn" name="cancel" value="<?php echo(__('Cancel')); ?>" onclick="$('#pages-menu-edit-detail-<?php echo($page['Page']['id']);?>').slideUp(300);" />
	</div>
	<?php endif; ?>
</fieldset>
</div>
<?php if($is_display): ?>
<?php
	echo $this->Html->css(array('plugins/jquery-ui-timepicker-addon.css'));
	echo $this->Html->script(array('plugins/jquery-ui-timepicker-addon.js', 'locale/'.$locale.'/plugins/jquery-ui-timepicker.js'));
?>
<script>
$(function(){
	$('#pages-menu-edit-display-from-date-<?php echo($page['Page']['id']);?>').datetimepicker();
	$('#pages-menu-edit-display-to-date-<?php echo($page['Page']['id']);?>').datetimepicker();
	$.PageMenu.pageDetailInit(<?php echo($page['Page']['id']); ?>,"<?php echo(str_replace("\\", "\\\\\\", NC_PERMALINK_CONTENT)); ?>", "<?php echo(NC_PERMALINK_PROHIBITION_REPLACE); ?>");
});
</script>
<?php endif; ?>