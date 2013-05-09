<?php
/**
 * BlogPostモデル
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Model
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class BlogPost extends AppModel
{
	public $name = 'BlogPost';

	public $actsAs = array('TimeZone', 'Validation', 'Common');

	public $belongsTo = array(
		'Revision'      => array(
			'foreignKey'    => '',
			'type' => 'LEFT',
			'fields' => array('Revision.id', 'Revision.group_id', 'Revision.content',
				'Revision.revision_name', 'Revision.is_approved_pointer', 'Revision.created', 'Revision.created_user_id', 'Revision.created_user_name'),
			'conditions' => array(
				'BlogPost.revision_group_id = Revision.group_id',
				'Revision.pointer' => _ON,
				'Revision.revision_name !=' => 'auto-draft',
				'Revision.is_approved_pointer' => _ON
			),
		),
		'Content'      => array(
			'type' => 'INNER',
			'fields' => array('Content.title')
		),
		'PageUserLink' => array(
			'foreignKey'    => '',
			'fields' => array(),
			'conditions' => array(
				'BlogPost.created_user_id = PageUserLink.user_id',
				'Content.room_id = PageUserLink.room_id'
			)
		),
		'Authority'    => array(
			'foreignKey'    => '',
			'fields' => array('Authority.hierarchy'),
			'conditions' => array(
				'PageUserLink.authority_id = Authority.id',
			)
		),
	);

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
			'post_date' => array(
				'notEmpty'  => array(
					'rule' => array('notEmpty'),
					'last' => true,
					'required' => true,
					'allowEmpty' => false,
					'message' => __('Please be sure to input.')
				),
				'datetime'  => array(
					'rule' => array('datetime'),
					'last' => true,
					'allowEmpty' => true,
					'message' => __('Unauthorized pattern for %s.', __('Date-time'))
				)
			),
			'is_future' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'last' => true,
					'required' => true,
					'message' => __('The input must be a boolean.')
				)
			),
			'title' => array(
				'notEmpty'  => array(
					'rule' => array('notEmpty'),
					'last' => true,
					'required' => true,
					'allowEmpty' => false,
					'message' => __('Please be sure to input.')
				),
				'maxlength'  => array(
					'rule' => array('maxLength', NC_VALIDATOR_TITLE_LEN),
					'message' => __('The input must be up to %s characters.', NC_VALIDATOR_TITLE_LEN)
				),
			),
			'permalink' => array(
				'notEmpty'  => array(
					'rule' => array('notEmpty'),
					'last' => true,
					'required' => true,
					'allowEmpty' => false,
					'message' => __('Please be sure to input.')
				),
				'maxlength'  => array(
					'rule' => array('maxLength', NC_VALIDATOR_TITLE_LEN),
					'message' => __('The input must be up to %s characters.', NC_VALIDATOR_TITLE_LEN)
				),
			),
			'icon_name' => array(
				// TODO:未作成 共通のバリデータに通すこと
			),
			'revision_group_id' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'allowEmpty' => false,
					'message' => __('The input must be a number.')
				)
			),
			//'vote',
			'status' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'last' => true,
					'required' => true,
					'message' => __('The input must be a boolean.')
				),
			),
			'is_approved' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'last' => true,
					'required' => true,
					'message' => __('The input must be a boolean.')
				)
			),
			'pre_change_flag' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'last' => true,
					'required' => true,
					'message' => __('The input must be a boolean.')
				)
			),
			'pre_change_date' => array(
				'datetime'  => array(
					'rule' => array('datetime'),
					'last' => true,
					'allowEmpty' => true,
					'message' => __('Unauthorized pattern for %s.', __('Date-time'))
				),
				'isFutureDateTime'  => array(
					'rule' => array('isFutureDateTime'),
					'last' => true,
					'allowEmpty' => true,
					'message' => __('%s in the past can not be input.', __('Date-time'))
				),
			),
			'post_password' => array(
				'maxlength'  => array(
					'rule' => array('maxLength', 20),
					'message' => __('The input must be up to %s characters.', 20)
				),
			),
			//'trackback_link',
			'approved_comment_count' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'required' => true,
					'message' => __('The input must be a number.')
				),
			),
			'comment_count' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'required' => true,
					'message' => __('The input must be a number.')
				),
			),
			'approved_trackback_count' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'required' => true,
					'message' => __('The input must be a number.')
				),
			),
			'trackback_count' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'required' => true,
					'message' => __('The input must be a number.')
				),
			),
			'vote_count' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'required' => true,
					'message' => __('The input must be a number.')
				),
			),
		);
	}

/**
 * permalinkが重複していればリネーム
 * post_dateをグリニッジ標準日時に変換
 * @param   array $options
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function beforeSave($options = array()) {
		if(isset($this->data[$this->alias]['permalink'])) {
			$permalink = $buf_permalink = $this->data[$this->alias]['permalink'];
			$count = 0;
			while(1) {
				if(!$this->isUniqueWith(array(), array('permalink' => $permalink, 'content_id'))) {
					$count++;
					$permalink = $buf_permalink. '-' . $count;
				} else {
					break;
				}
			}
			$this->data[$this->alias]['permalink'] = $permalink;
		}
		if (!empty($this->data[$this->alias]['post_date']) ) {
			$this->data[$this->alias]['post_date'] = $this->date($this->data[$this->alias]['post_date']);
		}
		if (!empty($this->data[$this->alias]['pre_change_date']) ) {
			$this->data[$this->alias]['pre_change_date'] = $this->date($this->data[$this->alias]['pre_change_date']);
		}
		return true;
	}

/**
 * 記事投稿時初期値
 * @param   integer $content_id
 * @return  Model BlogPost
 * @since   v 3.0.0.0
 */
	public function findDefault($content_id) {
		$ret = array(
			'BlogPost' => array(
				'id' => 0,
				'content_id' => $content_id,
				'post_date' => $this->nowDate(),
				'is_future' => _OFF,
				'title' => '',
				'permalink' => '',
				'icon_name' => null,
				'revision_group_id' => 0,
				'vote' => null,
				'status' => NC_STATUS_PUBLISH,
				'is_approved' => _ON,
				'pre_change_flag' => _OFF,
				'pre_change_date' => null,
				'post_password' => '',
				'trackback_link' => '',
				'comment_count' => 0,
				'trackback_count' => 0,
				'vote_count' => 0,
			),
			'Revision' => array(
				'content' => '',
				'revision_name' => 'publish',
			)
		);

		return $ret;
	}

