<?php
/**
 * AnnouncementAppControllerクラス
 *
 * @copyright     Copyright 2012, NetCommons Project
 * @package       App.Controller
 * @author        Noriko Arai,Ryuji Masukawa
 * @since         v 3.0.0.0
 * @license       http://www.netcommons.org/license.txt  NetCommons License
 */
App::uses('AppPluginController', 'Controller');
class AnnouncementAppController extends AppPluginController {
/**
 * Model name
 *
 * @var array
 */
	public $uses = array('Announcement.Announcement', 'Announcement.AnnouncementEdit');
}