<?php
/**
 * BlogBlockモデル
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Model
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class BlogStyle extends AppModel
{
	public $name = 'BlogStyle';

	public $order = array("BlogStyle.col_num" => "ASC", "BlogStyle.row_num" => "ASC");

/**
 * 各ウジェットのoptionsのValidate
 * @var   array
 * @since   v 3.0.0.0
 */
	public $validate_options = array();
/**
 * メインのValidate
 * @var   array
 * @since   v 3.0.0.0
 */
	public $validate_main = array();

	public $initInsert = false;

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
		 * options値
		 */
		$this->validate_options[BLOG_WIDGET_TYPE_RECENT_POSTS] = array(
			// display_type // ドロップダウン表示|リスト表示 TODO:未作成
			'display_post_date' => array(	// 投稿日を表示するかいなか
				'boolean'  => array(
					'rule' => array('boolean'),
					'required' => true,
					'message' => __('The input must be a boolean.')
				)
			),
		);
		$this->validate_options[BLOG_WIDGET_TYPE_MAIN] = array(
			'visible_item_comments' => array(	// コメント表示件数
				'numeric' => array(
					'rule' => array('numeric'),
					'required' => true,
					'message' => __('The input must be a number.')
				),
				'minLength'  => array(
					'rule' => array('minLength', 1),
					'message' => __('It contains an invalid string.')
				),
			),
			'position_comments' => array(	// コメント表示位置
				'inList' => array(
					'rule' => array('inList', array(
						BLOG_POSITION_COMMENTS_LAST,
						BLOG_POSITION_COMMENTS_FIRST,
					)),
					'allowEmpty' => false,
					'message' => __('It contains an invalid string.')
				)
			),
			'order_comments' => array(	// コメント表示順序
				'inList' => array(
					'rule' => array('inList', array(
						BLOG_ORDER_COMMENTS_NEWEST,
						BLOG_ORDER_COMMENTS_OLDEST,
					)),
					'allowEmpty' => false,
					'message' => __('It contains an invalid string.')
				)
			),
			'threaded_comments' => array(	// コメントをスレッド (入れ子) 形式にする。
				'boolean'  => array(
					'rule' => array('boolean'),
					'required' => true,
					'message' => __('The input must be a boolean.')
				)
			),
		);
		$this->validate_options[BLOG_WIDGET_TYPE_ARCHIVES] = array(
			'display_type' => array(	// ドロップダウン表示|リスト表示
				'inList' => array(
					'rule' => array('inList', array(
						BLOG_DISPLAY_TYPE_LIST,
						BLOG_DISPLAY_TYPE_SELECTBOX,
					)),
					'allowEmpty' => false,
					'message' => __('It contains an invalid string.')
				)
			),
			'show_post_count' => array(	// 投稿数を表示
				'boolean'  => array(
					'rule' => array('boolean'),
					'required' => true,
					'message' => __('The input must be a boolean.')
				)
			),
		);
		$this->validate_options[BLOG_WIDGET_TYPE_CATEGORIES] = array(
			'display_type' => array(	// ドロップダウン表示|リスト表示
				'inList' => array(
					'rule' => array('inList', array(
						BLOG_DISPLAY_TYPE_LIST,
						BLOG_DISPLAY_TYPE_SELECTBOX,
					)),
					'allowEmpty' => false,
					'message' => __('It contains an invalid string.')
				)
			),
			'show_post_count' => array(	// 投稿数を表示
				'boolean'  => array(
					'rule' => array('boolean'),
					'required' => true,
					'message' => __('The input must be a boolean.')
				)
			),
			'show_hierarchy' => array(	// 階層を表示
				'boolean'  => array(
					'rule' => array('boolean'),
					'required' => true,
					'message' => __('The input must be a boolean.')
				)
			),
		);
		$this->validate_options[BLOG_WIDGET_TYPE_NUMBER_POSTS] = array(
			'display_type' => array(	// ドロップダウン表示|リスト表示
				'inList' => array(
					'rule' => array('inList', array(
						BLOG_DISPLAY_TYPE_LIST,
						BLOG_DISPLAY_TYPE_SELECTBOX,
					)),
					'allowEmpty' => false,
					'message' => __('It contains an invalid string.')
				)
			),
		);
		$this->validate_options[BLOG_WIDGET_TYPE_TAGS] = array(
			'display_type' => array(	// ドロップダウン表示|リスト表示
				'inList' => array(
					'rule' => array('inList', array(
						BLOG_DISPLAY_TYPE_LIST,
						BLOG_DISPLAY_TYPE_SELECTBOX,
					)),
					'allowEmpty' => false,
					'message' => __('It contains an invalid string.')
				)
			),
			'taxonomy' => array(	// タグ,カテゴリー
				'inList' => array(
					'rule' => array('inList', array(
						BLOG_DISPLAY_TYPE_TAGS,
						BLOG_DISPLAY_TYPE_CATEGORIES,
					)),
					'allowEmpty' => false,
					'message' => __('It contains an invalid string.')
				)
			),

		);

		$this->validate_options[BLOG_WIDGET_TYPE_RSS] = array(
			'display_type' => array(	// ドロップダウン表示|リスト表示
				'inList' => array(
					'rule' => array('inList', array(
						BLOG_DISPLAY_TYPE_POST_ONLY,
						BLOG_DISPLAY_TYPE_POST_AND_COMMENTS,
						BLOG_DISPLAY_TYPE_COMMENTS_ONLY,
					)),
					'allowEmpty' => false,
					'message' => __('It contains an invalid string.')
				)
			),
		);

		/*
		 * エラーメッセージ設定
		*/
		$this->validate_main = array(
			'block_id' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'required' => true,
					'allowEmpty' => false,
					'message' => __('The input must be a number.')
				)
			),
			'widget_type' => array(
				'inList' => array(
					'rule' => array('inList', array(
						BLOG_WIDGET_TYPE_MAIN,
						BLOG_WIDGET_TYPE_RECENT_POSTS,
						BLOG_WIDGET_TYPE_RECENT_COMMENTS,
						BLOG_WIDGET_TYPE_ARCHIVES,
						BLOG_WIDGET_TYPE_CATEGORIES,
						BLOG_WIDGET_TYPE_NUMBER_POSTS,
						BLOG_WIDGET_TYPE_TAGS,
						BLOG_WIDGET_TYPE_CALENDAR,
						BLOG_WIDGET_TYPE_RSS,
					), false),
					'required' => true,
					'allowEmpty' => false,
					'message' => __('It contains an invalid string.')
				)
			),
			'display_flag' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'last' => true,
					'required' => true,
					'message' => __('The input must be a boolean.')
				)
			),
			'col_num' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'required' => true,
					'allowEmpty' => false,
					'message' => __('The input must be a number.')
				),
				'inList' => array(
					'rule' => array('inList', array(
						1,
						2,
						3,
					), false),
					'allowEmpty' => false,
					'message' => __('It contains an invalid string.')
				),
				'_checkColNum'  => array(
					'rule' => array('_checkColNum'),
					'message' => __('It contains an invalid string.')
				)
			),
			'row_num' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'required' => true,
					'allowEmpty' => false,
					'message' => __('The input must be a number.')
				),
				'range'  => array(
					'rule' => array('range', 0, BLOG_WIDGET_TYPE_RSS + 1),
					'allowEmpty' => false,
					'message' => __('The input must be a number.')
				)
			),
			'visible_item' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'required' => true,
					'message' => __('The input must be a number.')
				),
				'minLength'  => array(
					'rule' => array('minLength', 0),						// 0ならばすべて表示
					'message' => __('It contains an invalid string.')
				),
			)
		);
	}

