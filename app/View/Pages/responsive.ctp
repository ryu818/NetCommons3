<?php
// TODO:php部分はcontrollerで行ったほうがよい個所もある
// 同ディレクトリのindex.ctpと同等の処理になるため共通化するべき
	$nc_user = $this->Session->read(NC_AUTH_KEY.'.'.'User');
	$nc_mode = $this->Session->read(NC_SYSTEM_KEY.'.'.'mode');

	if(isset($page_id_arr[1]) && $page_id_arr[1] != 0 && (isset($blocks[$page_id_arr[1]]) || $nc_mode == NC_BLOCK_MODE)) {
		// headercolumn
		$headercolumn_str = $this->element('Pages/column', array('blocks' => isset($blocks[$page_id_arr[1]]) ? $blocks[$page_id_arr[1]] : null, 'page' => $pages[$page_id_arr[1]], 'parent_id' => 0, 'attr' => 'data-column-top="1"'));
	}
	if(isset($page_id_arr[2]) && $page_id_arr[2] != 0 && (isset($blocks[$page_id_arr[2]]) || $nc_mode == NC_BLOCK_MODE)) {
		// leftcolumn
		$leftcolumn_str = $this->element('Pages/column', array('blocks' => isset($blocks[$page_id_arr[2]]) ? $blocks[$page_id_arr[2]] : null, 'page' => $pages[$page_id_arr[2]], 'parent_id' => 0, 'attr' => 'data-column-top="1"'));
	}

	if(isset($blocks[$page_id_arr[0]]) || $nc_mode == NC_BLOCK_MODE) {
		// centercolumn
		$centercolumn_str = $this->element('Pages/column', array('blocks' => isset($blocks[$page_id_arr[0]]) ? $blocks[$page_id_arr[0]] : null, 'page' => $pages[$page_id_arr[0]], 'parent_id' => 0, 'attr' => 'data-column-top="1"'));

	}

	if(isset($page_id_arr[3]) && $page_id_arr[3] != 0 && (isset($blocks[$page_id_arr[3]]) || $nc_mode == NC_BLOCK_MODE)) {
		// rightcolumn
		$rightcolumn_str = $this->element('Pages/column', array('blocks' => isset($blocks[$page_id_arr[3]]) ? $blocks[$page_id_arr[3]] : null, 'page' => $pages[$page_id_arr[3]], 'parent_id' => 0, 'attr' => 'data-column-top="1"'));

	}

	if(isset($page_id_arr[4]) && $page_id_arr[4] != 0 && (isset($blocks[$page_id_arr[4]]) || $nc_mode == NC_BLOCK_MODE)) {
		// footercolumn
		$footercolumn_str = $this->element('Pages/column', array('blocks' => isset($blocks[$page_id_arr[4]]) ? $blocks[$page_id_arr[4]] : null, 'page' => $pages[$page_id_arr[4]], 'parent_id' => 0, 'attr' => 'data-column-top="1"'));
	}
	$page_style = Configure::read(NC_SYSTEM_KEY.'.'.'Page_Style');

	$style = '';
	$header_style = '';
	$footer_style = '';
	if(!empty($page_style['PageStyle']['leftmargin'])) {
		$style .= 'margin-left:'.intval($page_style['PageStyle']['leftmargin']).'px;';
	}
	if(!empty($page_style['PageStyle']['rightmargin'])) {
		$style .= 'margin-right:'.intval($page_style['PageStyle']['rightmargin']).'px;';
	}
	if(!empty($page_style['PageStyle']['topmargin'])) {
		$style .= 'margin-top:'.intval($page_style['PageStyle']['topmargin']).'px;';
	}
	if(!empty($page_style['PageStyle']['bottommargin'])) {
		$style .= 'margin-bottom:'.intval($page_style['PageStyle']['bottommargin']).'px;';
	}
	if(!empty($page_style['PageStyle']['min_width_size'])) {
		switch($page_style['PageStyle']['min_width_size']) {
			case -1:
				$style .= 'width:100%;';
				break;
			case 0:
				break;
			default:
				$style .= 'width:'.intval($page_style['PageStyle']['min_width_size']).'px;';
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
?>
<?php if(!empty($nc_user['id']) || Configure::read(NC_CONFIG_KEY.'.'.'display_header_menu') != NC_HEADER_MENU_NONE) {
	echo($this->element('Dialogs/hmenu', array('hierarchy' => isset($pages[$page_id_arr[0]]['Authority']['hierarchy']) ? $pages[$page_id_arr[0]]['Authority']['hierarchy'] : NC_AUTH_OTHER)));
}?>
<div id="container">
	<div id="main_container"<?php if($style != ''): ?> style="<?php echo($style); ?>"<?php endif; ?>>
		<?php if(isset($headercolumn_str)): ?>
		<header id="headercolumn" class="nc_columns table_row"<?php if($header_style != ''): ?> style="<?php echo($header_style); ?>"<?php endif; ?>>
				<?php echo($headercolumn_str); ?>
		</header>
		<?php endif; ?>
		<div id="centercolumn" class="nc_columns">
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
		<footer id="footercolumn" class="nc_columns table_row"<?php if($footer_style != ''): ?> style="<?php echo($footer_style); ?>"<?php endif; ?>>
				<?php echo($footercolumn_str); ?>
		</footer>
		<?php endif; ?>
	</div>
</div>
<?php
echo $this->Html->script('plugins/jquery.masonry.js');
?>
<script>
$(function(){
	$('#main_container').masonry({
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
				var centercolumn = $('#main_container');
				centercolumn.masonry( 'reload' );

				$._blocks_size[id]['width'] = w;
				$._blocks_size[id]['height'] = h;

			}
		}

		setTimeout($.Common.masonryReload, 500);
	};
});
</script>