<?php
/**
 * BlogCommentモデル
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Model
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class BlogComment extends AppModel
{
	public $order = array("lft" => "ASC");

	public $actsAs = array('Tree');

/**
 * construct
 * @param integer|string|array $id Set this ID for this model on startup, can also be an array of options, see above.
 * @param string $table Name of database table to use.
 * @param string $ds DataSource connection name.
 * @return  void
 * @since   v 3.0.0.0
 */
	public function __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);

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
				),
			),
			'blog_post_id' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'required' => true,
					'allowEmpty' => false,
					'message' => __('The input must be a number.')
				),
			),
			'parent_id' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'allowEmpty' => true,
					'message' => __('The input must be a number.')
				),
			),
			'title' => array(
				'maxLength'  => array(
					'rule' => array('maxLength', NC_VALIDATOR_VARCHAR_LEN),
					'message' => __('The input must be up to %s characters.', NC_VALIDATOR_VARCHAR_LEN),
					'allowEmpty' => true,
				),
			),
			'comment' => array(
				'notEmpty'  => array(
					'rule' => array('notEmpty'),
					'message' => __('Please input %s.', __('Comment')),
					'required' => true,
				),
			),
			'author' => array(
				'maxLength'  => array(
					'rule' => array('maxLength', NC_VALIDATOR_USER_NAME_LEN),
					'message' => __('The input must be up to %s characters.', NC_VALIDATOR_USER_NAME_LEN),
				),
			),
			'author_email' => array(
				'email' => array(
					'rule' => array('email'),
					'last' => true,
					'message' => __('The input must be a %s.', __('E-mail')),
					'allowEmpty' => true,
				),
				'maxLength'  => array(
					'rule' => array('maxLength', NC_VALIDATOR_VARCHAR_LEN),
					'message' => __('The input must be up to %s characters.', NC_VALIDATOR_VARCHAR_LEN)
				),
			),
			'author_url' => array(
				'url' => array(
					'rule' => array('url'),
					'last' => true,
					'message' => __('The input must be a %s.', __('URL')),
					'allowEmpty' => true,
				),
				'maxLength'  => array(
					'rule' => array('maxLength', NC_VALIDATOR_VARCHAR_LEN),
					'message' => __('The input must be up to %s characters.', NC_VALIDATOR_VARCHAR_LEN)
				),
			),
			'author_ip' => array(
				'ip' => array(
					'rule' => array('ip'),
					'last' => true,
					'message' => __('The input must be a %s.', __('IP')),
				),
				'maxLength'  => array(
					'rule' => array('maxLength', 16),
					'message' => __('The input must be up to %s characters.', 16)
				),
			),
			'is_approved' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'required' => true,
					'message' => __('The input must be a boolean.')
				),
			),
			'blog_name' => array(
				'maxLength'  => array(
					'rule' => array('maxLength', NC_VALIDATOR_VARCHAR_LEN),
					'message' => __('The input must be up to %s characters.', NC_VALIDATOR_VARCHAR_LEN),
					'allowEmpty' => true,
				),
			),
		);
	}

/**
 * beforeDelete
 * @param   void
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function beforeDelete($cascade = true) {
		$Archive = ClassRegistry::init('Archive');
		// アーカイブ削除
		if(!$Archive->deleteUnique($this->alias, $this->id)) {
			return false;
		}
		return true;
	}


/**
* コメントトラックバック更新後処理
* is_approvedが_ONならば、該当が存在しているならば、Archiveを更新
* @param boolean $created
* @param   array   $options
* @return boolean
* @since v 3.0.0.0
*/
	public function afterSave($created, $options = array()) {
		if (!$created) {
			$Archive = ClassRegistry::init('Archive');
			if($this->data[$this->alias]['is_approved'] == NC_APPROVED_FLAG_ON) {
				if(!$Archive->updateApprove('BlogComment', $this->id, $this->data[$this->alias]['is_approved'])) {
					return false;
				}
			}
		}
	}


