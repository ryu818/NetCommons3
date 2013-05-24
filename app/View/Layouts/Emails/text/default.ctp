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
 * @package       Cake.View.Layouts.Emails.text
 * @since         CakePHP(tm) v 0.10.0.1076
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
?>
<?php
$header = Configure::read(NC_CONFIG_KEY.'.'.'mailheader');
if($header != "") {
	echo $header."\n\n";
}
?>
<?php echo $this->fetch('content');?>
<?php
$footer = Configure::read(NC_CONFIG_KEY.'.'.'mailfooter');
if($footer != "") {
	echo "\n\n".$footer;
}
?>
