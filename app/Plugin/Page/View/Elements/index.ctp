<?php /* ページメニュー */ ?>
<div class="nc_pages_setting_title nc_popup_color" data-pages-header="true">
	<?php echo(__d('page', 'Pages menu')); ?>
</div>
<div id="nc_pages_menu_tab">
	<ul data-pages-header="true">
		<li><a href="#nc_pages_menu_page"><span><?php echo(__d('page', 'Page'));?></span></a></li>
		<li><a href="#nc_pages_menu_community"><span><?php echo(__d('page', 'Community'));?></span></a></li>
	</ul>

	<div id="nc_pages_menu_page" class="nc_pages_setting_content">
		<?php
			$thread_num = 2;
			$display_sequence = 1;
		?>
		<ol class="dd-list">
			<?php if(!empty($pages[NC_SPACE_TYPE_PUBLIC])): ?>
				<?php $parent_id = $pages[NC_SPACE_TYPE_PUBLIC][1][1][1][0]['id']; ?>
				<?php echo($this->element('page', array('pages' => $pages, 'menus' => $pages[NC_SPACE_TYPE_PUBLIC][1][$thread_num][$parent_id], 'space_type' => NC_SPACE_TYPE_PUBLIC, 'page_id' => $page_id, 'root_sequence' => 1))); ?>
			<?php endif; ?>
			<?php if(!empty($pages[NC_SPACE_TYPE_MYPORTAL])): ?>
				<?php $parent_id = $pages[NC_SPACE_TYPE_MYPORTAL][1][1][2][0]['id'];?>
				<?php echo($this->element('page', array('pages' => $pages, 'menus' => $pages[NC_SPACE_TYPE_MYPORTAL][1][$thread_num][$parent_id], 'space_type' => NC_SPACE_TYPE_MYPORTAL, 'page_id' => $page_id, 'root_sequence' => 1))); ?>
			<?php endif; ?>
			<?php if(!empty($pages[NC_SPACE_TYPE_PRIVATE])): ?>
				<?php $parent_id = $pages[NC_SPACE_TYPE_PRIVATE][1][1][3][0]['id'];?>
				<?php echo($this->element('page', array('pages' => $pages, 'menus' => $pages[NC_SPACE_TYPE_PRIVATE][1][$thread_num][$parent_id], 'space_type' => NC_SPACE_TYPE_PRIVATE, 'page_id' => $page_id, 'root_sequence' => 1))); ?>
			<?php endif; ?>
		</ol>
	</div>
	<div id="nc_pages_menu_community" class="nc_pages_setting_content">
	</div>
</div>
<?php
	echo $this->Html->css(array('Page.index/index.css', 'plugins/jquery.nestable.css'));
	echo $this->Html->script(array('Page.index/index.js', 'plugins/jquery.nestable.js'));
?>
<script>
	$('.nc_pages_setting_content').PageMenu();
</script>