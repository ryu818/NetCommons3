<?php
/**
 * Announcementモデル
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Model
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class Announcement extends AppModel
{
	public $name = 'Announcement';

	public $actsAs = array('Common', 'TimeZone');

	public $belongsTo = array(
		'Revision'      => array(
			'foreignKey'    => '',
			'type' => 'LEFT',
			'fields' => array('Revision.id', 'Revision.group_id', 'Revision.content',
				'Revision.revision_name', 'Revision.is_approved_pointer', 'Revision.created', 'Revision.created_user_id', 'Revision.created_user_name'),
			'conditions' => array(
				'Announcement.revision_group_id = Revision.group_id',
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
				'Announcement.created_user_id = PageUserLink.user_id',
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

		/*
		 * エラーメッセージ設定
		 */
		$this->validate = array(
			'content_id' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'allowEmpty' => false,
					'message' => __('The input must be a number.')
				)
			),
			'revision_group_id' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'allowEmpty' => false,
					'message' => __('The input must be a number.')
				)
			),
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
	    );
	}

/**
 * beforeSave
 * @param   array  $options
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function beforeSave($options = array()) {
		if (!empty($this->data[$this->alias]['pre_change_date']) ) {
			$this->data[$this->alias]['pre_change_date'] = $this->dateUtc($this->data[$this->alias]['pre_change_date']);
		}
		return true;
	}

/**
 * 記事投稿時初期値
 * @param   integer $content_id
 * @return  Model Announcement
 * @since   v 3.0.0.0
 */
	public function findDefault($content_id) {
		$ret = array(
			'Announcement' => array(
				'id' => 0,
				'content_id' => $content_id,
				'revision_group_id' => 0,
				'status' => NC_STATUS_TEMPORARY_BEFORE_RELEASED,
				'is_approved' => _ON,
				'pre_change_flag' => _OFF,
				'pre_change_date' => null,
			),
			'Revision' => array(
				'content' => '',
				'revision_name' => 'publish',
			)
		);

		return $ret;
	}

}