<?php
$this->assign('title', __d('install', 'Admin user setting'));
?>
<div class="install">
	<form method="post" action="<?php echo $this->Html->url(array('plugin' => 'install','controller' => 'install','action' => 'admin_setting')); ?>">
		<?php echo($this->element('hidden')); ?>
		<h1><?php echo $this->fetch('title'); ?></h1>
		<div class="top-description">
			<?php __d('install', 'Please choose your site admin&acute;s name and password.'); ?>
		</div>
		<?php
			$lists = array(
				array(
					'id' => 'UserLoginId',
					'title' => __d('install', 'Admin Name'),
					'value' => $this->Form->input('User.login_id', array(
						'label' => '',
						'default' => isset($user['User']['login_id']) ? $user['User']['login_id'] : '',
						'type' => 'text'
					)),
					'error' => 'login_id',
				),
				array(
					'id' => 'UserHandle',
					'title' => __d('install', 'Admin Handle'),
					'value' => $this->Form->input('User.handle', array(
						'label' => '',
						'default' => isset($user['User']['handle']) ? $user['User']['handle'] : '',
					)),
					'error' => 'handle',
				),
				array(
					'id' => 'UserPassword',
					'title' => __d('install', 'Admin Password'),
					'value' => $this->Form->input('User.password', array(
						'label' => '',
						'default' => isset($user['User']['password']) ? $user['User']['password'] : '',
					)),
					'error' => 'password',
				),
				array(
					'id' => 'UserConfirmPassword',
					'title' => __d('install', 'Confirm Password'),
					'value' => $this->Form->input('User.confirm_password', array(
						'label' => '',
						'default' => isset($user['User']['confirm_password']) ? $user['User']['confirm_password'] : '',
						'type' => 'password'
					)),
				),
			);
		?>
		<ul class="lists install-lists">
			<?php foreach($lists as $list): ?>
			<li>
				<dl>
					<dt>
						<label<?php if(isset($list['id'])){echo(' for="'.$list['id'].'"');} ?>>
							<?php echo($list['title']); ?>
						</label>
					</dt>
					<dd>
						<?php if(isset($list['value'])){echo($list['value']);} ?>
						<?php if(isset($list['note'])){echo('<div class="note">'.$list['note'].'</div>');} ?>

					</dd>
				</dl>
			</li>
			<?php endforeach; ?>
		</ul>

		<div class="btn-bottom align-right">
			<input type="submit" value="<?php echo(h(__('Next>>'))); ?>" name="next" class="btn" />
		</div>
	</form>
</div>
<script>
;(function($) {
	$(function(){
		$('#UserLoginId').focus();
	});
})(jQuery);
</script>