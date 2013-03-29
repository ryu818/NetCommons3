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
	public $name = 'BlogComment';

	public $order = array("BlogComment.created" => "ASC");

	public $belongsTo = array(
		'BlogPost'      => array('type' => 'INNER'),
		'Content'      => array(
			'type' => 'INNER',
			'fields' => array()
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
			'root_id' => array(
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
					'required' => true,
					'message' => __('The input must be a number.')
				),
			),
			// TODO:トラックバックのタイトルがはいるのみ？バリデートが必要ならば追加する。
			// 'title',
			// 'comment',
			'author_email' => array(
				'email' => array(
					'rule' => array('email'),
					'message' => __('The input must be a number.')
				),
				'maxlength'  => array(
					'rule' => array('maxLength', NC_VALIDATOR_VARCHAR_LEN),
					'message' => __('The input must be up to %s characters.', NC_VALIDATOR_VARCHAR_LEN)
				),
			),
			'author_url' => array(
				'url' => array(
					'rule' => array('url'),
					'message' => __('The input must be a number.')
				),
				'maxlength'  => array(
					'rule' => array('maxLength', NC_VALIDATOR_VARCHAR_LEN),
					'message' => __('The input must be up to %s characters.', NC_VALIDATOR_VARCHAR_LEN)
				),
			),
			'author_ip' => array(
				'ip' => array(
					'rule' => array('ip'),
					'message' => __('The input must be a number.')
				),
				'maxlength'  => array(
					'rule' => array('maxLength', 100),
					'message' => __('The input must be up to %s characters.', 100)
				),
			),
			'status' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'required' => true,
					'allowEmpty' => false,
					'message' => __('The input must be a number.')
				),
				'inList' => array(
					'rule' => array('inList', array(
						NC_STATUS_PUBLISH,
						NC_STATUS_TEMPORARY,
					), false),
					'allowEmpty' => false,
					'message' => __('It contains an invalid string.')
				),
			),
			'approved_flag' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'last' => true,
					'required' => true,
					'message' => __('The input must be a boolean.')
				),
			),
			// TODO:2系のカラムをそのまま移動したが、使用するかどうかカラム名から考慮する必要あり。
			//'blog_name',
			//'direction_flag',
			//'tb_url',
			//'link',
		);
	}

/**
 * 最近のコメント取得
 * @param   integer $content_id
 * @param   integer $visible_item
 * @param   array   $conditions
 * @return  Model BlogComments
 * @since   v 3.0.0.0
 */
	public function recentComments($content_id, $visible_item, $conditions = array()) {
		$params = array(
			'fields' => array('BlogComment.*', 'BlogPost.post_date', 'BlogPost.title', 'BlogPost.permalink'),
			'conditions' => $conditions,	//array('BlogComment.content_id' => $content_id),
			'limit' => intval($visible_item),
			'page' => 1
		);
		return $this->find('all', $params);
	}
}