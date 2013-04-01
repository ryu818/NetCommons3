<?php
// ブロックテーマ表示
$this->extend('/Frame/block');

//$this->assign('title', "block_id:". $block_id);
echo ($this->Html->script(array('Announcement.edit', 'plugins/jquery.nc_wysiwyg.js')));
echo ($this->Html->css('plugins/jquery.nc_wysiwyg.css'));
echo $this->Form->create(null, array('data-pjax' => '#'.$id));
?>
<fieldset class="form">
	<ul class="lists blog-edits-lists">
		<li>
			<dl>
				<dt>
					<?php
						echo $this->Form->label('Content.title', __d('blog', 'Blog name'));
					?>
				</dt>
				<dd>
					<?php
						$settings = array(
							'type' => 'text',
							'value' => $block['Content']['title'],
							'label' => false,
							'div' => false,
							'maxlength' => NC_VALIDATOR_BLOCK_TITLE_LEN,
							'size' => 35,
							'class' => 'nc-title',
							'error' => array('attributes' => array(
								'selector' => true
							))
						);
						echo $this->Form->input('Content.title', $settings);
					?>
				</dd>
			</dl>
		</li>
		<li>
			<dl>
				<?php
					echo($this->Form->error('Htmlarea.content'));
					echo $this->Form->textarea('content', array('escape' => false, 'class' => 'nc-wysiwyg', 'value' => isset($htmlarea['Htmlarea']['content']) ? $htmlarea['Htmlarea']['content'] : ''));
				?>
			</dd>
		</li>
	</ul>
</fieldset>
<?php
echo $this->Html->div('submit',
	$this->Form->button(__('Ok'), array('name' => 'ok', 'class' => 'common-btn')).
	$this->Form->button(__('Cancel'), array('name' => 'cancel', 'class' => 'common-btn', 'type' => 'button'))
);
echo $this->Form->end();
?>
<script>
$(function(){
	$('#<?php echo($id); ?>').AnnouncementEdit('<?php echo ($id);?>');
});
</script>