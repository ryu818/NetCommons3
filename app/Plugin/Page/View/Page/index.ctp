<div id="nc_pages_setting_dialog">
	<div class="nc_pages_setting_icon table_cell nc_popup_color">
		<?php 
		$postfix = ($this->action == 'index') ? '_on' : '';
		echo $this->Html->link('', array('plugin' => 'page', 'controller' => 'page', 'action' => 'index'), 
		array('title' => __d('page', 'Pages menu'), 'class' => 'nc_pages_menu_icon' . $postfix . ' nc_tooltip', 
			'data-powertip' => __d('page', 'Displays the menu for displaying and editing the page list, the community.'), 'data-ajax-replace' => '#nc_pages_setting_dialog'));
		$postfix = ($this->action == 'favorite') ? '_on' : '';
		echo $this->Html->link('', array('plugin' => 'page', 'controller' => 'page', 'action' => 'favorite'), 
		array('title' => __d('page', 'Pages favorite'), 'class' => 'nc_pages_favorite_icon' . $postfix . ' nc_tooltip', 
			'data-powertip' => __d('page', 'I display the list of pages that oneself looks at well.'), 'data-ajax-replace' => '#nc_pages_setting_dialog'));
		$postfix = ($this->action == 'meta') ? '_on' : '';
		echo $this->Html->link('', array('plugin' => 'page', 'controller' => 'page', 'action' => 'meta'), 
		array('title' => __d('page', 'Page info'), 'class' => 'nc_page_metas_icon' . $postfix . ' nc_tooltip', 
			'data-powertip' => __d('page', 'I can edit the page title, the description of the page, the keyword.'), 'data-ajax-replace' => '#nc_pages_setting_dialog'));
		$postfix = ($this->action == 'theme') ? '_on' : '';
		echo $this->Html->link('', array('plugin' => 'page', 'controller' => 'page', 'action' => 'theme'), 
		array('title' => __d('page', 'Page theme'), 'class' => 'nc_page_themes_icon' . $postfix . ' nc_tooltip', 
			'data-powertip' => __d('page', 'I can change the design of the page.'), 'data-ajax-replace' => '#nc_pages_setting_dialog'));
		$postfix = ($this->action == 'style') ? '_on' : '';
		echo $this->Html->link('', array('plugin' => 'page', 'controller' => 'page', 'action' => 'style'), 
		array('title' => __d('page', 'Page style'), 'class' => 'nc_page_styles_icon' . $postfix . ' nc_tooltip', 
			'data-powertip' => __d('page', 'I can change the page style color, and font.'), 'data-ajax-replace' => '#nc_pages_setting_dialog'));
		$postfix = ($this->action == 'layout') ? '_on' : '';
		echo $this->Html->link('', array('plugin' => 'page', 'controller' => 'page', 'action' => 'layout'), 
		array('title' => __d('page', 'Page layout'), 'class' => 'nc_page_layouts_icon' . $postfix . ' nc_tooltip', 
			'data-powertip' => __d('page', 'I can switch the display and non-display of up, down, left, or right column.'), 'data-ajax-replace' => '#nc_pages_setting_dialog'));
		?>
	</div>
	<div class="table_cell nc_popup_color">
		<div id="nc_pages_setting_main">
			<?php echo($this->element($this->action, isset($element_params) ? $element_params : array())); ?>
		</div>
	</div>
	<div id="nc_pages_setting_arrow_outer" class="table_cell">
		<div class="nc_pages_setting_arrow nc_arrow_left"></div>
	</div>
	<?php
		echo $this->Html->css('Page.index');
		echo $this->Html->script('Page.index');
	?>
	<script>
	$('#nc_pages_setting_dialog').Page();
	</script>
</div>