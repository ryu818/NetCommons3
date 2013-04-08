<?php if($block['Block']['display_flag'] == NC_DISPLAY_FLAG_OFF): ?>
	<span class="nonpublic-lbl"><?php echo(__('(Private)')); ?></span>
<?php elseif($block['Content']['display_flag'] == NC_DISPLAY_FLAG_OFF): ?>
	<span class="nonpublic-lbl"><?php echo(__('(Private content)')); ?></span>
<?php endif; ?>