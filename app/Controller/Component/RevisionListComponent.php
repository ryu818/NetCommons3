<?php
/**
 * RevisionListComponentクラス
 *
 * <pre>
 * リビジョンの復元処理
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       App.Controllers.Components
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class RevisionListComponent extends Component {
/**
 * Controller
 *
 * @var     object
 */
	protected $_controller = null;

/**
 * Constructor
 *
 * @param ComponentCollection $collection A ComponentCollection this component can use to lazy load its components
 * @param array $settings Array of configuration settings.
 */
	public function __construct(ComponentCollection $collection, $settings = array()) {
		$this->_controller = $collection->getController();
		parent::__construct($collection, $settings);
	}

/**
 * リビジョン記事投稿処理の初期データ取得処理
 * @param   integer $id 投稿ID
 * @return  array('id', 'isAutoRegist', 'status', 'revision_name')		// statusのみnullならば、編集データのstatusを維持する。
 * @since   v 3.0.0.0
 */
	public function beforeAutoRegist($id) {
		$isAutoRegist = false;
		if(isset($this->_controller->request->data['AutoRegist']['on']) && $this->_controller->request->data['AutoRegist']['on']) {
			$isAutoRegist = true;
		}
		if(empty($id) && isset($this->_controller->request->data['AutoRegist']['post_id']) && $this->_controller->request->data['AutoRegist']['post_id']) {
			// 新規投稿で自動保存の2回目以降は$post_idがセットされないためセット
			$id = $this->_controller->request->data['AutoRegist']['post_id'];
		}

		if(empty($id) && $isAutoRegist) {
			// 自動保存で新規登録時は一時保存
			$isTemporally = _ON;
		}
		if(!isset($isTemporally)) {
			if(!isset($this->_controller->request->data['AutoRegist']['status']) || $this->_controller->request->data['AutoRegist']['status'] == _OFF) {
				$isTemporally = _OFF;
			} else {
				$isTemporally = _ON;
			}
		}

		$status = null;
		if(empty($id) || !$isAutoRegist) {
			// 自動保存で編集時はstatusを維持
			$status = ($isTemporally) ? NC_STATUS_TEMPORARY : NC_STATUS_PUBLISH;
		}

		$revision_name = ($isAutoRegist) ? 'auto-draft' : (($isTemporally) ? 'draft' : 'publish');

		return array(
			'id' => $id,
			'isAutoRegist'=> $isAutoRegist,
			'status'=> $status,
			'revision_name'=> $revision_name
		);
	}

/**
 * リビジョン記事投稿処理の後処理(Render)
 * @param   integer $id 投稿ID
 * @return  void
 * @since   v 3.0.0.0
 */
	public function afterAutoRegist($id) {
		echo $id;
		$this->_controller->render(false);
	}

/**
 * 履歴情報画面表示 リビジョン比較画面表示・復元処理
 * @param   string  $title          投稿記事のタイトル
 * @param   array   $url            array(投稿記事のid)
 * 										・View->linkのurl(default: 'revision_id' => $revision['Revision']['id'], '#' => $id )
 *  									  セットするとマージする。
 *                                  	・Form->actionのurl(default: '#' => $id ) セットするとマージする。
 * @param   array   $cancelUrl     キャンセルクリック時、復元後戻り先
 * @param   Model Object           記事投稿モデル $postModel(status, is_approved, revision_group_id, 'Revision')
 * @param   Model                  記事投稿モデルデータ $postModelData(status, is_approved, revision_group_id, 'Revision')
 * @param   integer $hierarchy
 * @param   boolean $approved_flag
 * @param   boolean $approved_pre_change_flag
 * @return  boolean|integer 復元時 復元revisionId
 * @since   v 3.0.0.0
 */
	public function showRegist($title, $url, $cancelUrl, $postModel, $postModelData, $hierarchy, $approved_flag = _OFF, $approved_pre_change_flag = _OFF) {
		$isApproved = _ON;
		if($approved_flag == _ON && $hierarchy  <= NC_AUTH_MODERATE) {
			$isApproved = _OFF;
		}
		$revision_name = ($postModelData[$postModel->alias]['status'] == NC_STATUS_TEMPORARY) ? 'draft' : 'publish';
		// 自動保存等で最新のデータがあった場合、表示
		$revision = $this->_controller->Revision->findRevisions(null, $postModelData[$postModel->alias]['revision_group_id'], 1);
		if(isset($revision[0])) {
			$postModelData['Revision'] = $revision[0]['Revision'];
			$revision = $revision[0];
		} else {
			return false;
		}
		$postModelData['Revision']['revision_name'] = $revision_name;

		if($approved_flag == _ON && $hierarchy  <= NC_AUTH_MODERATE) {
			$isApprovalSystem = true;
		} else {
			$isApprovalSystem = false;
		}
		if($isApprovalSystem) {
			$pointer = ($approved_pre_change_flag== _OFF) ? _ON : _OFF;
		} else {
			$pointer = _ON;
		}
		$id = $revision['Revision']['id'];
		$groupId = $revision['Revision']['group_id'];

		if($this->_controller->request->is('post') && isset($this->_controller->request->named['revision_id'])) {
			// 復元
			$newRevisionId = $this->restore($postModel, $postModelData, $this->_controller->request->named['revision_id'], $revision['Revision']['revision_name'], $pointer, $isApproved);
			if($newRevisionId === false) {
				return false;
			}
			return $newRevisionId;
		} else if(isset($this->_controller->request->query['current_revision_id']) && isset($this->_controller->request->query['revision_id'])) {
			$currentRevisionId = $this->_controller->request->query['current_revision_id'];
			$revisionId = $this->_controller->request->query['revision_id'];
			// リビジョン比較
			$currentRevision = $this->_controller->Revision->findById($currentRevisionId);
			$revision = $this->_controller->Revision->findById($revisionId);
			if(!isset($currentRevision['Revision']) || !isset($revision['Revision'])) {
				return false;
			}
			$diffText = $this->compare($currentRevision['Revision']['content'], $revision['Revision']['content']);
			if($diffText === false) {
				return false;
			}
			// セット
			$this->_controller->set('diffText', $diffText);
		} else if(isset($this->_controller->request->named['revision_id'])){
			$revisionId = $this->_controller->request->named['revision_id'];
			$currentRevisionId = $id;
		} else {
			return false;
		}

		// セット
		$this->_controller->set('title', $title);
		$this->_controller->set('url', $url);
		$this->_controller->set('cancel_url', $cancelUrl);
		$this->_controller->set('current_revision_id', $currentRevisionId);
		$this->_controller->set('revision_id', $revisionId);
		$this->_controller->set('revisions', $this->_controller->Revision->findRevisions($id, $groupId, null));
		return true;
	}

