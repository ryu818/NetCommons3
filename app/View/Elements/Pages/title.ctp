<?php
//TODO:test
$title = $this->fetch('title');
if(!isset($title)) {
	$title = $title_for_layout;
}
if($title != '') {
	$title .= NC_TITLE_SEPARATOR;
}
$title .= Configure::read(NC_CONFIG_KEY.'.'.'sitename');

echo '<title>'. h($title) . '</title>';

?>