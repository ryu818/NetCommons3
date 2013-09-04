<?php
/**
 * ContentControllerクラス
 *
 * <pre>
 * コンテンツ一覧画面用コントローラ
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       App.Controller
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class ContentController extends ContentAppController {
/**
 * Component name
 *
 * @var array
 */
	public $components = array('CheckAuth' => array('allowAuth' => NC_AUTH_CHIEF, 'chkPlugin' => false));

/**
 * Model name
 *
 * @var array
 */
	public $uses = array('Content.ContentList', 'Content.BlockList');

/**
 * コンテンツ一覧表示
 * @param   integer $activeRoomId	表示するルームID
 *						Activeなコンテンツを削除した場合、再描画できなくなるため。
 * @return  void
 * @since   v 3.0.0.0
 */
	public function index($activeRoomId = null) {
		$user = $this->Auth->user();
		$authorityId = isset($user['authority_id']) ? $user['authority_id'] : 0;

		if(!isset($this->nc_block['Module']['id'])) {
			throw new BadRequestException(__('Unauthorized request.<br />Please reload the page.'));
		}
		$activeModuleId = isset($this->request->named['module_id']) ? intval($this->request->named['module_id']) : $this->nc_block['Module']['id'];

		// 削除コンテンツがページにはってあれば、そのブロックをリロードするため
		$reloadBlockId = isset($this->request->named['reload_block_id']) ? intval($this->request->named['reload_block_id']) : null;
		$activeContentId = $this->nc_block['Block']['content_id'];

		if($this->request->is('post')) {
			if(!isset($this->request->data['Content']['id'])) {
				throw new BadRequestException(__('Unauthorized request.<br />Please reload the page.'));
			}
			$activeContentId = $this->request->data['Content']['id'];
			$activeContent = $this->Content->findById($activeContentId);
			$activeModule = isset($activeContent['Content']['module_id']) ? $this->Module->findById($activeContent['Content']['module_id']) : null;
			if(!isset($activeContent['Content']) || !isset($activeModule['Module'])
				|| $activeContent['Content']['display_flag'] == NC_DISPLAY_FLAG_DISABLE
				|| $activeContent['Content']['module_id'] == 0 || $activeModule['Module']['system_flag'] == _ON) {
				$this->response->statusCode('404');
				$this->flash(__('Content not found.'), '');
				return;
			}
			// 登録処理
			$activeModuleId = $activeModule['Module']['id'];
			$block = $this->nc_block;
			$fieldList = array('content_id');
			$block['Block']['content_id'] = $activeContent['Content']['id'];
			if($activeContent['Content']['module_id'] != $block['Block']['module_id']) {
				// モジュールの切替
				// delete_blockアクションを呼ぶ
				$fieldList[] = 'module_id';
				$fieldList[] = 'controller_action';
				$block['Block']['module_id'] = $activeContent['Content']['module_id'];
				$block['Block']['controller_action'] = $activeModule['Module']['controller_action'];
				$dirName = $block['Module']['dir_name'];
				if($this->Module->isOperationAction($dirName, 'delete_block')) {
					$page = $this->Page->findById($block['Block']['page_id']);
					$args = array(
						array('Block' => $block['Block']),
						array('Content' => $block['Content']),
						$page,
					);
					if(!$this->Module->operationAction($dirName, 'delete_block', $args)) {
						throw new InternalErrorException(__('Failed to execute the %s.', __d('content', 'Delete block')));
					}
				}
			}

			if($this->nc_block['Content']['display_flag'] == NC_DISPLAY_FLAG_DISABLE && !$this->Content->delete($this->nc_block['Content']['id'])) {
				// コンテンツがまだ設定前のデータなので削除
				throw new InternalErrorException(__('Failed to delete the database, (%s).', 'contents'));
			}

			if($this->Block->save($block, true, $fieldList)) {
				$this->Session->setFlash(__('Has been successfully updated.'));
				$this->set('active_controller_action', $activeModule['Module']['controller_action']);
			}
		} else if(isset($reloadBlockId)) {
			$reloadBlock = $this->Block->findById($reloadBlockId);
			if(isset($reloadBlock['Block'])) {
				$this->set('active_id', '_'. $reloadBlock['Block']['id']);
			}
		}

		if(!isset($activeContent)) {
			$activeContent = $this->Content->findById($activeContentId);
			if(!isset($activeRoomId) && !isset($activeContent['Content']['id'])) {
				$this->response->statusCode('404');
				$this->flash(__('No Content.'), '');
				return;
			}
			$activeRoomId = !isset($activeRoomId) ? $activeContent['Content']['room_id'] : $activeRoomId;
		}

		$activeRoom = $this->Page->findById($activeRoomId);
		if(!isset($activeRoom['Page']['id'])) {
			$this->response->statusCode('404');
			$this->flash(__('No Content.'), '');
			return;
		}
		$activeRoom = $this->Page->setPageName($activeRoom);

		$this->set('room_name', $activeRoom['Page']['page_name']);
		// モジュール一覧
		$this->set('active_module_id', $activeModuleId);
		$this->set('active_room_id', $activeRoomId);

		$this->set('active_content_id', $activeContentId);
		$this->set('modules', $this->ModuleLink->findModulelinks($this->nc_page['Page']['room_id'], $authorityId, $this->nc_page['Page']['space_type']));
	}

