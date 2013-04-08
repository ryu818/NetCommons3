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
		$blog_term = $this->findById($check['parent']);
		if(!isset($blog_term['BlogTerm']) || $blog_term['BlogTerm']['parent'] != '0')
			return false;
		return true;
	}

/**
 * Model BlogPost からタグ一覧、カテゴリー一覧の取得
 * @param   Model BlogPost $blog_posts
 * @return  array(blog_post_id.'_'.taxonomy => Model BlogTerms)
 * @since   v 3.0.0.0
 */
	public function findByBlogPosts($blog_posts) {
		$rets = array();
		if(count($blog_posts) == 0) {
			return $rets;
		}
		foreach($blog_posts as $blog_post) {
			$rets[$blog_post['BlogPost']['id']] = $this->findByBlogPostId($blog_post['BlogPost']['id']);
		}
		return $rets;
	}

/**
 * BlogTermLink.blog_post_idから取得
 * @param   integer $blog_post_id
 * @param   string  INNER OR LEFT $joins_type
 * @param   string  $taxonomy
 * @return  Model BlogTerms BlogTermLinks
 * @since   v 3.0.0.0
 */
	public function findByBlogPostId($blog_post_id, $joins_type = 'INNER', $taxonomy = '') {
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
				'type' => $joins_type,
				'alias' => 'BlogTermLink',
				'table' => 'blog_term_links',
				'conditions' => array(
					'BlogTermLink.blog_term_id = BlogTerm.id',
					'BlogTermLink.blog_post_id' => $blog_post_id
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
 * @param   integer $content_id
 * @param   integer $visible_item
 * @param   string  $taxonomy	category or tag
 * @param   boolean $is_chief
 * @return  void
 * @since   v 3.0.0.0
 */
	public function findTerms($content_id, $visible_item, $taxonomy) {
		$conditions = array(
			'BlogTerm.content_id' => $content_id,
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
 * @param   integer $content_id
 * @param   integer $blog_post_id
 * @param   array   $active_category_arr アクティブなカテゴリー名称　記事投稿、カテゴリー追加直後
 * @return  array($categories, $tags)
 * @since   v 3.0.0.0
 */
	public function findCategories($content_id, $blog_post_id = null, $active_category_arr = null) {
		$conditions = array(
			'BlogTerm.content_id' => $content_id,
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
		if(isset($blog_post_id)) {
			$params['fields'][] = 'BlogTermLink.blog_post_id';
			$params['joins'] = array(
				array(
					'type' => 'LEFT',
					'alias' => 'BlogTermLink',
					'table' => 'blog_term_links',
					'conditions' => array(
						'BlogTermLink.blog_term_id = BlogTerm.id',
						'BlogTermLink.blog_post_id' => $blog_post_id
					)
				)
			);
			$params['order'] = array("BlogTermLink.blog_post_id" => "DESC", "BlogTerm.count" => "DESC", "BlogTerm.id" => "DESC");
		}

		$blog_terms = $this->find('all', $params);

		$categories = array();
		foreach($blog_terms as $blog_term) {
			if(is_array($active_category_arr)) {
				$blog_term['BlogTerm']['checked'] = false;
				if(in_array($blog_term['BlogTerm']['id'] , $active_category_arr)) {
					$blog_term['BlogTerm']['checked'] = true;
				}
			} else if(isset($blog_post_id)) {
				$blog_term['BlogTerm']['checked'] = false;
				if(isset($blog_term['BlogTermLink']) && isset($blog_term['BlogTermLink']['blog_post_id'])) {
					$blog_term['BlogTerm']['checked'] = true;
				}
			}

			if(intval($blog_term['BlogTerm']['parent']) != 0) {
				$categories[$blog_term['BlogTerm']['parent']][1][] = $blog_term;
			} else {
				$categories[$blog_term['BlogTerm']['id']][0]['BlogTerm'] = $blog_term['BlogTerm'];
			}
		}

		return $categories;
	}

/**
 * カテゴリ一覧、タグ一覧の体裁を整える
 * @param   integer $content_id
 * @param   integer $blog_post_id
 * @param   array   $active_tag_arr アクティブなタグ名称　記事投稿直後
 * @return  array($categories, $tags)
 * @since   v 3.0.0.0
 */
	public function findTags($content_id, $blog_post_id = null, $active_tag_arr = null) {
		$conditions = array(
			'BlogTerm.content_id' => $content_id,
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
		if(isset($blog_post_id)) {
			$params['fields'][] = 'BlogTermLink.blog_post_id';
			$params['joins'] = array(
				array(
					'type' => 'LEFT',
					'alias' => 'BlogTermLink',
					'table' => 'blog_term_links',
					'conditions' => array(
						'BlogTermLink.blog_term_id = BlogTerm.id',
						'BlogTermLink.blog_post_id' => $blog_post_id
					)
				)
			);
			$params['order'] = array("BlogTermLink.blog_post_id" => "DESC", "BlogTerm.count" => "DESC", "BlogTerm.id" => "DESC");
		}

		$blog_terms = $this->find('all', $params);

		$tags = array();
		$tags_names_arr = array();
		foreach($blog_terms as $blog_term) {
			if(is_array($active_tag_arr)) {
				$blog_term['BlogTerm']['checked'] = false;
				if(in_array($blog_term['BlogTerm']['name'] , $active_tag_arr)) {
					$blog_term['BlogTerm']['checked'] = true;
				}
			} else if(isset($blog_post_id)) {
				$blog_term['BlogTerm']['checked'] = false;
				if(isset($blog_term['BlogTermLink']) && isset($blog_term['BlogTermLink']['blog_post_id'])) {
					$blog_term['BlogTerm']['checked'] = true;
				}
			}
			$tags_names_arr[] = $blog_term['BlogTerm']['name'];
			$tags[] = $blog_term;
		}
		if(is_array($active_tag_arr)) {
			foreach($active_tag_arr as $tag_name) {
				if(!in_array($tag_name , $tags_names_arr)) {
					$tags[] = array(
						'BlogTerm' => array(
							'checked' => true,
							'name' => $tag_name
						)
					);
				}
			}
		}

		return $tags;
	}
}