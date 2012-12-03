<div id="nc-pages-setting-dialog">
	<div class="nc-pages-setting-icon table_cell nc_popup_color">
		<?php
		$postfix = ($this->action == 'index') ? '-on' : '';
		echo $this->Html->link('', array('plugin' => 'page', 'controller' => 'page', 'action' => 'index'),
		array('title' => __d('page', 'Pages menu'), 'class' => 'nc-pages-menu-icon' . $postfix . ' nc_tooltip',
			'data-tooltip-desc' => __d('page', 'Displays the menu for displaying and editing the page list, the community.'), 'data-ajax-replace' => '#nc-pages-setting-dialog'));
		$postfix = ($this->action == 'favorite') ? '-on' : '';
		echo $this->Html->link('', array('plugin' => 'page', 'controller' => 'page', 'action' => 'favorite'),
		array('title' => __d('page', 'Pages favorite'), 'class' => 'nc-pages-favorite-icon' . $postfix . ' nc_tooltip',
			'data-tooltip-desc' => __d('page', 'I display the list of pages that oneself looks at well.'), 'data-ajax-replace' => '#nc-pages-setting-dialog'));
		$postfix = ($this->action == 'meta') ? '-on' : '';
		echo $this->Html->link('', array('plugin' => 'page', 'controller' => 'page', 'action' => 'meta'),
		array('title' => __d('page', 'Page info'), 'class' => 'nc-page-metas-icon' . $postfix . ' nc_tooltip',
			'data-tooltip-desc' => __d('page', 'I can edit the page title, the description of the page, the keyword.'), 'data-ajax-replace' => '#nc-pages-setting-dialog'));
		$postfix = ($this->action == 'theme') ? '-on' : '';
		echo $this->Html->link('', array('plugin' => 'page', 'controller' => 'page', 'action' => 'theme'),
		array('title' => __d('page', 'Page theme'), 'class' => 'nc-page-themes-icon' . $postfix . ' nc_tooltip',
			'data-tooltip-desc' => __d('page', 'I can change the design of the page.'), 'data-ajax-replace' => '#nc-pages-setting-dialog'));
		$postfix = ($this->action == 'style') ? '-on' : '';
		echo $this->Html->link('', array('plugin' => 'page', 'controller' => 'page', 'action' => 'style'),
		array('title' => __d('page', 'Page style'), 'class' => 'nc-page-styles-icon' . $postfix . ' nc_tooltip',
			'data-tooltip-desc' => __d('page', 'I can change the page style color, and font.'), 'data-ajax-replace' => '#nc-pages-setting-dialog'));
		$postfix = ($this->action == 'layout') ? '-on' : '';
		echo $this->Html->link('', array('plugin' => 'page', 'controller' => 'page', 'action' => 'layout'),
		array('title' => __d('page', 'Page layout'), 'class' => 'nc-page-layouts-icon' . $postfix . ' nc_tooltip',
			'data-tooltip-desc' => __d('page', 'I can switch the display and non-display of up, down, left, or right column.'), 'data-ajax-replace' => '#nc-pages-setting-dialog'));
		?>
	</div>
	<div class="table_cell nc_popup_color">
		<div id="nc-pages-setting-main">
			<?php echo($this->element($this->action, isset($element_params) ? $element_params : array())); ?>
		</div>
	</div>
	<div id="nc-pages-setting-arrow-outer" class="table_cell">
		<div class="nc-pages-setting-arrow nc_arrow_left"></div>
	</div>
	<?php
		echo $this->Html->css('Page.index');
		echo $this->Html->script('Page.index');
	?>
	<script>
	$('#nc-pages-setting-dialog').Page();
	</script>
</div>