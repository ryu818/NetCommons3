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
		'Htmlarea'     => array('type' => 'INNER'),
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
			'htmlarea_id' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'required' => true,
					'allowEmpty' => false,
					'message' => __('The input must be a number.')
				),
			),
			//'vote',
			'status' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'last' => true,
					'required' => true,
					'message' => __('The input must be a boolean.')
				),
				/*'inList' => array(
					'rule' => array('inList', array(
						NC_STATUS_PUBLISH,
						NC_STATUS_TEMPORARY,
					), false),
					'allowEmpty' => false,
					'message' => __('It contains an invalid string.')
				)*/
			),
			// TODO:未使用の可能性あり
			'approved_flag' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'last' => true,
					'required' => true,
					'message' => __('The input must be a boolean.')
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
				'htmlarea_id' => 0,
				'vote' => null,
				'status' => NC_STATUS_PUBLISH,
				'approved_flag' => _OFF,
				'post_password' => '',
				'trackback_link' => '',
				'comment_count' => 0,
				'trackback_count' => 0,
				'vote_count' => 0,
			),
			'Htmlarea' => array(
				//'id' => 0,
				//'content_id' => $content_id,
				//'revision_parent' => 0,
				//'revision_name' => 'publish',
				'content' => '',
				//'non_approved_content' => '',
			)
		);

		return $ret;
	}

/**
 * 投稿一覧のconditions取得
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
					// TODO:approved_flag 未使用になるかも
				),

				'Authority.hierarchy '.$separator => $hierarchy,
				'BlogPost.created_user_id' => $user_id,
			)
		);
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
}