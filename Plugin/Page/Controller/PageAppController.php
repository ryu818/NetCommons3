<?php
/**
 * PageAppControllerクラス
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       App.Controller
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
namespace Page\Controller;

use NC\Controller\AppPluginController;

class PageAppController extends AppPluginController {
	/**
	 * Component name
	 *
	 * @var array
	 */
	public $components = array('CheckAuth' => array('chkPlugin' => false, 'chkBlockId' => false));
}
