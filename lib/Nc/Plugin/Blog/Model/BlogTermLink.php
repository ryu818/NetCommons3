<?php
/**
 * BlogTermLinkモデル
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Model
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class BlogTermLink extends AppModel
{

/**
 * バリデート処理
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function __construct() {
		parent::__construct();

		include_once dirname(dirname(__FILE__)).'/Config/defines.inc.php';

		/*
		 * エラーメッセージ設定
		*/
		$this->validate = array(
			'blog_post_id' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'required' => true,
					'allowEmpty' => false,
					'message' => __('The input must be a number.')
				)
			),
			'blog_term_id' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'required' => true,
					'allowEmpty' => false,
					'message' => __('The input must be a number.')
				),
			),
		);
	}

/**
 * blog_post_id, $tag_name_arrの配列による登録、更新処理
 * @param   integer $content_id
 * @param   integer $blog_post_id
 * @param   boolean $is_before_update_term_count  BlogTerm.countが更新されているかどうか(編集前の状態)
 * @param   boolean $is_after_update_term_count   BlogTerm.countを更新するかどうか(編集後の状態)
 * @param   mixed integer|array   $tag_name_arr
 * @param   string name|id(blog_term_id) $arr_column_name
 * @param   string  $taxonomy
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function saveTermLinks($content_id, $blog_post_id, $is_before_update_term_count = false,  $is_after_update_term_count = false,  $tag_name_arr = array(), $arr_column_name = 'name', $taxonomy = 'tag') {

		if(!is_array($tag_name_arr)) {
			$tag_name_arr = array($tag_name_arr);
		}
		if(count($tag_name_arr) == 0 || $tag_name_arr[0] == "") {
			return true;
		}
		$arr_column_name = ($arr_column_name != 'name') ? 'id' : $arr_column_name;
		$before_update_term_count = !$is_before_update_term_count ? 0 : 1;
		$after_update_term_count = !$is_after_update_term_count ? -1 : 1;

		$BlogTerm = ClassRegistry::init('Blog.BlogTerm');
		$blog_terms = $BlogTerm->findByBlogPostId($blog_post_id, 'LEFT', $taxonomy);
		if($arr_column_name == 'name') {
			// マスタになければ新規追加
			foreach($tag_name_arr as $tag_name) {
				$is_add = true;
				foreach($blog_terms as $blog_term) {
					if($blog_term['BlogTerm'][$arr_column_name] == $tag_name) {
						// 等しいものがマスタに存在
						$is_add = false;
						break;
					}
				}
				if($is_add) {
					$blog_term = array(
						'BlogTerm' => array(
							'content_id' => $content_id,
							'name' => $tag_name,
							'slug' => $tag_name,
							'taxonomy' => $taxonomy,
							'checked' => _OFF,
							'parent' => 0,
							'count' => ($after_update_term_count <= 0) ? 0 : $after_update_term_count,
						)
					);
					$BlogTerm->create();
					if(!$BlogTerm->save($blog_term)) {
						return false;
					}
					$blog_term['BlogTerm']['id'] = $BlogTerm->id;
					$blog_terms[] = $blog_term;
				}
			}
		}
		foreach($blog_terms as $blog_term) {
			if(isset($blog_term['BlogTermLink']['id'])) {
				// 既に登録されているが、登録されるデータにはない
				if(!in_array($blog_term['BlogTerm'][$arr_column_name], $tag_name_arr)) {
					if(!$this->delete($blog_term['BlogTermLink']['id'])) {
						// 削除
						return false;
					}
					if($before_update_term_count == 1) {
						// $before_update_term_countの値にかかわらず-1
						$blog_term['BlogTerm']['count'] = intval($blog_term['BlogTerm']['count']) - 1;
						if($blog_term['BlogTerm']['count'] >= 0) {
							$BlogTerm->create();
							if(!$BlogTerm->save($blog_term, true, array('count'))) {
								return false;
							}
						}
					}
				} else if(($before_update_term_count == 1 && $after_update_term_count == -1) ||
							($before_update_term_count == 0 && $after_update_term_count == 1)){
					$blog_term['BlogTerm']['count'] = intval($blog_term['BlogTerm']['count']) + $after_update_term_count;
					if($blog_term['BlogTerm']['count'] >= 0) {
						$BlogTerm->create();
						if(!$BlogTerm->save($blog_term, true, array('count'))) {
							return false;
						}
					}
				}
			} else if(in_array($blog_term['BlogTerm'][$arr_column_name], $tag_name_arr)) {
				// 追加(マスタは既に追加済)
				$blog_term_link = array(
					'BlogTermLink' => array(
						'content_id' => $content_id,
						'blog_post_id' => $blog_post_id,
						'blog_term_id' => $blog_term['BlogTerm']['id']
					)
				);
				$this->create();
				if(!$this->save($blog_term_link)) {
					return false;
				}
				if($after_update_term_count == 1) {
					$blog_term['BlogTerm']['count'] = intval($blog_term['BlogTerm']['count']) + $after_update_term_count;
					if($blog_term['BlogTerm']['count'] >= 0) {
						$BlogTerm->create();
						if(!$BlogTerm->save($blog_term, true, array('count'))) {
							return false;
						}
					}
				}
			}
		}
		return true;
	}
}