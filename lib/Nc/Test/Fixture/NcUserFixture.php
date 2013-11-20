<?php
/**
 * User Fixture
 */
class NcUserFixture extends CakeTestFixture {
	public $useDbConfig = 'test';
	public $name = 'User';

	public $fields = array(
		'id'=>array(      'type'=>'integer','length' =>11 ,  'key' => 'primary' ),
		'login_id'=>array('type'=>'string', 'length' =>50 ,  'null'=>false ),
		'password'=>array('type'=>'string', 'length' =>50 ,  'null'=>true , 'default' =>null),
		'handle'=>array(  'type'=>'string', 'length' =>100 , 'null'=>false),
		'authority_id'=>array('type'=>'integer','length' =>11 ,  'null'=>false , 'default' => 0),
		'is_active'=>array(   'type'=>'integer','length' =>3 ,   'null'=>false , 'default' => 1),
		'permalink'=>array(   'type'=>'string', 'length' =>255 , 'null'=>false),
		'myportal_page_id'=>array('type'=>'integer','length' =>11 ,  'null'=>false , 'default' => 0),
		'private_page_id'=>array( 'type'=>'integer','length' =>11 ,  'null'=>false , 'default' => 0),
		'avatar'=>array(          'type'=>'string', 'length' =>50 , 'null'=>true , 'default' =>null),
		'activate_key'=>array(    'type'=>'string', 'length' =>8  , 'null'=>true , 'default' =>null),
		'lang'=>array(            'type'=>'string', 'length' =>50 , 'null'=>true , 'default' =>'ja'),
		'timezone_offset'=>array( 'type'=>'integer','length' =>11 ,  'null'=>true , 'default' => 9),
		'email'=>array(           'type'=>'string', 'length' =>100 , 'null'=>true),
		'mobile_email'=>array(     'type'=>'string', 'length' =>100 , 'null'=>true),
		'password_regist'=>array(  'type'=>'datetime' , 'null'=>true , 'default' =>null),
		'last_login'=>array(       'type'=>'datetime' , 'null'=>true , 'default' =>null),
		'previous_login'=>array(   'type'=>'datetime' , 'null'=>true , 'default' =>null),
		'created'=>array('datetime'),
		'created_user_id'=>array(  'type'=>'integer','length' =>11 , 'null'=>true , 'default' => null),
		'created_user_name'=>array('type'=>'string', 'length' =>50 , 'null'=>true , 'default' => null),
		'modified'=>array('datetime'),
		'modified_user_id'=>array(  'type'=>'integer','length' =>11 , 'null'=>true , 'default' => null),
		'modified_user_name'=>array('type'=>'string', 'length' =>50 , 'null'=>true , 'default' => null)
	);

	public $records = array(
		array(
			'id'=>"1",
			'login_id'=>'admin' ,
			'password'=>'1a9faaae0428d9f50245c1fb77cad74a25fd917a' ,
			'handle'=>'admin',
			'authority_id'=>"1",
			'is_active'=>"1",
			'permalink'=>'admin',
			'myportal_page_id'=>"10",
			'private_page_id'=>"11",
			'avatar'=>NULL,
			'activate_key'=>NULL,
			'lang'=>'ja',
			'timezone_offset'=>"9",
			'email'=>'',
			'mobile_email'=>'',
			'password_regist'=>NULL ,
			'last_login'=>'2013-09-27 00:42:44',
			'previous_login'=>'2013-07-22 08:20:15',
			'created'=>'2013-10-31 04:09:21',
			'created_user_id'=>"1",
			'created_user_name'=>'admin',
			'modified'=>'2013-10-31 09:52:46',
			'modified_user_id'=>'1',
			'modified_user_name'=>''
		),
		array(
			'id'=>"2",
			'login_id'=>'admin_2' ,
			'password'=>'1a9faaae0428d9f50245c1fb77cad74a25fd917a' ,
			'handle'=>'admin',
			'authority_id'=>"1",
			'is_active'=>"1",
			'permalink'=>'admin',
			'myportal_page_id'=>"10",
			'private_page_id'=>"11",
			'avatar'=>NULL,
			'activate_key'=>NULL,
			'lang'=>'ja',
			'timezone_offset'=>"9",
			'email'=>'',
			'mobile_email'=>'',
			'password_regist'=>NULL ,
			'last_login'=>'2013-09-27 00:42:44',
			'previous_login'=>'2013-07-22 08:20:15',
			'created'=>'2013-10-31 04:09:21',
			'created_user_id'=>"1",
			'created_user_name'=>'admin',
			'modified'=>'2013-10-31 09:52:46',
			'modified_user_id'=>'0',
			'modified_user_name'=>''
		),
		array(
			'id'=>"7",
			'login_id'=>'admin_7' ,
			'password'=>'1a9faaae0428d9f50245c1fb77cad74a25fd917a' ,
			'handle'=>'admin',
			'authority_id'=>"1",
			'is_active'=>"1",
			'permalink'=>'admin',
			'myportal_page_id'=>"10",
			'private_page_id'=>"11",
			'avatar'=>NULL,
			'activate_key'=>NULL,
			'lang'=>'ja',
			'timezone_offset'=>"9",
			'email'=>'',
			'mobile_email'=>'',
			'password_regist'=>NULL ,
			'last_login'=>'2013-09-27 00:42:44',
			'previous_login'=>'2013-07-22 08:20:15',
			'created'=>'2013-10-31 04:09:21',
			'created_user_id'=>"1",
			'created_user_name'=>'admin',
			'modified'=>'2013-10-31 09:52:46',
			'modified_user_id'=>'0',
			'modified_user_name'=>''
		)
	);

}