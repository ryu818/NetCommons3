<div id="nc_block_group" style="display:none;">
	<ul>
		<li onclick="$(this).children(':first').get(0).onclick(event);return false;" class="pointer_cursor nowrap">
			<a onclick="$.PagesBlock.addGrouping(event); return false;" href="#" id="nc_add_group" class="link">
				<img class="icon" alt="" src="<?php echo($this->Html->url('/img/dialogs/group/grouping.gif')); ?>" />&nbsp;
				<?php echo(__('Grouping'));?>
			</a>
		</li>
		<li onclick="$(this).children(':first').get(0).onclick(event);return false;" class="pointer_cursor nowrap">
			<a onclick="$.PagesBlock.cancelGrouping(event); return false;" href="#" id="nc_cancel_group" class="link">
				<img class="icon" alt="" src="<?php echo($this->Html->url('/img/dialogs/group/cancel_grouping.gif')); ?>" />&nbsp;
				<?php echo(__('Cancel grouping'));?>
			</a>
		</li>
	</ul>
</div>