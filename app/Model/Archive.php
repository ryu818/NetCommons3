<?php
/**
 * Archiveモデル
 * <pre>
 * 新着、検索用データアーカイブテーブル
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Model
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class Archive extends AppModel
{
	public $name = 'Archive';

	public $actsAs = array('TimeZone');

	public $validate = array();
/**
 * バリデート処理
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
*/
	public function __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);
		$this->validate = array(
			'parent_id' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'allowEmpty' => false,
					'message' => __('The input must be a number.')
				)
			),
			'module_id' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'allowEmpty' => false,
					'message' => __('The input must be a number.')
				)
			),
			'content_id' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'allowEmpty' => false,
					'message' => __('The input must be a number.')
				)
			),
			'unique_id' => array(
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
					'message' => __('The input must be a boolean.')
				),
			),
			'access_hierarchy' => array(
				'inList' => array(
					'rule' => array('inList', array(
						NC_AUTH_OTHER,
						NC_AUTH_MIN_GENERAL,
						NC_AUTH_MIN_MODERATE,
						NC_AUTH_MIN_CHIEF,
					), false),
					'allowEmpty' => true,
					'message' => __('It contains an invalid string.')
				)
			),
			'count' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'allowEmpty' => false,
					'message' => __('The input must be a number.')
				)
			),
			'title' => array(
				'notEmpty'  => array(
					'rule' => array('notEmpty'),
					'last' => true,
					'required' => true,
					'message' => __('Please be sure to input.')
				),
				'maxlength'  => array(
					'rule' => array('maxLength', NC_VALIDATOR_TITLE_LEN),
					'last' => false ,
					'message' => __('The input must be up to %s characters.', NC_VALIDATOR_TITLE_LEN)
				)
			),
			'content' => array(
				'maxLength' => array(
					'rule' => array('maxLength', NC_VALIDATOR_WYSIWYG_LEN),
					'message' => __('The input must be up to %s characters.' , NC_VALIDATOR_WYSIWYG_LEN),
				)
			),
			//search_content 検索文字列をスペース区切りで登録
		);
	}

/**
 * 新着情報の取得処理($model_name, $unique_idで一意に決定)
 * @param  string       $modelName モデル名
 * @param  integer      $uniqueId
 * @return Model Archive
 * @since  v 3.0.0.0
 */
	public function findUnique($modelName, $uniqueId) {
		$fieldList = array(
			'id',
			'parent_id',
			'module_id',
			'content_id',
			'model_name',
			'unique_id',
			'access_hierarchy',
			'count',
			'title',
			'content',
			'search_content',
			'url',
		);
		return $this->find('first', array('field' => $fieldList, 'conditions' => array(
			'model_name' => $modelName,
			'unique_id' => $uniqueId,
		), 'recursive' => -1));
	}

/**
 * 新着情報の削除処理($parentModelName, $parentIdでグループ単位で削除)
 * @param  string       $parentModelName モデル名
 * @param  integer      $parentId
 * @return boolean
 * @since  v 3.0.0.0
 */
	public function deleteParent($parentModelName, $parentId) {
		$conditions = array('parent_model_name' => $parentModelName, 'parent_id' => $parentId);
		return $this->deleteAll($conditions);
	}


/**
 * 新着情報の削除処理($model_name, $unique_idで一意に決定)
 * @param  string       $modelName モデル名
 * @param  integer      $uniqueId
 * @return boolean
 * @since  v 3.0.0.0
 */
	public function deleteUnique($modelName, $uniqueId) {

		// countデクリメント
		$archive = $this->findUnique($modelName, $uniqueId);
		$fields = array(
			$this->alias.'.count' => $this->alias.'.count - 1'
		);
		$conditions = array(
			$this->alias.".parent_model_name" => $archive[$this->alias]['parent_model_name'],
			$this->alias.".parent_id" => $archive[$this->alias]['parent_id'],
		);
		if(!$this->updateAll($fields, $conditions)) {
			return false;
		}

		$conditions = array('model_name' => $modelName, 'unique_id' => $uniqueId);
		return $this->deleteAll($conditions);
	}

