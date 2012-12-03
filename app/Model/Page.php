<?php
/**
 * Pageモデル
 *
 * <pre>
 *  ページ一覧
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Model
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */

class Page extends AppModel
{
	public $name = 'Page';
	public $actsAs = array('Page', 'TimeZone');	// , 'Validation'
	public $validate = array();

	public $autoConvert = true;

/**
 * バリデート処理
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function __construct() {
		parent::__construct();

		//エラーメッセージ取得
		$this->validate = array(
				'page_name' => array(
					'notEmpty'  => array(
						'rule' => array('notEmpty'),
						'last' => true,
						'required' => true,
						//'allowEmpty' => false,
						'message' => __('Please be sure to input.')
					),
					'maxlength'  => array(
						'rule' => array('maxLength', 30),
						'last' => false ,
						'message' => __('The input must be up to %s characters.', 30)
					),
					'duplicationPageName'  => array(
						'rule' => array('_duplicationPageName'),
						'message' => __('The same name is already in use.Please choose another one.')
					)
				),
				'permalink' => array(
					'notEmptyPermalink'  => array(
						'rule' => array('_notEmptyPermalink'),
						'last' => true,
						//'required' => true,
						//'allowEmpty' => false,
						'message' => __('Please be sure to input.')
					),
					'invalidPermalink'  => array(
						'rule' => array('_invalidPermalink'),
						'last' => true,
						'message' => __('It contains an invalid string.')
					),
					'maxlength'  => array(
						'rule' => array('maxLength', 255),
						'message' => __('The input must be up to %s characters.', 255)
					),
					'duplicationPermalink'  => array(
						'rule' => array('_duplicationPermalink'),
						'message' => __('The same name is already in use.Please choose another one.')
					)
				),
				'display_sequence' => array(
					'numeric' => array(
						'rule' => array('numeric'),
						'allowEmpty' => false,
						'message' => __('The input must be a number.')
					)
				),
				'position_flag' => array(
					'boolean'  => array(
						'rule' => array('boolean'),
						'allowEmpty' => false,
						'message' => __('The input must be a boolean.')
					)
				),
				'space_type' => array(
					'numeric' => array(
						'rule' => array('numeric'),
						'required' => true,
						'message' => __('The input must be a number.')
					),
					'range' => array(
						'rule' => array('range', NC_SPACE_TYPE_PUBLIC - 1, NC_SPACE_TYPE_GROUP + 1),
						'message' => __('The input must be a number bigger than %d and less than %d.', NC_SPACE_TYPE_PUBLIC, NC_SPACE_TYPE_GROUP)
					)
				),
				'display_flag' => array(
					'boolean'  => array(
						'rule' => array('boolean'),
						'last' => true,
						'required' => true,
						'allowEmpty' => false,
						'message' => __('Unauthorized pattern for %s.', __('Publishing setting'))
					)
				),

				'display_from_date' => array(
					'datetime'  => array(
						'rule' => array('datetime'),
						'last' => true,
						'allowEmpty' => true,
						'message' => __('Unauthorized pattern for %s.', __('Date-time'))
					),
					'pastDateTime'  => array(
							'rule' => array('pastDateTime'),
							'last' => true,
							'allowEmpty' => true,
							'message' => __('%s in the past can not be input.', __('Date-time'))
					),
					'invalidDisplayFromDate'  => array(
							'rule' => array('_invalidDisplayFromDate'),
							'last' => true,
							'allowEmpty' => true,
							'message' => __d('page', 'Because the parent page is closed, I cannot set a public date.')
					),
				),
				'display_to_date' => array(
					'datetime'  => array(
							'rule' => array('datetime'),
							'last' => true,
							'allowEmpty' => true,
							'message' => __('Unauthorized pattern for %s.', __('Date-time'))
					),
					'pastDateTime'  => array(
							'rule' => array('pastDateTime'),
							'last' => true,
							'allowEmpty' => true,
							'message' => __('%s in the past can not be input.', __('Date-time'))
					),
					'invalidDisplayToDate'  => array(
							'rule' => array('_invalidDisplayToDate'),
							'last' => true,
							'allowEmpty' => true,
							'message' => __d('page', 'Because the page is not published, I cannot set a closed date.')
					),
					'_invalidDisplayFromToDate'  => array(
							'rule' => array('_invalidDisplayFromToDate'),
							'last' => true,
							'allowEmpty' => true,
							'message' => __d('page', 'Please input in [publish date < closed date].')
					),
				),
				'display_apply_subpage' => array(
					'boolean'  => array(
						'rule' => array('boolean'),
						'last' => true,
						'allowEmpty' => true,
						'message' => __('It contains an invalid string.')
					)
				),
		);
	}

/**
 * ページ名称重複チェック
 * 		コミュニティ名称のみ
 * @param   array    $check
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function _duplicationPageName($check){
		$position_flag = intval($this->data['Page']['position_flag']);
		if($position_flag == _OFF) {
			return true;
		}
		$lang = Configure::read(NC_CONFIG_KEY.'.'.'language');
		$check['lang'] = array('', $lang);
		$check['position_flag'] = _ON;
		if(!isset($this->data['Page']['space_type']) || $this->data['Page']['space_type'] != NC_SPACE_TYPE_GROUP) {
			return true;
		}
		if(!isset($this->data['Page']['thread_num']) || $this->data['Page']['thread_num'] != 1) {
			return true;
		}

		$check['thread_num'] = $this->data['Page']['thread_num'];
		$check['space_type'] = $this->data['Page']['space_type'];

		if(!empty($this->data['Page']['id']))
			$check['id !='] = $this->data['Page']['id'];

		$count = $this->find( 'count', array('conditions' => $check, 'recursive' => -1) );
		if($count != 0)
			return false;
		return true;
	}

/**
 * 固定リンクが空かどうかのチェック
 * @param   array    $check
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function _notEmptyPermalink($check) {
		$permalink = trim($check['permalink'], '/');
		$space_type = intval($this->data['Page']['space_type']);
		$thread_num = intval($this->data['Page']['thread_num']);
		$position_flag = intval($this->data['Page']['position_flag']);
		$display_sequence = intval($this->data['Page']['display_sequence']);

		if($position_flag == _OFF || $thread_num == 0) {
			// TOPノード
			if($permalink != '') {
				return false;
			}
			return true;
		}
		$permalink_arr = explode('/', $permalink);
		$chk_thread_num = ($space_type == NC_SPACE_TYPE_PUBLIC || ($thread_num == 2 && $display_sequence == 1)) ? $thread_num - 1 : $thread_num;
		if(!isset($permalink_arr[$chk_thread_num - 1])) {
			/*if($thread_num == 0 || ($thread_num == 1 && $space_type == NC_SPACE_TYPE_PUBLIC)
				|| ($thread_num == 2 && $display_sequence == 1)) {
				// Topノード、パブリックTopノード、各ノードのTopページが存在する可能性あり
				return true;
			}*/
			return false;
		}
		$current_permalink = $permalink_arr[$chk_thread_num - 1];
		// notEmpty
		if (empty($current_permalink) && $current_permalink != '0') {
			return false;
		}
		return preg_match('/[^\s]+/m', $current_permalink);
	}

