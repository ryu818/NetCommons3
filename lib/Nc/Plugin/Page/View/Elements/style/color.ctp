<?php
/**
 * 色選択
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.Page.View
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
?>
<div class="pages-menu-background-color">
	<ul>
	<?php foreach ($backgrounds as $background): ?>
	<li>
		<?php 
			$src = $this->Html->url('/', true).'img/backgrounds/'.$background['Background']['type'].'s/'.$background['Background']['file_path'];
		?>
		<?php
			echo $this->Html->link('', $src, array(
				'class' => 'pages-menu-background-color-link',
				'title' => __d('background', $background['Background']['name']),
				'data-background-id' => $background['Background']['id'],
				'data-background-group-id' => $background['Background']['group_id'],
				'style' => "background-image: url('".$src."');",
				'onclick' => '$.PageStyle.clickBackgroundSub(this); return false;',
			));
		?>
	</li>
	<?php endforeach; ?>
	</ul>
</div>