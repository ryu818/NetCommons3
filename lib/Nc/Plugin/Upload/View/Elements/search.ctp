<?php
/**
 * ライブラリから追加 - 検索部分
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.Upload.View
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
?>
<fieldset class="form upload-library-search clearfix">
	<ul>
		<li id="upload-library-file-type<?php echo $id; ?>" class="upload-library-file-type">
			<?php if ($popup_type == 'image'): ?>
				<a class="link upload-current" href="#" data-file-type="image"><?php echo(__d('upload', 'Image'));?></a>&nbsp;|&nbsp;
				<a class="link" href="#" data-file-type="image-unused"><?php echo(__d('upload', 'Unattached'));?></a>
			<?php else: ?>
				<a class="upload-current" href="#" data-file-type=""><?php echo(__d('upload', 'All'));?></a>&nbsp;|&nbsp;
				<a href="#" data-file-type="image"><?php echo(__d('upload', 'Image'));?></a>&nbsp;|&nbsp;
				<a href="#" data-file-type="audio"><?php echo(__d('upload', 'Audio'));?></a>&nbsp;|&nbsp;
				<a href="#" data-file-type="video"><?php echo(__d('upload', 'Video'));?></a>&nbsp;|&nbsp;
				<a href="#" data-file-type="other"><?php echo(__d('upload', 'Other'));?></a>&nbsp;|&nbsp;
				<a href="#" data-file-type="file-unused"><?php echo(__d('upload', 'Unattached'));?></a>
			<?php endif; ?>
			<?php echo $this->Form->hidden('file_type', array('value' => $upload_search['UploadSearch']['file_type'])); ?>
		</li>
		<?php if($is_admin): ?>
		<li>
			<?php
				$options = array(
					UPLOAD_SEARCH_CONDITION_USER_MYSELF => __d('upload', 'User Myself'),
					UPLOAD_SEARCH_CONDITION_USER_ALL => __d('upload', 'User All'),
					UPLOAD_SEARCH_CONDITION_USER_WITHDRAW => __d('upload', 'User Withdraw')
				);
				echo $this->Form->input('user_type', array(
					'type' => 'select',
					'options' => $options,
					'class' => 'upload-search-user-type',
					'label' => false,
					'div' => false,
					'value' => $upload_search['UploadSearch']['user_type'],
					'onchange' => "$.Upload.changeSearchUserType(this, '".$id."', ".UPLOAD_SEARCH_CONDITION_USER_MYSELF."); $(this.form).submit();",
				));
			?>
		</li>
		<?php endif; ?>
		<li>
			<?php
				echo $this->Form->input('plugin', array(
					'type' => 'select',
					'options' => $upload_search_plugin_options,
					'class' => 'upload-search-module-id',
					'label' => false,
					'div' => false,
					'value' => $upload_search['UploadSearch']['plugin'],
					'onchange' => '$(this.form).submit();',
				));
			?>
		</li>
		<li>
			<?php
				echo $this->Form->input('created', array(
					'type' => 'select',
					'options' => $upload_search_created_options,
					'class' => 'upload-search-created',
					'label' => false,
					'div' => false,
					'value' => $upload_search['UploadSearch']['created'],
					'onchange' => '$(this.form).submit();',
				));
			?>
		</li>
		<li id="upload-created-all<?php echo $id; ?>"<?php if ($upload_search['UploadSearch']['user_type'] == UPLOAD_SEARCH_CONDITION_USER_MYSELF): ?> style="display:none;"<?php endif; ?>>
			<?php
				echo $this->Form->input('created-all', array(
					'type' => 'select',
					'options' => $upload_search_created_all_options,
					'class' => 'upload-search-created',
					'label' => false,
					'div' => false,
					'value' => $upload_search['UploadSearch']['created'],
					'onchange' => '$(this.form).submit();',
				));
			?>
		</li>
		<li>
			<dl>
				<dt>
					<?php
						echo $this->Form->label('order', __d('upload', 'Sorting'));
					?>
				</dt>
				<dd>
					<?php
						$options = array(
							'created' => __d('upload', 'Upload date'),
							'file_name' => __d('upload', 'File name'),
							'file_size' => __d('upload', 'File size')
						);
						echo $this->Form->input('order', array(
							'type' => 'select',
							'options' => $options,
							'class' => 'upload-search-order',
							'label' => false,
							'div' => false,
							'value' => $upload_search['UploadSearch']['order'],
							'onchange' => '$(this.form).submit();',
						));
					?>
					<?php
						$options = array(
							'ASC' => __d('upload', 'Ascending order'),
							'DESC' => __d('upload', 'Descending order')
						);
						echo $this->Form->input('order_direction', array(
							'type' => 'select',
							'options' => $options,
							'class' => 'upload-search-order-direction',
							'label' => false,
							'div' => false,
							'value' => $upload_search['UploadSearch']['order_direction'],
							'onchange' => '$(this.form).submit();',
						));
					?>
				</dd>
			</dl>
		</li>
		<li>
			<?php
				echo $this->Form->input('text', array(
					'type' => 'text',
					'class' => 'upload-search-text',
					'label' => false,
					'div' => false,
					'error' => array('attributes' => array(
						'selector' => true
					)),
					'value' => $upload_search['UploadSearch']['text']
				));
				echo $this->Form->button(__('Search'), array(
					'type' => 'submit',
					'class' => 'common-btn-min'
				));
			?>
			<?php if ($is_admin): ?>
			<div id="upload-text-options<?php echo $id; ?>"<?php if ($upload_search['UploadSearch']['user_type'] == UPLOAD_SEARCH_CONDITION_USER_MYSELF): ?> style="display:none;"<?php endif; ?>>
				<?php
					$options = array(
					UPLOAD_SEARCH_CONDITION_FROM_FILE => __d('upload', 'Search by file name, description.'),
					UPLOAD_SEARCH_CONDITION_FROM_CREATOR => __d('upload', 'Search by creator.')
				);
				$attributes = array(
					'div' => false,
					'legend' => false,
					'value' => $upload_search['UploadSearch']['search_type']
				);
				echo $this->Form->radio('search_type', $options, $attributes);
			?>
			</div>
			<?php endif; ?>
		</li>
	</ul>
</fieldset>
<?php echo $this->Form->hidden('page',array('value' => $upload_search['UploadSearch']['page'])); ?>