/**
 * 固定リンクに不正な文字列がないかのチェック
 *
 * @param  array     $check
 * @return boolean
 * @since   v 3.0.0.0
 */
	public function _invalidPermalink($check) {
		$permalink = trim($check['permalink'], '/');
		$space_type = intval($this->data['Page']['space_type']);
		$thread_num = intval($this->data['Page']['thread_num']);
		$display_sequence = intval($this->data['Page']['display_sequence']);
		if($permalink == '') {
			return true;
		}
		$space_type = intval($this->data['Page']['space_type']);
		$permalink_arr = explode('/', $permalink);
		$chk_thread_num = ($space_type == NC_SPACE_TYPE_PUBLIC || ($thread_num == 2 && $display_sequence == 1)) ? $thread_num - 1 : $thread_num;
		if(count($permalink_arr) != $chk_thread_num) {
			return false;
		}
		$permalink = $permalink_arr[$chk_thread_num - 1];

		if(preg_match(NC_PERMALINK_PROHIBITION, $permalink)) {
			return false;
		}
		$chk_permalink = $this->getPermalink($permalink, $space_type);
		if(preg_match(NC_PERMALINK_PROHIBITION_DIR_PATTERN, $chk_permalink)) {
			return false;
		}
		return true;
	}

/**
 * 固定リンクの重複チェック
 *
 * @param  array     $check
 * @return boolean
 * @since   v 3.0.0.0
 */
	public function _duplicationPermalink($check){
		if($check['permalink'] == '') {
			return true;
		}
		$lang = Configure::read(NC_CONFIG_KEY.'.'.'language');
		$check['lang'] = array('', $lang);
		$check['position_flag'] = _ON;
		$check['display_sequence !='] = 0;

		if(!empty($this->data['Page']['id']))
			$check['id !='] = $this->data['Page']['id'];

		if(!empty($this->data['Page']['space_type']))
			$check['space_type'] = $this->data['Page']['space_type'];
		$count = $this->find( 'count', array('conditions' => $check, 'recursive' => -1) );
		if(!empty($this->data['Page']['id']) && $this->data['Page']['thread_num'] == 1 && $count == 1) {
			// ノード
			$count = 0;
		}
		if($count != 0)
			return false;
		return true;
	}

