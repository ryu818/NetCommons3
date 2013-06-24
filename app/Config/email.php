<?php
/**
 * This is email configuration file.
 *
 * Use it to configure email transports of Cake.
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Config
 * @since         CakePHP(tm) v 2.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 *
 * Email configuration class.
 * You can specify multiple configurations for production, development and testing.
 *
 * transport => The name of a supported transport; valid options are as follows:
 *		Mail 		- Send using PHP mail function
 *		Smtp		- Send using SMTP
 *		Debug		- Do not send the email, just return the result
 *
 * You can add custom transports (or override existing transports) by adding the
 * appropriate file to app/Network/Email. Transports should be named 'YourTransport.php',
 * where 'Your' is the name of the transport.
 *
 * from =>
 * The origin email. See CakeEmail::from() about the valid values
 *
 */
class EmailConfig {

	public $default = array(
		'transport' => 'Mail',
		'from' => 'you@localhost',
		//'charset' => 'utf-8',
		//'headerCharset' => 'utf-8',
	);

	public $smtp = array(
		'transport' => 'Smtp',
		'from' => array('site@localhost' => 'My Site'),
		'host' => 'localhost',
		'port' => 25,
		'timeout' => 30,
		'username' => 'user',
		'password' => 'secret',
		'client' => null,
		'log' => false,
		//'charset' => 'utf-8',
		//'headerCharset' => 'utf-8',
	);

	public $fast = array(
		'from' => 'you@localhost',
		'sender' => null,
		'to' => null,
		'cc' => null,
		'bcc' => null,
		'replyTo' => null,
		'readReceipt' => null,
		'returnPath' => null,
		'messageId' => true,
		'subject' => null,
		'message' => null,
		'headers' => null,
		'viewRender' => null,
		'template' => false,
		'layout' => false,
		'viewVars' => null,
		'attachments' => null,
		'emailFormat' => null,
		'transport' => 'Smtp',
		'host' => 'localhost',
		'port' => 25,
		'timeout' => 30,
		'username' => 'user',
		'password' => 'secret',
		'client' => null,
		'log' => true,
		//'charset' => 'utf-8',
		//'headerCharset' => 'utf-8',
	);


//TODO:Config でSMTP TLSの値(boolean)をもちTLSによる暗号化送信を可能とすることが望ましい。
/**
 * コンストラクター
 * Configテーブルからメール設定をOptionにセット
 */
	public function __construct() {
		$lang = Configure::read(NC_CONFIG_KEY.'.'.'language');
		App::uses('Config', 'Model');
		$Config = new Config();

		$params = array(
			'fields' => array(
				'Config.name',
				'Config.value',
				'ConfigLang.value'
			),
			'conditions' => array(
				'Config.module_id' => 0,
				'Config.cat_id' => NC_MAIL_CATID
			),
			'joins' => $Config->getJoinsArray($lang),
		);
		$configs = $Config->find('all', $params);

		switch($configs['mailmethod']) {
			case 'sendmail':
				// TODO:SendMailは未実装　app/Network/Email/SendmailTransport.phpを追加して実装する。
				$this->default['transport'] = 'Sendmail';
				$this->default['sendmailpath'] = $configs['sendmailpath'];
				break;
			case 'smtp':
			case 'smtpauth':
				$this->default['transport'] = 'Smtp';
				$smtphost_arr = explode(':', $configs['smtphost']);
				if(count($smtphost_arr) == 2) {
					$this->default['host'] = $smtphost_arr[0];
					$this->default['port'] = $smtphost_arr[1];
				} else {
					$this->default['host'] = $configs['smtphost'];
				}
				if($configs['mailmethod'] == 'smtpauth') {
					$this->default['username'] = $configs['smtpuser'];
					$this->default['password'] = $configs['smtppass'];
				}
				break;
			default:
				$this->default['transport'] = 'Mail';
				break;
		}
		if($configs['htmlmail']) {
			$this->default['emailFormat'] = 'html';
		} else {
			$this->default['emailFormat'] = 'text';
		}
		$this->default['template'] = 'default';
		//$this->default['layout'] = 'default';

		if($configs['fromname'] != '') {
			$this->default['from'] = array($configs['from'] => $configs['fromname']);
		} else {
			$this->default['from'] = $configs['from'];
		}
		// MailComponentに渡すためセット
		Configure::write(NC_CONFIG_KEY.'.'.'htmlmail', $configs['htmlmail']);
		Configure::write(NC_CONFIG_KEY.'.'.'mobile_htmlmail', $configs['mobile_htmlmail']);
		// Mail：Viewに渡すためセット
		Configure::write(NC_CONFIG_KEY.'.'.'mailheader', $configs['mailheader']);
		Configure::write(NC_CONFIG_KEY.'.'.'mailfooter', $configs['mailfooter']);
	}
}
