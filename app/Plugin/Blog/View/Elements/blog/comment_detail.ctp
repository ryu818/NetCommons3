<?php
	$permalink = $blog_post['BlogPost']['permalink'];
	$blogDates = $this->TimeZone->dateValues($blog_post['BlogPost']['post_date']);
	$baseUrl = array('plugin' => 'blog', 'controller' => 'blog', $blogDates['year'], $blogDates['month'], $blogDates['day'],
				$permalink, 'page' => $this->Paginator->current());
?>
<?php for($i = 0; $i < count($blog_comments_tree); $i++){ ?>
	<li id="<?php echo $id?>-comment-list-<?php echo $blog_comments_tree[$i]['BlogComment']['id']?>">
		<div class="blog-comment-list">
			<article id="<?php echo $id ?>-comment-<?php echo $blog_comments_tree[$i]['BlogComment']['id']?>" class="blog-comment">
				<?php // コメントのヘッダー ?>
				<header class="comment-header table">
					<div class="table-row">
						<?php //TODO:サムネイル未実装?>
<!-- 						<div class="user-thumbnail table-cell"></div> -->
						<div class="table-cell">
							<cite class="blog-comment-citation">
								<?php	// ユーザ名
									$author = !empty($blog_comments_tree[$i]['BlogComment']['author'])? $blog_comments_tree[$i]['BlogComment']['author'] : __('Guest');
									if(!empty($blog_comments_tree[$i]['BlogComment']['author_url'])){
										echo $this->Html->link(h($author),
											$this->MyHtml->linkUrl($blog_comments_tree[$i]['BlogComment']['author_url']),
											array('id' => 'comment-author-link'.$id.'-'.$blog_comments_tree[$i]['BlogComment']['blog_post_id'],
												'class' => 'nc-tooltip', 'title' => $this->MyHtml->linkUrl($blog_comments_tree[$i]['BlogComment']['author_url']),'target' => '_blank'
										));
									}else{
										echo h($author);
									}
								?>
								<?php if($blog_post['BlogPost']['created_user_id'] == $blog_comments_tree[$i]['BlogComment']['created_user_id']): ?>
									<?php echo $this->Html->tag('span', __('Author'), array('class' => 'blog-comment-author')); ?>
								<?php endif; ?>
							</cite>
							<a href="<?php echo '#'.$id.'-comment-'.$blog_comments_tree[$i]['BlogComment']['id'] ?>" class="blog-comment-time-link">
								<?php $dates = $this->TimeZone->dateValues($blog_comments_tree[$i]['BlogComment']['modified']) ?>
								<time datetime="<?php echo $dates['atom_date']?>" class="comment-date">
									<?php echo date(__('(Y-m-d h:i A)'), strtotime($dates['full_date'])) ?>
								</time>
							</a>
						</div>
					</div>

					<div class="table-row">
						<div class="table-cell">
							<?php if(!empty($blog_comments_tree[$i]['BlogComment']['author_email'])): ?>
								<?php echo $this->Html->link(__('Send to mail.'), 'mailto:'.$blog_comments_tree[$i]['BlogComment']['author_email'],
									array('class' => 'nc-tooltip', 'title' => $blog_comments_tree[$i]['BlogComment']['author_email'])); ?>
							<?php endif; ?>
						</div>
					</div>
				</header>

				<?php // コメント内容  ?>
				<section id="<?php echo $id?>-comment-detail-<?php echo $blog_comments_tree[$i]['BlogComment']['id']?>" class="blog-comment-detail">
					<p><?php echo nl2br(h($blog_comments_tree[$i]['BlogComment']['comment'])) ?></p>
				</section>

				<?php // リンク ?>
				<footer class="blog-comment-footer">
					<?php // TODO:編集と削除は、権限による表示制御が必要?>
					<?php	// 編集
						$editUrl = array_merge($baseUrl,
							array('comment_edit' => $blog_comments_tree[$i]['BlogComment']['id'],'#' => $id. '-comment-' .$blog_comments_tree[$i]['BlogComment']['id']));
						echo $this->Html->link(__('Edit'),
							$editUrl,
							array('id' => 'comment-edit-link'.$id.'-'.$blog_comments_tree[$i]['BlogComment']['blog_post_id'], 'title' =>__('Edit'),
								'data-pjax' => '#'.$id
						));
					?>
					&nbsp;|&nbsp;
					<?php	// 削除
						$deleteUrl = array(
							'controller' => 'comments', 'action' => 'delete',
							$blog_comments_tree[$i]['BlogComment']['blog_post_id'], $blog_comments_tree[$i]['BlogComment']['id'],
							'?' => array('comment_back_page' => $this->Paginator->current())
						);
						echo $this->Html->link(__('Delete'),
							$deleteUrl,
							array('title' =>__('Delete Comment'), 'data-ajax-confirm' => __('Deleting %s. <br />Are you sure to proceed?',__('Comment')),
								'data-pjax' => '#'.$id, 'data-ajax-type' => 'post'
						));
					?>
					<?php	// 返信
						if($depth > 1){
							echo '&nbsp;|&nbsp;';
							$replyUrl = array_merge($baseUrl,
								array('comment_reply' => $blog_comments_tree[$i]['BlogComment']['id'],'#' => $id. '-comment-' .$blog_comments_tree[$i]['BlogComment']['id']));

							echo $this->Html->link(__('Reply'),
								$replyUrl,
								array('id' => 'comment-reply-link'.$id.'-'.$blog_comments_tree[$i]['BlogComment']['blog_post_id'], 'title' =>__('Reply'),
								'data-pjax' => '#'.$id
							));
						}
					?>
				</footer>
			</article>

			<?php //コメント編集、返信フォームの表示
				if((isset($this->request->named['comment_edit']) && $blog_comments_tree[$i]['BlogComment']['id'] == $this->request->named['comment_edit'])
					|| (isset($this->request->named['comment_reply']) && $blog_comments_tree[$i]['BlogComment']['id'] == $this->request->named['comment_reply'] )){
					echo $this->element('blog/comment_respond', array('blog_post' => $blog_post, 'comment' => $comment, 'edit' => true));
				}
			?>
		</div>

		<?php // ツリーに子供がいる場合は続きを表示 ?>
		<?php if(array_key_exists('children', $blog_comments_tree[$i]) && !empty($blog_comments_tree[$i]['children'])): ?>
			<ul class="blog-comment-child-lists">
				<?php echo $this->element('blog/comment_detail', array('blog_comments_tree' => $blog_comments_tree[$i]['children'], 'depth' => $depth - 1, 'blog_post' => $blog_post, 'comment' => $comment)); ?>
			</ul>
		<?php endif; ?>
	</li>
<?php }?>
