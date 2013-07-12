<?php
/**
 * Revisionモデル
 *
 * group_idカラムに再編集時のRevision.idをセットし、毎回Insertすることで履歴機能を実現
 * （NC_REVISION_RETENTION_NUMBERを越えたデータが、古いものから10行ずつ削除している）
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Model
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class Revision extends AppModel
{
	public $validate = array();

	public $actsAs = array('TimeZone');

	public $order = array('Revision.created' => 'DESC', 'Revision.id' => 'DESC');

/**
 * バリデート処理
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);
		$this->validate = array(
			'group_id' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'allowEmpty' => false,
					'message' => __('The input must be a number.')
				)
			),
			'revision_name' => array(
				'inList' => array(
					'rule' => array('inList', array(
						'publish',
						'draft',
						'auto-draft',
						// 'pending',
						// 'future',
					)),
					'allowEmpty' => false,
					'message' => __('It contains an invalid string.')
				)
			),
			'pointer' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'message' => __('The input must be a boolean.')
				)
			),
			'is_approved_pointer' => array(
				'boolean'  => array(
					'rule' => array('boolean'),
					'message' => __('The input must be a boolean.')
				)
			),
			'content_id' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'allowEmpty' => false,
					'message' => __('The input must be a number.')
				)
			),
			'content' => array(
				'notEmpty'  => array(
					'rule' => array('notEmpty'),
					'last' => true,
					'message' => __('Please be sure to input.',true)
				),
				'maxLength' => array(
					'rule' => array('maxLength', NC_VALIDATOR_WYSIWYG_LEN),
					'message' => __('The input must be up to %s characters.' , NC_VALIDATOR_WYSIWYG_LEN),
				)
			)
		);
	}

/**
 * バリデート前処理
 * @param   array $options
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function beforeValidate($options = array()) {
		if(isset($this->data[$this->alias]['content']) && (preg_match('/^\s*<div><\/div>\s*$/iu', $this->data[$this->alias]['content']) || preg_match('/^\s*<br\s*\/?>\s*$/iu', $this->data[$this->alias]['content']))) {
			$this->data[$this->alias]['content'] = "";
		}
		//$this->data[$this->alias]['content'] = $this->cleanHTML($this->data[$this->alias]['content']);
		return true;
	}

/**
 * 履歴更新前処理
 * 		自動保存以外のコンテンツ変更ない履歴は、追加しない。
 * @param   boolean $created
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function beforeSave($options = array()) {
		if($this->data[$this->alias]['group_id'] != 0 && $this->data[$this->alias]['revision_name'] != 'auto-draft') {
			$revisions = $this->findRevisions(null, $this->data[$this->alias]['group_id'], 1);
			if(isset($revisions[0]) && $revisions[0][$this->alias]['revision_name']  != 'auto-draft'
				&& $revisions[0][$this->alias]['content'] == $this->data[$this->alias]['content'] &&
				$revisions[0][$this->alias]['pointer'] == $this->data[$this->alias]['pointer']) {
				// コンテンツ変更なし。
				if($revisions[0][$this->alias]['revision_name'] != $this->data[$this->alias]['revision_name'] ||
					$revisions[0][$this->alias]['is_approved_pointer'] != $this->data[$this->alias]['is_approved_pointer']) {
					$fields = array(
						$this->alias.'.revision_name' => "'" .$this->data[$this->alias]['revision_name']."'",
						$this->alias.'.is_approved_pointer' => $this->data[$this->alias]['is_approved_pointer']
					);
					$conditions = array(
						$this->alias.".id" => $revisions[0][$this->alias]['id']
					);
					$this->updateAll($fields, $conditions);
				}
				$this->id = $revisions[0][$this->alias]['id'];
				return false;
			}
		}
		return true;
	}

/**
 * 履歴更新後処理
 * 		・group_idが0ならば、$this->idで更新
 * 		・自動保存でNC_REVISION_AUTO_DRAFT_GC_LIFETIMEより古いものを削除
 * @param   boolean $created
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function afterSave($created) {
		if ($created) {
			if($this->data[$this->alias]['group_id'] == 0) {
				// group_idが0ならば、$this->idで更新
				$fields = array(
					$this->alias.'.group_id' => $this->id
				);
				$conditions = array(
					$this->alias.".id" => $this->id
				);
				$this->updateAll($fields, $conditions);
				$this->data[$this->alias]['group_id'] = $this->id;
			} else if($this->data[$this->alias]['pointer'] == _ON) {
				$fields = array(
					$this->alias.'.pointer' => _OFF
				);
				$conditions = array(
					$this->alias.".group_id" => $this->data[$this->alias]['group_id'],
					$this->alias.".id !=" => $this->id
				);
				$this->updateAll($fields, $conditions);
			}

			// 自動保存でNC_REVISION_AUTO_DRAFT_GC_LIFETIMEより古いものを削除
			// TODO:現状：今後、システム管理等で自動保存のデータの有効期限を設定できたほうがよい？
			// TODO:現状コメント（自動保存の削除も行っているため）
			/*
			$rmTime = strtotime($this->nowDate("Y-m-d H:i:s")) - NC_REVISION_AUTO_DRAFT_GC_LIFETIME;
			$conditions = array(
				$this->alias.'.group_id' => 'auto-draft',
				$this->alias.'.pointer' => _OFF,
				$this->alias.".created <" => date("Y-m-d H:i:s", $rmTime)
			);
			$this->deleteAll($conditions);
			*/

			// 履歴の保存最大個数を超えた場合、古いものから削除。ただし、自動保存は含めない。
			$params = array(
				'conditions' => array(
					$this->alias.'.group_id' => $this->data[$this->alias]['group_id'],
					$this->alias.'.pointer' => _OFF,
					$this->alias.'.revision_name !=' => 'auto-draft',
				),
				'offset' => NC_REVISION_RETENTION_NUMBER,
				'limit'  => 10,								// 10行単位
				'order'  => array('id' => 'DESC')
			);

			$revision = $this->find('list', $params);
			if(count($revision) > 0) {
				$this->delete($revision);
			}

			// 自動保存（auto-draft）による履歴情報の削除で、1人につきNC_REVISION_RETENTION_NUMBER件までとする
			$user = Configure::read(NC_SYSTEM_KEY.'.user');
			$params = array(
				'conditions' => array(
					$this->alias.'.pointer' => _OFF,
					$this->alias.'.revision_name' => 'auto-draft',
					$this->alias.'.created_user_id' => $user['id'],
				),
				'offset' => NC_REVISION_RETENTION_NUMBER,
				'limit'  => 10,								// 10行単位
				'order'  => array('id' => 'DESC')
			);

			$revision = $this->find('list', $params);
			if(count($revision) > 0) {
				$this->delete($revision);
			}
		}
	}

