<?php
/**
 * ModulesLinkモデル
 *
 * <pre>
 *  モジュールの追加リストの表示
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Model
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
class ModulesLink extends AppModel
{
    var $name = 'ModulesLink';

    public $belongsTo = array(
        'Module' => array(
            'type'    => 'INNER'
        )
    );

/**
 * block_id,user_idから該当ブロックを取得
 * @param   mixed $room_id
 * @param   mixed $authority_id
 * @param   mixed $space_type
 * @param   boolean $afterFind_flag	コールバックを呼ぶかどうか
 * @return  array   $block
 * @since   v 3.0.0.0
 */
	public function findModuleslinks($room_id, $authority_id, $space_type) {
		if(is_array($room_id)) {
			$room_id[] = 0;
		} else if($room_id != 0) {
			$room_id = array(0, $room_id);
		}
		if(is_array($authority_id)) {
			$authority_id[] = 0;
		} else if($authority_id != 0) {
			$authority_id = array(0, $authority_id);
		}
		$conditions = array (
			'Module.disposition_flag' => _ON,
			'ModulesLink.room_id' => $room_id,
			'ModulesLink.authority_id' => $authority_id,
			'ModulesLink.space_type' => $space_type
		);

		$order = array('Module.display_sequence' => 'ASC');
		$params = array(
						'fields' => array(
							'ModulesLink.room_id',
							'ModulesLink.authority_id',
							'ModulesLink.space_type',
							'ModulesLink.module_id',
							'Module.dir_name',
							'Module.module_icon'
						),
						'conditions' => $conditions,
						'order' => $order
		);

		$modules_links = $this->find('all', $params);

		return $modules_links;
	}

/**
 * afterFind
 *
 * @param  array $results
 * @return array $results
 * @since   v 3.0.0.0
 */
	public function afterFind($results) {

		$ret_room_id = array();
		$ret_authority_id = array();
		$ret_space_authority_id = array();
		$ret_space_type = array();

		$locale = Configure::read('locale');

		foreach ($results as $key => $val) {
			// 1.みているroom_idのデータ(ルーム)
			// 2.みているspace_type, authority_id(会員権限)のデータ(マイポータル、マイルーム)
			// 3.みているspace_type(パブリック、マイポータル、マイルーム、コミュニティ、デフォルト)
			$val['ModulesLink']['module_name'] = __('New module');
			$file_path = App::pluginPath($val['Module']['dir_name']) . 'Locale'. '/' . $locale. '/'. NC_MODINFO_FILENAME;
			if (file_exists($file_path)) {
	 	        $modinfo_ini = parse_ini_file($file_path);
	 	        if(!empty($modinfo_ini["module_name"])) {
	 	        	$val['Module']['module_name'] = $modinfo_ini["module_name"];
	 	        }
	       	}

	       	if( $val['ModulesLink']['room_id'] > 0 ) {
	       		$ret_room_id[$val['ModulesLink']['module_id']] = $val;
	       	} else if($val['ModulesLink']['authority_id'] > 0 && $val['ModulesLink']['space_type'] > 0) {
	       		$ret_space_authority_id[$val['ModulesLink']['module_id']] = $val;
	       	} else if($val['ModulesLink']['authority_id'] > 0) {
	       		$ret_authority_id[$val['ModulesLink']['module_id']] = $val;
	       	} else {
	       		$ret_space_type[$val['ModulesLink']['module_id']] = $val;
	       	}
		}
		if(count($ret_room_id) > 0)
			return $ret_room_id;
		else if(count($ret_space_authority_id) > 0)
			return $ret_space_authority_id;
		else if(count($ret_authority_id) > 0)
			return $ret_authority_id;

		return $ret_space_type;
	}
}