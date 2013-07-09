<?php
$this->extend('/Frame/block');
?>
<div id="blog-edits<?php echo($id); ?>" style="display:none;">
	<?php
		echo $this->Form->create('Blog', array('data-pjax' => '#'.$id));
	?>
	<div id="blog-edits-tab<?php echo($id); ?>">
		<ul>
			<li><a href="#blog-edits-tab-init<?php echo($id); ?>"><span><?php echo(__('General setting'));?></span></a></li>
			<li><a href="#blog-edits-tab-comment<?php echo($id); ?>"><span><?php echo(__d('blog', 'Comment setting'));?></span></a></li>
			<li><a href="#blog-edits-tab-trackback<?php echo($id); ?>"><span><?php echo(__d('blog', 'Trackback setting'));?></span></a></li>
			<li><a href="#blog-edits-tab-approval<?php echo($id); ?>"><span><?php echo(__('Approved Settings'));?></span></a></li>
		</ul>
		<div id="blog-edits-tab-init<?php echo($id); ?>">
			<?php /* 一般設定 */ ?>
			<fieldset class="form">
				<ul class="lists nc-edits-lists">
					<li>
						<dl>
							<dt>
								<?php
									echo $this->Form->label('Content.title', __d('blog', 'Blog name'));
								?>
							</dt>
							<dd>
								<?php
									$settings = array(
										'type' => 'text',
										'value' => $block['Content']['title'],
										'label' => false,
										'div' => false,
										'maxlength' => NC_VALIDATOR_BLOCK_TITLE_LEN,
										'class' => 'nc-title',
										'size' => 35,
										'error' => array('attributes' => array(
											'selector' => true
										))
									);
									echo $this->Form->input('Content.title', $settings);
								?>
							</dd>
						</dl>
					</li>
					<li>
						<dl>
							<dt>
								<?php
									echo $this->Form->label('Blog.post_hierarchy', __('Authority to post root articles'));
								?>
							</dt>
							<dd>
								<?php
									echo $this->Form->authoritySlider('Blog.post_hierarchy', array('value' => $blog['Blog']['post_hierarchy']));
								?>
							</dd>
						</dl>
					</li>
					<li>
						<dl>
							<dt>
								<?php
									echo $this->Form->label('Blog.term_hierarchy', __d('blog', 'Authority to allow addition of a new category,tag.'));
								?>
							</dt>
							<dd>
								<?php
									echo $this->Form->authoritySlider('Blog.term_hierarchy', array('value' => $blog['Blog']['term_hierarchy']));
								?>
							</dd>
						</dl>
					</li>
					<li>
						<dl>
							<dt>
								<?php
									echo $this->Form->label('Blog.vote_flag', __d('blog', 'Allow votes?'));
								?>
							</dt>
							<dd>
								<?php
									echo $this->Form->input('Blog.vote_flag',array(
										'type' => 'radio',
										'options' => array(_ON => __('Yes'), _OFF => __('No')),
										'value' => intval($blog['Blog']['vote_flag']),
										'div' => false,
										'legend' => false,
									));
								?>
							</dd>
						</dl>
					</li>
					<li>
						<dl>
							<dt>
								<?php
									echo $this->Form->label('Blog.sns_flag', __d('blog', 'Allow Twitter,Facebook icon'));
								?>
							</dt>
							<dd>
								<?php
									echo $this->Form->input('Blog.sns_flag',array(
										'type' => 'radio',
										'options' => array(_ON => __('Yes'), _OFF => __('No')),
										'value' => intval($blog['Blog']['sns_flag']),
										'div' => false,
										'legend' => false,
									));
								?>
							</dd>
						</dl>
					</li>
					<li>
						<dl>
							<dt>
								<?php
									echo $this->Form->label('Blog.new_period', __d('blog', 'Period to show with &quot; new &quot; icon'));
								?>
							</dt>
							<dd>
								<?php
									echo $this->Form->input('Blog.new_period', array(
										'options' => array(
											0 => __('None'),
											1 => __d('blog', '%1$s day(s)', 1),
											2 => __d('blog', '%1$s day(s)', 2),
											3 => __d('blog', '%1$s day(s)', 3),
											5 => __d('blog', '%1$s day(s)', 5),
											7 => __d('blog', '%1$s day(s)', 7),
											30 => __d('blog', '%1$s day(s)', 30),
										),
										'selected' => intval($blog['Blog']['new_period']),
										'label' => false,
										'div' => false,
									));
								?>
							</dd>
						</dl>
					</li>
					<li>
						<dl>
							<dt>
								<?php
									echo $this->Form->label('Blog.mail_flag', __('Deliver e-mail when posting?'));
								?>
							</dt>
							<dd>
								<?php
									echo $this->Form->input('Blog.mail_flag',array(
										'type' => 'radio',
										'options' => array(_ON => __('Email delivery when posting.'), _OFF => __('Email not delivery when posting.')),
										'value' => intval($blog['Blog']['mail_flag']),
										'div' => false,
										'legend' => false,
									));
								?>
								<div class="hr">
								<?php
									echo $this->Form->label('Blog.mail_hierarchy', __('Notify whom? :'));
									echo $this->Form->authoritySlider('Blog.mail_hierarchy', array('value' => $blog['Blog']['mail_hierarchy'], 'display_guest' => true));
									$settings = array(
										'type' => 'text',
										'value' => $blog['Blog']['mail_subject'],
										'label' => __('E-mail Subject:'),
										'maxlength' => NC_VALIDATOR_TITLE_LEN,
										'size' => 23,
										'error' => array('attributes' => array(
											'selector' => true
										))
									);
									echo $this->Form->input('Blog.mail_subject', $settings);
									$settings = array(
										'type' => 'textarea',
										'escape' => false,
										'value' => $blog['Blog']['mail_body'],
										'label' => __('Message：'),
										'error' => array('attributes' => array(
											'selector' => true
										))
									);
									echo $this->Form->input('Blog.mail_body', $settings);
								?>
								<div class="note">
									<?php echo __d('blog', 'You may use the following keywords in the title and content of the message, <br />{X-SITE_NAME},{X-ROOM},<br />{X-CONTENT_NAME},{X-CATEGORY_NAME},{X-TAG_NAME},{X-SUBJECT},{X-USER},<br />{X-TO_DATE}、{X-BODY}、{X-URL}<br /><br />Each keyword will be translated to <br />site name, room name, <br />Blog title, category, tag, title of the article, creator<br />timestamp, article and url.');?>
								</div>
								</div>
							</dd>
						</dl>
					</li>
				</ul>
			</fieldset>
		</div>
		<div id="blog-edits-tab-comment<?php echo($id); ?>">
			<?php /* コメント設定 */ ?>
			<fieldset class="form">
				<ul class="lists nc-edits-lists">
					<li>
						<dl>
							<dt>
								<?php
									echo $this->Form->label('Blog.comment_flag', __d('blog', 'Allow replies?'));
								?>
							</dt>
							<dd>
								<?php
									echo $this->Form->input('Blog.comment_flag',array(
										'type' => 'radio',
										'options' => array(_ON => __('Yes'), _OFF => __('No')),
										'value' => intval($blog['Blog']['comment_flag']),
										'div' => false,
										'legend' => false,
									));
								?>
								<div class="hr">
									<?php
										echo $this->Form->label('Blog.comment_hierarchy', __d('blog', 'Authority to post comments:'));
										echo $this->Form->authoritySlider('Blog.comment_hierarchy', array('value' => $blog['Blog']['comment_hierarchy'], 'display_guest' => true));
									?>
									<div class="note">
										<?php echo __d('blog', 'If set to guest, users can comment without logged in.'); ?>
									</div>
									<?php
										echo $this->Form->input('Blog.comment_required_name',array(
											'type' => 'checkbox',
											'value' => _ON,
											'checked' => !empty($blog['Blog']['comment_required_name']) ? true : false,
											'label' => __d('blog', 'For non-members, comment author must fill out name and e-mail.'),
										));
										echo $this->Form->input('Blog.comment_image_auth',array(
											'type' => 'checkbox',
											'value' => _ON,
											'checked' => !empty($blog['Blog']['comment_image_auth']) ? true : false,
											'label' => __d('blog', 'Use image authentication?'),
										));
									?>
								</div>
							</dd>
						</dl>
					</li>
					<li>
						<dl>
							<dt>
								<?php
									echo $this->Form->label('Blog.mail_flag', __('Deliver e-mail when posting?'));
								?>
							</dt>
							<dd>
								<?php
									echo $this->Form->input('Blog.comment_mail_flag',array(
										'type' => 'radio',
										'options' => array(_ON => __d('blog', 'Email delivery when commenting.'), _OFF => __d('blog', 'Email not delivery when commenting.')),
										'value' => intval($blog['Blog']['comment_mail_flag']),
										'div' => false,
										'legend' => false,
									));
								?>
								<div class="hr">
								<?php
									echo $this->Form->label('Blog.comment_mail_hierarchy', __('Notify whom? :'));
									echo $this->Form->authoritySlider('Blog.comment_mail_hierarchy', array('value' => $blog['Blog']['comment_mail_hierarchy']));
									$settings = array(
										'type' => 'text',
										'value' => $blog['Blog']['comment_mail_subject'],
										'label' => __('E-mail Subject:'),
										'maxlength' => NC_VALIDATOR_TITLE_LEN,
										'size' => 23,
										'error' => array('attributes' => array(
											'selector' => true
										))
									);
									echo $this->Form->input('Blog.comment_mail_subject', $settings);
									$settings = array(
										'type' => 'textarea',
										'escape' => false,
										'value' => $blog['Blog']['comment_mail_body'],
										'label' => __('Message：'),
										'error' => array('attributes' => array(
											'selector' => true
										))
									);
									echo $this->Form->input('Blog.comment_mail_body', $settings);
								?>
								<div class="note">
									<?php echo __d('blog', 'You may use the following keywords in the title and content of the message, <br />{X-SITE_NAME},{X-ROOM},<br />{X-CONTENT_NAME},{X-CATEGORY_NAME},{X-TAG_NAME},{X-SUBJECT},{X-USER},<br />{X-TO_DATE}、{X-BODY}、{X-URL}<br /><br />Each keyword will be translated to <br />site name, room name, <br />Blog title, category, tag, title of the article, creator<br />timestamp, article and url.');?>
								</div>
								</div>
							</dd>
						</dl>
					</li>
				</ul>
			</fieldset>
		</div>
		<div id="blog-edits-tab-trackback<?php echo($id); ?>">
			<?php /* トラックバック設定 */ ?>
			<fieldset class="form">
				<ul class="lists nc-edits-lists">
					<li>
						<dl>
							<dt>
								<?php
									echo $this->Form->label('Blog.trackback_transmit_flag', __d('blog', 'Allow trackbacks?'));
								?>
							</dt>
							<dd>
								<?php
									echo $this->Form->input('Blog.trackback_transmit_flag',array(
										'type' => 'checkbox',
										'value' => _ON,
										'checked' => !empty($blog['Blog']['trackback_transmit_flag']) ? true : false,
										'label' => __d('blog', 'Transmit trackbacks?'),
									));
								?>
								<div class="hr">
								<?php
									$settings = array(
										'type' => 'text',
										'value' => $blog['Blog']['transmit_blog_name'],
										'label' => __d('blog', 'Sending title'),
										'div' => false,
										'maxlength' => NC_VALIDATOR_TITLE_LEN,
										'size' => 30,
										'error' => array('attributes' => array(
											'selector' => true
										))
									);
									echo $this->Form->input('Blog.transmit_blog_name', $settings);
								?>
								<div class="note">
									<?php echo __d('blog', 'You may use the following keywords when sending trackbacks.<br />{X-USER}：username<br />{X-SITE_NAME}：site name');?>
								</div>
								</div>
								<div class="hr"></div>
								<?php
									echo $this->Form->input('Blog.trackback_receive_flag',array(
										'type' => 'checkbox',
										'value' => _ON,
										'checked' => !empty($blog['Blog']['trackback_receive_flag']) ? true : false,
										'label' => __d('blog', 'Recieve trackbacks?'),
									));
								?>
							</dd>
						</dl>
					</li>
				</ul>
			</fieldset>
		</div>
		<div id="blog-edits-tab-approval<?php echo($id); ?>">
			<?php /* 承認機能設定 */ ?>
			<fieldset class="form">
				<ul class="lists nc-edits-lists">
					<li>
						<dl>
							<dt>
								<?php
									echo $this->Form->label('Blog.approved_flag', __('Post approval setting'));
								?>
							</dt>
							<dd>
								<?php
									echo $this->Form->input('Blog.approved_flag',array(
										'type' => 'radio',
										'options' => array(_ON => __('Need room manager approval'), _OFF => __('Automatic approval')),
										'value' => intval($blog['Blog']['approved_flag']),
										'div' => false,
										'legend' => false,
									));
									echo $this->Form->input('Blog.approved_pre_change_flag',array(
										'type' => 'checkbox',
										'value' => _ON,
										'checked' => !empty($blog['Blog']['approved_pre_change_flag']) ? true : false,
										'label' => __('If not approved, You display the contents of the change before.'),
									));
								?>
							</dd>
						</dl>
					</li>
					<li>
						<dl>
							<dt>
								<?php
									echo $this->Form->label('Blog.approved_mail_flag', __('Announce mail setting'));
								?>
							</dt>
							<dd>
								<?php
									echo $this->Form->input('Blog.approved_mail_flag',array(
										'type' => 'radio',
										'options' => array(_ON => __('Send email'), _OFF => __('Not send')),
										'value' => intval($blog['Blog']['approved_mail_flag']),
										'div' => false,
										'legend' => false,
									));
								?>
								<div class="hr">
								<?php
									$settings = array(
										'type' => 'text',
										'value' => $blog['Blog']['approved_mail_subject'],
										'label' => __('E-mail Subject:'),
										'maxlength' => NC_VALIDATOR_TITLE_LEN,
										'size' => 23,
										'error' => array('attributes' => array(
											'selector' => true
										))
									);
									echo $this->Form->input('Blog.approved_mail_subject', $settings);
									$settings = array(
										'type' => 'textarea',
										'escape' => false,
										'value' => $blog['Blog']['approved_mail_body'],
										'label' => __('Message：'),
										'error' => array('attributes' => array(
											'selector' => true
										))
									);
									echo $this->Form->input('Blog.approved_mail_body', $settings);
								?>
								</div>
							</dd>
						</dl>
					</li>
					<li>
						<dl>
							<dt>
								<?php
									echo $this->Form->label('Blog.comment_approved_flag', __d('blog', 'Comment approval setting'));
								?>
							</dt>
							<dd>
								<?php
									echo $this->Form->input('Blog.comment_approved_flag',array(
										'type' => 'radio',
										'options' => array(_ON => __('Need room manager approval'), _OFF => __('Automatic approval')),
										'value' => intval($blog['Blog']['comment_approved_flag']),
										'div' => false,
										'legend' => false,
									));
								?>
							</dd>
						</dl>
					</li>
					<li>
						<dl>
							<dt>
								<?php
									echo $this->Form->label('Blog.trackback_approved_flag', __d('blog', 'Trackback approval setting'));
								?>
							</dt>
							<dd>
								<?php
									echo $this->Form->input('Blog.trackback_approved_flag',array(
										'type' => 'radio',
										'options' => array(_ON => __('Need room manager approval'), _OFF => __('Automatic approval')),
										'value' => intval($blog['Blog']['trackback_approved_flag']),
										'div' => false,
										'legend' => false,
									));
								?>
							</dd>
						</dl>
					</li>
					<li>
						<dl>
							<dt>
								<?php
									echo $this->Form->label('Blog.comment_approved_mail_flag', __d('blog', 'Comment, trackback announce mail setting'));
								?>
							</dt>
							<dd>
								<?php
									echo $this->Form->input('Blog.comment_approved_mail_flag',array(
										'type' => 'radio',
										'options' => array(_ON => __('Send email'), _OFF => __('Not send')),
										'value' => intval($blog['Blog']['comment_approved_mail_flag']),
										'div' => false,
										'legend' => false,
									));
								?>
								<div class="hr">
								<?php
									$settings = array(
										'type' => 'text',
										'value' => $blog['Blog']['comment_approved_mail_subject'],
										'label' => __('E-mail Subject:'),
										'maxlength' => NC_VALIDATOR_TITLE_LEN,
										'size' => 23,
										'error' => array('attributes' => array(
											'selector' => true
										))
									);
									echo $this->Form->input('Blog.comment_approved_mail_subject', $settings);
									$settings = array(
										'type' => 'textarea',
										'escape' => false,
										'value' => $blog['Blog']['comment_approved_mail_body'],
										'label' => __('Message：'),
										'error' => array('attributes' => array(
											'selector' => true
										))
									);
									echo $this->Form->input('Blog.comment_approved_mail_body', $settings);
								?>
								</div>
							</dd>
						</dl>
					</li>
				</ul>
			</fieldset>
		</div>
	</div>
	<?php
		echo $this->Html->div('submit',
			$this->Form->button(__('Ok'), array('name' => 'ok', 'class' => 'common-btn', 'type' => 'submit')).
			$this->Form->button(__('Cancel'), array('name' => 'cancel', 'class' => 'common-btn', 'type' => 'button',
				'data-pjax' => '#'.$id, 'data-ajax-url' =>  $this->Html->url(array('controller' => 'blog', '#' => $id))))
		);
		echo $this->Form->end();
	?>
<?php
	echo $this->Html->script('Blog.blog_edits/index');
?>
<script>
$(function(){
	$('#blog-edits<?php echo($id); ?>').BlogEdits('<?php echo($id);?>'<?php if(isset($active_tab)){ echo(','.$active_tab); }?>);
});
</script>
</div>