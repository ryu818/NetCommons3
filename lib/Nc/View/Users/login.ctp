<?php
$this->assign('title', __('Sign in'));
$class = "";
if (!$this->request->is('ajax') && empty($this->request->named['popup'])) {
	$this->extend('/Frame/base');
	$class = " login-base";
}
?>
<?php echo $this->Session->flash('auth'); ?>
<?php echo $this->Form->create('User', array('url'=>array('popup'=>_OFF)));?>
<?php
$this->Form->inputDefaults(array(
	'label' => false,
	'div' => false
));
?>
<fieldset class="form<?php echo($class); ?>">
	<ul class="nc-lists">
		<li>
			<dl>
				<dt>
					<label for="UserLoginId">
						<?php echo(__('Login ID')); ?>
					</label>
				</dt>
				<dd>
					<?php echo $this->Form->input('login_id', array('type' => 'text', 'value' => (isset($this->data['User']['login_id']) ? $this->data['User']['login_id'] : ''))); ?>
				</dd>
			</dl>
		</li>
		<li>
			<dl>
				<dt>
					<label for="UserPassword">
						<?php echo(__('Password')); ?>
					</label>
				</dt>
				<dd>
					<?php echo $this->Form->input('password', array('value' => (isset($this->data['User']['password']) ? $this->data['User']['password'] : ''))); ?>
					<?php if($autologin_use == NC_AUTOLOGIN_ON): ?>
					<div class="login-save-my-info">
						<label for="login-save-my-info">
							<?php echo $this->Form->input('login_save_my_info', array('type' => 'checkbox', 'value' => _ON, 'id' => 'login-save-my-info')); ?>
							&nbsp;<?php echo(__('Stay signed in.')); ?>
						</label>
					</div>
					<?php endif; ?>
				</dd>
			</dl>
		</li>
	</ul>
	<p class="align-right">
		<?php echo $this->Form->button(__('Sign in'), array('name' => 'login', 'class' => 'common-btn')); ?>
	</p>
</fieldset>
<?php echo $this->Form->end();?>
<script>
$(function(){
$("#UserLoginId").attr("autocomplete", "<?php echo($login_autocomplete);?>");
$("#UserPassword").attr("autocomplete", "<?php echo($login_autocomplete);?>");
// IE
setTimeout(function(){
	$("#UserLoginId").select();
}, 500);
});
</script>
