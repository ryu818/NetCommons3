<?php
	if(isset($options['disable']) && $options['disable']) {
		$disable = true;
	} else {
		$disable = false;
	}
	unset($options['disable']);
	echo $this->Form->hidden($fieldName , $options);
	$domId = $this->Form->domId($options);
	$id = $domId['id'];
?>
<div id="<?php echo($id); ?>slider" class="authority-slider">
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
</div>

<script>
$(function(){
	$.Common.sliderAuthority('<?php echo($id); ?>', <?php if($disable){echo("true");} else{echo 'false';} ?>);
});
</script>