<?php if($pages_list[count($pages_list) - 1]['Page']['permalink'] == ''): ?>
	<?php echo(__('Home')); return;?>
<?php endif; ?>

<?php foreach ($pages_list as $key => $page_list): ?>
	<?php if($page_list['Page']['id'] == NC_TOP_PUBLIC_ID || $page_list['Page']['position_flag'] == _OFF || ($page_list['Page']['display_sequence'] == 1 && $page_list['Page']['space_type'] == NC_SPACE_TYPE_PUBLIC)){continue;} ?>
	<?php if(count($pages_list) - 1 > $key ): ?>
		<a href="<?php echo($this->webroot); ?><?php echo(h($page_list['Page']['permalink'])); ?>">
			<?php if($page_list['Page']['permalink'] == ''): ?>
				<?php echo(__('Home'));?>
			<?php else: ?>
				<?php echo(h($page_list['Page']['page_name'])); ?>
			<?php endif; ?>
		</a>
	<?php else: ?>
		<?php echo(h($page_list['Page']['page_name'])); ?>
	<?php endif; ?>
	<?php if(count($pages_list) - 1 > $key ): ?>
		&nbsp;&gt;&gt;&nbsp;
	<?php endif; ?>
<?php endforeach; ?>