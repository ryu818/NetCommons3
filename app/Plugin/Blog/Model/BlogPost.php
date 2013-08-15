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
	public $actsAs = array('TimeZone', 'Validation', 'Common', 'Auth');

	public $belongsTo = array(
		'Revision'      => array(
			'foreignKey'    => '',
			'type' => 'LEFT',
			'fields' => array('id', 'group_id', 'content',
				'revision_name', 'is_approved_pointer', 'created', 'created_user_id', 'created_user_name'),
			'conditions' => array(
				'BlogPost.revision_group_id = Revision.group_id',
				'Revision.pointer' => _ON,
				'Revision.revision_name !=' => 'auto-draft',
				'Revision.is_approved_pointer' => _ON
			),
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
				'inList' => array(
					'rule' => array('inList', array(
						NC_STATUS_PUBLISH,
						NC_STATUS_TEMPORARY,
						NC_STATUS_TEMPORARY_BEFORE_RELEASED,
					), false),
					'allowEmpty' => true,
					'message' => __('It contains an invalid string.')
				)
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
			'to_ping' => array(
				'trackbackUrl' => array(
					'rule' => array('trackbackUrl'),
					'allowEmpty' => true,
					'message' => __('The input must be a %s.', __('URL'))
				),
			),
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
			$permalink = $bufPermalink = preg_replace(NC_PERMALINK_PROHIBITION, NC_PERMALINK_PROHIBITION_REPLACE, $this->data[$this->alias]['permalink']);
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
			$this->data[$this->alias]['post_date'] = $this->dateUtc($this->data[$this->alias]['post_date']);
		}
		if (!empty($this->data[$this->alias]['pre_change_date']) ) {
			$this->data[$this->alias]['pre_change_date'] = $this->dateUtc($this->data[$this->alias]['pre_change_date']);
		}
		return true;
	}

/**
 * beforeDelete
 * @param   void
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function beforeDelete($cascade = true) {
		App::uses('Archive', 'Model');
		$Archive = new Archive();
		// アーカイブ削除
		if(!$Archive->deleteParent($this->alias, $this->id)) {
			return false;
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
				'status' => NC_STATUS_TEMPORARY_BEFORE_RELEASED,
				'is_approved' => _ON,
				'pre_change_flag' => _OFF,
				'pre_change_date' => null,
				'post_password' => '',
				'to_ping' => '',
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

				'PageAuthority.hierarchy '.$separator => $hierarchy,
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
				'MONTH( BlogPost.post_date ) AS month'
			),
			'conditions' => $conditions,
			'order' => array('BlogPost.post_date' => 'DESC'),
			'limit' => intval($visible_item),
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
		$prevConditions[]['OR'] = array(
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
		$nextConditions[]['OR'] = array(
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

/**
 * ブログポストへのコメント数のインクリメント、デクリメント （承認済みコメント数を含む）
 *
 * @param   string  $mode 'add'新規コメント追加時 、'edit'編集時、'reply'コメント返信時、'approve'承認時、'delete'削除時
 * @param   integer $blogPostId コメントのカウント数を調整する対象のブログポスト
 * @param   integer $isApprovedComment ブログコメントが承認されているか否か
 * @param   integer $blogCommentApprovedFlag ブログへのコメント投稿が承認制か否か
 * @param   integer $hierarchy
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function adjustCommentCount($mode, $blogPostId, $isApprovedComment, $blogCommentApprovedFlag = null, $hierarchy = null) {
		// TODO:権限見直し後見直し
		if($mode == 'add' || $mode == 'reply') {
			if(!$this->incrementSeq($blogPostId, 'comment_count')) {
				return false;
			}
			if($blogCommentApprovedFlag == _OFF || $hierarchy >= NC_AUTH_MIN_CHIEF) {
				if(!$this->incrementSeq($blogPostId, 'approved_comment_count')) {
					return false;
				}
			}
		} elseif($mode == 'edit') {
			if($isApprovedComment && $blogCommentApprovedFlag == _ON && $hierarchy < NC_AUTH_MIN_CHIEF) {
				if(!$this->decrementSeq($blogPostId, 'approved_comment_count')) {
					return false;
				}
			}
		} elseif($mode == 'delete') {
			if(!$this->decrementSeq($blogPostId, 'comment_count')) {
				return false;
			}
			if($isApprovedComment) {
				if(!$this->decrementSeq($blogPostId, 'approved_comment_count')) {
					return false;
				}
			}
		} elseif($mode == 'approve') {
			if(!$this->incrementSeq($blogPostId, 'approved_comment_count')) {
				return false;
			}
		}
		return true;
	}

/**
 * ブログポストへのトラックバック数のインクリメント、デクリメント （承認済みコメント数を含む）
 *
 * @param   string  $mode 'add'新規トラックバック受信時 、'approve'承認時、'delete'削除時
 * @param   integer $blogPostId カウント数を調整する対象のブログポスト
 * @param   integer $isApprovedTrackback トラックバックが承認されているか否か
 * @param   integer $blogTrackbackApprovedFlag ブログへのトラックバックが承認制か否か
 * @return  boolean
 * @since   v 3.0.0.0$blogCommentApprovedFlag
 */
	public function adjustTrackbackCount($mode, $blogPostId, $isApprovedTrackback, $blogTrackbackApprovedFlag = null) {
		if($mode == 'add') {
			if(!$this->incrementSeq($blogPostId, 'trackback_count')) {
				return false;
			}
			if($blogTrackbackApprovedFlag == _OFF) {
				if(!$this->incrementSeq($blogPostId, 'approved_trackback_count')) {
					return false;
				}
			}
		} elseif($mode == 'delete') {
			if(!$this->decrementSeq($blogPostId, 'trackback_count')) {
				return false;
			}
			if($isApprovedTrackback) {
				if(!$this->decrementSeq($blogPostId, 'approved_trackback_count')) {
					return false;
				}
			}
		} elseif($mode == 'approve') {
			if(!$this->incrementSeq($blogPostId, 'approved_trackback_count')) {
				return false;
			}
		}
		return true;
	}

