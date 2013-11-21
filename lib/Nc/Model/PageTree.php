<?php
/**
 * PageTreeモデル
 *
 * <pre>
 *  ページの階層情報を制御する
 * </pre>
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       app.Model
 * @author        Noriko Arai,Takako Miyagawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */

class PageTree extends AppModel
{

/**
 * construct
 * @param   void
 * @return  void
 * @since   v 3.0.0.0
 */
	public function __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);

	}


	function hoge()
	{
		//var_dump($this->ds);
		//var_dump($this->useDbConfig);
		return $this->find('all');
	}

}