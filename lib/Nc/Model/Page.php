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
	public $actsAs = array('Page', 'TimeZone', 'Auth' => array('joins' => false, 'afterFind' => false));	// , 'Validation'
	public $validate = array();

// 公開日付をsaveする前に変換するかどうかのフラグ
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
					'maxLength'  => array(
						'rule' => array('maxLength', NC_VALIDATOR_PAGE_TITLE_LEN),
						'last' => false ,
						'message' => __('The input must be up to %s characters.', NC_VALIDATOR_PAGE_TITLE_LEN)
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
					'maxLength'  => array(
						'rule' => array('maxLength', NC_VALIDATOR_PERMALINK_LEN),
						'message' => __('The input must be up to %s characters.', NC_VALIDATOR_PERMALINK_LEN).__('(The total number of the top node)')
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
					'numeric' => array(
						'rule' => array('numeric'),
						'required' => true,
						'message' => __('The input must be a number.')
					),
					'inList' => array(
						'rule' => array('inList', array(
							NC_DISPLAY_FLAG_OFF,
							NC_DISPLAY_FLAG_ON,
							NC_DISPLAY_FLAG_DISABLE,
						), false),
						'allowEmpty' => false,
						'message' => __('It contains an invalid string.')
					)
				),

				'display_from_date' => array(
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
					'invalidDisplayFromDate'  => array(
						'rule' => array('invalidDisplayFromDate'),
						'last' => true,
						'allowEmpty' => true,
						'message' => __('Because the page is not a private, You can\'t set a publish date.')
					),
				),
				'display_to_date' => array(
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
					'invalidDisplayToDate'  => array(
						'rule' => array('invalidDisplayToDate'),
						'last' => true,
						'allowEmpty' => true,
						'message' => __('Because the page is not published, You can\'t set a closed date.')
					),
					'invalidDisplayFromToDate'  => array(
						'rule' => array('invalidDisplayFromToDate'),
						'last' => true,
						'allowEmpty' => true,
						'message' => __('Please input in [publish date < closed date].')
					),
				),
				'display_apply_subpage' => array(
					'boolean'  => array(
						'rule' => array('boolean'),
						'last' => true,
						'required' => true,
						'message' => __('It contains an invalid string.')
					)
				),
				'is_approved' => array(
					'boolean'  => array(
						'rule' => array('boolean'),
						'last' => true,
						'required' => true,
						'message' => __('It contains an invalid string.')
					)
				),
		);
	}

/**
 * ページ名称重複チェック
 * 		コミュニティー名称のみ
 * @param   array    $check
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function _duplicationPageName($check){
		$position_flag = intval($this->data['Page']['position_flag']);
		if($position_flag == _OFF) {
			return true;
		}
		if(!isset($this->data['Page']['lang'])) {
			$lang = Configure::read(NC_CONFIG_KEY.'.'.'language');
			$check['lang'] = array('', $lang);
		} else {
			$check['lang'] = $this->data['Page']['lang'];
		}
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
		if(!isset($permalink_arr[$chk_thread_num - 1]) || $permalink_arr[$chk_thread_num - 1] == '') {
			if($thread_num == 0 || ($thread_num == 1 && $space_type == NC_SPACE_TYPE_PUBLIC)
				|| ($thread_num == 2 && $display_sequence == 1 && $space_type == NC_SPACE_TYPE_PUBLIC)) {
				// Topノード、パブリックTopノード、各ノードのTopページが存在する可能性あり
				// パブリックでトップページのページ追加可能
				return true;
			}
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
		$current_permalink = $permalink_arr[$chk_thread_num - 1];

		if(preg_match(NC_PERMALINK_PROHIBITION, $current_permalink)) {
			return false;
		}
		$chk_permalink = $this->getPermalink($permalink, $space_type);
		if(preg_match(NC_PERMALINK_PROHIBITION_DIR_PATTERN, '/'.$chk_permalink)) {
			return __('Unavailable string is used by the system.');
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
		if(!isset($this->data['Page']['lang'])) {
			$lang = Configure::read(NC_CONFIG_KEY.'.'.'language');
			$check['lang'] = array('', $lang);
		} else {
			$check['lang'] = $this->data['Page']['lang'];
		}
		$check['position_flag'] = _ON;
		$check['display_sequence !='] = 0;

		if(!empty($this->data['Page']['id']))
			$check['id !='] = $this->data['Page']['id'];

		if(!empty($this->data['Page']['space_type']))
			$check['space_type'] = $this->data['Page']['space_type'];
		$count = $this->find( 'count', array('conditions' => $check, 'recursive' => -1) );
		if(($this->data['Page']['thread_num'] == 1 || $this->data['Page']['display_sequence'] == 1) && $count == 1) {
			// ノード Or Node Top Page
			$count = 0;
		}
		if($count != 0)
			return false;
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
			$this->data['Page']['display_from_date'] = $this->dateUtc($this->data['Page']['display_from_date']);
		}
		if (!empty($this->data['Page']['display_to_date']) ) {
			$this->data['Page']['display_to_date'] = $this->dateUtc($this->data['Page']['display_to_date']);
		}
		return true;
	}

/**
 * 初期値設定
 * @param   integer $spaceType
 * @return  Model Page
 * @since   v 3.0.0.0
 */
	public function findDefault($spaceType) {
		$lang = Configure::read(NC_CONFIG_KEY.'.'.'language');
		$data = array('Page');

		$data['Page']['root_id'] = 0;
		$data['Page']['parent_id'] = 0;
		$data['Page']['thread_num'] = 1;
		$data['Page']['display_sequence'] = 0;
		$data['Page']['position_flag'] = _ON;
		$data['Page']['lang'] = '';
		$data['Page']['is_page_meta_node'] = _OFF;
		$data['Page']['is_page_style_node'] = _OFF;
		$data['Page']['is_page_layout_node'] = _OFF;
		$data['Page']['is_page_theme_node'] = _OFF;
		$data['Page']['is_page_column_node'] = _OFF;
		$data['Page']['space_type'] = $spaceType;
		$data['Page']['show_count'] = 0;
		$data['Page']['display_flag'] = NC_DISPLAY_FLAG_ON;

		$data['Page']['display_apply_subpage'] = _ON;
		$data['Page']['is_approved'] = NC_APPROVED_FLAG_ON;
		$data['Page']['lock_authority_id'] = NC_AUTH_OTHER_ID;

		if($spaceType == NC_SPACE_TYPE_PUBLIC) {
			$data['Page']['parent_id'] = NC_TOP_PUBLIC_ID;
			$data['Page']['page_name'] = "Public room";
		} else if($spaceType == NC_SPACE_TYPE_MYPORTAL) {
			$data['Page']['parent_id'] = NC_TOP_MYPORTAL_ID;
			$data['Page']['page_name'] = "Myportal";
		} else if($spaceType == NC_SPACE_TYPE_PRIVATE) {
			$data['Page']['parent_id'] = NC_TOP_PRIVATE_ID;
			$data['Page']['page_name'] = "Private room";
		} else {
			$data['Page']['parent_id'] = NC_TOP_GROUP_ID;
			$data['Page']['page_name'] = "Community";
		}
		if($spaceType == NC_SPACE_TYPE_PRIVATE) {
			$ins_page['Page']['lang'] = '';
		} else {
			$ins_page['Page']['lang'] = $lang;
		}
		return $data;
	}

