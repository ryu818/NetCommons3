<?php
$nc_mode = $this->Session->read(NC_SYSTEM_KEY.'.mode');
if($block['Block']['controller_action'] == 'group') {
	$class_name = 'nc_group';
	$attr = ' data-columns="group"';
} else {
	$class_name = 'nc_block';
	$attr = '';
}
/* スタイル */
$margin = "margin:".$block['Block']['topmargin']."px ".$block['Block']['rightmargin']."px ".$block['Block']['bottommargin']."px ".
					$block['Block']['leftmargin']."px; ";
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
if($block['Block']['min_height_size'] != 0) {
	$height = "height:".$block['Block']['min_height_size']."px;";
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
	$parent_class_name = 'th_' . Inflector::underscore($parent_class_name);
} else {
	$theme_name = $block['Block']['theme_name'].'.block';
}
$block['Block']['theme_name'] = 'th_' . str_replace('.', '_', Inflector::underscore($block['Block']['theme_name']));	// th_(frame_name)_(color_dir)
?>
<div id="<?php echo($id); ?>" class="<?php echo($class_name); ?>"<?php echo($block['Block']['margin_style']); ?> data-block='<?php echo($block['Block']['id']); ?>' data-action='<?php echo($block['Block']['controller_action']); ?>'<?php echo($attr); ?>>
	<div class="<?php if(isset($parent_class_name)): ?><?php echo($parent_class_name.' '); ?><?php endif; ?><?php echo($block['Block']['theme_name']); ?> nc_frame table"<?php echo($block['Block']['style']); ?>>
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
		<?php /* echoしてないため、テーマのCSSはAjaxで表示する際は読み込まれない。 */ ?>
		<?php $this->Html->css($theme_name, null, array('frame' => true)); ?>
	</div>
	<?php if($nc_mode == NC_BLOCK_MODE && $hierarchy >= NC_AUTH_MIN_CHIEF): ?>
		<script>$.PagesBlock.initBlock('<?php echo($id); ?>');</script>
	<?php endif; ?>
</div>