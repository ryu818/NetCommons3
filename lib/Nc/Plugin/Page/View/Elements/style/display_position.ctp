<?php
/**
 * 表示位置設定
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

?>
<fieldset class="form">
	<ul class="nc-lists pages-menu-style-details">
		<li><dl>
			<dt>
				<?php
					$name = 'PageStyle.align';
					echo $this->Form->label($name, __d('page', 'Align'));
				?>
			</dt>
			<dd>
				<?php
					$aligns = explode(',', PAGES_STYLE_ALIGN);
					$options = array();
					foreach($aligns as $align) {
						$alignArr = explode(':', $align);
						$options[$alignArr[0]] = __d('page', $alignArr[1]);
					}

					$settings = array(
						'type' => 'select',
						'options' => $options,
						'value' => !empty($page_style['PageStyle']['align']) ? $page_style['PageStyle']['align'] : '',
						'label' => false,
						'div' => false,
						'style' => 'width: 140px;',
					);
					echo $this->Form->input($name, $settings);
				?>
			</dd>
		</dl></li>
		<li><dl>
			<dt>
				<?php
					$name = 'PageStyle.width';
					echo $this->Form->label($name, __d('page', 'Minimal width'));
				?>
			</dt>
			<dd>
				<?php
					$sizes = explode(',', PAGES_STYLE_WIDTH_SIZE);
					$options = array();
					foreach($sizes as $size) {
						$sizeArr = explode(':', $size);
						$options[$sizeArr[0]] = __d('page', $sizeArr[1]);
					}
					if($page_style['PageStyle']['width'] == 'auto' || $page_style['PageStyle']['width'] == '') {
						$value = 'auto';
					} else if($page_style['PageStyle']['width'] == '100%') {
						$value = '100%';
					} else {
						$value = 'by hand';
					}
					$settings = array(
						'type' => 'select',
						'options' => $options,
						'value' =>  $value,
						'label' => false,
						'div' => false,
						'style' => 'width: 100px;',
					);
					echo $this->Form->input($name, $settings);

					$settings = array(
						'type' => 'text',
						'value' => ($value == 'by hand') ? $page_style['PageStyle']['width'] : '',
						'label' => false,
						'div' => false,
						'class' => 'pages-menu-style-size',
						'maxlength' => 5,
						'error' => array('attributes' => array(
							'selector'
						)),
					);
					$name .= '-custom';
					echo $this->Form->input($name, $settings);
					echo '<span class="pages-menu-style-px">'.__d('page', 'px').'</span>';
				?>
			</dd>
		</dl></li>
		<li><dl>
			<dt>
				<?php
					$name = 'PageStyle.height';
					echo $this->Form->label($name, __d('page', 'Minimal height'));
				?>
			</dt>
			<dd>
				<?php
					$sizes = explode(',', PAGES_STYLE_HEIGHT_SIZE);
					$options = array();
					foreach($sizes as $size) {
						$sizeArr = explode(':', $size);
						$options[$sizeArr[0]] = __d('page', $sizeArr[1]);
					}
					$value = ($page_style['PageStyle']['height'] == 'auto') ? $page_style['PageStyle']['height'] : 'by hand';
					if($page_style['PageStyle']['height'] == 'auto' || $page_style['PageStyle']['height'] == '') {
						$value = 'auto';
					} else {
						$value = 'by hand';
					}

					$settings = array(
						'type' => 'select',
						'options' => $options,
						'value' => $value,
						'label' => false,
						'div' => false,
						'style' => 'width: 100px;',
					);
					echo $this->Form->input($name, $settings);

					$settings = array(
						'type' => 'text',
						'value' => ($value == 'by hand') ? $page_style['PageStyle']['height'] : '',
						'label' => false,
						'div' => false,
						'class' => 'pages-menu-style-size',
						'maxlength' => 5,
						'error' => array('attributes' => array(
							'selector'
						)),
					);
					$name .= '-custom';
					echo $this->Form->input($name, $settings);
					echo '<span class="pages-menu-style-px">'.__d('page', 'px').'</span>';
				?>
			</dd>
		</dl></li>
		<li><dl>
			<dt>
				<?php
					$name = 'PageStyle.style.#container.margin';
					echo $this->Form->label($name.'-top', __d('page', 'Margin'));
				?>
			</dt>
			<dd>
				<div class="pages-menu-style-margin">
				<?php
					if(isset($page_style['PageStyle']['style']['#container']['margin-top']) && $page_style['PageStyle']['style']['#container']['margin-top'] != 'auto') {
						$value = preg_replace('/px$/', '', $page_style['PageStyle']['style']['#container']['margin-top']);
					} else {
						$value = '0';
					}
					echo $this->Form->label($name.'-top', __d('page', 'Top'));
					$settings = array(
						'type' => 'text',
						'value' => $value,
						'label' => false,
						'div' => false,
						'class' => 'pages-menu-style-size',
						'maxlength' => 4,
						'error' => array('attributes' => array(
							'selector'
						))
					);
					echo $this->Form->input($name.'-top', $settings);
					echo '<span class="pages-menu-style-px">'.__d('page', 'px').'</span>';
				?>
				</div><div class="pages-menu-style-margin">
				<?php
					if(isset($page_style['PageStyle']['style']['#container']['margin-right']) && $page_style['PageStyle']['style']['#container']['margin-right'] != 'auto') {
						$value = preg_replace('/px$/', '', $page_style['PageStyle']['style']['#container']['margin-right']);
					} else {
						$value = '0';
					}
					echo $this->Form->label($name.'-right', __d('page', 'Right'));
					$settings = array(
						'type' => 'text',
						'value' => $value,
						'label' => false,
						'div' => false,
						'class' => 'pages-menu-style-size',
						'maxlength' => 4,
						'error' => array('attributes' => array(
							'selector'
						)),
					);
					echo $this->Form->input($name.'-right', $settings);
					echo '<span class="pages-menu-style-px">'.__d('page', 'px').'</span>';
				?>
				</div><div class="pages-menu-style-margin">
				<?php
					if(isset($page_style['PageStyle']['style']['#container']['margin-bottom']) && $page_style['PageStyle']['style']['#container']['margin-bottom'] != 'auto') {
						$value = preg_replace('/px$/', '', $page_style['PageStyle']['style']['#container']['margin-bottom']);
					} else {
						$value = '0';
					}
					echo $this->Form->label($name.'-bottom', __d('page', 'Bottom'));
					$settings = array(
						'type' => 'text',
						'value' => $value,
						'label' => false,
						'div' => false,
						'class' => 'pages-menu-style-size',
						'maxlength' => 4,
						'error' => array('attributes' => array(
							'selector'
						))
					);
					echo $this->Form->input($name.'-bottom', $settings);
					echo '<span class="pages-menu-style-px">'.__d('page', 'px').'</span>';
				?>
				</div><div class="pages-menu-style-margin">
				<?php
					if(isset($page_style['PageStyle']['style']['#container']['margin-left']) && $page_style['PageStyle']['style']['#container']['margin-left'] != 'auto') {
						$value = preg_replace('/px$/', '', $page_style['PageStyle']['style']['#container']['margin-left']);
					} else {
						$value = '0';
					}
					echo $this->Form->label($name.'-left', __d('page', 'Left'));
					$settings = array(
						'type' => 'text',
						'value' => $value,
						'label' => false,
						'div' => false,
						'class' => 'pages-menu-style-size',
						'maxlength' => 4,
						'error' => array('attributes' => array(
							'selector'
						)),
					);
					echo $this->Form->input($name.'-left', $settings);
					echo '<span class="pages-menu-style-px">'.__d('page', 'px').'</span>';
				?>
				</div>
			</dd>
		</dl></li>
	</ul>
</fieldset>
<script>
	$(function(){
		$.PageStyle.initDisplayPosition('<?php echo $id; ?>', <?php if($this->request->is('post')): ?>true<?php else: ?>false<?php endif;?>);
	});
</script>
<?php
	echo $this->Html->div('submit',
		$this->Form->button(__('Ok'), array('name' => 'regist', 'class' => 'common-btn', 'type' => 'submit', 'onclick' => "$.PageStyle.setConfirm('".$id."', 'submit');")).
		$this->Form->button(__('Reset'), array('name' => 'reset', 'class' => 'common-btn', 'type' => 'submit', 'onclick' => "$.PageStyle.setConfirm('".$id."', 'reset');"))
	);
	echo $this->Form->end();
?>