/**
 * コンテンツ一覧Grid表示
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function content_list() {
		if(!$this->request->is('post')) {
			throw new BadRequestException(__('Unauthorized request.<br />Please reload the page.'));
		}

		$userId = $this->Auth->user('id');
		$rp = intval($this->request->data['rp']);
		$sortname = ($this->request->data['sortname'] == "title" || $this->request->data['sortname'] == "display_flag")
			? $this->request->data['sortname'] : null;
		$sortorder = ($this->request->data['sortorder'] == "asc" || $this->request->data['sortorder'] == "desc") ? $this->request->data['sortorder'] : "asc";
		$pageNum = intval($this->request->data['page']) == 0 ? 1 : intval($this->request->data['page']);
		$moduleId = isset($this->request->named['module_id']) ? intval($this->request->named['module_id']) : $this->nc_block['Block']['module_id'];

		$activeRoomId = $this->request->named['active_room_id'];
		$activeContentId = isset($this->request->named['active_content_id']) ? intval($this->request->named['active_content_id']) : $this->content_id;
		if(!isset($activeRoomId)) {
			throw new BadRequestException(__('Unauthorized request.<br />Please reload the page.'));
		}

		$order = null;
		if(!empty($sortname) && ($sortname != "title" || $sortorder != "asc")) {
			$order = array(
				'Content.'.$sortname => $sortorder,
				'Content.id' => $sortorder
			);
		}
		$approvedFlag = _ON; 								// TODO:固定

		list($total, $contents) = $this->ContentList->findContents($userId, $activeRoomId, $pageNum, $rp, $order, $activeContentId, $moduleId, $approvedFlag);
		$this->set('page_num', $pageNum);
		$this->set('total', $total);
		$this->set('contents', $contents);
	}

/**
 * 配置ブロック一覧表示
 * @param   integer $contentId
 * @return  void
 * @since   v 3.0.0.0
 */
	public function block($contentId) {
		$userId = $this->Auth->user('id');

		if (!$this->request->is('ajax') || empty($contentId)) {
			throw new BadRequestException(__('Unauthorized request.<br />Please reload the page.'));
		}

		$content = $this->Content->findAuthById($contentId, $userId);
		if(!isset($content['Content'])) {
			$this->response->statusCode('404');
			$this->flash(__('Content not found.'), '');
			return;
		}
		if(!$this->CheckAuth->checkAuth($content['PageAuthority']['hierarchy'], NC_AUTH_CHIEF)) {
			$this->response->statusCode('403');
			$this->flash(__('Authority Error!  You do not have the privilege to access this page.'), '');
			return;
		}

		$this->set('content', $content);
	}

