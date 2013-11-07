<?php
if(!isset($views)) {
	$views = !empty($this->request->named['views']) ? intval($this->request->named['views']) : NC_PAGINATE_VIEWS;
}
$moveParams = array('tag' => 'li', 'escape' => false);
$numbersParams = array('ellipsis' => '<li>...</li>','last'=>1,'tag' => 'li', 'separator' => '', 'modulus' => $views);
//$currentParams = array('class' => 'display-none');
if(isset($options)) {
	if(isset($options['url'])) {
		$options['url'] = array_merge($this->Paginator->options['url'], $options['url']);
	}
	$this->Paginator->options($options);
}
$hasPage = $this->Paginator->hasPage(null, 2);
$before = $this->fetch('paginator_content_before');
$after = $this->fetch('paginator_content_after');
?>
<?php if($hasPage || isset($before) || isset($after)): ?>
<div class="nc-paginator-outer clearfix">
	<?php echo $this->fetch('paginator_content_before'); ?>
	<?php if($hasPage): ?>
	<nav>
		<ul class="nc-paginator">
			<?php echo $this->Paginator->prev(__('&lt;'), $moveParams); ?>
			<?php echo $this->Paginator->numbers($numbersParams); ?>
			<?php echo $this->Paginator->next(__('&gt;'), $moveParams); ?>
		</ul>
	</nav>
	<?php endif; ?>
	<?php /* カレント再表示用:未使用echo $this->Paginator->link("current", null, $currentParams) */  ?>
	<?php echo $this->fetch('paginator_content_after'); ?>
</div>
<?php endif; ?>