/**
 * ページリストからページ取得
 * @param   integer or array    $pageIdArr
 * @param   integer  $userId
 * @param   integer  $spaceType
 * @return  array    $pages
 * @since   v 3.0.0.0
 */
	public function findAuthById($pageIdArr, $userId = null, $spaceType = null) {
		if(empty($userId)) {
			$loginUser = Configure::read(NC_SYSTEM_KEY.'.user');
			$userId = isset($loginUser['id']) ? $loginUser['id'] : null;
		}
		$conditions = array('Page.id' => $pageIdArr);

		$params = array(
			'fields' => $this->getFieldsArray($userId, $spaceType),
			'joins' => $this->getJoinsArray($userId, 'LEFT', $spaceType),
			'conditions' => $conditions
		);

		if(is_array($pageIdArr)) {
			return $this->afterFindIds($this->find('all', $params), $userId);
		}
		$ret = $this->afterFindIds($this->find('first', $params), $userId);
		return $ret;
	}

/**
 * afterFind
 * @param   array   $results
 * @param   integer  $userId
 * @param   string   'all' or 'list' or 'menu'
 * @param   array   $fields $typeがlistの場合のみ使用
 * @param   integer $ativePageId アクティブなpage_id$type=menu時使用
 * @return  array   $pages
 * @since   v 3.0.0.0
 */
	public function afterFindIds($results, $userId, $type = "all", $fields = null, $ativePageId = null) {
		$listKeyAlias = $this->alias;
		$listValueAlias = $this->alias;
		$listGroupAlias = $this->alias;
		$listKey = 'id';
		$listValue = 'id';
		if($type == 'list' && isset($fields)) {
			if(isset($fields[0])) {
				$fields0Arr = explode('.', $fields[0]);
				if(count($fields0Arr) == 2) {
					$listKeyAlias = $fields0Arr[0];
					$listKey = $fields0Arr[1];
				} else {
					$listKey = $fields[0];
				}
			}
			if(isset($fields[1])) {
				$fields1Arr = explode('.', $fields[1]);
				if(count($fields1Arr) == 2) {
					$listValueAlias = $fields1Arr[0];
					$listValue = $fields1Arr[1];
				} else {
					$listValue = $fields[1];
				}
			} else {
				$listValueAlias = $listKeyAlias;
				$listValue = $listKey;
			}
			if(isset($fields[2])) {
				$fields2Arr = explode('.', $fields[2]);
				if(count($fields2Arr) == 2) {
					$listGroupAlias = $fields2Arr[0];
					$listGroup = $fields2Arr[1];
				} else {
					$listGroup = $fields[2];
				}
			}
		}
		$pages = array();
		$single_flag = false;
		if(isset($results[$this->alias]['id'])) {
			$single_flag = true;
			$currentPageId = $results[$this->alias]['id'];
			$results = array($results);
		}
		if(is_array($results)) {
			if(!empty($ativePageId) && $type == "menu") {
				$bufPages = array();
				foreach ($results as $key => $val) {
					$bufPages[$val[$this->alias]['id']] = $val[$this->alias]['parent_id'];
				}
				$activeIdArr = array($ativePageId => true);
				$bufPageId = $ativePageId;
				while(1) {
					if(!empty($bufPages[$bufPageId])) {
						$activeIdArr[$bufPages[$bufPageId]] = true;
						$bufPageId = $bufPages[$bufPageId];
					} else {
						break;
					}
				}
			}
			if($userId == 'all') {
				$loginUser = Configure::read(NC_SYSTEM_KEY.'.user');
				$setUserId = isset($loginUser['id']) ? $loginUser['id'] : _OFF;
			} else {
				$setUserId = $userId;
			}
			if(!empty($setUserId)) {
				$User = ClassRegistry::init('User');
				$currentUser = $User->findById($setUserId);
			}

			if($type == "thread") {
				$bufRootPages = array();
				$bufPages = array();
				foreach ($results as $key => $val) {
					$val = $this->setPageName($val);

					if($userId != 'all') {
						if(!empty($setUserId)) {
							$val['Authority'] = $currentUser['Authority'];
						}
						if(!isset($val['PageAuthority']['hierarchy'])) {
							$retDefault = $this->getDefaultAuthority($val);
							$val['PageAuthority']['id'] = $retDefault['id'];
							$val['PageAuthority']['hierarchy'] = $retDefault['hierarchy'];
						}

						if($val['PageAuthority']['hierarchy'] == NC_AUTH_OTHER) {
							continue;
						}
					}
					if($val[$this->alias]['thread_num'] == 1) {
						$bufRootPages[intval($val[$this->alias]['space_type'])][intval($val[$this->alias]['display_sequence'])] = $val;
					} else {
						$bufPages[intval($val[$this->alias]['root_id'])][intval($val[$this->alias]['display_sequence'])] = $val;
					}
				}
				foreach($bufRootPages as $bufRootPageSpace) {
					foreach($bufRootPageSpace as $bufRootPage) {
						$pages[$bufRootPage[$this->alias]['id']] = $bufRootPage;
						if(isset($bufPages[$bufRootPage[$this->alias]['id']])) {
							ksort($bufPages[$bufRootPage[$this->alias]['id']]);
							foreach($bufPages[$bufRootPage[$this->alias]['id']] as $bufPage) {
								$pages[$bufPage[$this->alias]['id']] = $bufPage;
							}
						}
					}
				}
			} else {
				$parentDisplayArr = array();
				foreach ($results as $key => $val) {
					$val = $this->setPageName($val);
					if(!empty($setUserId)) {
						$val['Authority'] = $currentUser['Authority'];
					}
					if(!isset($val['PageAuthority']['hierarchy'])) {
						$retDefault = $this->getDefaultAuthority($val);
						$val['PageAuthority']['id'] = $retDefault['id'];
						$val['PageAuthority']['hierarchy'] = $retDefault['hierarchy'];
					}
					if($type == "menu") {
						$preDisplayFlag = $val[$this->alias]['display_flag'];

						$val[$this->alias] = $this->updateDisplayFlag($val[$this->alias]);

						if($preDisplayFlag != $val[$this->alias]['display_flag'] &&
						($val[$this->alias]['display_flag'] == _OFF ||
						($val[$this->alias]['display_flag'] == _ON && $val[$this->alias]['display_apply_subpage'] == _ON))) {
							// 親が非公開ならば、子供が公開になっていても非公開として表示。
							// 公開日付Toを設定直後に親が非公開、子供が公開で表示されてしまうため
							// 親が公開で、「下位ページにも適用」のチェックボックスがONの場合も同様。
							$parentDisplayArr[$val[$this->alias]['id']] = $val[$this->alias]['display_flag'];
						}
						if(isset($parentDisplayArr[$val[$this->alias]['parent_id']])) {
							$val[$this->alias]['display_flag'] = $parentDisplayArr[$val[$this->alias]['parent_id']];
						}
						if(!empty($ativePageId)) {
							if(!empty($activeIdArr[$val[$this->alias]['id']])) {
								$val[$this->alias]['active'] = true;
							} else {
								$val[$this->alias]['active'] = false;
							}

							if(isset($activeIdArr[$val[$this->alias]['parent_id']]) || $val[$this->alias]['thread_num'] <= 1) {	// || $val[$this->alias]['thread_num'] <= 2
								$val[$this->alias]['show'] = true;
							} else {
								$val[$this->alias]['show'] = false;
							}
						}
						$val[$this->alias]['visibility_flag'] = empty($val['Menu']['visibility_flag']) ? _ON : $val['Menu']['visibility_flag'];
						$val[$this->alias]['permalink'] = $this->getPermalink($val[$this->alias]['permalink'], $val[$this->alias]['space_type']);
						$pages[$val[$this->alias]['space_type']][$val[$this->alias]['thread_num']][$val[$this->alias]['parent_id']][$val[$this->alias]['display_sequence']] = $val;
					} else if($type == "all") {
						$pages[$val[$this->alias]['id']] = $val;
					} else {
						// list
						if(isset($listGroup)) {
							$pages[$val[$listGroupAlias][$listGroup]][$val[$listKeyAlias][$listKey]] = $val[$listValueAlias][$listValue];
						} else {
							$pages[$val[$listKeyAlias][$listKey]] = $val[$listValueAlias][$listValue];
						}
					}

				}
			}
		}
		if(count($pages) == 0)
			return false;

		if($single_flag) {
			return $pages[$currentPageId];
		}

		return $pages;
	}

