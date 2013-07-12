<?php
/**
 * BlogControllerクラス
 *
 * <pre>
 * ブログメイン画面用コントローラ
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       App.Controller
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class BlogController extends BlogAppController {
/**
 * Helper
 * @var   array
 * @since   v 3.0.0.0
 */
	public $helpers = array('Time', 'CheckAuth');	// TODO:TimeZoneヘルパーをTimeで置き換えられるかどうか検証する

/**
 * Component name
 *
 * @var array
 */
	public $components = array('Security', 'Blog.BlogCommon', 'Blog.BlogTrackback','RevisionList');

/**
 * Pagination
 * @var   array
 * @since   v 3.0.0.0
 */
	public $paginate = array(
		'order' => array(
			'BlogPost.post_date' => 'DESC',
			'BlogPost.id' => 'DESC',
		)
	);

/**
 * 実行前処理
 * <pre>Tokenチェック処理</pre>
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function beforeFilter() {
		parent::beforeFilter();

		// 投票
		if($this->action == 'vote') {
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
		// トラックバック
		elseif($this->action == 'trackback') {
			$this->Security->validatePost = false;
			$this->Security->csrfCheck = false;
		}
		// 記事詳細URLの最後に /trackback をつけてトラックバックURLとした場合向け
		elseif($this->action == 'index' && $this->request->is('post') && !empty($this->request->data['url']) &&
				!empty($this->request->params['pass']) && array_search('trackback', $this->request->params['pass']) !== false) {
			$this->Security->validatePost = false;
			$this->Security->csrfCheck = false;
		}
	}

/**
 * ブログ記事一覧表示
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function index() {
		// TODO:is_future=_ONのものを検索し、既に過去になっていたら、termマスタのcountを更新　is_future=_OFFへ
		//      メール記事投稿送信の設定がされていれば、メールも送信

		// 短縮URLによるアクセス
		if($this->request->is('get') && !empty($this->request->query['p']) && is_numeric($this->request->query['p'])) {
			$tmpBlogPost = $this->BlogPost->findById($this->request->query['p']);
			if(empty($tmpBlogPost['BlogPost'])) {
				$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'Blog.index.001', '500');
				return;
			}
			$this->redirect($this->BlogCommon->getDetailRedirectUrl($tmpBlogPost));
		}

		$params = array(
			'conditions' => array('block_id' => $this->block_id, 'OR' => array('widget_type' => BLOG_WIDGET_TYPE_MAIN, 'display_flag' => _ON))
		);
		$blog_styles = $this->BlogStyle->find('all', $params);
		if(empty($blog_styles)) {
			// コンテンツ一覧から表示
			$blog_styles = $this->BlogStyle->findDefault();
		}

		foreach($blog_styles as $blog_style) {
			switch($blog_style['BlogStyle']['widget_type']) {
				case BLOG_WIDGET_TYPE_MAIN:
					if($blog_style['BlogStyle']['display_flag']) {
						$this->_main($this->content_id, $blog_style['BlogStyle']['visible_item']);
					} else {
						$limit = !empty($this->request->named['limit']) ? intval($this->request->named['limit']) : $blog_style['BlogStyle']['visible_item'];
						$this->set('limit', $limit);
					}
					break;
				case BLOG_WIDGET_TYPE_RECENT_POSTS:
					$this->_recentPosts($this->content_id, $blog_style['BlogStyle']['visible_item']);
					break;
				case BLOG_WIDGET_TYPE_RECENT_COMMENTS:
					$this->_recentComments($this->content_id, $blog_style['BlogStyle']['visible_item']);
					break;
				case BLOG_WIDGET_TYPE_ARCHIVES:
					$this->_archives($this->content_id, $blog_style['BlogStyle']['visible_item']);
					break;
				case BLOG_WIDGET_TYPE_CATEGORIES:
					$this->_categories($this->content_id, $blog_style['BlogStyle']['visible_item']);
					break;
				case BLOG_WIDGET_TYPE_NUMBER_POSTS:
					break;
				case BLOG_WIDGET_TYPE_TAGS:
					$unserialize_options = unserialize($blog_style['BlogStyle']['options']);
					if(isset($unserialize_options['taxonomy']) && $unserialize_options['taxonomy'] == BLOG_DISPLAY_TYPE_CATEGORIES) {
						$taxonomy = 'category';
					} else {
						$taxonomy = 'tag';
					}
					$this->_tags($this->content_id, $blog_style['BlogStyle']['visible_item'], $taxonomy);
					break;
				case BLOG_WIDGET_TYPE_CALENDAR:
					$this->_calendar($this->content_id);
					break;
				case BLOG_WIDGET_TYPE_RSS:
					$this->_rss($this->content_id);
					break;
			}
		}
		$blog = $this->Blog->find('first', array('conditions' => array('content_id' => $this->content_id)));
		$this->set('blog', $blog);

		$userId = $this->Auth->user('id');
		$isAdd = false;
		if(!empty($userId) && isset($blog['Blog'])) {
			$isAdd = $this->CheckAuth->isEdit($this->hierarchy, $blog['Blog']['post_hierarchy']);
		}
		$this->set('is_add', $isAdd);

		$blog_styles = $this->BlogStyle->afterFindColRow($blog_styles, true);

		$this->set('blog_styles', $blog_styles);
	}

/**
 * メイン記事表示
 * @param   integer $contentId
 * @param   integer $visibleItem
 * @return  void
 * @since   v 3.0.0.0
 */
	protected function _main($contentId, $visibleItem) {
		$addParams = array();
		$backQuery = array();
		$userId = $this->Auth->user('id');
		$page = !empty($this->request->named['page']) ? intval($this->request->named['page']) : 1;
		$limit = !empty($this->request->named['limit']) ? intval($this->request->named['limit']) : $visibleItem;
		$taxonomy = (isset($this->request->params['taxonomy'])) ? $this->request->params['taxonomy'] : null;
		$name = (isset($this->request->params['name'])) ? $this->request->params['name'] : null;

		$requestConditions = array(
			'subject' => isset($this->request->params['subject']) ? $this->request->params['subject'] : null,
			'year' => isset($this->request->params['year']) ? $this->request->params['year'] : null,
			'month' => isset($this->request->params['month']) ? $this->request->params['month'] : null,
			'day' => isset($this->request->params['day']) ? $this->request->params['day'] : null,
			'author' => isset($this->request->params['author']) ? $this->request->params['author'] : null,
			'tag' => null,
			'category' => null,
			'keyword' => isset($this->request->params['keyword']) ? $this->request->params['keyword'] : null,		// TODO:未対応
		);
		if(preg_match('/:/', $requestConditions['subject'])) {
			// :が含まれていれば、page: Or limit: とみなしスルー
			$requestConditions['subject'] = null;
		}
		if(isset($requestConditions['subject'])) {
			$this->set('detail_type', 'subject');
		} else if(isset($requestConditions['year']) && isset($requestConditions['month']) && isset($requestConditions['day'])) {
			$backQuery['back_query'] = $requestConditions['year'] . '/' . $requestConditions['month'] . '/' . $requestConditions['day'];
			$this->set('detail_type', 'day');
		} else if(isset($requestConditions['year']) && isset($requestConditions['month'])) {
			$backQuery['back_query'] = $requestConditions['year'] . '/' . $requestConditions['month'];
			$this->set('detail_type', 'month');
		} else if(isset($requestConditions['year'])) {
			$backQuery['back_query'] = $requestConditions['year'];
			$this->set('detail_type', 'year');
		} else if(isset($requestConditions['author'])) {
			$backQuery['back_query'] = 'author/' . $requestConditions['author'];
			$this->set('detail_type', 'author');
		} else if(isset($taxonomy) && isset($name)) {
			if($taxonomy == 'tag') {
				$backQuery['back_query'] = 'tag/' . $name;
				$this->set('detail_type', 'tag');
				$requestConditions['tag'] = $name;
			} else {
				$backQuery['back_query'] = 'category/' . $name;
				$this->set('detail_type', 'category');
				$requestConditions['category'] = $name;
			}
		}

		list($addParams, $this->paginate['joins']) = $this->BlogPost->getPaginateConditions($requestConditions);
		$this->paginate['conditions'] = $this->BlogPost->getConditions($contentId, $userId, $this->hierarchy);

		$this->paginate['limit'] = $limit;
		if(isset($requestConditions['subject'])) {
			$params = $this->paginate;
			$params['conditions'] = array_merge($this->paginate['conditions'], $addParams);
			$params['limit'] = 1;
			$params['page'] = 1;

			$blogPosts = $this->BlogPost->find('all',$params);

			// トラックバック受信
			if($this->request->is('post') && !empty($this->request->data['url']) &&
			 !empty($this->request->params['pass']) &&  array_search('trackback', $this->request->params['pass']) !== false) {
				$this->trackBack($blogPosts[0]);
				return;
			}
			// コメント、トラックバック取得
			if(isset($blogPosts[0])) {
				$this->_comments($blogPosts[0]);
				if($this->_trackbacks($blogPosts[0]['BlogPost']['id'], BLOG_TRACKBACK_VIEW_MAX)) {
					$this->set('trackback_is_max', BLOG_TRACKBACK_VIEW_MAX);
				}
			}
		} else {
			$blogPosts = $this->paginate('BlogPost',$addParams);
		}
		if(count($addParams) > 0 && count($blogPosts) == 0) {
			$this->set('detail_type', 'none');
		}
		// 変更後のデータの変換(pre_change_flag,pre_change_date)
		// if_futureの更新、カテゴリー、タグのカウントアップ
		$tmpIsFutureId = $tmpBlogPostToPing = $tmpCategoryArr = $tmpTagArr = array();
		foreach($blogPosts as $key => $blogPost) {
			$blogPosts[$key]['Revision']['content'] = $this->RevisionList->updatePreChange($this->BlogPost, $blogPost);

			if($blogPost['BlogPost']['is_future'] == _ON && $blogPost['BlogPost']['post_date'] <= $this->BlogPost->nowDate()) {
				array_push($tmpIsFutureId, $blogPost['BlogPost']['id']);
				$categories = $this->BlogTerm->findByBlogPostId($blogPost['BlogPost']['id'], 'INNER', 'category');
				foreach ($categories as $category) {
					array_push($tmpCategoryArr, $category['BlogTerm']['name']);
				}
				$tags = $this->BlogTerm->findByBlogPostId($blogPost['BlogPost']['id'], 'INNER', 'tag');
				foreach ($tags as $tag) {
					array_push($tmpTagArr, $tag['BlogTerm']['name']);
				}
				array_push($tmpBlogPostToPing, $blogPost);
			}
		}
		if(!empty($tmpIsFutureId)) {
			if(!empty($tmpCategoryArr)) {
				$categoryCnt = array_count_values($tmpCategoryArr);
				foreach ($categoryCnt as $key => $cnt) {
					$this->BlogTerm->incrementSeq(array('content_id' => $this->content_id, 'taxonomy' => 'category', 'name' => $key), 'count', $cnt);
				}
			}
			if(!empty($tmpTagArr)) {
				$tagCnt = array_count_values($tmpTagArr);
				foreach ($tagCnt as $key => $cnt) {
					$this->BlogTerm->incrementSeq(array('content_id' => $this->content_id, 'taxonomy' => 'tag', 'name' => $key), 'count', $cnt);
				}
			}
			foreach ($tmpBlogPostToPing as $blogPostToping) {
				$url = Router::url($this->BlogCommon->getDetailRedirectUrl($blogPostToping), true);
				$this->BlogPost->sendTrackback($blogPostToping, $url);
			}
			$this->BlogPost->updateAll(array('BlogPost.is_future' => _OFF), array('BlogPost.id' => $tmpIsFutureId));
		}

		$this->set('blog_posts', $blogPosts);
		$this->set('blog_posts_terms', $this->BlogTerm->findByBlogPosts($blogPosts));
		$this->set('limit', $limit);
		//編集の決定,編集のキャンセル,記事詳細等の一覧へ戻る, 記事削除（リダイレクト先で使用）の戻り先をセット
		if($limit != $visibleItem) {
			$backQuery['back_limit'] = $limit;
		}
		if($page != 1) {
			$backQuery['back_page'] = $page;
		}
		$this->set('backQuery', $backQuery);

		if(isset($blogPosts[0]) && isset($this->request->params['subject'])) {
			// 前の記事取得
			$this->set('blog_prev_post', $this->BlogPost->findPrev($blogPosts[0], $userId, $this->hierarchy));

			// 次の記事取得
			$this->set('blog_next_post', $this->BlogPost->findNext($blogPosts[0], $userId, $this->hierarchy));
		}
	}

