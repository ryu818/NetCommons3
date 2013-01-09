<div id="<?php echo($id); ?>" class="authority-slider">
	<div class="authority-slider-labels-outer">
		<div class="authority-slider-label-outer" style="left: 0%;">
			<div class="authority-slider-label" title="<?php echo(__('Room Manager'));?>">
				<?php echo(__('Room Manager'));?>
			</div>
			<span class="authority-slider-line">
			</span>
		</div>
		<div class="authority-slider-label-outer" style="left: 50%;">
			<div class="authority-slider-label" title="<?php echo(__('Moderator'));?>">
				<?php echo(__('Moderator'));?>
			</div>
			<span class="authority-slider-line">
			</span>
		</div>
		<div class="authority-slider-label-outer" style="left: 100%;">
			<div class="authority-slider-label" title="<?php echo(__('Common User'));?>">
				<?php echo(__('Common User'));?>
			</div>
			<span class="authority-slider-line">
			</span>
		</div>
	</div>
<script>
$(function(){
	$.Common.sliderAuthority('<?php echo($id); ?>', <?php if(isset($input_selector)){echo("'".$input_selector."'");} else{echo 'null';} ?>, <?php if(isset($disable) && $disable){echo("true");} else{echo 'false';} ?>);
});
</script>
</div>