/**
 * 表示可能投稿一覧のconditions取得
 * @param   integer $content_id
 * @param   integer $user_id
 * @param   integer $hierarchy
 * @return  array $conditions
 * @since   v 3.0.0.0
 */
	public function getConditions($content_id, $user_id, $hierarchy) {
		if($hierarchy >= NC_AUTH_MIN_CHIEF) {
			return array(
				'BlogPost.content_id' => $content_id
			);
		}
		$hierarchy = ($hierarchy <= NC_AUTH_GUEST) ? NC_AUTH_MIN_GENERAL : $hierarchy;

		if($hierarchy >= NC_AUTH_MIN_MODERATE) {
			$separator = '<=';
		} else {
			$separator = '<';
		}

		return array(
			'BlogPost.content_id' => $content_id,
			'OR' => array(
				array(
					'BlogPost.status' => NC_STATUS_PUBLISH,
					'BlogPost.post_date <=' => $this->nowDate(),
					'BlogPost.is_approved' => _ON
				),

				'Authority.hierarchy '.$separator => $hierarchy,
				'BlogPost.created_user_id' => $user_id,
			)
		);
	}

/**
 * メイン部分の投稿のPaginateの追加conditions, joins取得
 * @param   array $requestConditions
 * 				$requestConditions {
 * 					'subject',
 * 					'year',
 * 					'month',
 * 					'day',
 * 					'author',
 * 					'tag',
 * 					'category',
 * 					'keyword',		// TODO:未対応
 * 				}
 * @return  array($addParams = array(), $joins = array())
 * @since   v 3.0.0.0
 */
	public function getPaginateConditions($requestConditions = array()) {
		$addParams = array();
		$joins = array();
		if(isset($requestConditions['subject'])) {
			$addParams['BlogPost.permalink'] = $requestConditions['subject'];
		} else {
			if(isset($requestConditions['year']) && isset($requestConditions['month']) && isset($requestConditions['day'])) {
				$addParams['BlogPost.post_date >='] = gmdate( 'Y-m-d H:i:s', strtotime($requestConditions['year'].$requestConditions['month'].$requestConditions['day'].'000000') );
				$addParams['BlogPost.post_date <'] = gmdate( 'Y-m-d H:i:s', strtotime('+1 day', strtotime($requestConditions['year'].$requestConditions['month'].$requestConditions['day'].'000000')) );
			} else if(isset($requestConditions['year']) && isset($requestConditions['month'])) {
				$addParams['BlogPost.post_date >='] = gmdate( 'Y-m-d H:i:s', strtotime($requestConditions['year'].$requestConditions['month'].'01'.'000000') );
				$addParams['BlogPost.post_date <'] = gmdate( 'Y-m-d H:i:s', strtotime('+1 month', strtotime($requestConditions['year'].$requestConditions['month'].'01'.'000000')) );
			} else if(isset($requestConditions['year'])) {
				$addParams['BlogPost.post_date >='] = gmdate( 'Y-m-d H:i:s', strtotime($requestConditions['year'].'01'.'01'.'000000') );
				$addParams['BlogPost.post_date <'] = gmdate( 'Y-m-d H:i:s', strtotime('+1 year', strtotime($requestConditions['year'].'01'.'01'.'000000')) );
			} else if(isset($requestConditions['author'])) {
				$addParams['BlogPost.created_user_id'] = intval($requestConditions['author']);
			} else if(isset($requestConditions['tag']) || isset($requestConditions['category'])) {
				if(isset($requestConditions['tag'])) {
					$taxonomy = 'tag';
					$name = $requestConditions['tag'];
				} else {
					$taxonomy = 'category';
					$name = $requestConditions['category'];
				}
				$joins[] = array(
					'type' => 'INNER',
					'alias' => 'BlogTermLink',
					'table' => 'blog_term_links',
					'conditions' => 'BlogPost.id = BlogTermLink.blog_post_id'
				);
				$joins[] = array(
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
		return array($addParams, $joins);
	}

/**
 * アーカイブ表示
 * @param   integer $content_id
 * @param   integer $visible_item
 * @param   integer $user_id
 * @param   integer $hierarchy
 * @return  void
 * @since   v 3.0.0.0
 */
	public function findArchives($content_id, $visible_item, $user_id, $hierarchy) {
		$conditions = $this->getConditions($content_id, $user_id, $hierarchy);

		$params = array(
			'fields' => array(
				'DISTINCT YEAR( BlogPost.post_date ) AS year',
				'MONTH( post_date ) AS month'
			),
			'conditions' => $conditions,
			'order' => array('BlogPost.post_date' => 'DESC'),
			'limit' => intval($visible_item),
			//'recursive' => -1,
			'page' => 1
		);
		return $this->find('all', $params);
	}

/**
 * カレントの記事の前の記事を取得
 * @param   Model BlogPost  カレントの
 * @param   integer $userId
 * @param   integer $hierarchy
 * @return  Model BlogPost
 * @since   v 3.0.0.0
 */
	public function findPrev($currentBlogPost, $userId, $hierarchy) {
		$params = array();
		$prevConditions = $this->getConditions($currentBlogPost['BlogPost']['content_id'], $userId, $hierarchy);

		// 前の記事取得
		$prevConditions['OR'] = array(
			array(
				'BlogPost.post_date' => $currentBlogPost['BlogPost']['post_date'],
				'BlogPost.id >' => $currentBlogPost['BlogPost']['id'],
			),
			'BlogPost.post_date >' => $currentBlogPost['BlogPost']['post_date'],
		);
		$params['conditions'] = $prevConditions;
		$params['order'] = array(
			'BlogPost.post_date' => 'ASC',
			'BlogPost.id' => 'ASC',
		);

		return $this->find('first', $params);
	}

/**
 * カレントの記事の次の記事を取得
 * @param   Model BlogPost  カレントの
 * @param   integer $userId
 * @param   integer $hierarchy
 * @return  Model BlogPost
 * @since   v 3.0.0.0
 */
	public function findNext($currentBlogPost, $userId, $hierarchy) {
		$params = array();
		$nextConditions = $this->getConditions($currentBlogPost['BlogPost']['content_id'], $userId, $hierarchy);

		// 前の記事取得
		$nextConditions['OR'] = array(
			array(
				'BlogPost.post_date' => $currentBlogPost['BlogPost']['post_date'],
				'BlogPost.id <' => $currentBlogPost['BlogPost']['id'],
			),
			'BlogPost.post_date <' => $currentBlogPost['BlogPost']['post_date'],
		);
		$params['conditions'] = $nextConditions;
		$params['order'] = array(
			'BlogPost.post_date' => 'DESC',
			'BlogPost.id' => 'DESC',
		);

		return $this->find('first', $params);
	}
}