/**
 * beforeSave
 * @param   array  $options
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function beforeSave($options = array()) {
		if($this->initInsert) {
			return true;
		}
		if(isset($this->validate_options[$this->data['BlogStyle']['widget_type']])) {
			$this->validate = $this->validate_options[$this->data['BlogStyle']['widget_type']];
			if(!$this->validates()) {
				return false;
			}
			$serialize_options = array();
			foreach($this->validate as $key => $v) {
				if(isset($this->data['BlogStyle'][$key])) {
					$serialize_options[$key] = $this->data['BlogStyle'][$key];
					unset($this->data['BlogStyle'][$key]);
				}
			}
			$this->data['BlogStyle']['options'] = serialize($serialize_options);
			$this->validate = $this->validate_main;
		}
		return true;
	}

/**
 * ブログ(widget_type=BLOG_WIDGET_TYPE_MAIN)は、col_num = 2以外エラー
 * @param   array    $check
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function _checkColNum($check){
		$widget_type = intval($this->data['BlogStyle']['widget_type']);
		if($widget_type == BLOG_WIDGET_TYPE_MAIN && $check['col_num'] != 2) {
			return false;
		}
		return true;
	}

/**
 * ブロック追加時初期値
 * @param   integer $block_id
 * @param   boolean $insert_flag 初期値をInsertするかどうか default false
 * @return  Model BlogStyles
 * @since   v 3.0.0.0
 */
	public function findDefault($block_id = 0, $insert_flag = false) {
		$blog_styles =  array(
			array('BlogStyle' => array(
				'block_id' => $block_id,
				'widget_type' => BLOG_WIDGET_TYPE_NUMBER_POSTS,
				'display_flag' => _ON,
				'options' => 'a:1:{s:12:"display_type";s:1:"'.BLOG_DISPLAY_TYPE_SELECTBOX.'";}',
				'col_num' => 2,
				'row_num' => 1,
				'visible_item' => BLOG_DEFAULT_NUMBER_POSTS_VISIBLE_ITEM
			)),
			array('BlogStyle' => array(
				'block_id' => $block_id,
				'widget_type' => BLOG_WIDGET_TYPE_MAIN,
				'display_flag' => _ON,
				'options' => 'a:1:{s:21:"visible_item_comments";s:'.(strlen(BLOG_DEFAULT_VISIBLE_ITEM_COMMENTS)).':"'.BLOG_DEFAULT_VISIBLE_ITEM_COMMENTS.'";}',
				'col_num' => 2,
				'row_num' => 2,
				'visible_item' => BLOG_DEFAULT_VISIBLE_ITEM
			)),
			array('BlogStyle' => array(
				'block_id' => $block_id,
				'widget_type' => BLOG_WIDGET_TYPE_RECENT_POSTS,
				'display_flag' => _ON,
				'options' => '',
				'col_num' => 3,
				'row_num' => 1,
				'visible_item' => BLOG_DEFAULT_RECENT_POSTS_VISIBLE_ITEM
			)),
			array('BlogStyle' => array(
				'block_id' => $block_id,
				'widget_type' => BLOG_WIDGET_TYPE_RECENT_COMMENTS,
				'display_flag' => _ON,
				'options' => '',
				'col_num' => 3,
				'row_num' => 2,
				'visible_item' => BLOG_DEFAULT_RECENT_COMMENTS_VISIBLE_ITEM
			)),
			array('BlogStyle' => array(
				'block_id' => $block_id,
				'widget_type' => BLOG_WIDGET_TYPE_ARCHIVES,
				'display_flag' => _ON,
				'options' => '',
				'col_num' => 3,
				'row_num' => 3,
				'visible_item' => BLOG_DEFAULT_ARCHIVES_VISIBLE_ITEM
			)),
			array('BlogStyle' => array(
				'block_id' => $block_id,
				'widget_type' => BLOG_WIDGET_TYPE_CATEGORIES,
				'display_flag' => _ON,
				'options' => '',
				'col_num' => 3,
				'row_num' => 4,
				'visible_item' => BLOG_DEFAULT_CATEGORIES_VISIBLE_ITEM
			)),
			array('BlogStyle' => array(
				'block_id' => $block_id,
				'widget_type' => BLOG_WIDGET_TYPE_TAGS,
				'display_flag' => _OFF,
				'options' => '',
				'col_num' => 3,
				'row_num' => 5,
				'visible_item' => BLOG_DEFAULT_TAGS_VISIBLE_ITEM
			)),
			array('BlogStyle' => array(
				'block_id' => $block_id,
				'widget_type' => BLOG_WIDGET_TYPE_CALENDAR,
				'display_flag' => _OFF,
				'options' => '',
				'col_num' => 3,
				'row_num' => 6,
				'visible_item' => 0
			)),
			array('BlogStyle' => array(
				'block_id' => $block_id,
				'widget_type' => BLOG_WIDGET_TYPE_RSS,
				'display_flag' => _OFF,
				'options' => '',
				'col_num' => 3,
				'row_num' => 7,
				'visible_item' => 0
			)),
		);

		if($insert_flag) {
			// 初期データInsert
			// 表示方法変更画面　最初の表示でInsert
			$this->initInsert = true;
			foreach($blog_styles as $blog_style) {
				$this->create();
				if(!$this->save($blog_style)) {
					return false;
				}
			}
		}

		return $blog_styles;
	}

