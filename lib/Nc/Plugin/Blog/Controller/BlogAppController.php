<?php
/**
 * BlogAppControllerクラス
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       App.Controller
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
App::uses('AppPluginController', 'Controller');
class BlogAppController extends AppPluginController {
/**
 * Model name
 *
 * @var array
 */
	public $uses = array('Blog.Blog', 'Blog.BlogStyle', 'Blog.BlogTerm', 'Blog.BlogComment', 'Blog.BlogPost');

/**
 * __construct
 *
 * @param CakeRequest $request
 * @param CakeResponse $response
 */
	public function __construct($request = null, $response = null) {
		parent::__construct($request, $response);
		include_once dirname(dirname(__FILE__)).'/Config/defines.inc.php';
	}
}