<?php
/**
 * Htmlareaモデル
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Model
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class Htmlarea extends AppModel
{
	public $name = 'Htmlarea';

	public $validate = array();

	public function __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);
		$this->validate = array(
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

	public function beforeValidate($options = array()) {
		if(isset($this->data['Htmlarea']['content']) && preg_match('/^\s*<div><\/div>\s*$/iu', $this->data['Htmlarea']['content']) || preg_match('/^\s*<br\s*\/?>\s*$/iu', $this->data['Htmlarea']['content'])) {
			$this->data['Htmlarea']['content'] = "";
		}
		//$this->data['Htmlarea']['content'] = $this->cleanHTML($this->data['Htmlarea']['content']);
		return true;
	}


/**
 * 履歴更新処理
 * @param   array $options
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function afterSave($created) {
		if ($created) {
			// insert
			if(isset($this->data[$this->alias]['revision_parent']) && $this->data[$this->alias]['revision_parent'] > 0) {
				$id = $this->id;
				$revision_parent = intval($this->data[$this->alias]['revision_parent']);

				$fields = array(
					$this->alias.'.revision_parent' => $this->id
				);

				$conditions = array(
					array('OR' => array(
						$this->alias.".id" => $revision_parent,
						$this->alias.".revision_parent" => $revision_parent
					))
				);
				$this->updateAll($fields, $conditions);

				// revision_parentを0に最更新
				$fields = array(
					$this->alias.'.revision_parent' => 0
				);

				$conditions = array(
					$this->alias.".id" => $this->id
				);
				$this->updateAll($fields, $conditions);

				$htmlarea = $this->findById($revision_parent);
				if($htmlarea[$this->alias]['content'] == $this->data[$this->alias]['content']) {
					// コンテンツ変更なし。1つ前のヒストリー削除
					$this->delete($revision_parent);
					$this->id = $id;
					return;
				}

				// 履歴の保存最大個数を超えた場合、古いものから削除
				$params = array(
					'conditions' => array(
						$this->alias.'.revision_parent' => $this->id
					),
					'offset' => NC_REVISION_RETENTION_NUMBER,
					'limit'  => 10,								// 10行単位
					'order'  => array('id' => 'DESC')
				);

				$htmlarea = $this->find('list', $params);
				if(count($htmlarea) > 0) {
					$this->delete($htmlarea);
				}

				$this->id = $id;
			}
		}
	}

}