/**
 * コメント表示、追加、編集、返信
 * @param   Model BlogPost $blogPost
 * @return  void
 * @since   v 3.0.0.0
 */
	protected function _comments($blogPost) {
		$blog = $this->Blog->findByContentId($this->content_id);
		if(!isset($blog['Blog'])) {
			$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'Blog.comments.001', '500');
			return;
		}

		$userId = $this->Auth->user('id');

		if($this->request->is('post')) {
			if (!empty($this->request->data['BlogComment']['comment_id'])) {
				$mode = 'edit';
			} elseif (!empty($this->request->data['BlogComment']['parent_id'])) {
				$mode = 'reply';
			} else {
				$mode = 'add';
			}
			list($savedId, $comment) = $this->_commentSave($blog, $blogPost, $mode, $userId);

		} else {
			// 編集、返信時のコメント入力フォーム内容取得
			if(isset($this->request->named['comment_edit'])) {
				$comment = $this->BlogComment->findById($this->request->named['comment_edit']);
				if(!isset($comment['BlogComment'])) {
					$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'Blog.comments.002', '500');
					return;
				}
			} else {
				$comment = $this->BlogComment->findDefault($this->content_id, $blogPost['BlogPost']['id']);
				if(isset($this->request->named['comment_reply'])) {
					$comment['BlogComment']['parent_id'] = $this->request->named['comment_reply'];
				}
			}
		}

		// 送信フォーム内容取得
		$this->set('comment', $comment);

		// ログインしていないユーザが保存したコメントはセッションで保持
		if(!empty($savedId) && empty($userId)) {
			$savedCommentAry = $this->Session->read('Blog.savedComment');
			$savedCommentAry[] = $savedId;
			$this->Session->write('Blog.savedComment', $savedCommentAry);
		}

		// コメントのrootを元にページングを設定
		$blogStyleOptions = $this->BlogStyle->findOptions($this->block_id, BLOG_WIDGET_TYPE_MAIN);
		$this->paginate = array('threaded');
		$this->paginate['limit'] = !empty($blogStyleOptions['BlogStyle']['visible_item_comments']) ? $blogStyleOptions['BlogStyle']['visible_item_comments'] : BLOG_DEFAULT_VISIBLE_ITEM_COMMENTS;
		$this->paginate['conditions'] = $this->BlogComment->getPaginateConditions($blogPost['BlogPost']['id'], $userId, $this->hierarchy, $this->Session->read('Blog.savedComment'));
		if(isset($blogStyleOptions['BlogStyle']['position_comments']) && $blogStyleOptions['BlogStyle']['position_comments'] == BLOG_POSITION_COMMENTS_LAST) {
			$this->paginate['recordCount'] = $this->BlogComment->find('count', array('conditions' => $this->paginate['conditions']));
			$this->paginate['page'] = intval(ceil($this->paginate['recordCount'] / $this->paginate['limit']));
		}
		if(isset($blogStyleOptions['BlogStyle']['order_comments']) && $blogStyleOptions['BlogStyle']['order_comments'] == BLOG_ORDER_COMMENTS_NEWEST) {
			$this->BlogComment->order = array("BlogComment.lft" => "DESC");
		}
		$rootComments = $this->paginate($this->BlogComment);

		// saveがうまくいっていた場合はリダイレクト（getDetailRedirectUrlファンクション内でページングを利用）
		if(!empty($savedId)) {
			$redirectUrl = $this->BlogCommon->getDetailRedirectUrl($blogPost, $mode, $savedId);

			// 新着・検索
			if(!$this->_commentArchiveSave($blogPost, $comment, $redirectUrl)) {
				$this->flash(__('Failed to update the database, (%s).', 'archives'), null, 'Blog.comments.003', '500');
				return;
			}
			$this->redirect($redirectUrl);
		}
		// コメントのrootを親とするtreeを取得
		$this->set('blog_comments_tree', $this->BlogComment->findCommentTree($rootComments, $userId, $this->hierarchy, $this->Session->read('Blog.savedComment')));
		// コメント投稿において、投稿者名とメールアドレスが、必須か否かを取得
		$this->set('is_required_name', $blog['Blog']['comment_required_name']);

	}