/**
 * パンくずリストの配列を取得する
 *
 * @param Model Page $page
 * @param string $userId
 * @return array
 * @access public
 */
	function findBreadcrumb($page, $userId = null) {
		$results = array();
		if(empty($userId)) {
			$loginUser = Configure::read(NC_SYSTEM_KEY.'.user');
			$userId = isset($loginUser['id']) ? $loginUser['id'] : null;
		}

		if(!isset($page['PageAuthority']['hierarchy'])) {
			$page['PageAuthority']['hierarchy'] = $this->getDefaultHierarchy($page);
		}
		if(isset($page['CommunityLang']['community_name'])) {
			$page['Page']['page_name'] = $page['CommunityLang']['community_name'];
		}
		$page = $this->setPageName($page);
		$page['Page']['permalink'] = $this->getPermalink($page['Page']['permalink'], $page['Page']['space_type']);

		if(($page['Page']['space_type'] != NC_SPACE_TYPE_PUBLIC && $page['Page']['thread_num'] > 1) ||
				($page['Page']['space_type'] == NC_SPACE_TYPE_PUBLIC && $page['Page']['display_sequence'] > 1)) {
			$parent_page = $this->findAuthById($page['Page']['parent_id'], $userId, $page['Page']['space_type']);
			$parents_page = $this->findBreadcrumb($parent_page, $userId);
			foreach($parents_page as $buf_parent_page) {
				$results[] = $buf_parent_page;
			}
		}
		$results[] = $page;

		return $results;
	}

/**
 * 閲覧可能のルームリストを取得(セレクトボックス用)
 *
 * @param   integer|string 'all'  $userId
 * @param   integer  $spaceType nullならば、すべてのルームが対象
 * @param   boolean $isShowAllCommunity:default true
 * 					true :公開コミュニティーを含む閲覧可能なすべてのコミュニティー　
 * 					false:参加コミュニティーのみ
 * 						PageUserLinkテーブルがあるデータ+コミュニティーのデフォルト参加が一般ならば、公開コミュニティーすべて-不参加にした会員のルームを引く。
 * @return  array key:room_id => array(key:thread_num => page_name)
 * @since   v 3.0.0.0
 */
	public function findRoomList($userId, $spaceType = null, $isShowAllCommunity = true) {
		$addParams = array();
		if(isset($spaceType)) {
			$addParams = array(
				'conditions' => array(
					'Page.space_type' => $spaceType,
				)
			);
		}
		$options = array(
			'isRoom' => true,
			'isShowAllCommunity' => $isShowAllCommunity,
		);
		return $this->findViewable('thread', $userId, $addParams, $options);
	}

/**
 * 閲覧可能のルームリストを取得
 *
 * type:listはkey,valueまで取得可能
 * TODO:左カラム等は現状、含めていない。
 *
 * @param   string  $type first or all or list, menu(メニュー表示時), child_menus(メニュー表示時)
 * @param   integer|string 'all'  $userId
 * @param   array   $addParams
 * @param   array   $options
 * 				'isShowAllCommunity':default true
 * 					true :公開コミュニティーを含む閲覧可能なすべてのコミュニティー　
 * 					false:参加コミュニティーのみ
 * 						PageUserLinkテーブルがあるデータ+コミュニティーのデフォルト参加が一般ならば、公開コミュニティーすべて-不参加にした会員のルームを引く。
 * 				'isMyPortalCurrent':カレントのマイポータルを優先的に取得するかどうか default false
 * 				'ativePageId': $type='menu'時に使用。アクティブなページIDを指定default null
 * @return  Model Pages
 * @since   v 3.0.0.0
 */
	public function findViewableRoom($type = 'all', $userId = null, $addParams = array(), $options = array()) {
		$options['isRoom'] = true;
		return $this->findViewable($type, $userId, $addParams, $options);
	}

