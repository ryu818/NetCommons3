<?php
$nc_mode = $this->Session->read(NC_SYSTEM_KEY.'.mode');
$content = $this->fetch('content');
if($content == '') {
	if($hierarchy < NC_AUTH_MIN_CHIEF) {
		// コンテンツが空で、主坦以下の権限ならば、非表示にする。
		return;
	}
	$content = __('Content not found.');
	$this->assign('content', $content);
}
if($block['Block']['controller_action'] == 'group') {
	$class_name = 'nc-group';
	$attr = ' data-columns="group"';
} else {
	$class_name = 'nc-block';
	$attr = '';
}
/* スタイル */
$margin = "margin:".$block['Block']['top_margin']."px ".$block['Block']['right_margin']."px ".$block['Block']['bottom_margin']."px ".
					$block['Block']['left_margin']."px; ";
if($margin == "margin:0px 0px 0px 0px;") {
	$margin = "";
}
$width = "";
if($block['Block']['min_width_size'] != 0) {
	if($block['Block']['min_width_size'] == -1)
		$width = "width:100%;";
	else
		$width = "width:".$block['Block']['min_width_size']."px;";
}
//if($nc_mode == NC_BLOCK_MODE) {
//	/* セッティングモードがONならば広さを指定 IE6等では機能しない */
//	$width .= "min-width:120px;";
//}
$height = "";
$min_height = "";
if($block['Block']['min_height_size'] != 0) {
	$height = "height:".$block['Block']['min_height_size']."px;";
	$min_height = "min-height:".$block['Block']['min_height_size']."px;";
}
if($width != "" || $height != "" || $margin != "") {
	$block['Block']['margin_style'] = ' style="'.$margin.'"';
	$block['Block']['style'] = ' style="'.$width.$height.'"';
} else {
	$block['Block']['margin_style'] = '';
	$block['Block']['style'] = '';
}
$pos = strpos($block['Block']['theme_name'], '.');
$parent_class_name = null;
if($pos !== false) {
	$parent_class_name = substr($block['Block']['theme_name'], 0, $pos);
	$theme_name = array($parent_class_name.'.block', $block['Block']['theme_name'].'/block');
	$parent_class_name = 'th-' . Inflector::underscore($parent_class_name);
} else {
	$theme_name = $block['Block']['theme_name'].'.block';
}
$block['Block']['theme_name'] = 'th-' . str_replace('.', '-', Inflector::underscore($block['Block']['theme_name']));	// th-(frame_name)-(color_dir)
$current_url = $this->here;
if(count($this->params->query) > 0) {
	$query = '';
	foreach($this->params->query as $k => $v) {
		if(substr($k, 0, 1) == '_') {
			// 先頭「_」のものはURLに加えない。
			continue;
		}
		$query .= ($query == '') ? '?' : '&';
		$query .= $k. '='. $v;
	}
	$current_url .= $query;
}
if($min_height != "" && $nc_mode == NC_BLOCK_MODE && $hierarchy >= NC_AUTH_MIN_CHIEF) {
	// 最小の高さを高く設定してもリサイズのアイコンの位置がかわらないため、コンテンツの高さも設定するように修正。
	$block['Block']['height'] = ' style="'.$min_height.'"';
} else {
	$block['Block']['height'] = '';
}
?>
<div id="<?php echo($id); ?>" class="<?php echo($class_name); ?>"<?php echo($block['Block']['margin_style']); ?> data-block='<?php echo($block['Block']['id']); ?>' data-action='<?php echo($block['Block']['controller_action']); ?>' data-url='<?php echo(h($current_url)); ?>'<?php echo($attr); ?>>
	<div class="<?php if(isset($parent_class_name)): ?><?php echo($parent_class_name.' '); ?><?php endif; ?><?php echo($block['Block']['theme_name']); ?> nc-frame table"<?php echo($block['Block']['style']); ?>>
		<?php if($hierarchy >= NC_AUTH_MIN_CHIEF && !isset($nc_error_flag)): ?>
			<?php if($page['Page']['room_id'] != $block['Content']['room_id']): ?>
				<div class="nc-block-header-shortcut nc-block-header-shortcut-show"><div></div></div>
			<?php elseif(!$block['Content']['is_master']): ?>
				<div class="nc-block-header-shortcut nc-block-header-shortcut-edit"><div></div></div>
			<?php endif; ?>
		<?php endif; ?>
		<?php /* ブロックヘッダー */ ?>
		<?php if($nc_mode == NC_BLOCK_MODE && $hierarchy >= NC_AUTH_MIN_CHIEF): ?>
			<?php echo($this->element('Frame/block_header')); ?>
		<?php endif; ?>
		<section>
		<?php
		//TODO: 固定 frame' => 'default' 固定値の意味から再度、調査する必要あり
		echo($this->element('index', array('title' => $this->element('Frame/block_title', array('block' => $block, 'parent_class_name' => $parent_class_name)), 'content' => $this->element('Frame/block_content', array('block' => $block, 'parent_class_name' => $parent_class_name))), array('frame' => 'default')));
		?>
		</section>
		<?php if($nc_mode == NC_BLOCK_MODE && $hierarchy >= NC_AUTH_MIN_CHIEF): ?>
			<?php echo($this->element('Frame/block_footer')); ?>
		<?php endif; ?>
		<?php
			/* 基本、echoしてないため、テーマのCSSはAjaxで表示する際は読み込まれない。 */
			$block_css = $this->Html->css($theme_name, null, array('frame' => true));
			if(isset($this->request->query['_nc_include_css']) && $this->request->query['_nc_include_css']) {
				echo $block_css;
			}
		?>
	</div>
	<?php if($nc_mode == NC_BLOCK_MODE && $hierarchy >= NC_AUTH_MIN_CHIEF): ?>
		<script>
		$(function(){
			$.PagesBlock.initBlock('<?php echo($id); ?>');
		});
		</script>
	<?php endif; ?>
</div>