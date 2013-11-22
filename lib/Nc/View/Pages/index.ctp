<?php
// TODO:php部分はcontrollerで行ったほうがよい個所もある
	$nc_user = $this->Session->read(NC_AUTH_KEY.'.'.'User');
	$ncMode = $this->Session->read(NC_SYSTEM_KEY.'.'.'mode');

	if(isset($page_id_arr[1]) && $page_id_arr[1] != 0 && (isset($blocks[$page_id_arr[1]]) || $ncMode == NC_BLOCK_MODE)) {
		// headercolumn
		$headercolumn_str = $this->element('Pages/column', array('column' => '/headercolumn', 'blocks' => isset($blocks[$page_id_arr[1]]) ? $blocks[$page_id_arr[1]] : null, 'page' => $pages[$page_id_arr[1]], 'parent_id' => 0));
	}
	if(isset($page_id_arr[2]) && $page_id_arr[2] != 0 && (isset($blocks[$page_id_arr[2]]) || $ncMode == NC_BLOCK_MODE)) {
		// leftcolumn
		$leftcolumn_str = $this->element('Pages/column', array('column' => '/leftcolumn', 'blocks' => isset($blocks[$page_id_arr[2]]) ? $blocks[$page_id_arr[2]] : null, 'page' => $pages[$page_id_arr[2]], 'parent_id' => 0));
	}

	if(isset($blocks[$page_id_arr[0]]) || $ncMode == NC_BLOCK_MODE) {
		// centercolumn
		$centercolumn_str = $this->element('Pages/column', array('column' => '/centercolumn', 'blocks' => isset($blocks[$page_id_arr[0]]) ? $blocks[$page_id_arr[0]] : null, 'page' => $pages[$page_id_arr[0]], 'parent_id' => 0));

	}

	if(isset($page_id_arr[3]) && $page_id_arr[3] != 0 && (isset($blocks[$page_id_arr[3]]) || $ncMode == NC_BLOCK_MODE)) {
		// rightcolumn
		$rightcolumn_str = $this->element('Pages/column', array('column' => '/rightcolumn', 'blocks' => isset($blocks[$page_id_arr[3]]) ? $blocks[$page_id_arr[3]] : null, 'page' => $pages[$page_id_arr[3]], 'parent_id' => 0));

	}

	if(isset($page_id_arr[4]) && $page_id_arr[4] != 0 && (isset($blocks[$page_id_arr[4]]) || $ncMode == NC_BLOCK_MODE)) {
		// footercolumn
		$footercolumn_str = $this->element('Pages/column', array('column' => '/footercolumn', 'blocks' => isset($blocks[$page_id_arr[4]]) ? $blocks[$page_id_arr[4]] : null, 'page' => $pages[$page_id_arr[4]], 'parent_id' => 0));
	}
	if($ncMode == NC_BLOCK_MODE) {
		/* グループ */
		echo($this->element('Dialogs/group'));
		/* リサイズ */
		echo('<div id="nc-block-show-size" class="display-none"></div>');
	}
	$column = empty($this->params['column']) ? null : trim($this->params['column'], '/');
