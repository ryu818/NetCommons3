<?php
/**
 * CSS編集
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.Page.View
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
?>
<?php
echo $this->Form->create('PageStyle', array(
	'data-ajax' => 'this',
	'data-ajax-confirm' => __d('page', 'You change setting. Are you sure to proceed?'),
	'data-confirm-reset' => __d('page', 'You cancel applied setting now.? Are you sure to proceed?'),
));
echo $this->Form->error('PageStyle.type');
echo $this->element('scope', array('languages' => $languages, 'model_name' => 'PageStyle', 'page_style' => $page_style, 'page' => $page));

$name = 'PageStyle.css';
$settings = array(
	'type' => 'textarea',
	'id' => 'pages-menu-edit-css-textarea',
	'escape' => false,
	'value' => $page_style['PageStyle']['content'],
	'label' => false,
	'error' => array('attributes' => array(
		'selector' => true
	))
);
echo $this->Form->input($name, $settings);
?>

<script>
	$(function(){
		$.PageStyle.initEditCss('<?php echo $id; ?>');
	});
</script>
<?php
	echo $this->Html->div('submit',
		$this->Form->button(__('Ok'), array('name' => 'regist', 'class' => 'nc-common-btn', 'type' => 'submit', 'onclick' => "$.PageStyle.setConfirm('".$id."', 'submit');")).
		$this->Form->button(__('Reset'), array('name' => 'reset', 'class' => 'nc-common-btn', 'type' => 'submit', 'onclick' => "$.PageStyle.setConfirm('".$id."', 'reset');"))
	);
	echo $this->Form->end();
?>