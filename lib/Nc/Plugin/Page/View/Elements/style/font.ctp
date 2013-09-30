<?php
/**
 * フォント設定
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.Page.View
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
?>
<?php
// フォント
$font_items = array(
	'title' => __d('page', 'Text body'),
	'font-family' => __d('page', 'Font'),
	'font-size' => __d('page', 'Font size'),
	'line-height' => __d('page', 'Line height'),
	'color' => __d('page', 'Font color')
);
// 見出し
$h1_items = array(
	'title' => __d('page', 'Heading'),
	'font-family' => __d('page', 'Font'),
	'font-size' => __d('page', 'Font size'),
	'color' => __d('page', 'Font color')
);
// リンク
$a_items = array(
	'title' => __d('page', 'Link'),
	'color' => __d('page', 'Font color')
);
// 水平線
$horizon_items = array(
	'title' => __d('page', 'Horizon'),
	'border-top-color' => __d('page', 'Border color'),
	'border-top-style' => __d('page', 'Border style')
);

$items = array(
	'body'=> $font_items,
	'h1'=> $h1_items,
	'a'=> $a_items,
	'hr'=> $horizon_items
);
echo $this->Form->create('PageStyle', array(
	'data-ajax' => 'this',
	'data-ajax-confirm' => __d('page', 'You change setting. Are you sure to proceed?'),
	'data-confirm-reset' => __d('page', 'You cancel applied setting now.? Are you sure to proceed?'),
));
echo $this->Form->error('PageStyle.type');
echo $this->element('scope', array('languages' => $languages, 'page_style' => isset($page_styles[NC_PAGE_TYPE_FONT_ID]) ? $page_styles[NC_PAGE_TYPE_FONT_ID] : null, 'page' => $page));
?>
<div id="pages-menu-style-font-accordion">
<?php foreach ($items as $itemKey => $item): ?>
	<div><a href="#"><?php echo(h($item['title'])); ?></a></div>
	<div>
		<fieldset class="form">
			<ul class="lists pages-menu-style-details">
			<?php foreach ($item as $key => $value): ?>
				<?php 
					// bodyのfont-sizeはYUI Font を使用しているため使用できない。代わりに#containerを使用
					if($itemKey == 'body' && $key == 'font-size'){$itemKey = '#container';} else if($itemKey == '#container'){$itemKey = 'body';}
				 ?>
				
				
				<?php if($key == 'title'){continue;} ?>
				<li><dl>
				<?php if ($key == 'font-family'): ?>
					<dt>
						<?php
							$name = 'PageStyle.style.'.$itemKey.'.'.$key;
							echo $this->Form->label($name, $value);
						?>
					</dt>
					<dd>
						<?php
							$fonts = explode(',', PAGES_STYLE_FONT);
							$options = array();
							foreach($fonts as $font) {
								$options[$font] = __d('page', $font);
							}
	
							$settings = array(
								'type' => 'select',
								'options' => $options,
								'value' => '',
								'label' => false,
								'div' => false,
								'style' => 'width: 170px;',
							);
							echo $this->Form->input($name, $settings);
						?>
					</dd>
				<?php elseif ($key == 'font-size'): ?>
					<dt>
						<?php
							$name = 'PageStyle.style.'.$itemKey.'.'.$key;
							echo $this->Form->label($name, $value);
						?>
					</dt>
					<dd>
						<?php
						
							$options = array();
							if($itemKey == '#container') {
								$fontSizes = explode(',', PAGES_STYLE__BODY_FONT_SIZE);
								foreach($fontSizes as $fontSize) {
									$fontSizeArr = explode(':', $fontSize);
									$options[$fontSizeArr[0]] = __d('page', $fontSizeArr[1]);
								}
							} else {
								$fontSizes = explode(',', PAGES_STYLE_FONT_SIZE);
								foreach($fontSizes as $fontSize) {
									$options[$fontSize] = $fontSize;
								}
							}
							$settings = array(
								'type' => 'select',
								'options' => $options,
								'value' => '',
								'label' => false,
								'div' => false,
								'style' => 'width: 150px;',
							);
							echo $this->Form->input($name, $settings);
						?>
					</dd>
				<?php elseif ($key == 'line-height'): ?>
					<dt>
						<?php
							$name = 'PageStyle.style.article.'.$key;
							echo $this->Form->label($name, $value);
						?>
					</dt>
					<dd>
						<?php
							$lineHeights = explode(',', PAGES_STYLE_LINE_HEIGHT);
							$options = array();
							foreach($lineHeights as $lineHeight) {
								$options[$lineHeight] = __d('page', $lineHeight);
							}
	
							$settings = array(
								'type' => 'select',
								'options' => $options,
								'value' => '',
								'label' => false,
								'div' => false,
								'style' => 'width: 150px;',
							);
							echo $this->Form->input($name, $settings);
						?>
					</dd>
				<?php elseif ($key == 'color' || $key == 'border-top-color'): ?>
					<dt>
						<?php
							$name = 'PageStyle.style.'.$itemKey.'.'.$key;
							echo $this->Form->label($name, $value);
						?>
					</dt>
					<dd>
						<?php
							$settings = array(
								'type' => 'text',
								'value' => '',
								'label' => false,
								'div' => false,
								'class' => 'pages-menu-style-colorpicker',
								'maxlength' => 7,
								'error' => array('attributes' => array(
									'selector'
								))
							);
							echo $this->Form->input($name, $settings);
						?>
					</dd>
				<?php elseif ($key == 'border-top-style'): ?>
					<dt>
						<?php
							$name = 'PageStyle.style.'.$itemKey.'.'.$key;
							echo $this->Form->label($name, $value);
						?>
					</dt>
					<dd>
						<?php
							$options = array();
							$borderStyles = explode(',', PAGES_STYLE_BORDER_STYLE);
							foreach($borderStyles as $borderStyle) {
								$options[$borderStyle] = $borderStyle;
							}
							$settings = array(
								'class' => 'pages-menu-style-border-style',
								'type' => 'select',
								'options' => $options,
								'value' => '',
								'label' => false,
								'div' => false,
								'style' => 'width: 140px;',
							);
							echo $this->Form->input($name, $settings);
						?>
					</dd>
				<?php endif; ?>
				</dl></li>
			<?php endforeach; ?>
			</ul>
		</fieldset>
		
	</div>
<?php endforeach; ?>
<?php /* 設定するタグがあるかもしれないため非表示で設定 */; ?>
<div id="pages-menu-style-visible-hide" class="visible-hide"><h1></h1><article></article><hr /><a></a></div>
</div>
<script>
	$(function(){
		$.PageStyle.initFont('<?php echo $id; ?>');
	});
</script>
<?php
	echo $this->Form->hidden('type' , array('id' => $id. '-type', 'name' => 'type', 'value' => 'submit'));
	echo $this->Html->div('submit',
		$this->Form->button(__('Ok'), array('name' => 'regist', 'class' => 'common-btn', 'type' => 'submit', 'onclick' => "$.PageStyle.setConfirm('".$id."', 'submit');")).
		$this->Form->button(__('Reset'), array('name' => 'reset', 'class' => 'common-btn', 'type' => 'submit', 'onclick' => "$.PageStyle.setConfirm('".$id."', 'reset');"))
	);
	echo $this->Form->end();
?>