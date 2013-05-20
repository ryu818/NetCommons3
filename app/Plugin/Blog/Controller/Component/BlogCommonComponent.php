<?php
/**
 * BlogCommonComponentクラス
 *
 * <pre>
 * ブログ共通コンポーネント
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Plugin.Blog.Component
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class BlogCommonComponent extends Component {
/**
 * _controller
 *
 * @var Controller
 */
	protected $_controller = null;

/**
 * startup
 *
 * @param Controller $controller
 */
	public function startup(Controller $controller) {
		$this->_controller = $controller;
	}

/**
 * ブログ記事詳細、コメント追加、コメント編集、返信時のリダイレクト先URL取得
 *  内部で$this->request->params['paging']の内容を利用しています
 * @param   Model BlogPost $blogPost
 * @param   string  $mode 'edit'コメント編集時、'reply'コメント返信時、'add'新規コメント追加時 指定されれば詳細記事コメントページングを指定
 * @param   integer $commentId saveしたコメントのid 指定されれば詳細記事コメント個所へ
 * @return  array
 * @since   v 3.0.0.0
 */
	public function getDetailRedirectUrl($blogPost, $mode = null, $commentId = null) {
		$permalink = $blogPost['BlogPost']['permalink'];
		$blogDates = strtotime($this->_controller->BlogPost->date($blogPost['BlogPost']['post_date']));

		$url = array('plugin' => 'blog', 'controller' => 'blog', 'action'=>'index',
			date('Y', $blogDates), date('m', $blogDates), date('d', $blogDates),
			$permalink);

		if(isset($mode)) {
			if($mode == 'add'){
				// ページネーションから全ページ数を取得
				$page = isset($this->_controller->request->params['paging']['BlogComment']['pageCount']) ? $this->_controller->request->params['paging']['BlogComment']['pageCount'] : 1;
			} else  {
				$page = isset($this->_controller->request->query['comment_back_page']) ? $this->_controller->request->query['comment_back_page'] : 1;
			}
			$url['page'] = $page;
		}
		if(!isset($commentId)) {
			$id = $this->_controller->id;
		} else {
			$id = $this->_controller->id. '-comment-' .$commentId;
		}
		$url['#'] = $id;

		return $url;
	}
}