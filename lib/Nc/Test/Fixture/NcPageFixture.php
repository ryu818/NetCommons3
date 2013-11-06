<?php
/**
 * Created by IntelliJ IDEA.
 * User: nekoget
 * Date: 13/11/06
 * Time: 16:52
 * To change this template use File | Settings | File Templates.
 */

class NcPageFixture extends CakeTestFixture {
	public $useDbConfig = 'test';
	public $name = 'Page';

	public $fields = array(
		'id'        =>array( 'type' => 'integer','null' => false, 'default' => null, 'key' => 'primary'),
		'root_id'   =>array( 'type' => 'integer','length' =>11 ,'null'=>false ,'default' => 0), //int(11) NOT NULL DEFAULT '0' COMMENT '??????ID??????????????????0????0?',
		'parent_id' =>array( 'type' => 'integer','length' =>11 ,'null'=>false ,'default' => 0), //int(11) NOT NULL DEFAULT '0' COMMENT '????ID???0????0?',
		'thread_num'=>array( 'type' => 'integer','length' =>11 ,'null'=>false ,'default' => 0), // int(11) NOT NULL DEFAULT '0' COMMENT '??????????0?',
		'display_sequence'=>array('type' => 'integer','length' =>11 ,'null'=>false ,'default' => 0), // int(11) NOT NULL DEFAULT '0' COMMENT '??????????1????????????????????????????1???????(?????????????????????0)?',
		'page_name' =>array( 'type' => 'string' ,'length' =>30 ,'null'=>false), // varchar(30) NOT NULL COMMENT '????',
		'permalink' =>array( 'type' => 'string' ,'length' =>255 ,'null'=>false), // varchar(255) NOT NULL COMMENT '?????',
		'position_flag'=>array('type' => 'integer','length' =>1,'null'=>false ,'default' => 1), // tinyint(1) NOT NULL DEFAULT '1' COMMENT '??????????1',
		'lang'      =>array( 'type' => 'string' ,'length' =>10 ,'null'=>false), // varchar(10) NOT NULL COMMENT '??(ja,en?)',
		'is_page_meta_node'=>array(  'type' => 'integer','length' =>1 ,'null'=>false ,'default' => 0), // tinyint(1) NOT NULL DEFAULT '0' COMMENT '????????????ID????????????????????',
		'is_page_style_node'=>array( 'type' => 'integer','length' =>1 ,'null'=>false ,'default' => 0), // tinyint(1) NOT NULL DEFAULT '0' COMMENT '??????????????ID????????????????????',
		'is_page_layout_node'=>array('type' => 'integer','length' =>1 ,'null'=>false ,'default' => 0), // tinyint(1) NOT NULL DEFAULT '0' COMMENT '???????????????ID????????????????????',
		'is_page_theme_node'=>array( 'type' => 'integer','length' =>1 ,'null'=>false ,'default' => 0), // tinyint(1) NOT NULL DEFAULT '0' COMMENT '?????????????ID????????????????????',
		'is_page_column_node'=>array('type' => 'integer','length' =>1 ,'null'=>false ,'default' => 0), // tinyint(1) NOT NULL DEFAULT '0' COMMENT '?????????????ID????????????????????',
		'room_id'     =>array(  'type' => 'integer','length' =>11 ,'null'=>false ,'default' => 0), // int(11) NOT NULL DEFAULT '0',
		'space_type'  =>array(  'type' => 'integer','length' =>2 ,'null'=>false ,'default' => 0), // int(2) NOT NULL DEFAULT '0' COMMENT '???????\n1??????????\n2???????\n3??????????????????\n4????????',
		'show_count'  =>array(  'type' => 'integer','length' =>11 ,'null'=>false ,'default' => 0), // int(11) NOT NULL DEFAULT '0' COMMENT '???????????????????????????????????????????????Block.row_num,Block.col_num????????????????????????????',
		'display_flag'=>array(  'type' => 'integer','length' =>2 ,'null'=>false ,'default' => 1), // int(2) NOT NULL DEFAULT '1' COMMENT '????????',
		'display_from_date'  =>array('datetime'), // datetime DEFAULT NULL COMMENT '????From',
		'display_to_date'    =>array(  'datetime'), // datetime DEFAULT NULL COMMENT '????To',
		'display_apply_subpage'     =>array('type' => 'integer','length' =>1,'null'=>false ,'default' => 1), // tinyint(1) NOT NULL DEFAULT '1' COMMENT '????From??????????????????????????',
		'display_reverse_permalink' =>array('type' => 'string' ,'length' =>255 ,'null'=>true , 'default'=>NULL), // varchar(255) DEFAULT NULL,
		'is_approved'=>array('type' => 'integer','length' =>1 ,'null'=>false ,'default' =>1), // tinyint(1) NOT NULL DEFAULT '1' COMMENT '??????????????????',
		'lock_authority_id'  =>array('type' => 'integer','length' =>11 ,'null'=>false ,'default' => 0), // int(11) NOT NULL DEFAULT '0' COMMENT '????????????????????????????????????????????????????',
		'created'    => array('datetime') , // datetime DEFAULT NULL,
		'created_user_id'   => array('type' => 'integer','length' =>11 ,'null'=>false ,'default' => 0) , // int(11) DEFAULT NULL,
		'created_user_name' => array('type' => 'string' ,'length' =>255 ,'null'=>false) , // varchar(255) NOT NULL,
		'modified'   => array('datetime') , // datetime DEFAULT NULL,
		'modified_user_id'   => array('type' => 'integer','length' =>11 ,'null'=>true ,'default' =>NULL) , // int(11) DEFAULT NULL,
		'modified_user_name' => array('type' => 'string' ,'length' =>255 ,'null'=>false) , // varchar(255) NOT NULL,
	);

