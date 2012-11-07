<div id="nc_pages_setting_dialog">
	<div class="nc_pages_setting_icon table_cell nc_popup_color">
		<?php echo $this->Html->link('', array('plugin' => 'page', 'controller' => 'page', 'action' => 'index'), 
		array('title' => __d('page', 'Pages menu'), 'class' => 'nc_pages_menu_icon nc_tooltip', 'data-ajax-replace' => '#nc_pages_setting_dialog')); ?>
		
		<?php echo $this->Html->link('', array('plugin' => 'page', 'controller' => 'page', 'action' => 'history'), 
		array('title' => __d('page', 'Pages history'), 'class' => 'nc_pages_history_icon nc_tooltip', 'data-ajax-replace' => '#nc_pages_setting_dialog')); ?>
		
		
		<?php echo $this->Html->link('', array('plugin' => 'page', 'controller' => 'page', 'action' => 'meta'), 
		array('title' => __d('page', 'Page info'), 'class' => 'nc_page_metas_icon nc_tooltip', 'data-ajax-replace' => '#nc_pages_setting_dialog')); ?>
		
		<?php echo $this->Html->link('', array('plugin' => 'page', 'controller' => 'page', 'action' => 'theme'), 
		array('title' => __d('page', 'Page theme'), 'class' => 'nc_page_themes_icon nc_tooltip', 'data-ajax-replace' => '#nc_pages_setting_dialog')); ?>
		
		<?php echo $this->Html->link('', array('plugin' => 'page', 'controller' => 'page', 'action' => 'style'), 
		array('title' => __d('page', 'Page style'), 'class' => 'nc_page_styles_icon nc_tooltip', 'data-ajax-replace' => '#nc_pages_setting_dialog')); ?>
		
		<?php echo $this->Html->link('', array('plugin' => 'page', 'controller' => 'page', 'action' => 'layout'), 
		array('title' => __d('page', 'Page layout'), 'class' => 'nc_page_layouts_icon nc_tooltip', 'data-ajax-replace' => '#nc_pages_setting_dialog')); ?>
	</div>
	<div class="table_cell nc_popup_color">
		<div id="nc_pages_setting_main">
			<?php echo($this->element($this->action, isset($element_params) ? $element_params : array())); ?>
		</div>
	</div>
	<div class="nc_pages_setting_arrow_outer table_cell">
		<div class="nc_pages_setting_arrow nc_arrow_left"></div>
	</div>
	<?php
		echo $this->Html->script('Page.index');
		echo $this->Html->css('Page.index');
	?>
	<script>
	$('#nc_pages_setting_dialog').Page();
	</script>
</div>