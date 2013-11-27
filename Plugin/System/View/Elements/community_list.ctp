<?php if($hasNext || $page > 1): ?>
<div class="nc-paginator-outer clearfix">

	<nav>
		<ul class="nc-paginator">
			<li class="prev">
			<?php if($page > 1): ?>
				<?php echo $this->Html->link(__('&lt;'), array('action' => 'community_list', 'page' => $page - 1), array('data-ajax' => '#communities'.$id, 'data-ajax-method' =>'inner', 'escape' => false)); ?>
			<?php else: ?>
				<?php echo __('&lt;'); ?>
			<?php endif; ?>
			</li>
			<li class="system-paginator-center">&nbsp;</li>
			<li class="next">
			<?php if($hasNext): ?>
				<?php echo $this->Html->link(__('&gt;'), array('action' => 'community_list', 'page' => $page + 1), array('data-ajax' => '#communities'.$id, 'data-ajax-method' =>'inner', 'escape' => false)); ?>
			<?php else: ?>
				<?php echo __('&gt;'); ?>
			<?php endif; ?>
			</li>
		</ul>
	</nav>

</div>
<?php endif; ?>
<?php
$name = 'ConfigRegist.first_startcommunity_id';
$attributes = array(
	'value' => $first_startcommunity_id,
);
echo $this->Form->selectRooms($name, $communities, $attributes);
?>
