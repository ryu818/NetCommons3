<?php
/**
 * PageTreeモデル
 *
 * <pre>
 *  ページの階層情報を制御する
 *  このmodelはModel/Pageもしくはそれに類するModel Classから呼び出されて使われることを想定しているため
 *  このClass内ではトランザクション処理は行なっていません。
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Model
 * @author        Noriko Arai,Takako Miyagawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */

class PageTree extends AppModel {

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
	}

/**
 * 新規ページの追加時に必要なinsert処理
 * @param   $page_id
 * @param   $parent_id
 * @return  bool
 * @since   v 3.0.0.0
 */
	public function addPage($pageId, $parentId) {
		//型チェック
		if (! is_numeric($pageId) || ! is_numeric($parentId)) {
			return false;
		}

		//先祖一覧
		$treeList = $this->getParentAll($parentId);
		$ins = $this->__createAddArray($pageId, $treeList);
		//先祖なし...はありえない。必ずspace_type別の、meta 真祖の子供になる。
		if (! $ins) {
			//保存すべき情報がない。
			return false;
		}

		//このメソッドを使うPageの追加処理のところでトランザクション処理必要。
		//insert処理
		foreach ($ins as $item) {
			$this->create();
			if (! $this->save($item)) {
				return false;
			}
		}
		return true;
	}

/**
 * ページを新規登録するときに、insertする用の配列を作る。
 * @param   $page_id
 * @param   $list
 * @return  array
 * @since   v 3.0.0.0
 */
	private function __createAddArray($pageId, $treeList) {
		//pageが数字じゃないor $listが配列じゃない。
		if (! is_numeric($pageId)
			|| ! is_array($treeList)
			|| ! $treeList //空 1件もない状態はない。
		) {
			return array();
		}

		$ins = array();
		$con = 0;
		//親からみた情報をつくる。
		foreach ($treeList as $item) {
			$ins[$con]['parent_id'] = $item['PageTree']['parent_id'];
			$ins[$con]['child_id'] = $pageId;
			$ins[$con]['stratum_num'] = $item['PageTree']['stratum_num'] + 1;
			$con ++;
		}
		//自身が先祖のレコードを追加する
		$con ++;
		$ins[$con]['parent_id'] = $pageId;
		$ins[$con]['child_id'] = $pageId;
		$ins[$con]['stratum_num'] = 0;

		return $ins;
	}

/**
 * ページの削除
 *
 * <pre>
 * トランザクションはこれを利用するModel内で実装されるべきなのでここではトランザクションをしていません。
 * 成功した場合はtrue,失敗した場合はfalseが戻ります。
 * </pre>
 * @param  int $pageId
 * @return bool
 * @since  v 3.0.0.0
 */
	public function deletePage($pageId) {
		//パラメータチェック
		if (! is_numeric($pageId) || ! $pageId) {
			return false;
		}

		//すぐ上の親を調べる
		$parentId = $this->getParentOneId($pageId);
		//直親のいない子は削除できない。メタデータだから。
		if (! $parentId) {
			return false;
		}

		//子のid情報を取得
		$childList = $this->__childIdsArray($this->getChildAll($pageId));
		//移動するノード以下の親子情報を取得
		$treeList = $this->find('all',
			array(
					'conditions' => array(
					'PageTree.child_id' => $childList,
					'PageTree.stratum_num' => 1,
					'PageTree.child_id != ' . $pageId
				),
				'order' => array('PageTree.stratum_num ASC')
			)
		);

		//再構築のため、一旦ノードごと削除する
		if (! $this->deleteNode($pageId)) {
			return false;
		}

		//自身が親になっている場合のみ、$parent_idに差し替えて再構築する。
		//再構築
		foreach ($treeList as $item) {
			if (isset($item['PageTree'])
				&& isset($item['PageTree']['child_id'])
				&& isset($item['PageTree']['parent_id']) ) {
				//parent_idがPageIdだった場合は、親の付け替えをする。
				if ($item['PageTree']['parent_id'] == $pageId) {
					$item['PageTree']['parent_id'] = $parentId;
				}
				$ck = $this->addPage($item['PageTree']['child_id'], $item['PageTree']['parent_id']);
				if (! $ck) {
					return false;
				}
			}
		}
		return true;
	}

