<!DOCTYPE html>
<html>
<head>
<?php echo $this->Html->charset(); ?>
<title><?php echo h(strip_tags($page_title)); ?></title>
<?php echo($this->element('Pages/include_header')); ?>
<?php echo $this->Html->css('redirect/', null, array('inline' => true, 'data-title' => 'Redirect')); ?>
<?php if (Configure::read('debug') == 0) { ?>
<meta http-equiv="Refresh" content="<?php echo $pause; ?>;url=<?php echo $url; ?>">
<?php } ?>
</head>
<body class="nc-redirect-body">
	<ul class="nc-redirect">
		<li class="nc-redirect-text">
			<?php echo($message); ?>
		</li>
		<li class="nc-redirect-subtext">
			<?php echo(sprintf($sub_message, h($url))); ?>
		</li>
		<?php if(isset($file) && isset($line)): ?>
			<li class="nc-redirect-subtext">
				<?php echo(sprintf('%s (line %s)', $file, $line)); ?>
			</li>
		<?php endif; ?>
	</ul>
<?php
if(Configure::read('debug') == 0) {
	echo "<script>".
	"setTimeout(function(){var location_str = '".h($url)."';location.href=location_str.replace(/&amp;/ig,\"&\");}, ".$pause."*1000);".
	"</script>";
}
?>
<?php echo $this->element('sql_dump'); ?>
<?php
if(!$this->request->query('_iframe_upload')) {
	echo($this->element('Pages/include_footer'));
}
?>
</body>
</html>