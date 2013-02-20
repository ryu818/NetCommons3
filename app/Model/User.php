<?php
/**
 * Userモデル
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Model
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class User extends AppModel
{
	public $name = 'User';

	public $belongsTo = 'Authority';

/**
 * Behavior name
 *
 * @var array
 */
	public $actsAs = array('TimeZone');

	public $validate = array();

	public $email_tags = array();

/**
 * バリデート前処理
 *
 * <pre>
 * validatesメソッドを実行すると自動で処理される
 * </pre>
 *
 * @param   array  オプション
 * @return  void
 * @since   v 3.0.0.0
 */
	public function beforeValidate($options = array())
	{
		/*
		 * エラーメッセージ設定
		 */
	    //App::import('Model','Item');
	    App::uses('Item', 'Model');
		$this->Item = new Item();

	    $params = array(
				'order' => array(
					'Item.col_num' => 'ASC',
					'Item.row_num' => 'ASC'
				),
				'recursive' => -1
		);
		$items = $this->Item->find('all', $params);
		/*
		 * emailカラムセット
		 */
		$this->email_tags = array();
		foreach($items as $item) {
			if($item['Item']['type'] == NC_ITEM_TYPE_EMAIL || $item['Item']['type'] == NC_ITEM_TYPE_MOBILE_EMAIL) {
				$this->email_tags[$item['Item']['id']] = $item['Item']['tag_name'];
			}
		}

		foreach($items as $item) {
			if($item['Item']['tag_name'] == "") {
				continue;
			}

			// 必須チェック
			if($item['Item']['require_flag'] == _ON) {
				//if($item['Item']['tag_name'] != 'password' || empty($this->data[$this->alias]['id'])) {
					// パスワードで編集ならば必須としない
					// TODO:インストールでは、パスワードで編集であっても必須チェックをしなければならないため、一時コメントアウト。
					$this->validate[$item['Item']['tag_name']]['notEmpty'] = array(
						'rule' => array('notEmpty'),
						'last' => true,
						//'required' => true,
						'allowEmpty' => false,
						'message' => __('Please input %s.', __d('user_items', $item['Item']['item_name'])),
					);
				//}
			}

			// 重複チェック
			if($item['Item']['duplicate_flag'] == _ON) {
				if($item['Item']['type'] == NC_ITEM_TYPE_EMAIL || $item['Item']['type'] == NC_ITEM_TYPE_MOBILE_EMAIL) {
					$this->validate[$item['Item']['tag_name']]['duplicateEmail'] = array(
						'rule' => array('duplicateEmail'),
						//'last' => false,
						'allowEmpty' => true,
						'message' => __('The contents of %s is already in use.', __d('user_items', $item['Item']['item_name'])),
					);
				} else {
					$this->validate[$item['Item']['tag_name']]['duplicate'] = array(
						'rule' => array('duplicate'),
						//'last' => false,
						'allowEmpty' => true,
						'message' => __('The contents of %s is already in use.', __d('user_items', $item['Item']['item_name'])),
					);
				}
			}

			// minlengthチェック
			if($item['Item']['minlength'] != '0') {
				$this->validate[$item['Item']['tag_name']]['minlength'] = array(
					'rule' => array('minLength', $item['Item']['minlength']),
					//'last' => false,
					'allowEmpty' => true,
					'message' => __('The input must be at least %s characters.', $item['Item']['minlength']),
				);
			}

			// maxlengthチェック
			if($item['Item']['maxlength'] != '0') {
				$this->validate[$item['Item']['tag_name']]['maxlength'] = array(
					'rule' => array('maxLength', $item['Item']['maxlength']),
					//'last' => true,
					//'required' => true,
					'allowEmpty' => true,
					'message' => __('The input must be up to %s characters.', $item['Item']['maxlength']),
				);
			}

			// emailチェック
			if($item['Item']['type'] == NC_ITEM_TYPE_EMAIL || $item['Item']['type'] == NC_ITEM_TYPE_MOBILE_EMAIL) {
				$this->validate[$item['Item']['tag_name']]['email'] = array(
					'rule' => array('email'),
					//'last' => true,
					//'required' => true,
					'allowEmpty' => true,
					'message' => __('Unauthorized pattern for %s.', __d('user_items', $item['Item']['item_name'])),
				);
			}

			// 正規表現チェック
			if($item['Item']['regexp'] != '') {
				$this->validate[$item['Item']['tag_name']]['email'] = array(
					'rule' => array('custom', $item['Item']['regexp']),
					//'last' => true,
					//'required' => true,
					'allowEmpty' => true,
					'message' => __('Unauthorized pattern for %s.', __d('user_items', $item['Item']['item_name'])),
				);
			}

			// authority_idチェック
			/*if($item['Item']['tag_name'] == 'authority_id') {
				$this->validate[$item['Item']['tag_name']]['chkAuthorityId'] = array(
					'rule' => array('_chkAuthorityId'),
					//'last' => true,
					//'required' => true,
					'allowEmpty' => false,
					'message' => __('Unauthorized request.<br />Please reload the page.')
				);
			}*/

			// permalinkチェック
			if($item['Item']['tag_name'] == 'permalink') {
				$this->validate[$item['Item']['tag_name']]['invalidPermalink'] = array(
					'rule' => array('invalidPermalink'),
					'last' => true,
					'message' => __('It contains an invalid string.')
				);
			}
		}

		return parent::beforeValidate($options);
	}

