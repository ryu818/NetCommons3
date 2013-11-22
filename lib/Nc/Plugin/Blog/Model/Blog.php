<?php
/**
 * Blogモデル
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Model
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class Blog extends AppModel
{

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
				)
			),
			'post_hierarchy' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'required' => true,
					'message' => __('The input must be a number.')
				),
				'inList' => array(
					'rule' => array('inList', array(
						NC_AUTH_MIN_GENERAL,
						NC_AUTH_MIN_MODERATE,
						NC_AUTH_MIN_CHIEF,
					), false),
					'allowEmpty' => false,
					'message' => __('It contains an invalid string.')
				)
			),
			'term_hierarchy' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'required' => true,
					'message' => __('The input must be a number.')
				),
				'inList' => array(
					'rule' => array('inList', array(
						NC_AUTH_MIN_GENERAL,
						NC_AUTH_MIN_MODERATE,
						NC_AUTH_MIN_CHIEF,
					), false),
					'allowEmpty' => false,
					'message' => __('It contains an invalid string.')
				)
			),
			'vote_flag' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'last' => true,
					'required' => true,
					'message' => __('The input must be a boolean.')
				)
			),
			'sns_flag' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'last' => true,
					'required' => true,
					'message' => __('The input must be a boolean.')
				)
			),
			'new_period' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'required' => true,
					'message' => __('The input must be a number.')
				),
				'minLength'  => array(
					'rule' => array('minLength', 0),						// 0ならばなし
					'message' => __('It contains an invalid string.')
				)
			),
			'mail_flag' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'last' => true,
					'required' => true,
					'message' => __('The input must be a boolean.')
				)
			),
			'mail_hierarchy' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'required' => true,
					'message' => __('The input must be a number.')
				),
				'inList' => array(
					'rule' => array('inList', array(
						NC_AUTH_GUEST,
						NC_AUTH_MIN_GENERAL,
						NC_AUTH_MIN_MODERATE,
						NC_AUTH_MIN_CHIEF,
					), false),
					'allowEmpty' => false,
					'message' => __('It contains an invalid string.')
				)
			),
			'mail_subject' => array(
				'maxLength'  => array(
					'rule' => array('maxLength', NC_VALIDATOR_TITLE_LEN),
					'message' => __('The input must be up to %s characters.', NC_VALIDATOR_TITLE_LEN)
				),
				'_notEmptyCondition'  => array(
					'rule' => array('_notEmptyCondition', array('mail_flag')),
					'required' => true,
					'message' => __('Please be sure to input.')
				),
			),
			'mail_body' => array(
				'_notEmptyCondition'  => array(
					'rule' => array('_notEmptyCondition', array('mail_flag')),
					'required' => true,
					'message' => __('Please be sure to input.')
				),
			),
			'comment_flag' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'last' => true,
					'required' => true,
					'message' => __('The input must be a boolean.')
				)
			),
			'comment_required_name' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'last' => true,
					'required' => true,
					'message' => __('The input must be a boolean.')
				)
			),
			'comment_image_auth' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'last' => true,
					'required' => true,
					'message' => __('The input must be a boolean.')
				)
			),
			'comment_hierarchy' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'required' => true,
					'message' => __('The input must be a number.')
				),
				'inList' => array(
					'rule' => array('inList', array(
						NC_AUTH_GUEST,
						NC_AUTH_MIN_GENERAL,
						NC_AUTH_MIN_MODERATE,
						NC_AUTH_MIN_CHIEF,
					), false),
					'allowEmpty' => false,
					'message' => __('It contains an invalid string.')
				)
			),
			'comment_mail_flag' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'last' => true,
					'required' => true,
					'message' => __('The input must be a boolean.')
				)
			),
			'comment_mail_hierarchy' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'required' => true,
					'message' => __('The input must be a number.')
				),
				'inList' => array(
					'rule' => array('inList', array(
						NC_AUTH_GUEST,
						NC_AUTH_MIN_GENERAL,
						NC_AUTH_MIN_MODERATE,
						NC_AUTH_MIN_CHIEF,
					), false),
					'allowEmpty' => false,
					'message' => __('It contains an invalid string.')
				)
			),
			'comment_mail_subject' => array(
				'maxLength'  => array(
					'rule' => array('maxLength', NC_VALIDATOR_TITLE_LEN),
					'message' => __('The input must be up to %s characters.', NC_VALIDATOR_TITLE_LEN)
				),
				'_notEmptyCondition'  => array(
					'rule' => array('_notEmptyCondition', array('comment_mail_flag')),
					'required' => true,
					'message' => __('Please be sure to input.')
				),
			),
			'comment_mail_body' => array(
					'_notEmptyCondition'  => array(
						'rule' => array('_notEmptyCondition', array('comment_mail_flag')),
						'required' => true,
						'message' => __('Please be sure to input.')
					),
			),
			'trackback_transmit_flag' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'last' => true,
					'required' => true,
					'message' => __('The input must be a boolean.')
				)
			),
			'trackback_receive_flag' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'last' => true,
					'required' => true,
					'message' => __('The input must be a boolean.')
				)
			),
			'transmit_blog_name' => array(
				'_notEmptyCondition'  => array(
					'rule' => array('_notEmptyCondition', array('trackback_transmit_flag')),
					'required' => true,
					'message' => __('Please be sure to input.')
				),
				'maxLength'  => array(
					'rule' => array('maxLength', NC_VALIDATOR_TITLE_LEN),
					'message' => __('The input must be up to %s characters.', NC_VALIDATOR_TITLE_LEN)
				)
			),
			'approved_flag' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'last' => true,
					'required' => true,
					'message' => __('The input must be a boolean.')
				)
			),
			'approved_pre_change_flag' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'last' => true,
					'required' => true,
					'message' => __('The input must be a boolean.')
				)
			),
			'approved_mail_flag' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'last' => true,
					'required' => true,
					'message' => __('The input must be a boolean.')
				)
			),
			'approved_mail_subject' => array(
				'maxLength'  => array(
					'rule' => array('maxLength', NC_VALIDATOR_TITLE_LEN),
					'message' => __('The input must be up to %s characters.', NC_VALIDATOR_TITLE_LEN)
				),
				'_notEmptyCondition'  => array(
					'rule' => array('_notEmptyCondition', array('approved_flag', 'approved_mail_flag')),
					'required' => true,
					'message' => __('Please be sure to input.')
				),
			),
			'approved_mail_body' => array(
				'_notEmptyCondition'  => array(
					'rule' => array('_notEmptyCondition', array('approved_flag', 'approved_mail_flag')),
					'required' => true,
					'message' => __('Please be sure to input.')
				),
			),
			'comment_approved_flag' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'last' => true,
					'required' => true,
					'message' => __('The input must be a boolean.')
				)
			),
			'trackback_approved_flag' => array(
					'boolean'  => array(
							'rule' => array('boolean'),
							'last' => true,
							'required' => true,
							'message' => __('The input must be a boolean.')
					)
			),
			'comment_approved_mail_flag' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'last' => true,
					'required' => true,
					'message' => __('The input must be a boolean.')
				)
			),
			'comment_approved_mail_subject' => array(
				'maxLength'  => array(
					'rule' => array('maxLength', NC_VALIDATOR_TITLE_LEN),
					'message' => __('The input must be up to %s characters.', NC_VALIDATOR_TITLE_LEN)
				),
				'_notEmptyCondition'  => array(
					'rule' => array('_notEmptyCondition', array('comment_approved_flag', 'trackback_approved_flag', 'comment_approved_mail_flag')),
					'required' => true,
					'message' => __('Please be sure to input.')
				),
			),
			'comment_approved_mail_body' => array(
				'_notEmptyCondition'  => array(
					'rule' => array('_notEmptyCondition', array('comment_approved_flag', 'trackback_approved_flag', 'comment_approved_mail_flag')),
					'required' => true,
					'message' => __('Please be sure to input.')
				),
			),
		);
	}

/**
 * メールSubject、メールBody等条件付き必須チェック
 * @param   array    $check
 * @param   array    $columns	$columnsのカラムの値がすべてOnならば必須
 * @return  boolean
 * @since   v 3.0.0.0
 * TODO:共通化するべき
 */
	public function _notEmptyCondition($check, $columns){
		$notEmpty = true;
		foreach($columns as $column) {
			$value = intval($this->data[$this->alias][$column]);
			if($value) {
				$notEmpty = true;
			} else {
				$notEmpty = false;
				break;
			}
		}

		if($notEmpty) {
			$check_arr = array_values($check);
			return Validation::notEmpty($check_arr[0]);
		}
		return true;
	}