?>
	<?php if(!empty($nc_user['id']) || Configure::read(NC_CONFIG_KEY.'.'.'display_header_menu') != NC_HEADER_MENU_NONE) {
		echo($this->element('Dialogs/hmenu', array('page' => $pages[$page_id_arr[0]], 'hierarchy' => isset($pages[$page_id_arr[0]]['PageAuthority']['hierarchy']) ? $pages[$page_id_arr[0]]['PageAuthority']['hierarchy'] : NC_AUTH_OTHER)));
	}?>
	<div id="container" class="table">
	<?php if(isset($headercolumn_str)): ?>
	<?php if($ncMode == NC_BLOCK_MODE && isset($pages[$page_id_arr[1]]) && $pages[$page_id_arr[1]]['PageAuthority']['hierarchy'] >= NC_AUTH_MIN_CHIEF && $column != 'headercolumn'): ?>
	<div class="table-row" data-add-columns='headercolumn'>
		<?php echo($this->element('Pages/add_block', array('id' => 'nc-add-block-headercolumn', 'add_modules' => $add_modules[$pages[$page_id_arr[1]]['Page']['room_id']], 'copy_content' => $copy_content))); ?>
	</div>
	<?php endif; ?>
	<header id="headercolumn" class="nc-columns table-row" data-page='<?php echo($page_id_arr[1]); ?>' data-columns='top'>
		<?php echo($headercolumn_str); ?>
	</header>
	<?php endif; ?>
	<div class="table widthmax">
		<?php if(isset($leftcolumn_str)): ?>
		<div id="leftcolumn" class="nc-columns table-cell" data-page='<?php echo($page_id_arr[2]); ?>' data-columns='top'>
			<?php if($ncMode == NC_BLOCK_MODE && isset($pages[$page_id_arr[2]]) && $pages[$page_id_arr[2]]['PageAuthority']['hierarchy'] >= NC_AUTH_MIN_CHIEF && $column != 'leftcolumn'): ?>
			<div class="table">
				<?php echo($this->element('Pages/add_block', array('id' => 'nc-add-block-leftcolumn', 'add_modules' => $add_modules[$pages[$page_id_arr[2]]['Page']['room_id']], 'copy_content' => $copy_content))); ?>
			</div>
			<?php endif; ?>
			<?php if(isset($blocks[$page_id_arr[2]]) || $ncMode == NC_BLOCK_MODE): ?>
				<?php echo($leftcolumn_str); ?>
			<?php endif; ?>
		</div>
		<?php endif; ?>
		<div id="centercolumn" class="nc-columns table-cell" data-columns='top' data-page='<?php echo($page_id_arr[0]); ?>'>
			<?php if($ncMode == NC_BLOCK_MODE && isset($pages[$page_id_arr[0]]) && $pages[$page_id_arr[0]]['PageAuthority']['hierarchy'] >= NC_AUTH_MIN_CHIEF && $column != 'centercolumn'): ?>
			<div class="table">
				<?php echo($this->element('Pages/add_block', array('id' => 'nc-add-block-centercolumn', 'add_modules' => $add_modules[$pages[$page_id_arr[0]]['Page']['room_id']], 'copy_content' => $copy_content))); ?>
			</div>
			<?php endif; ?>
			<?php if(isset($centercolumn_str)): ?>
				<?php echo($centercolumn_str); ?>
			<?php endif; ?>
		</div>
		<?php if(isset($rightcolumn_str)): ?>
		<div id="rightcolumn" class="nc-columns table-cell" data-page='<?php echo($page_id_arr[3]); ?>' data-columns='top'>
			<?php if($ncMode == NC_BLOCK_MODE && isset($pages[$page_id_arr[3]]) && $pages[$page_id_arr[3]]['PageAuthority']['hierarchy'] >= NC_AUTH_MIN_CHIEF && $column != 'rightcolumn'): ?>
			<div class="table">
				<?php echo($this->element('Pages/add_block', array('id' => 'nc-add-block-rightcolumn', 'add_modules' => $add_modules[$pages[$page_id_arr[3]]['Page']['room_id']], 'copy_content' => $copy_content))); ?>
			</div>
			<?php endif; ?>
			<?php if(isset($blocks[$page_id_arr[3]]) || $ncMode == NC_BLOCK_MODE): ?>
				<?php echo($rightcolumn_str); ?>
			<?php endif; ?>
		</div>
		<?php endif; ?>
	</div>
	<?php if(isset($footercolumn_str)): ?>
	<?php if($ncMode == NC_BLOCK_MODE && isset($pages[$page_id_arr[4]]) && $pages[$page_id_arr[4]]['PageAuthority']['hierarchy'] >= NC_AUTH_MIN_CHIEF && $column != 'footercolumn'): ?>
		<div class="table-row" data-add-columns='footercolumn'>
			<?php echo($this->element('Pages/add_block', array('id' => 'nc-add-block-footercolumn', 'add_modules' => $add_modules[$pages[$page_id_arr[4]]['Page']['room_id']], 'copy_content' => $copy_content))); ?>
		</div>
	<?php endif; ?>
	<footer id="footercolumn" class="nc-columns table-row" data-page='<?php echo($page_id_arr[4]); ?>' data-columns='top'>
		<?php echo($footercolumn_str); ?>
	</footer>
	<?php endif; ?>
	</div>