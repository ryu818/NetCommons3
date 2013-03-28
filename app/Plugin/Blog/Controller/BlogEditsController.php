<?php
/**
 * BlogEditsControllerクラス
 *
 * <pre>
 * ブログ編集画面用コントローラ
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       App.Controller
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class BlogEditsController extends BlogAppController {
/**
 * Component name
 *
 * @var array
 */
	public $components = array('CheckAuth' => array('allowAuth' => NC_AUTH_CHIEF));

/**
 * 編集画面か否か（セッティングモードONの場合の上部、編集ボタンをリンク先を変更するため）
 * Default:false
 * @var boolean
 */
	protected $nc_is_edit = true;

/**
 * ブログ編集画面
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function index() {
		$params = array('conditions' => array('content_id' => $this->content_id));
		$blog = $this->Blog->find('first', $params);
		if(empty($blog)) {
			$blog = $this->Blog->findDefault($this->content_id);
		}

		if($this->request->is('post')) {
			if(!isset($this->request->data['Blog']) || !isset($this->request->data['Content']['title'])) {
				$this->flash(__('Unauthorized request.<br />Please reload the page.'), null, 'Blog.index.001', '500');
				return;
			}

			// 登録処理
			$content['Content'] = array(
				'id' => $this->content_id,
				'title' => $this->request->data['Content']['title']
			);
			$blog['Blog'] = array_merge($blog['Blog'], $this->request->data['Blog']);
			$blog['Blog']['content_id'] = $this->content_id;
			// エラー時：アクティブタブに移動させるため
			$fieldLists = array(
				0 => array('post_hierarchy', 'term_hierarchy', 'vote_flag', 'term_hierarchy', 'sns_flag', 'new_period',
							'mail_flag', 'mail_subject', 'mail_body',),
				1 => array('comment_flag', 'comment_members', 'comment_required_name', 'comment_image_auth', 'comment_hierarchy',
							'comment_mail_flag', 'comment_mail_hierarchy', 'comment_mail_subject', 'comment_mail_body',),
				2 => array('trackback_transmit_flag', 'trackback_transmit_article', 'trackback_receive_flag', 'transmit_blog_name',),
				3 => array('approved_flag', 'approved_mail_flag', 'approved_mail_subject', 'approved_mail_body',
							'comment_approved_flag', 'comment_approved_mail_flag', 'comment_approved_mail_subject', 'comment_approved_mail_body',)
			);
			$fieldList = array_merge(array('content_id'), $fieldLists[0], $fieldLists[1], $fieldLists[2], $fieldLists[3]);

			$this->Content->set($content);
			$this->Blog->set($blog);
			if($this->Content->validates(array('fieldList' => array('title'))) && $this->Blog->validates(array('fieldList' => $fieldList))) {
				$this->Content->save($content, false, array('title'));
				$this->Blog->save($blog, false, $fieldList);
				if(empty($blog['Blog']['id'])) {
					$this->Session->setFlash(__('Has been successfully registered.'));
				} else {
					$this->Session->setFlash(__('Has been successfully updated.'));
				}
				$this->redirect(array('controller' => 'blog', '#' => $this->id));
				return;
			} else {
				// error
				$this->nc_block['Content']['title'] = $this->request->data['Content']['title'];
				foreach($this->Blog->validationErrors as $key => $value) {
					foreach($fieldLists as $index => $buf_fieldList) {
						if(in_array($key, $buf_fieldList)) {
							$active_tab = $index;
							break;
						}
					}
					if(isset($active_tab)) {
						break;
					}
				}
				if(!isset($active_tab)) {
					$active_tab = 0;
				}
				$this->set('active_tab', $active_tab);
			}
		}

		$this->set('blog', $blog);
	}
}