/**
 * 承認画面表示・「承認する」、「承認しない」実行。
 * @param   Model Object           記事投稿モデル $postModel(status, is_approved, revision_group_id, 'Revision')
 * @param   Model                  記事投稿モデルデータ $postModelData(status, is_approved, revision_group_id, 'Revision')
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function approve($postModel, $postModelData) {
		$conditions = array(
			"Revision.group_id" => $postModelData[$postModel->alias]['revision_group_id'],
			"Revision.is_approved_pointer" => _ON,
			"Revision.revision_name !=" => 'auto-draft',
		);
		$preRevision = $this->_controller->Revision->find('first', array('conditions' => $conditions));

		$ret = $this->_controller->Revision->findRevisions(null, $postModelData[$postModel->alias]['revision_group_id'], 1);
		if(isset($ret[0])) {
			$revision = $ret[0];
		} else {
			return false;
		}
		if ($this->_controller->request->is('post')) {
			if(!isset($this->_controller->request->data['revision_id']) || !isset($this->_controller->request->data['is_approve'])) {
				return false;
			}
			$isApprove = $this->_controller->request->data['is_approve'];
			$approveRevision = $this->_controller->Revision->findById($this->_controller->request->data['revision_id']);
			if($postModelData[$postModel->alias]['revision_group_id'] != $approveRevision['Revision']['group_id']) {
				return false;
			}

			if($isApprove) {
				// 承認する
				$fields = array(
					'Revision.pointer' => _OFF
				);
				$conditions = array(
					"Revision.group_id" => $postModelData[$postModel->alias]['revision_group_id'],
					"Revision.pointer" => _ON
				);
				if(!$this->_controller->Revision->updateAll($fields, $conditions)) {
					return false;
				}

				$revision_id = $this->_controller->request->data['revision_id'];
				$fields = array(
					'Revision.pointer' => _ON,
					'Revision.is_approved_pointer' => _ON
				);
				$conditions = array(
					"Revision.id" => $revision_id
				);
				if(!$this->_controller->Revision->updateAll($fields, $conditions)) {
					return false;
				}
				$revision_name = $approveRevision['Revision']['revision_name'];
			} else {
				// 承認しない。

				$revision = array(
					'Revision' => array(
						'group_id' => $postModelData[$postModel->alias]['revision_group_id'],
						'pointer' => _ON,
						'is_approved_pointer' => _ON,
						'revision_name' => isset($preRevision['Revision']) ? $preRevision['Revision']['revision_name'] : $revision['Revision']['revision_name'],
						'content_id' => $revision['Revision']['content_id'],
						'content' => isset($preRevision['Revision']) ? $preRevision['Revision']['content'] : ''
					)
				);
				$this->_controller->Revision->create();
				$this->_controller->Revision->set($revision);
				if(!$this->_controller->Revision->save($revision, ($revision['Revision']['content'] != '') ? true : false)) {
					return false;
				}
				$revision['Revision']['id'] = $this->_controller->Revision->id;
				$revision_name = isset($preRevision['Revision']) ? $preRevision['Revision']['revision_name'] : NC_STATUS_PUBLISH;
			}
			$postModelData[$postModel->alias]['status'] = ($revision_name == 'publish') ? NC_STATUS_PUBLISH :
				(($postModelData[$postModel->alias]['status'] == NC_STATUS_TEMPORARY) ? NC_STATUS_TEMPORARY : NC_STATUS_TEMPORARY_BEFORE_RELEASED);
			$postModelData[$postModel->alias]['is_approved'] = _ON;
			$postModelData[$postModel->alias]['pre_change_flag'] = _OFF;
			$postModelData[$postModel->alias]['pre_change_date'] = null;
			if(!$postModel->save($postModelData, true, array('status', 'is_approved', 'pre_change_flag', 'pre_change_date'))) {
				return false;
			}

			// 新着・検索
			$archive = array(
				'Archive' => array(
					'model_name' => $postModel->alias,
					'unique_id' => $postModelData[$postModel->alias]['id'],
					'status' => isset($postModelData[$postModel->alias]['status']) ? $postModelData[$postModel->alias]['status'] : NC_STATUS_PUBLISH,
					'is_approved' => isset($postModelData[$postModel->alias]['is_approved']) ? $postModelData[$postModel->alias]['is_approved'] : _ON,
					'content' => ($isApprove) ? strip_tags($revision['Revision']['content']) : strip_tags($revision['Revision']['content']),
				)
			);
			if(!$this->_controller->Archive->saveAuto($this->_controller->params, $archive)) {
				return false;
			}


			if($isApprove) {
				$this->_controller->Session->setFlash(__('Approved.'));
			} else {
				$this->_controller->Session->setFlash(__('Changed to unapproved.'));
			}
		}

		if(!isset($preRevision['Revision'])) {
			$preContent = '';
		} else {
			$preContent = $preRevision['Revision']['content'];
		}

		if($preContent != '' && $preRevision['Revision']['id'] != $revision['Revision']['id']) {
			$this->_controller->set('pre_approval', $preRevision);
		}
		$this->_controller->set('post_approval', $revision);

		$this->_controller->set('diffText', $this->compare($revision['Revision']['content'], $preContent));
		return true;
	}

/**
 * リビジョン比較処理
 * @param   string $currentContent
 * @param   string $content
 * @return   string   <tr><td></td><td></td><td></td><td></td></tr>となる文字データ
 * @since   v 3.0.0.0
 */
	public function compare($currentContent, $content) {
		$diffText = '';

		//App::uses("Text_Diff", "Vendor/pear/Text/Diff.php");
		//App::uses("Text_Diff_Renderer_compare", "Vendor/pear/Text/Renderer/compare.php");
		App::import('Vendor', 'Text_Diff', array('file' => 'pear/Text/Diff.php'));
		App::import('Vendor', 'Text_Diff_Renderer_compare', array('file' => 'pear/Text/Diff/Renderer/compare.php'));

		$diff = new Text_Diff('auto', array($this->_splitBlocktag($content), $this->_splitBlocktag($currentContent)));

		$renderer = new Text_Diff_Renderer_compare();	// レンダラーを変更
		if(!$diff->isEmpty()){
			$diffText = $renderer->render($diff);
		}

		return $diffText;
	}

