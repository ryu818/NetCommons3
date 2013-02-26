<?php
/**
 * BlockStyleControllerクラス
 *
 * <pre>
 * ブロックスタイル
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       App.Controller
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class BlockStyleController extends BlockAppController {
/**
 * Model name
 *
 * @var array
 */
	public $uses = array();

/**
 * Component name
 *
 * @var array
 */
	public $components = array('CheckAuth' => array('allowAuth' => NC_AUTH_CHIEF));

/**
 * Helper name
 *
 * @var array
 */
	public $helpers = array('TimeZone');

/**
 * Model Block Content Module
 *
 * @var integer
 */
	public $nc_block = null;

/**
 * ブロックスタイル画面
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function index() {
		include_once dirname(dirname(__FILE__)).'/Config/defines.inc.php';

		$user_id = $this->Auth->user('id');
		$block_id = $this->nc_block['Block']['id'];
		$content_title = $this->nc_block['Content']['title'];

		$is_apply = isset($this->request->data['is_apply']) ? intval($this->request->data['is_apply']) : _OFF;
		unset($this->request->data['is_apply']);
		$block = $this->request->data;

		if($this->request->is('post')) {
			// 登録処理
			if($block['Block']['min_width_size_select'] == BLOCK_STYLE_MIN_SIZE_AUTO ||
					$block['Block']['min_width_size_select'] == BLOCK_STYLE_MIN_SIZE_100) {
				$block['Block']['min_width_size'] = $block['Block']['min_width_size_select'];
			}
			if($block['Block']['min_height_size_select'] == BLOCK_STYLE_MIN_SIZE_AUTO ||
					$block['Block']['min_height_size_select'] == BLOCK_STYLE_MIN_SIZE_100) {
				$block['Block']['min_height_size'] = $block['Block']['min_height_size_select'];
			}
			unset($block['Block']['id']);
			unset($block['Block']['min_width_size_select']);
			unset($block['Block']['min_height_size_select']);
			if($block['Block']['title'] == $content_title) {
				$block['Block']['title'] = "{X-CONTENT}";
			}

			$block['Block'] = array_merge($this->nc_block['Block'], $block['Block']);
			$fieldList = array(
				'title',
				'show_title',
				'show_title',
				//'temp_name',
				'display_flag',
				'display_from_date',
				'display_to_date',
				'min_width_size',
				'min_height_size',
				'left_margin',
				'right_margin',
				'top_margin',
				'bottom_margin',
			);

			$this->Block->set($block);
			if($this->Block->save($block, true, $fieldList)) {
				$this->Session->setFlash(__('Has been successfully updated.'));
			}
			if($block['Block']['title'] == "{X-CONTENT}") {
				$block['Block']['title'] = $content_title;
			}
		} else {
			$block = $this->nc_block;
		}

		$this->set('block_id', $block_id);
		$this->set('block', $block);
		$this->set('active_tab', 0);//固定

		if(count($this->Block->validationErrors) == 0 && !$is_apply && $this->request->is('post')) {
			$this->render(false);
		}
	}
}