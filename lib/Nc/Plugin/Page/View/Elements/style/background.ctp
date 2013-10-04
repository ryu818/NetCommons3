<?php
/**
 * 背景設定
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.Page.View
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
?>
<?php
$items = array(
	'patterns'=> $patterns,
	'images'=> $images,
	'color'=> '',
);
echo $this->Form->create('Background', array(
	'data-ajax' => 'this',
	'data-ajax-confirm' => __d('page', 'You change setting. Are you sure to proceed?'),
	'data-confirm-reset' => __d('page', 'You cancel applied setting now.? Are you sure to proceed?'),
));
echo $this->Form->error('PageStyle.type');
echo $this->element('scope', array('languages' => $languages, 'model_name' => 'PageStyle', 'page_style' => $page_style, 'page' => $page));
echo $this->element('style/filter');
?>
<div id="<?php echo $id;?>-accordion">
<?php foreach ($items as $itemKey => $backgrounds): ?>
	<div><a href="#">
		<?php if($itemKey == 'patterns'): ?>
		<?php echo __d('page', 'Background patterns'); ?>
		<?php elseif($itemKey == 'color'): ?>
		<?php echo __d('page', 'Background color'); ?>
		<?php else: ?>
		<?php echo __d('page', 'Background images'); ?>
		<?php endif; ?>
	</a></div>
	<div id="<?php echo $id;?>-<?php echo $itemKey;?>">
		<?php if($itemKey == 'color'): ?>
		<div id="<?php echo $id;?>-picker" class="pages-menu-picker"></div>
		<?php else: ?>
		<fieldset class="form">
			<ul class="lists pages-menu-style-details pages-menu-backgrounds clearfix">
				<?php echo $this->element('style/background_items', array('backgrounds' => $backgrounds, 'type' => $itemKey)); ?>
			</ul>
		</fieldset>
		<?php endif; ?>
	</div>
<?php endforeach; ?>
</div>
<script>
	$(function(){
		$.PageStyle.initBackground('<?php echo $id; ?>');
	});
</script>
<?php
	echo $this->Form->hidden('PageStyle.style.body.background-image' , array('id' => $id. '-image-hidden', 'value' => ''));
	echo $this->Form->hidden('PageStyle.style.body.background-color' , array('id' => $id. '-color-hidden', 'value' => ''));
	echo $this->Html->div('submit',
		$this->Form->button(__('Ok'), array('name' => 'regist', 'class' => 'common-btn', 'type' => 'submit', 'onclick' => "$.PageStyle.setConfirm('".$id."', 'submit');")).
		$this->Form->button(__('Reset'), array('name' => 'reset', 'class' => 'common-btn', 'type' => 'submit', 'onclick' => "$.PageStyle.setConfirm('".$id."', 'reset');"))
	);
	echo $this->Form->end();
?>