/**
 * 新着・検索テーブル追加・更新
 *
 * <pre>
 * model_name, unique_idから同一レコードがあればupdate
 * </pre>
 *
 * @param array Controller->params
 * @param array $data Data to save.
 * @param boolean|array $validate Either a boolean, or an array.
 *   If a boolean, indicates whether or not to validate before saving.
 *   If an array, allows control of validate, callbacks, and fieldList
 * @param array $fieldList List of fields to allow to be written
 * @return mixed On success Model::$data if its not empty or true, false on failure
 * @since  v 3.0.0.0
 */
	public function saveAuto($params, $data = null, $validate = true, $fieldList = array()) {
		if(!isset($data['Archive'])) {
			$data['Archive'] = $data;
		}
		if(!empty($data['Archive']['search_content'])) {
			$data['Archive']['search_content'] = $data['Archive']['content'];
		}
		if(!empty($data['Archive']['url']) && is_array($data['Archive']['url'])) {
			if(!isset($data['Archive']['url']['plugin']) && isset($params['plugin'])) {
				$data['Archive']['url']['plugin'] = $params['plugin'];
			}
			if(!isset($data['Archive']['url']['controller']) && isset($params['controller'])) {
				$data['Archive']['url']['controller'] = $params['controller'];
			}
			if(!isset($data['Archive']['url']['action']) && isset($params['action'])) {
				$data['Archive']['url']['action'] = $params['action'];
			}
			$data['Archive']['url'] = serialize($data['Archive']['url']);
		}
		if(!empty($data['Archive']['content_id'])) {
			// masterのcontent_idで常に登録
			App::uses('Content', 'Model');
			$Content = new Content();
			$content = $Content->findById($data['Archive']['content_id']);
			if(!isset($content['Content'])) {
				return false;
			}
			$data['Archive']['content_id'] = $content['Content']['master_id'];
		}

		$archive = $this->findUnique($data['Archive']['model_name'], $data['Archive']['unique_id']);
		if(isset($archive['Archive'])) {
			$data['Archive'] = array_merge($archive['Archive'], $data['Archive']);
		}
		if(!isset($data['Archive']['parent_model_name'])) {
			$parent_model_name = null;
			$data['Archive']['parent_model_name'] = $data['Archive']['model_name'];
		} else {
			$parent_model_name = $data['Archive']['parent_model_name'];
		}
		if(!isset($data['Archive']['parent_id']) ) {
			$parent_id = null;
			$data['Archive']['parent_id'] = $data['Archive']['unique_id'];
		} else {
			$parent_id = $data['Archive']['parent_id'];
		}

		$ret = $this->save($data, $validate, $fieldList);
		if($ret && !isset($data['Archive']['id']) && isset($parent_model_name) &&
			isset($parent_id) && $parent_id != 0) {
			$archive = $this->findUnique($parent_model_name, $parent_id);
			$count = isset($archive['Archive']) ? intval($archive['Archive']['count']) : 0;
			$fields = array(
				$this->alias.'.count' => $count + 1
			);
			$conditions = array(
				$this->alias.".parent_model_name" => $parent_model_name,
				$this->alias.".parent_id" => $parent_id,
			);
			/*$conditions = array(
				'OR' => array(
					array(
						$this->alias.".model_name" => $parent_model_name,
						$this->alias.".unique_id" => $parent_id,
					),
					array(
						$this->alias.".parent_model_name" => $parent_model_name,
						$this->alias.".parent_id" => $parent_id,
					)
				)
			);*/

			if(!$this->updateAll($fields, $conditions)) {
				return false;
			}
		} else if($ret && isset($archive['Archive']) && $archive['Archive']['parent_id'] == $archive['Archive']['unique_id']) {
			// 親を一時保存、未承認にした場合、子供も更新
			$status = isset($data['Archive']['status']) ? $data['Archive']['status'] : NC_STATUS_PUBLISH;
			$is_approved = isset($data['Archive']['is_approved']) ? $data['Archive']['is_approved'] : _ON;
			if($status != $archive['Archive']['status'] || $is_approved != $archive['Archive']['is_approved']) {
				$fields = array(
					$this->alias.'.status' => $status,
					$this->alias.'.is_approved' => $is_approved,
				);
				$conditions = array(
					$this->alias.".parent_model_name" => $archive['Archive']['parent_model_name'],
					$this->alias.".parent_id" => $archive['Archive']['parent_id'],
				);
				if(!$this->updateAll($fields, $conditions)) {
					return false;
				}
			}
		}
		return $ret;
	}

