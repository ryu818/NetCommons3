<?php if(count($blog_recent_comments) > 0): ?>
<aside class="blog-widget blog-widget-recent-comments">
	<h3 class="blog-widget-title">
		<?php echo(__d('blog', 'Recent Comments')); ?>
	</h3>
	<ul>
		<?php foreach ($blog_recent_comments as $post): ?>
		<li>
			<?php
				$permalink = $post['BlogPost']['permalink'];
				$dates = $this->TimeZone->date_values($post['BlogPost']['post_date']);
			?>
			<?php
				/* TODO: 会員でない方からのコメントならば、rel="external nofollow"を付与 */
				if($post['BlogComment']['comment'] != '') {
					$post_fix = $id.'_comments';
					$comment_text = $this->Text->truncate(
						($post['BlogComment']['title'] != '') ? $post['BlogComment']['title'] : $post['BlogComment']['comment'],
						BLOG_RECENT_COMMENTS_MAX_LENGTH
					);
				} else {
					$post_fix = $id.'_trackbacks';
					/* TODO:トラックバックは未作成 */
					$comment_text = 'TestTrackback';
				}
				$comment = $this->Html->link($comment_text, array('plugin' => 'blog', 'controller' => 'blog', $dates['year'], $dates['month'], $dates['day'], $permalink, '#' => $post_fix),
					array('title' => $comment_text)
				);
				/* TODO: 会員のリンク先は未定義、会員情報を表示させる */
				$user = $this->Html->link($post['BlogComment']['created_user_name'], '#',
					array('title' => $post['BlogComment']['created_user_name']));
				echo(__d('blog', '%1$s on %2$s', $user, $comment));
			?>
		</li>
		<?php endforeach; ?>
	</ul>
</aside>
<?php endif; ?>