/**
 * From公開日付チェック
 *
 * @param  array     $check
 * @return boolean
 * @since   v 3.0.0.0
 */
	public function _invalidDisplayFromDate($check){
		if(isset($this->data['Page']['display_flag']) && $this->data['Page']['display_flag'] == _ON) {
			// 既に公開中
			return false;
		}

		if(isset($this->data['parentPage']) && $this->data['parentPage']['display_flag'] == _OFF) {
			// 親が非公開ならば、公開日付を設定させない。
			return false;
		}
		return true;
	}

/**
 * To公開日付チェック
 *
 * @param  array     $check
 * @return boolean
 * @since   v 3.0.0.0
 */
	public function _invalidDisplayToDate($check){
		if(isset($this->data['Page']['display_flag']) && ($this->data['Page']['display_flag'] != _ON &&
				empty($this->data['Page']['display_from_date']))) {
			// 公開ではないか、公開日付が入力していない
			return false;
		}

		return true;
	}

/**
 * From-To公開日付チェック
 *
 * @param  array     $check
 * @return boolean
 * @since   v 3.0.0.0
 */
	public function _invalidDisplayFromToDate($check){
		if(!empty($this->data['Page']['display_from_date']) && !empty($this->data['Page']['display_to_date']) &&
				strtotime($this->data['Page']['display_from_date']) >= strtotime($this->data['Page']['display_to_date'])) {
			// "[公開日付 < 非公開日付]
			return false;
		}

		return true;
	}

/**
 * beforeSave
 * @param   array  $options
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function beforeSave($options = array()) {
		if(!$this->autoConvert) {
			return true;
		}
		if (!empty($this->data['Page']['display_from_date']) ) {
			$this->data['Page']['display_from_date'] = $this->date($this->data['Page']['display_from_date']);
		}
		if (!empty($this->data['Page']['display_to_date']) ) {
			$this->data['Page']['display_to_date'] = $this->date($this->data['Page']['display_to_date']);
		}
		return true;
	}

/**
 * ページリストからページ取得
 * @param   integer or array    $page_id_arr
 * @param   integer  $user_id
 * @return  array    $pages
 * @since   v 3.0.0.0
 */
	public function findAuthById($page_id_arr, $user_id) {
		$conditions = array('Page.id' => $page_id_arr);

		$params = array(
			'fields' => $this->_getFieldsArray(),
			'joins' => $this->_getJoinsArray($user_id),
			'conditions' => $conditions
		);

		if(is_array($page_id_arr)) {
			return $this->afterFindIds($this->find('all', $params));
		}
		$ret = $this->afterFindIds($this->find('first', $params));
		return $ret;
	}

