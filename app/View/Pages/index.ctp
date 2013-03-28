<?php
// TODO:php部分はcontrollerで行ったほうがよい個所もある
	$nc_user = $this->Session->read(NC_AUTH_KEY.'.'.'User');
	$nc_mode = $this->Session->read(NC_SYSTEM_KEY.'.'.'mode');

	if(isset($page_id_arr[1]) && $page_id_arr[1] != 0 && (isset($blocks[$page_id_arr[1]]) || $nc_mode == NC_BLOCK_MODE)) {
		// headercolumn
		$headercolumn_str = $this->element('Pages/column', array('blocks' => isset($blocks[$page_id_arr[1]]) ? $blocks[$page_id_arr[1]] : null, 'page' => $pages[$page_id_arr[1]], 'parent_id' => 0));
	}
	if(isset($page_id_arr[2]) && $page_id_arr[2] != 0 && (isset($blocks[$page_id_arr[2]]) || $nc_mode == NC_BLOCK_MODE)) {
		// leftcolumn
		$leftcolumn_str = $this->element('Pages/column', array('blocks' => isset($blocks[$page_id_arr[2]]) ? $blocks[$page_id_arr[2]] : null, 'page' => $pages[$page_id_arr[2]], 'parent_id' => 0));
	}

	if(isset($blocks[$page_id_arr[0]]) || $nc_mode == NC_BLOCK_MODE) {
		// centercolumn
		$centercolumn_str = $this->element('Pages/column', array('blocks' => isset($blocks[$page_id_arr[0]]) ? $blocks[$page_id_arr[0]] : null, 'page' => $pages[$page_id_arr[0]], 'parent_id' => 0));

	}

	if(isset($page_id_arr[3]) && $page_id_arr[3] != 0 && (isset($blocks[$page_id_arr[3]]) || $nc_mode == NC_BLOCK_MODE)) {
		// rightcolumn
		$rightcolumn_str = $this->element('Pages/column', array('blocks' => isset($blocks[$page_id_arr[3]]) ? $blocks[$page_id_arr[3]] : null, 'page' => $pages[$page_id_arr[3]], 'parent_id' => 0));

	}

	if(isset($page_id_arr[4]) && $page_id_arr[4] != 0 && (isset($blocks[$page_id_arr[4]]) || $nc_mode == NC_BLOCK_MODE)) {
		// footercolumn
		$footercolumn_str = $this->element('Pages/column', array('blocks' => isset($blocks[$page_id_arr[4]]) ? $blocks[$page_id_arr[4]] : null, 'page' => $pages[$page_id_arr[4]], 'parent_id' => 0));
	}
	$page_style = Configure::read(NC_SYSTEM_KEY.'.'.'Page_Style');

	$style = '';
	$left_style = '';
	$right_style = '';
	$header_style = '';
	$footer_style = '';
	if(!empty($page_style['PageStyle']['left_margin'])) {
		$style .= 'margin-left:'.intval($page_style['PageStyle']['left_margin']).'px;';
	}
	if(!empty($page_style['PageStyle']['right_margin'])) {
		$style .= 'margin-right:'.intval($page_style['PageStyle']['right_margin']).'px;';
	}
	if(!empty($page_style['PageStyle']['top_margin'])) {
		$style .= 'margin-top:'.intval($page_style['PageStyle']['top_margin']).'px;';
	}
	if(!empty($page_style['PageStyle']['bottom_margin'])) {
		$style .= 'margin-bottom:'.intval($page_style['PageStyle']['bottom_margin']).'px;';
	}
	if(!empty($page_style['PageStyle']['min_width_size'])) {
		switch($page_style['PageStyle']['min_width_size']) {
			case -1:
				$style .= 'width:100%;';
				$left_style .= 'width:10%;';
				$right_style .= 'width:10%;';
				break;
			case 0:
				break;
			default:
				$style .= 'width:'.intval($page_style['PageStyle']['min_width_size']).'px;';
				$left_style .= 'width:10%;';
				$right_style .= 'width:10%;';
		}
	}
	if(!empty($page_style['PageStyle']['min_height_size'])) {
		switch($page_style['PageStyle']['min_height_size']) {
			case -1:
				$style .= 'height:100%;';
				$header_style .= 'height:10%;';
				$footer_style .= 'height:10%;';
				break;
			case 0:
				break;
			default:
				$style .= 'height:'.intval($page_style['PageStyle']['min_height_size']).'px;';
				$header_style .= 'height:10%;';
				$footer_style .= 'height:10%;';
		}
	}
	//$page_style['PageStyle']['align'] = "center";
	//$style .= "margin:0 auto;"
	if($nc_mode == NC_BLOCK_MODE) {
		/* グループ */
		echo($this->element('Dialogs/group'));
		/* リサイズ */
		echo('<div id="nc-block-show-size" class="display-none"></div>');
	}