/**
 * リビジョン復元処理
 * @param   Model Object           記事投稿モデル $postModel(status, is_approved, revision_group_id, 'Revision')
 * @param   Model                  記事投稿モデルデータ $postModelData(status, is_approved, revision_group_id, 'Revision')
 * @param   integer $revisionId
 * @param   string  $revision_name
 * @param   boolean $pointer
 * @param   boolean $isApproved
 * @return  boolean|integer
 * @since   v 3.0.0.0
 */
	public function restore($postModel, $postModelData, $revisionId, $revision_name = 'publish', $pointer = _ON, $isApprovedPointer = _ON) {

		$revision = $this->_controller->Revision->findById($revisionId);
		if(!isset($revision['Revision'])) {
			return false;
		}
		//$currentRevisions = $this->_controller->Revision->findRevisions($revision['Revision']['id'], $revision['Revision']['group_id'], 1);
		//if(!isset($revision['Revision']) || !isset($currentRevisions[0]['Revision'])) {
		//	return false;
		//}

		/*if($currentRevisions[0]['Revision']['pointer'] == _ON) {
			$fields = array(
				$this->_controller->Revision->alias.'.pointer' => _OFF
			);
			$conditions = array(
				$this->_controller->Revision->alias.".id" => $currentRevisions[0]['Revision']['id']
			);
			$this->_controller->Revision->updateAll($fields, $conditions);
		}*/

		$setRevision = array(
			'Revision' => array(
				'group_id' => $revision['Revision']['group_id'],
				'pointer' => $pointer,
				'is_approved_pointer' => $isApprovedPointer,
				'revision_name' => $revision_name,
				'content_id' => $revision['Revision']['content_id'],
				'content' => $revision['Revision']['content']
			)
		);

		$fieldListRevision = array(
			'group_id', 'pointer', 'is_approved_pointer', 'revision_name', 'content_id', 'content',
		);

		if(!$this->_controller->Revision->save($setRevision, ($setRevision['Revision']['content'] != '') ? true : false, $fieldListRevision)) {
			return false;
		}

		$isApproved = isset($postModelData[$postModel->alias]['is_approved']) ? $postModelData[$postModel->alias]['is_approved'] : _ON;
		if($this->_controller->request->is('post') && $isApprovedPointer == _OFF) {
			$isApproved = _OFF;
			$fields = array(
				$postModel->alias.'.is_approved' => $isApproved
			);
			$conditions = array(
				$postModel->alias.".id" => $postModelData[$postModel->alias]['id']
			);
			if(!$postModel->updateAll($fields, $conditions)) {
				return false;
			}
		}

		// 新着・検索
		$archive = array(
			'Archive' => array(
				'model_name' => $postModel->alias,
				'unique_id' => $postModelData[$postModel->alias]['id'],
				'status' => isset($postModelData[$postModel->alias]['status']) ? $postModelData[$postModel->alias]['status'] : NC_STATUS_PUBLISH,
				'is_approved' => $isApproved,
			)
		);
		if(empty($postModelData[$postModel->alias]['pre_change_flag'])) {
			$archive['Archive']['content'] = strip_tags($revision['Revision']['content']);
		}
		if(!$this->_controller->Archive->saveAuto($this->_controller->params, $archive)) {
			return false;
		}

		$this->_controller->Session->setFlash(__('Post restored to revision from %s',
			$this->_controller->Revision->dateUtc($revision['Revision']['created'], __('Y-m-d H:i:s'))));

		return $this->_controller->Revision->id;
	}