/**
 * 閲覧可能のページリストを取得
 *
 * type:listはkey,valueまで取得可能
 * TODO:左カラム等は現状、含めていない。
 *
 * @param   string  $type first or all or list, menu(メニュー表示時), child_menus(メニュー表示時)
 * @param   integer|string 'all'  $userId
 * @param   array   $addParams
 * @param   array   $options
 * 				'isShowAllCommunity':default true
 * 					true :公開コミュニティーを含む閲覧可能なすべてのコミュニティー　
 * 					false:参加コミュニティーのみ
 * 						PageUserLinkテーブルがあるデータ+コミュニティーのデフォルト参加が一般ならば、公開コミュニティーすべて-不参加にした会員のルームを引く。
 * 				'isMyPortalCurrent':カレントのマイポータルを優先的に取得するかどうか default false
 * 				'ativePageId': $type='menu'時に使用。アクティブなページIDを指定default null
 * 				'autoLang' : 言語を自動で条件にいれる場合、true default true
 * 				'isRoom':ルームのみ取得するかどうか。default false
 * @return  Model Pages
 * @since   v 3.0.0.0
 */
	public function findViewable($type = 'all', $userId = null, $addParams = array(), $options = array()) {
		$lang = Configure::read(NC_CONFIG_KEY.'.'.'language');
		$loginUser = Configure::read(NC_SYSTEM_KEY.'.user');

		if(!isset($userId)) {
			$userId = $loginUser['id'];
		}

		$options = array_merge(array(
			'isShowAllCommunity' => true,
			'isMyPortalCurrent' => false,
			'ativePageId' => null,
			'autoLang' => true,
			'isRoom' => false,
		), $options);

		$conditions = array(
			'Page.position_flag' => _ON,
			'Page.display_flag !=' => NC_DISPLAY_FLAG_DISABLE,
			'Page.thread_num !=' => 0,
		);
		if($options['autoLang']) {
			$conditions['Page.lang'] = array('', $lang);
		}

		if($options['isRoom']) {
			$conditions[] = "`Page`.`id`=`Page`.`room_id`";
		}
		$joins = array(
			array(
				"type" => "LEFT",
				"table" => "communities",
				"alias" => "Community",
				"conditions" => "`Page`.`root_id`=`Community`.`room_id`"
			),
		);

		if($options['isMyPortalCurrent']) {
			$centerPage = Configure::read(NC_SYSTEM_KEY.'.'.'center_page');

			if($centerPage['Page']['room_id'] != $loginUser['myportal_page_id'] &&
				$centerPage['Page']['space_type'] == NC_SPACE_TYPE_MYPORTAL) {
				// マイポータルで、現在のカレントのもの取得
				// TODO:マイポータルに子グループを作成できる仕様にすると動作しない。
				$User = ClassRegistry::init('User');
				$currentUser = $User->currentUser($centerPage, $loginUser);

				// allow_myportal_viewing_hierarchyは、会員権限の上下でみせるべきかいなか
				// 決定するため、$loginUser['hierarchy']でチェック
				if(isset($currentUser['Authority']) && (
					($currentUser['Authority']['myportal_use_flag'] == NC_MYPORTAL_USE_ALL) ||
					($currentUser['Authority']['myportal_use_flag'] == NC_MYPORTAL_MEMBERS &&
					$loginUser['hierarchy'] >= $currentUser['Authority']['allow_myportal_viewing_hierarchy']))) {
					// 参加
					$currentMyPortal = $centerPage['Page']['room_id'];
				}
				$currentPrivate = $loginUser['private_page_id'];
			} else {
				$currentMyPortal = $loginUser['myportal_page_id'];
				$currentPrivate = $loginUser['private_page_id'];
			}
		} else if(!empty($userId) && $userId != 'all') {
			$User = ClassRegistry::init('User');
			$currentUser = $User->findById($userId);
			$currentMyPortal = $currentUser['User']['myportal_page_id'];
			$currentPrivate = $currentUser['User']['private_page_id'];
		}

		$defaultHierarchy = Configure::read(NC_CONFIG_KEY.'.default_entry_group_hierarchy');
		if($userId === 'all') {
			// 全ルーム取得
		} else if(empty($userId)) {
			// ログイン前
			// TODO:NC_SPACE_TYPE_MYPORTALは設定によっては、ログインした一部会員しかみえないため
			// 修正する必要あり？
			if($defaultHierarchy >= NC_AUTH_MIN_GENERAL || $options['isShowAllCommunity']) {
				$conditions['or'] = array(
					'Page.space_type' => array(NC_SPACE_TYPE_PUBLIC, NC_SPACE_TYPE_MYPORTAL),
					'Community.publication_range_flag' => NC_PUBLICATION_RANGE_FLAG_ALL,
					array(
						'PageUserLink.authority_id IS NOT NULL',
						'PageUserLink.authority_id !=' => NC_AUTH_OTHER_ID,
					),
				);
			} else {
				$conditions['or'] = array(
					'Page.space_type' => array(NC_SPACE_TYPE_PUBLIC, NC_SPACE_TYPE_MYPORTAL)
				);
			}
		} else {
			// ログイン後
			if($defaultHierarchy >= NC_AUTH_MIN_GENERAL || $options['isShowAllCommunity']) {
				$conditions['or'] = array(
					'Page.space_type' => array(NC_SPACE_TYPE_PUBLIC, NC_SPACE_TYPE_MYPORTAL, NC_SPACE_TYPE_PRIVATE),
					array(
						'Community.publication_range_flag' => array(NC_PUBLICATION_RANGE_FLAG_LOGIN_USER, NC_PUBLICATION_RANGE_FLAG_ALL),
						'or' => array('PageUserLink.authority_id !=' => NC_AUTH_OTHER_ID,'PageUserLink.authority_id IS NULL'),
					),
					'PageUserLink.authority_id !=' => NC_AUTH_OTHER_ID,
					//array(
					//	'PageUserLink.authority_id IS NOT NULL',
					//	'PageUserLink.authority_id !=' => NC_AUTH_OTHER_ID,
					//),
				);
			} else {
				$conditions['or'] = array(
					'Page.space_type' => array(NC_SPACE_TYPE_PUBLIC, NC_SPACE_TYPE_MYPORTAL, NC_SPACE_TYPE_PRIVATE),
					'PageUserLink.authority_id !=' => NC_AUTH_OTHER_ID,
					//array(
					//	'PageUserLink.authority_id IS NOT NULL',
					//	'PageUserLink.authority_id !=' => NC_AUTH_OTHER_ID,
					//),
				);
			}

		}

		if(!empty($userId) && $userId != 'all' || ($defaultHierarchy >= NC_AUTH_MIN_GENERAL || $options['isShowAllCommunity'])) {
			// ログイン後 OR すべての公開コミュニティーを含む
			$joins[] = array(
				"type" => "LEFT",
				"table" => "page_user_links",
				"alias" => "PageUserLink",
				"conditions" => "`PageUserLink`.`user_id`=".intval($userId).
				" AND `Page`.`room_id`=`PageUserLink`.`room_id`"
			);
		}

		if(!empty($userId) && $userId != 'all') {
			// ログイン後
			$joins[] = array(
				"type" => "LEFT",
				"table" => "authorities",
				"alias" => "PageAuthority",
				"conditions" => "`PageAuthority`.`id`=`PageUserLink`.`authority_id`"
			);
		}
		$spaceType = null;
		if(isset($addParams['conditions']['Page.space_type'])) {
			$spaceType = $addParams['conditions']['Page.space_type'];
		}
		if((empty($spaceType) || $spaceType != NC_SPACE_TYPE_GROUP) && isset($currentMyPortal) && isset($currentPrivate)) {
			$conditions['and']['or'] = array(
				'Page.room_id' => array($currentMyPortal, $currentPrivate),
				'Page.space_type' => array(NC_SPACE_TYPE_PUBLIC, NC_SPACE_TYPE_GROUP)
			);
		}
		if(empty($spaceType) || $spaceType == NC_SPACE_TYPE_GROUP || (is_array($spaceType) && in_array(NC_SPACE_TYPE_GROUP, $spaceType))) {
			$joins[] = array(
				"type" => "LEFT",
				"table" => "community_langs",
				"alias" => "CommunityLang",
				"conditions" => "`Page`.`id`=`CommunityLang`.`room_id`".
				" AND `CommunityLang`.`lang` ='".$lang."'"
			);
		}
		$fields = array();
		$order = array();
		$offset = (isset($addParams['offset'])) ? $addParams['offset'] : null;
		$page = (isset($addParams['page'])) ? $addParams['page'] : null;
		$limit = (isset($addParams['limit'])) ? $addParams['limit'] : null;
		if($type != 'count') {
			$fields = $this->getFieldsArray($userId, $spaceType);
			if(!isset($addParams['fields']) && $type == 'list') {
				$addParams['fields'] = array('Page.room_id', 'PageAuthority.id');
			}

			if(isset($addParams['order'])) {
				$order = $addParams['order'];
			} else {
				$order = array(
					'Page.space_type' => "ASC",
					'Page.thread_num' => "ASC",
					'Page.display_sequence' => "ASC"
				);
			}
		}

		if(isset($addParams['conditions'])) {
			$conditions = array_merge($conditions, $addParams['conditions']);
		}
		if(isset($addParams['joins'])) {
			$joins = array_merge($joins, $addParams['joins']);
		}

		$params = array(
			'fields' => $fields,
			'conditions' => $conditions,
			'joins' => $joins,
			'order' => $order,
			'page' => $page,
			'offset' => $offset,
			'limit' => $limit,
		);

		if($type == 'count') {
			return $this->find($type, $params);
		}
		if($type === 'child_menus') {
			$results = $this->find('all', $params);
			if(isset($options['ativePageId']) ) {
				$parentIdArr = array($options['ativePageId'] => true);
				if(isset($results['Page'])) {
					$results = array($results['Page']);
				}
				foreach($results as $key => $result) {
					if(isset($parentIdArr[$result['Page']['parent_id']])) {
						$parentIdArr[$result['Page']['id']] = true;
					} else {
						unset($results[$key]);
					}
				}
				$bufResults = array();
				$count = 0;
				foreach($results as $result) {
					$result = $this->setPageName($result);
					$bufResults[$count] = $result;
					$count++;
				}
				$results = $bufResults;
			}
			return $results;
		}
		return $this->afterFindIds($this->find('all', $params), $userId, $type, isset($addParams['fields']) ? $addParams['fields'] : null, $options['ativePageId']);
	}

