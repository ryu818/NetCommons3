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
					<input type="text" id="UserLoginId" name="data[User][login_id]" value="<?php if(isset($this->data['User']['login_id'])){ echo($this->data['User']['login_id']); } ?>" />
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
					<input type="password" id="UserPassword" name="data[User][password]" value="<?php if(isset($this->data['User']['password'])){ echo($this->data['User']['password']); } ?>" />
					<?php if($autologin_use == NC_AUTOLOGIN_ON): ?>
					<div class="login_save_my_info">
						<label for="login_save_my_info">
							<input type="checkbox" id="login_save_my_info" value="1" name="data[User][login_save_my_info]" />&nbsp;<?php echo(__('Save my info?')); ?>
						</label>
					</div>
					<?php endif; ?>
				</dd>
			</dl>
		</li>
	</ul>
	<p>
		<input type="submit" value="<?php echo(__('Login')); ?>" name="login" class="common_btn" />
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