/**
 * 履歴リスト取得処理
 * @param   integer $id
 * @param   boolean|integer $groupId セットすれば現在のリビジョンを含めたすべてのリスト取得
 * @param   integer $limit
 * @param   $outsideAutoDraft true 自動保存以外
 * @return  Model Revisions
 * @since   v 3.0.0.0
 */
	public function findRevisions($id, $groupId = false, $limit = NC_REVISION_SHOW_LIMIT, $outsideAutoDraft = false) {
		$user = Configure::read(NC_SYSTEM_KEY.'.user');
		$userId = $user['id'];
		$revisions = array();

		if(!$groupId) {
			$current_revision = $this->findById($id);
			if(!isset($current_revision[$this->alias])) {
				return $revisions;
			}
			$currentGroupId = $current_revision[$this->alias]['group_id'];
		} else {
			$currentGroupId = $groupId;
		}

		if($id != 0 || $groupId > 0) {
			if($groupId) {
				if($outsideAutoDraft) {
					$conditions = array(
						'group_id' => $currentGroupId,
						'revision_name !=' => 'auto-draft',
					);
				} else {
					$conditions = array(
						'group_id' => $currentGroupId,
						'or' => array(
							'created_user_id' => $userId,
							'revision_name !=' => 'auto-draft',
						)
					);
				}
			} else {
				if($outsideAutoDraft) {
					$conditions = array(
						'id !=' => $id,
						'group_id' => $currentGroupId,
						'revision_name !=' => 'auto-draft',
					);
				} else {
					$conditions = array(
						'id !=' => $id,
						'group_id' => $currentGroupId,
						'or' => array(
							'created_user_id' => $userId,
							'revision_name !=' => 'auto-draft',
						)
					);
				}
			}
			$params = array(
				'conditions' => $conditions,
				'order' => array($this->alias.'.created' => 'DESC', $this->alias.'.id' => 'DESC'),
				'limit' => $limit,
			);
			$revisions = $this->find('all', $params);
		}
		return $revisions;
	}

/**
 * 履歴リスト削除処理
 * @param   integer $groupId
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function deleteRevison($groupId) {
		$delConditions = array($this->alias.'.group_id' => $groupId);
		if(!$this->deleteAll($delConditions)){
			return false;
		}
		return true;
	}
}