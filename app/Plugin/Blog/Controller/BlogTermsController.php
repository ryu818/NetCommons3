<?php
/**
 * BlogTermsControllerクラス
 *
 * <pre>
 * ブログカテゴリー、タグ追加・編集・削除コントローラ
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       App.Controller
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class BlogTermsController extends BlogAppController {
/**
 * Component name
 *
 * @var array
 */
	public $components = array('CheckAuth' => array('allowAuth' => NC_AUTH_GENERAL));

/**
 * ブログカテゴリー追加(投稿画面からの追加)
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function add_category($post_id = null) {
		// TODO:権限チェック未作成
		if($this->request->is('post')) {
			if(!isset($this->request->data['BlogTerm']['name'])) {
				$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'BlogTerms.add_category.001', '500');
				return;
			}

			$parent = !isset($this->request->data['BlogTerm']['parent']) ? 0 : intval($this->request->data['BlogTerm']['parent']);

			$blog_term = array('BlogTerm' => array(
				'id' => 0,
				'content_id' => $this->content_id,
				'name' => $this->request->data['BlogTerm']['name'],
				'slug' => $this->request->data['BlogTerm']['name'],
				'taxonomy' => 'category',
				'checked' => _OFF,
				'parent' => $parent,
				'count' => 0
			));

			$this->BlogTerm->set($blog_term);
			if(!$this->BlogTerm->validates()) {
				/* 同名の名称のエラーの場合、エラーメッセージを表示させない。 */
				foreach($this->BlogTerm->validationErrors as $field => $values) {
					if($field == 'name' || $field == 'slug') {
						foreach($values as $key => $value) {
							if($value == __('The same name is already in use.Please choose another one.')) {
								if(count($this->BlogTerm->validationErrors[$field]) == 1) {
									unset($this->BlogTerm->validationErrors[$field]);
								} else {
									unset($this->BlogTerm->validationErrors[$field][$key]);
								}
							}
						}
					}
				}
			} else {
				$this->BlogTerm->save($blog_term, false);
			}
			// 現状、選択済のカテゴリー＋追加したカテゴリーをマージして、表示させる
			if(count($this->BlogTerm->validationErrors) == 0 && $this->BlogTerm->id == false) {
				// 再取得
				$conditions = array(
					'BlogTerm.content_id' => $this->content_id,
					'BlogTerm.taxonomy' => 'category',
					'BlogTerm.name' => $this->request->data['BlogTerm']['name'],
				);
				$params = array('conditions' => $conditions);
				$blog_term = $this->BlogTerm->find('first', $params);
				$this->BlogTerm->id = $blog_term['BlogTerm']['id'];
			}
			// 現在、追加済カテゴリー
			$active_category_arr = array();
			if(isset($this->request->data['BlogTermLink']['category_id']) && count($this->request->data['BlogTermLink']['category_id']) > 0) {
				$active_category_arr = $this->request->data['BlogTermLink']['category_id'];
			}
			if($this->BlogTerm->id != false) {
				$active_category_arr[] = $this->BlogTerm->id;
			}

			$params = array('conditions' => array('content_id' => $this->content_id));
			$blog = $this->Blog->find('first', $params);

			$this->set('blog', $blog);

			$categories = $this->BlogTerm->findCategories($this->content_id, isset($post_id) ? $post_id : null, $active_category_arr);
			$this->set('categories', $categories);
			$this->set('post_id', $post_id);
			$this->render('Elements/blog_posts/add_category');
			return;
		}
	}
}