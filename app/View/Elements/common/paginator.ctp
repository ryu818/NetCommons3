<?php
	$paginator_content_before = $this->fetch('paginator_content_before');
	$paginator_content_after = $this->fetch('paginator_content_after');
 ?>
<?php if($this->Paginator->hasPage(null, 2) || isset($paginator_content_before) || isset($paginator_content_after)): ?>
<?php
if(!isset($views)) {
	$views = !empty($this->request->named['views']) ? intval($this->request->named['views']) : NC_PAGINATE_VIEWS;
}
$move_params = array('tag' => 'li');
$numbers_params = array('ellipsis' => '<li>...</li>','last'=>1,'tag' => 'li', 'separator' => '', 'modulus' => $views);
$current_params = array('class' => 'display-none');
if(isset($add_params)) {
	$move_params = array_merge($move_params, $add_params);
	$numbers_params = array_merge($numbers_params, $add_params);
	$current_params = array_merge($current_params, $add_params);
}
?>
<div class="nc-paginator-outer clearfix">
	<?php echo $this->fetch('paginator_content_before'); ?>
	<?php if($this->Paginator->hasPage(null, 2)): ?>
	<ul class="nc-paginator">
		<?php echo $this->Paginator->prev(__('<'), $move_params); ?>
		<?php echo $this->Paginator->numbers($numbers_params); ?>
		<?php echo $this->Paginator->next(__('>'), $move_params); ?>
	</ul>
	<?php endif; ?>
	<?php /* カレント再表示用 */ echo $this->Paginator->link("current", null, $current_params) ?>
	<?php echo $this->fetch('paginator_content_after'); ?>
</div>
<?php endif; ?>