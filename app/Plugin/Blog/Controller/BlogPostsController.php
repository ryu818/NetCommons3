<?php
/**
 * BlogPostsControllerクラス
 *
 * <pre>
 * ブログ記事投稿画面用コントローラ
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       App.Controller
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class BlogPostsController extends BlogAppController {

/**
 * Component name
 *
 * @var array
 */
	public $components = array('Revision', 'CheckAuth' => array('allowAuth' => NC_AUTH_GENERAL));

/**
 * Model name
 *
 * @var array
 */
	public $uses = array('Blog.BlogTermLink', 'Htmlarea');

/**
 * ブログ記事投稿表示・登録
 * @param   integer $post_id
 * @return  void
 * @since   v 3.0.0.0
 */
	public function index($post_id = null) {
		// TODO:権限チェックが未作成
		// TODO:承認機能未実装
		// 		未承認の場合、最初の投稿では一時保存へ。その後、現在のstatusを引き継ぐ。
		// TODO:email送信未実装

		// 自動登録
		$isAutoRegist = false;
		if(isset($this->request->data['auto_regist']) && $this->request->data['auto_regist']) {
			$isAutoRegist = true;
		}
		if(!isset($post_id) && isset($this->request->data['autoregist_post_id']) && $this->request->data['autoregist_post_id']) {
			// 新規投稿で自動登録の2回目以降は$post_idがセットされないためセット
			$post_id = $this->request->data['autoregist_post_id'];
		}

		$blog = $this->Blog->find('first', array('conditions' => array('content_id' => $this->content_id)));
		if(!isset($blog['Blog'])) {
			$this->flash(__('Content not found.'), null, 'BlogPost.index.001', '404');
			return;
		}

		if(isset($post_id)) {
			// 編集
			$blog_post = $this->BlogPost->findById($post_id);
			if(!isset($blog_post['BlogPost'])) {
				$this->flash(__('Content not found.'), null, 'BlogPost.index.002', '404');
				return;
			}
			if($blog_post['BlogPost']['is_future'] == _ON || $blog_post['BlogPost']['status'] == NC_STATUS_TEMPORARY) {
				$is_before_update_term_count = false;
			} else {
				$is_before_update_term_count = true;
			}
		} else {
			$blog_term_links = array();
			$blog_post = $this->BlogPost->findDefault($this->content_id);
			$is_before_update_term_count = false;
			if($isAutoRegist) {
				// 自動登録で新規登録時は一時保存
				$is_temporally = _ON;
			}
		}
		$active_category_arr = null;
		$active_tag_arr = null;
		if($this->request->is('post')) {
			// 登録処理
			if(!isset($this->request->data['BlogPost']) || !isset($this->request->data['Htmlarea']['content'])) {
				$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'BlogPosts.index.003', '500');
				return;
			}
			if(!isset($is_temporally)) {
				if(!isset($this->request->data['is_temporally']) || $this->request->data['is_temporally'] == _OFF) {
					$is_temporally = _OFF;
				} else {
					$is_temporally = _ON;
				}
			}
			if(isset($this->request->data['BlogPost']['id'])) {
				// リクエストからidの変更は許さない。
				unset($this->request->data['BlogPost']['id']);
			}
			$blog_post['BlogPost'] = array_merge($blog_post['BlogPost'], $this->request->data['BlogPost']);
			$blog_post['BlogPost']['content_id'] = $this->content_id;
			$blog_post['BlogPost']['permalink'] = $blog_post['BlogPost']['title'];	// TODO:仮でtitleをセット「「/,:」等の記号を取り除いたり同じタイトルがあればリネームしたりすること。」
			if(!isset($post_id) || !$isAutoRegist) {
				// 自動登録で編集時はstatusを維持
				$blog_post['BlogPost']['status'] = ($is_temporally) ? NC_STATUS_TEMPORARY : NC_STATUS_PUBLISH;
			}

			$blog_post['Htmlarea']['content'] = $this->request->data['Htmlarea']['content'];

			$fieldList = array(
				'content_id', 'post_date', 'title', 'permalink', 'icon_name', 'htmlarea_id', 'status', 'post_password', 'trackback_link',
			);

			$htmlarea = array(
				'Htmlarea' => array(
					'revision_parent' => $blog_post['BlogPost']['htmlarea_id'],
					'revision_name' => ($isAutoRegist) ? 'auto-draft' : (($is_temporally) ? 'draft' : 'publish'),
					'content_id' => $this->content_id,
					'content' => $this->request->data['Htmlarea']['content'],
					'non_approved_content' => '',
				)
			);

			$fieldListHtmlarea = array(
				'revision_parent', 'revision_name', 'content_id', 'content', 'non_approved_content',
			);

			$active_category_arr = (isset($this->request->data['BlogTermLink']) && isset($this->request->data['BlogTermLink']['category_id'])) ?
				$this->request->data['BlogTermLink']['category_id'] : array();
			$active_tag_arr = (isset($this->request->data['BlogTermLink']) && isset($this->request->data['BlogTermLink']['tag_name'])) ?
				$this->request->data['BlogTermLink']['tag_name'] : array();

			$this->Htmlarea->set($htmlarea);
			$this->BlogPost->set($blog_post);
			if($this->BlogPost->validates(array('fieldList' => $fieldList)) && $this->Htmlarea->validates(array('fieldList' => $fieldListHtmlarea))) {
				$this->Htmlarea->save($htmlarea, false, $fieldListHtmlarea);
				$blog_post['BlogPost']['htmlarea_id'] = $this->Htmlarea->id;
				if(strtotime($this->BlogPost->date($blog_post['BlogPost']['post_date'])) > strtotime($this->BlogPost->nowDate())) {
					// 未来の記事
					$blog_post['BlogPost']['is_future'] = _ON;
				} else {
					$blog_post['BlogPost']['is_future'] = _OFF;
				}

				$this->BlogPost->save($blog_post, false, $fieldList);

				if($isAutoRegist) {
					// 自動登録時
					echo $this->BlogPost->id;
					$this->render(false);
					return;
				}

				if(empty($blog_post['BlogPost']['id'])) {
					$this->Session->setFlash(__('Has been successfully registered.'));
				} else {
					$this->Session->setFlash(__('Has been successfully updated.'));
				}
				if($blog_post['BlogPost']['is_future'] == _ON || $blog_post['BlogPost']['status'] == NC_STATUS_TEMPORARY) {
					$is_after_update_term_count = false;
				} else {
					$is_after_update_term_count = true;
				}
				// カテゴリー登録
				if(!$this->BlogTermLink->saveTermLinks($this->content_id, $this->BlogPost->id, $is_before_update_term_count, $is_after_update_term_count,
					$active_category_arr, 'id', 'category')) {
					$this->flash(__('Failed to register the database, (%s).', 'blog_term_links'), null, 'BlogPost.index.003', '500');
					return;
				}
				// タグ登録
				if(!$this->BlogTermLink->saveTermLinks($this->content_id, $this->BlogPost->id, $is_before_update_term_count, $is_after_update_term_count,
						$active_tag_arr, 'name', 'tag')) {
					$this->flash(__('Failed to register the database, (%s).', 'blog_term_links'), null, 'BlogPost.index.004', '500');
					return;
				}

				if(!$is_temporally) {
					// 決定の場合、リダイレクト
					$backId = 'blog-post' . $this->id. '-' . $this->BlogPost->id;
					$editUrl = array('controller' => 'blog', '#' => $backId);
					if(isset($this->request->query['back_query'])) {
						$editUrl = array_merge($backUrl, explode('/', $this->request->query['back_query']));
					}
					$editUrl['limit'] = isset($this->request->query['back_limit']) ? $this->request->query['back_limit'] : null;
					$editUrl['page'] = isset($this->request->query['back_page']) ? $this->request->query['back_page'] : null;
					$this->redirect($editUrl);
					return;
				} else if(!isset($post_id) || isset($this->request->data['autoregist_post_id'])) {
					// 新規投稿ならば、編集画面にするためリダイレクト
					$this->redirect(array('controller' => 'blog_posts', $this->BlogPost->id, '#' => $this->id));
					return;
				}
			}
		}

		// 履歴情報
		$this->set('revisions', $this->Htmlarea->findRevisions($blog_post['BlogPost']['htmlarea_id']));

		$this->set('blog', $blog);
		$this->set('blog_post', $blog_post);

		// カテゴリ一覧、タグ一覧
		$categories = $this->BlogTerm->findCategories($this->content_id, isset($post_id) ? $post_id : null, $active_category_arr);
		$tags = $this->BlogTerm->findTags($this->content_id, isset($post_id) ? $post_id : null, $active_tag_arr);
		$this->set('categories', $categories);
		$this->set('tags', $tags);
		$this->set('post_id', $post_id);
	}

