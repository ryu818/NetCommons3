<?php
/**
 * AnnouncementOperationsComponentクラス
 *
 * <pre>
 * お知らせ用削除、コピー、移動、ショートカット等操作クラス
 * 削除用関数等は、親から呼ばれるため、Model等のクラスは、このクラス内で完結している
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Plugin.Announcement.Component
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class AnnouncementOperationsComponent extends Object {

	public $Content = null;
	public $Htmlarea = null;

/**
 * 初期処理
 *
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function startup() {
		App::uses('Content', 'Model');
		App::uses('Htmlarea', 'Model');

		$this->Content = new Content();
		$this->Htmlarea = new Htmlarea();
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
	//	public function delete_block($block, $content, $to_page) {
	//		return true;
	//	}

/**
 * コンテンツ削除時に呼ばれる関数
 *
 * @param   Model Content 削除コンテンツ $content
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function delete($content) {
		if(isset($content['Content'])) {
			$condition = array('Htmlarea.content_id' => $content['Content']['master_id']);
			if(!$this->Htmlarea->deleteAll($condition)) {
				return false;
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
//	public function shortcut($from_block, $to_block, $from_content, $to_content, $from_page, $to_page) {
//		return true;
//	}


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
		$htmlarea = $this->Htmlarea->findByContentId($from_content['Content']['id']);
		if(isset($htmlarea['Htmlarea'])) {
			unset($htmlarea['Htmlarea']['id']);
			$htmlarea['Htmlarea']['content_id'] = $to_content['Content']['id'];
			if(!$this->Htmlarea->save($htmlarea, false)) {
				return false;
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