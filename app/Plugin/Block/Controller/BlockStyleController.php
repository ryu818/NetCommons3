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
	public $uses = array('Theme');

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
		$data_block = $this->Block->findById($block_id);

		$content_title = $this->nc_block['Content']['title'];
		$active_tab = 0;

		$is_apply = isset($this->request->data['is_apply']) ? intval($this->request->data['is_apply']) : _OFF;
		$is_theme = isset($this->request->data['is_theme']) ? intval($this->request->data['is_theme']) : _OFF;

		unset($this->request->data['is_apply']);
		$block = $this->request->data;

		if($this->request->is('post')) {
			// 登録処理
			if(!$is_theme) {
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

				$block['Block'] = array_merge($data_block['Block'], $block['Block']);
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
			} else {
				// ブロックテーマ変更
				$block['Block'] = array_merge($data_block['Block'], $block['Block']);
				$fieldList = array(
					'theme_name',
				);
				$active_tab = 1;
			}

			$this->Block->set($block);
			if($this->Block->save($block, true, $fieldList)) {
				$this->Session->setFlash(__('Has been successfully updated.'));
			}
		} else {
			$block = $data_block;
		}

		$this->_showTheme($block['Block']['theme_name']);
		$this->set('block_id', $block_id);
		$this->set('content_title', $content_title);
		$this->set('block', $block);
		$this->set('active_tab', $active_tab);

		if(count($this->Block->validationErrors) == 0 && !$is_apply && $this->request->is('post')) {
			$this->render(false);
		}
	}

/**
 * ブロックスタイル画面
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	protected function _showTheme($theme_name) {
		$ret = $this->Theme->getThemeList($theme_name);
		list($category_list, $theme_list, $image_path, $act_category) = $ret;
		$this->set('category_list', $category_list);
		$this->set('theme_list', $theme_list);
		$this->set('image_path', $image_path);
		$this->set('act_category', $act_category);
	}
}