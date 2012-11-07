<?php
/**
 * PagesettingControllerクラス
 *
 * <pre>
 * ページ情報表示・編集用コントローラ
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       App.Controller
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class PagesettingController extends PagesettingAppController {
	/**
	 * Model name
	 *
	 * @var array
	 */
	public $uses = array('PageStyle');

	/**
	 * page_id
	 * @var integer
	 */
	public $page_id = null;

	public function index() {

	}

	public function meta() {

	}

	public function theme() {

	}

	public function layout() {
		
	}
	
	public function style() {
		// ページ情報を取得
		$page = $this->Page->findById($this->page_id);
		// TODO ノードを基にスタイル情報を取得
		$page_style = $this->PageStyle->findByStylePageId($this->page_id);

		if ($this->request->is('post')) {
			$content = 'body {'.
					'color: '.$this->request['data']['color'].';'.
					'background-color: '.$this->request['data']['bgcolor'].';'.
					'}';
			// 既存のCSSファイルを削除
			if (!empty($page_style['PageStyle']['file'])) {
				$this->PageStyle->deleteCssFile($page_style['PageStyle']['file']);
			}
			// webroot/theme/page_styles/下にCSSファイルを生成
			$file = $this->PageStyle->createCssFile($content);
			$data = array(
				'id' => (isset($page_style['PageStyle']['id'])) ? $page_style['PageStyle']['id'] : null,
				'style_page_id' => $this->page_id,
				'color' => $this->request['data']['color'],
				'bgcolor' => $this->request['data']['bgcolor'],
				'file' => $file
			);
			$this->PageStyle->save($data);
		}

		$this->set('page', $page['Page']);
		$this->set('page_style', $page_style['PageStyle']);
	}
}