/**
 * 直近の親のidを取得する
 * @param   int $pageId
 * @return  null or int
 * @since   v 3.0.0.0
 */
	public function getParentOneId($pageId) {
		//パラメータチェック
		if (! is_numeric($pageId) || ! $pageId) {
			return null;
		}
		$treeList = $this->find(
			'list',
			array(
				'conditions' => array(
					'PageTree.child_id' => $pageId,
					'PageTree.stratum_num' => 1,
				),
				'fields' => array(
					'PageTree.parent_id'
				)
			)
		);
		$ids = array_values($treeList);
		if (isset($ids[0])) {
			return $ids[0];
		}
		return null;
	}

/**
 * pageTreeの一覧（配列）から、子idだけを抜き出した配列を返す
 * @param   array $pageTree
 * @return  array
 * @since   v 3.0.0.0
 */
	private function __childIdsArray($pageTree) {
		//パラメータのチェック
		if (! $pageTree || ! is_array($pageTree)) {
			return array();
		}
		$result = array();
		foreach ($pageTree as $item) {
			if (isset($item['PageTree']) && isset($item['PageTree']['child_id'])) {
				$result[] = $item['PageTree']['child_id'];
			}
		}
		return $result;
	}

/**
 * ノードごと削除（子孫も一緒に削除）
 * @param   int $pageId
 * @return  bool
 * @since   v 3.0.0.0
 */
	public function deleteNode($pageId) {
		//パラメータチェック
		if (! is_numeric($pageId) || ! $pageId) {
			return false;
		}
		//自身も含む子のidの情報が削除できればよい
		$pageIds = $this->__childIdsArray($this->getChildAll($pageId));
		$ck = $this->deleteAll(array('PageTree.child_id' => $pageIds));
		if (! $ck) {
			return false;
		}
		//無事完了
		return true;
	}

/**
 * ページのノードの移動
 *
 * @param   int $pageId
 * @param   int $newParentId
 * @return  bool
 * @since   v 3.0.0.0
 */
	public function moveNode($pageId, $newParentId) {
		//子のid情報を取得
		$childList = $this->__childIdsArray($this->getChildAll($pageId));
		//移動するノード以下の親子情報を取得
		$treeList = $this->find('all',
			array(
				'conditions' => array(
						'PageTree.child_id' => $childList,
						'PageTree.stratum_num' => 1,
						'PageTree.child_id != ' . $pageId
					),
					'order' => array('PageTree.stratum_num ASC')
				)
			);
		//再構築のため、一旦ノードを削除する
		if (! $this->deleteNode($pageId)) {
			return false;
		}

		//新しい親に追加
		if (! $this->addPage($pageId, $newParentId)) {
			return false;
		}

		//再構築
		foreach ($treeList as $item) {
			if (isset($item['PageTree'])
				&& isset($item['PageTree']['child_id'])
				&& isset($item['PageTree']['parent_id']) ) {
					$ck = $this->addPage($item['PageTree']['child_id'], $item['PageTree']['parent_id']);
					if (! $ck) {
						return false;
					}
			}
		}
		//すべて無事完了
		return true;
	}

/**
 * 祖先情報の取得
 * 自身のデータまで取得する。
 * @param   int$page_id
 * @return  array
 * @since   v 3.0.0.0
 */
	public function getParentAll($pageId) {
		//パラメータチェック
		if (! is_numeric($pageId) || ! $pageId) {
			return array();
		}
		//指定されたidが子になる一覧
		$ck = $this->find(
			'all',
			array('conditions' => array('child_id' => $pageId))
		);

		return $ck;
	}

/**
 *  子孫一覧（全件）
 * @param   int$pageId
 * @return  array
 * @since   v 3.0.0.0
 */
	public function getChildAll($pageId) {
		//パラメータチェック
		if (! $pageId || ! is_numeric($pageId)) {
			return array();
		}

		$ck = $this->find('all',
			array(
				'conditions' => array(
					'PageTree.parent_id' => $pageId,
				),
				'order' => array(
					'PageTree.stratum_num ASC' //改装の浅い順
				)
			));
		return $ck;
	}
}