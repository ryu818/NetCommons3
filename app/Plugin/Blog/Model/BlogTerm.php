<?php
/**
 * BlogTermモデル
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Model
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class BlogTerm extends AppModel
{
	public $name = 'BlogTerm';

	public $actsAs = array('Validation', 'Common');

	public $order = array("BlogTerm.count" => "DESC", "BlogTerm.id" => "DESC");

/**
 * バリデート処理
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function __construct() {
		parent::__construct();

		include_once dirname(dirname(__FILE__)).'/Config/defines.inc.php';

		/*
		 * エラーメッセージ設定
		*/
		$this->validate = array(
			'content_id' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'required' => true,
					'allowEmpty' => false,
					'message' => __('The input must be a number.')
				)
			),
			'name' => array(
				'notEmpty'  => array(
					'rule' => array('notEmpty'),
					'last' => true,
					'required' => true,
					'message' => __('Please be sure to input.')
				),
				'maxlength'  => array(
					'rule' => array('maxLength', NC_VALIDATOR_TITLE_LEN),
					'message' => __('The input must be up to %s characters.', NC_VALIDATOR_TITLE_LEN)
				),
				'isUniqueWith'  => array(
					'rule' => array('isUniqueWith', array('content_id', 'name', 'taxonomy')),
					'message' => __('The same name is already in use.Please choose another one.')
				),
			),
			'slug' => array(
				'notEmpty'  => array(
					'rule' => array('notEmpty'),
					'last' => true,
					'required' => true,
					'message' => __('Please be sure to input.')
				),
				'maxlength'  => array(
					'rule' => array('maxLength', NC_VALIDATOR_TITLE_LEN),
					'message' => __('The input must be up to %s characters.', NC_VALIDATOR_TITLE_LEN)
				),
				// beforeSaveでリネームするためコメント
				//'isUniqueWith'  => array(
				//	'rule' => array('isUniqueWith', array('slug', 'taxonomy')),
				//	'message' => __('The same name is already in use.Please choose another one.')
				//)
			),
			'taxonomy' => array(
				'inList' => array(
					'rule' => array('inList', array(
						'category',
						'tag',
					)),
					'allowEmpty' => false,
					'message' => __('It contains an invalid string.')
				)
			),
			'checked' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'last' => true,
					'required' => true,
					'message' => __('The input must be a boolean.')
				)
			),
			'parent' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'required' => true,
					'message' => __('The input must be a number.')
				),
				'existsParent'  => array(
					'rule' => array('_existsParent'),
					'message' => __d('blog', 'Parent category does not exist.')
				)
			),
			// TODO:未来の日付の記事、一時保存の記事、未承認の記事が存在するが、ここのcountは、一般会員が閲覧できるカウント数の合計とする。
			// 現状、カウントアップ-ダウン処理未実装
			'count' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'required' => true,
					'message' => __('The input must be a number.')
				),
			),
		);
	}

/**
 * slugが重複していればリネーム
 * @param   array $options
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function beforeSave($options = array()) {
		if(isset($this->data[$this->alias]['slug']) && isset($this->data[$this->alias]['taxonomy'])) {
			$slug = $buf_slug = $this->data[$this->alias]['slug'];
			$count = 0;
			while(1) {
				if(!$this->isUniqueWith(array(), array('slug' => $slug, 'taxonomy'))) {
					$count++;
					$slug = $buf_slug. '-' . $count;
				} else {
					break;
				}

			}
			$this->data[$this->alias]['slug'] = $slug;
		}
		return true;
	}

/**
 * parentチェック(現状、1階層までしか許していない。投稿画面：jquery.chosenにより表示しているため)
 * 0ならばOK
 * @param   array    $check
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function _existsParent($check){
		if($check['parent'] == 0) {
			return true;
		}
		$blogTerm = $this->findById($check['parent']);
		if(!isset($blogTerm['BlogTerm']) || $blogTerm['BlogTerm']['parent'] != '0')
			return false;
		return true;
	}

/**
 * Model BlogPost からタグ一覧、カテゴリー一覧の取得
 * @param   Model BlogPost $blogPosts
 * @return  array(blog_post_id.'_'.taxonomy => Model BlogTerms)
 * @since   v 3.0.0.0
 */
	public function findByBlogPosts($blogPosts) {
		$rets = array();
		if(count($blogPosts) == 0) {
			return $rets;
		}
		foreach($blogPosts as $blogPost) {
			$rets[$blogPost['BlogPost']['id']] = $this->findByBlogPostId($blogPost['BlogPost']['id']);
		}
		return $rets;
	}