/**
 * 権限IDチェック
 * set(User)時にAuthorityのデータもセットしておく
 *
 * @param   array    $data
 * @return  boolean
 * @since   v 3.0.0.0
 */
/* TODO:使用方法を再検討
	protected function _chkAuthorityId($data) {
		if(!isset($this->data['Authority']))
			return false;
		return true;
    }
*/

/**
 * 重複チェック
 *
 * @param   array    $data
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function duplicate($data) {
		if(!empty($this->data[$this->alias]['id']))
			$data['id !='] = $this->data[$this->alias]['id'];

		$count = $this->find( 'count', array('conditions' => $data, 'recursive' => -1) );
		if($count != 0)
			return false;
		return true;
    }

/**
 * 重複チェック(Email)
 *
 * @param   array    $check
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function duplicateEmail($data) {
		$values = array_values($data);

		$conditions['or'] = array(
									array('User.email' => $values[0]),
									array('User.mobile_email' => $values[0])
							);
		if(!empty($this->data[$this->alias]['id']))
			$conditions['id !='] = $this->data[$this->alias]['id'];

		$count = $this->find( 'count', array('conditions' => $conditions, 'recursive' => -1) );
		if($count != 0)
			return false;

		// $this->email_tagsからusers_items_linkテーブルのチェックを行う必要がある
		// TODO:未作成
		return true;
    }

/**
 * リンク識別子に不正な文字列がないかのバリデータ
 *
 * @param  array     $data
 * @return boolean
 * @since   v 3.0.0.0
 */
	public function invalidPermalink($data) {
		if($data['permalink'] == '') {
			return true;
		}
		$permalink = $data['permalink'];
		if(preg_match(NC_PERMALINK_PROHIBITION, $permalink)) {
			return false;
		}

		$mypotal_prefix = NC_SPACE_MYPORTAL_PREFIX;
		if($mypotal_prefix != '') {
			$chk_permalink = $mypotal_prefix.'/'.$permalink;
		} else {
			$chk_permalink = $permalink;
		}
		if(preg_match(NC_PERMALINK_PROHIBITION_DIR_PATTERN, $chk_permalink.'/')) {
			return false;
		}

		$private_prefix = NC_SPACE_PRIVATE_PREFIX;
		if($private_prefix != '') {
			$chk_permalink = $private_prefix.'/'.$permalink;
		} else {
			$chk_permalink = $permalink;
		}
		if(preg_match(NC_PERMALINK_PROHIBITION_DIR_PATTERN, $chk_permalink.'/')) {
			return false;
		}

		return true;
    }

/**
 * パスワードhash化
 * @param   array $options
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function beforeSave($options = array()) {
		if(isset($this->data[$this->alias]['password'])) {
	        $this->data[$this->alias]['password'] = AuthComponent::password($this->data[$this->alias]['password']);
		}
        return true;
    }
/**
 * 最終ログイン日時更新
 * @param   array $user
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function updLastLogin($user) {
		$this->create();
		$this->id = $user[$this->alias]['id'];
		$user[$this->alias]['previous_login'] = $user[$this->alias]['last_login'];
		$user[$this->alias]['last_login'] = $this->nowDate();
		$this->set($user);
		$fields = array('last_login', 'previous_login');

		$ret = $this->save($user, true, $fields);
		if(!$ret) {
			return false;
		}
		return true;
    }

/**
 *
 * マイポータル、マイルームのページ情報から、その会員情報を取得する。
 * マイポータル、マイルーム以外ならば'' エラーならばfalse
 * @param   array $page
 * @param   array $login_user
 * @return  mixed '' or false or $user
 * @since   v 3.0.0.0
 */
    public function currentUser($page, $login_user = null) {
    	$user = array();
    	if($page['Page']['space_type'] != NC_SPACE_TYPE_MYPORTAL && $page['Page']['space_type'] != NC_SPACE_TYPE_PRIVATE) {
    		return '';
    	}

    	$buf_permalink_arr = explode('/', $page['Page']['permalink']);
    	if(!isset($login_user['permalink']) || $buf_permalink_arr[0] != $login_user['permalink']) {
    		$conditions = array('permalink' => $buf_permalink_arr[0]);
    		$user = $this->find( 'first', array('conditions' => $conditions, 'recursive' => -1) );
    	} else {
    		$user['User'] = $login_user;
    	}
    	if(!isset($user['User']))
    		return false;

    	return $user;
    }