/**
 * トラックバック送信
 *
 * @param Model    $blogPost トラックバック送信元となる記事
 * @param string    $fromUrl トラックバック送信元となる記事詳細のurl
 * @param boolean $isEdit 記事の編集から呼び出す場合
 * @return  array ($rtn, $message)  $rtn:失敗時はfalse
 * @since   v 3.0.0.0
 */
	public function sendTrackback($blogPost, $fromUrl, $isEdit = false) {
		App::uses('HttpSocket', 'Network/Http');
		$httpSocket = new HttpSocket();

		$rtn = true;
		$message = '';

		if(empty($blogPost['BlogPost']['to_ping'])) {
			return array($rtn, $message);
		}
		$toPings = $saveTopings = explode(' ', $blogPost['BlogPost']['to_ping']);
		if(empty($blogPost['BlogPost']['pinged'])) {
			$pingeds = $this->find('first', array('fields' => array('BlogPost.pinged'), 'conditions' => array('BlogPost.id' => $blogPost['BlogPost']['id']), 'recursive' => -1));
			$pingeds = empty($pingeds['BlogPost']['pinged']) ? array() :  explode(' ', $pingeds['BlogPost']['pinged']);
		} else {
			$pingeds = explode(' ', $blogPost['BlogPost']['pinged']);
		}

		foreach ($toPings as $key => $toPing) {
			if(in_array($toPing, $pingeds)) {
				unset($saveTopings[$key]);
				continue;
			}

			// URLの存在チェック
			$header = get_headers($toPing);
			if(empty($header[0]) || !preg_match('/^https?\/.*\s+(200|302)\s/i', $header[0])) {
				$rtn = false;
				$message = __d('blog', 'Could not connect to the destination TrackBack.');
				break;
			}

			App::uses('TextHelper', 'View/Helper');
			$text = new TextHelper(new View());
			$excerpt = strip_tags($blogPost['Revision']['content']);
			$excerpt = $text->truncate(	$excerpt, BLOG_TRACKBACK_EXCERPT_MAX_LENGTH);

			$response = $httpSocket->post(
				$toPing,
				array(
					'title' => $blogPost['BlogPost']['title'],
					'excerpt' => $excerpt,
					'url' => $fromUrl,
					'blog_name' => $this->_replaceBlogNameTags($blogPost)),
		 		array('header' => array('Accept-Language' => Configure::read(NC_CONFIG_KEY.'.'.'language'))
			));
			$result = preg_match('/<error>(\d+)<\/error>/', $response->body(), $matches);
			if($result == 0) {
				$rtn = false;
				$message = __d('blog', 'There was an incorrect response.');
				break;
			}
			$errNum = $matches[1];
			if ($errNum != 0) {
				$rtn = false;
				$result = preg_match('/<message>(.+?)<\/message>/ms', $response, $matches);
				if ($result == 1) {
					$message = $matches[1];
				} else {
					$message = __d('blog', 'Failed to send trackbacks.');
				}
				break;
			}
			unset($saveTopings[$key]);
			array_push($pingeds, $toPing);
		}
		$id = !empty($this->id) ? $this->id : $blogPost['BlogPost']['id'];
		$this->_savePing($id, $saveTopings, $pingeds);
		return array($rtn, $message);
	}

