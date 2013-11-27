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
	public $components = array('Security', 'Blog.BlogCommon', 'CheckAuth' => array('allowAuth' => NC_AUTH_GENERAL));

/**
 * 実行前処理
 * <pre>Tokenチェック処理</pre>
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function beforeFilter() {
		parent::beforeFilter();
		if($this->action == "delete" || $this->action == "approve") {
			$this->Security->validatePost = false;
			$this->Security->csrfUseOnce = false;
			// 手動でチェック
			$requestToken = $this->request->data['_Token']['key'];
			$csrfTokens = $this->Session->read('_Token.csrfTokens');
			if (!isset($csrfTokens[$requestToken]) || $csrfTokens[$requestToken] < time()) {
				$this->errorToken();
				return;
			}
		}
	}

/**
 * コメント、トラックバックの削除
 *
 * @param   integer $blogPostId
 * @param   integer $commentId
 * @return  void
 * @since   v 3.0.0.0
 */
	public function delete($blogPostId = null, $commentId = null) {
		if(empty($blogPostId) || empty($commentId) || !$this->request->is('post')) {
			throw new BadRequestException(__('Unauthorized request.<br />Please reload the page.'));
		}

		// 削除するコメントの取得
		$comment = $this->BlogComment->findById($commentId);
		if(!isset($comment['BlogComment']['id'])) {
			$this->response->statusCode('404');
			$this->flash(__('Content not found.'), '');
			return;
		}

		$blogPost = $this->BlogPost->findById($blogPostId);
		if(!isset($blogPost['BlogPost'])) {
			$this->response->statusCode('404');
			$this->flash(__('Content not found.'), '');
			return;
		}
		// TODO:削除する人の権限チェック
// 		$isOverChief = $this->CheckAuth->checkAuth($this->hierarchy, NC_AUTH_CHIEF);
// 		if(!$isOverChief) {
// 			$this->response->statusCode('403');
// 			$this->flash(__('Forbidden permission to access the page.'), '');
// 			return;
// 		}

		// コメント削除
		$this->BlogComment->Behaviors->attach('Tree', array(
			'scope' => array('BlogComment.blog_post_id' => $blogPostId)
		));
		if(!$this->BlogComment->removeFromTree($commentId, true)) {
			throw new InternalErrorException(__('Failed to delete the database, (%s).', 'blog_comments'));
		}

		// コメント数デクリメント
		if(empty($this->request->query['is_trackback'])) {
			$result = $this->BlogPost->adjustCommentCount('delete', $blogPostId, $comment['BlogComment']['is_approved']);
		} else {
			$result = $this->BlogPost->adjustTrackbackCount('delete', $blogPostId, $comment['BlogComment']['is_approved']);
		}
		if(!$result) {
			throw new InternalErrorException(__('Failed to update the database, (%s).', 'blog_posts'));
		}

		$this->Session->setFlash(__('Has been successfully deleted.'));

		if(isset($this->request->query['is_trackback'])) {
			$this->redirect($this->BlogCommon->getDetailRedirectUrl($blogPost, 'trackback'));
		}
		$this->redirect($this->BlogCommon->getDetailRedirectUrl($blogPost, 'delete', $comment['BlogComment']['parent_id']));
	}

/**
 * コメント、トラックバック承認
 *
 * @param   integer $blogPostId
 * @param   integer $commentId
 * @return  void
 * @since   v 3.0.0.0
 */
	public function approve($blogPostId = null, $commentId = null) {
		if(empty($blogPostId) || empty($commentId) || !$this->request->is('post')) {
			throw new BadRequestException(__('Unauthorized request.<br />Please reload the page.'));
		}

		$isOverChief = $this->CheckAuth->checkAuth($this->hierarchy, NC_AUTH_CHIEF);
		if(!$isOverChief) {
			$this->response->statusCode('403');
			$this->flash(__('Authority Error!  You do not have the privilege to access this page.'), '');
			return;
		}

		$blogPost = $this->BlogPost->findById($blogPostId);
		if(!isset($blogPost['BlogPost'])) {
			$this->response->statusCode('404');
			$this->flash(__('Content not found.'), '');
			return;
		}
		$blog = $this->Blog->findByContentId($blogPost['BlogPost']['content_id']);
		if(!isset($blog['Blog'])) {
			$this->response->statusCode('404');
			$this->flash(__('Content not found.'), '');
			return;
		}

		$comment = $this->BlogComment->findById($commentId, 'is_approved');
		if(empty($comment['BlogComment'])) {
			$this->response->statusCode('404');
			$this->flash(__('Content not found.'), '');
			return;
		}
		if($comment['BlogComment']['is_approved']) {
			$this->response->statusCode('400');
			$this->flash(__('Unauthorized request.<br />Please reload the page.'), '');
			return;
		}

		$this->BlogComment->id = $commentId;
		if(!$this->BlogComment->saveField('is_approved', NC_APPROVED_FLAG_ON)) {
			throw new InternalErrorException(__('Failed to update the database, (%s).', 'blog_comments'));
		}

		if(empty($this->request->query['is_trackback'])) {
			$result = $this->BlogPost->adjustCommentCount('approve', $blogPostId, $comment['BlogComment']['is_approved'], $blog['Blog']['comment_approved_flag']);
		} else {
			$result = $this->BlogPost->adjustTrackbackCount('approve', $blogPostId, $comment['BlogComment']['is_approved']);
		}
		if(!$result) {
			throw new InternalErrorException(__('Failed to update the database, (%s).', 'blog_posts'));
		}

		$this->Session->setFlash(__('Has been successfully updated.'));

		if(isset($this->request->query['is_trackback'])) {
			$this->redirect($this->BlogCommon->getDetailRedirectUrl($blogPost, 'trackback'));
		}
		$this->redirect($this->BlogCommon->getDetailRedirectUrl($blogPost, 'approve', $commentId));
	}
}