/**
 * ブログ記事削除
 * @param   integer $postId
 * @return  void
 * @since   v 3.0.0.0
 */
	public function delete($postId = null) {
		if(empty($postId) || !$this->request->is('post')) {
			$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'BlogPost.delete.001', '500');
			return;
		}

		// コメント削除
		$delConditions = array('BlogComment.blog_post_id'=>$postId);
		if(!$this->BlogComment->deleteAll($delConditions, false)){
			$this->flash(__('Failed to delete the database, (%s).', 'blog_comments'), null, 'BlogPost.delete.002', '500');
			return;
		}

		// 一般会員が閲覧できるカウント数のカウントダウン
		$blogPost = $this->BlogPost->findById($postId);
		if($blogPost['BlogPost']['is_future'] != _ON && $blogPost['BlogPost']['status'] != NC_STATUS_TEMPORARY  && $blogPost['BlogPost']['approved_flag'] != _OFF){
			$termLinks = $this->BlogTermLink->findAllByBlogPostId($postId);
			if($termLinks){
				// blogに結びつくすべてのtermがカウントダウン対象
				$termIds = array();
				foreach ($termLinks as $key => $termLink){
					array_push($termIds, $termLink['BlogTermLink']['blog_term_id']);
				}
				$cntdown_conditions = array('BlogTerm.id'=>$termIds);
				if(!$this->BlogTerm->decrementSeq($cntdown_conditions, 'count')){
					$this->flash(__('Failed to update the database, (%s).', 'blog_term_links'), null, 'BlogPost.delete.003', '500');
					return;
				}
			}
		}

		// タームリンク削除
		$delConditions = array('BlogTermLink.blog_post_id'=>$postId);
		if(!$this->BlogTermLink->deleteAll($delConditions)){
			$this->flash(__('Failed to delete the database, (%s).', 'blog_term_links'), null, 'BlogPost.delete.004', '500');
			return;
		}

		// blog削除
		$delConditions = array('BlogPost.id'=>$postId);
		if(!$this->BlogPost->deleteAll($delConditions)){
			$this->flash(__('Failed to delete the database, (%s).', 'blog_posts'), null, 'BlogPost.delete.005', '500');
			return;
		}

		// リダイレクト
		$this->redirect($this->_getRedirectUrl($this->id, $this->block_id, $blogPost['BlogPost']['content_id'], $this->hierarchy));
	}

