<?php
/**
 * BlogTrackbackComponentクラス
 *
 * <pre>
 * トラックバック用コンポーネント
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Plugin.Blog.Component
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class BlogTrackbackComponent extends Component {
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
	 * トラックバック返信
	 *
	 * @param $error エラーなし:0 エラーあり:1
	 * @param $errorMessage エラーメッセージ
	 * @return  void
	 * @since   v 3.0.0.0
	 */
	public function rtnTrackback($error, $errorMessage = null) {
		$this->_controller->viewClass = 'Xml';
		$this->_controller->set('error', $error);
		$this->_controller->set('error_message', $errorMessage);
		$this->_controller->render('trackback');
		$this->_controller->response->send();
		$this->_controller->_stop();
	}

	/**
	 * トラックバック保存
	 *
	 * @param Model BlogPost $blogPost
	 * @return  array(boolean, string, Model BlogComment $tbComment)
	 *     boolean:成功時true, string:エラーメッセージ, Model:成功時:BlogComment
	 * @since   v 3.0.0.0
	 */
	public function trackbackSave($blogPost) {
		$url = $this->_controller->request->data['url'];
		$title = isset($this->_controller->request->data['title']) ? $this->_controller->request->data['title'] : '';
		$excerpt = isset($this->_controller->request->data['excerpt']) ? $this->_controller->request->data['excerpt'] : '';
		$blogName = isset($this->_controller->request->data['blog_name']) ? $this->_controller->request->data['blog_name'] : '';

		$charset = isset($this->_controller->request->data['charset']) ? $this->_controller->request->data['charset'] : '';
		if (!empty($charset)) {
			$charset = str_replace( array(',', ' '), '', strtoupper( trim($charset) ) );
		} else {
			$charset = 'ASCII, UTF-8, ISO-8859-1, JIS, EUC-JP, SJIS';
		}
		if ( false !== strpos($charset, 'UTF-7') ) {
			$errorMessage = __d('blog', 'It is illegal trackback.');
			return array(BLOG_TRACKBACK_FAILED, $errorMessage);
		}

		$title = mb_convert_encoding($title, Configure::read('App.encoding'), $charset);
		$excerpt = mb_convert_encoding($excerpt, Configure::read('App.encoding'), $charset);
		$blogName = mb_convert_encoding($blogName, Configure::read('App.encoding'), $charset);
		// 文字列の切り詰め
		$title = mb_substr($title, 0, BLOG_TRACKBACK_TITLE_MAX_LENGTH, Configure::read('App.encoding'));
		$excerpt = mb_substr($excerpt, 0, BLOG_TRACKBACK_EXCERPT_MAX_LENGTH, Configure::read('App.encoding'));
		$blogName = mb_substr($blogName, 0, BLOG_TRACKBACK_BLOGNAME_MAX_LENGTH, Configure::read('App.encoding'));

		$tbComment = $this->_controller->BlogComment->findDefault($this->_controller->content_id, $blogPost['BlogPost']['id']);
		$tbComment['BlogComment']['author_url'] = $url;
		$tbComment['BlogComment']['title'] = $title;
		$tbComment['BlogComment']['comment'] = $excerpt;
		$tbComment['BlogComment']['blog_name'] = $blogName;
		$tbComment['BlogComment']['comment_type'] = BLOG_TRACKBACK_TYPE_TRACKBACK;
		$tbComment['BlogComment']['author_ip'] = $this->_controller->request->clientIp(false);

		if($blogPost['Blog']['trackback_approved_flag'] == _ON) {
			$tbComment['BlogComment']['is_approved'] = NC_APPROVED_FLAG_OFF;
		}
		$fieldList = array('content_id', 'blog_post_id', 'comment', 'blog_name', 'title', 'author_url', 'author_ip', 'is_approved', 'comment_type');

		$this->_controller->BlogComment->set($tbComment);
		if(!$this->_controller->BlogComment->validates(array('fieldList' => $fieldList))) {
			$errorMessage = __d('blog', 'It is illegal trackback.');
			return array(BLOG_TRACKBACK_FAILED, $errorMessage);
		}
		$this->_controller->BlogComment->Behaviors->attach('Tree', array(
				'scope' => array('BlogComment.blog_post_id' => $blogPost['BlogPost']['id'])
		));
		$trackbackComment = $this->_controller->BlogComment->save($tbComment, false, $fieldList);

		if(!$trackbackComment) {
			$errorMessage = __('Failed to execute the %s.', __d('blog', 'TrackBack'));
			return array(BLOG_TRACKBACK_FAILED, $errorMessage);
		}
		if(!$this->_controller->BlogPost->adjustTrackbackCount('add', $blogPost['BlogPost']['id'], $tbComment['BlogComment']['is_approved'], $blogPost['Blog']['trackback_approved_flag'])) {
			$errorMessage = __('Failed to execute the %s.', __d('blog', 'TrackBack'));
			return array(BLOG_TRACKBACK_FAILED, $errorMessage);
		}

		return array(BLOG_TRACKBACK_SUCCEED, '', $trackbackComment);

	}
}