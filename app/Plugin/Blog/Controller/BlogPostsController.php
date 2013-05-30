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
	public $components = array('Security', 'Blog.BlogCommon', 'Mail', 'RevisionList', 'CheckAuth' => array('allowAuth' => NC_AUTH_GENERAL));

/**
 * Model name
 *
 * @var array
 */
	public $uses = array('Blog.BlogTermLink', 'Revision');

/**
 * 実行前処理
 * <pre>Tokenチェック処理</pre>
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function beforeFilter() {
		parent::beforeFilter();
		$this->autoRegistBeforeFilter();
		if($this->action == "delete") {
			$this->Security->csrfCheck = false;
			$this->Security->validatePost = false;
			// 手動でチェック
			if($this->action == "delete") {
				$requestToken = $this->request->data['_Token']['key'];
				$csrfTokens = $this->Session->read('_Token.csrfTokens');
				if (!isset($csrfTokens[$requestToken]) || $csrfTokens[$requestToken] < time()) {
					$this->errorToken();
					return;
				}
			}
		}
	}

/**
 * ブログ記事投稿表示・登録
 * @param   integer $postId
 * @return  void
 * @since   v 3.0.0.0
 */
	public function index($postId = null) {
		// TODO:権限チェックが未作成
		// TODO:承認処理を共通化

		// 自動保存前処理
		$autoRegistParams = $this->RevisionList->beforeAutoRegist($postId);
		$postId = $autoRegistParams['id'];
		$isAutoRegist = $autoRegistParams['isAutoRegist'];
		$status = $autoRegistParams['status'];
		$revisionName = $autoRegistParams['revision_name'];

		$blog = $this->Blog->findByContentId($this->content_id);
		if(!isset($blog['Blog'])) {
			$this->flash(__('Content not found.'), null, 'BlogPosts.index.001', '404');
			return;
		}

		if(isset($postId)) {
			// 編集
			$blogPost = $this->BlogPost->findById($postId);
			if(!isset($blogPost['BlogPost'])) {
				$this->flash(__('Content not found.'), null, 'BlogPosts.index.002', '404');
				return;
			}
			if($blogPost['BlogPost']['is_future'] == _ON || $blogPost['BlogPost']['status'] == NC_STATUS_TEMPORARY) {
				$isBeforeUpdateTermCount = false;
			} else {
				$isBeforeUpdateTermCount = true;
			}
			$beforeContent = $blogPost['Revision']['content'];
			// 自動保存等で最新のデータがあった場合、表示
			$revision = $this->Revision->findRevisions(null, $blogPost['BlogPost']['revision_group_id'], 1);
			if(isset($revision[0])) {
				$blogPost['Revision'] = $revision[0]['Revision'];
			}
		} else {
			$blog_term_links = array();
			$blogPost = $this->BlogPost->findDefault($this->content_id);
			$isBeforeUpdateTermCount = false;
			$beforeContent = '';
		}
		$beforeStatus = $blogPost['BlogPost']['status'];
		$beforeIsApproved = $blogPost['BlogPost']['is_approved'];

		$userId = $this->Auth->user('id');
		$isEdit = false;
		if(!empty($userId)) {
			if(isset($postId)) {
				$isEdit = $this->CheckAuth->isEdit($this->hierarchy, $blog['Blog']['post_hierarchy'], $blogPost['BlogPost']['created_user_id'],
					$blogPost['Authority']['hierarchy']);
			} else {
				$isEdit = $this->CheckAuth->isEdit($this->hierarchy, $blog['Blog']['post_hierarchy']);
			}
		}

		if (!$isEdit) {
			$this->flash(__('Forbidden permission to access the page.'), null, 'BlogPosts.index.003', '403');
			return;
		}

		$activeCategoryArr = null;
		$activeTagArr = null;
		if($this->request->is('post')) {
			if(!isset($this->request->data['BlogPost']) || !isset($this->request->data['Revision']['content'])) {
				$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'BlogPosts.index.004', '500');
				return;
			}
			// 登録処理
			unset($this->request->data['BlogPost']['id']);
			unset($this->request->data['BlogPost']['revision_group_id']);
			unset($this->request->data['BlogPost']['status']);
			//unset($this->request->data['BlogPost']['is_approved']);
			if(!isset($status) || ($status == NC_STATUS_TEMPORARY && $blogPost['BlogPost']['status'] == NC_STATUS_TEMPORARY_BEFORE_RELEASED)) {
				$status = $blogPost['BlogPost']['status'];
			}

			$blogPost['BlogPost'] = array_merge($blogPost['BlogPost'], $this->request->data['BlogPost']);
			$blogPost['BlogPost']['content_id'] = $this->content_id;
			$blogPost['BlogPost']['permalink'] = $blogPost['BlogPost']['title'];	// TODO:仮でtitleをセット「「/,:」等の記号を取り除いたり同じタイトルがあればリネームしたりすること。」
			$blogPost['BlogPost']['status'] = $status;
			$blogPost['BlogPost']['is_approved'] = _ON;

			$blogPost['Revision']['content'] = $this->request->data['Revision']['content'];

			$isApproved = _ON;
			if(!$isAutoRegist &&
				$blog['Blog']['approved_flag'] == _ON && $this->hierarchy  <= NC_AUTH_MODERATE) {
				// 承認機能On
				$blogPost['BlogPost']['pre_change_flag'] = (!$isAutoRegist && $blog['Blog']['approved_pre_change_flag'] == _ON) ? _ON : _OFF;
				$blogPost['BlogPost']['pre_change_date'] = null;
				$blogPost['BlogPost']['is_approved'] = _OFF;
				$isApproved = _OFF;
			}

			$pointer = _OFF;
			if((!isset($postId) || empty($blogPost['BlogPost']['pre_change_flag'])) &&
				(!isset($blogPost['Revision']['id']) || !$isAutoRegist)) {
				$pointer = _ON;
			}

			$revision = array(
				'Revision' => array(
					'group_id' => $blogPost['BlogPost']['revision_group_id'],
					'pointer' => $pointer,
					'is_approved_pointer' => ($pointer == _ON) ? $isApproved : _OFF,
					'revision_name' => $revisionName,
					'content_id' => $this->content_id,
					'content' => $this->request->data['Revision']['content']
				)
			);

			$fieldList = array(
				'content_id', 'post_date', 'title', 'permalink', 'icon_name', 'revision_group_id', 'status', 'is_approved',
				'post_password', 'trackback_link', 'pre_change_flag', 'pre_change_date',
			);

			$fieldListRevision = array(
				'group_id', 'pointer', 'is_approved_pointer', 'revision_name', 'content_id', 'content',
			);

			$activeCategoryArr = (isset($this->request->data['BlogTermLink']) && isset($this->request->data['BlogTermLink']['category_id'])) ?
				$this->request->data['BlogTermLink']['category_id'] : array();
			$activeTagArr = (isset($this->request->data['BlogTermLink']) && isset($this->request->data['BlogTermLink']['tag_name'])) ?
				$this->request->data['BlogTermLink']['tag_name'] : array();

			$this->Revision->set($revision);
			$this->BlogPost->set($blogPost);
			if($this->BlogPost->validates(array('fieldList' => $fieldList)) && $this->Revision->validates(array('fieldList' => $fieldListRevision))) {
				$this->Revision->save($revision, false, $fieldListRevision);
				$blogPost['Revision']['id'] = $this->Revision->id;
				if(empty($blogPost['BlogPost']['revision_group_id'])) {
					$blogPost['BlogPost']['revision_group_id'] = $this->Revision->id;
				}
				$postDateUtc = $this->BlogPost->dateUtc($blogPost['BlogPost']['post_date']);
				if(strtotime($postDateUtc) > strtotime($this->BlogPost->nowDate())) {
					// 未来の記事
					$blogPost['BlogPost']['is_future'] = _ON;
				} else {
					$blogPost['BlogPost']['is_future'] = _OFF;
				}

				$this->BlogPost->save($blogPost, false, $fieldList);

				if($isAutoRegist) {
					// 自動保存時後処理
					$this->RevisionList->afterAutoRegist($this->BlogPost->id);
					return;
				}

				if($blogPost['BlogPost']['is_future'] == _ON || $blogPost['BlogPost']['status'] == NC_STATUS_TEMPORARY) {
					$isAfterUpdateTermCount = false;
				} else {
					$isAfterUpdateTermCount = true;
				}
				// カテゴリー登録
				if(!$this->BlogTermLink->saveTermLinks($this->content_id, $this->BlogPost->id, $isBeforeUpdateTermCount, $isAfterUpdateTermCount,
					$activeCategoryArr, 'id', 'category')) {
					$this->flash(__('Failed to register the database, (%s).', 'blog_term_links'), null, 'BlogPosts.index.005', '500');
					return;
				}
				// タグ登録
				if(!$this->BlogTermLink->saveTermLinks($this->content_id, $this->BlogPost->id, $isBeforeUpdateTermCount, $isAfterUpdateTermCount,
						$activeTagArr, 'name', 'tag')) {
					$this->flash(__('Failed to register the database, (%s).', 'blog_term_links'), null, 'BlogPosts.index.006', '500');
					return;
				}

				// メール送信
				$mailType = $this->Mail->checkPost(isset($postId), $blog['Blog']['mail_flag'], $blogPost['BlogPost']['status'], $beforeStatus, $blogPost['BlogPost']['is_approved'], $beforeIsApproved);
				if(isset($mailType['Unapproved'])) {
					$this->Mail->moreThanHierarchy = NC_AUTH_MIN_CHIEF;
					$this->Mail->subject = __('Pending [%s]', $blog['Blog']['mail_subject']);
					$this->Mail->body = $blog['Blog']['mail_body'];
				} else if(isset($mailType['Approved'])) {
					$this->Mail->userId = $blogPost['Revision']['created_user_id'];
					$this->Mail->subject = $blog['Blog']['approved_mail_subject'];
					$this->Mail->body = $blog['Blog']['approved_mail_body'];
				}
				if(count($mailType) > 0) {
					$this->Mail->contentId = $this->content_id;

					$this->BlogCommon->mailAssignedTags($blogPost, $revision['Revision']['content']);

					if(isset($mailType['Approved']) || isset($mailType['Unapproved'])) {
						$this->Mail->send();
					}
					if(isset($mailType['Post']) && $blogPost['BlogPost']['is_future'] != _ON) {
						$this->Mail->userId = null;
						$this->Mail->moreThanHierarchy = $blog['Blog']['mail_hierarchy'];
						$this->Mail->subject = $blog['Blog']['mail_subject'];
						$this->Mail->body = $blog['Blog']['mail_body'];
						$this->Mail->send();
					}
				}

				// 新着・検索
				$archive = array(
					'Archive' => array(
						'module_id' => $this->module_id,
						'content_id' => $this->content_id,
						'model_name' => 'BlogPost',
						'unique_id' => $this->BlogPost->id,
						'status' => $blogPost['BlogPost']['status'],
						'is_approved' => $blogPost['BlogPost']['is_approved'],
						'title' => $blogPost['BlogPost']['title'],
						'content' => ($blogPost['BlogPost']['pre_change_flag']) ?  strip_tags($beforeContent) : strip_tags($revision['Revision']['content']),
						'url' => $this->BlogCommon->getDetailRedirectUrl($blogPost),
						'creared' => $postDateUtc,
					)
				);
				if(!$this->Archive->saveAuto($this->params, $archive)) {
					$this->flash(__('Failed to update the database, (%s).', 'archives'), null, 'AnnouncementPosts.index.003', '500');
					return;
				}

				// メッセージ表示
				if(empty($blogPost['BlogPost']['id'])) {
					$this->Session->setFlash(__('Has been successfully registered.'));
				} else {
					$this->Session->setFlash(__('Has been successfully updated.'));
				}

				if($status == NC_STATUS_PUBLISH) {
					// 決定の場合、メイン画面にリダイレクト
					$backId = 'blog-post' . $this->id. '-' . $this->BlogPost->id;
					$editUrl = array('controller' => 'blog', '#' => $backId);
					if(isset($this->request->query['back_query'])) {
						$editUrl = array_merge($backUrl, explode('/', $this->request->query['back_query']));
					}
					$editUrl['limit'] = isset($this->request->query['back_limit']) ? $this->request->query['back_limit'] : null;
					$editUrl['page'] = isset($this->request->query['back_page']) ? $this->request->query['back_page'] : null;
					$this->redirect($editUrl);
					return;
				} else if(!isset($postId)) {
					// 新規投稿ならば、編集画面リダイレクト
					$this->redirect(array('controller' => 'blog_posts', $this->BlogPost->id, '#' => $this->id));
					return;
				}
			}
		}

		// 履歴情報
		if(isset($blogPost['Revision']['id'])) {
			$this->set('revisions', $this->Revision->findRevisions($blogPost['Revision']['id']));
		}

		$this->set('blog', $blog);
		$this->set('blog_post', $blogPost);

		// カテゴリ一覧、タグ一覧
		$categories = $this->BlogTerm->findCategories($this->content_id, isset($postId) ? $postId : null, $activeCategoryArr);
		$tags = $this->BlogTerm->findTags($this->content_id, isset($postId) ? $postId : null, $activeTagArr);
		$this->set('categories', $categories);
		$this->set('tags', $tags);
		$this->set('post_id', $postId);
	}

