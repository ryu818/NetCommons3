<div class="pages-menu-edit-outer">
<fieldset class="form">
<ul class="lists">
	<li>
		<dl>
			<dt>
				<label for="pages-menu-edit-permalink-<?php echo($page['Page']['id']);?>">
					<?php echo(__('Permalink'));?>
					<span class="require"><?php echo(__('*')); ?></span>
				</label>
			</dt>
			<dd>
				<span class="bold">
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
				</span>
				<?php if($page['Page']['display_sequence'] != 1): ?>
					<input id="pages-menu-edit-permalink-<?php echo($page['Page']['id']);?>" type="text" name="data[Page][permalink]" maxlength="50" size="25" value="<?php echo(h($page['Page']['permalink'])); ?>" />
				<?php else: ?>
					<input name="data[Page][permalink]" type="hidden" value="<?php echo(h($page['Page']['permalink'])); ?>" />
				<?php endif; ?>
			</dd>
		</dl>
	</li>
	<?php
		////$catalog = Configure::read('locale');

			if(!empty($page['Page']['display_date'])) {
				$page['Page']['display_date'] = $timezone->date($page['Page']['display_date']);
				$display_date = date(__('Y-m-d', true), strtotime($page['Page']['display_date']));
				$display_time = date(__('g:ia', true), strtotime($page['Page']['display_date']));
			} else {
				$display_date = '';
				$display_time = '';
			}
			?>
	<?php if($page['Page']['display_sequence'] != 1 && (!isset($parent_page['Page']) || $parent_page['Page']['display_flag'] == NC_DISPLAY_FLAG_ON)): ?>
	<li>
		<dl>
			<dt>
				<label for="pages-menu-edit-display-date-<?php echo($page['Page']['id']);?>">
					<?php echo(__('Published Date'));?>
				</label>
			</dt>
			<dd>
				<?php if(!isset($parent_page['Page']) || $parent_page['Page']['display_flag'] == NC_DISPLAY_FLAG_ON): ?>
					<input id="pages-menu-edit-display-date-<?php echo($page['Page']['id']);?>" class="nc-date" type="text" name="data[Page][display_date]" value="<?php echo($display_date); ?>"  />
					<input id="pages-menu-edit-display-time-<?php echo($page['Page']['id']);?>" class="nc-time" type="text" name="data[Page][display_time]" value="<?php echo($display_time); ?>"  />



					<div style="padding:8px;">
					&nbsp;～&nbsp;<input id="pages-menu-edit-display-date-<?php echo($page['Page']['id']);?>" class="nc-date" type="text" name="data[Page][display_date]" value="<?php echo($display_date); ?>"  />
					<input id="pages-menu-edit-display-time-<?php echo($page['Page']['id']);?>" class="nc-time" type="text" name="data[Page][display_time]" value="<?php echo($display_time); ?>"  />
					</div>


				<?php else: ?>
					<?php echo($display_date.'&nbsp;&nbsp;'.$display_time); ?>
					<input name="data[Page][display_date]" type="hidden" value="<?php echo($display_date); ?>" />
					<input name="data[Page][display_time]" type="hidden" value="<?php echo($display_time); ?>" />
				<?php endif; ?>
			</dd>
		</dl>
	</li>
	<?php endif; ?>
	</ul>
	<div class="btn-bottom">
		<input type="submit" class="common_btn" name="ok" value="<?php echo( __('Ok')); ?>" />
		<input type="button" class="common_btn" name="cancel" value="<?php echo(__('Cancel')); ?>" onclick="$('#pages-menu-edit-detail-<?php echo($page['Page']['id']);?>').slideUp(300);" />
	</div>
</fieldset>
</div>