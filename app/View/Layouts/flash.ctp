<!DOCTYPE html>
<html>
<head>
<?php echo $this->Html->charset(); ?>
<title><?php echo h($page_title); ?></title>
<?php
if(method_exists($this->Html,'fetchScript')) {
	echo($this->element('Pages/include_header'));
} else {
	echo($this->element('Pages/include_header_error'));
}
?>
<?php echo $this->Html->css('redirect/', null, array('inline' => true, 'data-title' => 'Redirect')); ?>
<?php if (Configure::read('debug') == 0) { ?>
<meta http-equiv="Refresh" content="<?php echo $pause; ?>;url=<?php echo $url; ?>">
<?php } ?>
</head>
<body class="nc_redirect_body">
	<ul class="nc_redirect">
		<li class="nc_redirect_text">
			<?php echo($message); ?>
		</li>
		<li class="nc_redirect_subtext">
			<?php echo(sprintf($sub_message, h($url))); ?>
		</li>
		<?php if(isset($error_id_str) && $error_id_str != ''): ?>
			<li class="nc_redirect_subtext">
				(error:<?php echo($error_id_str); ?>)
			</li>
		<?php endif; ?>
	</ul>
<?php
if(Configure::read('debug') == _OFF) {
	echo "<script>".
	"setTimeout(function(){var location_str = '".h($url)."';location.href=location_str.replace(/&amp;/ig,\"&\");}, ".$pause."*1000);".
	"</script>";
}
?>
<?php echo $this->element('sql_dump'); ?>
</body>
</html>