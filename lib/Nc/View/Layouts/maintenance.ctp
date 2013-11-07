<!DOCTYPE html>
<html>
<head>
<?php echo $this->Html->charset(); ?>
<title><?php echo h(strip_tags($page_title)); ?></title>
<?php echo($this->element('Pages/include_header')); ?>
<?php echo $this->Html->css('redirect/', null, array('inline' => true, 'data-title' => 'Redirect')); ?>

</head>
<body>
<div class="nc-hmenu-maintenance">
<?php
echo $this->Html->link(__('Sign in'), array('controller' => 'users', 'action' => 'login'));
?>
</div>
	<ul class="nc-redirect">
		<li class="nc-redirect-text">
			<?php echo($message); ?>
		</li>
		<?php if(isset($error_id_str) && $error_id_str != ''): ?>
			<li class="nc-redirect-subtext">
				(error:<?php echo($error_id_str); ?>)
			</li>
		<?php endif; ?>
	</ul>
<?php echo $this->element('sql_dump'); ?>
<?php
if(!$this->request->query('_iframe_upload')) {
	echo($this->element('Pages/include_footer'));
}
?>
</body>
</html>