/**
 * 参加者情報取得
 * サブグループの場合は、親で参加している会員のみ取得
 *
 * @param   Model Page $page
 * @param   integer  $is_participant_only
 * 						false: 公開
 * 						true : 参加者のみ公開
 * 						null : 親ルームのデータを取得しない(会員参加登録時使用)
 * @param   array    $conditions
 * @param   integer  $start_page
 * @param   integer  $limit
 * @param   string $sortname
 * @param   string   $sortorder
 * @return  array array($total, $pages_users_link)
 * @since   v 3.0.0.0
 */
    public function findParticipant($page, $is_participant_only = true, $conditions = array(), $start_page = 1, $limit= 30, $sortname= 'chief', $sortorder = 'DESC') {

    	$room_id = $page['Page']['id'];
		$fields = array(
			'Page.*',
			'PageUserLink.id',
			'PageUserLink.user_id',
			'PageUserLink.authority_id',
			'User.id',
			'User.handle',
			'User.authority_id',
			'Authority.hierarchy',
			'UserAuthority.hierarchy',
		);
		$type = (($is_participant_only && $page['Page']['thread_num'] <= 1) || is_null($is_participant_only)) ? "INNER" : "LEFT";
		$joins = array(
			array(
				"type" => $type,
				"table" => "page_user_links",
				"alias" => "PageUserLink",
				"conditions" => "`User`.`id`=`PageUserLink`.`user_id`".
				" AND `PageUserLink`.`room_id` =".intval($room_id)
			),
			array(
				"type" => "LEFT",
				"table" => "authorities",
				"alias" => "Authority",
				"conditions" => "`Authority`.id``=`PageUserLink`.`authority_id`"
			),
			array(
				"type" => "INNER",
				"table" => "authorities",
				"alias" => "UserAuthority",
				"conditions" => "`UserAuthority`.id``=`User`.`authority_id`"
			),
			array(
				"type" => "LEFT",
				"table" => "pages",
				"alias" => "Page",
				"conditions" => "`Page`.id``=`PageUserLink`.`room_id`"
			)
		);

		if(!is_null($is_participant_only) && $page['Page']['thread_num'] > 1) {
		//if($page['Page']['id'] != $page['Page']['room_id'] && $page['Page']['thread_num'] > 1) {
			// 親ルームが存在するならば、親ルームの参加者のみ表示
			$fields[] = 'PageUserLinkParent.authority_id';
			$fields[] = 'AuthorityParent.hierarchy';

			App::uses('Page', 'Model');
			$Page = new Page();
			$parent_page = $Page->findById($page['Page']['parent_id']);
			$parent_room_id = $parent_page['Page']['room_id'];
			$type = ($is_participant_only) ? "INNER" : "LEFT";
			$joins[] = array(
				"type" => ($is_participant_only) ? "INNER" : "LEFT",
				"table" => "page_user_links",
				"alias" => "PageUserLinkParent",
				"conditions" => "`User`.`id`=`PageUserLinkParent`.`user_id`".
				" AND `PageUserLinkParent`.`room_id` =".intval($parent_room_id)
			);
			$joins[] = array(
				"type" => "LEFT",
				"table" => "authorities",
				"alias" => "AuthorityParent",
				"conditions" => "`AuthorityParent`.id``=`PageUserLinkParent`.`authority_id`"
			);
		}

		if(empty($conditions)) {
			$conditions = array();
		}

		$total = $this->find('count', array(
			'fields' => 'COUNT(*) as count',
			'joins' => $joins,
			'conditions' => $conditions,
			'recursive' => -1
		));
		if($total == 0) {
			return array(0, array() );
		}

		if($sortname == 'chief') {
			$order = array(
				'Authority.hierarchy' => $sortorder,
				'UserAuthority.hierarchy' => $sortorder,
				'User.handle' => $sortorder,
				'User.id' => $sortorder
			);
			if(isset($parent_room_id)) {
				$order = array(
					'Authority.hierarchy' => $sortorder,
					'AuthorityParent.hierarchy' => $sortorder,
					'UserAuthority.hierarchy' => $sortorder,
					'User.handle' => 'ASC',
					'User.id' => 'ASC'
				);
			} else {
				$order = array(
					'Authority.hierarchy' => $sortorder,
					'UserAuthority.hierarchy' => $sortorder,
					'User.handle' => 'ASC',
					'User.id' => 'ASC'
				);
			}
		} else if(!empty($sortname)) {
			$order = array(
				'User.'.$sortname => $sortorder,
				'User.id' => $sortorder
			);
		}

		$params = array(
			'fields' => $fields,
			'joins' => $joins,
			'conditions' => $conditions,
			'limit' => $limit,
			'page' => $start_page,
			'recursive' => -1,
			'order' => $order
		);

    	return array($total, $this->find('all', $params));
    }
}