/**
 * CommunityLang.community_nameを含むページ情報取得
 *
 * @param integer   $pageId
 * @return Model Page, CommunityLang.community_name
 * @since   v 3.0.0.0
 */
	public function findIncludeComunityLang($pageId) {
		$lang = Configure::read(NC_CONFIG_KEY.'.'.'language');
		$params = array(
			'fields' => array('Page.*', 'CommunityLang.community_name'),
			'joins' => array(
				array(
					"type" => "LEFT",
					"table" => "community_langs",
					"alias" => "CommunityLang",
					"conditions" => "`Page`.`room_id`=`CommunityLang`.`room_id`".
					" AND `CommunityLang`.`lang` ='".$lang."'"
				),
			),
			'conditions' => array('Page.id' => $pageId)
		);
		return $this->find('first', $params);
	}

/**
 * Current Pageの子供のページを取得
 *
 * @param string    $type first or all or list
 * @param array     $current_user
 * @param string    $lang
 * @param   integer|string 'all'  $userId
 * @return  array   $fields
 * @since   v 3.0.0.0
 */
	public function findChilds($type, $currentPage, $lang = null, $userId = 'all') {

		$lang = !isset($lang) ? $currentPage['Page']['lang'] : $lang;
		$addParams = array(
			'conditions' => array(
				'Page.space_type' => $currentPage['Page']['space_type'],
				'Page.root_id' => $currentPage['Page']['root_id'],
				'Page.thread_num >' => $currentPage['Page']['thread_num']
			)
		);
		if(isset($lang) && $lang != '') {
			$addParams['conditions']['Page.lang'] = array('', $lang);
		}
		$options = array(
			'isShowAllCommunity' => true,
			'autoLang' => false,
			'ativePageId' => $currentPage['Page']['id'],
		);
		return $this->findViewable('child_menus', $userId, $addParams, $options);
	}

/**
 * コミュニティー数の取得
 * @param integer    $loginUserId
 * @param array      $params
 * @param array      $options
 * @return  integer コミュニティー数
 * @since   v 3.0.0.0
 */
	public function findCommunityCount($loginUserId = null, $params = null, $options = array()) {
		$addParams = array(
			'conditions' => array(
				'Page.space_type' => NC_SPACE_TYPE_GROUP,
				'Page.thread_num' => 1,
			)
		);
		$defaultOptions = array(
			'isShowAllCommunity' => true
		);
		if(isset($params['conditions'])) {
			$addParams['conditions'] = array_merge($addParams['conditions'], $params['conditions']);
		}

		$options = array_merge($defaultOptions, $options);
		return $this->findViewable('count', $loginUserId, $addParams, $options);
	}

/**
 * コミュニティーpaginate用メソッド
 * @param array    $conditions
 * @param array    $fields
 * @param array    $order
 * @param array    $limit
 * @param array    $page
 * @param integer  $recursive
 * @param array    $extra
 * @return  array  コミュニティーのroom_idリスト
 * @since   v 3.0.0.0
 */
	public function paginate($conditions, $fields, $order, $limit, $page = 1, $recursive = null, $extra = array()) {
		$conditions = array_merge(array(
			'Page.space_type' => NC_SPACE_TYPE_GROUP
		), $conditions);

		if(isset($extra['is_all']) && $extra['is_all']) {
			$is_all = true;
		} else {
			$is_all = false;
		}

		$addParams = array(
			'fields' => $fields,
			'conditions' => $conditions,
			'page' => $page,
			'limit' => $limit,
			'recursive' => $recursive
		);
		$options = array(
			'isShowAllCommunity' => true
		);
		return $this->findViewable('list', ($is_all) ? 'all' : $extra['user_id'], $addParams, $options);
	}

