<?php
/**
 * Page Fixture
 * Last update 2013.11.08
 */

class NcPageTreeFixture extends CakeTestFixture {
	public $useDbConfig = 'test';
	public $name = 'PageTree';

	public $fields = array(
		'id'            =>array( 'type' => 'integer','null' => false, 'default' => null, 'key' => 'primary'),
		'parent_id'     =>array( 'type' => 'integer','null'=>false ,'default' => 0 , 'comment'=>'先祖のpage_id'),
		'children_id'   =>array( 'type' => 'integer','null'=>false ,'default' => 0 , 'comment'=>'子孫のpage_id'),
		'stratum_num'   =>array('type' => 'integer','null'=>false ,'default' => 0 , 'comment'=>'先祖からみた子孫の階層'),
		'created'       =>array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified'      =>array('type' => 'datetime', 'null' => true, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);

	public $records = array(
		array(
			'id'            =>1 ,
			'parent_id'     =>1 ,
			'children_id'   =>1 ,
			'stratum_num'   =>0 ,
			'created'       =>'2013-11-11 12:00',
			'modified'      =>'2013-11-11 12:00',
		),
		array(
			'id'            =>2 ,
			'parent_id'     =>2 ,
			'children_id'   =>2 ,
			'stratum_num'   =>0 ,
			'created'       =>'2013-11-11 12:00',
			'modified'      =>'2013-11-11 12:00',
		),
		array(
			'id'            =>3 ,
			'parent_id'     =>3 ,
			'children_id'   =>3 ,
			'stratum_num'   =>0 ,
			'created'       =>'2013-11-11 12:00',
			'modified'      =>'2013-11-11 12:00',
		),
		array(
			'id'            =>4 ,
			'parent_id'     =>4 ,
			'children_id'   =>4 ,
			'stratum_num'   =>0 ,
			'created'       =>'2013-11-11 12:00',
			'modified'      =>'2013-11-11 12:00',
		),
		array(
			'id'            =>5 ,
			'parent_id'     =>5 ,
			'children_id'   =>5 ,
			'stratum_num'   =>0 ,
			'created'       =>'2013-11-11 12:00',
			'modified'      =>'2013-11-11 12:00',
		),
		array(
			'id'            =>6 ,
			'parent_id'     =>6 ,
			'children_id'   =>6 ,
			'stratum_num'   =>0 ,
			'created'       =>'2013-11-11 12:00',
			'modified'      =>'2013-11-11 12:00',
		),
		array(
			'id'            =>7 ,
			'parent_id'     =>7 ,
			'children_id'   =>7 ,
			'stratum_num'   =>0 ,
			'created'       =>'2013-11-11 12:00',
			'modified'      =>'2013-11-11 12:00',
		),
		array(
			'id'            =>8 ,
			'parent_id'     =>8 ,
			'children_id'   =>8 ,
			'stratum_num'   =>0 ,
			'created'       =>'2013-11-11 12:00',
			'modified'      =>'2013-11-11 12:00',
		),
		array(
			'id'            =>9 ,
			'parent_id'     =>1 ,
			'children_id'   =>9 ,
			'stratum_num'   =>1 ,
			'created'       =>'2013-11-11 12:00',
			'modified'      =>'2013-11-11 12:00',
		),
		array(
			'id'            =>10 ,
			'parent_id'     =>9 ,
			'children_id'   =>9 ,
			'stratum_num'   =>0 ,
			'created'       =>'2013-11-11 12:00',
			'modified'      =>'2013-11-11 12:00',
		),
		array(
			'id'            =>11 ,
			'parent_id'     =>2 ,
			'children_id'   =>10 ,
			'stratum_num'   =>1 ,
			'created'       =>'2013-11-11 12:00',
			'modified'      =>'2013-11-11 12:00',
		),
		array(
			'id'            =>12 ,
			'parent_id'     =>10 ,
			'children_id'   =>10 ,
			'stratum_num'   =>0 ,
			'created'       =>'2013-11-11 12:00',
			'modified'      =>'2013-11-11 12:00',
		),
		array(
			'id'            =>13 ,
			'parent_id'     =>3 ,
			'children_id'   =>11 ,
			'stratum_num'   =>1 ,
			'created'       =>'2013-11-11 12:00',
			'modified'      =>'2013-11-11 12:00',
		),
		array(
			'id'            =>14 ,
			'parent_id'     =>11 ,
			'children_id'   =>11 ,
			'stratum_num'   =>0 ,
			'created'       =>'2013-11-11 12:00',
			'modified'      =>'2013-11-11 12:00',
		),
		array(
			'id'            =>15 ,
			'parent_id'     =>9 ,
			'children_id'   =>12 ,
			'stratum_num'   =>2 ,
			'created'       =>'2013-11-11 12:00',
			'modified'      =>'2013-11-11 12:00',
		),
		array(
			'id'            =>16 ,
			'parent_id'     =>1 ,
			'children_id'   =>12 ,
			'stratum_num'   =>1 ,
			'created'       =>'2013-11-11 12:00',
			'modified'      =>'2013-11-11 12:00',
		),
		array(
			'id'            =>17 ,
			'parent_id'     =>12 ,
			'children_id'   =>12 ,
			'stratum_num'   =>0 ,
			'created'       =>'2013-11-11 12:00',
			'modified'      =>'2013-11-11 12:00',
		),
		array(
			'id'            =>18 ,
			'parent_id'     =>10 ,
			'children_id'   =>13 ,
			'stratum_num'   =>2 ,
			'created'       =>'2013-11-11 12:00',
			'modified'      =>'2013-11-11 12:00',
		),
		array(
			'id'            =>19 ,
			'parent_id'     =>2 ,
			'children_id'   =>13 ,
			'stratum_num'   =>1,
			'created'       =>'2013-11-11 12:00',
			'modified'      =>'2013-11-11 12:00',
		),
		array(
			'id'            =>20 ,
			'parent_id'     =>13 ,
			'children_id'   =>13 ,
			'stratum_num'   =>0 ,
			'created'       =>'2013-11-11 12:00',
			'modified'      =>'2013-11-11 12:00',
		),
		array(
			'id'            =>21 ,
			'parent_id'     =>11 ,
			'children_id'   =>14 ,
			'stratum_num'   =>2 ,
			'created'       =>'2013-11-11 12:00',
			'modified'      =>'2013-11-11 12:00',
		),
		array(
			'id'            =>22 ,
			'parent_id'     =>3 ,
			'children_id'   =>14 ,
			'stratum_num'   =>1 ,
			'created'       =>'2013-11-11 12:00',
			'modified'      =>'2013-11-11 12:00',
		),
		array(
			'id'            =>23 ,
			'parent_id'     =>14 ,
			'children_id'   =>14 ,
			'stratum_num'   =>0 ,
			'created'       =>'2013-11-11 12:00',
			'modified'      =>'2013-11-11 12:00',
		),
		array(
			'id'            =>24 ,
			'parent_id'     =>11 ,
			'children_id'   =>15 ,
			'stratum_num'   =>2 ,
			'created'       =>'2013-11-11 12:00',
			'modified'      =>'2013-11-11 12:00',
		),
		array(
			'id'            =>25 ,
			'parent_id'     =>3 ,
			'children_id'   =>15 ,
			'stratum_num'   =>1 ,
			'created'       =>'2013-11-11 12:00',
			'modified'      =>'2013-11-11 12:00',
		),
		array(
			'id'            =>26 ,
			'parent_id'     =>15 ,
			'children_id'   =>15 ,
			'stratum_num'   =>0 ,
			'created'       =>'2013-11-11 12:00',
			'modified'      =>'2013-11-11 12:00',
		),
		array(
			'id'            =>27 ,
			'parent_id'     =>11 ,
			'children_id'   =>16 ,
			'stratum_num'   =>2 ,
			'created'       =>'2013-11-11 12:00',
			'modified'      =>'2013-11-11 12:00',
		),
		array(
			'id'            =>28 ,
			'parent_id'     =>3 ,
			'children_id'   =>16 ,
			'stratum_num'   =>1 ,
			'created'       =>'2013-11-11 12:00',
			'modified'      =>'2013-11-11 12:00',
		),
		array(
			'id'            =>29 ,
			'parent_id'     =>16 ,
			'children_id'   =>16 ,
			'stratum_num'   =>0 ,
			'created'       =>'2013-11-11 12:00',
			'modified'      =>'2013-11-11 12:00',
		),
	);
}
