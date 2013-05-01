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
		if(isset($this->_controller->request->data['auto_regist']) && $this->_controller->request->data['auto_regist']) {
			$isAutoRegist = true;
		}
		if(empty($id) && isset($this->_controller->request->data['autoregist_post_id']) && $this->_controller->request->data['autoregist_post_id']) {
			// 新規投稿で自動保存の2回目以降は$post_idがセットされないためセット
			$id = $this->_controller->request->data['autoregist_post_id'];
		}

		if(empty($id) && $isAutoRegist) {
			// 自動保存で新規登録時は一時保存
			$isTemporally = _ON;
		}
		if(!isset($isTemporally)) {
			if(!isset($this->_controller->request->data['is_temporally']) || $this->_controller->request->data['is_temporally'] == _OFF) {
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
 * 履歴情報画面表示 リビジョン比較画面表示処理
 * @param   string  $title          投稿記事のタイトル
 * @param   Model Revision          Model Revision
 * @param   array   $url            array(投稿記事のid)
 * 										・View->linkのurl(default: 'revision_id' => $revision['Revision']['id'], '#' => $id )
 *  									  セットするとマージする。
 *                                  	・Form->actionのurl(default: '#' => $id ) セットするとマージする。
 * @param   array   $cancelUrl     キャンセルクリック時、復元後戻り先
 * @return  boolean|integer 復元時 復元revisionId
 * @since   v 3.0.0.0
 */
	public function setDatas($title, $revision, $url, $cancelUrl) {
		$id = $revision['Revision']['id'];
		$groupId = $revision['Revision']['group_id'];

		if($this->_controller->request->is('post') && isset($this->_controller->request->named['revision_id'])) {
			$newRevisionId = $this->restore($this->_controller->request->named['revision_id']);
			if($newRevisionId === false) {
				return false;
			}
			return $newRevisionId;
		} else if(isset($this->_controller->request->query['current_revision_id']) && isset($this->_controller->request->query['revision_id'])) {
			$currentRevisionId = $this->_controller->request->query['current_revision_id'];
			$revisionId = $this->_controller->request->query['revision_id'];
			// リビジョン比較
			$diffText = $this->compare($currentRevisionId, $revisionId);
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
 * リビジョン比較処理
 * @param   integer $currentRevisionId
 * @param   integer $revisionId
 * @param   string   <tr><td></td><td></td><td></td><td></td></tr>となる文字データ
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function compare($currentRevisionId, $revisionId) {
		$diffText = '';
		$currentRevision = $this->_controller->Revision->findById($currentRevisionId);
		$revision = $this->_controller->Revision->findById($revisionId);
		if(!isset($currentRevision['Revision']) || !isset($revision['Revision'])) {
			return false;
		}

		//App::uses("Text_Diff", "Vendor/pear/Text/Diff.php");
		//App::uses("Text_Diff_Renderer_compare", "Vendor/pear/Text/Renderer/compare.php");
		App::import('Vendor', 'Text_Diff', array('file' => 'pear/Text/Diff.php'));
		App::import('Vendor', 'Text_Diff_Renderer_compare', array('file' => 'pear/Text/Diff/Renderer/compare.php'));

		$diff = new Text_Diff('auto', array($this->_splitBlocktag($revision['Revision']['content']), $this->_splitBlocktag($currentRevision['Revision']['content'])));

		$renderer = new Text_Diff_Renderer_compare();	// レンダラーを変更
		if(!$diff->isEmpty()){
			$diffText = $renderer->render($diff);
		}

		return $diffText;
	}

/**
 * リビジョン復元処理
 * @param   integer $revisionId
 * @param   array   $cancelUrl     キャンセルクリック時、復元後戻り先
 * @return  boolean|integer
 * @since   v 3.0.0.0
 */
	public function restore($revisionId) {

		$revision = $this->_controller->Revision->findById($revisionId);
		$currentRevisions = $this->_controller->Revision->findRevisions($revision['Revision']['id'], $revision['Revision']['group_id'], 1);
		if(!isset($revision['Revision']) || !isset($currentRevisions[0]['Revision'])) {
			return false;
		}

		if($currentRevisions[0]['Revision']['pointer'] == _ON) {
			$fields = array(
				$this->_controller->Revision->alias.'.pointer' => _OFF
			);
			$conditions = array(
				$this->_controller->Revision->alias.".id" => $currentRevisions[0]['Revision']['id']
			);
			$this->_controller->Revision->updateAll($fields, $conditions);
		}

		$setRevision = array(
			'Revision' => array(
				'group_id' => $revision['Revision']['group_id'],
				'pointer' => $currentRevisions[0]['Revision']['pointer'],
				'revision_name' => 'restore',
				'content_id' => $revision['Revision']['content_id'],
				'content' => $revision['Revision']['content']
			)
		);

		$fieldListRevision = array(
			'group_id', 'pointer', 'revision_name', 'content_id', 'content',
		);

		if(!$this->_controller->Revision->save($setRevision, true, $fieldListRevision)) {
			return false;
		}
		$this->_controller->Session->setFlash(__('Post restored to revision from %s',
			$this->_controller->Revision->date($revision['Revision']['created'], __('Y-m-d H:i:s'))));

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

				App::uses('Revision', 'Model');
				$Revision = new Revision();
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
						'Revision.pointer' => _ON
					);
					$conditions = array(
						"Revision.id" => $revision[0]['Revision']['id'],
					);
					$Revision->updateAll($fields, $conditions);

					$data['Revision']['content'] = $revision[0]['Revision']['content'];
				}
			}
		}
		return $data['Revision']['content'];
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