/**
 * コミュニティーpaginate用メソッド(コミュニティー数)
 * @param array    $conditions
 * @param integer  $recursive
 * @param array    $extra
 * @return  integer コミュニティー数
 * @since   v 3.0.0.0
 */
	public function paginateCount($conditions = null, $recursive = 0, $extra = array()) {
		if(isset($extra['is_all']) && $extra['is_all']) {
			$is_all = true;
		} else {
			$is_all = false;
		}
		$params = array(
			'conditions' => $conditions,
			'recursive' => $recursive
		);
		return $this->findCommunityCount(($is_all) ? 'all' : $extra['user_id'], $params);
	}

/**
 * Pageモデル共通Fields文
 * @param   integer|string 'all'  $userId
 * @param   string|array  $spaceType
 * @return  array   $fields
 * @since   v 3.0.0.0
 */
	public function getFieldsArray($userId, $spaceType = null) {
		if(empty($userId) || $userId == 'all') {
			$ret = array(
				'Page.*',
			);
		} else {
			$ret = array(
				'Page.*',
				'PageUserLink.authority_id',
				'PageAuthority.id',
				'PageAuthority.myportal_use_flag', 'PageAuthority.private_use_flag', 'PageAuthority.hierarchy'
			);
		}
		if(empty($spaceType) || $spaceType == NC_SPACE_TYPE_GROUP || (is_array($spaceType) && in_array(NC_SPACE_TYPE_GROUP, $spaceType))) {
			$ret[count($ret)] = 'Community.publication_range_flag, CommunityLang.community_name, CommunityLang.summary';
		}
		return $ret;
	}

/**
 * Pageモデル共通JOIN文
 * @param   integer $userId
 * @param   string  $type LEFT or INNER
 * @param   string  $spaceType
 * @return  array   $joins
 * @since   v 3.0.0.0
 */
	public function getJoinsArray($userId, $type = 'LEFT', $spaceType = null) {
		$ret = array(
			array(
				"type" => $type,
				"table" => "page_user_links",
				"alias" => "PageUserLink",
				"conditions" => "`Page`.`room_id`=`PageUserLink`.`room_id`".
					" AND `PageUserLink`.`user_id` =".intval($userId)
			),
			array(
				"type" => "LEFT",
				"table" => "authorities",
				"alias" => "PageAuthority",
				"conditions" => "`PageAuthority`.`id`=`PageUserLink`.`authority_id`"
			)
		);
		if(empty($spaceType) || $spaceType == NC_SPACE_TYPE_GROUP) {
			$lang = Configure::read(NC_CONFIG_KEY.'.'.'language');
			$ret[count($ret)] = array(
				"type" => "LEFT",
				"table" => "communities",
				"alias" => "Community",
				"conditions" => "`Page`.`root_id`=`Community`.`room_id`"
			);
			$ret[count($ret)] = array(
				"type" => "LEFT",
				"table" => "community_langs",
				"alias" => "CommunityLang",
				"conditions" => "`Page`.`id`=`CommunityLang`.`room_id`".
					" AND `CommunityLang`.`lang` ='".$lang."'"
			);
		}
		return $ret;
	}

/**
 * ルーム（ページ）削除処理
 * @param mixed $id ID of record to delete
 * @param boolean $allDelete コンテンツもすべて削除するかどうか（NC_DELETE_MOVE_PARENTの場合、コンテンツを親のコンテンツへ）
 * @param Model Page $childPages 指定されていなければ取得
 * @param integer $parentRoomId $allDelete NC_DELETE_MOVE_PARENTの場合の振り替え先room_id
 * @param boolean $isRecursive 再帰的に呼ばれたかどうか
 * @return boolean True on success
 * @since   v 3.0.0.0
 */
	public function deletePage($id = null, $allDelete = _OFF, $childPages = null, $parentRoomId = null, $isRecursive = false) {
		if (!empty($id)) {
			$this->id = $id;
		}
		$id = $this->id;

		$page = $this->findById($id);
		if(!$page) {
			return false;
		}

		if($allDelete == NC_DELETE_MOVE_PARENT && !isset($parentRoomId)) {
			$parent_page = $this->findById($page['Page']['parent_id']);
			$parentRoomId = isset($parent_page['Page']) ? $parent_page['Page']['room_id'] : 0;
		}

		/*
		 * ブロック削除
		*/
		$Block = ClassRegistry::init('Block');
		$blocks = $Block->findAllByPageId($id);
		if($blocks != false && count($blocks) > 0) {
			if(isset($blocks['Block'])) {
				$blocks = array($blocks);
			}
			foreach($blocks as $block) {
				$Block->deleteBlock($block, $allDelete, $parentRoomId, true);
			}
		}

		if(!$isRecursive) {
			// 子ページ削除処理
			if(!isset($childPages)) {
				$childPages = $this->findChilds('all', $page);
			}
			foreach($childPages as $child_page) {
				if(!$this->deletePage($child_page['Page']['id'], $allDelete, null, $parentRoomId, true)) {
					return false;
				}
			}
			//前詰め処理
			if($page['Page']['display_sequence'] > 0) {
				if($page['Page']['thread_num'] == 1) {
					$childs_count = 0;
				} else {
					$childs_count = count($childPages);
				}

				if(!$this->decrementDisplaySeq($page, $childs_count + 1)) {
					return false;
				}
			}
		}

		// TODO:page_columns削除
		// TODO:page_metas削除
		// TODO:page_styles削除
		// TODO:page_sum_views削除
		// TODO:page_columns削除
		// TODO:page_themes削除
		// TODO:uploads削除
		// TODO:menu削除

		if($page['Page']['id'] == $page['Page']['room_id']) {
			// ルーム
			$PageUserLink = ClassRegistry::init('PageUserLink');
			$conditions = array(
				"PageUserLink.room_id" => $page['Page']['id']
			);
			$ret = $PageUserLink->deleteAll($conditions);
			if(!$ret) {
				return false;
			}

			$ModuleLink = ClassRegistry::init('ModuleLink');
			$conditions = array(
				"ModuleLink.room_id" => $page['Page']['id']
			);
			$ret = $ModuleLink->deleteAll($conditions);
			if(!$ret) {
				return false;
			}

			if($page['Page']['thread_num'] == 1 && $page['Page']['space_type'] == NC_SPACE_TYPE_GROUP) {
				// コミュニティー削除
				$Community = ClassRegistry::init('Community');
				$conditions = array(
					"Community.room_id" => $page['Page']['id']
				);
				$community = $Community->findByRoomId($page['Page']['id']);
				$ret = $Community->deleteAll($conditions);
				if(!$ret) {
					return false;
				}

				$CommunityLang = ClassRegistry::init('CommunityLang');
				$communityLangs = $CommunityLang->findAllByRoomId($page['Page']['id']);
				$conditions = array(
					"CommunityLang.room_id" => $page['Page']['id']
				);
				$ret = $CommunityLang->deleteAll($conditions);
				if(!$ret) {
					return false;
				}

				$UploadLink = ClassRegistry::init('UploadLink');
				$Revision = ClassRegistry::init('Revision');
				$CommunityTag = ClassRegistry::init('CommunityTag');
				$conditions = array(
					"UploadLink.plugin" => 'Page',
					"UploadLink.unique_id" => $community['Community']['id'],
					"UploadLink.model_name" => 'Community',
					"UploadLink.field_name" => 'photo',
				);
				$ret = $UploadLink->deleteAll($conditions);
				if(!$ret) {
					return false;
				}
				foreach($communityLangs as $communityLang) {
					if($communityLang['CommunityLang']['revision_group_id'] > 0) {
						$conditions = array(
							"UploadLink.plugin" => 'Page',
							"UploadLink.unique_id" => $communityLang['CommunityLang']['revision_group_id'],
							"UploadLink.model_name" => 'Revision',
							"UploadLink.field_name" => 'content',
						);
						$ret = $UploadLink->deleteAll($conditions);
						if(!$ret) {
							return false;
						}
						$conditions = array(
							"Revision.group_id" => $communityLang['CommunityLang']['revision_group_id'],
						);
						$ret = $Revision->deleteAll($conditions);
						if(!$ret) {
							return false;
						}
					}
					if(!$CommunityTag->deleteTags($page['Page']['id'], $communityLang['CommunityLang']['lang'])) {
						return false;
					}
				}
			}

			// ブロックとして配置していない該当ルームのコンテンツを親ルームがあれば、そちらへ移動、なければ完全に削除。
			$Content = ClassRegistry::init('Content');
			$params = array(
				'conditions' => array(
					"Content.room_id" => $page['Page']['room_id']
				)
			);

			$delete_contents = $Content->find('all', $params);
			if(count($delete_contents) > 0) {
				foreach($delete_contents as $delete_content) {
					$Content->deleteContent($delete_content, NC_DELETE_MOVE_PARENT, $parentRoomId);	// $allDelete
				}
			}
		}

		// 削除処理
		$ret = $this->delete($id);
		if($ret === false) {
			return $ret;
		}

		/*
		 * 削除されたページがConfig.first_startpage_id,second_startpage_id,third_startpage_idならば、更新
		* (パブリックに更新)
		*/
		$Config = ClassRegistry::init('Config');
		$conditions = array(
			'module_id' => 0,
			'cat_id' => NC_SYSTEM_CATID,
			'name' => array('first_startpage_id','second_startpage_id','third_startpage_id')
		);
		$params = array(
			'fields' => array(
				'Config.name',
				'Config.value'
			),
			'conditions' => $conditions
		);
		$configs = $Config->find('all', $params);
		$fields = array(
			'Config.value'=> 0
		);
		if($id == $configs['first_startpage_id'] && !$Config->updateAll($fields, array('Config.name' => 'first_startpage_id'))) {
			return false;
		}
		if($id == $configs['second_startpage_id'] && !$Config->updateAll($fields, array('Config.name' => 'second_startpage_id'))) {
			return false;
		}
		if($id == $configs['third_startpage_id'] && !$Config->updateAll($fields, array('Config.name' => 'third_startpage_id'))) {
			return false;
		}

		return true;
	}