/**
 * afterFind
 * @param   array   $results
 * @return  array   $pages
 * @since   v 3.0.0.0
 */
	public function afterFindIds($results) {
		$pages = array();
		$single_flag = false;
		if(isset($results['Page']['id'])) {
			$single_flag = true;
			$current_page_id = $results['Page']['id'];
			$results = array($results);
		}
		if(is_array($results)) {
			foreach ($results as $key => $val) {
				if(!isset($val['Authority']['hierarchy'])) {
					$val['Authority']['hierarchy'] = $this->getDefaultHierarchy($val['Page']);
				}
				$val['Page'] = $this->setPageName($val['Page']);
				$pages[$val['Page']['id']] = $val;
			}
		}
		if(count($pages) == 0)
			return false;

		if($single_flag) {
			return $pages[$current_page_id];
		}

		return $pages;
	}

/**
 * ページメニューのリストを取得
 *
 * @param string    $type all or count or list
 * @param integer   $login_user_id ログイン会員ID
 * @param integer   $space_type
 * @param array     $current_user
 * @param array     $params
 * @param function  $fetchcallback callback関数 default メニュー形式
 *                                     $pages[space_type][thread_num][parent_id][display_sequence] = Page
 * @param array     $fetch_params callback関数 parameter
 * @param integer   $admin_hierarchy
 * @return array
 * @since   v 3.0.0.0
 */
	public function findMenu($type, $login_user_id = null, $space_type = NC_SPACE_TYPE_PUBLIC, $current_user = null, $params = null, $fetchcallback = null, $fetch_params = null, $admin_hierarchy = null) {
		//$lang = Configure::read(NC_CONFIG_KEY.'.'.'language');
		$space_type_flag = true;
		if(is_array($space_type)) {
			foreach($space_type as $buf_space_type) {
				if($buf_space_type != NC_SPACE_TYPE_PUBLIC && $buf_space_type != NC_SPACE_TYPE_GROUP) {
					$space_type_flag = false;
					break;
				}
			}
		}
		if($space_type == NC_SPACE_TYPE_PUBLIC || $space_type == NC_SPACE_TYPE_GROUP || (is_array($space_type) && $space_type_flag)) {
			$conditions = array(
					'Page.space_type' => $space_type,
					'Page.position_flag' => _ON,
					'Page.thread_num !=' => 0
					//'Page.lang' => array('', $lang)
			);
		} else {
			$conditions = array(
					//'Page.space_type' => array(NC_SPACE_TYPE_MYPORTAL, NC_SPACE_TYPE_PRIVATE),
					'Page.position_flag' => _ON,
					'Page.display_flag !=' => NC_DISPLAY_FLAG_DISABLE,
					'Page.thread_num !=' => 0
					//'Page.lang' => array('', $lang)
			);
			if(isset($current_user)) {
				if($login_user_id != $current_user['User']['id'] || $space_type == NC_SPACE_TYPE_MYPORTAL) {
					// マイルームを表示しない。
					$conditions['Page.room_id'] = $current_user['User']['myportal_page_id'];
				} else if($space_type == NC_SPACE_TYPE_PRIVATE) {
					// マイルームのみ
					$conditions['Page.room_id'] = $current_user['User']['private_page_id'];
				} else {
					$conditions['Page.room_id'] = array($current_user['User']['myportal_page_id'], $current_user['User']['private_page_id']);
				}
			}
		}
		if(!isset($params['conditions'])) {
			$params['conditions'] = $conditions;
		} else {
			$params['conditions'] = array_merge($conditions, $params['conditions']);
		}

		if($type != 'count' && !isset($params['order'])) {
			$params['order'] = array(
					'Page.space_type' => "ASC",
					'Page.thread_num' => "ASC",
					'Page.display_sequence' => "ASC"
			);
		}

		if($type == 'count') {
			unset($params['fields']);
		} else if(empty($login_user_id)) {
			if(!isset($params['fields'])) {
				$params['fields'] = array('Page.*');
			}
		} else {
			if(!isset($params['fields'])) {
				$params['fields'] = $this->_getFieldsArray();
			}
			if(!isset($params['joins'])) {
				$join_type = ($admin_hierarchy < NC_AUTH_MIN_ADMIN && ($space_type == NC_SPACE_TYPE_PUBLIC || $space_type == NC_SPACE_TYPE_GROUP)) ? 'INNER' : 'LEFT';
				$params['joins'] = $this->_getJoinsArray($login_user_id, $join_type);
			}
		}

		/*if((isset($params['limit']) || isset($params['page'])) && $space_type == NC_SPACE_TYPE_GROUP &&
				!isset($params['conditions']['Page.display_sequence'])) {
			// ページメニュー：グループルーム編集モード
			$top_params = $params;
			$top_params['fields'] = array('Page.root_sequence');
			$top_params['conditions']['Page.display_sequence'] = 0;
			$top_results = $this->find('list', $top_params);
			if(count($top_results) == 0) {
				return $top_results;
			}
			$params['conditions']['Page.root_sequence'] = $top_results;
			//$params['conditions']['Page.room_id'] = $top_results;
			unset($params['limit']);
			unset($params['page']);
		}*/

		if($fetchcallback === "" || ($fetchcallback === null && $type == 'count')) {
			$results = $this->find($type, $params);
		} else if(!is_null($fetchcallback)) {
			$results = call_user_func_array($fetchcallback, array($this->find($type, $params), $fetch_params));
		} else {
			$results = $this->afterFindMenu($this->find($type, $params), $fetch_params);
		}
		return $results;
	}

