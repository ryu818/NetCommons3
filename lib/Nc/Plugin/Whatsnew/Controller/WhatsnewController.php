<?php
/**
 * WhatsnewControllerクラス
 *
 * <pre>
 * 新着情報コントローラ
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       App.Controller
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class WhatsnewController extends WhatsnewAppController {

	public function index() {
		include_once dirname(dirname(__FILE__)).'/Config/defines.inc.php';
		// ($type = 'all', $addConditions = array(), $userId = null, $roomIdArr = null, $moduleIdArr = null, $lang = null, $isDisplayComment = true, $isPublicCommunity = true, $isDisplayAllMyportal = false)
		$whatsnews = $this->Archive->findList('all', array(), null, null, null, null, true, true, false);
		foreach($whatsnews as $key => $whatsnew) {
			//
			//$url = $whatsnew['Archive']['url'];
			$unserializeUrl = unserialize($whatsnew['Archive']['url']);
			if(!$unserializeUrl) {
				$whatsnews[$key]['Archive']['url'] = $whatsnew['Archive']['url'];
			} else {
				$blockId = $whatsnew['Block']['id'];
				$unserializeUrl['block_id'] = $whatsnew['Block']['id'];
				if(isset($unserializeUrl['#'])) {
					// 先頭{$id}xxxxxという形式ならば自動変換
					$unserializeUrl['#'] = preg_replace('/_([0-9]+)(.*)/', '_'.$blockId.'$2', $unserializeUrl['#']);
					// {Id}という文字列があれば自動変換
					$unserializeUrl['#'] = str_replace('{Id}', '_'.$blockId, $unserializeUrl['#']);
				}
				$permalink = $this->Page->getPermalink($whatsnew['Page']['permalink'], $whatsnew['Page']['space_type']);
				if($permalink != '') {
					$unserializeUrl['permalink'] = $permalink;
				}

				$whatsnews[$key]['Archive']['url'] = Router::url($unserializeUrl, true);
			}
		}

		$this->set('whatsnews', $whatsnews);
	}
}