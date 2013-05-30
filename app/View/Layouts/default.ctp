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
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
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
	<?php echo $this->fetch('content'); ?>
	<?php echo($this->element('Pages/include_footer')); ?>
	<?php echo $this->element('flash_mes'); ?>
	<?php echo $this->element('sql_dump'); ?>
</body>
</html>