<?php
/**
 * BlockStylesControllerクラス
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
class BlockStylesController extends BlockAppController {
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
		$is_resize = isset($this->request->data['is_resize']) ? intval($this->request->data['is_resize']) : _OFF;

		unset($this->request->data['is_apply']);
		$block = $this->request->data;
		$templates = $this->_showTemplete($data_block['Block']['controller_action']);

		if($this->request->is('post')) {
			// 登録処理
			if(!$is_theme && !$is_resize) {
				if($block['Block']['min_width_size_select'] == BLOCK_STYLES_MIN_SIZE_AUTO ||
						$block['Block']['min_width_size_select'] == BLOCK_STYLES_MIN_SIZE_100) {
					$block['Block']['min_width_size'] = $block['Block']['min_width_size_select'];
				}
				if($block['Block']['min_height_size_select'] == BLOCK_STYLES_MIN_SIZE_AUTO ||
						$block['Block']['min_height_size_select'] == BLOCK_STYLES_MIN_SIZE_100) {
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
				if(isset($templates[$block['Block']['temp_name']])) {
					if($block['Block']['temp_name'] == 'Default') {
						$block['Block']['temp_name'] = '';
					}
					$fieldList[] = 'temp_name';
				}
			} else if($is_resize) {
				$block['Block'] = $data_block['Block'];
				if(isset($this->request->data['min_width_size'])) {
					$block['Block']['min_width_size'] = intval($this->request->data['min_width_size']);
				}
				if(isset($this->request->data['min_height_size'])) {
					$block['Block']['min_height_size'] = intval($this->request->data['min_height_size']);
				}
				$fieldList = array(
					'min_width_size',
					'min_height_size',
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
			if($is_resize) {
				$this->render(false);
				return;
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
 * テーマ一覧をset
 * @param   string $theme_name
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

/**
 * テンプレート一覧をset
 * @param   string $controller_action
 * @return  array  $templates
 * @since   v 3.0.0.0
 */
	protected function _showTemplete($controller_action) {
		$templates = array();
		if($controller_action != 'group') {
			$controller_action_arr = explode('/', $controller_action);
			if(isset($controller_action_arr[0])) {
				$plugin_path = App::pluginPath(Inflector::camelize($controller_action_arr[0]));
				$view_path = $plugin_path . 'View' . DS . 'Themed' . DS;
				if(is_dir($view_path)) {
					$templates['Default'] = __('Default');

					$dirArray = $this->Theme->getCurrentDir($view_path);
					if(is_array($dirArray) && count($dirArray) > 0) {
						foreach($dirArray as $value) {
							$templates[$value] = __d($controller_action_arr[0], $value);
						}
						$this->set('templates', $templates);
					}
				}
			}
		}
		return $templates;
	}
}