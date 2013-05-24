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
$isApprove = false;
if(isset($announcement['Announcement']) && $announcement['Announcement']['status'] != NC_STATUS_TEMPORARY &&
	$announcement['Announcement']['is_approved'] != _ON) {
	$isApprove = true;
}
$url = array('controller' => 'posts', '#' => $id);
if(!empty($announcement['Announcement']['id'])) {
	$url[] = $announcement['Announcement']['id'];
}
?>
<?php if(!empty($announcement['Revision']['content']) && ($is_edit || $announcement['Announcement']['status'] != NC_STATUS_TEMPORARY)): ?>
	<?php if($is_edit): ?>
		<?php if($isApprove): ?>
			<?php
				$approveUrl =  $url;
				$approveUrl['controller'] = 'posts';
				$approveUrl['action'] = 'approve';
				echo $this->Html->link(__('Pending'), $approveUrl, array(
					'title' => __('Pending'),
					'class' => 'nc-button nc-button-red small',
					'data-ajax-inner' =>'#announcement-approve-'.$id,
					'data-ajax-dialog' => true,
					'data-ajax-effect' => 'fold',
					'data-ajax-dialog-options' => '{"title" : "'.$this->Js->escape(__('Pending [%s]', h($announcement['Content']['title']))).'","modal": true, "resizable": true, "autoResize": true, "position":"mouse", "width":"600"}',
					'data-ajax-effect' => 'fold'
				));
			?>
		<?php elseif(isset($announcement['Announcement']) && $announcement['Announcement']['status'] != NC_STATUS_PUBLISH): ?>
			<span class="temporary">
				<?php echo __('Temporary...'); ?>
			</span>
		<?php endif; ?>
		<?php /* 編集メニュー表示 */  ?>
		<a href="#" title="<?php echo __('Display edit menu');  ?>" class="float-right nc-arrow-outer" onclick="$(this).next().slideToggle();return false;"><span class="nc-arrow"></span></a>
		<div class="nc-panel-color nc-arrow-header-link">
		<?php if(isset($announcement['Announcement'])): ?>
			<?php
				/* TODO:後にリンクにする */
				echo (h($announcement['Revision']['created_user_name']));
			?>
			 |
		<?php endif; ?>
		<?php
			echo $this->Html->link(__('Edit'), $url);
		?>
		</div>
	<?php endif; ?>
	<?php echo ($announcement['Revision']['content']);?>
	<?php if($is_edit): ?>
		<?php
			echo $this->Html->script('Announcement.announcement/index');
		?>
		<script>
		$(function(){
			$('#<?php echo($id); ?>').Announcement('<?php echo ($id);?>', '<?php echo($this->Html->url($url)); ?>', "<?php echo($this->Js->escape(__('Double-Click to edit.'))); ?>");
		});
		</script>
	<?php endif; ?>
<?php endif; ?>