<?php
/**
 * パンくずリストメイン画面(Apple)
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       Plugin.Announcement.View
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
$this->extend('/Frame/block');
?>
<div class="breadcrumb">
	<ol class="breadcrumb-ol">
		<?php if($pages_list[count($pages_list) - 1]['Page']['permalink'] == ''): ?>
			<li>
				<span>
					<?php echo __('Home');?>
				</span>
			</li>
		<?php else: ?>
			<?php if($pages_list[0]['Page']['permalink'] != ''): ?>
				<li>
				<a href="<?php echo($this->webroot); ?>">
					<?php echo __('Home');?>
				</a>
				&nbsp;&gt;&gt;&nbsp;
				</li>
			<?php endif; ?>
			<?php foreach ($pages_list as $key => $page_list): ?>
				<li>
				<?php if($page_list['Page']['id'] == NC_TOP_PUBLIC_ID || $page_list['Page']['position_flag'] == _OFF || ($page_list['Page']['display_sequence'] == 1 && $page_list['Page']['space_type'] == NC_SPACE_TYPE_PUBLIC)){continue;} ?>
				<?php if(count($pages_list) - 1 > $key ): ?>
					<a href="<?php echo($this->webroot); ?><?php echo(h($page_list['Page']['permalink'])); ?>">
						<?php if($page_list['Page']['permalink'] == ''): ?>
							<?php echo __('Home');?>
						<?php else: ?>
							<?php echo h($page_list['Page']['page_name']); ?>
						<?php endif; ?>
					</a>
				<?php else: ?>
					<?php echo h($page_list['Page']['page_name']); ?>
				<?php endif; ?>
				<?php if(count($pages_list) - 1 > $key ): ?>
					&nbsp;&gt;&gt;&nbsp;
				<?php endif; ?>
				</li>
			<?php endforeach; ?>
			
		<?php endif; ?>
		
	</ol>
</div>
<?php
	echo $this->Html->css('Breadcrumb.index');
?>