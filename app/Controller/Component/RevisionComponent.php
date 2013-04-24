<?php
/**
 * RevisionComponentクラス
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
class RevisionComponent extends Component {
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
 * 履歴情報画面表示 リビジョン比較画面表示処理
 * @param   integer $contentId      ContentID
 * @param   string  $title          投稿記事のタイトル
 * @param   integer $htmlareaId	    投稿記事のhtmlarea_id
 * @param   array   $url            array(投稿記事のid)
 * 										・View->linkのurl(default: 'revision_id' => $revision['Htmlarea']['id'], '#' => $id )
 *  									  セットするとマージする。
 *                                  	・Form->actionのurl(default: '#' => $id ) セットするとマージする。
 * @param   array   $cancelUrl     キャンセルクリック時、復元後戻り先
 * @return  boolean|integer 復元時 復元htmlareaId
 * @since   v 3.0.0.0
 */
	public function setDatas($contentId, $title, $htmlareaId, $url, $cancelUrl) {

		if($this->_controller->request->is('post') && isset($this->_controller->request->named['revision_id'])) {
			$newHtmlareaId = $this->restore($contentId, $htmlareaId, $this->_controller->request->named['revision_id']);
			if($newHtmlareaId === false) {
				return false;
			}
			return $newHtmlareaId;
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
		} else {
			$revisionId = $this->_controller->request->named['revision_id'];
			$currentRevisionId = $htmlareaId;
		}

		// セット
		$this->_controller->set('title', $title);
		$this->_controller->set('url', $url);
		$this->_controller->set('cancel_url', $cancelUrl);

		$this->_controller->set('current_revision_id', $currentRevisionId);
		$this->_controller->set('revision_id', $revisionId);
		$this->_controller->set('now_revision_id', $htmlareaId);
		$this->_controller->set('revisions', $this->_controller->Htmlarea->findRevisions($htmlareaId, true));
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
		$currentRevision = $this->_controller->Htmlarea->findById($currentRevisionId);
		$revision = $this->_controller->Htmlarea->findById($revisionId);
		if(!isset($currentRevision['Htmlarea']) || !isset($revision['Htmlarea'])) {
			return false;
		}

		//App::uses("Text_Diff", "Vendor/pear/Text/Diff.php");
		//App::uses("Text_Diff_Renderer_compare", "Vendor/pear/Text/Renderer/compare.php");
		App::import('Vendor', 'Text_Diff', array('file' => 'pear/Text/Diff.php'));
		App::import('Vendor', 'Text_Diff_Renderer_compare', array('file' => 'pear/Text/Diff/Renderer/compare.php'));

		$diff = new Text_Diff('auto', array($this->_splitBlocktag($revision['Htmlarea']['content']), $this->_splitBlocktag($currentRevision['Htmlarea']['content'])));

		$renderer = new Text_Diff_Renderer_compare();	// レンダラーを変更
		if(!$diff->isEmpty()){
			$diffText = $renderer->render($diff);
		}

		return $diffText;
	}

/**
 * リビジョン比較処理
 * @param   integer $contentId
 * @param   integer $htmlareaId
 * @param   integer $revisionId

 * @param   array   $cancelUrl     キャンセルクリック時、復元後戻り先
 * @return  boolean|integer
 * @since   v 3.0.0.0
 */
	public function restore($contentId, $htmlareaId, $revisionId) {
		$revision = $this->_controller->Htmlarea->findById($revisionId);
		if(!isset($revision['Htmlarea'])) {
			return false;
		}

		$htmlarea = array(
			'Htmlarea' => array(
				'revision_parent' => $htmlareaId,
				'revision_name' => 'restore',
				'content_id' => $contentId,
				'content' => $revision['Htmlarea']['content'],
				'non_approved_content' => $revision['Htmlarea']['non_approved_content'],
			)
		);

		$fieldListHtmlarea = array(
			'revision_parent', 'revision_name', 'content_id', 'content', 'non_approved_content',
		);

		if(!$this->_controller->Htmlarea->save($htmlarea, true, $fieldListHtmlarea)) {
			return false;
		}
		$this->_controller->Session->setFlash(__('Post restored to revision from %s',
			$this->_controller->Htmlarea->date($revision['Htmlarea']['created'], __('Y-m-d H:i:s'))));

		return $this->_controller->Htmlarea->id;
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