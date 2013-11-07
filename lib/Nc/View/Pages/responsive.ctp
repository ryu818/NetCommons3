<?php
// TODO:php部分はcontrollerで行ったほうがよい個所もある
// 同ディレクトリのindex.ctpと同等の処理になるため共通化するべき
	$nc_user = $this->Session->read(NC_AUTH_KEY.'.'.'User');
	$ncMode = $this->Session->read(NC_SYSTEM_KEY.'.'.'mode');

	if(isset($page_id_arr[1]) && $page_id_arr[1] != 0 && (isset($blocks[$page_id_arr[1]]) || $ncMode == NC_BLOCK_MODE)) {
		// headercolumn
		$headercolumn_str = $this->element('Pages/column', array('blocks' => isset($blocks[$page_id_arr[1]]) ? $blocks[$page_id_arr[1]] : null, 'page' => $pages[$page_id_arr[1]], 'parent_id' => 0, 'attr' => 'data-column-top="1"'));
	}
	if(isset($page_id_arr[2]) && $page_id_arr[2] != 0 && (isset($blocks[$page_id_arr[2]]) || $ncMode == NC_BLOCK_MODE)) {
		// leftcolumn
		$leftcolumn_str = $this->element('Pages/column', array('blocks' => isset($blocks[$page_id_arr[2]]) ? $blocks[$page_id_arr[2]] : null, 'page' => $pages[$page_id_arr[2]], 'parent_id' => 0, 'attr' => 'data-column-top="1"'));
	}

	if(isset($blocks[$page_id_arr[0]]) || $ncMode == NC_BLOCK_MODE) {
		// centercolumn
		$centercolumn_str = $this->element('Pages/column', array('blocks' => isset($blocks[$page_id_arr[0]]) ? $blocks[$page_id_arr[0]] : null, 'page' => $pages[$page_id_arr[0]], 'parent_id' => 0, 'attr' => 'data-column-top="1"'));

	}

	if(isset($page_id_arr[3]) && $page_id_arr[3] != 0 && (isset($blocks[$page_id_arr[3]]) || $ncMode == NC_BLOCK_MODE)) {
		// rightcolumn
		$rightcolumn_str = $this->element('Pages/column', array('blocks' => isset($blocks[$page_id_arr[3]]) ? $blocks[$page_id_arr[3]] : null, 'page' => $pages[$page_id_arr[3]], 'parent_id' => 0, 'attr' => 'data-column-top="1"'));

	}

	if(isset($page_id_arr[4]) && $page_id_arr[4] != 0 && (isset($blocks[$page_id_arr[4]]) || $ncMode == NC_BLOCK_MODE)) {
		// footercolumn
		$footercolumn_str = $this->element('Pages/column', array('blocks' => isset($blocks[$page_id_arr[4]]) ? $blocks[$page_id_arr[4]] : null, 'page' => $pages[$page_id_arr[4]], 'parent_id' => 0, 'attr' => 'data-column-top="1"'));
	}
?>
	<?php if(!empty($nc_user['id']) || Configure::read(NC_CONFIG_KEY.'.'.'display_header_menu') != NC_HEADER_MENU_NONE) {
		echo($this->element('Dialogs/hmenu', array('hierarchy' => isset($pages[$page_id_arr[0]]['PageAuthority']['hierarchy']) ? $pages[$page_id_arr[0]]['PageAuthority']['hierarchy'] : NC_AUTH_OTHER)));
	}?>
	<div id="container"<?php if($style != ''): ?> style="<?php echo($style); ?>"<?php endif; ?>>
		<?php if(isset($headercolumn_str)): ?>
		<header id="headercolumn" class="nc-columns table-row">
				<?php echo($headercolumn_str); ?>
		</header>
		<?php endif; ?>
		<div id="centercolumn" class="nc-columns">
			<?php if(isset($leftcolumn_str)): ?>
				<?php echo($leftcolumn_str); ?>
			<?php endif; ?>
			<?php if(isset($centercolumn_str)): ?>
				<?php echo($centercolumn_str); ?>
			<?php endif; ?>
			<?php if(isset($rightcolumn_str)): ?>
				<?php echo($rightcolumn_str); ?>
			<?php endif; ?>
		</div>
		<?php if(isset($footercolumn_str)): ?>
		<footer id="footercolumn" class="nc-columns table-row">
				<?php echo($footercolumn_str); ?>
		</footer>
		<?php endif; ?>
	</div>
<?php
echo $this->Html->script('plugins/jquery.masonry.js');
?>
<script>
$(function(){
	$('#container').masonry({
      	itemSelector: $('[data-column-top]').children(),	// グループ化したものは一塊として表示　ブロックすべての場合は「[data-block]」
		columnWidth: 1,
		isAnimated: true,
		isFitWidth: true
	});
	$(window).load(function () {
		$._blocks_size = [];
		var resize_blocks = $("[data-block]");
		resize_blocks.each(function(){
			var $this = $(this);
			var id = $this.attr('id');
			$._blocks_size[id] = new Array();
			$._blocks_size[id]['width'] = $this.outerWidth(true);
			$._blocks_size[id]['height'] = $this.outerHeight(true);
		});
		$.Common.masonryReload();
	});
	$.Common.masonryReload = function () {
		for(var id in $._blocks_size) {
			var $block = $('#'+id);
			var w = $block.outerWidth(true);
			var h = $block.outerHeight(true);
			if($._blocks_size[id]['width'] != w || $._blocks_size[id]['height'] != h) {
				// サイズ変更あり
				var centercolumn = $('#container');
				centercolumn.masonry( 'reload' );

				$._blocks_size[id]['width'] = w;
				$._blocks_size[id]['height'] = h;

			}
		}

		setTimeout($.Common.masonryReload, 500);
	};
});
</script>