<?php
/**
 * BlogStylesControllerクラス
 *
 * <pre>
 * ブログ表示方法変更画面用コントローラ
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       App.Controller
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class BlogStylesController extends BlogAppController {
/**
 * Component name
 *
 * @var array
 */
	public $components = array('Security', 'CheckAuth' => array('allowAuth' => NC_AUTH_CHIEF));

/**
 * 実行前処理
 * <pre>Tokenチェック処理</pre>
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function beforeFilter() {
		parent::beforeFilter();
		$this->Security->validatePost = false;
		$this->Security->csrfUseOnce = false;
	}

/**
 * ブログ表示方法変更画面
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function index() {
		$params = array('conditions' => array('block_id' => $this->block_id));
		$blog_styles = $this->BlogStyle->find('all', $params);
		if(empty($blog_styles)) {
			$blog_styles = $this->BlogStyle->findDefault($this->block_id, true);
			if($blog_styles === false) {
				$this->flash(__('Failed to register the database, (%s).', 'blog_styles'), null, 'BlogStyles.index.001', '500');
				return;
			}
		}
		$blog_styles = $this->BlogStyle->afterFindColRow($blog_styles);

		if($this->request->is('post')) {
			if(!isset($this->request->data['widget_type']) || !isset($this->request->data['col_num']) || !isset($this->request->data['row_num'])) {
				$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'BlogStyles.index.002', '500');
				return;
			}

			// 表示順変更処理
			if(!$this->BlogStyle->changeDisplay($this->block_id, $this->request->data['widget_type'], $this->request->data['col_num'], $this->request->data['row_num'])) {
				$this->flash(__('Failed to update the database, (%s).', 'blog_styles'), null, 'BlogStyles.index.003', '500');
				return;
			}

			$this->render(false);
			return;
		}
		$this->set('blog_styles', $blog_styles);
	}

/**
 * 公開・非公開変更
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function display() {
		if(!$this->request->is('post') || !isset($this->request->data['widget_type']) || !isset($this->request->data['display_flag'])) {
			$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'BlogStyles.display.001', '500');
			return;
		}
		$fields = array(
			'BlogStyle.display_flag' => intval($this->request->data['display_flag'])
		);
		$conditions = array(
			"BlogStyle.block_id" => $this->block_id,
			"BlogStyle.widget_type" => intval($this->request->data['widget_type'])
		);
		$result = $this->BlogStyle->updateAll($fields, $conditions);
		if(!$result) {
			$this->flash(__('Failed to update the database, (%s).', 'blog_styles'), null, 'BlogStyles.display.002', '500');
			return;
		}
		$this->render(false);
		return;
	}

/**
 * ブログの表示方法Widget更新
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function widget() {

		if(!$this->request->is('post') || !isset($this->request->data['BlogStyle']['widget_type'])) {
			$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'BlogStyles.widget.001', '500');
		}

		$widget_type = intval($this->request->data['BlogStyle']['widget_type']);
		$params = array('conditions' => array('block_id' => $this->block_id, 'widget_type' => $widget_type));
		$blog_style = $this->BlogStyle->find('first', $params);
		if(!isset($blog_style['BlogStyle'])) {
			$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'BlogStyles.widget.002', '500');
		}
		$data_blog_style = $this->request->data;

		$blog_style['BlogStyle'] = array_merge($blog_style['BlogStyle'], $data_blog_style['BlogStyle']);
		$fieldList = array(
			'visible_item',
			'options',
		);

		$this->BlogStyle->set($blog_style);
		if($this->BlogStyle->save($blog_style, true, $fieldList)) {
			$this->Session->setFlash(__('Has been successfully updated.'));
		}
		$this->set('blog_style', $data_blog_style);
		switch($widget_type) {
			case BLOG_WIDGET_TYPE_MAIN:
				$this->render('Elements/blog_styles/main');
				break;
			case BLOG_WIDGET_TYPE_RECENT_POSTS:
				$this->render('Elements/blog_styles/widget/recent_posts');
				break;
			case BLOG_WIDGET_TYPE_RECENT_COMMENTS:
				$this->render('Elements/blog_styles/widget/recent_comments');
				break;
			case BLOG_WIDGET_TYPE_ARCHIVES:
				$this->render('Elements/blog_styles/widget/archives');
				break;
			case BLOG_WIDGET_TYPE_CATEGORIES:
				$this->render('Elements/blog_styles/widget/categories');
				break;
			case BLOG_WIDGET_TYPE_NUMBER_POSTS:
				$this->render('Elements/blog_styles/widget/number_posts');
				break;
			case BLOG_WIDGET_TYPE_TAGS:
				$this->render('Elements/blog_styles/widget/tags');
				break;
			case BLOG_WIDGET_TYPE_CALENDAR:
				$this->render('Elements/blog_styles/widget/calendar');
				break;
			case BLOG_WIDGET_TYPE_RSS:
				$this->render('Elements/blog_styles/widget/rss');
				break;
			default:
				$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'BlogStyles.widget.003', '500');
		}
	}
}