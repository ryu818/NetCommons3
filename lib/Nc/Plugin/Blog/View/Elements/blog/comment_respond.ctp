<?php
	$permalink = $blog_post['BlogPost']['permalink'];
	$dates = $this->TimeZone->dateValues($blog_post['BlogPost']['post_date']);
	$nc_user = $this->Session->read(NC_AUTH_KEY.'.'.'User');

	// edit:編集、返信、 add:新規
	if($edit) {
		$mode = 'edit';
	} else {
		$mode = 'add';
	}

	$baseSettings = array(
		'type' => 'text',
		'label' => false,
		'div' => false,
		'error' => array('attributes' => array(
				'selector' => true
		))
	);

	// 編集か返信の場合、新規コメント用のフォームにバリデート結果を表示しない、入力フォームの値も初期化
	if((isset($this->request->named['comment_edit']) || isset($this->request->named['comment_reply'])) && $mode == 'add') {
		$baseSettings['error'] = false;
		$comment['BlogComment']['author'] = '';
		$comment['BlogComment']['author_email'] = '';
		$comment['BlogComment']['author_url'] = '';
		$comment['BlogComment']['comment'] = '';
		$comment['BlogComment']['parent_id'] = '';
		$comment['BlogComment']['id'] = '';
	}
	$authorId = 'BlogCommentAuthor'.$id.$mode;
	$authorEmailId = 'BlogCommentAuthorEmail'.$id.$mode;
	$authorUrlId = 'BlogCommentAuthorUrl'.$id.$mode;
	$commentAreaId = 'BlogCommentComment'.$id.$mode;

	$authorSettings = array_merge($baseSettings, array(
		'id' => $authorId,
		'size' => 23,
		'maxlength' => NC_VALIDATOR_USER_NAME_LEN,
		'value' => $comment['BlogComment']['author'],
		'required' => $is_required_name? 'required' : ''));

	$emailSettings = array_merge($baseSettings, array(
		'id' => $authorEmailId,
		'size' => 23,
		'maxlength' => NC_VALIDATOR_VARCHAR_LEN,
		'value' => $comment['BlogComment']['author_email'],
		'required' => $is_required_name? 'required' : ''));

	$urlSettings = array_merge($baseSettings, array(
		'id' => $authorUrlId,
		'size' => 23,
		'maxlength' => NC_VALIDATOR_VARCHAR_LEN,
		'value' => $comment['BlogComment']['author_url']));

	$commentSettings = array_merge($baseSettings, array(
		'id' => $commentAreaId,
		'cols' => 22,
		'type' => 'textarea',
		'value' => $comment['BlogComment']['comment']));

	// 編集か返信の場合にキャンセルボタンを表示する
	$viewBtn = $this->Form->button(__('Submit a comment'), array('name' => 'ok', 'class' => 'nc-common-btn blog-comment-btn', 'type' => 'submit'));
	if($mode == 'edit'){
		$viewBtn .= $this->Form->button(__('Cancel'), array('name' => 'cancel', 'class' => 'nc-common-btn blog-comment-btn', 'type' => 'button',
		'onclick' => '$.Blog.clkCommentCancel(\'' .$id. '\');'));
	}

	$formSubmitUrl = array('controller' => 'blog', 'action' => 'index',
		$dates['year'], $dates['month'], $dates['day'], $permalink,
		'?' => array('comment_back_page' => $this->Paginator->current()),
		'#' => 'Form' .$id.$mode);
	if($mode == 'edit') {
		$formSubmitUrl['comment_edit'] = !empty($comment['BlogComment']['id']) ? $comment['BlogComment']['id'] : $comment['BlogComment']['parent_id'];
	}
?>


<div id="<?php echo $id .$mode .'-respond' ?>">
	<h3 class="blog-comment-recomment-title"> <?php echo __d('blog', 'Leave a comments') ?></h3>
	<?php
		echo $this->Form->create('BlogComment', array('id' => 'Form'.$id.$mode,'url' => $formSubmitUrl, 'data-pjax' => '#'.$id));
	?>
		<fieldset class="form">
			<ul class="nc-lists">
				<?php if (empty($nc_user['id'])):// ログインしてない場合は表示項目を追加する ?>
					<li>
						<dl>
							<dt>
								<?php echo $this->Form->label('BlogComment.author', __('Name'), array('for' => $authorId)) ?>
								<?php if($is_required_name): ?>
									<span class="require">
										<?php echo __('*');?>
									</span>
								<?php endif;?>
							</dt>
							<dd>
								<?php echo $this->Form->input('BlogComment.author', $authorSettings); ?>
							</dd>
						</dl>
					</li>
					<li>
						<dl>
							<dt>
								<?php echo $this->Form->label('BlogComment.author_email', __('E-mail'), array('for' => $authorEmailId)); ?>
								<?php if($is_required_name): ?>
									<span class="require">
										<?php echo __('*');?>
									</span>
								<?php endif;?>
							</dt>
							<dd>
								<?php echo $this->Form->input('BlogComment.author_email', $emailSettings); ?>
							</dd>
						</dl>
					</li>
					<li>
						<dl>
							<dt>
								<?php echo $this->Form->label('BlogComment.author_url', __('URL'), array('for' => $authorUrlId)); ?>
							</dt>
							<dd>
								<?php echo $this->Form->input('BlogComment.author_url', $urlSettings); ?>
							</dd>
						</dl>
					</li>
				<?php endif; ?>

				<li>
					<dl>
						<dt>
							<?php echo $this->Form->label('BlogComment.comment',  __('Comment'), array('for' => $commentAreaId)); ?>
						</dt>
						<dd>
							<?php echo $this->Form->input('BlogComment.comment', $commentSettings); ?>
						</dd>
					</dl>
				</li>
			</ul>
		</fieldset>
		<?php
			// コメント返信先（親コメント）のID
			echo $this->Form->hidden('BlogComment.parent_id' ,
				array('id' => $id. '-parent_id-' .$mode, 'value' => $comment['BlogComment']['parent_id']));
			// コメントのID
			echo $this->Form->hidden('BlogComment.comment_id' ,
				array('id' => $id. '-comment_id-' .$mode, 'value' => $comment['BlogComment']['id']));
		?>
		<?php echo $this->Html->div('submit', $viewBtn); ?>
	<?php echo $this->Form->end(); ?>

	<?php if ($mode == 'edit'):?>
		<script>
			$(function(){
				<?php if (empty($nc_user['id'])): ?>
					$('#<?php echo($authorId); ?>').focus();
				<?php else: ?>
					$('#<?php echo($commentAreaId); ?>').focus();
				<?php endif; ?>
			});
		</script>
	<?php endif; ?>
</div>
