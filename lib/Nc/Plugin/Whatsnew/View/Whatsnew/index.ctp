<?php
/**
 * 新着メイン画面
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.Whatsnew.View
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
$this->extend('/Frame/block');
?>
<ul>
<?php foreach ($whatsnews as $whatsnew): ?>
	<li>
		<?php
			$content = h($this->Text->truncate(
				$whatsnew['Archive']['content'],
				WHATSNEW_DETAIL_MAX_LENGTH
			));
			echo $this->Html->link($whatsnew['Archive']['title'], $whatsnew['Archive']['url'], array(
				'title' => $content
			));
		?>
		<div style="padding:20px;">
			<?php
				echo $content;
			?>
		</div>
	</li>
<?php endforeach; ?>
</ul>