/**
 * コメント、トラックバックの新着・検索情報の追加・更新
 *
 * @param   Model BlogPost $blogPost
 * @param   Model BlogComment $comment
 * @param  array $url
 * @return   boolean
 * @since   v 3.0.0.0
 */
	protected function _commentArchiveSave($blogPost, $comment, $url) {
		// 新着・検索
		$archive = array(
			'Archive' => array(
				'parent_model_name' => 'BlogPost',
				'parent_id' => $blogPost['BlogPost']['id'],
				'module_id' => $this->module_id,
				'content_id' => $this->content_id,
				'model_name' => 'BlogComment',
				'unique_id' => $this->BlogComment->id,
				'is_approved' => $comment['BlogComment']['is_approved'],
				'title' => $blogPost['BlogPost']['title'],
				'content' => $comment['BlogComment']['comment'],
				'url' => $url,
			)
		);

		if(!$this->Archive->saveAuto($this->params, $archive)) {
			return false;
		}
		return true;
	}

/**
 * コメントの保存または更新
 *
 * @param   Model Blog $blog
 * @param   Model BlogPost $blogPost
 * @param   string $mode 'add'新規コメント追加時 、'edit'コメント編集時、'reply'コメント返信時
 * @param  integer $userId ログインしているユーザのID
 * @return  array($savedId, $comment)
 * @since   v 3.0.0.0
 */
	protected function _commentSave($blog, $blogPost, $mode, $userId) {
		$savedId = 0;
		if($mode == 'edit') {
			// 編集
			$comment = $this->BlogComment->findById( $this->request->data['BlogComment']['comment_id']);
			if(!isset($comment['BlogComment'])) {
				$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'Blog.commentSave.001', '500');
				return;
			}
		} else {
			// 新規または返信
			if(!$blog['Blog']['comment_flag']) {
				$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'Blog.commentSave.002', '500');
				return;
			}
			$comment = $this->BlogComment->findDefault($this->content_id, $blogPost['BlogPost']['id']);
			$comment['BlogComment']['parent_id'] = $this->request->data['BlogComment']['parent_id'];
			$comment['BlogComment']['author_ip'] = $this->request->clientIp(false);
			$comment['BlogComment']['author'] = empty($userId) ? $this->request->data['BlogComment']['author'] : $this->Auth->user('handle');
		}

		if(empty($userId)) {
			$comment['BlogComment']['author_email'] = $this->request->data['BlogComment']['author_email'];
			$comment['BlogComment']['author_url'] = $this->request->data['BlogComment']['author_url'];
		}
		$comment['BlogComment']['comment'] = $this->request->data['BlogComment']['comment'];

		if($blog['Blog']['comment_approved_flag'] == _ON && !$this->CheckAuth->checkAuth($this->hierarchy, NC_AUTH_CHIEF)) {
			$comment['BlogComment']['is_approved'] = NC_APPROVED_FLAG_OFF;
		}

		$fieldList = array(
			'content_id', 'blog_post_id', 'parent_id', 'comment', 'author', 'author_email', 'author_url', 'author_ip', 'is_approved', 'comment_type'
		);
		$this->BlogComment->set($comment);
		if($this->BlogComment->validates(array('fieldList' => $fieldList))) {
			$this->BlogComment->Behaviors->attach('Tree', array(
					'scope' => array('BlogComment.blog_post_id' => $blogPost['BlogPost']['id'])
			));
			if(!$this->BlogComment->save($comment, false, $fieldList)) {
				$this->flash(__('Failed to register the database, (%s).', 'blog_comments'), null, 'Blog.commentSave.003', '500');
				return;
			}
			if(!$this->BlogPost->adjustCommentCount($mode, $blogPost['BlogPost']['id'], $comment['BlogComment']['is_approved'], $blog['Blog']['comment_approved_flag'], $this->hierarchy)) {
				$this->flash(__('Failed to update the database, (%s).', 'blog_posts'), null, 'Blog.commentSave.004', '500');
				return;
			}
			if(empty($comment['BlogComment']['id'])) {
				$this->Session->setFlash(__('Has been successfully registered.'));
			} else {
				$this->Session->setFlash(__('Has been successfully updated.'));
			}

			$savedId = $this->BlogComment->id;
		}

		return array($savedId, $comment);
	}

