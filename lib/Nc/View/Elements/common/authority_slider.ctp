<?php
	$sliderOptionsStr = '{';
	if(count($sliderOptions) > 0) {
		foreach($sliderOptions as $sliderKey => $sliderOption) {
			if($sliderOptionsStr != '{') {
				$sliderOptionsStr .= ',';
			}
			if(gettype($sliderOption) == 'boolean') {
				$sliderOption = ($sliderOption) ? 'true' : 'false';
			}
			$sliderOptionsStr .= $sliderKey .':'.$sliderOption;
		}
	}
	$sliderOptionsStr .= '}';

	$minAuthorityId = isset($options['min_authority_id']) ? intval($options['min_authority_id']) : NC_AUTH_GENERAL_ID;
	$maxAuthorityId = isset($options['max_authority_id']) ? intval($options['max_authority_id']) : NC_AUTH_CHIEF_ID;
	$otherLabel = isset($options['other_label']) ? $options['other_label'] : __('Not specified');
	$adminLabel = isset($options['administrator_label']) ? $options['administrator_label'] : __('Administrator');
	$sliderWidth = isset($options['width']) ? intval($options['width']) : 75;
	$labelStyle = '';
	if($sliderWidth > 75) {
		$labelStyle = ' style="margin-left:-'.(33 + ($sliderWidth - 75)/2).'px; width:'.(66 + ($sliderWidth - 75)).'px;"';
	}

	$leftArr = array();
	$count = $minAuthorityId - $maxAuthorityId;
	$addWidth = floor(100/$count);
	$width = 0;
	for($i = 0; $i <= $count; $i++) {
		if($i == 0) {
			$leftArr[] = 0;
		} else if($i == $count) {
			$leftArr[] = 100;
		} else {
			$width += $addWidth;
			$leftArr[] = $width;
		}
	}

	unset($options['max_authority_id']);
	unset($options['min_authority_id']);
	unset($options['other_label']);
	unset($options['administrator_label']);
	unset($options['width']);
	echo $this->Form->hidden($fieldName , $options);
	$domId = $this->Form->domId($options);
	$id = $domId['id'];
	$i = 0;

?>

<div id="<?php echo($id); ?>-slider" class="authority-slider" style="width:<?php echo (count($leftArr) - 1)*$sliderWidth; ?>px;">
	<div class="authority-slider-labels-outer">
		<?php for($j = $maxAuthorityId; $j <= $minAuthorityId; $j++): ?>
			<?php if ($j == NC_AUTH_OTHER_ID):?>
				<div class="authority-slider-label-outer" style="left: <?php echo($leftArr[$i]); ?>%;">
					<div class="authority-slider-label" title="<?php echo $otherLabel;?>"<?php echo $labelStyle; ?>>
						<?php echo $otherLabel;?>
					</div>
					<span class="authority-slider-line">
					</span>
				</div>
			<?php elseif ($j == NC_AUTH_ADMIN_ID):?>
				<div class="authority-slider-label-outer" style="left: <?php echo($leftArr[$i]); ?>%;">
					<div class="authority-slider-label" title="<?php echo($adminLabel);?>"<?php echo $labelStyle; ?>>
						<?php echo($adminLabel);?>
					</div>
					<span class="authority-slider-line">
					</span>
				</div>
			<?php elseif ($j == NC_AUTH_CHIEF_ID):?>
				<div class="authority-slider-label-outer" style="left: <?php echo($leftArr[$i]); ?>%;">
					<div class="authority-slider-label" title="<?php echo(__('Room Manager'));?>"<?php echo $labelStyle; ?>>
						<?php echo(__('Room Manager'));?>
					</div>
					<span class="authority-slider-line">
					</span>
				</div>
			<?php elseif ($j == NC_AUTH_MODERATE_ID):?>
				<div class="authority-slider-label-outer" style="left: <?php echo($leftArr[$i]); ?>%;">
					<div class="authority-slider-label" title="<?php echo(__('Moderator'));?>"<?php echo $labelStyle; ?>>
						<?php echo(__('Moderator'));?>
					</div>
					<span class="authority-slider-line">
					</span>
				</div>
			<?php elseif ($j == NC_AUTH_GENERAL_ID):?>
				<div class="authority-slider-label-outer" style="left: <?php echo($leftArr[$i]); ?>%;">
					<div class="authority-slider-label" title="<?php echo(__('Common User'));?>"<?php echo $labelStyle; ?>>
						<?php echo(__('Common User'));?>
					</div>
					<span class="authority-slider-line">
					</span>
				</div>
			<?php elseif ($j == NC_AUTH_GUEST_ID):?>
				<div class="authority-slider-label-outer" style="left: <?php echo($leftArr[$i]); ?>%;">
					<div class="authority-slider-label" title="<?php echo(__('Guest'));?>"<?php echo $labelStyle; ?>>
						<?php echo(__('Guest'));?>
					</div>
					<span class="authority-slider-line">
					</span>
				</div>
			<?php endif; ?>
			<?php $i++; ?>
		<?php endfor; ?>
	</div>
</div>

<script>
$(function(){
	$.Common.sliderAuthority('<?php echo($id); ?>', <?php echo $sliderOptionsStr; ?>, <?php echo $minAuthorityId ?>, <?php echo $maxAuthorityId ?>);
});
</script>