/**
 * afterFind
 *
 * @param  array $results
 * @param  boolean $is_show_main : メイン表示かいなか メイン表示の場合、メインエリア内でセレクトボックス表示のものをまとめて表示させるため
 * @return array $results
 * @since   v 3.0.0.0
 */
	public function afterFindColRow($results, $is_show_main = false) {
		$rets = array();

		if($is_show_main) {
			// ブログが表示されているかどうかチェック(display_flag=_ON)
			foreach ($results as $val) {
				if($val['BlogStyle']['widget_type'] == BLOG_WIDGET_TYPE_MAIN) {
					$is_show_main = ($val['BlogStyle']['display_flag']) ? true : false;
					$col_num = intval($val['BlogStyle']['col_num']);
					$row_num = intval($val['BlogStyle']['row_num']);
					break;
				}
			}
		}

		foreach ($results as $val) {
			$unserialize_options = (isset($val['BlogStyle']) && $val['BlogStyle']['options'] != '')? unserialize($val['BlogStyle']['options']) : null;
			if(is_array($unserialize_options)) {
				$val['BlogStyle'] = array_merge($val['BlogStyle'], $unserialize_options);
			}
			unset($val['BlogStyle']['options']);

			if($is_show_main && isset($col_num) && $val['BlogStyle']['col_num'] == 2) {
				if((isset($val['BlogStyle']['display_type']) &&
						$val['BlogStyle']['widget_type'] == BLOG_WIDGET_TYPE_CATEGORIES &&
						$val['BlogStyle']['display_type'] == BLOG_DISPLAY_TYPE_SELECTBOX) ||
						(isset($val['BlogStyle']['display_type']) &&
						$val['BlogStyle']['widget_type'] == BLOG_WIDGET_TYPE_NUMBER_POSTS &&
						$val['BlogStyle']['display_type'] == BLOG_DISPLAY_TYPE_SELECTBOX) ||
						(isset($val['BlogStyle']['display_type']) &&
						$val['BlogStyle']['widget_type'] == BLOG_WIDGET_TYPE_TAGS &&
						$val['BlogStyle']['display_type'] == BLOG_DISPLAY_TYPE_SELECTBOX) ||
						(isset($val['BlogStyle']['display_type']) &&
						$val['BlogStyle']['widget_type'] == BLOG_WIDGET_TYPE_ARCHIVES &&
						$val['BlogStyle']['display_type'] == BLOG_DISPLAY_TYPE_SELECTBOX) ||
						(isset($val['BlogStyle']['display_type']) &&
						$val['BlogStyle']['widget_type'] == BLOG_WIDGET_TYPE_RSS)
					) {
					// センターブログにまとめて表示

					$rets[$col_num][$row_num]['BlogWidget'][] = $val;
					continue;
				}
			}

			$rets[intval($val['BlogStyle']['col_num'])][intval($val['BlogStyle']['row_num'])]['BlogStyle'] = $val['BlogStyle'];
		}
		return $rets;
	}