/**
 * 最近のコメント取得
 *
 * @param   integer $content_id
 * @param   integer $visible_item
 * @param   array   $conditions
 * @return  Model BlogComments
 * @since   v 3.0.0.0
 */
	public function recentComments($content_id, $visible_item, $conditions = array()) {
		$params = array(
			'fields' => array('BlogComment.*', 'BlogPost.post_date', 'BlogPost.title', 'BlogPost.permalink'),
			'conditions' => array_merge($conditions, array('BlogComment.comment_type' => BLOG_TRACKBACK_TYPE_COMMENT)),
			'limit' => intval($visible_item),
			'page' => 1,
			'joins' => array(
				array(
					"type" => "INNER",
					"table" => "blog_posts",
					"alias" => "BlogPost",
					"conditions" => "`BlogComment`.`blog_post_id`=`BlogPost`.`id`"
				),
				array(
					"type" => "INNER",
					"table" => "contents",
					"alias" => "Content",
					"conditions" => "`BlogComment`.`content_id`=`Content`.`id`"
				),
				// TODO:以下のJOIN必要ないかも。fieldsで取得しているわけではないため。
				array(
					"type" => "LEFT",
					"table" => "page_user_links",
					"alias" => "PageUserLink",
					"conditions" => "`BlogPost`.`created_user_id`=`PageUserLink`.`user_id`".
						" AND `Content`.`room_id`=`PageUserLink`.`room_id`"
				),
				array(
					"type" => "LEFT",
					"table" => "authorities",
					"alias" => "ContentAuthority",
					"conditions" => "`ContentAuthority`.`id`=`PageUserLink`.`authority_id`"
				)
			),
			'order' => array('BlogComment.created' => 'DESC')
		);
		return $this->find('all', $params);
	}

/**
 * コメント投稿時初期値
 *
 * @param   integer $contentId
 * @param   integer $blogPostId
 * @return  Model BlogComment
 * @since   v 3.0.0.0
 */
	public function findDefault($contentId, $blogPostId) {
		$ret = array(
			'BlogComment' => array(
				'id' => 0,
				'content_id' => $contentId,
				'blog_post_id' => $blogPostId,
				'parent_id' => null,
				'title' => '',
				'comment' => '',
				'author' => '',
				'author_email' => '',
				'author_url' => '',
				'author_ip' => '',
				'is_approved' => NC_APPROVED_FLAG_ON,
				'comment_type' => BLOG_TRACKBACK_TYPE_COMMENT,
			),
		);
		return $ret;
	}

/**
 * バリデート前処理
 *  ブログの設定に応じてauthorとauthor_emailを必須に変更
 *
 * @param   array $options
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function beforeValidate($options = array()) {
		if(isset($this->data['BlogComment']['content_id']) && !Configure::read(NC_SYSTEM_KEY.'.isLogin')){
			$Blog = ClassRegistry::init('Blog.Blog');
			$blog = $Blog->findByContentId($this->data['BlogComment']['content_id']);

			// 投稿者名とEmailアドレスを必須に変更
			if($blog['Blog']['comment_required_name']){
				$this->validate['author']['notEmpty'] = array(
					'rule' => array('notEmpty'),
					'message' => __('Please input %s.', __('Name')),
					'required' => true,
				);
				$this->validate['author_email']['notEmpty'] = array(
					'rule' => array('notEmpty'),
					'message' => __('Please input %s.', __('E-mail')),
					'required' => true,
				);
				$this->validate['author_email']['email'] = array(
					'rule' => array('email'),
					'message' => __('The input must be a %s.', __('E-mail')),
					'required' => true,
				);
			}
		}
		if($this->data['BlogComment']['comment_type'] == BLOG_TRACKBACK_TYPE_TRACKBACK) {
			// トラックバックなのでコメントの必須条件をはずす
			unset($this->validate['comment']['notEmpty']);
		}
		return parent::beforeValidate($options);
	}

/**
 * コメント一覧表示のparams取得
 *
 * @param   integer $blogPostId
 * @param   integer $userId
 * @param   integer $hierarchy
 * @param   array   $savedComment 個別に取得する必要があるコメントIDのArray
 * @return  array
 * @since   v 3.0.0.0
 */
	public function getConditions($blogPostId, $userId, $hierarchy, $savedComment) {
		if($hierarchy >= NC_AUTH_MIN_CHIEF) {
			return array(
				'BlogComment.blog_post_id' => $blogPostId,
				'BlogComment.comment_type' => BLOG_TRACKBACK_TYPE_COMMENT,
			);
		}

		return array(
			'BlogComment.blog_post_id' => $blogPostId,
			'BlogComment.comment_type' => BLOG_TRACKBACK_TYPE_COMMENT,
			'OR' => array(
				'BlogComment.is_approved' => NC_APPROVED_FLAG_ON,
				'BlogComment.created_user_id' => $userId,
				'BlogComment.id' => $savedComment,
			)
		);
	}

