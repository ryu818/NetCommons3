<?php /* ページスタイル */ ?>
<?php
if ($this->request->is('post')) {
	echo '<link href="theme/page_styles/'.$page_style['file'].'" rel="stylesheet "type="text/css">';
}
?>
<div class="nc_pages_setting_title nc_popup_color">
	<?php echo(__d('page', 'Page style')); ?>
</div>
<div id="nc_pages_setting_content">
	<?php echo($this->Form->input(__d('page', 'Font color:'), array('id' => 'page_style_color', 'type' => 'text', 'value' => '', 'data-pagestyle-name' => 'color', 'data-pagestyle-selector' => 'body'))); ?>
	<?php echo($this->Form->input(__d('page', 'Background color:'), array('id' => 'page_style_bgcolor', 'type' => 'text', 'value' => '', 'data-pagestyle-name' => 'background-color', 'data-pagestyle-selector' => 'body'))); ?>
	<form name="pages_style_form" method="post" action="<?php echo $this->Html->url(array('plugin' => 'page','action' => 'style')); ?>" data-ajax-replace="#nc_pages_setting_dialog">
		<?php echo($this->form->textarea('textarea', array('id' => 'page_setting_textarea', 'value' => $file_content, 'class' => 'display-none'))); ?>
		<div class="btn-bottom">
			<input id="pages_style_button" type="submit" class="common_btn" name="ok" value="<?php echo( __('Ok')); ?>" />
		</div>
	</form>
</div>
<?php
	echo $this->Html->script(array('Page.style/style.js'));
?>