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
	public $helpers = array('Time');	// TODO:TimeZoneヘルパーをTimeで置き換えられるかどうか検証する

/**
 * Pagination
 * @var   array
 * @since   v 3.0.0.0
 */
	public $paginate = array(
		'fields' => array('BlogPost.*', 'Htmlarea.content'),
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
			'conditions' => array('block_id' => $this->block_id, 'display_flag' => _ON));
		$blog_styles = $this->BlogStyle->find('all', $params);
		if(empty($blog_styles)) {
			// コンテンツ一覧から表示
			$blog_styles = $this->BlogStyle->findDefault();
		}
		foreach($blog_styles as $blog_style) {
			switch($blog_style['BlogStyle']['widget_type']) {
				case BLOG_WIDGET_TYPE_MAIN:
					$this->_main($this->content_id, $blog_style['BlogStyle']['visible_item']);
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
 * @param   integer $content_id
 * @param   integer $visible_item
 * @return  void
 * @since   v 3.0.0.0
 */
	protected function _main($content_id, $visible_item) {
		$add_params = array();
		$user_id = $this->Auth->user('id');
		$limit = !empty($this->request->named['limit']) ? intval($this->request->named['limit']) : $visible_item;
		$taxonomy = (isset($this->request->params['taxonomy'])) ? $this->request->params['taxonomy'] : null;
		$name = (isset($this->request->params['name'])) ? $this->request->params['name'] : null;


		if(isset($this->request->params['subject'])) {
			$this->set('detail_type', 'subject');
			$add_params['BlogPost.permalink'] = $this->request->params['subject'];
		} else {
			if(isset($this->request->params['year']) && isset($this->request->params['month']) && isset($this->request->params['day'])) {
				$this->set('detail_type', 'day');
				$add_params['BlogPost.post_date >='] = gmdate( 'Y-m-d H:i:s', strtotime($this->request->params['year'].$this->request->params['month'].$this->request->params['day'].'000000') );
				$add_params['BlogPost.post_date <'] = gmdate( 'Y-m-d H:i:s', strtotime('+1 day', strtotime($this->request->params['year'].$this->request->params['month'].$this->request->params['day'].'000000')) );
			} else if(isset($this->request->params['year']) && isset($this->request->params['month'])) {
				$this->set('detail_type', 'month');
				$add_params['BlogPost.post_date >='] = gmdate( 'Y-m-d H:i:s', strtotime($this->request->params['year'].$this->request->params['month'].'01'.'000000') );
				$add_params['BlogPost.post_date <'] = gmdate( 'Y-m-d H:i:s', strtotime('+1 month', strtotime($this->request->params['year'].$this->request->params['month'].'01'.'000000')) );
			} else if(isset($this->request->params['year'])) {
				$this->set('detail_type', 'year');
				$add_params['BlogPost.post_date >='] = gmdate( 'Y-m-d H:i:s', strtotime($this->request->params['year'].'01'.'01'.'000000') );
				$add_params['BlogPost.post_date <'] = gmdate( 'Y-m-d H:i:s', strtotime('+1 year', strtotime($this->request->params['year'].'01'.'01'.'000000')) );
			} else if(isset($this->request->params['author'])) {
				$this->set('detail_type', 'author');
				$add_params['BlogPost.created_user_id'] = intval($this->request->params['author']);
			} else if(isset($taxonomy) && isset($name)) {
				if($taxonomy == 'tag') {
					$this->set('detail_type', 'tag');
				} else {
					$this->set('detail_type', 'category');
				}
				$this->paginate['fields'][]= 'BlogTerm.name';
				//$this->paginate['fields'][]= 'BlogTerm.slug';
				$this->paginate['joins'][] = array(
						'type' => 'INNER',
						'alias' => 'BlogTermLink',
						'table' => 'blog_term_links',
						'conditions' => 'BlogPost.id = BlogTermLink.blog_post_id'
				);
				$this->paginate['joins'][] = array(
					'type' => 'INNER',
					'alias' => 'BlogTerm',
					'table' => 'blog_terms',
					'conditions' => array(
						'BlogTermLink.blog_term_id = BlogTerm.id',
						'BlogTerm.slug' => $name,
						'BlogTerm.taxonomy' => $taxonomy
					)
				);
			}
		}

		$this->paginate['conditions'] = $this->BlogPost->getConditions($content_id, $user_id, $this->hierarchy);

		$this->paginate['limit'] = $limit;
		$blog_posts = $this->paginate('BlogPost',$add_params);
		if(count($add_params) > 0 && count($blog_posts) == 0) {
			$this->set('detail_type', 'none');
		}
		$this->set('blog_posts', $blog_posts);
		$this->set('blog_posts_terms', $this->BlogTerm->findByBlogPosts($blog_posts));
		$this->set('limit', $limit);

		if($taxonomy == 'category') {
			$this->set('category', $name);
		} else {
			$this->set('tag', $name);
		}

		if(isset($blog_posts[0]) && isset($this->request->params['subject'])) {
			$next_conditions = $prev_conditions = $this->BlogPost->getConditions($content_id, $user_id, $this->hierarchy);
			$params = array();

			// 前の記事取得
			$prev_conditions['BlogPost.id >'] = $blog_posts[0]['BlogPost']['id'];
			$prev_conditions['BlogPost.post_date >='] = $blog_posts[0]['BlogPost']['post_date'];
			$params['conditions'] = $prev_conditions;
			$params['order'] = array(
				'BlogPost.post_date' => 'ASC',
				'BlogPost.id' => 'ASC',
			);
			$this->set('blog_prev_post', $this->BlogPost->find('first', $params));

			// 次の記事取得
			$next_conditions['BlogPost.id <'] = $blog_posts[0]['BlogPost']['id'];
			$next_conditions['BlogPost.post_date <='] = $blog_posts[0]['BlogPost']['post_date'];
			$params['conditions'] = $next_conditions;
			$params['order'] = array(
				'BlogPost.post_date' => 'DESC',
				'BlogPost.id' => 'DESC',
			);
			$this->set('blog_next_post', $this->BlogPost->find('first', $params));
		}
	}

/**
 * 最近の投稿表示
 * @param   integer $content_id
 * @param   integer $visible_item
 * @return  void
 * @since   v 3.0.0.0
 */
	protected function _recentPosts($content_id, $visible_item) {
		$user_id = $this->Auth->user('id');
		$params = array(
			'conditions' => $this->BlogPost->getConditions($content_id, $user_id, $this->hierarchy),
			'order' => $this->paginate['order'],
			'limit' => intval($visible_item),
			'page' => 1
		);
		$this->set('blog_recent_posts', $this->BlogPost->find('all', $params));
	}

/**
 * 最近のコメント表示
 * @param   integer $content_id
 * @param   integer $visible_item
 * @return  void
 * @since   v 3.0.0.0
 */
	protected function _recentComments($content_id, $visible_item) {
		$user_id = $this->Auth->user('id');
		$this->set('blog_recent_comments', $this->BlogComment->recentComments($content_id, $visible_item, $this->BlogPost->getConditions($content_id, $user_id, $this->hierarchy)));
	}

/**
 * アーカイブ表示
 * @param   integer $content_id
 * @param   integer $visible_item
 * @return  void
 * @since   v 3.0.0.0
 */
	protected function _archives($content_id, $visible_item) {
		$user_id = $this->Auth->user('id');
		$this->set('blog_archives', $this->BlogPost->findArchives($content_id, $visible_item, $user_id, $this->hierarchy));
	}

/**
 * カテゴリー表示
 * @param   integer $content_id
 * @param   integer $visible_item
 * @return  void
 * @since   v 3.0.0.0
 */
	protected function _categories($content_id, $visible_item) {
		$this->set('blog_categories', $this->BlogTerm->findTerms($content_id, $visible_item, 'category'));
	}

/**
 * タグ表示
 * @param   integer $content_id
 * @param   integer $visible_item
 * @param   string  $taxonomy category or tag
 * @return  void
 * @since   v 3.0.0.0
 */
	protected function _tags($content_id, $visible_item, $taxonomy = 'tag') {
		$this->set('blog_tag_taxonomy', $taxonomy);
		$this->set('blog_tags', $this->BlogTerm->findTerms($content_id, $visible_item, $taxonomy));
	}

/**
 * カレンダー表示
 * @param   integer $content_id
 * @param   integer $visible_item
 * @return  void
 * @since   v 3.0.0.0
 */
	protected function _calendar($content_id) {

	}

/**
 * RSS表示
 * @param   integer $content_id
 * @param   integer $visible_item
 * @return  void
 * @since   v 3.0.0.0
 */
	protected function _rss($content_id) {

	}
}