/**
 * BlogTermLink.blog_post_idから取得
 * @param   integer $blogPostId
 * @param   string  INNER OR LEFT $joinsType
 * @param   string  $taxonomy
 * @return  Model BlogTerms BlogTermLinks
 * @since   v 3.0.0.0
 */
	public function findByBlogPostId($blogPostId, $joinsType = 'INNER', $taxonomy = '') {
		$params = array(
			'fields' => array(
				'BlogTerm.id',
				'BlogTerm.name',
				'BlogTerm.slug',
				'BlogTerm.taxonomy',
				'BlogTerm.count',
				'BlogTermLink.id',
				'BlogTermLink.blog_post_id',
				'BlogTermLink.blog_term_id',
			),
			'joins' => array(array(
				'type' => $joinsType,
				'alias' => 'BlogTermLink',
				'table' => 'blog_term_links',
				'conditions' => array(
					'BlogTermLink.blog_term_id = BlogTerm.id',
					'BlogTermLink.blog_post_id' => $blogPostId
				)
			)),
		);
		if($taxonomy == 'category' || $taxonomy == 'tag') {
			$params['conditions'] = array(
				'BlogTerm.taxonomy' => $taxonomy
			);
		}
		return $this->find('all', $params);
	}

/**
 * カテゴリ一覧、タグ一覧取得
 * @param   integer $contentId
 * @param   integer $visible_item
 * @param   string  $taxonomy	category or tag
 * @param   boolean $is_chief
 * @return  void
 * @since   v 3.0.0.0
 */
	public function findTerms($contentId, $visible_item, $taxonomy) {
		$conditions = array(
			'BlogTerm.content_id' => $contentId,
			'BlogTerm.taxonomy' => $taxonomy
		);

		$params = array(
			'fields' => array(
				'BlogTerm.name',
				'BlogTerm.slug',
				'BlogTerm.taxonomy',
				'BlogTerm.parent',
				'BlogTerm.count',
			),
			'conditions' => $conditions,
			'limit' => intval($visible_item),
			'page' => 1
		);
		return $this->find('all', $params);
	}

/**
 * カテゴリ一覧、タグ一覧の体裁を整える
 * @param   integer $contentId
 * @param   integer $blogPostId
 * @param   array   $activeCategoryArr アクティブなカテゴリー名称　記事投稿、カテゴリー追加直後
 * @param   string  'thread' or 'list'
 * @return  array($categories, $tags)
 * @since   v 3.0.0.0
 */
	public function findCategories($contentId, $blogPostId = null, $activeCategoryArr = null, $type = 'thread') {
		$conditions = array(
			'BlogTerm.content_id' => $contentId,
			'BlogTerm.taxonomy' => 'category'
		);

		$params = array(
			'fields' => array(
				'BlogTerm.id',
				'BlogTerm.name',
				'BlogTerm.slug',
				'BlogTerm.taxonomy',
				'BlogTerm.parent',
				'BlogTerm.count',
				'BlogTerm.checked',
			),
			'conditions' => $conditions,
		);
		if(isset($blogPostId)) {
			$params['fields'][] = 'BlogTermLink.blog_post_id';
			$params['joins'] = array(
				array(
					'type' => 'LEFT',
					'alias' => 'BlogTermLink',
					'table' => 'blog_term_links',
					'conditions' => array(
						'BlogTermLink.blog_term_id = BlogTerm.id',
						'BlogTermLink.blog_post_id' => $blogPostId
					)
				)
			);
			$params['order'] = array("BlogTermLink.blog_post_id" => "DESC", "BlogTerm.count" => "DESC", "BlogTerm.id" => "DESC");
		}

		$blogTerms = $this->find('all', $params);

		$categories = array();
		foreach($blogTerms as $blogTerm) {
			if(is_array($activeCategoryArr)) {
				$blogTerm['BlogTerm']['checked'] = false;
				if(in_array($blogTerm['BlogTerm']['id'] , $activeCategoryArr)) {
					$blogTerm['BlogTerm']['checked'] = true;
				}
			} else if(isset($blogPostId)) {
				$blogTerm['BlogTerm']['checked'] = false;
				if(isset($blogTerm['BlogTermLink']) && isset($blogTerm['BlogTermLink']['blog_post_id'])) {
					$blogTerm['BlogTerm']['checked'] = true;
				}
			}

			if($type == 'thread') {
				if(intval($blogTerm['BlogTerm']['parent']) != 0) {
					$categories[$blogTerm['BlogTerm']['parent']][1][] = $blogTerm;
				} else {
					$categories[$blogTerm['BlogTerm']['id']][0]['BlogTerm'] = $blogTerm['BlogTerm'];
				}
			} else if($blogTerm['BlogTerm']['checked'] == true) {
				$categories[] = $blogTerm['BlogTerm']['name'];
			}
		}

		return $categories;
	}