?>
<?php if(!empty($nc_user['id']) || Configure::read(NC_CONFIG_KEY.'.'.'display_header_menu') != NC_HEADER_MENU_NONE) {
	echo($this->element('Dialogs/hmenu', array('hierarchy' => isset($pages[$page_id_arr[0]]['Authority']['hierarchy']) ? $pages[$page_id_arr[0]]['Authority']['hierarchy'] : NC_AUTH_OTHER)));
}?>
<div id="container">
	<div id="main-container" class="table"<?php if($style != ''): ?> style="<?php echo($style); ?>"<?php endif; ?>>
	<?php if(isset($headercolumn_str)): ?>
	<?php if($nc_mode == NC_BLOCK_MODE && isset($pages[$page_id_arr[1]]) && $pages[$page_id_arr[1]]['Authority']['hierarchy'] >= NC_AUTH_MIN_CHIEF): ?>
	<div class="table-row" data-add-columns='headercolumn'>
		<?php echo($this->element('Pages/add_block', array('id' => 'nc-add-block-headercolumn', 'add_modules' => $add_modules[$pages[$page_id_arr[1]]['Page']['room_id']], 'copy_content' => $copy_content))); ?>
	</div>
	<?php endif; ?>
	<header id="headercolumn" class="nc-columns table-row"<?php if($header_style != ''): ?> style="<?php echo($header_style); ?>"<?php endif; ?> data-page='<?php echo($page_id_arr[1]); ?>' data-columns='top' data-show-count='<?php echo($pages[$page_id_arr[1]]['Page']['show_count']); ?>'>
		<?php echo($headercolumn_str); ?>
	</header>
	<?php endif; ?>
	<div class="table">
		<?php if(isset($leftcolumn_str)): ?>
		<div id="leftcolumn" class="nc-columns table-cell"<?php if($left_style != ''): ?> style="<?php echo($left_style); ?>"<?php endif; ?> data-page='<?php echo($page_id_arr[2]); ?>' data-columns='top' data-show-count='<?php echo($pages[$page_id_arr[2]]['Page']['show_count']); ?>'>
			<?php if($nc_mode == NC_BLOCK_MODE && isset($pages[$page_id_arr[2]]) && $pages[$page_id_arr[2]]['Authority']['hierarchy'] >= NC_AUTH_MIN_CHIEF): ?>
			<div class="table">
				<?php echo($this->element('Pages/add_block', array('id' => 'nc-add-block-leftcolumn', 'add_modules' => $add_modules[$pages[$page_id_arr[2]]['Page']['room_id']], 'copy_content' => $copy_content))); ?>
			</div>
			<?php endif; ?>
			<?php if(isset($blocks[$page_id_arr[2]]) || $nc_mode == NC_BLOCK_MODE): ?>
				<?php echo($leftcolumn_str); ?>
			<?php endif; ?>
		</div>
		<?php endif; ?>
		<div id="centercolumn" class="nc-columns table-cell" data-columns='top' data-page='<?php echo($page_id_arr[0]); ?>' data-show-count='<?php echo($pages[$page_id_arr[0]]['Page']['show_count']); ?>'>
			<?php if($nc_mode == NC_BLOCK_MODE && isset($pages[$page_id_arr[0]]) && $pages[$page_id_arr[0]]['Authority']['hierarchy'] >= NC_AUTH_MIN_CHIEF): ?>
			<div class="table">
				<?php echo($this->element('Pages/add_block', array('id' => 'nc-add-block-centercolumn', 'add_modules' => $add_modules[$pages[$page_id_arr[0]]['Page']['room_id']], 'copy_content' => $copy_content))); ?>
			</div>
			<?php endif; ?>
			<?php if(isset($centercolumn_str)): ?>
				<?php echo($centercolumn_str); ?>
			<?php endif; ?>
		</div>
		<?php if(isset($rightcolumn_str)): ?>
		<div id="rightcolumn" class="nc-columns table-cell"<?php if($right_style != ''): ?> style="<?php echo($right_style); ?>"<?php endif; ?> data-page='<?php echo($page_id_arr[3]); ?>' data-columns='top' data-show-count='<?php echo($pages[$page_id_arr[3]]['Page']['show_count']); ?>'>
			<?php if($nc_mode == NC_BLOCK_MODE && isset($pages[$page_id_arr[3]]) && $pages[$page_id_arr[3]]['Authority']['hierarchy'] >= NC_AUTH_MIN_CHIEF): ?>
			<div class="table">
				<?php echo($this->element('Pages/add_block', array('id' => 'nc-add-block-rightcolumn', 'add_modules' => $add_modules[$pages[$page_id_arr[3]]['Page']['room_id']], 'copy_content' => $copy_content))); ?>
			</div>
			<?php endif; ?>
			<?php if(isset($blocks[$page_id_arr[3]]) || $nc_mode == NC_BLOCK_MODE): ?>
				<?php echo($rightcolumn_str); ?>
			<?php endif; ?>
		</div>
		<?php endif; ?>
	</div>
	<?php if(isset($footercolumn_str)): ?>
	<?php if($nc_mode == NC_BLOCK_MODE && isset($pages[$page_id_arr[4]]) && $pages[$page_id_arr[4]]['Authority']['hierarchy'] >= NC_AUTH_MIN_CHIEF): ?>
		<div class="table-row" data-add-columns='footercolumn'>
			<?php echo($this->element('Pages/add_block', array('id' => 'nc-add-block-footercolumn', 'add_modules' => $add_modules[$pages[$page_id_arr[4]]['Page']['room_id']], 'copy_content' => $copy_content))); ?>
		</div>
	<?php endif; ?>
	<footer id="footercolumn" class="nc-columns table-row"<?php if($footer_style != ''): ?> style="<?php echo($footer_style); ?>"<?php endif; ?> data-page='<?php echo($page_id_arr[4]); ?>' data-columns='top' data-show-count='<?php echo($pages[$page_id_arr[4]]['Page']['show_count']); ?>'>
		<?php echo($footercolumn_str); ?>
	</footer>
	<?php endif; ?>
	</div>
</div>