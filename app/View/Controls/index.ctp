<?php
echo ($this->Html->css('controls/index/'));
echo ($this->element('Dialogs/hmenu'));
?>
<div class="controls-panel-outer">
	<div class="controls-panel-header">
		<?php echo(__('Control panel')); ?>
	</div>
	<div id="controls-panel">
		<?php foreach ($modules as $module): ?>
			<?php $module_name = h($module['Module']['module_name']); ?>
			<div class="controls-system-icon">
				<a class="controls-system-link" href="<?php echo($this->Html->url('/').'active-controls/'.$module['Module']['controller_action']);?>" data-ajax="" data-ajax-dialog-id="_system<?php echo($module['Module']['id']); ?>" data-ajax-dialog-options='{"title" : "<?php echo($module_name); ?>","position": "mouse"}' data-ajax-effect="fold" data-ajax-dialog-class="controls-module <?php echo(Inflector::underscore($module['Module']['dir_name'])); ?>-dialog">
					<img src="<?php echo($this->webroot); ?><?php echo($module['Module']['dir_name']); ?>/img/<?php echo($module['Module']['module_icon']); ?>" alt="<?php echo($module_name); ?>" title="<?php echo($module_name); ?>" />
					<br />
					<div><?php echo($module_name); ?></div>
				</a>
			</div>
		<?php endforeach; ?>
	</div>
</div>
