<?php /* ページスタイル */ ?>
<?php
if ($this->request->is('post')) {
	echo '<link href="theme/page_styles/'.$page_style['file'].'" rel="stylesheet "type="text/css">';
}
?>
<div class="nc-pages-setting-title nc-panel-color" data-pages-header="true">
	<?php echo(__d('page', 'Page style')); ?>
</div>
<div class="nc-pages-setting-content">
	<?php echo($this->Form->input(__d('page', 'Font color:'), array('id' => 'page_style_color', 'type' => 'text', 'value' => '', 'data-pagestyle-name' => 'color', 'data-pagestyle-selector' => 'body'))); ?>
	<?php echo($this->Form->input(__d('page', 'Background color:'), array('id' => 'page_style_bgcolor', 'type' => 'text', 'value' => '', 'data-pagestyle-name' => 'background-color', 'data-pagestyle-selector' => 'body'))); ?>
	<form name="pages_style_form" method="post" action="<?php echo $this->Html->url(array('plugin' => 'page','action' => 'style')); ?>" data-ajax-replace="#nc-pages-setting-dialog">
		<?php echo($this->element('style/textarea', isset($file_content) ? array('file_content'=>$file_content) : array())); ?>
		<div class="btn-bottom">
			<input id="pages-style-button" type="submit" class="common-btn" name="ok" value="<?php echo( __('Ok')); ?>" />
		</div>
	</form>
</div>
<?php
	echo $this->Html->script(array('Page.style/style.js'));
?>