/**
 * Current_pageの子供のページを取得
 *
 * @param string    $type
 * @param array     $current_user
 * @param integer   $login_user_id ログイン会員ID
 * @return  array   $fields
 * @since   v 3.0.0.0
 */
	public function findChilds($type, $current_page, $login_user_id = null) {
		$lang = Configure::read(NC_CONFIG_KEY.'.'.'language');
		$params = array('conditions' => array(
			'root_id' => $current_page['Page']['root_id'],
			'thread_num >' => $current_page['Page']['thread_num'],
			'lang' => array('', $lang)
		));
		return $this->findMenu($type, $login_user_id, $current_page['Page']['space_type'], null, $params, "");
	}

/**
 * Pageモデル共通Fields文
 * @param   void
 * @return  array   $fields
 * @since   v 3.0.0.0
 */
	protected function _getFieldsArray() {
		return array(
			'Page.*',
			'Authority.myportal_use_flag, Authority.private_use_flag, Authority.hierarchy'
		);
	}

/**
 * Pageモデル共通JOIN文
 * @param   integer $user_id
 * @return  array   $joins
 * @since   v 3.0.0.0
 */
	protected function _getJoinsArray($user_id) {
		$ret = array(
			array(
					"type" => "LEFT",
					"table" => "page_user_links",
					"alias" => "PageUserLink",
					"conditions" => "`Page`.`room_id`=`PageUserLink`.`room_id`".
						" AND `PageUserLink`.`user_id` =".intval($user_id)
			),
			array(
					"type" => "LEFT",
					"table" => "authorities",
					"alias" => "Authority",
					"conditions" => "`Authority`.id``=`PageUserLink`.`authority_id`"
			)
		);
		return $ret;
	}
}