<?php
/**
 * 背景設定(検索結果)
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.Page.View
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
?>
<?php if($this->request->data['type'] != 'images_search'):?>
<ul class="pages-menu-patterns-search">
	<?php echo $this->element('style/background_items', array('backgrounds' => $patterns, 'type' => 'patterns')); ?>
</ul>
<?php endif; ?>
<?php if($this->request->data['type'] != 'patterns_search'):?>
<ul class="pages-menu-images-search">
	<?php echo $this->element('style/background_items', array('backgrounds' => $images, 'type' => 'images')); ?>
</ul>
<?php endif; ?>