<?php
/**
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       Cake.View.Layouts
 * @since         CakePHP(tm) v 0.10.0.1076
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
?>
<!DOCTYPE html>
<html>
<head>
<?php echo $this->Html->charset(); ?>
<?php echo($this->element('Pages/title')); ?>
<?php echo($this->element('Pages/include_header')); ?>
</head>
<body>
<div id="parent-container">
	<?php echo $this->fetch('content'); ?>
	<?php
	if(!$this->request->query('_iframe_upload')) {
		echo($this->element('Pages/include_footer'));
	}
	?>
	<?php echo $this->element('flash_mes'); ?>
</div>
</body>
</html>