/**
 * トラックバック表示
 * @param   Model BlogPost $blogPost
 * @return  boolean $isOverLimit	トラックバックの件数がlimitよりも多い場合true
 * @since   v 3.0.0.0
 */
	protected function _trackbacks($blogPostId, $limit = null) {
		$prams['conditions'] = $this->BlogComment->getTrackbackConditions($blogPostId, $this->hierarchy);
		if(isset($limit)) {
			$prams['limit'] = $limit + 1;
		}
		$trackbacks = $this->BlogComment->find('all', $prams);
		$count = count($trackbacks);

		$isOverLimit = false;
		if(isset($limit) && $count > $limit) {
			$isOverLimit = true;
			array_pop($trackbacks);
		}
		$this->set('trackbacks', $trackbacks);
		return $isOverLimit;
	}

/**
 * 最近の投稿表示
 * @param   integer $contentId
 * @param   integer $visibleItem
 * @return  void
 * @since   v 3.0.0.0
 */
	protected function _recentPosts($contentId, $visibleItem) {
		$userId = $this->Auth->user('id');
		$params = array(
			'conditions' => $this->BlogPost->getConditions($contentId, $userId, $this->hierarchy),
			'order' => array(
				'BlogPost.post_date' => 'DESC',
				'BlogPost.id' => 'DESC',
			),
			'limit' => intval($visibleItem),
			'page' => 1
		);
		$this->set('blog_recent_posts', $this->BlogPost->find('all', $params));
	}

