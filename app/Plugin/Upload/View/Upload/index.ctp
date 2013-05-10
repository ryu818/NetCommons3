<form method="POST" enctype="multipart/form-data" class="nc-wysiwyg-insertfile" action="bbb">
	<p>アップロードするファイルを指定してください。</p>
	<input type="file" name="files" class="nc-wysiwyg-insertfile-inputfile">
	<ul class="nc-wysiwyg-insertfile-row">
		<li>
			<dl>
				<dt>タイトル</dt>
				<dd><input type="text" class="nc-wysiwyg-insertfile-title" name="alt_title" value=""></dd>
			</dl>
		</li>
	</ul>
	<div class="nc-wysiwyg-insertfile-btn">
		<input type="button" value="決定" class="common-btn" name="ok">
		<input type="button" value="キャンセル" class="common-btn" name="cancel">
	</div>
	<?php
		echo $this->Html->script('Upload.upload/index');
		echo $this->Html->css('Upload.upload/index');
	?>
</form>