/**
 * 新着、検索結果の表示
 * @param   string    $type first or all or list
 * @param   array     $addConditions				日付の絞り込み,検索キーワード、ハンドル（検索用）
 * @param   integer   $userId						指定されなければ、ログイン会員
 * @param   array     $roomIdArr					指定したルームのみ,カレントルーム指定
 * @param   array     $moduleIdArr					モジュールの絞り込み
 * @param   string    $lang							言語の絞り込み
 * @param   boolean   $isDisplayComment				コメントの新着を表示するかどうか default:true
 * @param   boolean   $isShowAllCommunity			default:true
 * 						true :公開コミュニティーを含む閲覧可能なすべてのコミュニティー　
 * 						false:参加コミュニティーのみ
 * @param   boolean   $isDisplayAllMyportal			マイポータルすべての新着を表示する。
 * @return  Model Archives
 * @since   v 3.0.0.0
 */
	public function findList($type = 'all', $addConditions = array(), $userId = null, $roomIdArr = null, $moduleIdArr = null, $lang = null, $isDisplayComment = true, $isShowAllCommunity = true, $isDisplayAllMyportal = false) {
		// TODO: マイポータル、コミュニティ、マイルームでの動作を検証すること。
		$ret = array();
		$loginUser = Configure::read(NC_SYSTEM_KEY.'.user');
		if(!isset($userId)) {
			$loginUser = Configure::read(NC_SYSTEM_KEY.'.user');
			$userId = $loginUser['id'];
		}

		$conditions = array(
			'Archive.created <' => $this->nowDate(),
			'Archive.status' => NC_STATUS_PUBLISH,
			'Archive.is_approved' => _ON,
			'OR' => array(
				'Archive.group_id' => 0,
				'GroupLink.id IS NOT NULL',
			)
		);

		App::uses('Page', 'Model');
		$Page = new Page();
		// $isDisplayAllMyportal + ルーム指定の新着はSQLが複雑になりそうなので対処しない。
		if(!$isDisplayAllMyportal) {
			$addParams = array();
			if(isset($roomIdArr)) {
				$addParams = array(
					'conditions' => array(
						'Page.room_id' => $roomIdArr
					)
				);
			}
			$options = array(
				'isShowAllCommunity' => $isShowAllCommunity,
				'isMyPortalSelf' => false,
			);
			$pages = $Page->findViewableRoom('all', $userId, $addParams, $options);

			if(count($pages) == 0) {
				return $ret;
			}
			$hierarchyRooms = array();
			foreach ($pages as $key => $val) {
				$hierarchy = $val['Authority']['hierarchy'];		//$Page->getDefaultHierarchy($val, $userId);
				if($hierarchy >= NC_AUTH_MIN_CHIEF) {
					$hierarchyRooms[NC_AUTH_CHIEF][] = $val['Page']['id'];
				} else if($hierarchy >= NC_AUTH_MIN_MODERATE) {
					$hierarchyRooms[NC_AUTH_MODERATE][] = $val['Page']['id'];
				} else if($hierarchy >= NC_AUTH_MIN_GENERAL) {
					$hierarchyRooms[NC_AUTH_GENERAL][] = $val['Page']['id'];
				} else {
					$hierarchyRooms[NC_AUTH_GUEST][] = $val['Page']['id'];
				}
			}
			$keyCount = count($conditions);
			if(isset($hierarchyRooms[NC_AUTH_CHIEF])) {
				$conditions[$keyCount]['OR'][] = array(
					"Content.room_id" => $hierarchyRooms[NC_AUTH_CHIEF],
					"Archive.access_hierarchy <=" => NC_AUTH_CHIEF
				);
			}
			if(isset($hierarchyRooms[NC_AUTH_MODERATE])) {
				$conditions[$keyCount]['OR'][] = array(
					"Content.room_id" => $hierarchyRooms[NC_AUTH_MODERATE],
					"Archive.access_hierarchy <=" => NC_AUTH_MODERATE
				);
			}
			if(isset($hierarchyRooms[NC_AUTH_GENERAL])) {
				$conditions[$keyCount]['OR'][] = array(
					"Content.room_id" => $hierarchyRooms[NC_AUTH_GENERAL],
					"Archive.access_hierarchy <=" => NC_AUTH_GENERAL
				);
			}
			if(isset($hierarchyRooms[NC_AUTH_GUEST])) {
				$conditions[$keyCount]['OR'][] = array(
					"Content.room_id" => $hierarchyRooms[NC_AUTH_GUEST]
				);
			}
		}

		if(!$isDisplayComment) {
			$conditions[] = array(
				"`Archive`.`parent_model_name` = `Archive`.`model_name`",
				"`Archive`.`parent_id` = `Archive`.`unique_id`",
			);
		} else {
			// サブクエリでparent_model_name,parent_id毎のcreatedのMAXのデータを取得
			// 記事の日時とコメントの日時が同じになった場合、記事とコメントの2レコード表示される。
			$conditionsSubQuery = array(
				"`Archive`.`parent_model_name` = `Archive2`.`parent_model_name`",
				"`Archive`.`parent_id` = `Archive2`.`parent_id`",
			);

			$db = $this->getDataSource();
			$subQuery = $db->buildStatement(
			array(
				'fields'     => array('MAX(`Archive2`.`created`)'),
				'table'      => $db->fullTableName($this),
				'alias'      => 'Archive2',
				'limit'      => null,
				'offset'     => null,
				'joins'      => array(),
				'conditions' => $conditionsSubQuery,
				'order'      => null,
				'group'      => null
			),
			$this
			);
			$subQuery = ' `Archive`.`created` = (' . $subQuery . ') ';
			$conditions[] = $db->expression($subQuery);
		}
		if(count($moduleIdArr) > 0) {
			$conditions[] = array(
				"`Archive`.`module_id`" => $moduleIdArr
			);
		}
		if(count($addConditions) > 0) {
			$conditions = array_merge($conditions, $addConditions);
		}

		$params = array(
			'fields' => array('Archive.*', 'Block.id', 'Page.permalink', 'Page.space_type'),
			'conditions' => $conditions,
			'joins' => array(
				array(
					"type" => "INNER",
					"table" => "contents",
					"alias" => "Content",
					"conditions" => "`Archive`.`content_id`=`Content`.`master_id`".
					" AND `Content`.`display_flag`=".NC_DISPLAY_FLAG_ON
				),
				array(
					"type" => "INNER",
					"table" => "blocks",
					"alias" => "Block",
					"conditions" => "`Content`.`id`=`Block`.`content_id`".
						" AND `Block`.`display_flag`=".NC_DISPLAY_FLAG_ON
				),
				array(
					"type" => "INNER",
					"table" => "pages",
					"alias" => "Page",
					"conditions" => "`Page`.`id`=`Block`.`page_id`".
						" AND `Page`.`display_flag`=".NC_DISPLAY_FLAG_ON
				),
				array(
					"type" => "LEFT",
					"table" => "group_links",
					"alias" => "GroupLink",
					"conditions" => "`Archive`.`group_id`=`GroupLink`.`group_id`".
						" AND `GroupLink`.`created`=".$userId
				),
			),
			'group' => array('Archive.module_id', 'Archive.parent_model_name', 'Archive.parent_id'),
			'order' => array('Archive.created' => 'DESC', 'Archive.id' => 'DESC')
		);
		if(isset($lang)) {
			$params['joins'][2]['conditions'] = array(
				"`Page`.`id`=`Block`.`page_id`",
				"`Page`.`display_flag`" => NC_DISPLAY_FLAG_ON,
				"`Page`.`lang`" => array('', $lang),
			);
		}
		// マイポータルすべての新着
		if($isDisplayAllMyportal) {
			$params['joins'][] = array(
				"type" => "INNER",
				"table" => "users",
				"alias" => "User",
				"conditions" => "`Page`.`permalink`=`User`.`permalink`".
					" AND `User`.`is_active`="._ON
			);
			$params['joins'][] = array(
				"type" => "INNER",
				"table" => "authorities",
				"alias" => "Authority",
				"conditions" => "`User`.`authority_id`=`Authority`.`id`".
					" AND (`Authority`.`myportal_use_flag`=".NC_MYPORTAL_USE_ALL.
						" OR (`Authority`.`myportal_use_flag`=".NC_MYPORTAL_MEMBERS.
							" AND `Authority`.`allow_myportal_viewing_hierarchy` <= ".$loginUser['hierarchy']."))"
			);
			// TODO:マイポータルの絞り込みは後に対応？
		}

		return $this->find($type, $params);
	}

/**
 * 承認ステータスの更新
 *
 * @param  string       $modelName モデル名
 * @param  integer     $uniqueId
 * @param   boolean $isApproved 更新する承認ステータス
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function updateApprove($modelName, $uniqueId, $isApproved = NC_APPROVED_FLAG_ON) {
		$fieldList = array(
			'id',
			'model_name',
			'unique_id',
			'is_approved'
		);

		$archive= $this->find('first', array('field' => $fieldList, 'conditions' => array(
			'model_name' => $modelName,
			'unique_id' => $uniqueId,
		), 'recursive' => -1));

		if(!isset($archive['Archive'])) {
			return false;
		}
		if($archive['Archive']['is_approved'] == $isApproved) {
			return true;
		}

		$archive['Archive']['is_approved'] = $isApproved;
		if(!$this->save($archive, true, $fieldList)) {
			return false;
		}
		return true;
	}

}