<?php
/**
 * CommunityDownloadComponentクラス
 *
 * <pre>
 * コミュニティーの写真、紹介部分のWYSIWYGにセットされたファイルの権限チェック
 * コミュニティーに登録された段階で、ログインしていなくても閲覧を許すため、trueのみ返す。
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       App.Plugin.Page.Controller.Component
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class CommunityDownloadComponent extends Component {

/**
 * ダウンロード権限チェック処理（写真）
 * @param   array $uploadLink
 * @param   int $fileOwnerId
 * @param   string $downloadPassword
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function check($uploadLink, $fileOwnerId, $downloadPassword=null) {
		return true;
	}

/**
 * ダウンロード権限チェック処理(紹介)
 * @param   array $uploadLink
 * @param   int $fileOwnerId
 * @param   string $downloadPassword
 * @return  boolean
 * @since   v 3.0.0.0
 */
	public function checkRevision($uploadLink, $fileOwnerId, $downloadPassword=null) {
		return true;
	}

}