/**
 * カテゴリ一覧、タグ一覧の体裁を整える
 * @param   integer $contentId
 * @param   integer $blogPostId
 * @param   array   $activeTagArr アクティブなタグ名称　記事投稿直後
 * @param   string  'thread' or 'list'
 * @return  array($categories, $tags)
 * @since   v 3.0.0.0
 */
	public function findTags($contentId, $blogPostId = null, $activeTagArr = null, $type = 'thread') {
		$conditions = array(
			'BlogTerm.content_id' => $contentId,
			'BlogTerm.taxonomy' => 'tag'
		);

		$params = array(
			'fields' => array(
				'BlogTerm.id',
				'BlogTerm.name',
				'BlogTerm.slug',
				'BlogTerm.taxonomy',
				'BlogTerm.parent',
				'BlogTerm.count',
				'BlogTerm.checked',
			),
			'conditions' => $conditions,
			//'limit' => BLOG_SHOW_MAX_TAGS
		);
		if(isset($blogPostId)) {
			$params['fields'][] = 'BlogTermLink.blog_post_id';
			$params['joins'] = array(
				array(
					'type' => 'LEFT',
					'alias' => 'BlogTermLink',
					'table' => 'blog_term_links',
					'conditions' => array(
						'BlogTermLink.blog_term_id = BlogTerm.id',
						'BlogTermLink.blog_post_id' => $blogPostId
					)
				)
			);
			$params['order'] = array("BlogTermLink.blog_post_id" => "DESC", "BlogTerm.count" => "DESC", "BlogTerm.id" => "DESC");
		}

		$blogTerms = $this->find('all', $params);

		$tags = array();
		$tagsNamesArr = array();
		foreach($blogTerms as $blogTerm) {
			if(is_array($activeTagArr)) {
				$blogTerm['BlogTerm']['checked'] = false;
				if(in_array($blogTerm['BlogTerm']['name'] , $activeTagArr)) {
					$blogTerm['BlogTerm']['checked'] = true;
				}
			} else if(isset($blogPostId)) {
				$blogTerm['BlogTerm']['checked'] = false;
				if(isset($blogTerm['BlogTermLink']) && isset($blogTerm['BlogTermLink']['blog_post_id'])) {
					$blogTerm['BlogTerm']['checked'] = true;
				}
			}
			$tagsNamesArr[] = $blogTerm['BlogTerm']['name'];
			if($type == 'thread') {
				$tags[] = $blogTerm;
			} else if($blogTerm['BlogTerm']['checked'] == true) {
				$tags[] = $blogTerm['BlogTerm']['name'];
			}
		}
		if(is_array($activeTagArr)) {
			foreach($activeTagArr as $tagName) {
				if(!in_array($tagName , $tagsNamesArr)) {
					$tags[] = array(
						'BlogTerm' => array(
							'checked' => true,
							'name' => $tagName
						)
					);
				}
			}
		}

		return $tags;
	}
}