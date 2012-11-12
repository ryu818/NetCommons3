<?php
$this->assign('title', __('Login'));
$class = "";
if (!$this->request->is('ajax')) {
	$this->extend('/Frame/base');
	$class = " login_base";
}
?>
<?php echo $this->Session->flash('auth'); ?>
<?php echo $this->Form->create('User');?>
<?php
$this->Form->inputDefaults(array(
	'label' => false,
	'div' => false
));
?>
<fieldset class="form<?php echo($class); ?>">
	<ul class="lists">
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
					<div class="login_save_my_info">
						<label for="login_save_my_info">
							<?php echo $this->Form->input('login_save_my_info', array('type' => 'checkbox', 'value' => _ON, 'id' => 'login_save_my_info')); ?>
							&nbsp;<?php echo(__('Save my info?')); ?>
						</label>
					</div>
					<?php endif; ?>
				</dd>
			</dl>
		</li>
	</ul>
	<p class="align-right">
		<?php echo $this->Form->button(__('Login'), array('name' => 'login', 'class' => 'common_btn')); ?>
	</p>
</fieldset>
<?php echo $this->Form->end();?>
<script>
$("#UserLoginId").attr("autocomplete", "<?php echo($login_autocomplete);?>");
$("#UserPassword").attr("autocomplete", "<?php echo($login_autocomplete);?>");
// IE
setTimeout(function(){
	$("#UserLoginId").select();
}, 500);
</script>
