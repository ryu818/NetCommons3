<?php
/**
 * お知らせメイン画面
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.Announcement.View
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
$this->extend('/Frame/block');
?>
<?php if(!empty($announcement['Revision']['content'])): ?>
	<?php if(isset($announcement['Revision']['revision_name']) && $announcement['Revision']['revision_name'] == 'auto-draft'): ?>
		<?php if($is_edit): ?>
			<?php /* 自動保存のものは表示しない */ ?>
			<span class="nc-auto-draft"><?php echo __('Editing...'); ?></span>
		<?php endif; ?>
	<?php else: ?>
		<?php echo ($announcement['Revision']['content']);?>
	<?php endif; ?>
	<?php if($is_edit): ?>
	<?php
		echo $this->Html->script('Announcement.announcement/index');

		$url = array('controller' => 'posts', '#' => $id);
		if(!empty($announcement['Announcement']['id'])) {
			$url[] = $announcement['Announcement']['id'];
		}
	?>
	<script>
	$(function(){
		$('#<?php echo($id); ?>').Announcement('<?php echo ($id);?>', '<?php echo($this->Html->url($url)); ?>');
	});
	</script>
	<?php endif; ?>
<?php endif; ?>