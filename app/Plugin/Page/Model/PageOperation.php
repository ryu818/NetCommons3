<?php
/**
 * PageOperationモデル
 *
 * <pre>
 *  ページ操作用モデル
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Plugin.Block.Model
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class PageOperation extends AppModel {
	public $name = 'PageOperation';
    public $useTable = 'pages';

/**
 * Insert Page defaults
 * @param   string     edit(編集) or inner or bottom(追加) $type
 * @param   array      $current_page
 * param   array       $current_page
 * @return  array      array($page, $parent_page)
 * @since   v 3.0.0.0
 */
	public function defaultPage($type, $current_page, $parent_page) {
		$lang = Configure::read(NC_CONFIG_KEY.'.'.'language');

		$ins_page = $current_page;
		if($type == 'inner') {
			unset($ins_page['Page']['id']);
			$ins_page['Page']['parent_id'] = $current_page['Page']['id'];
			$ins_page['Page']['thread_num'] = $current_page['Page']['thread_num'] + 1;

		} else if($type == 'bottom') {
			unset($ins_page['Page']['id']);

			// hierarchy
			$ins_page['Authority']['hierarchy'] =$parent_page['Authority']['hierarchy'];
		}
		if($current_page['Page']['thread_num'] == 1) {
			$display_sequence = 1;
		} else {
			$display_sequence = $current_page['Page']['display_sequence'] + 1;
		}
		$conditions = array(
			'PageOperation.position_flag' => _ON,
			'PageOperation.space_type' => $current_page['Page']['space_type'],
			'PageOperation.lang' => array('', $lang)
		);
		if($current_page['Page']['root_id'] != 0) {
			$conditions['PageOperation.root_id'] = $current_page['Page']['root_id'];
		}
		$display_sequence_results = $this->find('all', array(
			'fields' => 'PageOperation.id, PageOperation.parent_id, PageOperation.display_sequence',
			'recursive' => -1,
			'conditions' => $conditions,
			'order' => 'PageOperation.display_sequence'
		));

		$display_sequence_pages = array();
		$parent_id_arr[] = $current_page['Page']['id'];
		foreach ($display_sequence_results as $key => $val) {
			if(in_array($val['Page']['parent_id'], $parent_id_arr)) {
				$display_sequence_pages[$val['Page']['id']] = $val;
				$parent_id_arr[] = $val['Page']['id'];
				var_dump($val);
				$display_sequence = $val['Page']['display_sequence'] + 1;
			}
		}

		$space_type = $ins_page['Page']['space_type'];
		$thread_num = $ins_page['Page']['thread_num'];

		$count_fields = 'MAX(PageOperation.display_sequence) as max_number';
		$count_conditions = array(
			'PageOperation.root_id' => $parent_page['Page']['root_id'],
			'PageOperation.thread_num >' => 1,
			'PageOperation.lang' => array('', $lang)
		);
		$result = $this->find('first', array(
			'fields' => $count_fields,
			'recursive' => -1,
			'conditions' => $count_conditions
		));
		if(isset($result[0]['max_number'])) {
			$count = intval($result[0]['max_number']) + 1;
		} else {
			$count = 1;
		}

		if($space_type == NC_SPACE_TYPE_GROUP && $thread_num == 1) {
			$page_name = __d('page', "New community");	//. '-'.$count;
		} else {
			$page_name = __d('page', "New page");	//. '-'.$count;
		}

		if($display_sequence == 1) {
			// 各トップページ
			$permalink = '';
			if($parent_page['Page']['permalink'] != '') {
				$permalink = $parent_page['Page']['permalink'].$permalink;
			}
		} else {
			$replace_page_name = preg_replace(NC_PERMALINK_PROHIBITION, NC_PERMALINK_PROHIBITION_REPLACE, $page_name);
			if($parent_page['Page']['permalink'] != '') {
				$replace_page_name = $parent_page['Page']['permalink']. '/'. $replace_page_name;
			}
			while(1) {
				$conditions = array(
					'PageOperation.permalink' => $replace_page_name. '-' . $count,
					'PageOperation.lang' => array('', $lang)
				);
				$result = $this->find('first', array(
					'fields' => 'PageOperation.id',
					'recursive' => -1,
					'conditions' => $conditions
				));
				if(isset($result['Page'])) {
					$count++;
				} else {
					break;
				}
			}

			$permalink = $replace_page_name;
		}


		$ins_page['Page']['display_sequence'] = $display_sequence;
		$ins_page['Page']['page_name'] = $page_name. '-' . $count;
		$ins_page['Page']['permalink'] = $permalink. '-' . $count;
		$ins_page['Page']['show_count'] = 0;
		//$ins_page['Page']['default_entry_flag'] = _OFF;

		$ins_page['Page']['display_flag'] = $parent_page['Page']['display_flag'];
		if(!empty($parent_page['Page']['display_from_date']) && $parent_page['Page']['display_apply_subpage'] == _ON) {
			$ins_page['Page']['display_from_date'] = $parent_page['Page']['display_from_date'];
		} else {
			$ins_page['Page']['display_from_date'] = null;
		}
		if(!empty($parent_page['Page']['display_to_date'])) {
			$ins_page['Page']['display_to_date'] = $parent_page['Page']['display_to_date'];
		} else {
			$ins_page['Page']['display_to_date'] = null;
		}

		if($space_type == NC_SPACE_TYPE_PRIVATE || ($space_type == NC_SPACE_TYPE_GROUP && $thread_num == 1)) {
			$ins_page['Page']['lang'] = '';
		} else {
			$ins_page['Page']['lang'] = $lang;
		}

		return $ins_page;
	}

/**
 * afterFind
 *
 * @param  array $results
 * @return array $results
 * @since   v 3.0.0.0
 */
	public function afterFind($results) {
		if(!isset($results[0]['PageOperation'])) {
			return $results;
		}
		$return_results = array();
		foreach ($results as $key => $val) {
			$return_results[$key]['Page'] = $val['PageOperation'];
		}
		return $return_results;
	}
}