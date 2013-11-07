<?php
/**
 * ライブラリから追加 - Item内部 - ファイル情報テンプレート
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.Upload.View
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
?>
<?php
$idName = $name;
if($name == 'item' || $name == 'more') {
	$data = array(
		'id' => '${id}',
		'url' => '${url}',
		'file_type' => '${file_type}',
		'orientation' => '${orientation}',
		'file_name' => '${file_name}',
	);
} else {
	if($this->action == 'ref_url') {
		$_id = isset($upload['Upload']['id']) ? $upload['Upload']['id'] : null;
		$url = isset($upload['Upload']['url']) ? $upload['Upload']['url'] : '${real_url}';
		$real_url = isset($upload['Upload']['real_url']) ? h($upload['Upload']['real_url']) : '${real_url}';
		$file_type = isset($upload['Upload']['file_type']) ? h($upload['Upload']['file_type']) : 'image';
		$orientation = isset($upload['Upload']['orientation']) ? h($upload['Upload']['orientation']) : null;
		$file_name = isset($upload['Upload']['file_name']) ? h($upload['Upload']['file_name']) : '${file_name}';
		//$extension = isset($upload['Upload']['extension']) ? h($upload['Upload']['extension']) : null;
		$description = isset($upload['Upload']['description']) ? h($upload['Upload']['description']) : null;
		$file_size = isset($upload['Upload']['file_size']) ? h($upload['Upload']['file_size']) : null;
		$created = isset($upload['Upload']['created']) ? h($upload['Upload']['created']) : null;
	} else {
		$_id = null;
		$url = '${url}';
		$real_url = '${real_url}';
		$file_type = '${file_type}';
		$orientation = '${orientation}';
		$file_name = '${file_name}';
		//$basename = '${basename}';
		//$extension = '${extension}';
		$description = '${description}';
		$file_size = '${file_size}';
		$created = '${created}';
	}

	$data = array(
		'_top_id' => '${_top_id}',
		'_id' => $_id,
		'id' => '${id}',
		'url' => $url,
		'real_url' => $real_url,
		'file_type' => $file_type,
		'orientation' => $orientation,
		'file_name' => $file_name,
		//'extension' => $extension,
		'alt' => '${alt}',
		'description' => $description,
		'width' => '${width}',
		'height' => '${height}',
		'file_size' => $file_size,
		'created' => $created,
		'float' => '${float}',
		'margin_top_bottom' => '${margin_top_bottom}',
		'margin_left_right' => '${margin_left_right}',
		'border_width' => '${border_width}',
		'border_style' => '${border_style}',
	);
	if($name == 'ref_url') {
		$name = 'fileinfo';
		$idName = 'ref-url';
	} else if($name == 'fileinfo') {
		$id = $dialog_id;
	}
}
?>
<script id="<?php echo $id; ?><?php echo '-'.$idName; ?>-template" type="text/html">
	<?php echo($this->element('library_list/'.$name, array('data' => $data, 'page' => '${page}'))); ?>
</script>