/**
 * 表示順変更処理
 * @param  integer $block_id
 * @param  integer $widget_type
 * @param  integer $col_num
 * @param  integer $row_num
 * @return boolean true or false
 * @since   v 3.0.0.0
 */
	public function changeDisplay($block_id, $widget_type, $col_num, $row_num) {
		$params = array(
			'conditions' => array(
				'BlogStyle.block_id' => $block_id,
				'BlogStyle.widget_type' => intval($widget_type)
			)
		);

		$blog_style = $this->find('first', $params);
		if(!isset($blog_style['BlogStyle'])) {
			return false;
		}

		$new_blog_style['BlogStyle'] = array_merge($blog_style['BlogStyle'], array(
			'widget_type' => intval($widget_type),
			'col_num' => intval($col_num),
			'row_num' => intval($row_num)
		));
		$this->create();
		$this->set($new_blog_style);
		$fieldList = array(
			'col_num',
			'row_num',
		);
		if(!$this->save($new_blog_style, true, $fieldList) || !$this->decrementRowNum($blog_style) || !$this->incrementRowNum($new_blog_style)) {
			return false;
		}
		return true;
	}

/**
 * 行前詰め処理
 * @param  Model BlogStyle $blog_style
 * @param  integer $row_num
 * @return boolean true or false
 * @since   v 3.0.0.0
 */
	public function decrementRowNum($blog_style = null,$row_num = 1) {
		$row_num = -1*$row_num;
		return $this->_operationRowNum($blog_style, $row_num);
	}
	public function incrementRowNum($blog_style = null,$row_num = 1) {
		return $this->_operationRowNum($blog_style, $row_num);
	}
	protected function _operationRowNum($blog_style = null,$row_num = 1) {
		$fields = array(
			'BlogStyle.row_num'=>'BlogStyle.row_num+('.$row_num.')'
		);
		$conditions = array(
			"BlogStyle.id !=" => $blog_style['BlogStyle']['id'],
			"BlogStyle.block_id" => $blog_style['BlogStyle']['block_id'],
			"BlogStyle.col_num" => $blog_style['BlogStyle']['col_num'],
			"BlogStyle.row_num >=" => $blog_style['BlogStyle']['row_num']
		);
		$ret = $this->updateAll($fields, $conditions);
		return $ret;
	}
}