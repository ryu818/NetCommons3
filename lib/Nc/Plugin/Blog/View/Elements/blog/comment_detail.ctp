<?php
	$permalink = $blog_post['BlogPost']['permalink'];
	$blogDates = $this->TimeZone->dateValues($blog_post['BlogPost']['post_date']);
	$baseUrl = array('plugin' => 'blog', 'controller' => 'blog', $blogDates['year'], $blogDates['month'], $blogDates['day'],
		$permalink, 'page' => $this->Paginator->current());

?>

<?php foreach ($blog_comments_tree as $blogComments) { ?>
	<?php
		$author = !empty($blogComments['BlogComment']['author'])? $blogComments['BlogComment']['author'] : __('Guest');
		if(!empty($blogComments['BlogComment']['author_url'])) {
			$authorHtml = $this->Html->link(h($author), $this->Html->linkUrl($blogComments['BlogComment']['author_url']),
				array('id' => 'comment-author-link'.$id.'-'.$blogComments['BlogComment']['blog_post_id'],
					'class' => 'nc-tooltip', 'title' => $this->Html->linkUrl($blogComments['BlogComment']['author_url']),'target' => '_blank'
				));
		}else{
			$authorHtml = h($author);
		}

		$dates = $this->TimeZone->dateValues($blogComments['BlogComment']['modified']);

		// TODO:編集と削除は、権限による表示制御が必要
		// 編集
		$editUrl = array_merge($baseUrl,
				array('comment_edit' => $blogComments['BlogComment']['id'],'#' => $id. '-comment-' .$blogComments['BlogComment']['id']));
		$editHtml = $this->Html->link(__('Edit'), $editUrl,
				array('id' => 'comment-edit-link'.$id.'-'.$blogComments['BlogComment']['blog_post_id'], 'title' =>__('Edit'),
						'data-pjax' => '#'.$id
				));
		// 削除
		$deleteUrl = array('controller' => 'comments', 'action' => 'delete',
			$blogComments['BlogComment']['blog_post_id'], $blogComments['BlogComment']['id'],
			'?' => array('comment_back_page' => $this->Paginator->current())
		);
		$deleteHtml =  $this->Html->link(__('Delete'), $deleteUrl,
			array('title' =>__('Delete Comment'), 'data-ajax-confirm' => __('Deleting %s. <br />Are you sure to proceed?',__('Comment')),
				'data-pjax' => '#'.$id, 'data-ajax-type' => 'post', 'data-ajax-data' => '{"data[_Token][key]": "'.$this->params['_Token']['key'].'"}'
			));
		// 返信
		if($depth > 1 && $blog['Blog']['comment_flag'] && $this->CheckAuth->checkAuth($hierarchy,  $blog['Blog']['comment_hierarchy'])) {
			$replyUrl = array_merge($baseUrl,
				array('comment_reply' => $blogComments['BlogComment']['id'],'#' => $id. '-comment-' .$blogComments['BlogComment']['id']));
			$replyHtml = $this->Html->link(__('Reply'), $replyUrl,
				array('id' => 'comment-reply-link'.$id.'-'.$blogComments['BlogComment']['blog_post_id'], 'title' =>__('Reply'),
					'data-pjax' => '#'.$id
				));
		}
		// 承認
		if($this->CheckAuth->checkAuth($hierarchy, NC_AUTH_CHIEF) && !$blogComments['BlogComment']['is_approved']) {
			$approveUrl = array('controller' => 'comments', 'action' => 'approve',
				$blogComments['BlogComment']['blog_post_id'], $blogComments['BlogComment']['id'],
				'?' => array('comment_back_page' => $this->Paginator->current()
			));
			$approveHtml = $this->Html->link(__('Approve'), $approveUrl,
				array('title' =>__('Approve'), 'data-ajax-confirm' => __('Approve to %s. <br />Are you sure to proceed?',__('Comment')),
					'data-pjax' => '#'.$id, 'data-ajax-type' => 'post', 'data-ajax-data' => '{"data[_Token][key]": "'.$this->params['_Token']['key'].'"}'
				));
		}
	?>

	<li id="<?php echo $id?>-comment-list-<?php echo $blogComments['BlogComment']['id']?>">
		<div class="blog-comment-list">
			<article id="<?php echo $id ?>-comment-<?php echo $blogComments['BlogComment']['id']?>" class="blog-comment">
				<header class="comment-header table">
					<div class="table-row">
						<?php //TODO:サムネイル未実装?>
<!-- 						<div class="user-thumbnail table-cell"></div> -->
						<div class="table-cell">
							<cite class="blog-comment-citation">
								<?php echo $authorHtml; ?>
								<?php if($blog_post['BlogPost']['created_user_id'] == $blogComments['BlogComment']['created_user_id']): ?>
									<span class="blog-comment-author">
										<?php echo __('Author'); ?>
									</span>
								<?php endif; ?>
							</cite>
							<?php if(!$blogComments['BlogComment']['is_approved']): ?>
								<span class="nc-temporary">
									<?php echo __('Pending'); ?>
								</span>
							<?php endif; ?>
							<a href="<?php echo '#'.$id.'-comment-'.$blogComments['BlogComment']['id'] ?>" class="blog-comment-time-link">
								<time datetime="<?php echo $dates['atom_date']?>">
									<?php echo date(__('(Y-m-d h:i A)'), strtotime($dates['full_date'])) ?>
								</time>
							</a>
						</div>
					</div>
					<?php if(!empty($blogComments['BlogComment']['author_email'])): ?>
						<div class="table-row">
							<div class="table-cell">
								<?php echo $this->Html->link(__('Send to mail.'), 'mailto:'.$blogComments['BlogComment']['author_email'],
									array('class' => 'nc-tooltip', 'title' => $blogComments['BlogComment']['author_email'])); ?>
							</div>
						</div>
					<?php endif; ?>
				</header>
				<section id="<?php echo $id?>-comment-detail-<?php echo $blogComments['BlogComment']['id']?>" class="blog-comment-detail">
					<p><?php echo nl2br(h($blogComments['BlogComment']['comment'])) ?></p>
				</section>
				<footer class="blog-comment-footer">
					<?php if(isset($editHtml)): // 編集?>
						<?php echo $editHtml;?>
					<?php endif;?>

					<?php if(isset($deleteHtml)): // 削除?>
						&nbsp;|&nbsp;
						<?php echo $deleteHtml;?>
					<?php endif;?>

					<?php if(isset($replyHtml)): // 返信?>
						&nbsp;|&nbsp;
						<?php echo $replyHtml;?>
					<?php endif;?>

					<?php	if(isset($approveHtml)):// 承認?>
						&nbsp;|&nbsp;
						<?php echo $approveHtml;?>
					<?php endif;?>
				</footer>
			</article>

			<?php //コメント編集、返信フォームの表示
				if((isset($this->request->named['comment_edit']) && $blogComments['BlogComment']['id'] == $this->request->named['comment_edit'])
					|| (isset($this->request->named['comment_reply']) && $blogComments['BlogComment']['id'] == $this->request->named['comment_reply'] )){
					echo $this->element('blog/comment_respond', array('blog_post' => $blog_post, 'edit' => true));
				}
			?>
		</div>

		<?php // ツリーに子供がいる場合は続きを表示 ?>
		<?php if(array_key_exists('children', $blogComments) && !empty($blogComments['children'])): ?>
			<?php if(isset($blog_styles[2][2]['BlogStyle']['threaded_comments']) && $blog_styles[2][2]['BlogStyle']['threaded_comments'] == _OFF):?>
				<?php echo $this->element('blog/comment_detail', array('blog_comments_tree' => $blogComments['children'], 'depth' => $depth - 1, 'blog_post' => $blog_post)); ?>
			<?php else:?>
				<ul class="blog-comment-child-lists">
					<?php echo $this->element('blog/comment_detail', array('blog_comments_tree' => $blogComments['children'], 'depth' => $depth - 1, 'blog_post' => $blog_post)); ?>
				</ul>
			<?php endif; ?>
		<?php endif; ?>
	</li>
<?php }?>
