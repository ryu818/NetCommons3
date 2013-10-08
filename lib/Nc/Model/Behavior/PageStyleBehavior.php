<?php
/**
 * PageStyle Behavior
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Model.Behavior
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class PageStyleBehavior extends ModelBehavior {

/**
 * バリデート処理
 * @param   Model   $Model
 * @return  void
 * @since   v 3.0.0.0
 */
	public function constructDefault(Model $Model) {
		return  array(
			'scope' => array(
				'inList' => array(
					'rule' => array('inList', array(
						NC_PAGE_SCOPE_SITE,
						NC_PAGE_SCOPE_SPACE,
						NC_PAGE_SCOPE_ROOM,
						NC_PAGE_SCOPE_NODE,
						NC_PAGE_SCOPE_CURRENT,
					), false),
					'allowEmpty' => false,
					'message' => __('It contains an invalid string.')
				),
			),
			// lang
			'space_type' => array(
				'inList' => array(
					'rule' => array('inList', array(
						_OFF,
						NC_SPACE_TYPE_PUBLIC,
						NC_SPACE_TYPE_MYPORTAL,
						NC_SPACE_TYPE_PRIVATE,
						NC_SPACE_TYPE_GROUP,
					), false),
					'allowEmpty' => true,
					'message' => __('It contains an invalid string.')
				),
			),
			'page_id' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'message' => __('The input must be a number.')
				),
			),
		);
	}

/**
 * 適用範囲内ページスタイルデータ取得
 * @param   Model   $Model
 * @param   string $type
 * @param   Model page $page
 * @param   integer $nodeID
 * @return  array $pageStyle[pageStyle.type] = Model PageStyle
 * @since   v 3.0.0.0
 */
	public function findScope(Model $Model, $type = 'all', $page , $nodeID = null) {
		$Page = ClassRegistry::init('Page');
		$lang = Configure::read(NC_CONFIG_KEY.'.'.'language');
		if(empty($nodeId)) {
			$isNodeColumn = 'is_' . Inflector::underscore($Model->name) . '_node';
			$Page = ClassRegistry::init('Page');
			$rets = $Page->findNodeFlag($page, array($isNodeColumn => _OFF));
			$nodeId = $rets[$isNodeColumn];
		}
		
		$conditions = array(
			'or' => array(
				array(
					$Model->alias.'.scope' => NC_PAGE_SCOPE_SITE
				),
				array(
					$Model->alias.'.scope' => NC_PAGE_SCOPE_SPACE,
					$Model->alias.'.space_type' => array(0, $page['Page']['space_type']),
				),
				array(
					$Model->alias.'.scope' => NC_PAGE_SCOPE_CURRENT,
					$Model->alias.'.page_id' => $page['Page']['id'],
				),
			),
			$Model->alias.'.lang' => array('', $lang),
		);
		if(!empty($nodeId)) {
			$conditions['or'][] = array(
				$Model->alias.'.scope' => array(NC_PAGE_SCOPE_NODE, NC_PAGE_SCOPE_ROOM),
				$Model->alias.'.page_id' => $nodeId,
			);
		}
		$params = array(
			//'fields' => array($Model->alias.'.*'),
			'conditions' => $conditions,
			'order' => array($Model->alias.'.scope' => 'DESC', $Model->alias.'.lang' => 'DESC'),		// , 'Page.thread_num' => 'DESC'
		);
		return $Model->find($type, $params);
	}

