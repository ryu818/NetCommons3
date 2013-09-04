<?php
/**
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.View.Errors
 * @since         CakePHP(tm) v 0.10.0.1076
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
?>
<?php echo $this->Html->css('redirect/', null, array('inline' => true, 'data-title' => 'Redirect')); ?>
	<ul class="nc-redirect">
		<li class="nc-exception-text">
			<?php echo($name);?>
		</li>
		<li class="nc-redirect-subtext">
			<?php
			if (Configure::read('debug') > 0) {
				$file = str_replace(array(CAKE_CORE_INCLUDE_PATH, ROOT), '', $error->getFile());
				echo(sprintf('%s (line %s)', $file, $error->getLine()));
			}
			?>
		</li>
		<li class="nc-redirect-subtext">
			<?php echo __d('cake', 'An Internal Error Has Occurred.'); ?>
		</li>
	</ul>
<?php
if (Configure::read('debug') > 0 ):
	echo $this->element('exception_stack_trace');
endif;
?>
