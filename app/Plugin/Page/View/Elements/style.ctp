<?php /* ページスタイル */ ?>
<div class="nc_pages_setting_title nc_popup_color">
	<?php echo(__d('page', 'Page style')); ?>
</div>
<div id="nc_pages_setting_content">
	<form name="pages_style_form" method="post" action="<?php echo $this->Html->url(array('plugin' => 'page','action' => 'style')); ?>" data-ajax-replace="#nc_pages_setting_dialog">
		<input type="text" name="data[PageStyle][test]" value="Test" />
		<div class="btn-bottom">
			<input type="submit" class="common_btn" name="ok" value="<?php echo( __('Ok')); ?>" />
		</div>
	</form>
</div>