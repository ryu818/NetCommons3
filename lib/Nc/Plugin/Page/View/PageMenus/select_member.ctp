<?php
	$queryOptions = array(
		'url' => array('top_id' => h($this->request->named['top_id']), '#' => 'user-search-result-main-'.$id),
'onclick' => "$('#Form".h($this->request->named['top_id'])."').attr('action', $(this).attr('href')).submit(); return false;",
//'onclick' => '$(\'#Form'.h($this->request->named['top_id'].'\').attr(\'action\', $(this).attr(\'href\')).submit();',
	);
?>
<?php echo($this->element('/common/paginator', array('options' => $queryOptions))); ?>
<ul class="pages-menu-invite-community-search-list clearfix">
	<?php foreach ($users as $user): ?>
	<li data-handle="<?php echo h($user['User']['handle']); ?>">
		<?php if(empty($user['PageUserLink']['id'])): ?>
		<a href="#" onclick="$.PageInviteCommunity.selectMemberSearch('<?php echo '#pages-menus-invite-member-'.$page['Page']['id']; ?>', event, this, '<?php echo($this->Js->escape($user['User']['handle'])); ?>');">
		<?php endif; ?>
		<div class="pages-menu-community-inf-photo pages-menu-invite-community-search-photo">
			<div class="nc-thumbnail-centered">
				<?php
					if(empty($user['User']['avatar'])) {
						$imageUrl = $this->Html->url('/', true).'user/img/avatar_thumbnail.gif';
					} else {
						$avatarArr = explode('.', $user['User']['avatar']);
						$imageUrl = $this->Html->url('/', true).'nc-downloads/'.$avatarArr[0].'_thumbnail.'.$avatarArr[1];
					}
					echo '<img src="'.$imageUrl.'" />';
				?>
			</div>
		</div>
		<div class="pages-menu-invite-community-search-handle">
			<?php echo h($user['User']['handle']); ?>
		</div>
		<?php if(!empty($user['PageUserLink']['id'])): ?>
		<div class="pages-menu-invite-community-search-participating">
			<?php echo __d('page', 'Participating'); ?>
		</div>
		<?php else: ?>
		<div class="pages-menu-invite-community-search-participating">
			<?php echo __d('page', 'Selected'); ?>
		</div>
		</a>
		<?php endif; ?>
	</li>
	<?php endforeach; ?>
</ul>
<?php echo($this->element('/common/paginator', array('options' => $queryOptions))); ?>
<script>
	$(function(){
		$.PageInviteCommunity.selectMemberSearchInit('<?php echo '#user-search-result-main-'.h($this->request->named['top_id']); ?>', '<?php echo '#pages-menus-invite-member-'.$page['Page']['id']; ?>');
	});
	</script>