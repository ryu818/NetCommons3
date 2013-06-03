<?php
/**
 * BlogCommonComponentクラス
 *
 * <pre>
 * ブログ共通コンポーネント
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Plugin.Blog.Component
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class BlogCommonComponent extends Component {
/**
 * _controller
 *
 * @var Controller
 */
	protected $_controller = null;

/**
 * startup
 *
 * @param Controller $controller
 */
	public function startup(Controller $controller) {
		$this->_controller = $controller;
	}

/**
 * ブログ記事詳細、コメント追加、コメント編集、返信、承認、削除時のリダイレクト先URL取得
 *  内部で$this->request->params['paging']の内容を利用しています
 * @param   Model BlogPost $blogPost
 * @param   string  $mode 'add'新規コメント追加時 、'edit'コメント編集時、'reply'コメント返信時、'approve'コメント承認時、'delete'コメント削除時 指定されれば詳細記事コメントページングを指定
 * @param   integer $commentId saveしたコメントのid,deleteしたコメントのparent_id 指定されれば詳細記事コメント個所へアンカー
 * @return  array
 * @since   v 3.0.0.0
 */
	public function getDetailRedirectUrl($blogPost, $mode = null, $commentId = null) {
		$permalink = $blogPost['BlogPost']['permalink'];
		$blogDates = strtotime($this->_controller->BlogPost->date($blogPost['BlogPost']['post_date']));

		$url = array('plugin' => 'blog', 'controller' => 'blog', 'action'=>'index',
			date('Y', $blogDates), date('m', $blogDates), date('d', $blogDates),
			$permalink);

		if(isset($mode)) {
			if($mode == 'add'){
				$blogStyleOptions = $this->_controller->BlogStyle->findOptions($this->_controller->block_id, BLOG_WIDGET_TYPE_MAIN);
				if(isset($blogStyleOptions['BlogStyle']['order_comments']) && $blogStyleOptions['BlogStyle']['order_comments'] == BLOG_ORDER_COMMENTS_NEWEST) {
					$page = 1;
				} else {
					$page = isset($this->_controller->request->params['paging']['BlogComment']['pageCount']) ? $this->_controller->request->params['paging']['BlogComment']['pageCount'] : 1;
				}
			} elseif($mode == 'delete') {
				$blogStyleOptions = $this->_controller->BlogStyle->findOptions($this->_controller->block_id, BLOG_WIDGET_TYPE_MAIN);
				$page = isset($this->_controller->request->query['comment_back_page']) ? $this->_controller->request->query['comment_back_page'] : 1;
				$redirectBlogComments = $this->_controller->BlogComment->find('all', array(
					'conditions' => $this->_controller->BlogComment->getPaginateConditions($blogPost['BlogPost']['id'], $this->_controller->Auth->user('id'), $this->_controller->hierarchy, $this->_controller->Session->read('Blog.savedComment')),
					'page' => $page,
					'limit' => !empty($blogStyleOptions) ? $blogStyleOptions['BlogStyle']['visible_item_comments'] : BLOG_DEFAULT_VISIBLE_ITEM_COMMENTS,
					'recursive' => -1
				));
				if(count($redirectBlogComments) == 0 && $page >= 2) {
					// 1ページ前を表示
					$page = $page - 1;
				}
			} else {
				$page = isset($this->_controller->request->query['comment_back_page']) ? $this->_controller->request->query['comment_back_page'] : 1;
			}
			$url['page'] = $page;
		}

		$id = !empty($commentId) ? $this->_controller->id. '-comment-' .$commentId : $this->_controller->id;
		if(isset($mode) && $mode == 'delete' && empty($commentId)) {
			$id .= '-comments';
		}
		$url['#'] = $id;

		return $url;
	}

/**
 * メールの定義文セット
 * @param   Model BlogPost $blogPost
 * @param   string  $body	{X-BODY}文字列
 * @return  void
 * @since   v 3.0.0.0
 */
	public function mailAssignedTags($blogPost, $body) {
		$this->_controller->Mail->assignedTags['{X-SUBJECT}'] = $blogPost['BlogPost']['title'];
		$this->_controller->Mail->assignedTags['{X-BODY}'] = $body;
		$this->_controller->Mail->assignedTags['{X-URL}'] = $this->getDetailRedirectUrl($blogPost);
		$categories = $this->_controller->BlogTerm->findCategories($blogPost['BlogPost']['content_id'], $this->_controller->BlogPost->id, null, 'list');
		if(count($categories) > 0) {
			$this->_controller->Mail->assignedTags['{X-CATEGORY_NAME}'] = implode(',', $categories);
		} else {
			$this->_controller->Mail->assignedTags['{X-CATEGORY_NAME}'] = __('Uncategorized');
		}
		$tags = $this->_controller->BlogTerm->findTags($blogPost['BlogPost']['content_id'], $this->_controller->BlogPost->id, null, 'list');
		if(count($tags) > 0) {
			$this->_controller->Mail->assignedTags['{X-TAG_NAME}'] = implode(',', $tags);
		} else {
			$this->_controller->Mail->assignedTags['{X-TAG_NAME}'] = __('Uncategorized');
		}
	}
}