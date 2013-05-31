<?php
/**
 * BlogCommentsControllerクラス
 *
 * <pre>
 * ブログ記事のコメント表示、投稿用コントローラ
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       App.Controller
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class BlogCommentsController extends BlogAppController {

/**
 * Component name
 *
 * @var array
 */
	public $components = array('Blog.BlogCommon', 'CheckAuth' => array('allowAuth' => NC_AUTH_GENERAL));

/**
 * コメントの削除
 *
 * @param   integer $blogPostId
 * @param   integer $commentId
 * @return  void
 * @since   v 3.0.0.0
 */
	public function delete($blogPostId = null, $commentId = null) {
		if(empty($blogPostId) || empty($commentId) || !$this->request->is('post')) {
			$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'BlogComment.delete.001', '500');
			return;
		}

		// 削除するコメントの取得
		$comment = $this->BlogComment->findById($commentId);
		if(!isset($comment['BlogComment']['id'])) {
			$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'BlogComment.delete.002', '500');
			return;
		}

		// コメント削除
		$this->BlogComment->Behaviors->attach('Tree', array(
			'scope' => array('BlogComment.blog_post_id' => $blogPostId)
		));
		if(!$this->BlogComment->removeFromTree($commentId, true)) {
			$this->flash(__('Failed to delete the database, (%s).', 'blog_comments'), null, 'BlogComment.delete.003', '500');
			return;
		}

		// コメント数デクリメント
		if(!$this->BlogPost->adjustCommentCount('delete', $blogPostId, $comment['BlogComment']['is_approved'])) {
			$this->flash(__('Failed to update the database, (%s).', 'blog_posts'), null, 'Blog.comments.004', '500');
			return;
		}

		$blogPost = $this->BlogPost->findById($blogPostId);
		$this->redirect($this->BlogCommon->getDetailRedirectUrl($blogPost, 'delete', $comment['BlogComment']['parent_id']));
	}

/**
 * コメント承認
 *
 * @param   integer $blogPostId
 * @param   integer $commentId
 * @return  void
 * @since   v 3.0.0.0
 */
	public function approve($blogPostId = null, $commentId = null) {
		if(empty($blogPostId) || empty($commentId) || !$this->request->is('post')) {
			$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'BlogComment.approve.001', '500');
			return;
		}

		if(!$this->CheckAuth->checkAuth($this->hierarchy, NC_AUTH_CHIEF)) {
			$this->flash(__('Forbidden permission to access the page.'), null, 'BlogComment.approve.002', '403');
			return;
		}

		$blogPost = $this->BlogPost->findById($blogPostId);
		if(!isset($blogPost['BlogPost'])) {
			$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'BlogComment.approve.003', '500');
			return;
		}
		$blog = $this->Blog->findByContentId($blogPost['BlogPost']['content_id']);
		if(!isset($blog['Blog'])) {
			$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'BlogComment.approve.004', '500');
			return;
		}

		$comment = $this->BlogComment->findById($commentId, 'is_approved','is_approved');
		if(empty($comment['BlogComment']) || $comment['BlogComment']['is_approved']) {
			$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'BlogComment.approve.005', '500');
			return;
		}

		$this->BlogComment->id = $commentId;
		if(!$this->BlogComment->saveField('is_approved', NC_APPROVED_FLAG_ON)) {
			$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'BlogComment.approve.006', '500');
			return;
		}

		if(!$this->BlogPost->adjustCommentCount('approve', $blogPostId, $comment['BlogComment']['is_approved'], $blog['Blog']['comment_approved_flag'])) {
			$this->flash(__('Failed to update the database, (%s).', 'blog_posts'), null, 'BlogComment.approve.007', '500');
			return;
		}

		$this->redirect($this->BlogCommon->getDetailRedirectUrl($blogPost, 'approve', $commentId));
	}
}