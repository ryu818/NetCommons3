<div id="nc_block_group" style="display:none;">
	<ul>
		<li title="<?php echo(__('You can be combined into one, the selected block together.If you can arrange the blocks to the next, I want to one cohesive block, I used.')); ?>" onclick="$(this).children(':first').get(0).onclick(event);return false;" class="nc_tooltip pointer_cursor nowrap">
			<a onclick="$.PagesBlock.addGrouping(event); return false;" href="#" id="nc_add_group" class="link">
				<img class="icon" alt="" src="<?php echo($this->Html->url('/img/dialogs/group/grouping.gif')); ?>" />&nbsp;
				<?php echo(__('Grouping'));?>
			</a>
		</li>
		<li title="<?php echo(__('I unblock the selected grouping.')); ?>" onclick="$(this).children(':first').get(0).onclick(event);return false;" class="nc_tooltip pointer_cursor nowrap">
			<a onclick="$.PagesBlock.cancelGrouping(event); return false;" href="#" id="nc_cancel_group" class="link">
				<img class="icon" alt="" src="<?php echo($this->Html->url('/img/dialogs/group/cancel_grouping.gif')); ?>" />&nbsp;
				<?php echo(__('Cancel grouping'));?>
			</a>
		</li>
	</ul>
</div>