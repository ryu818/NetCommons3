<div>
	<?php // TODO CSS, class ?>
	<?php echo($page['page_name']); ?>
	<div>
		<?php //echo($this->Form->create(false, array('type' => 'post', 'action' => './style', 'default' => 'false'))); ?>
		<?php echo($this->Form->input('文字色：', array('id' => 'pageinfo_style_color', 'type' => 'text', 'value' => (!empty($page_style['color'])) ? $page_style['color'] : '#000000;' ))); ?>
		<?php echo($this->Form->input('背景色：', array('id' => 'pageinfo_style_bgcolor', 'type' => 'text', 'value' => (!empty($page_style['bgcolor'])) ? $page_style['bgcolor'] : '#ffffff;' ))); ?>
		<?php echo($this->Form->submit('決定', array('id' => 'pageinfo_style_submit', 'name' => 'ok'))); ?>
		<?php //echo($this->Form->end()); ?>
	</div>
</div>
<script>
$(function() {
	$('#pageinfo_style_submit').click(function() {
		$.ajax({
			type: 'post',
			url: './blocks/pageinfo/style',
			data: {
				'color': $('#pageinfo_style_color').val(),
				'bgcolor': $('#pageinfo_style_bgcolor').val()
			},
			success: function(data) {
				alert('OK');
			},
			error: function(data) {
				alert('NG');
			}
		});
	});
});
//$.fn.Pageinfo();
</script>