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
	'images'=> $images,
	'original'=> '',
	'patterns'=> $patterns,
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
		<?php echo __d('page', 'Background overlay'); ?>
		<?php elseif($itemKey == 'original'): ?>
		<?php echo __d('page', 'Original background'); ?>
		<?php elseif($itemKey == 'color'): ?>
		<?php echo __d('page', 'Background color'); ?>
		<?php else: ?>
		<?php echo __d('page', 'Background images'); ?>
		<?php endif; ?>
	</a></div>
	<div id="<?php echo $id;?>-<?php echo $itemKey;?>">
		<?php if($itemKey == 'color'): ?>
		<div id="<?php echo $id;?>-picker" class="pages-menu-picker"></div>
		<?php elseif($itemKey == 'original'): ?>
		<?php /* オリジナル背景 */ ?>
		<div id="pages-menu-background-original-outer">
			<?php
				if(!empty($page_style['PageStyle']['original_background_image'])) {
					$imageUrl = $this->Html->url('/', true).'nc-downloads/'.$page_style['PageStyle']['original_background_image'];
				} else {
					$imageUrl = '#';
				}
			?>
			<a class="pages-menu-background-original-inner nc-thumbnail" href="<?php echo $imageUrl; ?>">
				<div class="nc-thumbnail-centered">
					<?php
						if(!empty($page_style['PageStyle']['original_background_image'])) {
							echo '<img src="'.$imageUrl.'" />';
						}
					?>
				</div>
			</a>
		</div>
		<?php
			echo $this->Form->button(__('Select file'), array(
				'name' => 'select_file',
				'type' => 'button',
				'class' => 'common-btn pages-menu-select-file-btn',
				'onclick' => "$.Common.showUploadDialog('dialog-".$id."', {'el' : this, 'action' : 'library', 'callback' : function(fileName, url, libraryUrl){\$.PageStyle.selectBackgroundFile(fileName, url, libraryUrl);}});"
			));
		?>
		<fieldset class="form">
			<ul class="nc-lists pages-menu-background-original-ul">
				<li>
					<dl>
						<dt>
							<?php
								$name = 'PageStyle.original_background_position';
								echo $this->Form->label($name, __d('page', 'Position of the image'));
							?>
						</dt>
						<dd>
							<?php
								$options = array();
								$backgroundPositons = explode(',', PAGES_STYLE_BACKGROUND_POSITION);
								foreach($backgroundPositons as $backgroundPositon) {
									$backgroundPositonArr = explode(':', $backgroundPositon);
									$options[$backgroundPositonArr[0]] = __d('page', $backgroundPositonArr[1]);
								}
								$settings = array(
									'id' => 'pages-menu-background-original-position',
									'type' => 'select',
									'options' => $options,
									'value' => empty($page_style['PageStyle']['original_background_position']) ? PAGES_STYLE_BACKGROUND_POSITION_DEFAULT : $page_style['PageStyle']['original_background_position'],
									'label' => false,
									'div' => false,
									'style' => 'width: 120px;',
								);
								echo $this->Form->input($name, $settings);
							?>
						</dd>
					</dl>
				</li>
				<li>
					<dl>
						<dt>
							<?php
								$name = 'PageStyle.original_background_attachment';
								echo $this->Form->label($name, __d('page', 'Fixed background image'));
							?>
						</dt>
						<dd>
							<?php
								$settings = array(
									'type' => 'radio',
									'options' => array('fixed' => __('Yes'), 'scroll' => __('No')),
									'value' => empty($page_style['PageStyle']['original_background_attachment']) ? PAGES_STYLE_BACKGROUND_ATTACHMENT_DEFAULT : $page_style['PageStyle']['original_background_attachment'],
									'div' => false,
									'legend' => false,
								);
								echo $this->Form->input($name, $settings);
							?>
						</dd>
					</dl>
				</li>
			</ul>
		</fieldset>
		<ul class="nc-lists pages-menu-background-original-type clear">
			<?php
				$imageTypes = array(
					"no-repeat" => "Normal",
					"full" => "Full screen",
					"repeat" => "Tile display",
					"repeat-x" => "X repeat",
					"repeat-y" => "Y repeat"
				);
				$repeatValue = empty($page_style['PageStyle']['original_background_repeat']) ? PAGES_STYLE_BACKGROUND_REPEAT_DEFAULT : $page_style['PageStyle']['original_background_repeat'];
			?>
			<?php foreach ($imageTypes as $imageName => $imageValue): ?>
			<?php
				$imageValue = __d('page', $imageValue);
				$class = "";
				if($imageName == $repeatValue) {
					$class = ' class="pages-menu-background-highlight"';
				}
			?>
			<li<?php echo($class); ?>>
				<a data-background-repeat="<?php echo($imageName); ?>" class="nc-tooltip pages-menu-background-original-<?php echo($imageName); ?>" href="#" alt="<?php echo($imageValue); ?>" title="<?php echo($imageValue); ?>">
				</a>
			</li>
			<?php endforeach; ?>
		</ul>
		<?php
		echo $this->Form->hidden('PageStyle.original_background_image' , array('id' => 'pages-menu-background-original-image-hidden', 'value' => $page_style['PageStyle']['original_background_image']));
		echo $this->Form->hidden('PageStyle.original_background_repeat' , array('id' => 'pages-menu-background-original-repeat-hidden', 'value' => $repeatValue));
		?>
		<?php else: ?>
		<fieldset class="form">
			<ul class="nc-lists pages-menu-style-details pages-menu-backgrounds clearfix">
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
	echo $this->Form->hidden('PageStyle.style.body.background-image' , array('id' => $id. '-images-hidden', 'value' => ''));
	echo $this->Form->hidden('PageStyle.style.#parent-container.background-image' , array('id' => $id. '-patterns-hidden', 'value' => ''));
	echo $this->Form->hidden('PageStyle.style.body.background-color' , array('id' => $id. '-color-hidden', 'value' => ''));
	echo $this->Html->div('submit',
		$this->Form->button(__('Ok'), array('name' => 'regist', 'class' => 'common-btn', 'type' => 'submit', 'onclick' => "$.PageStyle.setConfirm('".$id."', 'submit');")).
		$this->Form->button(__('Reset'), array('name' => 'reset', 'class' => 'common-btn', 'type' => 'submit', 'onclick' => "$.PageStyle.setConfirm('".$id."', 'reset');"))
	);
	echo $this->Form->end();
?>