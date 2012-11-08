<?php /* ページスタイル */ ?>
<?php
$color = (!empty($page_style['color'])) ? $page_style['color'] : '#000000';
$bgcolor = (!empty($page_style['bgcolor'])) ? $page_style['bgcolor'] : '#ffffff';
?>
<div class="nc_pages_setting_title nc_popup_color">
	<?php echo(__d('page', 'Page style')); ?>
</div>
<div id="nc_pages_setting_content">
	<form name="pages_style_form" method="post" action="<?php echo $this->Html->url(array('plugin' => 'page','action' => 'style')); ?>" data-ajax-replace="#nc_pages_setting_dialog">
		<?php echo($this->Form->input('PageStyle.color', array('label' => '文字色：', 'type' => 'text', 'value' => $color))); ?>
		<?php echo($this->Form->input('PageStyle.background-color', array('label' => '背景色：', 'type' => 'text', 'value' => $bgcolor))); ?>
		<div class="btn-bottom">
			<input type="submit" class="common_btn" name="ok" value="<?php echo( __('Ok')); ?>" />
		</div>
	</form>
</div>