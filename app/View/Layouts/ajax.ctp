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
<?php
if($this->request->header('X-PJAX') && $this->request->is('ajax')) {
	echo($this->element('Pages/title'));
}
?>
<?php echo $this->fetch('content'); ?>
<?php 
$flashMes = $this->Session->flash();
if($flashMes) {
	echo '<script>$.Common.flash("'.$this->Js->escape($flashMes).'");</script>';
}
?>
<?php echo $this->element('sql_dump'); ?>
