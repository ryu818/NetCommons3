<article id="<?php echo $id?>-comments" class="blog-comment-group">
	<?php
		//TODO:権限　編集時の権限追加　ヒエラルキが上位のものか（一般の中で上下がついたら？？）自分のコメント　ゲストのコメントはモデレタ以上が編集可能
		//TODO:コメントを投稿するとブラウザのURL部分の日本語が壊れる
		//TODO:ブログのコンテンツをコピーしてもコメント群はコピーされない
		//TODO:コメントの表示数の設定値に未対応
	?>

	<?php
		$queryOptions = array('url' => array('#' => $id. '-comments'));
	?>

	<?php if (!empty($blog_comments_tree)): ?>
		<h3 class="blog-comment-title"><?php echo __d('blog', 'Comment on %s', $blog_post['BlogPost']['title'])?></h3>
		<?php echo($this->element('/common/paginator', array('options' => $queryOptions))); ?>
		<ul class="lists">
			<?php
				echo $this->element('blog/comment_detail',
					array('depth' => BLOG_COMMENTS_MAX_DEPTH, 'blog_post' => $blog_post,
					'comment' => $comment, 'blog_comments_tree' => $blog_comments_tree));
			?>
		</ul>
		<?php echo($this->element('/common/paginator', array('options' => $queryOptions))); ?>
	<?php endif; ?>
	<?php echo $this->element('blog/comment_respond', array('blog_post' => $blog_post, 'comment' => $comment, 'edit' => false)); ?>
</article>