/**
 * ブログ記事削除時リダイレクトURL取得
 * 		現在のページ上にほかの記事があれば、そのページへ
 * 		なければ、1ペー目へリダイレクト
 * @param   integer $id
 * @param   integer $blockId
 * @param   integer $contentId
 * @param   integer $hierarchy
 * @return  array $redirectUrl
 * @since   v 3.0.0.0
 */
	protected function _getRedirectUrl($id, $blockId, $contentId, $hierarchy) {
		$userId = $this->Auth->user('id');
		$redirectUrl = array('controller' => 'blog', 'action'=> 'index', '#' => $id);
		$joins = array();
		$page = isset($this->request->query['back_page']) ? intval($this->request->query['back_page']) : 1;
		if(isset($this->request->query['back_query'])) {
			$redirectUrl = array_merge($redirectUrl, explode('/', $this->request->query['back_query']));
			if(isset($redirectUrl[0])) {
				$requestConditions = array();
				if(preg_match('/[0-9]+/', $redirectUrl[0])) {
					$requestConditions = array('year' => $redirectUrl[0]);
					if(isset($redirectUrl[1])) {
						$requestConditions = array('month' => $redirectUrl[1]);
					}
					if(isset($redirectUrl[2])) {
						$requestConditions = array('day' => $redirectUrl[2]);
					}
					if(isset($redirectUrl[3])) {
						$requestConditions = array('subject' => $redirectUrl[3]);
					}
				} else if(isset($redirectUrl[1])) {
					switch($redirectUrl[0]) {
						case 'author':
						case 'tag':
						case 'category':
						case 'keyword':
							$requestConditions = array($redirectUrl[0] => $redirectUrl[1]);
							break;
					}
				}
				if(count($requestConditions) > 0) {
					list($addParams, $joins) = $this->BlogPost->getPaginateConditions($requestConditions);
				}
			}
		}
		if(isset($page) && $page > 1) {
			$limit = $redirectUrl['limit'] = isset($this->request->query['back_limit']) ? $this->request->query['back_limit'] : null;
			if(!isset($limit)) {
				$params = array(
					'fields' => array('BlogStyle.visible_item'),
					'conditions' => array('BlogStyle.block_id' => $blockId, 'BlogStyle.widget_type' => BLOG_WIDGET_TYPE_MAIN),
					'order' => null,
				);
				$blog_style = $this->BlogStyle->find('first', $params);
				if(isset($blog_style['BlogStyle'])) {
					$limit = $blog_style['BlogStyle']['visible_item'];
				} else {
					$limit = BLOG_DEFAULT_VISIBLE_ITEM;
				}
			}
			$conditions = $this->BlogPost->getConditions($contentId, $userId, $hierarchy);
			if(isset($addParams)) {
				$conditions = array_merge($conditions, $addParams);
			}
			$redirectBlogPosts = $this->BlogPost->find('all', array(
				'fields' => array('BlogPost.id'),
				'conditions' => $conditions,
				'joins' => $joins,
				'page' => $page,
				'limit' => $limit,
				'recursive' => -1
			));
			if(count($redirectBlogPosts) > 0) {
				$redirectUrl['page'] = $page;
			} else if($page > 2) {
				// 1ページ前を表示
				$redirectUrl['page'] = $page - 1;
			}
		}
		return $redirectUrl;
	}

