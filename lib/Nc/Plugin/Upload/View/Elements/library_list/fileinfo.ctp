<?php
/**
 * ライブラリから追加 - ファイルの詳細情報
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.Upload.View
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
?>
<?php
	$topId = 'upload-fineinfo-${_prefix}' . $data['id'];
?>
<div id="<?php echo $topId; ?>" class="upload-library-fileinfo-outer upload-fileinfo-type-<?php echo $data['file_type']; ?>">
<h3>
<?php
	echo __d('upload', 'File information');
?>
</h3>
<fieldset class="form  upload-fileinfo">
	<?php if(!isset($is_self_upload_file) || $is_self_upload_file): ?>
	<div class="table clearfix">
		<div class="table-cell upload-thumbnail">
			<a id="<?php echo $data['_top_id'] ?>-fileinfo-img" data-upload-id="<?php echo $data['id']; ?>" data-file-type="<?php echo $data['file_type']; ?>" href="<?php echo $data['real_url']; ?>" target="_blank">
				<div class="upload-thumbnail-preview">
					<?php echo __('Preview'); ?>
				</div>
				<img src="<?php echo $data['url']; ?>" alt="" />
			</a>
		</div>
		<ul class="table-cell upload-fileinfo-base lists">
			<li class="bold">
				<span id="<?php echo $topId; ?>-file-name"><?php echo $data['file_name']; ?></span>
			</li>
			<li>
				<?php echo $data['created']; ?>
			</li>
			<li>
				<?php echo $data['file_size']; ?>
			</li>
			<li class="upload-fileinfo-image">
				<?php echo $data['width'].__d('upload', '&nbsp;×&nbsp;').$data['height']; ?>
			</li>
		</ul>
	</div>
	<?php else: ?>
	<?php /* 外部画像URL参照 */ ?>
	<div class="upload-thumbnail">
		<a id="<?php echo $data['_top_id'] ?>-fileinfo-img" class="relative" data-upload-id="<?php echo $data['id']; ?>" data-file-type="<?php echo $data['file_type']; ?>" href="<?php echo $data['real_url']; ?>" target="_blank">
			<div class="upload-thumbnail-preview">
				<?php echo __('Preview'); ?>
			</div>
			<img src="<?php echo $data['url']; ?>" alt="" />
		</a>
	</div>
	<?php endif; ?>
	<?php if(!isset($is_edit) || $is_edit): ?>
	<div class="upload-fileinfo-link">
		<?php
			$uploadId = isset($data['_id']) ? $data['_id'] : $data['id'];
			$editFileUrl = $this->Html->url(array('action' => 'edit'), true);
			$editFileUrl .= '/'.$uploadId;
			$refUrl = '';
			if($this->action == 'ref_url') {
				$editFileUrl .= '/ref_url:1';
				$refUrl .= '-ref-url';
			}
			echo $this->Html->link(__d('upload', 'Edit file'), $editFileUrl, array(
				'data-ajax' =>'#upload-edit-'.$uploadId.$refUrl,
				'data-ajax-dialog' => true,
				'data-ajax-dialog-options' => '{"title" : "'.$this->Js->escape(__d('upload', 'Edit file')).'","modal": true, "resizable": true, "position":"mouse", "width":"460"}',
				'data-ajax-effect' => 'fold'
			));
		?>
		&nbsp;|&nbsp;
		<?php
			echo __d('upload', 'Delete file');
		?>
	</div>
	<?php endif; ?>
	<ul class="lists upload-fileinfo-detail">
		<?php if(isset($is_self_upload_file) && !$is_self_upload_file): ?>
		<li>
			<dl>
				<dt>
					<label for="<?php echo $topId; ?>-url">
						<?php echo __d('upload', 'Url'); ?>
					</label>
				</dt>
				<dd>
					<input id="<?php echo $topId; ?>-url" class="upload-fileinfo-text" type="text" value="<?php echo $data['file_name']; ?>" name="data[UploadDetail][url]" onchange="$.Upload.changeRefUrl(this);" />
				</dd>
			</dl>
		</li>
		<?php endif; ?>
		<?php if($popup_type == 'image'): ?>
		<li>
			<dl>
				<dt>
					<label class="upload-fileinfo-alt" for="<?php echo $topId; ?>-alt">
						<?php echo __d('upload', 'Alt'); ?>
					</label>
				</dt>
				<dd>
					<input id="<?php echo $topId; ?>-alt" class="upload-fileinfo-text" type="text" value="<?php echo $data['alt']; ?>" name="data[UploadDetail][alt]" onchange="$.Upload.setData(<?php echo $data['id']; ?>, 'alt', $(this).val());" />
				</dd>
			</dl>
		</li>
		<?php endif; ?>
		<?php if($this->action == 'ref_url'): ?>
		<li<?php if(!isset($data['description']) || $data['description'] == ''){ echo " class=\"display-none\"";} ?>>
		<?php else: ?>
		<li{{if $data['description'] == null || $data['description'] == ''}} class="display-none"{{/if}}>
		<?php endif; ?>
			<dl>
				<dt>
					<?php
						echo __('Description');
					?>
				</dt>
				<dd>
					<textarea id="<?php echo $topId; ?>-description" rows="2" cols="17" class="upload-fileinfo-description" name="data[UploadDetail][description]" disabled="disabled"><?php echo $data['description']; ?></textarea>
				</dd>
			</dl>
		</li>
		<?php if($popup_type == 'image'): ?>
		<li class="upload-fileinfo-{{if !$data['unit'] || $data['unit'] == '%'}}percent{{else}}px{{/if}}-size" >
			<dl>
				<dt>
					<label class="upload-fileinfo-percent-size" for="<?php echo $topId; ?>-percent-size">
						<?php echo __d('upload', 'Size'); ?>
					</label>
					<label class="upload-fileinfo-px-size" for="<?php echo $topId; ?>-px-size-width">
						<?php echo __d('upload', 'Size'); ?>
					</label>
				</dt>
				<dd>
					<select id="<?php echo $topId; ?>-percent-size" class="upload-fileinfo-percent-size" name="data[UploadDetail][percent_size]" onchange="$.Upload.setData(<?php echo $data['id']; ?>, 'percent_size', $(this).val(),'${_prefix}');">
						{{each $data['percent_size_list']}}
							<option value="${name}"{{if $data['percent_size'] == name}} selected='selected'{{/if}}>${value}</option>
						{{/each}}
					</select>

					<input id="<?php echo $topId; ?>-px-size-width" class="upload-fileinfo-px-size upload-fileinfo-integer" type="text" value="{{if $data['resize_width']}}${resize_width}{{else}}<?php echo $data['width']; ?>{{/if}}" maxlength="4" name="data[UploadDetail][width]" onchange="$.Upload.setData(<?php echo $data['id']; ?>, 'resize_width', $(this).val(),'${_prefix}');" />
					<span class="upload-fileinfo-px-size"><?php echo __d('upload', '&nbsp;×&nbsp;'); ?></span>
					<input id="<?php echo $topId; ?>-px-size-height" class="upload-fileinfo-px-size upload-fileinfo-integer" type="text" value="{{if $data['resize_height']}}${resize_height}{{else}}<?php echo $data['height']; ?>{{/if}}" maxlength="4" name="data[UploadDetail][height]" onchange="$.Upload.setData(<?php echo $data['id']; ?>, 'resize_height', $(this).val(),'${_prefix}');" />

					<select name="data[UploadDetail][unit]" onchange="$.Upload.changeUnit(this); $.Upload.setData(<?php echo $data['id']; ?>, 'unit', $(this).val(),'${_prefix}');">
						<option value="%"{{if $data['unit'] == '%'}} selected='selected'{{/if}}>%</option>
						<option value="px"{{if $data['unit'] == 'px'}} selected='selected'{{/if}}>px</option>
					</select>
				</dd>
			</dl>
		</li>

		<li>
			<dl>
				<dt>
					<label for="<?php echo $topId; ?>-float">
						<?php echo __d('upload', 'Float'); ?>
					</label>
				</dt>
				<dd>
					<select id="<?php echo $topId; ?>-float" name="data[UploadDetail][float]" onchange="$.Upload.setData(<?php echo $data['id']; ?>, 'float', $(this).val());">
						<option value=""{{if $data['float'] == ''}} selected='selected'{{/if}}><?php echo(__('None'));?></option>
						<option value="center"{{if $data['float'] == 'center'}} selected='selected'{{/if}}><?php echo(__d('upload', 'Block center'));?></option>
						<option value="left"{{if $data['float'] == 'left'}} selected='selected'{{/if}}><?php echo(__d('upload', 'Float left'));?></option>
						<option value="right"{{if $data['float'] == 'right'}} selected='selected'{{/if}}><?php echo(__d('upload', 'Float right'));?></option>
					</select>
				</dd>
			</dl>
		</li>
		<li>
			<dl>
				<dt>
					<label for="<?php echo $topId; ?>-margin">
						<?php echo __d('upload', 'Margin[horizontal x vertical]'); ?>
					</label>
				</dt>
				<dd>
					<input id="<?php echo $topId; ?>-margin" type="text" value="<?php echo $data['margin_top_bottom']; ?>" class="upload-fileinfo-integer" maxlength="4" name="data[UploadDetail][margin_top_bottom]" onchange="$.Upload.setData(<?php echo $data['id']; ?>, 'margin_top_bottom', $(this).val());" />
					<?php echo __d('upload', '&nbsp;×&nbsp;'); ?>
					<input type="text" value="<?php echo $data['margin_left_right']; ?>" class="upload-fileinfo-integer" maxlength="4" name="data[UploadDetail][margin_left_right]" onchange="$.Upload.setData(<?php echo $data['id']; ?>, 'margin_left_right', $(this).val());" />
				</dd>
			</dl>
		</li>
		<li>
			<dl>
				<dt>
					<label for="<?php echo $topId; ?>-border">
						<?php echo __d('upload', 'Border'); ?>
					</label>
				</dt>
				<dd>
					<select id="<?php echo $topId; ?>-border" class="upload-fileinfo-border" name="data[UploadDetail][border_width]" onchange="$.Upload.setData(<?php echo $data['id']; ?>, 'border_width', $(this).val(),'${_prefix}');">
						{{each $data['border_list']}}
							<option value="${name}"{{if $data['border_width'] == name}} selected='selected'{{/if}}>{{if value == ''}}<?php echo(__('None'));?>{{else}}${value}{{/if}}</option>
						{{/each}}
					</select>
					<select class="upload-fileinfo-border" name="data[UploadDetail][border_style]" onchange="$.Upload.setData(<?php echo $data['id']; ?>, 'border_style', $(this).val(),'${_prefix}');">
						{{each $data['border_style_list']}}
							<option value="${name}"{{if $data['border_style'] == name}} selected='selected'{{/if}}>${name}</option>
						{{/each}}
					</select>
				</dd>
			</dl>
		</li>
		<?php endif; ?>
	</ul>
</fieldset>
</div>