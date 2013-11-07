<?php
/**
 * アップロード画面(ファイル編集)
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.Upload.View
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
?>
<div class="upload-fileinfo-type-<?php echo $upload['Upload']['file_type']; ?>">
<?php
echo $this->Form->create('Upload', array('data-ajax' => '#'.$id, 'data-ajax-method' => 'inner'));
?>
<fieldset class="form  upload-fileinfo">
	<div class="table clearfix">
		<div class="table-cell upload-thumbnail">
			<img src="<?php echo h($upload['Upload']['url']); ?>" alt="" />
		</div>
		<ul class="table-cell upload-fileinfo-base lists">
			<li class="bold">
				<?php echo h($upload['Upload']['file_name']); ?>
			</li>
			<li>
				<?php echo $upload['Upload']['created']; ?>
			</li>
			<li>
				<?php echo $upload['Upload']['file_size']; ?>
			</li>
			<?php if($upload['Upload']['file_type'] == 'image'): ?>
			<li class="upload-fileinfo-image">
				<?php echo $upload['Upload']['width'].__d('upload', '&nbsp;×&nbsp;').$upload['Upload']['height']; ?>
			</li>
			<?php endif; ?>
		</ul>
	</div>
</fieldset>
<fieldset class="form">
	<ul class="lists">
		<?php
			$columnNames = array(
				'basename' => __d('upload', 'File name'),
			);

			if($upload['Upload']['file_type'] == 'image') {
				$columnNames['alt'] = __d('upload', 'Alt');
			}
			$columnNames['description'] = __('Description');
			$extendSettings = array(
				'alt' => array(
					'type' => 'textarea',
					'class' => 'upload-fileinfo-alt',
					'cols' => '20',
					'rows' => '1',
				),
				'description' => array(
					'type' => 'textarea',
					'class' => 'upload-fileinfo-edit-description',
					'cols' => '20',
					'rows' => '5',
				),
			);
		?>
		<?php foreach ($columnNames as $columnName => $title): ?>
		<li>
			<dl>
				<dt>
					<?php
						echo $this->Form->label($columnName, $title);
					?>
					<?php if($columnName == 'basename'): ?>
						<span class="require"><?php echo __('*'); ?></span>
					<?php endif; ?>
				</dt>
				<dd>

					<?php
						$settings = array(
							'type' => 'text',
							'class' => 'upload-fileinfo-text',
							'label' => false,
							'div' => false,
							'value' => $upload['Upload'][$columnName],
						);
						if(isset($extendSettings[$columnName])) {
							$settings = array_merge($settings, $extendSettings[$columnName]);
						}
						echo $this->Form->input($columnName, $settings);
					?>
				</dd>
			</dl>
		</li>
		<?php endforeach; ?>

	</ul>
</fieldset>
<?php
	echo $this->Form->hidden('extension', array('value' => $upload['Upload']['extension']));
	echo $this->Html->div('submit',
		$this->Form->button(__('Ok'), array('name' => 'ok', 'class' => 'common-btn', 'type' => 'submit')).
		$this->Form->button(__('Cancel'), array('name' => 'cancel', 'class' => 'common-btn', 'type' => 'button',
			'onclick' => "$('#".$id."').dialog('close');"))
	);
?>
<?php
echo $this->Form->end();
?>
<?php if($success): ?>
<script>
$(function(){
	$.Upload.successEdit(<?php echo $upload['Upload']['id'] ?>, <?php if($ref_url): ?>true<?php else: ?>false<?php endif; ?>, '#Form<?php echo $id; ?>', '<?php if($ref_url): ?>0<?php else: ?><?php echo intval($upload['Upload']['id']); ?><?php endif;?>');
});
</script>
<?php endif; ?>
</div>