/**
 * display_sequenceデクリメント処理
 *
 * @param  array     $page ページテーブル配列
 * @param  integer   $display_sequence デクリメントする数
 * @return boolean true or false
 * @since   v 3.0.0.0
 */
	public function decrementDisplaySeq($page = null,$display_sequence = 1, $conditions = array()) {
		$display_sequence = -1*$display_sequence;
		return $this->_operationDisplaySeq($page, $display_sequence, $conditions);
	}

/**
 * display_sequenceインクリメント処理
 *
 * @param  array     $page ページテーブル配列
 * @param  integer   $display_sequence インクリメントする数
 * @return boolean true or false
 * @since   v 3.0.0.0
 */
	public function incrementDisplaySeq($page = null,$display_sequence = 1, $conditions = array()) {
		return $this->_operationDisplaySeq($page, $display_sequence, $conditions);
	}

	protected function _operationDisplaySeq($page = null,$display_sequence = 1, $conditions = array()) {
		$lang = Configure::read(NC_CONFIG_KEY.'.'.'language');
		$fields = array('Page.display_sequence'=>'Page.display_sequence+('.$display_sequence.')');
		$conditions = array_merge($conditions, array(
			"Page.position_flag" => $page['Page']['position_flag'],
			"Page.lang" => array("", $lang),
			"Page.space_type" => $page['Page']['space_type'],
			"Page.display_sequence >=" => $page['Page']['display_sequence']
		));
		if($page['Page']['thread_num'] == 1) {
			$conditions["Page.thread_num"] = 1;
		} else {
			$conditions["Page.root_id"] = $page['Page']['root_id'];
			$conditions["Page.thread_num >"] = 1;
		}
		$ret = $this->updateAll($fields, $conditions);
		return $ret;
	}

/**
 * 移動後固定リンク取得
 *
 * @param  Model Page     $page
 * @param  Model Page     $parent_page 移動先親Page
 * @return boolean true or false
 * @since   v 3.0.0.0
 */
	public function getMovePermalink($page, $parent_page) {
		if($page['Page']['permalink'] == '') {
			// Topページ
			$page['Page']['permalink'] = preg_replace(NC_PERMALINK_PROHIBITION, NC_PERMALINK_PROHIBITION_REPLACE, $page['Page']['page_name']);
		}
		$permalink_arr = explode('/', $page['Page']['permalink']);
		if($parent_page['Page']['permalink'] != '') {
			$permalink = $parent_page['Page']['permalink'] . '/' . $permalink_arr[count($permalink_arr)-1];
		} else {
			$permalink = $permalink_arr[count($permalink_arr)-1];
		}
		return $permalink;
	}


/**
 * マイポータル作成, マイルーム作成, ルーム参加
 * @param   Model User $user
 * @return  boolean false|array($myportalPageId, private_page_id)
 * @since   v 3.0.0.0
 */
	public function createDefaultEntry($user) {
		$Authority = ClassRegistry::init('Authority');

		$authority = $Authority->find('first', array(
			'fields' => array('myportal_use_flag', 'private_use_flag'),
			'conditions' => array($Authority->primaryKey => $user['User']['authority_id']),
			'recursive' => -1
		));
		if(!isset($authority['Authority'])) {
			return false;
		}
		$myportalPageId = $this->insTopRoom(NC_SPACE_TYPE_MYPORTAL, $user['User']['id'], $user['User']['permalink'], $authority);
		$privatePageId = $this->insTopRoom(NC_SPACE_TYPE_PRIVATE, $user['User']['id'], $user['User']['permalink'], $authority);
		if(!$myportalPageId || !$privatePageId) {
			return false;
		}

		return array($myportalPageId, $privatePageId);
	}

