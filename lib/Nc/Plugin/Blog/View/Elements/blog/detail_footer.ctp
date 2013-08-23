<?php
	$title = $blog_post['BlogPost']['title'];
	$permalink = $blog_post['BlogPost']['permalink'];
	$dates = $this->TimeZone->dateValues($blog_post['BlogPost']['post_date']);
	$isEdit = $this->CheckAuth->isEdit($hierarchy, $blog['Blog']['post_hierarchy'], $blog_post['BlogPost']['created_user_id'],
		$blog_post['PageAuthority']['hierarchy']);

	$user = $this->Session->read(NC_AUTH_KEY.'.'.'User');
	// 投票済みの場合は取得できる
	$votedSession = $this->Session->read('Blog.vote.'.$user['id'].'.'.$blog_post['BlogPost']['id']);
	$pos = strpos($blog_post['BlogPost']['vote'], $user['id']);
	if($pos === false && !isset($votedSession)){
		$isVoted = false;
	}else{
		$isVoted = true;
	}

	$commentLinkOptions = array('title' => __d('blog', 'Comment on %s', $title), 'rel' => 'bookmark', 'data-pjax' => '#'.$id);
	$trackbackLinkOptions = array('title' => __d('blog', 'Trackback on %s', $title), 'rel' => 'bookmark', 'data-pjax' => '#'.$id);
	if($this->CheckAuth->checkAuth($hierarchy, NC_AUTH_CHIEF)) {
		$commentCount = intval($blog_post['BlogPost']['comment_count']);
		$trackbackCount = intval($blog_post['BlogPost']['trackback_count']);

		if($blog_post['BlogPost']['approved_comment_count'] != $blog_post['BlogPost']['comment_count']) {
			$commentLinkOptions = array_merge($commentLinkOptions, array('class'=> 'temporary-style'));
			$commentLinkOptions['title'] = $commentLinkOptions['title'].'['.__('There is a waiting for approval').']';
		}
		if($blog_post['BlogPost']['approved_trackback_count'] != $blog_post['BlogPost']['trackback_count']) {
			$trackbackLinkOptions = array_merge($trackbackLinkOptions, array('class'=> 'temporary-style'));
			$trackbackLinkOptions['title'] = $trackbackLinkOptions['title'].'['.__('There is a waiting for approval').']';
		}
	} else {
		$commentCount = intval($blog_post['BlogPost']['approved_comment_count']);
		$trackbackCount = intval($blog_post['BlogPost']['approved_trackback_count']);
	}

	// 承認待ちの判定（コメント、トラックバック）

?>
<footer id="blog-entry-footer<?php echo($id.'-'.$blog_post['BlogPost']['id']); ?>" class="blog-entry-footer">
	<span class="blog-edit-link">
		<?php if($isEdit): ?>
		<?php
			$editUrl = array(
				'controller' => 'blog_posts', 'action' => 'index', $blog_post['BlogPost']['id'], '#' => $id
			);
			if(isset($backQuery) && count($backQuery) > 0) {
				$editUrl['?'] = $backQuery;
			}
			echo $this->Html->link(__('Edit'),
				$editUrl,
				array('id' => 'blog-edit-link'.$id.'-'.$blog_post['BlogPost']['id'], 'title' =>__('Edit Post'), 'data-pjax' => '#'.$id
			));
		?>
		&nbsp;|&nbsp;
		<?php
			$deleteUrl = array(
				'controller' => 'blog_posts', 'action' => 'delete', $blog_post['BlogPost']['id']
			);
			if(isset($backQuery) && count($backQuery) > 0) {
				$deleteUrl['?'] = $backQuery;
			}
			echo $this->Html->link(__('Delete'),
				$deleteUrl,
				array('title' =>__('Delete Post'),
						'data-ajax-confirm' => __('Deleting %s. <br />Are you sure to proceed?',$blog_post['BlogPost']['title']),
						'data-pjax' => '#'.$id, 'data-ajax-type' => 'post',
						'data-ajax-data' => '{"data[_Token][key]": "'.$this->params['_Token']['key'].'"}',
			));
		?>
		&nbsp;|&nbsp;
		<?php endif; ?>
		<?php
			if($isVoted){
				echo '<span class="blog-posts-voted">'.__('Voted').'</span>';
			}else{
				echo $this->Html->link(__('Vote'),
					array('controller' => 'blog', 'action' => 'vote', $blog_post['BlogPost']['id']),
					array('title' =>__('Vote'),
						'data-ajax' => '#blog-entry-footer'.$id.'-'.$blog_post['BlogPost']['id'],
						'data-ajax-type' => 'post',
						'data-ajax-data' => '{"data[_Token][key]": "'.$this->params['_Token']['key'].'"}',
				));
			}
		?>
		&nbsp;|&nbsp;
	</span>
	<span class="blog-comments-link">
		<?php
			echo(__('Voted(%s)', intval($blog_post['BlogPost']['vote_count'])));
		?>
		<?php if($blog['Blog']['comment_flag'] || $commentCount > 0): ?>
			&nbsp;|&nbsp;
			<?php
				$commentUrl = array('controller' => 'blog', $dates['year'], $dates['month'], $dates['day'], $permalink, '#' => $id.'-comments');
				echo $this->Html->link(__d('blog', 'Comments(%s)', $commentCount), $commentUrl, $commentLinkOptions);
			?>
		<?php endif;?>
		<?php if (!empty($trackbackCount)): ?>
			&nbsp;|&nbsp;
			<?php
				$trackbackUrl = array('controller' => 'blog', $dates['year'], $dates['month'], $dates['day'], $permalink, '#' => $id.'-trackbacks');
				echo $this->Html->link(__d('blog', 'Trackbacks(%s)', $trackbackCount), $trackbackUrl, $trackbackLinkOptions);
			?>
		<?php endif;?>
	</span>
</footer>
