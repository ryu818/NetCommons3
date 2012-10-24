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
 * @since
 * @access  public
 */
	public function startup() {
		App::uses('Content', 'Model');
		App::uses('Htmlarea', 'Model');

		$this->Content = new Content();
		$this->Htmlarea = new Htmlarea();
	}

/**
 * ブロック削除時に呼ばれる関数
 *
 * @param   array 削除ブロック $block
 * @return  void
 * @since
 * @access  public
 */
	//public function delete_block($block) {
	//	return $this->delete($block);
	//}

/**
 * コンテンツ削除時に呼ばれる関数
 *
 * @param   array 削除コンテンツ $content
 * @return  void
 * @since
 * @access  public
 */
	public function delete($content) {
		if(isset($content['Content'])) {
			$condition = array('Htmlarea.master_id' => $content['Content']['master_id']);
			if(!$this->Htmlarea->deleteAll($condition)) {
				return false;
			}
		}
		return true;
	}

/**
 * ショートカット実行時に呼ばれる関数
 *
 * @param   array 移動元ブロック $block
 * @param   array 移動先ブロック $block
 * @param   array 移動元ページ   $page
 * @param   array 移動先ページ   $page
 * @return  void
 * @since
 * @access  public
 */
//	public function shortcut($from_block, $to_block, $from_page, $to_page) {
//		return true;
//	}


/**
 * コピー(ペースト)実行時に呼ばれる関数
 *
 * @param   array 移動元ブロック $block
 * @param   array 移動先ブロック $block
 * @param   array 移動元ページ   $page
 * @param   array 移動先ページ   $page
 * @return  void
 * @since
 * @access  public
 */
	public function paste($from_block, $to_block, $from_page, $to_page) {
		$htmlarea = $this->Htmlarea->findByMasterId($from_block['Content']['master_id']);
		if(isset($htmlarea['Htmlarea'])) {
			unset($htmlarea['Htmlarea']['id']);
			$htmlarea['Htmlarea']['master_id'] = $to_block['Content']['master_id'];
			if(!$this->Htmlarea->save($htmlarea, false)) {
				return false;
			}
		}
		return true;
	}


/**
 * 移動実行時に呼ばれる関数
 *
 * @param   array 移動元ブロック $block
 * @param   array 移動元ページ   $page
 * @param   array 移動先ページ   $page
 * @return  void
 * @since
 * @access  public
 */
//	public function move($from_block, $from_page, $to_page) {
//		return true;
//	}
/**
 * モジュールインストール時に呼ばれる関数
 *
 * @param   integer $module_id
 * @return  void
 * @since
 * @access  public
 */
//	public function install($module_id) {
//		return true;
//	}

/**
 * モジュールアップデート時に呼ばれる関数
 *
 * @param   integer $module_id
 * @return  void
 * @since
 * @access  public
 */
//	public function update($module_id) {
//		return true;
//	}

/**
 * モジュールアンインストール時に呼ばれる関数
 *
 * @param   integer $module_id
 * @return  void
 * @since
 * @access  public
 */
//	public function uninstall($module_id) {
//		return true;
//	}

}