<?php
/**
 * 削除確認画面
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.Announcement.View
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
$deleteFileUrl = $this->Html->url(array('action' => 'delete'), true);
$deleteFileUrl .= '/'.$uploadId;
$bufUploadId = '';
if(!preg_match('/,/', $uploadId)) {
	$bufUploadId = $uploadId;
}
echo $this->Form->create('UploadConfirm', array(
	'url' => $deleteFileUrl,
	'data-ajax' => 'this',
	'data-ajax-confirm' => __d('upload', 'If you delete, It can not be undone.<br />Are you sure to deleted?'),
	'data-ajax-callback' => '$.Upload.deleteSuccess(res, \''.$bufUploadId.'\'); $(\'#\' + $.Upload.libraryId + \'-confirm-dialog\').remove();',
));
?>
<div class="top-description upload-delete-confirm-description">
	<?php echo(__d('upload', 'The following files are in use. You will not be content that you are using the file, to access the link if you delete. Are you sure to deleted?'));?>
</div>
<table id="<?php echo $id; ?>-confirm-grid" class="upload-delete-confirm-grid">
	<?php foreach ($uploads as $uploadId => $upload): ?>
		<tr>
			<td>
				<?php
					echo(h($upload[0]['Upload']['file_name']));
				?>
			</td>
			<td>
				<ul>
					<?php foreach ($upload as $location): ?>
						<li>
							<span><?php echo(h($location['UploadLink']['module_name'])); ?></span>
							[<span><?php echo(h($location['Upload']['module_name'])); ?></span>
							<?php if (isset($location['Page']['page_name'])): ?>
							<span><?php echo(h($location['Page']['page_name'])); ?></span>
							<?php endif; ?>
							<?php if (isset($location['Content']['title'])): ?>
							<span><?php echo(h($location['Content']['title'])); ?></span>
							<?php endif; ?>]
						</li>
					<?php endforeach; ?>
				</ul>
			</td>
		</tr>
	<?php endforeach; ?>
</table>

<?php
echo $this->Html->div('btn-bottom',
	$this->Form->button(__('Delete'), array(
		'name' => 'ok',
		'class' => 'common-btn',
		'type' => 'submit',
	)).
	$this->Form->button(__('Cancel'), array('name' => 'cancel', 'class' => 'common-btn', 'type' => 'button',
		'onclick' => '$(\'#\' + $.Upload.libraryId + \'-confirm-dialog\').remove(); return false;'))
);
echo $this->Form->hidden('confirmed' , array('name' => 'confirmed','value' => _ON));
echo $this->Form->end();

echo $this->Html->css(array('plugins/flexigrid'));
echo $this->Html->script(array('plugins/flexigrid'));
?>
<script>
$(function(){
	$('#<?php echo $id; ?>-confirm-grid:first').flexigrid(
		{
			showToggleBtn: false,
			title:'<?php echo(__d('upload', 'File in use list')); ?>',
			colModel:
				[
					{display: '<?php echo(__d('upload', 'File name')); ?>', name : 'filename', width: 140, height: 44, sortable : true},
					{display: '<?php echo(__d('upload', 'Use location[Module name - Room name - Content name]')); ?>', name : 'location', width: 400, sortable : false }
				],
			height: 'auto',
			nowrap: false,
			///singleSelect: true,
			resizable : false
		}
	);
	$('input[name="data[_Token][key]"]:first', $('#Form'+ $.Upload.libraryId)).val('<?php echo $token; ?>');
});
</script>
