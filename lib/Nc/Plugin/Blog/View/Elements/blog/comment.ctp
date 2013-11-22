<article id="<?php echo $id?>-comments" class="blog-comment-group">
	<?php
		//TODO:権限　編集時の権限追加　ヒエラルキが上位のものか（一般の中で上下がついたら？？）自分のコメント　ゲストのコメントはモデレタ以上が編集可能
	?>
	<?php
		$queryOptions = array('url' => array('#' => $id. '-comments'), 'data-pjax' => '#'.$id);
	?>

	<?php if (!empty($blog_comments_tree)): ?>
		<h3 class="blog-comment-title"><?php echo __d('blog', 'Comment on %s', $blog_post['BlogPost']['title'])?></h3>
		<?php echo($this->element('/common/paginator', array('options' => $queryOptions))); ?>
		<ul class="nc-lists">
			<?php echo $this->element('blog/comment_detail', array('depth' => BLOG_COMMENTS_MAX_DEPTH, 'blog_post' => $blog_post)); ?>
		</ul>
		<?php echo($this->element('/common/paginator', array('options' => $queryOptions))); ?>
	<?php endif; ?>
	<?php if ($blog['Blog']['comment_flag'] && $this->CheckAuth->checkAuth($hierarchy,  $blog['Blog']['comment_hierarchy'])):?>
		<?php echo $this->element('blog/comment_respond', array('blog_post' => $blog_post, 'edit' => false)); ?>
	<?php endif;?>
</article>
