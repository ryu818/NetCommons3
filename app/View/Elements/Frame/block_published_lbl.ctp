<?php if($block['Block']['display_flag'] == NC_DISPLAY_FLAG_OFF): ?>
	<span class="nonpublic-lbl"><?php echo(__('(Private)')); ?></span>
<?php elseif(isset($block['Content']['display_flag']) && $block['Content']['display_flag'] == NC_DISPLAY_FLAG_OFF): ?>
	<span class="nonpublic-lbl"><?php echo(__('(Private content)')); ?></span>
<?php endif; ?>