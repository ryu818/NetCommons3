<?php
/**
 * 背景設定(背景一覧)
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.Page.View
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
?>
<?php foreach ($backgrounds as $key => $background): ?>
	<?php if($limit == $key){break;} ?>
	<li>
		<?php if (preg_match('/\//', $background['Background']['file_path'])): ?>
		<div class="nc-arrow-outer-pos">
			<a onclick="return false;" class="float-right nc-arrow-outer" title="<?php echo __d('page', 'Select type');?>" href="#">
				<span class="nc-arrow"></span>
			</a>
			<?php
				$colorTitle = __d('page', 'Select type'). '&nbsp;['.__d('background', $background['Background']['name']).']';
				$colorId = '#pages-menu-select-color-'. $background['Background']['group_id'];
				if(!empty($category)) {
					$colorId .= '-'. $category;
					$colorTitle .= '&nbsp;['.__d('page', $category).']';
				}
				if(!empty($color)) {
					$colorId .= '-'. $color;
					$colorTitle .= '&nbsp;['.__d('page', $color).']';
				}
				echo $this->Html->link('<span class="nc-arrow"></span>', array('action' => 'color', $background['Background']['group_id'], 'category' => $category, 'color' => $color), array(
					'title' => $colorTitle,
					'class' => 'float-right nc-arrow-outer',
					'data-ajax-dialog' => true,
					'data-ajax-dialog-options' => '{&quot;title&quot; : &quot;'.$this->Js->escape($colorTitle).'&quot;,&quot;position&quot;:&quot;mouse&quot;, &quot;width&quot; : 200}',
					'data-ajax-effect' => 'fold',
					'data-ajax' => $colorId,
					'escape' => false,
				));
			?>
		</div>
		<?php endif; ?>
		<?php 
			if($background['Background']['file_path'] == '') {
				$src = '';
			} else {
				$src = $this->Html->url('/', true).'img/backgrounds/'.$background['Background']['type'].'s/'.$background['Background']['file_path'];
			}
		?>
		<?php
			echo $this->Html->link('', empty($src) ? '#' : $src, array(
				'id' => 'pages-menu-background-group-'.$background['Background']['group_id'],
				'title' => __d('background', $background['Background']['name']),
				'class' => 'pages-menu-background',
				'data-background-id' => $background['Background']['id'],
				'style' => "background-image: url('".$src."');",
				'onclick' => '$.PageStyle.clickBackground(this); return false;',
			));
		?>
	</li>
<?php endforeach; ?>
<?php 
if($type == 'patterns') {
	$hasMore = $has_pattern;
	$page = $pattern_page;
} else {
	$hasMore = $has_image;
	$page = $image_page;
}
?>
<?php if($hasMore): ?>
	<?php echo($this->element('style/more', array('page' => $page+1, 'type' => $type))); ?>
<?php endif; ?>