/**
 * マイページ、マイポータルinsert
 *
 * @param integer   $spaceType
 * @param string    $permalink
 * @param array     $authority
 * @param array     $nodePage		編集の場合セット
 * @return mixed    false|integer $newRoomId
 * @since   v 3.0.0.0
 */
	public function insTopRoom($spaceType, $userId, $permalink, $authority = null, $nodePage = null) {
		if($spaceType == NC_SPACE_TYPE_MYPORTAL) {
			$useFlag = "myportal_use_flag";
			$lang = Configure::read(NC_CONFIG_KEY.'.'.'language');
			$pageName = 'Myportal Top';
		} else if($spaceType == NC_SPACE_TYPE_PRIVATE) {
			$useFlag = "private_use_flag";
			$lang = '';
			$pageName = 'Private Top';
		} else {
			$lang = Configure::read(NC_CONFIG_KEY.'.'.'language');
			$pageName = '';
		}

		$insPage = $this->findDefault($spaceType);
		if(!isset($useFlag) || $authority['Authority'][$useFlag]) {
			$insPage['Page']['display_flag'] = NC_DISPLAY_FLAG_ON;
		} else {
			$insPage['Page']['display_flag'] = NC_DISPLAY_FLAG_DISABLE;
		}
		if(isset($nodePage)) {
			$insPage['Page'] = array_merge($insPage['Page'], $nodePage['Page']);
		}

		/*
		 * Node Insert
		 */
		$insPage['Page']['permalink'] = $permalink;
		$nodePage = $insPage;
		//$nodePage['Page']['thread_num'] = 1;
		//$nodePage['Page']['display_sequence'] = 0;
		//	$nodePage['Page']['permalink'] = '';
		//if(isset($default_page_name)) {
		//	$nodePage['Page']['page_name'] = $default_page_name;
		//}
		$this->create();
		$this->set($nodePage);
		$ret = $this->save($nodePage);
		if(!$ret) {
			return false;
		}
		if(!empty($nodePage['Page']['id'])) {
			$newRoomId = $nodePage['Page']['id'];
		} else {
			$newRoomId = $this->id;
			$updNodePage = array();
			$updNodePage['Page']['id'] = $newRoomId;
			$updNodePage['Page']['root_id'] = $newRoomId;
			$updNodePage['Page']['room_id'] = $newRoomId;
			$this->create();
			$this->set($updNodePage);
			if(!$this->save($updNodePage, false, array('root_id', 'room_id'))) {
				return false;
			}
		}

		/*
		 * Page Insert
		 */
		$fields = null;
		/*if(isset($insPage['Page']['id'])) {
			// トップページを求める
			$conditions = array(
				'Page.parent_id' => $newRoomId,
				'Page.display_sequence' => 1
			);
			$params = array(
				'fields' => array(
					'Page.id'
				),
				'conditions' => $conditions
			);
			$topPage = $this->find('first', $params);
			$insPage['Page']['id'] = $topPage['Page']['id'];
			if($spaceType != NC_SPACE_TYPE_GROUP) {
				unset($insPage['Page']['page_name']);
				$fields = array('permalink', 'thread_num', 'display_sequence', 'root_id', 'room_id', 'parent_id', 'lang');
			}
		}*/

		$insPage['Page']['thread_num'] = 2;
		$insPage['Page']['display_sequence'] = 1;
		$insPage['Page']['root_id'] = $newRoomId;
		$insPage['Page']['room_id'] = $newRoomId;
		$insPage['Page']['parent_id'] = $newRoomId;
		$insPage['Page']['page_name'] = $pageName;
		//TODO:後に削除 0固定にしておき、その上のノードをみてテーマを判断するのをデフォルトにするため
		// 現状未作成 後にコメントをはずす
		// $insPage['Page']['page_style_id'] = 0;
		$insPage['Page']['lang'] = $lang;

		$this->create();
		$this->set($insPage);
		$ret = $this->save($insPage, true, $fields);
		if(!$ret) {
			return false;
		}

		/*
		 * page_user_links Insert
		 */
		$PageUserLink = ClassRegistry::init('PageUserLink');
		$pageUserLink = array('PageUserLink');
		$pageUserLink['PageUserLink']['room_id'] = $newRoomId;
		$pageUserLink['PageUserLink']['user_id'] = $userId;
		$pageUserLink['PageUserLink']['authority_id'] = NC_AUTH_CHIEF_ID;
		$PageUserLink->create();
		$PageUserLink->set($pageUserLink);
		if(!$PageUserLink->save($pageUserLink)) {
			return false;
		}

		return $newRoomId;
	}

/**
 * User.permalink更新処理
 *
 * @param integer   $pageId
 * @param string    $permalink
 * @return boolean
 * @since   v 3.0.0.0
 */
	public function updPermalinks($pageId, $permalink) {
		$pages = $this->findAllByRootId($pageId);
		foreach($pages as $page) {
			$permalinkArr = explode('/', $page['Page']['permalink']);
			$permalinkArr[0] = $permalink;
			$page['Page']['permalink'] = implode('/', $permalinkArr);

			if(!$this->save($page, true, array('permalink'))) {
				return false;
			}
		}
		return true;
	}

/**
 * is_page_XXX_nodeが指定されているNodeのリストを取得
 *
 * @param Model Page  $page
 * @param array $rets
 * @return array
 * @since   v 3.0.0.0
 */
	public function findNodeFlag($page, $rets = null) {
		if(empty($rets)) {
			$rets = array(
				'is_page_meta_node' => _OFF,
				'is_page_style_node' => _OFF,
				'is_page_layout_node' => _OFF,
				'is_page_theme_node' => _OFF,
				'is_page_column_node' => _OFF,
			);
		}
		$params = array(
			'fields' => array('Page.id', 'Page.parent_id', 'Page.is_page_meta_node', 'Page.is_page_style_node', 'Page.is_page_layout_node', 'Page.is_page_theme_node', 'Page.is_page_column_node'),
			'conditions' => array(
				//'Page.space_type' => $page['Page']['space_type'],
				'Page.root_id' => $page['Page']['root_id'],
				'Page.thread_num <' => $page['Page']['thread_num'],
				'Page.lang' => array('', $page['Page']['lang'])
			),
			'order' => array('Page.thread_num' => 'desc')
		);
		$parentIds = array($page['Page']['parent_id']);
		$pages = $this->find('all', $params);

		foreach($rets as $key => $ret) {
			if($page['Page'][$key]) {
				$rets[$key] = $page['Page']['id'];
			} else {
				$parentIds = array($page['Page']['parent_id']);
				foreach($pages as $bufPage) {
					if(in_array($bufPage['Page']['id'], $parentIds)) {
						$parentIds[] = $bufPage['Page']['parent_id'];
						if($bufPage['Page'][$key]) {
							$rets[$key] = $bufPage['Page']['id'];
							break;
						}
					}
				}
			}
		}
		return $rets;
	}
}