/**
 * 承認済みの最近のコメント表示
 * @param   integer $contentId
 * @param   integer $visibleItem
 * @return  void
 * @since   v 3.0.0.0
 */
	protected function _recentComments($contentId, $visibleItem) {
		$userId = $this->Auth->user('id');
		$conditions = $this->BlogPost->getConditions($contentId, $userId, $this->hierarchy);
		$conditions = array_merge($conditions, array('BlogComment.is_approved' => NC_APPROVED_FLAG_ON));
		$this->set('blog_recent_comments', $this->BlogComment->recentComments($contentId, $visibleItem, $conditions));
	}

/**
 * アーカイブ表示
 * @param   integer $contentId
 * @param   integer $visibleItem
 * @return  void
 * @since   v 3.0.0.0
 */
	protected function _archives($contentId, $visibleItem) {
		$userId = $this->Auth->user('id');
		$this->set('blog_archives', $this->BlogPost->findArchives($contentId, $visibleItem, $userId, $this->hierarchy));
	}

/**
 * カテゴリー表示
 * @param   integer $contentId
 * @param   integer $visibleItem
 * @return  void
 * @since   v 3.0.0.0
 */
	protected function _categories($contentId, $visibleItem) {
		$this->set('blog_categories', $this->BlogTerm->findTerms($contentId, $visibleItem, 'category'));
	}

