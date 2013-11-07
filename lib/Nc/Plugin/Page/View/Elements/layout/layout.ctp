<?php
/**
 * レイアウト設定
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
	echo $this->element('scope', array('languages' => $languages, 'model_name' => 'PageLayout', 'page_style' => $page_layout, 'page' => $page));
	
	
	if(!isset($page_layout['PageLayout'])) {
		$layouts = '1_1_1_1';
	} else {
		$layouts = intval($page_layout['PageLayout']['is_display_header']).'_'.intval($page_layout['PageLayout']['is_display_left']).'_'.
			intval($page_layout['PageLayout']['is_display_right']).'_'.intval($page_layout['PageLayout']['is_display_footer']);
	}
?>

<fieldset class="form">
	<ul id="<?php echo $id;?>-list" class="lists pages-menu-style-details pages-menu-backgrounds clearfix">
		<?php foreach($layoutFiles as $layoutFile):?>
		<li>
			<?php 
			$src = $this->Html->url('/', true).'page/img/layouts/'.$layoutFile;
			$title = $this->PageLayout->getTitle($layoutFile);
			$fileNameArr = explode('.', $layoutFile);
		
			echo $this->Html->link('', empty($src) ? '#' : $src, array(
				'title' => __d('page', $title),
				'class' => 'pages-menu-layout',
				'data-layout' => $fileNameArr[0],
				'style' => "background-image: url('".$src."');",
				'onclick' => '$.PageStyle.highlightLayout(false,\''.$fileNameArr[0].'\'); return false;',
			));
			
			?>
		</li>
		<?php endforeach; ?>
	</ul>
</fieldset>

<script>
$(function(){
	$.PageStyle.initLayout('<?php echo $id; ?>', '<?php echo $layouts; ?>');
});
</script>
<?php
	echo $this->Form->hidden('PageLayout.layouts' , array('id' => $id. '-hidden', 'value' => ''));
	echo $this->Html->div('submit',
		$this->Form->button(__('Ok'), array('name' => 'regist', 'class' => 'common-btn', 'type' => 'submit', 'onclick' => "$.PageStyle.setConfirm('".$id."', 'submitLayout');")).
		$this->Form->button(__('Reset'), array('name' => 'reset', 'class' => 'common-btn', 'type' => 'submit', 'onclick' => "$.PageStyle.setConfirm('".$id."', 'reset');"))
	);
	echo $this->Form->end();
?>