<?php
$title = $this->fetch('title');
if($title != '') {
	$title .= NC_TITLE_SEPARATOR;
}
$title .= Configure::read(NC_CONFIG_KEY.'.'.'sitename');

echo '<title>'. h($title) . '</title>';

?>