/**
 * 配置ブロック一覧Grid表示
 * @param   integer $contentId
 * @return  void
 * @since   v 3.0.0.0
 */
	public function block_list($contentId) {
		$userId = $this->Auth->user('id');

		if(!$this->request->is('post') || empty($contentId)) {
			throw new BadRequestException(__('Unauthorized request.<br />Please reload the page.'));
		}

		$content = $this->Content->findAuthById($contentId, $userId);
		if(!isset($content['Content'])) {
			$this->response->statusCode('404');
			$this->flash(__('Content not found.'), '');
			return;
		}
		if(!$this->CheckAuth->checkAuth($content['PageAuthority']['hierarchy'], NC_AUTH_CHIEF)) {
			$this->response->statusCode('403');
			$this->flash(__('Authority Error!  You do not have the privilege to access this page.'), '');
			return;
		}
		$rp = intval($this->request->data['rp']);
		$sortname = "title";
		$sortorder = ($this->request->data['sortorder'] == "asc" || $this->request->data['sortorder'] == "desc") ? $this->request->data['sortorder'] : "asc";
		$pageNum = intval($this->request->data['page']) == 0 ? 1 : intval($this->request->data['page']);

		$rets = $this->BlockList->findBlocks($userId, $content['Content']['master_id'], $this->block_id, $sortname, $sortorder, $pageNum, $rp);
		if($rets == false) {
			throw new InternalErrorException(__('Failed to obtain the database, (%s).', 'blocks'));
		}
		list($total, $blocks) = $rets;

		$this->set('content', $content);
		$this->set('blocks', $blocks);
		$this->set('page_num', $pageNum);
		$this->set('total', $total);
	}

/**
 * コンテンツ編集処理
 * @param   integer $contentId
 * @return  void
 * @since   v 3.0.0.0
 */
	public function edit($contentId) {
		$userId = $this->Auth->user('id');
		$content = $this->Content->findAuthById($contentId, $userId);
		if(!isset($content['Content'])) {
			$this->response->statusCode('404');
			$this->flash(__('Content not found.'), '');
			return;
		}
		if(!$this->CheckAuth->checkAuth($content['PageAuthority']['hierarchy'], NC_AUTH_CHIEF)) {
			$this->response->statusCode('403');
			$this->flash(__('Authority Error!  You do not have the privilege to access this page.'), '');
			return;
		}

		if($this->request->is('post')) {
			// 登録処理
			$content['Content'] = array(
				'id' => $contentId,
				'title' => $this->request->data['Content']['title'],
				'display_flag' => $this->request->data['Content']['display_flag'],
			);
			if($this->Content->save($content, true, array('title', 'display_flag'))) {
				$this->Session->setFlash(__('Has been successfully updated.'));
				$this->set('success', true);
			}
		}

		$this->set('content', $content);
	}

/**
 * コンテンツ削除処理
 * @param   integer $contentId
 * @return  void
 * @since   v 3.0.0.0
 */
	public function delete($contentId) {
		$userId = $this->Auth->user('id');
		if(!$this->request->is('post') || empty($contentId)) {
			throw new BadRequestException(__('Unauthorized request.<br />Please reload the page.'));
		}
		$content = $this->Content->findAuthById($contentId, $userId);
		if(!isset($content['Content'])) {
			$this->response->statusCode('404');
			$this->flash(__('Content not found.'), '');
			return;
		}
		if(!$this->CheckAuth->checkAuth($content['PageAuthority']['hierarchy'], NC_AUTH_CHIEF)) {
			$this->response->statusCode('403');
			$this->flash(__('Authority Error!  You do not have the privilege to access this page.'), '');
			return;
		}

		if(!$this->Content->deleteContent($content, true)) {
			throw new InternalErrorException(__('Failed to execute the %s.', __d('content', 'Delete content')));
		}
		$this->Session->setFlash(__('Has been successfully deleted.'));

		// 現在のページに配置されているブロックならば、再描画。
		// TODO:現在のページのカレントブロック以外については複数ある場合もあるため、再描画さしていない。
		if(isset($this->nc_block['Block']['content_id']) == $contentId) {
			$this->redirect(array('action' => 'index', $content['Content']['room_id'], 'reload_block_id' => $this->nc_block['Block']['id']));
		} else {
			$this->redirect(array('action' => 'index', $content['Content']['room_id']));
		}
	}
}