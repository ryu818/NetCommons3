<?php
/**
 * PageinfoAssetモデル
 *
 * <pre>
 *  ページスタイルCSSファイル生成モデル
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Plugin.Block.Model
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class PageinfoAsset extends Asset {
	public $name = 'PageinfoAsset';
    public $useTable = 'assets';

	public function findRowCount($page_id, $parent_id, $col_num)
	{
		$count_row_num = $this->find('count', array(
			'fields' => 'COUNT(*) as count',
			'recursive' => -1,
			'conditions' => array(
				'BlockOperation.page_id' => $page_id,
				'BlockOperation.parent_id' => $parent_id,
				'BlockOperation.col_num' => $col_num
			)
		));
		return $count_row_num;
	}
}