/**
 * ブロック追加時初期値
 * @param   integer $content_id
 * @return  Model Blog
 * @since   v 3.0.0.0
 */
	public function findDefault($content_id) {
		$ret = array(
			'Blog' => array(
				'content_id' => $content_id,
				'post_hierarchy' => NC_AUTH_MIN_CHIEF,
				'term_hierarchy' => NC_AUTH_MIN_CHIEF,
				'vote_flag' => _ON,
				'sns_flag' => _ON,
				'new_period' => BLOG_DEFAULT_NEW_PERIOD,
				'mail_flag' => _OFF,
				'mail_hierarchy' => NC_AUTH_MIN_CHIEF,
				'mail_subject' => __d('blog', "[{X-SITE_NAME}]Blog({X-ROOM} {X-CONTENT_NAME} {X-SUBJECT})"),
				'mail_body' => __d('blog', "You are receiving this email because a message was posted to Blog.\nRoom's name:{X-ROOM}\nBlog title:{X-CONTENT_NAME}\ntitle:{X-SUBJECT}\nuser:{X-USER}\ndate:{X-TO_DATE}\n\n\n{X-BODY}\n\nClick on the link below to reply to this article.\n{X-URL}"),
				'comment_flag' => _ON,
				'comment_required_name' => _ON,
				'comment_image_auth' => _OFF,
				'comment_hierarchy' => NC_AUTH_MIN_GENERAL,
				'comment_mail_flag' => _OFF,
				'comment_mail_hierarchy' => NC_AUTH_MIN_CHIEF,
				'comment_mail_subject' => __d('blog', "[{X-SITE_NAME}]Blog Comment({X-ROOM} {X-CONTENT_NAME} {X-SUBJECT})"),
				'comment_mail_body' => __d('blog', "You are receiving this email because a message was posted to Blog of comment.\nRoom's name:{X-ROOM}\nBlog title:{X-CONTENT_NAME}\ntitle:{X-SUBJECT}\nuser:{X-USER}\ndate:{X-TO_DATE}\n\n\n{X-BODY}\n\nClick on the link below to reply to this article.\n{X-URL}"),
				'trackback_transmit_flag' => _OFF,
				'trackback_receive_flag' => _OFF,
				'transmit_blog_name' => __d('blog', "{X-CONTENT_NAME}-{X-SITE_NAME}"),
				'approved_flag' => _OFF,
				'approved_pre_change_flag' => _ON,
				'approved_mail_flag' => _OFF,
				'approved_mail_subject' => __d('blog', "[{X-SITE_NAME}] [{X-CONTENT_NAME} {X-SUBJECT}] Post Approval completion notice"),
				'approved_mail_body' => __d('blog', "Your article posted to [{X-SITE_NAME}] [{X-CONTENT_NAME} {X-SUBJECT}] was approved.\nRoom's name:{X-ROOM}\nBlog title:{X-CONTENT_NAME}\ntitle:{X-SUBJECT}\nuser:{X-USER}\ndate:{X-TO_DATE}\n\n\n{X-BODY}\n\nClick the link below to check the article.\n{X-URL}"),
				'comment_approved_flag' => _OFF,
				'trackback_approved_flag' => _OFF,
				'comment_approved_mail_flag' => _OFF,
				'comment_approved_mail_subject' => __d('blog', "[{X-SITE_NAME}] [{X-CONTENT_NAME} {X-SUBJECT}] Comment, trackback Approval completion notice"),
				'comment_approved_mail_body' => __d('blog', "Your comment and trackback posted to [{X-SITE_NAME}] [{X-CONTENT_NAME} {X-SUBJECT}] was approved.\nRoom's name:{X-ROOM}\nBlog title:{X-CONTENT_NAME}\ntitle:{X-SUBJECT}\nuser:{X-USER}\ndate:{X-TO_DATE}\n\n\n{X-BODY}\n\nClick the link below to check the article.\n{X-URL}"),
			),
		);

		return $ret;
	}
}