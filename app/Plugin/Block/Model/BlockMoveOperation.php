<?php
/**
 * BlockMoveOperationモデル
 *
 * <pre>
 *  ブロック移動操作用モデル
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Plugin.Block.Model
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class BlockMoveOperation extends AppModel {
	public $name = 'BlockMoveOperation';
    public $useTable = 'blocks';

	public function findRowCount($page_id, $parent_id, $col_num)
	{
		$count_row_num = $this->find('count', array(
			'fields' => 'COUNT(*) as count',
			'recursive' => -1,
			'conditions' => array(
				'BlockMoveOperation.page_id' => $page_id,
				'BlockMoveOperation.parent_id' => $parent_id,
				'BlockMoveOperation.col_num' => $col_num
			)
		));
		return $count_row_num;
	}

	/**
	 * 行前詰め処理
	 *
	 * @return boolean true or false
	 * @access	public
	 */
	public function decrementRowNum($block = null,$row_num = 1) {
	 	$row_num = -1*$row_num;
	 	return $this->_operationRowNum($block, $row_num);
	}
	public function incrementRowNum($block = null,$row_num = 1) {
	 	return $this->_operationRowNum($block, $row_num);
	}
	protected function _operationRowNum($block = null,$row_num = 1) {
		$fields = array('BlockMoveOperation.row_num'=>'BlockMoveOperation.row_num+('.$row_num.')');
		$conditions = array(
			"BlockMoveOperation.page_id" => $block['Block']['page_id'],
			"BlockMoveOperation.id !=" => $block['Block']['id'],
			"BlockMoveOperation.parent_id" => $block['Block']['parent_id'],
			"BlockMoveOperation.col_num" => $block['Block']['col_num'],
			"BlockMoveOperation.row_num >" => $block['Block']['row_num']
		);
		$ret = $this->updateAll($fields, $conditions);
		return $ret;
	}
	/**
	 * 列前詰め処理
	 *
	 * @return boolean true or false
	 * @access	public
	 */
	public function decrementColNum($block = null,$col_num = 1) {
	 	$col_num = -1*$col_num;
	 	return $this->_operationColNum($block, $col_num);
	}
	public function incrementColNum($block = null,$col_num = 1) {
	 	return $this->_operationColNum($block, $col_num);
	}
	protected function _operationColNum($block = null,$col_num = 1) {
		$fields = array('BlockMoveOperation.col_num'=>'BlockMoveOperation.col_num+('.$col_num.')');
		$conditions = array(
			"BlockMoveOperation.page_id" => $block['Block']['page_id'],
			"BlockMoveOperation.id !=" => $block['Block']['id'],
			"BlockMoveOperation.parent_id" => $block['Block']['parent_id'],
			"BlockMoveOperation.col_num >=" => $block['Block']['col_num']
		);
		$ret = $this->updateAll($fields, $conditions);
		return $ret;
	}

	public function defaultBlock($ins_block) {
		// TODO:固定 test
		unset($ins_block['Block']['id']);
		$ins_block['Block']['title'] = '{X-CONTENT}';
		$ins_block['Block']['module_id'] = 0;
		$ins_block['Block']['content_id'] = 0;
		$ins_block['Block']['show_title'] = _ON;
		$ins_block['Block']['display_flag'] = NC_DISPLAY_FLAG_ON;
		$ins_block['Block']['display_from_date'] = null;
		$ins_block['Block']['display_to_date'] = null;
		$ins_block['Block']['controller_action'] = 'group';
		$ins_block['Block']['theme_name'] = 'None';
		$ins_block['Block']['temp_name'] = 'default';
		$ins_block['Block']['leftmargin'] = 8;
		$ins_block['Block']['rightmargin'] = 8;
		$ins_block['Block']['topmargin'] = 8;
		$ins_block['Block']['bottommargin'] = 8;
		$ins_block['Block']['min_width_size'] = 0;
		$ins_block['Block']['min_height_size'] = 0;
		$ins_block['Block']['lock_authority_id'] = 0;
		return $ins_block;
	}
}