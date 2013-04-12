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
 * Pagination
 * @var   array
 * @since   v 3.0.0.0
 */
	public $paginate = array(
		'fields' => array('BlogPost.*', 'Htmlarea.content', 'Authority.hierarchy'),
		'order' => array(
			'BlogPost.post_date' => 'DESC',
			'BlogPost.id' => 'DESC',
		)
	);

/**
 * ブログ記事一覧表示
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function index() {

		// TODO:is_future=_ONのものを検索し、既に過去になっていたら、termマスタのcountを更新　is_future=_OFFへ

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
		$blogPosts = $this->paginate('BlogPost',$addParams);
		if(count($addParams) > 0 && count($blogPosts) == 0) {
			$this->set('detail_type', 'none');
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
			'order' => $this->paginate['order'],
			'limit' => intval($visibleItem),
			'page' => 1
		);
		$this->set('blog_recent_posts', $this->BlogPost->find('all', $params));
	}

/**
 * 最近のコメント表示
 * @param   integer $contentId
 * @param   integer $visibleItem
 * @return  void
 * @since   v 3.0.0.0
 */
	protected function _recentComments($contentId, $visibleItem) {
		$userId = $this->Auth->user('id');
		$this->set('blog_recent_comments', $this->BlogComment->recentComments($contentId, $visibleItem, $this->BlogPost->getConditions($contentId, $userId, $this->hierarchy)));
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

		$userId = $this->Auth->user('id');
		// behaviorを利用して更新
		if(!$this->BlogPost->voting($postId, $userId)){
			$this->flash(__('Failed to update the database, (%s).', 'blog_posts'), null, 'BlogPost.vote.002', '500');
			return;
		}
		$this->Session->setFlash(__('Has been successfully updated.'));
		$this->Session->write('Blog.vote.'.$userId.'.'.$postId, true);

		$blogPost = $this->BlogPost->findById($postId);
		$this->set('blog_post', $blogPost);
		$this->render('Elements/blog/detail_footer');
	}
}