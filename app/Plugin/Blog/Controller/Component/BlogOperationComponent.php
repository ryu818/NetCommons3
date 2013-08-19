<?php
/**
 * BlogOperationComponentクラス
 *
 * <pre>
 * ブログ用削除、コピー、移動、ショートカット等操作クラス
 * 削除用関数等は、親から呼ばれるため、Model等のクラスは、このクラス内で完結している
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Plugin.Blog.Component
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class BlogOperationComponent extends Component {

	public $Content = null;
	public $Revision = null;

	public $Blog = null;
	public $BlogComment = null;
	public $BlogPost = null;
	public $BlogStyle = null;
	public $BlogTerm = null;
	public $BlogTermLink = null;

/**
 * 初期処理
 *
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function startup() {
		$this->Content = ClassRegistry::init('Content');
		$this->Revision = ClassRegistry::init('Revision');
		$this->Archive = ClassRegistry::init('Archive');

		$this->Blog = ClassRegistry::init('Blog.Blog');
		$this->BlogComment = ClassRegistry::init('Blog.BlogComment');
		$this->BlogPost = ClassRegistry::init('Blog.BlogPost');
		$this->BlogStyle = ClassRegistry::init('Blog.BlogStyle');
		$this->BlogTerm = ClassRegistry::init('Blog.BlogTerm');
		$this->BlogTermLink = ClassRegistry::init('Blog.BlogTermLink');

		$this->BlogPost->unbindModel( array( 'belongsTo' => array_keys( $this->BlogPost->belongsTo ) ) );
	}

/**
 * ブロック削除実行時に呼ばれる関数
 *
 * @param   Model Block   削除ブロック
 * @param   Model Content 削除コンテンツ
 * @param   Model Page    削除先ページ
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function delete_block($block, $content, $to_page) {
		$condition = array('block_id' => $block['Block']['id']);
		if(!$this->BlogStyle->deleteAll($condition)) {
			return false;
		}
		return true;
	}

/**
 * コンテンツ削除時に呼ばれる関数
 *
 * @param   Model Content 削除コンテンツ $content
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function delete($content) {
		if(isset($content['Content'])) {
			$tables = array('Revision', 'Archive', 'Blog', 'BlogComment', 'BlogPost', 'BlogTerm', 'BlogTermLink');
			foreach($tables as $table) {
				$condition = array($table.'.content_id' => $content['Content']['master_id']);
				if(!$this->{$table}->deleteAll($condition)) {
					return false;
				}
			}
		}
		return true;
	}

/**
 * ショートカット実行時に呼ばれる関数
 *
 * @param   Model Block   移動元ブロック
 * @param   Model Block   移動先ブロック
 * @param   Model Content 移動元コンテンツ
 * @param   Model Content 移動先コンテンツ
 * @param   Model Page    移動元ページ
 * @param   Model Page    移動先ページ
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function shortcut($from_block, $to_block, $from_content, $to_content, $from_page, $to_page) {
		$condition = array('block_id' => $from_block['Block']['id']);
		$blog_styles = $this->BlogStyle->find('all', array('conditions' => $condition));
		if(isset($blog_styles[0])) {
			$this->BlogStyle->initInsert = true;
			foreach($blog_styles as $blog_style) {
				unset($blog_style['BlogStyle']['id']);
				$blog_style['BlogStyle']['block_id'] = $to_block['Block']['id'];
				$this->BlogStyle->create();
				if(!$this->BlogStyle->save($blog_style)) {
					return false;
				}
			}
		}
		return true;
	}


/**
 * コピー(ペースト)実行時に呼ばれる関数
 *
 * @param   Model Block   移動元ブロック
 * @param   Model Block   移動先ブロック
 * @param   Model Content 移動元コンテンツ
 * @param   Model Content 移動先コンテンツ
 * @param   Model Page    移動元ページ
 * @param   Model Page    移動先ページ
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function paste($from_block, $to_block, $from_content, $to_content, $from_page, $to_page) {
		if(!$this->shortcut($from_block, $to_block, $from_content, $to_content, $from_page, $to_page)) {
			return false;
		}
		// lftとrghtの自動設定をやめるためにTreeBehaviorを無効化
		if ($this->BlogComment->Behaviors->enabled('Tree')) {
			$this->BlogComment->Behaviors->disable('Tree');
		}

		$newPostIdArr = array();
		$newCommentIdArr = array();
		$newGroupIdArr = array();
		$groupId = $newGroupId = 0;
		$tables = array('Revision', 'Blog', 'BlogPost', 'BlogComment', 'BlogTerm', 'BlogTermLink', 'Archive');
		foreach($tables as $table) {
			$condition = array($table.'.content_id' => $from_content['Content']['master_id']);
			$datas = $this->{$table}->find('all', array(
				'recursive' => -1,
				'conditions' => $condition,
				'order' => array($this->{$table}->primaryKey => 'ASC')
			));
			foreach($datas as $data) {
				$beforeId = $data[$table]['id'];
				if($table == 'Revision') {
					if($groupId != $data[$table]['group_id']) {
						$groupId = $data[$table]['group_id'];
						$data[$table]['group_id'] = 0;
					} else {
						$data[$table]['group_id'] = $newGroupId;
					}
				} else if($table == 'BlogPost') {
					$data[$table]['revision_group_id'] = $newGroupIdArr[$data[$table]['revision_group_id']];
				} else if($table == 'BlogComment') {
					if(!isset($newPostIdArr[$data[$table]['blog_post_id']])) {
						continue;
					}
					$data[$table]['blog_post_id'] = $newPostIdArr[$data[$table]['blog_post_id']];
					if(isset($data[$table]['parent_id']) && isset($newCommentIdArr[$data[$table]['parent_id']])) {
						$data[$table]['parent_id'] = $newCommentIdArr[$data[$table]['parent_id']];
					}
				} else if($table == 'Archive') {
					if($data[$table]['model_name'] == 'BlogPost') {
						$data[$table]['parent_id'] = $data[$table]['unique_id'] = $newPostIdArr[$data[$table]['parent_id']];
					} else {
						$data[$table]['parent_id'] = $newPostIdArr[$data[$table]['parent_id']];
						$data[$table]['unique_id'] = $newCommentIdArr[$data[$table]['unique_id']];
					}
				}

				unset($data[$table]['id']);
				$data[$table]['content_id'] = $to_content['Content']['id'];
				$this->{$table}->create();
				if(!$this->{$table}->save($data)) {
					return false;
				}
				if($table == 'BlogPost') {
					$newPostIdArr[$beforeId] = $this->{$table}->id;
				} else if($table == 'BlogComment') {
					$newCommentIdArr[$beforeId] = $this->{$table}->id;
				} else if($table == 'Revision' && $data['Revision']['group_id'] == 0) {
					$newGroupId = $this->{$table}->id;
					$newGroupIdArr[$groupId] = $newGroupId;
				}
			}
		}
		return true;
	}

/**
 * ブロック追加実行時に呼ばれる関数
 *
 * @param   Model Block   追加ブロック
 * @param   Model Content 追加コンテンツ
 * @param   Model Page    追加先ページ
 * @return  boolean
 * @since   v 3.0.0.0
 */
//	public function add_block($block, $content, $to_page) {
//		return true;
//	}

/**
 * 別ルームに移動実行時に呼ばれる関数
 *
 * @param   Model Block   移動元ブロック
 * @param   Model Block   移動先ブロック
 * @param   Model Content 移動元コンテンツ
 * @param   Model Content 移動先コンテンツ
 * @param   Model Page    移動元ページ
 * @param   Model Page    移動先ページ
 * @return  boolean
 * @since   v 3.0.0.0
 */
//	public function move($from_block, $to_block, $from_content, $to_content, $from_page, $to_page) {
//		return true;
//	}
}