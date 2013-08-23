<?php
	if(isset($options['disable']) && $options['disable']) {
		$disable = true;
	} else {
		$disable = false;
	}
	if(isset($options['display_guest']) && $options['display_guest']) {
		$display_guest = true;
		$leftArr = array(
			0,
			33,
			66,
			100
		);
	} else {
		$display_guest = false;
		$leftArr = array(
			0,
			50,
			100
		);
	}

	unset($options['disable']);
	echo $this->Form->hidden($fieldName , $options);
	$domId = $this->Form->domId($options);
	$id = $domId['id'];
?>
<div id="<?php echo($id); ?>slider" class="authority-slider<?php if($display_guest){echo(" authority-slider-display-guest");} ?>">
	<div class="authority-slider-labels-outer">
		<div class="authority-slider-label-outer" style="left: <?php echo($leftArr[0]); ?>%;">
			<div class="authority-slider-label" title="<?php echo(__('Room Manager'));?>">
				<?php echo(__('Room Manager'));?>
			</div>
			<span class="authority-slider-line">
			</span>
		</div>
		<div class="authority-slider-label-outer" style="left: <?php echo($leftArr[1]); ?>%;">
			<div class="authority-slider-label" title="<?php echo(__('Moderator'));?>">
				<?php echo(__('Moderator'));?>
			</div>
			<span class="authority-slider-line">
			</span>
		</div>
		<div class="authority-slider-label-outer" style="left: <?php echo($leftArr[2]); ?>%;">
			<div class="authority-slider-label" title="<?php echo(__('Common User'));?>">
				<?php echo(__('Common User'));?>
			</div>
			<span class="authority-slider-line">
			</span>
		</div>
		<?php if ($display_guest):?>
		<div class="authority-slider-label-outer" style="left: <?php echo($leftArr[3]); ?>%;">
			<div class="authority-slider-label" title="<?php echo(__('Guest'));?>">
				<?php echo(__('Guest'));?>
			</div>
			<span class="authority-slider-line">
			</span>
		</div>
		<?php endif; ?>
	</div>
</div>

<script>
$(function(){
	$.Common.sliderAuthority('<?php echo($id); ?>', <?php if($disable){echo("true");} else{echo 'false';} ?>, <?php if($display_guest){echo("true");} else{echo 'false';} ?>);
});
</script>