/**
 * ページスタイル関連 登録処理
 * @param   Model   $Model
 * @param   array $pageStyles
 * @param   array $centerPage
 * @param   array  $data
 * @param   string $type
 * @return  void
 * @since   v 3.0.0.0
 */
	public function saveScope(Model $Model, $pageStyles, $centerPage, $data, $type = _OFF) {
		if ($data['type'] == 'reset' && isset($pageStyles[0][$Model->alias])) {
			// リセット処理
			if(!$Model->delete($pageStyles[0][$Model->alias]['id'])) {
				throw new InternalErrorException(__('Failed to delete the database, (%s).', 'page_styles'));
			}
		} else if ($data['type'] == 'submit' &&
				isset($data[$Model->alias]['scope'])) {

			// 削除処理
			$savePageStyle = $data;
			$savePageStyle[$Model->alias]['type'] = $type;
			if($savePageStyle[$Model->alias]['lang'] == 'All') {
				$savePageStyle[$Model->alias]['lang'] = '';
			}

			if($savePageStyle[$Model->alias]['scope'] == NC_PAGE_SCOPE_SITE) {
				$savePageStyle[$Model->alias]['space_type'] = _OFF;
			} else {
				$savePageStyle[$Model->alias]['space_type'] = $centerPage['Page']['space_type'];
			}
			if(in_array($savePageStyle[$Model->alias]['scope'], array(NC_PAGE_SCOPE_SITE, NC_PAGE_SCOPE_SPACE)) ) {
				$savePageStyle[$Model->alias]['page_id'] = _OFF;
			} else if($savePageStyle[$Model->alias]['scope'] == NC_PAGE_SCOPE_ROOM) {
				$savePageStyle[$Model->alias]['page_id'] = $centerPage['Page']['room_id'];
			} else {
				$savePageStyle[$Model->alias]['page_id'] = $centerPage['Page']['id'];
			}
			$Model->set($savePageStyle);
			if($Model->validates()) {
				$id = null;
				if(count($pageStyles) > 0) {
					// 現在、設定中のものより優先順位が高いものが既に登録してあったら、削除。
					foreach($pageStyles as $pageStyle) {
						if($pageStyle[$Model->alias]['scope'] == $savePageStyle[$Model->alias]['scope'] &&
							$pageStyle[$Model->alias]['type'] == $savePageStyle[$Model->alias]['type'] &&
							$pageStyle[$Model->alias]['space_type'] == $savePageStyle[$Model->alias]['space_type'] &&
							$pageStyle[$Model->alias]['lang'] == $savePageStyle[$Model->alias]['lang'] &&
							$pageStyle[$Model->alias]['page_id'] == $savePageStyle[$Model->alias]['page_id']) {
							$id = $pageStyle[$Model->alias]['id'];
						} else if($pageStyle[$Model->alias]['scope'] > $savePageStyle[$Model->alias]['scope']) {
							if(!$Model->delete($pageStyle[$Model->alias]['id'])) {
								return false;
							}
						}
					}
				}
				
				// 登録処理
				$savePageStyle[$Model->alias]['id'] = $id;
				if(!$Model->save($savePageStyle)) {
					return false;
				}
			}
			$pageStyles[0] = $savePageStyle;
		}
		return $pageStyles;
	}

/**
 * 登録後処理
 * 		scope: NC_PAGE_SCOPE_NODE かつ、page_idが入っていればPage.is_XXXX_nodeを更新
 * @param   Model   $Model
 * @param   boolean $created
 * @return  void
 * @since   v 3.0.0.0
 */
	public function afterSave(Model $Model, $created) {
		$isNodeColumn = 'is_' . Inflector::underscore($Model->name) . '_node';
		if ($created && !empty($Model->data[$Model->alias]['page_id'])) {
			if(in_array($Model->data[$Model->alias]['scope'], array(NC_PAGE_SCOPE_NODE, NC_PAGE_SCOPE_ROOM))) {
				$Page = ClassRegistry::init('Page');
				$fields = array($Page->alias.'.'.$isNodeColumn=> _ON);
				$conditions = array(
					$Page->alias.".id" => $Model->data[$Model->alias]['page_id'],
				);
				$Page->updateAll($fields, $conditions);
			}
		}
	}

/**
 * 削除前処理：ファイル削除
 * page_idが入っていればPage.is_XXXX_nodeを更新
 * @param   Model   $Model
 * @param   boolean $cascade
 * @return  void
 * @since   v 3.0.0.0
 */
	public function beforeDelete(Model $Model, $cascade = true) {
		$isNodeColumn = 'is_' . Inflector::underscore($Model->name) . '_node';
		$pageStyle = $Model->findById($Model->id);
		if(isset($pageStyle[$Model->alias])) {
			if(isset($pageStyle[$Model->alias]['file'])) {
				$Model->deleteCssFile($pageStyle[$Model->alias]['file']);
			}
			// page_idが入っていればPage.page_style_idを更新
			if(!empty($pageStyle[$Model->alias]['page_id']) && in_array($pageStyle[$Model->alias]['scope'], array(NC_PAGE_SCOPE_NODE, NC_PAGE_SCOPE_ROOM))) {
				$Page = ClassRegistry::init('Page');
				$fields = array($Page->alias.'.'.$isNodeColumn=> _OFF);
				$conditions = array(
					$Page->alias.".id" => $pageStyle[$Model->alias]['page_id'],
				);
				$Page->updateAll($fields, $conditions);
			}
		}
	}
}