/**
 * 履歴情報表示
 * @param   integer $blogPostId
 * @return  void
 * @since   v 3.0.0.0
 */
	public function revision($blogPostId) {
		$blogPost = $this->BlogPost->findById($blogPostId);
		if(!isset($blogPost['BlogPost'])) {
			$this->flash(__('Content not found.'), null, 'BlogPost.revision.001', '404');
			return;
		}
		$cancelUrl = array('action' => 'index', $blogPostId, '#' => $this->id);

		$newHtmlareaId = $this->Revision->setDatas($this->content_id, $blogPost['BlogPost']['title'], $blogPost['BlogPost']['htmlarea_id'],
			array($blogPostId), $cancelUrl);
		if($newHtmlareaId === false) {
			$this->flash(__('Content not found.'), null, 'BlogPost.revision.002', '404');
			return;
		}

		if($this->request->is('post') && $newHtmlareaId > 0) {
			$fieldList = array(
				'htmlarea_id',
			);
			$blogPost['BlogPost']['htmlarea_id'] = $newHtmlareaId;
			if(!$this->BlogPost->save($blogPost, true, $fieldList)) {
				$this->flash(__('Failed to update the database, (%s).', 'blog_posts'), null, 'BlogPost.revision.003', '500');
				return;
			}
			$this->redirect($cancelUrl);
			return;
		}
		$this->render('/Revisions/index');
	}
}