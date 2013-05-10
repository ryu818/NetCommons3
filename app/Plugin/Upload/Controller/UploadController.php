<?php
/**
 * UploadControllerクラス
 *
 * <pre>
 * ファイルアップロード処理用コントローラ
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       App.Controller
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class UploadController extends UploadAppController {

	public $components = array('Block.BlockMove', 'CheckAuth' => array('chkBlockId' => false, 'chkPlugin' => false));

	public function index() {

	}
}