/**
 * トラックバック一覧表示のparams取得
 *
 * @param   integer $blogPostId
 * @param   integer $hierarchy
 * @return  array
 * @since   v 3.0.0.0
 */
	public function getTrackbackConditions($blogPostId, $hierarchy) {
		if($hierarchy >= NC_AUTH_MIN_CHIEF) {
			return array(
				'BlogComment.blog_post_id' => $blogPostId,
				'BlogComment.comment_type' => BLOG_TRACKBACK_TYPE_TRACKBACK,
			);
		}

		return array(
			'BlogComment.blog_post_id' => $blogPostId,
			'BlogComment.comment_type' => BLOG_TRACKBACK_TYPE_TRACKBACK,
			'BlogComment.is_approved' => NC_APPROVED_FLAG_ON
		);
	}

/**
 * ブログへのコメントをTree構造のArrayで取得
 *
 * @param   array $rootComments BlogCommentモデルのArray
 * @param   integer $userId
 * @param   integer $hierarchy
 * @param   array   $savedComment 個別に取得する必要があるコメントのID
 * @return  array $commentTree 引数のコメントをrootにもったTree構造のArray
 * @since   v 3.0.0.0
 */
	public function findCommentTree($rootComments, $userId, $hierarchy, $savedComment) {
		$commentTree = array();

		// ルートに紐づくコメントの取得
		if(!empty($rootComments)) {
			$conditions = $this->getConditions($rootComments[0]['BlogComment']['blog_post_id'], $userId, $hierarchy, $savedComment);

			$treeConditons = array(
				'BlogComment.lft >=' => $rootComments[0]['BlogComment']['lft'] < $rootComments[count($rootComments) - 1]['BlogComment']['lft'] ? $rootComments[0]['BlogComment']['lft'] : $rootComments[count($rootComments) - 1]['BlogComment']['lft'],
				'BlogComment.rght <=' => $rootComments[0]['BlogComment']['rght'] > $rootComments[count($rootComments) - 1]['BlogComment']['rght'] ? $rootComments[0]['BlogComment']['rght'] : $rootComments[count($rootComments) - 1]['BlogComment']['rght'],
			);
			$param['conditions'] = array_merge($treeConditons, $conditions);
			$commentTree =  $this->find('threaded', $param);
		}
		return $commentTree;
	}

/**
 * コメント一覧のPaginateの追加conditions取得
 *
 * @param   integer $blogPostId
 * @param   integer $userId
 * @param   integer $hierarchy
 * @param   array   $savedComment 個別に取得する必要があるコメントのID
 * @return  array
 * @since   v 3.0.0.0
 */
	public function getPaginateConditions($blogPostId, $userId, $hierarchy, $savedComment) {
		$conditions = $this->getConditions($blogPostId, $userId, $hierarchy, $savedComment);

		$rootConditions = array(
			'BlogComment.parent_id' => null
		);

		return array_merge($rootConditions, $conditions);
	}

/**
 * PaginatorComponentのpaginateより呼び出される
 * paginateの中で発行されるレコード数をカウントするSQLが不要な場合は発行しない
 *
 * @param   array   $conditions
 * @param   integer $recursive
 * @param   array   $extra
 * @return  integer
 * @since   v 3.0.0.0
 */
	public function paginateCount($conditions, $recursive, $extra) {
		if(isset($extra['recordCount'])) {
			return $extra['recordCount'];
		}
		return $this->find('count', array('conditions' => $conditions));
	}

}