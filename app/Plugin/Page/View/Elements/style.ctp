<?php /* ページスタイル */ ?>
<?php
if ($this->request->is('post')) {
	echo '<link href="theme/page_styles/'.$page_style['file'].'" rel="stylesheet "type="text/css">';
}
?>
<?php
$color = (!empty($page_style['color'])) ? $page_style['color'] : '';
$bgcolor = (!empty($page_style['bgcolor'])) ? $page_style['bgcolor'] : '';
?>
<div class="nc_pages_setting_title nc_popup_color">
	<?php echo(__d('page', 'Page style')); ?>
</div>
<div id="nc_pages_setting_content">
	<form name="pages_style_form" method="post" action="<?php echo $this->Html->url(array('plugin' => 'page','action' => 'style')); ?>" data-ajax-replace="#nc_pages_setting_dialog">
		<?php echo($this->Form->input('PageStyle.color', array('id' => 'page_setting_color', 'label' => '文字色：', 'type' => 'text', 'value' => $color))); ?>
		<?php echo($this->Form->input('PageStyle.background-color', array('id' => 'page_setting_bgcolor', 'label' => '背景色：', 'type' => 'text', 'value' => $bgcolor))); ?>
		<div class="btn-bottom">
			<input id="pages_style_button" type="submit" class="common_btn" name="ok" value="<?php echo( __('Ok')); ?>" />
		</div>
	</form>
</div>
<?php
	echo $this->Html->script(array('Page.style/style.js', 'plugins/jquery.nestable.js'));
?>
<script>
	$('#nc_pages_setting_content').PageStyle();
</script>