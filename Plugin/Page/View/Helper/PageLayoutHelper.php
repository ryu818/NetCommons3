<?php
/**
 * ページレイアウト用ヘルパー
 *
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       App.Plugin.Page.View.Helper
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class PageLayoutHelper extends AppHelper {


/**
 * レイアウト画像からタイトル取得
 *
 * @param   string $fileName
 * @return  string
 * @since   v 3.0.0.0
 */
	public function getTitle($fileName) {
		$fileNameArr = explode('.', $fileName);
		$columnsArr = explode('_', $fileNameArr[0]);
		$title = '';
		if($columnsArr[0] == _OFF && $columnsArr[1] == _OFF && $columnsArr[2] == _OFF && $columnsArr[3] == _OFF) {
			$title = 'None';
		} else if($columnsArr[0] == _ON && $columnsArr[1] == _OFF && $columnsArr[2] == _OFF && $columnsArr[3] == _ON) {
			$title = 'Display the header and footer';
		} else if($columnsArr[0] == _ON && $columnsArr[1] == _ON && $columnsArr[2] == _OFF && $columnsArr[3] == _ON) {
			$title = 'Display the header and left column, footer';
		} else if($columnsArr[0] == _ON && $columnsArr[1] == _OFF && $columnsArr[2] == _ON && $columnsArr[3] == _ON) {
			$title = 'Display the header and right column, footer';
		} else if($columnsArr[0] == _ON && $columnsArr[1] == _ON && $columnsArr[2] == _ON && $columnsArr[3] == _ON) {
			$title = 'Display the header and left column, right column, footer';
		} else if($columnsArr[0] == _OFF && $columnsArr[1] == _ON && $columnsArr[2] == _OFF && $columnsArr[3] == _ON) {
			$title = 'Display the left column and footer';
		} else if($columnsArr[0] == _OFF && $columnsArr[1] == _OFF && $columnsArr[2] == _ON && $columnsArr[3] == _ON) {
			$title = 'Display the right column and footer';
		} else if($columnsArr[0] == _OFF && $columnsArr[1] == _ON && $columnsArr[2] == _ON && $columnsArr[3] == _ON) {
			$title = 'Display the left column and right column, footer';
		} else if($columnsArr[0] == _ON && $columnsArr[1] == _OFF && $columnsArr[2] == _OFF && $columnsArr[3] == _OFF) {
			$title = 'Display the left header';
		} else if($columnsArr[0] == _ON && $columnsArr[1] == _ON && $columnsArr[2] == _OFF && $columnsArr[3] == _OFF) {
			$title = 'Display the header and left column';
		} else if($columnsArr[0] == _ON && $columnsArr[1] == _OFF && $columnsArr[2] == _ON && $columnsArr[3] == _OFF) {
			$title = 'Display the header and right column';
		} else if($columnsArr[0] == _ON && $columnsArr[1] == _ON && $columnsArr[2] == _ON && $columnsArr[3] == _OFF) {
			$title = 'Display the header and left column, right column';
		} else if($columnsArr[0] == _OFF && $columnsArr[1] == _ON && $columnsArr[2] == _OFF && $columnsArr[3] == _OFF) {
			$title = 'Display the left column';
		} else if($columnsArr[0] == _OFF && $columnsArr[1] == _OFF && $columnsArr[2] == _ON && $columnsArr[3] == _OFF) {
			$title = 'Display the right column';
		} else if($columnsArr[0] == _OFF && $columnsArr[1] == _ON && $columnsArr[2] == _ON && $columnsArr[3] == _OFF) {
			$title = 'Display the left column and right column';
		} else if($columnsArr[0] == _OFF && $columnsArr[1] == _OFF && $columnsArr[2] == _OFF && $columnsArr[3] == _ON) {
			$title = 'Display the footer';
		}
		return $title;
	}
}