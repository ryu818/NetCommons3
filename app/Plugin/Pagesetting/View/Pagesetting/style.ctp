<div>
	<?php echo($page['page_name']); ?>
	<div>
		<?php echo($this->Form->input('文字色：', array('id' => 'pagesetting_style_color', 'type' => 'text', 'value' => (!empty($page_style['color'])) ? $page_style['color'] : '#000000;' ))); ?>
		<?php echo($this->Form->input('背景色：', array('id' => 'pagesetting_style_bgcolor', 'type' => 'text', 'value' => (!empty($page_style['bgcolor'])) ? $page_style['bgcolor'] : '#ffffff;' ))); ?>
		<?php echo($this->Form->submit('決定', array('id' => 'pagesetting_style_submit', 'name' => 'ok'))); ?>
	</div>
</div>
<script>
$(function() {
	$('#pagesetting_style_submit').click(function() {
		$.ajax({
			type: 'post',
			url: './blocks/pagesetting/style',
			data: {
				'color': $('#pagesetting_style_color').val(),
				'bgcolor': $('#pagesetting_style_bgcolor').val()
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
</script>