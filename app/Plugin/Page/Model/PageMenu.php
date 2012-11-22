<?php
/**
 * PageMenuモデル
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
class PageMenu extends AppModel {
	public $name = 'PageMenu';
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
		include_once dirname(dirname(__FILE__)).'/Config/defines.inc.php';
		$lang = Configure::read(NC_CONFIG_KEY.'.'.'language');

		$ins_page = $current_page;
		if($type == 'edit') {
			return $current_page;
		} else if($type == 'inner') {
			$root_sequence = $current_page['Page']['root_sequence'];
			unset($ins_page['Page']['id']);
			$ins_page['Page']['parent_id'] = $current_page['Page']['id'];
			$ins_page['Page']['thread_num'] = $current_page['Page']['thread_num'] + 1;

			$display_sequence = $current_page['Page']['display_sequence'] + 1;

			$conditions = array(
				'PageMenu.position_flag' => _ON,
				'PageMenu.space_type' => $current_page['Page']['space_type'],
				'PageMenu.root_sequence' => $current_page['Page']['root_sequence'],
				'PageMenu.thread_num >' => $current_page['Page']['thread_num'],
				'PageMenu.lang' => array('', $lang)
			);
			if($current_page['Page']['root_id'] != 0) {
				$conditions['PageMenu.root_id'] = $current_page['Page']['root_id'];
			}
			$display_sequence_results = $this->find('all', array(
				'fields' => 'PageMenu.id, PageMenu.parent_id, PageMenu.display_sequence',
				'recursive' => -1,
				'conditions' => $conditions,
				'order' => 'PageMenu.display_sequence'
			));

			$display_sequence_pages = array();
			$parent_id_arr[] = $current_page['Page']['id'];
			foreach ($display_sequence_results as $key => $val) {
				if(in_array($val['PageMenu']['parent_id'], $parent_id_arr)) {
					$display_sequence_pages[$val['Page']['id']] = $val;
					$parent_id_arr[] = $val['PageMenu']['id'];
					$display_sequence = $val['PageMenu']['display_sequence'] + 1;
				}
			}
		} else if($type == 'bottom') {
			unset($ins_page['Page']['id']);
			$display_sequence = $current_page['Page']['display_sequence'] + 1;

			// hierarchy
			$ins_page['Page']['hierarchy'] =$parent_page['Authority']['hierarchy'];
		}

		$space_type = $ins_page['Page']['space_type'];
		$thread_num = $ins_page['Page']['thread_num'];
		$root_sequence = $current_page['Page']['root_sequence'];

		$count_fields = 'MAX(PageMenu.display_sequence) as max_number';
		$count_conditions = array(
				'PageMenu.parent_id' => $parent_page['Page']['id'],
				'PageMenu.lang' => array('', $lang)
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

		if($root_sequence == 0) {
			$root_sequence = $count;
		}

		if($space_type == NC_SPACE_TYPE_GROUP && $thread_num == 1) {
			$page_name = __d('page', "New community"). '-'.$count;
		} else if($space_type == NC_SPACE_TYPE_PUBLIC) {
			$page_name = __d('page', "New page"). '-'.$count;
		} else {
			$page_name = __d('page', "New page"). '-'.$count;
		}

		if($thread_num == 2 && $root_sequence == 1 && $display_sequence == 1) {
			// 各トップページ
			$permalink = '';
		} else if($space_type == NC_SPACE_TYPE_GROUP && $thread_num == 1) {
			$permalink = PAGES_GROUP_PREFIX. '-'.$count;
		} else if($space_type == NC_SPACE_TYPE_MYPORTAL) {
			$permalink = PAGES_MYPORTAL_PREFIX. '-'.$count;
		} else if($space_type == NC_SPACE_TYPE_PRIVATE) {
			$permalink = PAGES_PRIVATE_PREFIX. '-'.$count;
		} else {
			$permalink = PAGES_PUBLIC_PREFIX. '-'.$count;
		}

		$ins_page['Page']['display_sequence'] = $display_sequence;
		$ins_page['Page']['root_sequence'] = $root_sequence;
		$ins_page['Page']['page_name'] = $page_name;
		$ins_page['Page']['permalink'] = $permalink;
		$ins_page['Page']['show_count'] = 0;
		//$ins_page['Page']['default_entry_flag'] = _OFF;
		$ins_page['Page']['display_flag'] = NC_DISPLAY_FLAG_ON;
		$ins_page['Page']['display_from_date'] = null;
		$ins_page['Page']['display_to_date'] = null;
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
		if(!isset($results[0]['PageMenu'])) {
			return $results;
		}
		$return_results = array();
		foreach ($results as $key => $val) {
			$return_results[$key]['Page'] = $val['PageMenu'];
		}
		return $return_results;
	}
}