/**
 * ブログ記事削除
 * @param   integer $postId
 * @return  void
 * @since   v 3.0.0.0
 */
	public function delete($postId = null) {
		if(empty($postId) || !$this->request->is('post')) {
			$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'BlogPosts.delete.001', '500');
			return;
		}
		$blogPost = $this->BlogPost->findById($postId);
		if(!isset($blogPost['BlogPost'])) {
			$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'BlogPosts.delete.002', '500');
			return;
		}

		// コメント削除
		$delConditions = array('BlogComment.blog_post_id'=>$postId);
		if(!$this->BlogComment->deleteAll($delConditions, false)){
			$this->flash(__('Failed to delete the database, (%s).', 'blog_comments'), null, 'BlogPosts.delete.003', '500');
			return;
		}

		// 一般会員が閲覧できるカウント数のカウントダウン

		if($blogPost['BlogPost']['is_future'] != _ON && $blogPost['BlogPost']['status'] != NC_STATUS_TEMPORARY  && $blogPost['BlogPost']['is_approved'] != NC_DISPLAY_FLAG_OFF){
			$termLinks = $this->BlogTermLink->findAllByBlogPostId($postId);
			if($termLinks){
				// blogに結びつくすべてのtermがカウントダウン対象
				$termIds = array();
				foreach ($termLinks as $key => $termLink){
					array_push($termIds, $termLink['BlogTermLink']['blog_term_id']);
				}
				$cntdown_conditions = array('BlogTerm.id'=>$termIds);
				if(!$this->BlogTerm->decrementSeq($cntdown_conditions, 'count')){
					$this->flash(__('Failed to update the database, (%s).', 'blog_term_links'), null, 'BlogPosts.delete.004', '500');
					return;
				}
			}
		}

		// タームリンク削除
		$delConditions = array('BlogTermLink.blog_post_id'=>$postId);
		if(!$this->BlogTermLink->deleteAll($delConditions)){
			$this->flash(__('Failed to delete the database, (%s).', 'blog_term_links'), null, 'BlogPosts.delete.005', '500');
			return;
		}

		// blog削除
		if(!$this->BlogPost->delete($postId)){
			$this->flash(__('Failed to delete the database, (%s).', 'blog_posts'), null, 'BlogPosts.delete.006', '500');
			return;
		}

		// revision削除
		if(!$this->Revision->deleteRevison($blogPost['BlogPost']['revision_group_id'])){
			$this->flash(__('Failed to delete the database, (%s).', 'revisions'), null, 'BlogPosts.delete.007', '500');
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
 * 履歴情報表示・復元処理
 * 		承認制の一般会員による復元は未承認になるが、この時点ではメールは飛ばさない仕様とする。
 * @param   integer $postId
 * @return  void
 * @since   v 3.0.0.0
 */
	public function revision($postId) {
		// TODO:権限チェックが未作成
		// TODO:復元のバリデートでそのrevision番号が本当に戻せるかどうか確認すること auto-drafutのデータ等
		$blog = $this->Blog->findByContentId($this->content_id);
		if(!isset($blog['Blog'])) {
			$blog = $this->Blog->findDefault($this->content_id);
		}
		$blogPost = $this->BlogPost->findById($postId);
		if(!isset($blogPost['BlogPost'])) {
			$this->flash(__('Content not found.'), null, 'BlogPosts.revision.001', '404');
			return;
		}

		$cancelUrl = array('action' => 'index', $postId, '#' => $this->id);
		if(!$this->RevisionList->showRegist($this->nc_block['Content']['title'], array($postId), $cancelUrl,
			$this->BlogPost, $blogPost, $this->hierarchy,
			$blog['Blog']['approved_flag'], $blog['Blog']['approved_pre_change_flag'])) {
			$this->flash(__('Content not found.'), null, 'BlogPosts.revision.002', '404');
		}

		if($this->request->is('post')) {
			// 復元時
			$this->redirect($cancelUrl);
			return;
		}
		$this->render('/Revisions/index');
	}

/**
 * 承認画面表示・「承認する」、「承認しない」実行。
 * @param   integer $postId
 * @return  void
 * @since   v 3.0.0.0
 */
	public function approve($postId) {
		// TODO:記事編集権限チェックが未作成
		$blog = $this->Blog->findByContentId($this->content_id);
		if(!isset($blog['Blog'])) {
			$blog = $this->Blog->findDefault($this->content_id);
		}
		$blogPost = $this->BlogPost->findById($postId);
		if(empty($blogPost)) {
			$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'BlogPosts.approve.001', '500');
			return;
		}

		if ($this->request->is('post') && !$this->CheckAuth->checkAuth($this->hierarchy, NC_AUTH_CHIEF)) {
			$this->flash(__('Forbidden permission to access the page.'), null, 'BlogPosts.approve.002', '403');
			return;
		}

		if(!$this->RevisionList->approve($this->BlogPost, $blogPost)) {
			$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'BlogPosts.approve.003', '500');
			return;
		}

		if($this->request->is('post') && $this->request->data['is_approve']) {
			// 承認する
			$revision = $this->Revision->findRevisions(null, $blogPost['BlogPost']['revision_group_id'], 1);

			$mailType = $this->Mail->checkPost(isset($postId), $blog['Blog']['mail_flag'], $blogPost['BlogPost']['status'], $blogPost['BlogPost']['status'], true, false);
			$this->Mail->userId = $revision[0]['Revision']['created_user_id'];
			$this->Mail->subject = $blog['Blog']['approved_mail_subject'];
			$this->Mail->body = $blog['Blog']['approved_mail_body'];
			if(count($mailType) > 0) {
				$this->Mail->contentId = $this->content_id;

				$this->BlogCommon->mailAssignedTags($blogPost, $revision[0]['Revision']['content']);
				$this->Mail->send();
				if(isset($mailType['Post']) && $blogPost['BlogPost']['is_future'] != _ON) {
					$this->Mail->userId = null;
					$this->moreThanHierarchy = $blog['Blog']['mail_hierarchy'];
					$this->Mail->subject = $blog['Blog']['mail_subject'];
					$this->Mail->body = $blog['Blog']['mail_body'];
					$this->Mail->send();
				}
			}
		}

		$this->set('dialog_id', 'blog-posts-approve-'.$this->id);
		$this->render('/Approve/index');
	}
}