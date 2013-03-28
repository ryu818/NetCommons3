<?php
/**
 * BlogPostsControllerクラス
 *
 * <pre>
 * ブログ記事投稿画面用コントローラ
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       App.Controller
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class BlogPostsController extends BlogAppController {

/**
 * Component name
 *
 * @var array
 */
	public $components = array('CheckAuth' => array('allowAuth' => NC_AUTH_GENERAL));

/**
 * Model name
 *
 * @var array
 */
	public $uses = array('Blog.BlogTermLink', 'Htmlarea');

/**
 * ブログ記事投稿表示・登録
 * @param   integer $post_id
 * @return  void
 * @since   v 3.0.0.0
 */
	public function index($post_id = null) {
		// TODO:権限チェックが未作成
		// TODO:承認機能未実装
		// 		未承認の場合、最初の投稿では一時保存へ。その後、現在のstatusを引き継ぐ。
		// TODO:email送信未実装

		$blog = $this->Blog->find('first', array('conditions' => array('content_id' => $this->content_id)));
		if(!isset($blog['Blog'])) {
			$this->flash(__('Content not found.'), null, 'BlogPost.index.001', '404');
			return;
		}

		if(isset($post_id)) {
			// 編集
			$blog_post = $this->BlogPost->findById($post_id);
			if(!isset($blog_post['BlogPost'])) {
				$this->flash(__('Content not found.'), null, 'BlogPost.index.002', '404');
				return;
			}
			if($blog_post['BlogPost']['is_future'] == _ON || $blog_post['BlogPost']['status'] == NC_STATUS_TEMPORARY) {
				$is_before_update_term_count = false;
			} else {
				$is_before_update_term_count = true;
			}

			// 履歴情報 TODO:現状：未使用
			$params = array('conditions' => array('revision_parent' => $blog_post['BlogPost']['htmlarea_id']));
			$htmlareas = $this->Htmlarea->find('all', $params);
			$this->set('blog_revisions', $htmlareas);
		} else {
			$blog_term_links = array();
			$blog_post = $this->BlogPost->findDefault($this->content_id);
			$is_before_update_term_count = false;
		}
		$active_category_arr = null;
		$active_tag_arr = null;
		if($this->request->is('post')) {
			// 登録処理
			if(!isset($this->request->data['BlogPost']) || !isset($this->request->data['Htmlarea']['content'])) {
				$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'BlogPosts.index.003', '500');
				return;
			}
			if(!isset($this->request->data['is_temporally']) || $this->request->data['is_temporally'] == _OFF) {
				$is_temporally = _OFF;
			} else {
				$is_temporally = _ON;
			}
			if(isset($this->request->data['BlogPost']['id'])) {
				// リクエストからidの変更は許さない。
				unset($this->request->data['BlogPost']['id']);
			}
			$htmlarea_id = $blog_post['BlogPost']['htmlarea_id'];
			$blog_post['BlogPost'] = array_merge($blog_post['BlogPost'], $this->request->data['BlogPost']);
			$blog_post['BlogPost']['content_id'] = $this->content_id;
			$blog_post['BlogPost']['permalink'] = $blog_post['BlogPost']['title'];	// TODO:仮でtitleをセット
			$blog_post['BlogPost']['status'] = ($is_temporally) ? NC_STATUS_TEMPORARY : NC_STATUS_PUBLISH;
			$blog_post['BlogPost']['approved_flag'] = _OFF;	// TODO:仮でセット
			$blog_post['BlogPost']['htmlarea_id'] = 0;

			$blog_post['Htmlarea']['content'] = $this->request->data['Htmlarea']['content'];

			$fieldList = array(
				'content_id', 'post_date', 'title', 'permalink', 'icon_name', 'htmlarea_id', 'status', 'approved_flag', 'post_password', 'trackback_link',
			);

			$htmlarea = array(
				'Htmlarea' => array(
					'revision_parent' => $htmlarea_id,
					'revision_name' => ($is_temporally) ? 'draft' : 'publish',
					'content_id' => $this->content_id,
					'content' => $this->request->data['Htmlarea']['content'],
					'non_approved_content' => '',
				)
			);

			$fieldListHtmlarea = array(
				'revision_parent', 'revision_name', 'content_id', 'content', 'non_approved_content',
			);

			$active_category_arr = (isset($this->request->data['BlogTermLink']) && isset($this->request->data['BlogTermLink']['category_id'])) ?
				$this->request->data['BlogTermLink']['category_id'] : array();
			$active_tag_arr = (isset($this->request->data['BlogTermLink']) && isset($this->request->data['BlogTermLink']['tag_name'])) ?
				$this->request->data['BlogTermLink']['tag_name'] : array();

			$this->Htmlarea->set($htmlarea);
			$this->BlogPost->set($blog_post);
			if($this->BlogPost->validates(array('fieldList' => $fieldList)) && $this->Htmlarea->validates(array('fieldList' => $fieldListHtmlarea))) {
				$this->Htmlarea->save($htmlarea, false, $fieldListHtmlarea);
				$blog_post['BlogPost']['htmlarea_id'] = $this->Htmlarea->id;
				if(strtotime($this->BlogPost->date($blog_post['BlogPost']['post_date'])) > strtotime($this->BlogPost->nowDate())) {
					// 未来の記事
					$blog_post['BlogPost']['is_future'] = _ON;
				} else {
					$blog_post['BlogPost']['is_future'] = _OFF;
				}


				$this->BlogPost->save($blog_post, false, $fieldList);
				if(empty($blog_post['BlogPost']['id'])) {
					$this->Session->setFlash(__('Has been successfully registered.'));
				} else {
					$this->Session->setFlash(__('Has been successfully updated.'));
				}

				if($blog_post['BlogPost']['is_future'] == _ON || $blog_post['BlogPost']['status'] == NC_STATUS_TEMPORARY) {
					$is_after_update_term_count = false;
				} else {
					$is_after_update_term_count = true;
				}
				// カテゴリー登録
				if(!$this->BlogTermLink->saveTermLinks($this->content_id, $this->BlogPost->id, $is_before_update_term_count, $is_after_update_term_count,
					$active_category_arr, 'id', 'category')) {
					$this->flash(__('Failed to register the database, (%s).', 'blog_term_links'), null, 'BlogPost.index.003', '500');
					return;
				}
				// タグ登録
				if(!$this->BlogTermLink->saveTermLinks($this->content_id, $this->BlogPost->id, $is_before_update_term_count, $is_after_update_term_count,
						$active_tag_arr, 'name', 'tag')) {
					$this->flash(__('Failed to register the database, (%s).', 'blog_term_links'), null, 'BlogPost.index.004', '500');
					return;
				}

				if(!$is_temporally) {
					// 決定の場合、リダイレクト
					$this->redirect(array('controller' => 'blog', '#' => $this->id));
					return;
				} else if(!isset($post_id)) {
					$this->redirect(array('controller' => 'blog_posts', $this->BlogPost->id, '#' => $this->id));
					return;
				}
			}
		}

		$this->set('blog', $blog);
		$this->set('blog_post', $blog_post);

		// カテゴリ一覧、タグ一覧
		$categories = $this->BlogTerm->findCategories($this->content_id, isset($post_id) ? $post_id : null, $active_category_arr);
		$tags = $this->BlogTerm->findTags($this->content_id, isset($post_id) ? $post_id : null, $active_tag_arr);
		$this->set('categories', $categories);
		$this->set('tags', $tags);
		$this->set('post_id', $post_id);
	}
}