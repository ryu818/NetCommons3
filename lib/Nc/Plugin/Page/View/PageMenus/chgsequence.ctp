<script>
$(function(){
	var li = $('#pages-menu-edit-item-<?php echo($page['Page']['id']);?>');
	$('a.pages-menu-edit-title:first', li).html("<?php echo($this->Js->escape($page['Page']['page_name']));?>").attr("title", "<?php echo($this->Js->escape($page['Page']['page_name']));?>");
	$('input.pages-menu-edit-title:first', li).val("<?php echo($this->Js->escape($page['Page']['page_name']));?>");
});
</script>