/**
 * pre_change_flag,pre_change_date更新処理
 * @param   Model $currentModel
 * @param   array $data
 * @return  string
 * @since   v 3.0.0.0
 */
	public function updatePreChange(Model $currentModel, $data) {
		if(isset($data[$currentModel->alias]['pre_change_flag']) && isset($data[$currentModel->alias]['revision_group_id']) && $data[$currentModel->alias]['pre_change_flag'] == _ON
			&& isset($data[$currentModel->alias]['pre_change_date'])) {
			if(!$currentModel->isFutureDateTime(array($data[$currentModel->alias]['pre_change_date']), false)) {
				$fields = array(
					$currentModel->alias.'.pre_change_flag' => _OFF,
					$currentModel->alias.'.pre_change_date' => null
				);
				$conditions = array(
					$currentModel->alias.".id" => $data[$currentModel->alias]['id']
				);
				$currentModel->unbindModel( array( 'belongsTo' => array_keys( $currentModel->belongsTo ) ) );
				$currentModel->updateAll($fields, $conditions);

				$Revision = ClassRegistry::init('Revision');
				$revision = $Revision->findRevisions(null, $data[$currentModel->alias]['revision_group_id'], 1, true);
				if(isset($revision[0]['Revision'])) {
					$fields = array(
						'Revision.pointer' => _OFF
					);
					$conditions = array(
						"Revision.group_id" => $data[$currentModel->alias]['revision_group_id'],
					);
					$Revision->updateAll($fields, $conditions);

					$fields = array(
						'Revision.pointer' => _ON,
						'Revision.is_approved_pointer' => _ON
					);
					$conditions = array(
						"Revision.id" => $revision[0]['Revision']['id'],
					);
					$Revision->updateAll($fields, $conditions);

					$data['Revision']['content'] = $revision[0]['Revision']['content'];
				}
			}
		}
		return isset($data['Revision']['content']) ? $data['Revision']['content'] : '';
	}

/**
 * WYSIWYGをブロック要素毎に配列に格納し返す。
 * @param   string $string
 * @param   array
 * @return  boolean
 * @since   v 3.0.0.0
 */
	protected function _splitBlocktag($string) {
		return preg_split('/((?<=<br>)|(?<=<\/blockquote>)|(?<=<\/div>)|(?<=<\/h1>)|(?<=<\/h2>)|(?<=<\/h3>)|(?<=<\/h4>)|(?<=<\/h5>)|(?<=<\/h6>)|(?<=<\/p>)|(?<=<\/ol>)|(?<=<\/ul>)|(?<=<\/table>)|(?<=<\/pre>))/iu', $string);
	}
}