/**
 * タグ表示
 * @param   integer $contentId
 * @param   integer $visibleItem
 * @param   string  $taxonomy category or tag
 * @return  void
 * @since   v 3.0.0.0
 */
	protected function _tags($contentId, $visibleItem, $taxonomy = 'tag') {
		$this->set('blog_tag_taxonomy', $taxonomy);
		$this->set('blog_tags', $this->BlogTerm->findTerms($contentId, $visibleItem, $taxonomy));
	}

/**
 * カレンダー表示
 * @param   integer $contentId
 * @param   integer $visibleItem
 * @return  void
 * @since   v 3.0.0.0
 */
	protected function _calendar($contentId) {

	}

/**
 * RSS表示
 * @param   integer $contentId
 * @param   integer $visibleItem
 * @return  void
 * @since   v 3.0.0.0
 */
	protected function _rss($contentId) {

	}

/**
 * トラックバック受信用
 *
 * @param   Model $blogPost
 * @return  void
 * @since   v 3.0.0.0
 */
	public function trackback($blogPost = null) {
		if($this->request->is('get') && !empty($this->request->query['p']) && is_numeric($this->request->query['p'])) {
			$tmpBlogPost = $this->BlogPost->findById($this->request->query['p']);
			if(empty($tmpBlogPost['BlogPost'])) {
				$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'Blog.index.001', '500');
				return;
			}
			$this->redirect($this->BlogCommon->getDetailRedirectUrl($tmpBlogPost));
		}
		if(!$this->request->is('post') || empty($this->request->data['url'])) {
			$errorMessage = __d('blog', 'It is illegal trackback.');
			return $this->BlogTrackback->rtnTrackback(BLOG_TRACKBACK_FAILED, $errorMessage);
		}
		if($this->action != 'index' && $blogPost != null) {
			$errorMessage = __d('blog', 'It is illegal trackback.');
			return $this->BlogTrackback->rtnTrackback(BLOG_TRACKBACK_FAILED, $errorMessage);
		}

		if($this->action == 'index' && is_array($blogPost)) {
			$blogPost = array_merge($blogPost, $this->Blog->findByContentId($blogPost['BlogPost']['content_id']));
		}
		elseif(!empty($this->request->query['p'])) {
			if(!is_numeric($this->request->query['p'])){
				$errorMessage = __d('blog', 'It is illegal trackback.');
				return $this->BlogTrackback->rtnTrackback(BLOG_TRACKBACK_FAILED, $errorMessage);
			}
			$params = $this->BlogPost->getTrackbackParams($this->content_id, $this->request->query['p']);
			$blogPost = $this->BlogPost->find('first', $params);
		}

		if(!isset($blogPost['Blog']) || !isset($blogPost['BlogPost'])) {
			$errorMessage = __d('blog', 'It is illegal trackback.');
			return $this->BlogTrackback->rtnTrackback(BLOG_TRACKBACK_FAILED, $errorMessage);
		}
		// トラックバック受付BLOGのステータスチェック
		if($blogPost['Blog']['trackback_receive_flag'] == _OFF) {
			$errorMessage = __d('blog', 'Trackbacks are closed for this item.');
			return $this->BlogTrackback->rtnTrackback(BLOG_TRACKBACK_FAILED, $errorMessage);
		}

		// 受付済みのトラックバックははじく
		$commentParams['conditions'] = array('blog_post_id' => $blogPost['BlogPost']['id'], 'comment_type' => BLOG_TRACKBACK_TYPE_TRACKBACK, 'author_url' => $this->request->data['url']);
		$comment = $this->BlogComment->find('all', $commentParams);

		if(count($comment) > 0) {
			$errorMessage = __d('blog', 'We already have a trackback from that URL for this post.');
			return $this->BlogTrackback->rtnTrackback(BLOG_TRACKBACK_FAILED, $errorMessage);
		}

		list($error, $errorMessage, $trackbackComment) = $this->BlogTrackback->trackbackSave($blogPost);
		if($error == BLOG_TRACKBACK_SUCCEED) {
			$redirectUrl = $this->BlogCommon->getDetailRedirectUrl($blogPost, 'trackback');
			// 新着・検索
			if(!$this->_commentArchiveSave($blogPost, $trackbackComment, $redirectUrl)) {
				$errorMessage = __('Failed to execute the %s.', __d('blog', 'TrackBack'));
				return $this->BlogTrackback->rtnTrackback(BLOG_TRACKBACK_FAILED, $errorMessage);
			}
		}
		$this->BlogTrackback->rtnTrackback($error, $errorMessage);
	}