/**
 * トラックバック送信先、送信済みURLの保存
 *
 * @param integer $postId 更新対象の記事
 * @param array $toPing トラックバック未送信URL
 * @param array $pinged トラックバック送信済みURL
 * @return boolean
 * @since   v 3.0.0.0
 */
	protected function _savePing($postId, $toPing, $pinged) {
		$toPing = empty($toPing) ? '' : implode(' ', $toPing);
		$pinged = empty($pinged) ? '' :  implode(' ', $pinged);

		$blogPost = array('BlogPost' => array('id' => $postId, 'to_ping' => $toPing, 'pinged' => $pinged));
		$fieldList = array('to_ping', 'pinged');

		if(!$this->save($blogPost, false, $fieldList)) {
			return false;
		}
		return true;
	}

/**
 * トラックバック送信先URLのバリデーション
 *
 * @param string $check トラックバック送信先URL（複数の場合は半角スペースで区切ったもの）
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function trackbackUrl($check) {
		if(!is_string($check['to_ping'])) {
			return false;
		}
		$tbUrls = explode(' ', trim($check['to_ping']));
		foreach ($tbUrls as $tbUrl) {
			if(!Validation::url($tbUrl) || !preg_match('/^(?:http)/iu', $tbUrl)) {
				return false;
			}
		}
		return true;
	}

/**
 * トラックバックの内容のうちブログ名の変換文字列をReplace
 *
 * @param Model $blogPost
 * @return  string $blogName
 * @since   v 3.0.0.0
 */
	protected function _replaceBlogNameTags($blogPost) {
		App::uses('Blog', 'Blog.Model');
		$this->Blog = new Blog();

		$params = array(
			'conditions' => array('Blog.content_id' => $blogPost['BlogPost']['content_id']),
			'fields' => '*',
			'order' => null,
			'recursive' => -1,
			'joins' => array(
				array('type' => 'INNER',
					'table' => 'contents',
					'alias' => 'Content',
					'conditions' => array('Blog.content_id = Content.id')
				),
			),
		);
		$blog = $this->Blog->find('first', $params);
		$blogName =  $blog['Blog']['transmit_blog_name'];

		$assignedTags = array(
			'{X-CONTENT_NAME}' => $blog['Content']['title'],
			'{X-USER}' => $blog['Blog']['created_user_name'],
			'{X-SITE_NAME}' => Configure::read(NC_CONFIG_KEY.'.'.'sitename'));

		foreach($assignedTags as $key => $tags) {
			$blogName = str_replace($key, $tags, $blogName);
		}

		return $blogName;

	}

/**
 * BlogPostとBlogをJoinするパラメタを取得
 * Trackback受信時にBlogPostとBlogが同時に必要な場合に利用
 *
 * @param  integer $blogPostId
 * @return  array
 * @since   v 3.0.0.0
 */
	public function getTrackbackParams($contentId, $blogPostId) {
		return  array(
			'conditions' => array('BlogPost.id' => $blogPostId, 'Blog.content_id' => $contentId),
			'fields' => '*',
			'recursive' => -1,
			'joins' => array(
				array('type' => 'INNER',
					'table' => 'blogs',
					'alias' => 'Blog',
					'conditions' => array('BlogPost.content_id = Blog.content_id')
				),
			),
		);
	}

}