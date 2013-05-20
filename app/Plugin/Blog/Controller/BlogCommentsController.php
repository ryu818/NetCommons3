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
	public $components = array('CheckAuth' => array('allowAuth' => NC_AUTH_GENERAL));

/**
 * コメントの削除
 *
 * @param   integer $blogPostId
 * @param   integer $commentId
 * @return  void
 * @since   v 3.0.0.0
 */
	public function delete($blogPostId = null, $commentId = null) {
		// TODO:コメント数のカウントダウン未実装
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

		$this->redirect($this->_getRedirectUrl($blogPostId, $comment['BlogComment']['parent_id']));
	}

/**
 * コメント削除時リダイレクトURL取得
 * 		現在のページ上にほかのコメントがあれば、そのページへ
 * 		なければ、現在のページの1ページ前へリダイレクト
 *
 * @param   integer $blogPostId
 * @param   integer $parentId
 * @return  array $redirectUrl
 * @since   v 3.0.0.0
 */
	protected function _getRedirectUrl($blogPostId, $parentId) {
		$blogPost = $this->BlogPost->findById($blogPostId);
		$permalink = $blogPost['BlogPost']['permalink'];
		$int_post_date = strtotime($blogPost['BlogPost']['post_date']);
		$blogDates = strtotime($this->BlogPost->date($blogPost['BlogPost']['post_date']));

		if(!empty($parentId)) {
			$id = $this->id. '-comment-' .$parentId;
		} else {
			$id = $this->id. '-comments';
		}

		// コメント表示上限数取得
		$blogStyleOptions = $this->BlogStyle->findOptions($this->block_id, BLOG_WIDGET_TYPE_MAIN);
		if(!empty($blogStyleOptions)) {
			$commentLimit = $blogStyleOptions['BlogStyle']['visible_item_comments'];
		} else {
			$commentLimit = BLOG_DEFAULT_VISIBLE_ITEM_COMMENTS;
		}

		$page = isset($this->request->query['comment_back_page']) ? $this->request->query['comment_back_page'] : 1;

		$redirectBlogComments = $this->BlogComment->find('all', array(
			'fields' => array('BlogComment.id'),
			'conditions' => $this->BlogComment->getPaginateConditions($blogPostId),
			'page' => $page,
			'limit' => $commentLimit,
			'recursive' => -1
		));
		if(count($redirectBlogComments) == 0 && $page >= 2) {
			// 1ページ前を表示
			$page = $page - 1;
		}

		$redirectUrl = array('plugin' => 'blog', 'controller' => 'blog', 'action'=>'index',
			date('Y', $blogDates), date('m', $blogDates), date('d', $blogDates),
			$permalink, 'page' => $page,'#' => $id);

		return $redirectUrl;
	}
}