	public $records = array(
		array(
			'id'=> 1,
			'root_id'=> 0 ,
			'parent_id'=> 0 ,
			'thread_num'=> 0 ,
			'display_sequence'=> 1 ,
			'page_name'=> 'Public room',
			'permalink'=> '',
			'position_flag'=> 1 ,
			'lang'=> '',
			'is_page_meta_node'=> 0,
			'is_page_style_node'=> 0,
			'is_page_layout_node'=> 0,
			'is_page_theme_node'=> 0,
			'is_page_column_node'=> 0,
			'room_id'=> 0,
			'space_type'=> 1,
			'show_count'=> 0,
			'display_flag'=> 1,
			'display_from_date'=> NULL,
			'display_to_date'=> NULL,
			'display_apply_subpage'=> 1,
			'display_reverse_permalink'=> NULL,
			'is_approved'=> 1,
			'lock_authority_id'=> 0,
			'created'=> NULL,
			'created_user_id'=> 0,
			'created_user_name'=> '',
			'modified'=> NULL,
			'modified_user_id'=> 0,
			'modified_user_name'=>''
		),
		array(
			'id'                => 2,
			'root_id'           => 0 ,
			'parent_id'         => 0 ,
			'thread_num'        => 0 ,
			'display_sequence'  => 2 ,
			'page_name'         => 'Myportal',
			'permalink'         => '',
			'position_flag'     => 1 ,
			'lang'              => '',
			'is_page_meta_node'  => 0,
			'is_page_style_node' => 0,
			'is_page_layout_node'=> 0,
			'is_page_theme_node' => 0,
			'is_page_column_node'=> 0,
			'room_id'           => 0,
			'space_type'        => 2,
			'show_count'        => 0,
			'display_flag'      => 1,
			'display_from_date' => NULL,
			'display_to_date'   => NULL,
			'display_apply_subpage'=> 1,
			'display_reverse_permalink'=> NULL,
			'is_approved'       => 1,
			'lock_authority_id' => 0,
			'created'           => NULL,
			'created_user_id'   => 0,
			'created_user_name' => '',
			'modified'          => NULL,
			'modified_user_id'  => 0,
			'modified_user_name'=>''
		),
		array(
			'id'                => 3,
			'root_id'           => 0 ,
			'parent_id'         => 0 ,
			'thread_num'        => 0 ,
			'display_sequence'  => 3 ,
			'page_name'         => 'Private room',
			'permalink'         => '',
			'position_flag'     => 1 ,
			'lang'              => '',
			'is_page_meta_node'  => 0,
			'is_page_style_node' => 0,
			'is_page_layout_node'=> 0,
			'is_page_theme_node' => 0,
			'is_page_column_node'=> 0,
			'room_id'           => 0,
			'space_type'        => 3,
			'show_count'        => 0,
			'display_flag'      => 1,
			'display_from_date' => NULL,
			'display_to_date'   => NULL,
			'display_apply_subpage'=> 1,
			'display_reverse_permalink'=> NULL,
			'is_approved'       => 1,
			'lock_authority_id' => 0,
			'created'           => NULL,
			'created_user_id'   => 0,
			'created_user_name' => '',
			'modified'          => NULL,
			'modified_user_id'  => 0,
			'modified_user_name'=>''
		),
		array(
			'id'                => 4,
			'root_id'           => 0 ,
			'parent_id'         => 0 ,
			'thread_num'        => 0 ,
			'display_sequence'  => 4,
			'page_name'         => 'Community',
			'permalink'         => '',
			'position_flag'     => 1 ,
			'lang'              => '',
			'is_page_meta_node'  => 0,
			'is_page_style_node' => 0,
			'is_page_layout_node'=> 0,
			'is_page_theme_node' => 0,
			'is_page_column_node'=> 0,
			'room_id'           => 0,
			'space_type'        => 4,
			'show_count'        => 0,
			'display_flag'      => 1,
			'display_from_date' => NULL,
			'display_to_date'   => NULL,
			'display_apply_subpage'=> 1,
			'display_reverse_permalink'=> NULL,
			'is_approved'       => 1,
			'lock_authority_id' => 0,
			'created'           => NULL,
			'created_user_id'   => 0,
			'created_user_name' => '',
			'modified'          => NULL,
			'modified_user_id'  => 0,
			'modified_user_name'=>''
		),
		array(
			'id'                => 5,
			'root_id'           => 0 ,
			'parent_id'         => 0 ,
			'thread_num'        => 0 ,
			'display_sequence'  => 4,
			'page_name'         => 'Headercolumn',
			'permalink'         => '',
			'position_flag'     => 1 ,
			'lang'              => '',
			'is_page_meta_node'  => 0,
			'is_page_style_node' => 0,
			'is_page_layout_node'=> 0,
			'is_page_theme_node' => 0,
			'is_page_column_node'=> 0,
			'room_id'           => 9,
			'space_type'        => 1,
			'show_count'        => 8,
			'display_flag'      => 1,
			'display_from_date' => NULL,
			'display_to_date'   => NULL,
			'display_apply_subpage'=> 1,
			'display_reverse_permalink'=> NULL,
			'is_approved'       => 1,
			'lock_authority_id' => 0,
			'created'           => NULL,
			'created_user_id'   => 0,
			'created_user_name' => '',
			'modified'          => '2013-05-28 07:19:48',
			'modified_user_id'  => 1,
			'modified_user_name'=>'admin'
		),
		array(
			'id'                => 6,
			'root_id'           => 0 ,
			'parent_id'         => 0 ,
			'thread_num'        => 0 ,
			'display_sequence'  => 0,
			'page_name'         => 'Leftcolumn',
			'permalink'         => '',
			'position_flag'     => 0 ,
			'lang'              => '',
			'is_page_meta_node'  => 0,
			'is_page_style_node' => 0,
			'is_page_layout_node'=> 0,
			'is_page_theme_node' => 0,
			'is_page_column_node'=> 0,
			'room_id'           => 9,
			'space_type'        => 1,
			'show_count'        => 0,
			'display_flag'      => 1,
			'display_from_date' => NULL,
			'display_to_date'   => NULL,
			'display_apply_subpage'=> 1,
			'display_reverse_permalink'=> NULL,
			'is_approved'       => 1,
			'lock_authority_id' => 0,
			'created'           => NULL,
			'created_user_id'   => 0,
			'created_user_name' => '',
			'modified'          => NULL,
			'modified_user_id'  => 1,
			'modified_user_name'=>''
		),
		array(
			'id'                => 7,
			'root_id'           => 0 ,
			'parent_id'         => 0 ,
			'thread_num'        => 0 ,
			'display_sequence'  => 0,
			'page_name'         => 'Rightcolumn',
			'permalink'         => '',
			'position_flag'     => 0 ,
			'lang'              => '',
			'is_page_meta_node'  => 0,
			'is_page_style_node' => 0,
			'is_page_layout_node'=> 0,
			'is_page_theme_node' => 0,
			'is_page_column_node'=> 0,
			'room_id'           => 9,
			'space_type'        => 1,
			'show_count'        => 2,
			'display_flag'      => 1,
			'display_from_date' => NULL,
			'display_to_date'   => NULL,
			'display_apply_subpage'=> 1,
			'display_reverse_permalink'=> NULL,
			'is_approved'       => 1,
			'lock_authority_id' => 0,
			'created'           => NULL,
			'created_user_id'   => 0,
			'created_user_name' => '',
			'modified'          => NULL,
			'modified_user_id'  => 1,
			'modified_user_name'=>''
		),
		array(
			'id'                => 8,
			'root_id'           => 0 ,
			'parent_id'         => 0 ,
			'thread_num'        => 0 ,
			'display_sequence'  => 0,
			'page_name'         => 'Footercolumn',
			'permalink'         => '',
			'position_flag'     => 0 ,
			'lang'              => '',
			'is_page_meta_node'  => 0,
			'is_page_style_node' => 0,
			'is_page_layout_node'=> 0,
			'is_page_theme_node' => 0,
			'is_page_column_node'=> 0,
			'room_id'           => 9,
			'space_type'        => 1,
			'show_count'        => 0,
			'display_flag'      => 1,
			'display_from_date' => NULL,
			'display_to_date'   => NULL,
			'display_apply_subpage'=> 1,
			'display_reverse_permalink'=> NULL,
			'is_approved'       => 1,
			'lock_authority_id' => 0,
			'created'           => NULL,
			'created_user_id'   => 0,
			'created_user_name' => '',
			'modified'          => NULL,
			'modified_user_id'  => 1,
			'modified_user_name'=>''
		),
		array(
			'id'                => 9,
			'root_id'           => 9 ,
			'parent_id'         => 1 ,
			'thread_num'        => 1 ,
			'display_sequence'  => 0,
			'page_name'         => 'Public room',
			'permalink'         => '',
			'position_flag'     => 1 ,
			'lang'              => '',
			'is_page_meta_node'  => 0,
			'is_page_style_node' => 0,
			'is_page_layout_node'=> 0,
			'is_page_theme_node' => 0,
			'is_page_column_node'=> 0,
			'room_id'           => 9,
			'space_type'        => 1,
			'show_count'        => 0,
			'display_flag'      => 1,
			'display_from_date' => NULL,
			'display_to_date'   => NULL,
			'display_apply_subpage'=> 1,
			'display_reverse_permalink'=> NULL,
			'is_approved'       => 1,
			'lock_authority_id' => 0,
			'created'           => NULL,
			'created_user_id'   => 0,
			'created_user_name' => '',
			'modified'          => NULL,
			'modified_user_id'  => 1,
			'modified_user_name'=>''
		),
		array(
			'id'                => 10,
			'root_id'           => 10 ,
			'parent_id'         => 2 ,
			'thread_num'        => 1 ,
			'display_sequence'  => 0,
			'page_name'         => 'Myportal',
			'permalink'         => 'Admin',
			'position_flag'     => 1 ,
			'lang'              => '',
			'is_page_meta_node'  => 0,
			'is_page_style_node' => 0,
			'is_page_layout_node'=> 0,
			'is_page_theme_node' => 0,
			'is_page_column_node'=> 0,
			'room_id'           => 10,
			'space_type'        => 2,
			'show_count'        => 0,
			'display_flag'      => 2,
			'display_from_date' => NULL,
			'display_to_date'   => NULL,
			'display_apply_subpage'=> 1,
			'display_reverse_permalink'=> NULL,
			'is_approved'       => 1,
			'lock_authority_id' => 0,
			'created'           => NULL,
			'created_user_id'   => 0,
			'created_user_name' => '',
			'modified'          => NULL,
			'modified_user_id'  => 1,
			'modified_user_name'=>''
		),
		array(
			'id'                => 11,
			'root_id'           => 11 ,
			'parent_id'         => 3 ,
			'thread_num'        => 1 ,
			'display_sequence'  => 0,
			'page_name'         => 'Private room',
			'permalink'         => 'Admin',
			'position_flag'     => 1 ,
			'lang'              => '',
			'is_page_meta_node'  => 0,
			'is_page_style_node' => 0,
			'is_page_layout_node'=> 0,
			'is_page_theme_node' => 0,
			'is_page_column_node'=> 0,
			'room_id'           => 11,
			'space_type'        => 3,
			'show_count'        => 0,
			'display_flag'      => 1,
			'display_from_date' => NULL,
			'display_to_date'   => NULL,
			'display_apply_subpage'=> 1,
			'display_reverse_permalink'=> NULL,
			'is_approved'       => 1,
			'lock_authority_id' => 0,
			'created'           => NULL,
			'created_user_id'   => 0,
			'created_user_name' => '',
			'modified'          => NULL,
			'modified_user_id'  => 1,
			'modified_user_name'=>''
		),
		array(
			'id'                => 12,
			'root_id'           => 9 ,
			'parent_id'         => 9 ,
			'thread_num'        => 2 ,
			'display_sequence'  => 1,
			'page_name'         => '??????',
			'permalink'         => '',
			'position_flag'     => 1 ,
			'lang'              => 'ja',
			'is_page_meta_node'  => 0,
			'is_page_style_node' => 0,
			'is_page_layout_node'=> 0,
			'is_page_theme_node' => 0,
			'is_page_column_node'=> 0,
			'room_id'           => 9,
			'space_type'        => 1,
			'show_count'        => 110,
			'display_flag'      => 1,
			'display_from_date' => NULL,
			'display_to_date'   => NULL,
			'display_apply_subpage'=> 1,
			'display_reverse_permalink'=> NULL,
			'is_approved'       => 1,
			'lock_authority_id' => 0,
			'created'           => NULL,
			'created_user_id'   => 0,
			'created_user_name' => '',
			'modified'          => '2013-07-12 07:13:42',
			'modified_user_id'  => 1,
			'modified_user_name'=>'admin'
		),
		array(
			'id'                => 13,
			'root_id'           => 10 ,
			'parent_id'         => 10 ,
			'thread_num'        => 2 ,
			'display_sequence'  => 1,
			'page_name'         => 'Myportal Top',
			'permalink'         => 'admin',
			'position_flag'     => 1 ,
			'lang'              => 'ja',
			'is_page_meta_node'  => 0,
			'is_page_style_node' => 0,
			'is_page_layout_node'=> 0,
			'is_page_theme_node' => 0,
			'is_page_column_node'=> 0,
			'room_id'           => 10,
			'space_type'        => 2,
			'show_count'        => 13,
			'display_flag'      => 2,
			'display_from_date' => NULL,
			'display_to_date'   => NULL,
			'display_apply_subpage'=> 1,
			'display_reverse_permalink'=> NULL,
			'is_approved'       => 1,
			'lock_authority_id' => 0,
			'created'           => NULL,
			'created_user_id'   => 0,
			'created_user_name' => '',
			'modified'          => NULL,
			'modified_user_id'  => 0,
			'modified_user_name'=>''
		),
		array(
			'id'                => 14,
			'root_id'           => 11 ,
			'parent_id'         => 11 ,
			'thread_num'        => 2 ,
			'display_sequence'  => 1,
			'page_name'         => 'Private Top',
			'permalink'         => 'admin',
			'position_flag'     => 1 ,
			'lang'              => 'ja',
			'is_page_meta_node'  => 0,
			'is_page_style_node' => 0,
			'is_page_layout_node'=> 0,
			'is_page_theme_node' => 0,
			'is_page_column_node'=> 0,
			'room_id'           => 11,
			'space_type'        => 3,
			'show_count'        => 130,
			'display_flag'      => 1,
			'display_from_date' => NULL,
			'display_to_date'   => NULL,
			'display_apply_subpage'=> 1,
			'display_reverse_permalink'=> NULL,
			'is_approved'       => 1,
			'lock_authority_id' => 0,
			'created'           => NULL,
			'created_user_id'   => 0,
			'created_user_name' => '',
			'modified'          => '2013-06-24 06:59:37',
			'modified_user_id'  => 1,
			'modified_user_name'=>'admin'
		)
	);
}
