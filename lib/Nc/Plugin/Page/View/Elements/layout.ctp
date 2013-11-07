<?php
/**
 * ページレイアウト
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.Page.View
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
?>
<div class="nc-pages-setting-title nc-panel-color" data-pages-header="true">
	<?php echo(__d('page', 'Page layout')); ?>
</div>
<div class="nc-pages-setting-content">
	<?php
		echo $this->Html->css(array('plugins/colpick.css', 'Page.style/style.css'));
		echo $this->Html->script(array('plugins/colpick.js', 'Page.style/style.js'));
		echo $this->element('layout/layout');
	?>
</div>