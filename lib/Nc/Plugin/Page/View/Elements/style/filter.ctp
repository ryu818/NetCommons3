<?php
/**
 * 背景-絞り込みセレクトボックス
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.Page.View
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
?>
<div class="pages-menu-backgrounds-filter">
<?php
	echo __d('page', 'Filter');
	$colors = explode(',', NC_PAGES_BACKGROUND_COLOR_STYLE);
	$colorOptions = array('all' => __d('page', 'Color'));
	foreach($colors as $bufColor) {
		$colorOptions[$bufColor] = __d('page', $bufColor);
	}
	$settings = array(
		'id' => 'pages-menu-backgrounds-colors',
		'type' => 'select',
		'options' => $colorOptions,
		'value' => isset($color) ? $color : 'All',
		'label' => false,
		'div' => false,
		'style' => 'width: 80px;',
	);
	echo $this->Form->input('Background.color', $settings);

	$categories = explode(',', NC_PAGES_BACKGROUND_CATEGORY_STYLE);
	$categoryOptions = array('all' => __d('page', 'Category'));
	foreach($categories as $bufCategory) {
		$categoryOptions[$bufCategory] = __d('page', $bufCategory);
	}
	$settings = array(
		'id' => 'pages-menu-backgrounds-categories',
		'type' => 'select',
		'options' => $categoryOptions,
		'value' => isset($category) ? $category : 'All',
		'label' => false,
		'div' => false,
		'style' => 'width: 120px;',
	);
	echo $this->Form->input('Background.category', $settings);
	echo $this->Form->hidden('Background.patterns_page' , array('value' => 1));
	echo $this->Form->hidden('Background.images_page' , array('value' => 1));
	
?>
</div>