/**
 * 投票数カウントアップ
 * @param   integer $postId
 * @return  void
 * @since   v 3.0.0.0
 */
	public function vote($postId = null){

		if(empty($postId) || !$this->request->is('post')) {
			$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'Blog.vote.001', '500');
			return;
		}

		$blogPost = $this->BlogPost->findById($postId);
		if(empty($blogPost)) {
			$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'BlogPost.vote.003', '500');
			return;
		}
		$blog = $this->Blog->findByContentId($blogPost['BlogPost']['content_id']);
		if(empty($blog)) {
			$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'BlogPost.vote.004', '500');
			return;
		}

		$userId = $this->Auth->user('id');
		// behaviorを利用して更新
		if(!$this->BlogPost->voting($postId, $userId)){
			$this->flash(__('Failed to update the database, (%s).', 'blog_posts'), null, 'BlogPost.vote.002', '500');
			return;
		}

		$this->Session->setFlash(__('Has been successfully updated.'));
		$this->Session->write('Blog.vote.'.$userId.'.'.$postId, true);

		$this->set('blog', $blog);
		$this->set('blog_post', $blogPost);
		$this->render('Elements/blog/detail_footer');
	}

/**
 * トラックバックの追加取得
 *
 * @param   integer $postId
 * @return  void
 * @since   v 3.0.0.0
 */
	public function moreTrackback($postId = null){
		if(empty($postId) || !$this->request->is('get')) {
			$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'Blog.moreTrackback.001', '500');
			return;
		}
		$blogPost = $this->BlogPost->findById($postId);
		if(empty($blogPost)) {
			$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'Blog.moreTrackback.002', '500');
			return;
		}
		$this->_trackbacks($postId);
		$this->set('blog_post', $blogPost);
		$this->render('Elements/blog/trackback');
	}
}
