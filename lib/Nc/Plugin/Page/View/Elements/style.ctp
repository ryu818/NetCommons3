<?php
/**
 * ページスタイル
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.Page.View
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
?>

<div class="nc-pages-setting-content">
	<?php
		echo $this->Html->css(array('plugins/colpick.css', 'Page.style/style.css'));
		echo $this->Html->script(array('plugins/colpick.js', 'Page.style/style.js'));
	?>
	<div id="pages-menu-style-init-tab">
		<ul id="pages-menu-style-tab-ul">
			<li><a href="#pages-menu-style-tab-font"><?php echo __d('page', 'Font setting');?></a></li>
			<li><a href="<?php echo $this->Html->url(array('action' => 'background'));?>"><?php echo __d('page', 'Background');?></a></li>
			<li><a href="<?php echo $this->Html->url(array('action' => 'display_position'));?>"><?php echo __d('page', 'Display position');?></a></li>
			<li><a href="<?php echo $this->Html->url(array('action' => 'custom'));?>"><?php echo __d('page', 'Custom setting');?></a></li>
		</ul>
		<div id="pages-menu-style-tab-font">
		<?php
			echo $this->element('style/font', array('category' => 'font', 'languages' => $languages, 'page_style' => $page_style, 'page' => $page));
			echo($this->element('style/template', array('category' => 'font')));
		?>
		</div>
	</div>
</div>
